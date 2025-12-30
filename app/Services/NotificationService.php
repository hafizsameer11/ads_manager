<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Create notification for user.
     *
     * @param  User  $user
     * @param  string  $type
     * @param  string  $title
     * @param  string  $message
     * @param  array  $data
     * @return Notification
     */
    public function create(User $user, string $type, string $title, string $message, array $data = []): Notification
    {
        return Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'is_read' => false,
        ]);
    }

    /**
     * Create notification for campaign approval.
     *
     * @param  User  $user
     * @param  int  $campaignId
     * @param  string  $status
     * @return Notification
     */
    public function notifyCampaignApproval(User $user, int $campaignId, string $status): Notification
    {
        $title = $status === 'approved' ? 'Campaign Approved' : 'Campaign Rejected';
        $message = $status === 'approved' 
            ? "Your campaign #{$campaignId} has been approved and is now active."
            : "Your campaign #{$campaignId} has been rejected. Please check the details.";

        return $this->create($user, 'campaign_approved', $title, $message, [
            'campaign_id' => $campaignId,
            'status' => $status,
        ]);
    }

    /**
     * Create notification for withdrawal processing.
     *
     * @param  User  $user
     * @param  int  $withdrawalId
     * @param  float  $amount
     * @param  string  $status
     * @return Notification
     */
    public function notifyWithdrawalProcessing(User $user, int $withdrawalId, float $amount, string $status): Notification
    {
        $title = match($status) {
            'approved' => 'Withdrawal Approved',
            'rejected' => 'Withdrawal Rejected',
            'processing' => 'Withdrawal Processing',
            default => 'Withdrawal Update',
        };

        $message = match($status) {
            'approved' => "Your withdrawal request #{$withdrawalId} for \${$amount} has been approved and processed.",
            'rejected' => "Your withdrawal request #{$withdrawalId} for \${$amount} has been rejected.",
            'processing' => "Your withdrawal request #{$withdrawalId} for \${$amount} is being processed.",
            default => "Your withdrawal request #{$withdrawalId} has been updated.",
        };

        return $this->create($user, 'withdrawal_processed', $title, $message, [
            'withdrawal_id' => $withdrawalId,
            'amount' => $amount,
            'status' => $status,
        ]);
    }

    /**
     * Create notification for publisher approval.
     *
     * @param  User  $user
     * @param  string  $status
     * @return Notification
     */
    public function notifyPublisherApproval(User $user, string $status): Notification
    {
        $title = $status === 'approved' ? 'Publisher Account Approved' : 'Publisher Account Rejected';
        $message = $status === 'approved'
            ? 'Your publisher account has been approved. You can now start adding websites and creating ad units.'
            : 'Your publisher account has been rejected. Please contact support for more information.';

        return $this->create($user, 'publisher_approved', $title, $message, [
            'status' => $status,
        ]);
    }

    /**
     * Create notification for advertiser approval.
     *
     * @param  User  $user
     * @param  string  $status
     * @return Notification
     */
    public function notifyAdvertiserApproval(User $user, string $status): Notification
    {
        $title = $status === 'approved' ? 'Advertiser Account Approved' : 'Advertiser Account Rejected';
        $message = $status === 'approved'
            ? 'Your advertiser account has been approved. You can now create campaigns and deposit funds.'
            : 'Your advertiser account has been rejected. Please contact support for more information.';

        return $this->create($user, 'advertiser_approved', $title, $message, [
            'status' => $status,
        ]);
    }

    /**
     * Get unread notifications count for user.
     *
     * @param  User  $user
     * @return int
     */
    public function getUnreadCount(User $user): int
    {
        return Notification::where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Mark all notifications as read for user.
     *
     * @param  User  $user
     * @return int
     */
    public function markAllAsRead(User $user): int
    {
        return Notification::where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
}










