<?php

namespace App\Http\Controllers\Dashboard\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
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
        
        if ($advertiser->status !== 'approved') {
            return redirect()->route('dashboard.advertiser.home')
                ->with('error', 'Your account needs to be approved before creating campaigns.');
        }
        
        return view('dashboard.advertiser.create-campaign', compact('advertiser'));
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
            'ad_content' => 'required|string',
            'pricing_model' => 'required|in:cpm,cpc',
            'bid_amount' => 'required|numeric|min:0.01',
            'budget' => 'required|numeric|min:1',
            'start_date' => 'nullable|date|after_or_equal:today',
            'end_date' => 'nullable|date|after:start_date',
            'countries' => 'nullable|array',
            'devices' => 'nullable|array',
            'operating_systems' => 'nullable|array',
            'browsers' => 'nullable|array',
            'is_vpn_allowed' => 'nullable|boolean',
            'is_proxy_allowed' => 'nullable|boolean',
        ]);

        $user = Auth::user();
        $advertiser = $user->advertiser;

        if (!$advertiser) {
            return back()->withErrors(['error' => 'Advertiser profile not found.']);
        }

        if ($advertiser->status !== 'approved') {
            return back()->withErrors(['error' => 'Your account must be approved to create campaigns.']);
        }

        // Check balance
        if ($advertiser->balance < $request->budget) {
            return back()->withErrors(['error' => 'Insufficient balance. Please deposit funds first.']);
        }

        try {
            $campaignService = app(\App\Services\CampaignService::class);
            
            $campaignData = [
                'name' => $request->name,
                'ad_type' => $request->ad_type,
                'target_url' => $request->target_url,
                'ad_content' => $request->ad_content,
                'pricing_model' => $request->pricing_model,
                'bid_amount' => $request->bid_amount,
                'budget' => $request->budget,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ];

            $targetingData = [
                'countries' => $request->countries ?? [],
                'devices' => $request->devices ?? [],
                'operating_systems' => $request->operating_systems ?? [],
                'browsers' => $request->browsers ?? [],
                'is_vpn_allowed' => $request->boolean('is_vpn_allowed', false),
                'is_proxy_allowed' => $request->boolean('is_proxy_allowed', false),
            ];

            $campaign = $campaignService->createCampaign($advertiser, $campaignData, $targetingData);

            return redirect()->route('dashboard.advertiser.campaigns')
                ->with('success', 'Campaign created successfully. ' . ($campaign->approval_status === 'approved' ? 'Campaign is now active.' : 'Waiting for approval.'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create campaign: ' . $e->getMessage()])->withInput();
        }
    }
}
