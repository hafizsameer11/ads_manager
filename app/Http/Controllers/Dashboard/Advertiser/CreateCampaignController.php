<?php

namespace App\Http\Controllers\Dashboard\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\TargetCountry;
use App\Models\TargetDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreateCampaignController extends Controller
{
    /**
     * Display the create campaign page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        $advertiser = $user->advertiser;
        
        if (!$advertiser) {
            return redirect()->route('dashboard.advertiser.home')->with('error', 'Advertiser profile not found.');
        }
        
        if ($user->is_active !== 1) {
            return redirect()->route('dashboard.advertiser.home')
                ->with('error', 'Your account needs to be approved before creating campaigns.');
        }
        
        // Load enabled target countries and devices from database
        $targetCountries = TargetCountry::enabled()->ordered()->get();
        $targetDevices = TargetDevice::enabled()->ordered()->get();
        
        return view('dashboard.advertiser.create-campaign', compact('advertiser', 'targetCountries', 'targetDevices'));
    }

    /**
     * Store a newly created campaign.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'ad_type' => 'required|in:banner,popup,popunder',
            'target_url' => 'required|url|max:500',
            'pricing_model' => 'required|in:cpm,cpc',
            'bid_amount' => 'required|numeric|min:0.01',
            'budget' => 'required|numeric|min:1',
            'daily_budget' => 'nullable|numeric|min:0.01',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'ad_title' => 'nullable|string|max:255',
            'ad_description' => 'nullable|string|max:1000',
            'ad_image' => 'nullable|url|max:500',
            'target_countries' => 'nullable|array',
            'target_devices' => 'nullable|array',
            'is_vpn_allowed' => 'nullable|boolean',
            'is_proxy_allowed' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        $advertiser = $user->advertiser;

        if (!$advertiser) {
            return back()->withErrors(['error' => 'Advertiser profile not found.']);
        }

        if ($user->is_active !== 1) {
            return back()->withErrors(['error' => 'Your account must be approved to create campaigns.']);
        }

        // Check balance
        if ($advertiser->balance < $request->budget) {
            return back()->withErrors(['error' => 'Insufficient balance. Please deposit funds first.']);
        }

        // Validate that at least one ad content field is provided
        if (empty($request->ad_title) && empty($request->ad_description) && empty($request->ad_image)) {
            return back()->withErrors(['error' => 'Please provide at least one ad content field (title, description, or image).'])->withInput();
        }

        try {
            $campaignService = app(\App\Services\CampaignService::class);
            
            // Build ad_content array from form fields
            $adContent = [];
            if ($request->ad_title) {
                $adContent['title'] = $request->ad_title;
            }
            if ($request->ad_description) {
                $adContent['description'] = $request->ad_description;
            }
            if ($request->ad_image) {
                $adContent['image_url'] = $request->ad_image;
            }
            // If no content provided, use a default
            if (empty($adContent)) {
                $adContent = ['text' => $request->name];
            }
            
            $campaignData = [
                'name' => $request->name,
                'ad_type' => $request->ad_type,
                'target_url' => $request->target_url,
                'ad_content' => $adContent,
                'pricing_model' => $request->pricing_model,
                'bid_amount' => $request->bid_amount,
                'budget' => $request->budget,
                'daily_budget' => $request->daily_budget,
                'start_date' => $request->start_date ?: now(),
                'end_date' => $request->end_date,
            ];

            $targetingData = [
                'countries' => $request->target_countries ?? [],
                'devices' => $request->target_devices ?? [],
                'operating_systems' => [],
                'browsers' => [],
                'is_vpn_allowed' => $request->boolean('is_vpn_allowed', false),
                'is_proxy_allowed' => $request->boolean('is_proxy_allowed', false),
            ];

            $campaign = $campaignService->createCampaign($advertiser, $campaignData, $targetingData);

            return redirect()->route('dashboard.advertiser.campaigns')
                ->with('success', 'Campaign created successfully. ' . ($campaign->approval_status === 'approved' ? 'Campaign is now active.' : 'Waiting for approval.'));
        } catch (\Exception $e) {
            \Log::error('Campaign creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return back()->withErrors(['error' => 'Failed to create campaign: ' . $e->getMessage()])->withInput();
        }
    }
}
