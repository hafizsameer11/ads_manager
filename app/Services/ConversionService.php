<?php

namespace App\Services;

use App\Models\Conversion;
use App\Models\Campaign;
use App\Models\Click;
use App\Models\Impression;
use App\Models\Advertiser;
use App\Models\Publisher;
use App\Services\RevenueCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ConversionService
{
    protected $revenueCalculation;

    public function __construct(RevenueCalculationService $revenueCalculation)
    {
        $this->revenueCalculation = $revenueCalculation;
    }

    /**
     * Track a conversion for a CPA campaign.
     *
     * @param  Campaign  $campaign
     * @param  Request  $request
     * @param  string|null  $clickId
     * @param  string|null  $impressionId
     * @return Conversion|null
     */
    public function trackConversion(
        Campaign $campaign,
        Request $request,
        ?string $clickId = null,
        ?string $impressionId = null,
        ?string $conversionType = 'purchase',
        ?float $conversionValue = null
    ): ?Conversion {
        // Only track conversions for CPA campaigns
        if ($campaign->pricing_model !== 'cpa') {
            return null;
        }

        // Check if campaign is active
        if (!$campaign->isActive()) {
            return null;
        }

        // Find related click or impression
        $click = null;
        $impression = null;
        $adUnit = null;
        $website = null;

        if ($clickId) {
            $click = Click::find($clickId);
            if ($click && $click->campaign_id === $campaign->id) {
                $adUnit = $click->adUnit;
                $website = $click->website;
            }
        } elseif ($impressionId) {
            $impression = Impression::find($impressionId);
            if ($impression && $impression->campaign_id === $campaign->id) {
                $adUnit = $impression->adUnit;
                $website = $impression->website;
            }
        }

        // If no click/impression found, try to find recent click from same IP
        if (!$click && !$impression) {
            $ip = $request->ip();
            $click = Click::where('campaign_id', $campaign->id)
                ->where('ip_address', $ip)
                ->where('clicked_at', '>=', now()->subDays(30))
                ->orderBy('clicked_at', 'desc')
                ->first();

            if ($click) {
                $adUnit = $click->adUnit;
                $website = $click->website;
            }
        }

        // Calculate revenue (bid_amount is the CPA value)
        $revenue = $campaign->bid_amount;
        if ($conversionValue !== null) {
            $revenue = $conversionValue;
        }

        $distribution = $this->revenueCalculation->distributeRevenue($revenue);
        $publisherEarning = $distribution['publisher_share'];
        $adminProfit = $distribution['admin_profit'];

        // Atomic transaction
        return DB::transaction(function () use (
            $campaign,
            $click,
            $impression,
            $adUnit,
            $website,
            $request,
            $conversionType,
            $conversionValue,
            $revenue,
            $publisherEarning,
            $adminProfit
        ) {
            // Check advertiser balance
            $advertiser = $campaign->advertiser;
            if (!$advertiser || $advertiser->balance < $revenue) {
                $campaign->update(['status' => 'paused']);
                return null;
            }

            // Create conversion
            $conversion = Conversion::create([
                'campaign_id' => $campaign->id,
                'click_id' => $click?->id,
                'impression_id' => $impression?->id,
                'ad_unit_id' => $adUnit?->id,
                'website_id' => $website?->id,
                'conversion_type' => $conversionType,
                'conversion_value' => $conversionValue,
                'conversion_id' => Conversion::generateConversionId(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'country_code' => $this->getCountryCode($request),
                'conversion_data' => $request->except(['_token', 'click_id', 'impression_id']),
                'postback_url' => $request->input('postback_url'),
                'converted_at' => now(),
            ]);

            // Deduct from advertiser balance
            $advertiser->decrement('balance', $revenue);
            $advertiser->increment('total_spent', $revenue);

            // Add to publisher balance
            if ($website && $website->publisher) {
                $publisher = $website->publisher;
                $publisher->increment('balance', $publisherEarning);
                $publisher->increment('total_earnings', $publisherEarning);
                
                // Process referral earnings
                try {
                    $referralService = app(\App\Services\ReferralService::class);
                    $referralService->processPublisherReferralEarnings($publisher, $publisherEarning);
                } catch (\Exception $e) {
                    Log::error('Failed to process referral earnings for conversion: ' . $e->getMessage());
                }
            }

            // Update campaign stats
            $campaign->increment('total_spent', $revenue);

            // Send postback if configured
            if ($conversion->postback_url) {
                $this->sendPostback($conversion);
            }

            return $conversion;
        });
    }

    /**
     * Send postback URL.
     */
    protected function sendPostback(Conversion $conversion): void
    {
        if ($conversion->postback_sent) {
            return;
        }

        try {
            $url = $conversion->postback_url;
            $params = [
                'conversion_id' => $conversion->conversion_id,
                'campaign_id' => $conversion->campaign_id,
                'conversion_type' => $conversion->conversion_type,
                'conversion_value' => $conversion->conversion_value,
                'click_id' => $conversion->click_id,
                'timestamp' => $conversion->converted_at->timestamp,
            ];

            $response = Http::timeout(10)->get($url, $params);

            if ($response->successful()) {
                $conversion->update(['postback_sent' => true]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send postback for conversion ' . $conversion->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Get country code from request.
     */
    protected function getCountryCode(Request $request): ?string
    {
        // Try to get from header
        $countryCode = $request->header('CF-IPCountry') ?? 
                      $request->header('X-Country-Code') ?? 
                      $request->input('country_code');

        return $countryCode ? strtoupper(substr($countryCode, 0, 2)) : null;
    }

    /**
     * Get conversion pixel HTML.
     */
    public function getConversionPixel(Campaign $campaign): string
    {
        $pixelUrl = route('api.conversion.pixel', ['campaignId' => $campaign->id]);
        return '<img src="' . $pixelUrl . '" width="1" height="1" style="display:none;" alt="" />';
    }

    /**
     * Get conversion script.
     */
    public function getConversionScript(Campaign $campaign, array $options = []): string
    {
        $apiUrl = config('app.url') . '/api/conversion/track';
        $campaignId = $campaign->id;
        $conversionType = $options['type'] ?? 'purchase';
        $conversionValue = $options['value'] ?? null;

        $script = <<<SCRIPT
<script>
(function() {
    function trackConversion() {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '{$apiUrl}', true);
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.send(JSON.stringify({
            campaign_id: {$campaignId},
            conversion_type: '{$conversionType}',
            conversion_value: {$conversionValue},
            click_id: localStorage.getItem('ads_network_click_id_{$campaignId}'),
            impression_id: localStorage.getItem('ads_network_impression_id_{$campaignId}')
        }));
    }
    
    // Track on page load
    if (document.readyState === 'complete') {
        trackConversion();
    } else {
        window.addEventListener('load', trackConversion);
    }
})();
</script>
SCRIPT;

        return $script;
    }
}

