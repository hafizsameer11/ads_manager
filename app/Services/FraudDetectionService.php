<?php

namespace App\Services;

use App\Models\Click;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FraudDetectionService
{
    /**
     * Maximum clicks per IP per hour.
     */
    const MAX_CLICKS_PER_IP_PER_HOUR = 10;

    /**
     * Maximum clicks per IP per campaign per hour.
     */
    const MAX_CLICKS_PER_IP_PER_CAMPAIGN_PER_HOUR = 5;

    /**
     * Check if user agent is a bot.
     *
     * @param  string|null  $userAgent
     * @param  string  $ip
     * @return bool
     */
    public function isBot(?string $userAgent, string $ip): bool
    {
        if (!$userAgent) {
            return true;
        }

        // Common bot user agents
        $botPatterns = [
            'bot', 'crawler', 'spider', 'scraper',
            'googlebot', 'bingbot', 'yahoo', 'slurp',
            'duckduckbot', 'baiduspider', 'yandex',
            'facebookexternalhit', 'twitterbot', 'linkedinbot',
            'whatsapp', 'telegram', 'curl', 'wget',
            'python', 'java', 'php', 'ruby',
        ];

        $userAgentLower = strtolower($userAgent);

        foreach ($botPatterns as $pattern) {
            if (strpos($userAgentLower, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if IP is a VPN.
     *
     * @param  string  $ip
     * @return bool
     */
    public function isVpn(string $ip): bool
    {
        // In production, use a VPN detection service/API like ipapi.co, ip2location, etc.
        // For now, return false (implement with actual service)
        
        // Cache result for 24 hours
        return Cache::remember("vpn_check_{$ip}", 86400, function () use ($ip) {
            // TODO: Implement actual VPN detection API call
            // Example: $response = Http::get("https://ipapi.co/{$ip}/vpn/");
            return false;
        });
    }

    /**
     * Check if IP is a proxy.
     *
     * @param  string  $ip
     * @return bool
     */
    public function isProxy(string $ip): bool
    {
        // In production, use a proxy detection service/API
        // For now, return false (implement with actual service)
        
        // Cache result for 24 hours
        return Cache::remember("proxy_check_{$ip}", 86400, function () use ($ip) {
            // TODO: Implement actual proxy detection API call
            return false;
        });
    }

    /**
     * Check if click is fraudulent.
     *
     * @param  string  $ip
     * @param  int  $campaignId
     * @return bool
     */
    public function isClickFraud(string $ip, int $campaignId): bool
    {
        // Check click frequency per IP
        if ($this->exceedsClickLimit($ip, $campaignId)) {
            return true;
        }

        // Check for suspicious patterns (same IP, same campaign, very short time intervals)
        if ($this->hasSuspiciousClickPattern($ip, $campaignId)) {
            return true;
        }

        return false;
    }

    /**
     * Check if IP exceeds click limits.
     *
     * @param  string  $ip
     * @param  int  $campaignId
     * @return bool
     */
    protected function exceedsClickLimit(string $ip, int $campaignId): bool
    {
        $oneMinuteAgo = now()->subMinute();
        $oneHourAgo = now()->subHour();

        // Rate limit: Max 2 clicks per IP per minute per campaign
        $recentClicks = Click::where('ip_address', $ip)
            ->where('campaign_id', $campaignId)
            ->where('clicked_at', '>=', $oneMinuteAgo)
            ->count();

        if ($recentClicks >= 2) {
            return true;
        }

        // Check total clicks from this IP in the last hour
        $totalClicks = Click::where('ip_address', $ip)
            ->where('clicked_at', '>=', $oneHourAgo)
            ->count();

        if ($totalClicks >= self::MAX_CLICKS_PER_IP_PER_HOUR) {
            return true;
        }

        // Check clicks from this IP for this campaign in the last hour
        $campaignClicks = Click::where('ip_address', $ip)
            ->where('campaign_id', $campaignId)
            ->where('clicked_at', '>=', $oneHourAgo)
            ->count();

        if ($campaignClicks >= self::MAX_CLICKS_PER_IP_PER_CAMPAIGN_PER_HOUR) {
            return true;
        }

        return false;
    }
    
    /**
     * Check if IP exceeds impression limits.
     *
     * @param  string  $ip
     * @param  int  $adUnitId
     * @return bool
     */
    public function exceedsImpressionLimit(string $ip, int $adUnitId): bool
    {
        $oneMinuteAgo = now()->subMinute();

        // Rate limit: Max 20 impressions per IP per minute per ad unit
        $recentImpressions = \App\Models\Impression::where('ip_address', $ip)
            ->where('ad_unit_id', $adUnitId)
            ->where('impression_at', '>=', $oneMinuteAgo)
            ->count();

        return $recentImpressions >= 20;
    }

    /**
     * Check for suspicious click patterns.
     *
     * @param  string  $ip
     * @param  int  $campaignId
     * @return bool
     */
    protected function hasSuspiciousClickPattern(string $ip, int $campaignId): bool
    {
        $oneMinuteAgo = now()->subMinute();

        // Multiple clicks from same IP in less than 1 minute
        $recentClicks = Click::where('ip_address', $ip)
            ->where('campaign_id', $campaignId)
            ->where('clicked_at', '>=', $oneMinuteAgo)
            ->count();

        // More than 3 clicks in a minute is suspicious
        if ($recentClicks > 3) {
            return true;
        }

        return false;
    }

    /**
     * Get fraud reason for logging.
     *
     * @param  string  $ip
     * @param  int  $campaignId
     * @return string
     */
    public function getFraudReason(string $ip, int $campaignId): string
    {
        if ($this->exceedsClickLimit($ip, $campaignId)) {
            return 'Exceeds click limit per IP';
        }

        if ($this->hasSuspiciousClickPattern($ip, $campaignId)) {
            return 'Suspicious click pattern detected';
        }

        return 'Fraud detected';
    }

    /**
     * Block IP address.
     *
     * @param  string  $ip
     * @param  string  $reason
     * @return void
     */
    public function blockIp(string $ip, string $reason = 'Fraudulent activity'): void
    {
        // Store blocked IPs in cache or database
        Cache::put("blocked_ip_{$ip}", [
            'ip' => $ip,
            'reason' => $reason,
            'blocked_at' => now(),
        ], 86400 * 7); // Block for 7 days

        // TODO: Optionally store in database for permanent blocking
    }

    /**
     * Check if IP is blocked.
     *
     * @param  string  $ip
     * @return bool
     */
    public function isIpBlocked(string $ip): bool
    {
        return Cache::has("blocked_ip_{$ip}");
    }

    /**
     * Get fraud statistics for a campaign.
     *
     * @param  int  $campaignId
     * @param  string|null  $date
     * @return array
     */
    public function getFraudStats(int $campaignId, ?string $date = null): array
    {
        $date = $date ?? now()->format('Y-m-d');

        $totalClicks = Click::where('campaign_id', $campaignId)
            ->whereDate('clicked_at', $date)
            ->count();

        $fraudClicks = Click::where('campaign_id', $campaignId)
            ->whereDate('clicked_at', $date)
            ->where('is_fraud', true)
            ->count();

        $botClicks = Click::where('campaign_id', $campaignId)
            ->whereDate('clicked_at', $date)
            ->where('is_bot', true)
            ->count();

        return [
            'total_clicks' => $totalClicks,
            'fraud_clicks' => $fraudClicks,
            'bot_clicks' => $botClicks,
            'fraud_rate' => $totalClicks > 0 ? round(($fraudClicks / $totalClicks) * 100, 2) : 0,
            'bot_rate' => $totalClicks > 0 ? round(($botClicks / $totalClicks) * 100, 2) : 0,
        ];
    }
}


