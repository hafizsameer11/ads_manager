<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\AdUnit;
use App\Models\Publisher;
use App\Models\Impression;
use App\Models\Click;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class RevenueCalculationService
{
    /**
     * Get admin share percentage from settings or default to 20%.
     */
    protected function getAdminShare(): float
    {
        return (float) Setting::get('admin_percentage', 20) / 100;
    }

    /**
     * Get publisher share percentage from settings or default to 80%.
     */
    protected function getPublisherShare(): float
    {
        return (float) Setting::get('publisher_percentage', 80) / 100;
    }

    /**
     * Calculate revenue for an impression.
     *
     * @param  Campaign  $campaign
     * @param  AdUnit  $adUnit
     * @return float
     */
    public function calculateImpressionRevenue(Campaign $campaign, AdUnit $adUnit): float
    {
        if ($campaign->pricing_model === 'cpm') {
            // CPM: Cost Per Mille (per 1000 impressions)
            // Use campaign bid amount as CPM rate
            return ($campaign->bid_amount / 1000);
        }

        // For CPC/CPA, impressions don't generate revenue
        return 0.00;
    }

    /**
     * Calculate revenue for a click.
     *
     * @param  Campaign  $campaign
     * @param  AdUnit  $adUnit
     * @return float
     */
    public function calculateClickRevenue(Campaign $campaign, AdUnit $adUnit): float
    {
        if ($campaign->pricing_model === 'cpc') {
            // CPC: Cost Per Click
            // Use campaign bid amount as CPC rate
            return $campaign->bid_amount;
        }

        // For CPM/CPA, clicks don't generate direct revenue
        return 0.00;
    }

    /**
     * Distribute revenue between admin and publisher.
     * Ensures: revenue = publisher_earning + admin_profit exactly.
     *
     * @param  float  $totalRevenue
     * @return array
     */
    public function distributeRevenue(float $totalRevenue): array
    {
        $publisherShare = $this->getPublisherShare();
        $adminShare = $this->getAdminShare();
        
        // Calculate publisher share (rounded to 4 decimals)
        $publisherEarning = round($totalRevenue * $publisherShare, 4);
        
        // Calculate admin profit as remainder to ensure exact match: cost = publisher_earning + admin_profit
        // This prevents rounding errors from causing discrepancies
        $adminProfit = round($totalRevenue - $publisherEarning, 4);
        
        return [
            'publisher_share' => $publisherEarning,
            'admin_profit' => $adminProfit,
            'total' => $totalRevenue,
        ];
    }

    /**
     * Calculate publisher earnings from impression.
     *
     * @param  Campaign  $campaign
     * @param  AdUnit  $adUnit
     * @return float
     */
    public function calculatePublisherEarningFromImpression(Campaign $campaign, AdUnit $adUnit): float
    {
        $totalRevenue = $this->calculateImpressionRevenue($campaign, $adUnit);
        $distribution = $this->distributeRevenue($totalRevenue);
        return $distribution['publisher_share'];
    }

    /**
     * Calculate admin profit from impression.
     *
     * @param  Campaign  $campaign
     * @param  AdUnit  $adUnit
     * @return float
     */
    public function calculateAdminProfitFromImpression(Campaign $campaign, AdUnit $adUnit): float
    {
        $totalRevenue = $this->calculateImpressionRevenue($campaign, $adUnit);
        $distribution = $this->distributeRevenue($totalRevenue);
        return $distribution['admin_profit'];
    }

    /**
     * Calculate publisher earnings from click.
     *
     * @param  Campaign  $campaign
     * @param  AdUnit  $adUnit
     * @return float
     */
    public function calculatePublisherEarningFromClick(Campaign $campaign, AdUnit $adUnit): float
    {
        $totalRevenue = $this->calculateClickRevenue($campaign, $adUnit);
        $distribution = $this->distributeRevenue($totalRevenue);
        return $distribution['publisher_share'];
    }

    /**
     * Calculate admin profit from click.
     *
     * @param  Campaign  $campaign
     * @param  AdUnit  $adUnit
     * @return float
     */
    public function calculateAdminProfitFromClick(Campaign $campaign, AdUnit $adUnit): float
    {
        $totalRevenue = $this->calculateClickRevenue($campaign, $adUnit);
        $distribution = $this->distributeRevenue($totalRevenue);
        return $distribution['admin_profit'];
    }

    /**
     * Update publisher balance from impression.
     *
     * @param  Impression  $impression
     * @return void
     */
    public function updatePublisherBalanceFromImpression(Impression $impression): void
    {
        $adUnit = $impression->adUnit;
        $campaign = $impression->campaign;
        $publisher = $adUnit->website->publisher;

        if (!$publisher || $impression->is_bot) {
            return;
        }

        $earning = $this->calculatePublisherEarningFromImpression($campaign, $adUnit);

        if ($earning > 0) {
            $publisher->increment('balance', $earning);
            $publisher->increment('total_earnings', $earning);
            
            // Process referral earnings
            try {
                $referralService = app(\App\Services\ReferralService::class);
                $referralService->processPublisherReferralEarnings($publisher, $earning);
            } catch (\Exception $e) {
                // Log error but don't fail the transaction
                \Log::error('Failed to process referral earnings: ' . $e->getMessage());
            }
        }
    }

    /**
     * Update publisher balance from click.
     *
     * @param  Click  $click
     * @return void
     */
    public function updatePublisherBalanceFromClick(Click $click): void
    {
        $adUnit = $click->adUnit;
        $campaign = $click->campaign;
        $publisher = $adUnit->website->publisher;

        if (!$publisher || $click->is_bot || $click->is_fraud) {
            return;
        }

        $earning = $this->calculatePublisherEarningFromClick($campaign, $adUnit);

        if ($earning > 0) {
            $publisher->increment('balance', $earning);
            $publisher->increment('total_earnings', $earning);
            
            // Process referral earnings
            try {
                $referralService = app(\App\Services\ReferralService::class);
                $referralService->processPublisherReferralEarnings($publisher, $earning);
            } catch (\Exception $e) {
                // Log error but don't fail the transaction
                \Log::error('Failed to process referral earnings: ' . $e->getMessage());
            }
        }
    }

    /**
     * Calculate daily earnings for a publisher.
     *
     * @param  Publisher  $publisher
     * @param  string|null  $date
     * @return array
     */
    public function calculateDailyEarnings(Publisher $publisher, ?string $date = null): array
    {
        $date = $date ?? now()->format('Y-m-d');

        // Use stored publisher_earning values for accurate reporting
        $impressionEarnings = Impression::whereHas('adUnit.website', function ($query) use ($publisher) {
            $query->where('publisher_id', $publisher->id);
        })
        ->whereDate('impression_at', $date)
        ->where('is_bot', false)
        ->sum('publisher_earning');

        $clickEarnings = Click::whereHas('adUnit.website', function ($query) use ($publisher) {
            $query->where('publisher_id', $publisher->id);
        })
        ->whereDate('clicked_at', $date)
        ->where('is_bot', false)
        ->where('is_fraud', false)
        ->sum('publisher_earning');

        $impressions = Impression::whereHas('adUnit.website', function ($query) use ($publisher) {
            $query->where('publisher_id', $publisher->id);
        })
        ->whereDate('impression_at', $date)
        ->where('is_bot', false)
        ->count();

        $clicks = Click::whereHas('adUnit.website', function ($query) use ($publisher) {
            $query->where('publisher_id', $publisher->id);
        })
        ->whereDate('clicked_at', $date)
        ->where('is_bot', false)
        ->where('is_fraud', false)
        ->count();

        return [
            'date' => $date,
            'impressions' => $impressions,
            'clicks' => $clicks,
            'impression_revenue' => round((float)$impressionEarnings, 2),
            'click_revenue' => round((float)$clickEarnings, 2),
            'total_revenue' => round((float)$impressionEarnings + (float)$clickEarnings, 2),
        ];
    }

    /**
     * Calculate campaign spending.
     *
     * @param  Campaign  $campaign
     * @param  string|null  $date
     * @return array
     */
    public function calculateCampaignSpending(Campaign $campaign, ?string $date = null): array
    {
        $date = $date ?? now()->format('Y-m-d');

        // Use stored revenue values for accurate reporting (cost = publisher_earning + admin_profit)
        $impressionCost = Impression::where('campaign_id', $campaign->id)
            ->whereDate('impression_at', $date)
            ->where('is_bot', false)
            ->sum('revenue');

        $clickCost = Click::where('campaign_id', $campaign->id)
            ->whereDate('clicked_at', $date)
            ->where('is_bot', false)
            ->where('is_fraud', false)
            ->sum('revenue');

        $impressions = Impression::where('campaign_id', $campaign->id)
            ->whereDate('impression_at', $date)
            ->where('is_bot', false)
            ->count();

        $clicks = Click::where('campaign_id', $campaign->id)
            ->whereDate('clicked_at', $date)
            ->where('is_bot', false)
            ->where('is_fraud', false)
            ->count();

        return [
            'date' => $date,
            'impressions' => $impressions,
            'clicks' => $clicks,
            'impression_cost' => round((float)$impressionCost, 2),
            'click_cost' => round((float)$clickCost, 2),
            'total_cost' => round((float)$impressionCost + (float)$clickCost, 2),
        ];
    }
}


