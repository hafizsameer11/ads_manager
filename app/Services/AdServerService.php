<?php

namespace App\Services;

use App\Models\AdUnit;
use App\Models\Campaign;
use App\Models\CampaignTargeting;
use App\Models\Impression;
use App\Models\Click;
use App\Models\Website;
use App\Models\Setting;
use App\Services\FraudDetectionService;
use App\Services\RevenueCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AdServerService
{
    protected $fraudDetection;
    protected $revenueCalculation;

    public function __construct(
        FraudDetectionService $fraudDetection,
        RevenueCalculationService $revenueCalculation
    ) {
        $this->fraudDetection = $fraudDetection;
        $this->revenueCalculation = $revenueCalculation;
    }

    /**
     * Serve ad for a given ad unit code.
     *
     * @param  string  $unitCode
     * @param  Request  $request
     * @return array|null
     */
    public function serveAd(string $unitCode, Request $request): ?array
    {
        $adUnit = AdUnit::where('unit_code', $unitCode)
            ->where('status', 'active')
            ->with('website.publisher')
            ->first();

        if (!$adUnit) {
            return null;
        }
        
        // Check if website is approved (ad units on rejected/suspended websites should not serve ads)
        if ($adUnit->website->status !== 'approved') {
            return null;
        }

        // Get active campaigns matching ad unit type
        $campaigns = Campaign::where('ad_type', $adUnit->type)
            ->where('status', 'active')
            ->where('approval_status', 'approved')
            ->where('start_date', '<=', now())
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->whereColumn('budget', '>', 'total_spent')
            ->with(['targeting', 'advertiser'])
            ->get();
        
        // Filter campaigns by advertiser balance (auto-pause campaigns with insufficient balance)
        $campaigns = $campaigns->filter(function ($campaign) {
            // Check advertiser balance
            if (!$campaign->advertiser || $campaign->advertiser->balance <= 0) {
                // Auto-pause campaign if balance is insufficient
                if ($campaign->status === 'active') {
                    $campaign->update(['status' => 'paused']);
                }
                return false;
            }
            return true;
        });

        if ($campaigns->isEmpty()) {
            return null;
        }

        // Filter campaigns based on targeting
        $eligibleCampaigns = $this->filterCampaignsByTargeting($campaigns, $request);

        if ($eligibleCampaigns->isEmpty()) {
            return null;
        }

        // Filter campaigns by frequency limits (IP-based)
        $ip = $request->ip();
        $eligibleCampaigns = $this->filterCampaignsByFrequencyLimits($eligibleCampaigns, $ip, $adUnit);

        if ($eligibleCampaigns->isEmpty()) {
            return null;
        }

        // Select campaign using rotation logic (from admin settings)
        $campaign = $this->selectCampaign($eligibleCampaigns, $adUnit->id);

        if (!$campaign) {
            return null;
        }

        // Track impression (handles balance updates atomically)
        $impression = $this->trackImpression($campaign, $adUnit, $request);
        
        // If impression was blocked (rate limit, insufficient balance), return null
        if (!$impression) {
            return null;
        }

        // Calculate revenue for response
        $revenue = $this->revenueCalculation->calculateImpressionRevenue($campaign, $adUnit);

        // Parse ad content (can be JSON string or array)
        $adContent = $campaign->ad_content;
        if (is_string($adContent)) {
            $adContent = json_decode($adContent, true) ?: [];
        }
        if (!is_array($adContent)) {
            $adContent = [];
        }

        // Format response for JavaScript SDK
        $response = [
            'campaign_id' => $campaign->id,
            'ad_unit_id' => $adUnit->id,
            'type' => $campaign->ad_type,
            'target_url' => $campaign->target_url,
            'title' => $adContent['title'] ?? $campaign->name,
            'width' => $adUnit->width,
            'height' => $adUnit->height,
        ];

        // Add ad creative based on what's available
        if (!empty($adContent['image_url'])) {
            $response['image_url'] = $adContent['image_url'];
        } elseif (!empty($adContent['html'])) {
            $response['html'] = $adContent['html'];
        } elseif (!empty($adContent['text'])) {
            $response['text'] = $adContent['text'];
        } elseif (!empty($adContent['description'])) {
            $response['text'] = $adContent['description'];
        } else {
            // Fallback: use campaign name as text
            $response['text'] = $campaign->name;
        }

        return $response;
    }

    /**
     * Filter campaigns based on targeting criteria.
     *
     * @param  \Illuminate\Support\Collection  $campaigns
     * @param  Request  $request
     * @return \Illuminate\Support\Collection
     */
    protected function filterCampaignsByTargeting($campaigns, Request $request): \Illuminate\Support\Collection
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $countryCode = $this->getCountryCode($request);
        $deviceInfo = $this->parseDeviceInfo($userAgent);

        return $campaigns->filter(function ($campaign) use ($countryCode, $deviceInfo, $ip, $request) {
            if (!$campaign->targeting) {
                return true; // No targeting = show to all
            }

            $targeting = $campaign->targeting;

            // Check country targeting
            if ($targeting->countries && !empty($targeting->countries)) {
                if (!in_array($countryCode, $targeting->countries)) {
                    return false;
                }
            }

            // Check device targeting
            if ($targeting->devices && !empty($targeting->devices)) {
                if (!in_array($deviceInfo['device'], $targeting->devices)) {
                    return false;
                }
            }

            // Check OS targeting
            if ($targeting->operating_systems && !empty($targeting->operating_systems)) {
                if (!in_array($deviceInfo['os'], $targeting->operating_systems)) {
                    return false;
                }
            }

            // Check browser targeting
            if ($targeting->browsers && !empty($targeting->browsers)) {
                if (!in_array($deviceInfo['browser'], $targeting->browsers)) {
                    return false;
                }
            }

            // Check VPN/Proxy
            if (!$targeting->is_vpn_allowed) {
                if ($this->fraudDetection->isVpn($ip)) {
                    return false;
                }
            }

            if (!$targeting->is_proxy_allowed) {
                if ($this->fraudDetection->isProxy($ip)) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * Filter campaigns by frequency limits.
     *
     * @param  \Illuminate\Support\Collection  $campaigns
     * @param  string  $ip
     * @param  AdUnit  $adUnit
     * @return \Illuminate\Support\Collection
     */
    protected function filterCampaignsByFrequencyLimits($campaigns, string $ip, AdUnit $adUnit): \Illuminate\Support\Collection
    {
        $today = now()->startOfDay();
        
        // Get global defaults
        $globalMaxImpressions = Setting::get('global_max_impressions_per_ip_per_day');
        $globalMaxClicks = Setting::get('global_max_clicks_per_ip_per_day');
        
        return $campaigns->filter(function ($campaign) use ($ip, $adUnit, $today, $globalMaxImpressions, $globalMaxClicks) {
            // Get campaign-specific limits or use global defaults
            $maxImpressions = $campaign->max_impressions_per_user ?? $globalMaxImpressions;
            $maxClicks = $campaign->max_clicks_per_user ?? $globalMaxClicks;
            
            // Check impression limit for this IP + campaign + ad unit today
            if ($maxImpressions !== null) {
                $impressionsCount = Impression::where('campaign_id', $campaign->id)
                    ->where('ip_address', $ip)
                    ->where('ad_unit_id', $adUnit->id)
                    ->where('impression_at', '>=', $today)
                    ->count();
                
                if ($impressionsCount >= $maxImpressions) {
                    return false; // Frequency limit exceeded
                }
            }
            
            // Check click limit for this IP + campaign today
            if ($maxClicks !== null) {
                $clicksCount = Click::where('campaign_id', $campaign->id)
                    ->where('ip_address', $ip)
                    ->where('clicked_at', '>=', $today)
                    ->where('is_fraud', false)
                    ->count();
                
                if ($clicksCount >= $maxClicks) {
                    return false; // Click limit exceeded
                }
            }
            
            return true;
        });
    }

    /**
     * Select campaign using rotation logic (based on admin settings).
     *
     * @param  \Illuminate\Support\Collection  $campaigns
     * @param  int  $adUnitId
     * @return Campaign|null
     */
    protected function selectCampaign($campaigns, int $adUnitId): ?Campaign
    {
        if ($campaigns->isEmpty()) {
            return null;
        }
        
        // Get rotation mode from settings (default: weighted)
        $rotationMode = Setting::get('ad_rotation_mode', 'weighted');
        
        switch ($rotationMode) {
            case 'round_robin':
                return $this->selectRoundRobin($campaigns, $adUnitId);
                
            case 'weighted':
                return $this->selectWeighted($campaigns);
                
            case 'random':
                return $campaigns->random();
                
            default:
                // Fallback to weighted
                return $this->selectWeighted($campaigns);
        }
    }

    /**
     * Select campaign using round-robin rotation.
     *
     * @param  \Illuminate\Support\Collection  $campaigns
     * @param  int  $adUnitId
     * @return Campaign
     */
    protected function selectRoundRobin($campaigns, int $adUnitId): Campaign
    {
        // Get sorted campaign IDs for consistent ordering
        $campaignIds = $campaigns->sortBy('id')->pluck('id')->toArray();
        
        // Get last served campaign index from cache
        $cacheKey = "ad_rotation_rr:ad_unit:{$adUnitId}";
        $lastIndex = Cache::get($cacheKey, -1);
        
        // Get next index (wrap around if needed)
        $nextIndex = ($lastIndex + 1) % count($campaignIds);
        
        // Update cache with next index (expires in 24 hours, but we update it each time)
        Cache::put($cacheKey, $nextIndex, now()->addHours(24));
        
        // Find and return the campaign at next index
        $nextCampaignId = $campaignIds[$nextIndex];
        return $campaigns->firstWhere('id', $nextCampaignId);
    }

    /**
     * Select campaign using weighted rotation.
     *
     * @param  \Illuminate\Support\Collection  $campaigns
     * @return Campaign
     */
    protected function selectWeighted($campaigns): Campaign
    {
        // Calculate total weight (sum of rotation_weight)
        $totalWeight = $campaigns->sum('rotation_weight');
        
        if ($totalWeight <= 0) {
            // If all weights are 0 or null, fallback to random
            return $campaigns->random();
        }
        
        // Generate random number between 1 and totalWeight
        $random = mt_rand(1, $totalWeight);
        $current = 0;
        
        foreach ($campaigns as $campaign) {
            $weight = $campaign->rotation_weight ?? 1;
            $current += $weight;
            
            if ($random <= $current) {
                return $campaign;
            }
        }
        
        // Fallback to first campaign (should not happen)
        return $campaigns->first();
    }

    /**
     * Track impression.
     *
     * @param  Campaign  $campaign
     * @param  AdUnit  $adUnit
     * @param  Request  $request
     * @return Impression|null
     */
    public function trackImpression(Campaign $campaign, AdUnit $adUnit, Request $request): ?Impression
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $countryCode = $this->getCountryCode($request);
        $deviceInfo = $this->parseDeviceInfo($userAgent);

        // Rate limiting: Max 20 impressions per IP per minute per ad unit
        if ($this->fraudDetection->exceedsImpressionLimit($ip, $adUnit->id)) {
            // Ignore event (no charge, no earnings)
            return null;
        }

        // Fraud detection
        $isBot = $this->fraudDetection->isBot($userAgent, $ip);
        $isVpn = $this->fraudDetection->isVpn($ip);
        $isProxy = $this->fraudDetection->isProxy($ip);

        // Calculate revenue and earnings
        $revenue = $this->revenueCalculation->calculateImpressionRevenue($campaign, $adUnit);
        $distribution = $this->revenueCalculation->distributeRevenue($revenue);
        $publisherEarning = $distribution['publisher_share'];
        $adminProfit = $distribution['admin_profit'];

        // If bot or no revenue, still log but don't charge/earn
        if ($isBot || $revenue <= 0) {
            return Impression::create([
                'campaign_id' => $campaign->id,
                'ad_unit_id' => $adUnit->id,
                'website_id' => $adUnit->website_id,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'country_code' => $countryCode,
                'device_type' => $deviceInfo['device'],
                'os' => $deviceInfo['os'],
                'browser' => $deviceInfo['browser'],
                'is_bot' => $isBot,
                'is_vpn' => $isVpn,
                'is_proxy' => $isProxy,
                'revenue' => $revenue,
                'publisher_earning' => 0,
                'admin_profit' => 0,
                'impression_at' => now(),
            ]);
        }

        // Load relationships
        $campaign->load('advertiser');
        $adUnit->load('website.publisher');

        // Atomic balance updates in DB transaction
        return DB::transaction(function () use ($campaign, $adUnit, $ip, $userAgent, $countryCode, $deviceInfo, $isBot, $isVpn, $isProxy, $revenue, $publisherEarning, $adminProfit) {
            // Check advertiser balance before processing
            $advertiser = $campaign->advertiser;
            if (!$advertiser || $advertiser->balance < $revenue) {
                // Insufficient balance - auto-pause campaign
                $campaign->update(['status' => 'paused']);
                return null; // Don't process event
            }

            // Create impression record with earnings
            $impression = Impression::create([
                'campaign_id' => $campaign->id,
                'ad_unit_id' => $adUnit->id,
                'website_id' => $adUnit->website_id,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'country_code' => $countryCode,
                'device_type' => $deviceInfo['device'],
                'os' => $deviceInfo['os'],
                'browser' => $deviceInfo['browser'],
                'is_bot' => $isBot,
                'is_vpn' => $isVpn,
                'is_proxy' => $isProxy,
                'revenue' => $revenue,
                'publisher_earning' => $publisherEarning,
                'admin_profit' => $adminProfit,
                'impression_at' => now(),
            ]);

            // Deduct from advertiser balance
            $advertiser->decrement('balance', $revenue);
            $advertiser->increment('total_spent', $revenue);

            // Add to publisher balance
            $publisher = $adUnit->website->publisher;
            if ($publisher) {
                $publisher->increment('balance', $publisherEarning);
                $publisher->increment('total_earnings', $publisherEarning);
            }

            // Update campaign stats
            $campaign->increment('impressions');
            $campaign->increment('total_spent', $revenue);
            $campaign->update(['ctr' => $campaign->calculateCTR()]);

            return $impression;
        });
    }

    /**
     * Track click.
     *
     * @param  Campaign  $campaign
     * @param  AdUnit  $adUnit
     * @param  Request  $request
     * @param  int|null  $impressionId
     * @return Click|null
     */
    public function trackClick(Campaign $campaign, AdUnit $adUnit, Request $request, ?int $impressionId = null): ?Click
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $countryCode = $this->getCountryCode($request);
        $deviceInfo = $this->parseDeviceInfo($userAgent);

        // Rate limiting: Max 2 clicks per IP per minute per campaign
        if ($this->fraudDetection->exceedsClickLimit($ip, $campaign->id)) {
            // Ignore event (no charge, no earnings)
            return null;
        }

        // Fraud detection
        $isBot = $this->fraudDetection->isBot($userAgent, $ip);
        $isFraud = $this->fraudDetection->isClickFraud($ip, $campaign->id);
        $fraudReason = $isFraud ? $this->fraudDetection->getFraudReason($ip, $campaign->id) : null;

        // Calculate revenue and earnings
        $revenue = $this->revenueCalculation->calculateClickRevenue($campaign, $adUnit);
        $distribution = $this->revenueCalculation->distributeRevenue($revenue);
        $publisherEarning = $distribution['publisher_share'];
        $adminProfit = $distribution['admin_profit'];

        // If bot, fraud, or no revenue, still log but don't charge/earn
        if ($isBot || $isFraud || $revenue <= 0) {
            return Click::create([
                'impression_id' => $impressionId,
                'campaign_id' => $campaign->id,
                'ad_unit_id' => $adUnit->id,
                'website_id' => $adUnit->website_id,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'country_code' => $countryCode,
                'device_type' => $deviceInfo['device'],
                'os' => $deviceInfo['os'],
                'browser' => $deviceInfo['browser'],
                'is_bot' => $isBot,
                'is_fraud' => $isFraud,
                'fraud_reason' => $fraudReason,
                'revenue' => 0,
                'publisher_earning' => 0,
                'admin_profit' => 0,
                'clicked_at' => now(),
            ]);
        }

        // Load relationships
        $campaign->load('advertiser');
        $adUnit->load('website.publisher');

        // Atomic balance updates in DB transaction
        return DB::transaction(function () use ($campaign, $adUnit, $impressionId, $ip, $userAgent, $countryCode, $deviceInfo, $isBot, $isFraud, $fraudReason, $revenue, $publisherEarning, $adminProfit) {
            // Check advertiser balance before processing
            $advertiser = $campaign->advertiser;
            if (!$advertiser || $advertiser->balance < $revenue) {
                // Insufficient balance - auto-pause campaign
                $campaign->update(['status' => 'paused']);
                return null; // Don't process event
            }

            // Create click record with earnings
            $click = Click::create([
                'impression_id' => $impressionId,
                'campaign_id' => $campaign->id,
                'ad_unit_id' => $adUnit->id,
                'website_id' => $adUnit->website_id,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'country_code' => $countryCode,
                'device_type' => $deviceInfo['device'],
                'os' => $deviceInfo['os'],
                'browser' => $deviceInfo['browser'],
                'is_bot' => $isBot,
                'is_fraud' => $isFraud,
                'fraud_reason' => $fraudReason,
                'revenue' => $revenue,
                'publisher_earning' => $publisherEarning,
                'admin_profit' => $adminProfit,
                'clicked_at' => now(),
            ]);

            // Deduct from advertiser balance
            $advertiser->decrement('balance', $revenue);
            $advertiser->increment('total_spent', $revenue);

            // Add to publisher balance
            $publisher = $adUnit->website->publisher;
            if ($publisher) {
                $publisher->increment('balance', $publisherEarning);
                $publisher->increment('total_earnings', $publisherEarning);
            }

            // Update campaign stats
            $campaign->increment('clicks');
            $campaign->increment('total_spent', $revenue);
            $campaign->update(['ctr' => $campaign->calculateCTR()]);

            return $click;
        });
    }

    /**
     * Get country code from request.
     *
     * @param  Request  $request
     * @return string|null
     */
    protected function getCountryCode(Request $request): ?string
    {
        // In production, use GeoIP service like MaxMind or ipapi.co
        // For now, return null or get from headers
        return $request->header('CF-IPCountry') // Cloudflare
            ?? $request->header('X-Country-Code')
            ?? null;
    }

    /**
     * Parse device info from user agent.
     *
     * @param  string|null  $userAgent
     * @return array
     */
    protected function parseDeviceInfo(?string $userAgent): array
    {
        if (!$userAgent) {
            return ['device' => 'unknown', 'os' => 'unknown', 'browser' => 'unknown'];
        }

        $device = 'desktop';
        $os = 'unknown';
        $browser = 'unknown';

        // Simple device detection (use a proper library like Mobile_Detect in production)
        if (preg_match('/mobile|android|iphone|ipad/i', $userAgent)) {
            $device = 'mobile';
            if (preg_match('/tablet|ipad/i', $userAgent)) {
                $device = 'tablet';
            }
        }

        // OS detection
        if (preg_match('/windows/i', $userAgent)) {
            $os = 'windows';
        } elseif (preg_match('/mac|os x/i', $userAgent)) {
            $os = 'mac';
        } elseif (preg_match('/android/i', $userAgent)) {
            $os = 'android';
        } elseif (preg_match('/ios|iphone|ipad/i', $userAgent)) {
            $os = 'ios';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $os = 'linux';
        }

        // Browser detection
        if (preg_match('/chrome/i', $userAgent) && !preg_match('/edg/i', $userAgent)) {
            $browser = 'chrome';
        } elseif (preg_match('/firefox/i', $userAgent)) {
            $browser = 'firefox';
        } elseif (preg_match('/safari/i', $userAgent) && !preg_match('/chrome/i', $userAgent)) {
            $browser = 'safari';
        } elseif (preg_match('/edg/i', $userAgent)) {
            $browser = 'edge';
        }

        return compact('device', 'os', 'browser');
    }
}


