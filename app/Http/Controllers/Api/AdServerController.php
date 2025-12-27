<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdUnit;
use App\Models\Campaign;
use App\Services\AdServerService;
use App\Services\RevenueCalculationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdServerController extends Controller
{
    protected $adServerService;
    protected $revenueCalculation;

    public function __construct(AdServerService $adServerService, RevenueCalculationService $revenueCalculation)
    {
        $this->adServerService = $adServerService;
        $this->revenueCalculation = $revenueCalculation;
    }

    /**
     * Serve ad for a given ad unit code.
     *
     * @param  string  $unitCode
     * @param  Request  $request
     * @return JsonResponse
     */
    public function serveAd(string $unitCode, Request $request): JsonResponse
    {
        $adData = $this->adServerService->serveAd($unitCode, $request);

        if (!$adData) {
            return response()->json([
                'success' => false,
                'message' => 'No ad available',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $adData,
        ]);
    }

    /**
     * Track impression.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function trackImpression(Request $request): JsonResponse
    {
        $request->validate([
            'campaign_id' => 'required|exists:campaigns,id',
            'ad_unit_id' => 'required|exists:ad_units,id',
            'impression_id' => 'nullable|string',
            'visitor_info' => 'nullable|array',
        ]);

        $campaign = Campaign::findOrFail($request->campaign_id);
        $adUnit = AdUnit::findOrFail($request->ad_unit_id);

        // Track impression (handles balance updates atomically)
        $impression = $this->adServerService->trackImpression($campaign, $adUnit, $request);

        if (!$impression) {
            return response()->json([
                'success' => false,
                'message' => 'Impression not processed (rate limit or insufficient balance)',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'impression_id' => $impression->id,
        ]);
    }

    /**
     * Track click.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function trackClick(Request $request): JsonResponse
    {
        $request->validate([
            'campaign_id' => 'required|exists:campaigns,id',
            'ad_unit_id' => 'required|exists:ad_units,id',
            'impression_id' => 'nullable|string',
            'visitor_info' => 'nullable|array',
        ]);

        $campaign = Campaign::findOrFail($request->campaign_id);
        $adUnit = AdUnit::findOrFail($request->ad_unit_id);

        // Track click (handles balance updates atomically)
        $click = $this->adServerService->trackClick(
            $campaign,
            $adUnit,
            $request,
            $request->impression_id
        );

        if (!$click) {
            return response()->json([
                'success' => false,
                'message' => 'Click not processed (rate limit or insufficient balance)',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'click_id' => $click->id,
            'target_url' => $campaign->target_url,
        ]);
    }

    /**
     * Get ad unit statistics.
     *
     * @param  string  $unitCode
     * @param  Request  $request
     * @return JsonResponse
     */
    public function getStats(string $unitCode, Request $request): JsonResponse
    {
        $adUnit = AdUnit::where('unit_code', $unitCode)
            ->with('website.publisher')
            ->firstOrFail();

        // Check if user owns this ad unit
        $user = $request->user();
        if ($user->isPublisher() && $adUnit->website->publisher->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $impressions = $adUnit->impressions()
            ->where('is_bot', false)
            ->count();

        $clicks = $adUnit->clicks()
            ->where('is_bot', false)
            ->where('is_fraud', false)
            ->count();

        $ctr = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0;

        // Use publisher_earning for revenue display (what publisher actually earned)
        $revenue = $adUnit->impressions()
            ->where('is_bot', false)
            ->sum('publisher_earning') + $adUnit->clicks()
            ->where('is_bot', false)
            ->where('is_fraud', false)
            ->sum('publisher_earning');

        return response()->json([
            'success' => true,
            'data' => [
                'impressions' => $impressions,
                'clicks' => $clicks,
                'ctr' => $ctr,
                'revenue' => round($revenue, 2),
            ],
        ]);
    }
}

