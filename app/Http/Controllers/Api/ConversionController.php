<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Services\ConversionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ConversionController extends Controller
{
    protected $conversionService;

    public function __construct(ConversionService $conversionService)
    {
        $this->conversionService = $conversionService;
    }

    /**
     * Track conversion (POST endpoint).
     */
    public function track(Request $request): JsonResponse
    {
        $request->validate([
            'campaign_id' => 'required|exists:campaigns,id',
            'conversion_type' => 'nullable|string|max:50',
            'conversion_value' => 'nullable|numeric|min:0',
            'click_id' => 'nullable|exists:clicks,id',
            'impression_id' => 'nullable|exists:impressions,id',
            'postback_url' => 'nullable|url|max:500',
        ]);

        $campaign = Campaign::findOrFail($request->campaign_id);

        // Check if campaign is CPA
        if ($campaign->pricing_model !== 'cpa') {
            return response()->json([
                'success' => false,
                'message' => 'Conversion tracking is only available for CPA campaigns',
            ], 400);
        }

        $conversion = $this->conversionService->trackConversion(
            $campaign,
            $request,
            $request->click_id,
            $request->impression_id,
            $request->conversion_type ?? 'purchase',
            $request->conversion_value
        );

        if (!$conversion) {
            return response()->json([
                'success' => false,
                'message' => 'Conversion not processed (campaign inactive or insufficient balance)',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'conversion_id' => $conversion->conversion_id,
            'message' => 'Conversion tracked successfully',
        ]);
    }

    /**
     * Conversion pixel (1x1 image).
     */
    public function pixel(Request $request, int $campaignId): Response
    {
        $campaign = Campaign::findOrFail($campaignId);

        // Track conversion via pixel
        $conversion = $this->conversionService->trackConversion(
            $campaign,
            $request,
            $request->query('click_id'),
            $request->query('impression_id'),
            $request->query('type', 'purchase'),
            $request->query('value')
        );

        // Return 1x1 transparent pixel
        $pixel = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        
        return response($pixel, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
