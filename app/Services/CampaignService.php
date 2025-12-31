<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignTargeting;
use App\Models\Advertiser;
use App\Models\Setting;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class CampaignService
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Create campaign.
     *
     * @param  Advertiser  $advertiser
     * @param  array  $campaignData
     * @param  array  $targetingData
     * @return Campaign
     */
    public function createCampaign(Advertiser $advertiser, array $campaignData, array $targetingData = []): Campaign
    {
        return DB::transaction(function () use ($advertiser, $campaignData, $targetingData) {
            // Check if auto-approval is enabled
            $autoApprove = Setting::get('campaign_auto_approval', false);
            
            $campaignData['advertiser_id'] = $advertiser->id;
            $campaignData['status'] = 'pending';
            $campaignData['approval_status'] = $autoApprove ? 'approved' : 'pending';

            // Create campaign
            $campaign = Campaign::create($campaignData);

            // Create targeting
            if (!empty($targetingData)) {
                CampaignTargeting::create(array_merge($targetingData, [
                    'campaign_id' => $campaign->id,
                ]));
            }

            // If auto-approved, activate campaign
            if ($autoApprove) {
                $this->activateCampaign($campaign);
            } else {
                // Notify admin about pending campaign
                $this->notificationService->notifyAdmins(
                    'campaign_created',
                    'campaign',
                    'New Campaign Submitted',
                    "A new campaign '{$campaign->name}' has been submitted by {$advertiser->user->name} and is pending approval.",
                    ['campaign_id' => $campaign->id, 'advertiser_id' => $advertiser->id]
                );
            }

            return $campaign;
        });
    }

    /**
     * Approve campaign.
     *
     * @param  Campaign  $campaign
     * @return bool
     */
    public function approveCampaign(Campaign $campaign): bool
    {
        return DB::transaction(function () use ($campaign) {
            $campaign->update([
                'approval_status' => 'approved',
                'status' => 'active',
            ]);

            $this->activateCampaign($campaign);

            // Notify advertiser
            $advertiser = $campaign->advertiser;
            if ($advertiser && $advertiser->user) {
                NotificationService::notifyCampaignApproval(
                    $advertiser->user,
                    $campaign->id,
                    'approved'
                );
            }

            return true;
        });
    }

    /**
     * Reject campaign.
     *
     * @param  Campaign  $campaign
     * @param  string  $reason
     * @return bool
     */
    public function rejectCampaign(Campaign $campaign, string $reason = ''): bool
    {
        return DB::transaction(function () use ($campaign, $reason) {
            $campaign->update([
                'approval_status' => 'rejected',
                'status' => 'stopped',
                'rejection_reason' => $reason,
            ]);

            // Notify advertiser
            $advertiser = $campaign->advertiser;
            if ($advertiser && $advertiser->user) {
                NotificationService::notifyCampaignApproval(
                    $advertiser->user,
                    $campaign->id,
                    'rejected',
                    $reason
                );
            }

            return true;
        });
    }

    /**
     * Activate campaign.
     *
     * @param  Campaign  $campaign
     * @return bool
     */
    public function activateCampaign(Campaign $campaign): bool
    {
        // Check if campaign has budget
        if ($campaign->budget <= $campaign->total_spent) {
            return false;
        }

        // Check if campaign dates are valid
        if ($campaign->start_date && $campaign->start_date > now()) {
            return false;
        }

        if ($campaign->end_date && $campaign->end_date < now()) {
            return false;
        }

        return $campaign->update([
            'status' => 'active',
        ]);
    }

    /**
     * Pause campaign.
     *
     * @param  Campaign  $campaign
     * @return bool
     */
    public function pauseCampaign(Campaign $campaign): bool
    {
        return $campaign->update([
            'status' => 'paused',
        ]);
    }

    /**
     * Resume campaign.
     *
     * @param  Campaign  $campaign
     * @return bool
     */
    public function resumeCampaign(Campaign $campaign): bool
    {
        // Cannot resume stopped campaigns
        if ($campaign->status === 'stopped') {
            return false;
        }

        // Campaign must be approved before it can be resumed
        if ($campaign->approval_status !== 'approved') {
            return false;
        }

        // Check advertiser balance
        $advertiser = $campaign->advertiser;
        if (!$advertiser || $advertiser->balance <= 0) {
            return false;
        }

        return $this->activateCampaign($campaign);
    }

    /**
     * Stop campaign.
     *
     * @param  Campaign  $campaign
     * @return bool
     */
    public function stopCampaign(Campaign $campaign): bool
    {
        return $campaign->update([
            'status' => 'stopped',
        ]);
    }

    /**
     * Check if campaign can be served.
     *
     * @param  Campaign  $campaign
     * @return bool
     */
    public function canServeCampaign(Campaign $campaign): bool
    {
        // Check status
        if ($campaign->status !== 'active' || $campaign->approval_status !== 'approved') {
            return false;
        }

        // Check budget
        if ($campaign->budget <= $campaign->total_spent) {
            return false;
        }

        // Check dates
        if ($campaign->start_date && $campaign->start_date > now()) {
            return false;
        }

        if ($campaign->end_date && $campaign->end_date < now()) {
            return false;
        }

        // Check advertiser balance
        $advertiser = $campaign->advertiser;
        if (!$advertiser || $advertiser->balance <= 0) {
            return false;
        }

        return true;
    }
}


