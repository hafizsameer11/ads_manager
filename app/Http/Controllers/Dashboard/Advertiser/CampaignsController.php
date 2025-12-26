<?php

namespace App\Http\Controllers\Dashboard\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampaignsController extends Controller
{
    /**
     * Display the campaigns page.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $advertiser = $user->advertiser;
        
        if (!$advertiser) {
            return redirect()->route('dashboard.advertiser.home')->with('error', 'Advertiser profile not found.');
        }
        
        $query = Campaign::where('advertiser_id', $advertiser->id)
            ->with(['targeting']);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by approval status
        if ($request->filled('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }
        
        // Filter by ad type
        if ($request->filled('ad_type')) {
            $query->where('ad_type', $request->ad_type);
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }
        
        $campaigns = $query->latest()->paginate(20);
        
        // Stats
        $stats = [
            'total' => Campaign::where('advertiser_id', $advertiser->id)->count(),
            'active' => Campaign::where('advertiser_id', $advertiser->id)
                ->where('status', 'active')
                ->where('approval_status', 'approved')
                ->count(),
            'paused' => Campaign::where('advertiser_id', $advertiser->id)
                ->where('status', 'paused')
                ->count(),
            'pending' => Campaign::where('advertiser_id', $advertiser->id)
                ->where('approval_status', 'pending')
                ->count(),
            'total_spent' => Campaign::where('advertiser_id', $advertiser->id)->sum('total_spent'),
        ];
        
        return view('dashboard.advertiser.campaigns', compact('campaigns', 'stats'));
    }

    /**
     * Pause campaign.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function pause($id)
    {
        $user = Auth::user();
        $advertiser = $user->advertiser;
        
        if (!$advertiser) {
            return back()->withErrors(['error' => 'Advertiser profile not found.']);
        }
        
        $campaign = Campaign::where('advertiser_id', $advertiser->id)->findOrFail($id);
        
        $campaignService = app(\App\Services\CampaignService::class);
        $campaignService->pauseCampaign($campaign);

        return back()->with('success', 'Campaign paused successfully.');
    }

    /**
     * Resume campaign.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resume($id)
    {
        $user = Auth::user();
        $advertiser = $user->advertiser;
        
        if (!$advertiser) {
            return back()->withErrors(['error' => 'Advertiser profile not found.']);
        }
        
        $campaign = Campaign::where('advertiser_id', $advertiser->id)->findOrFail($id);
        
        // Check if campaign is stopped (cannot resume)
        if ($campaign->status === 'stopped') {
            return back()->withErrors(['error' => 'Cannot resume a stopped campaign.']);
        }
        
        // Check if campaign is approved
        if ($campaign->approval_status !== 'approved') {
            return back()->withErrors(['error' => 'Cannot resume campaign. Campaign must be approved by admin first.']);
        }
        
        // Check if campaign has budget
        if ($campaign->budget <= $campaign->total_spent) {
            return back()->withErrors(['error' => 'Cannot resume campaign. Budget has been exhausted.']);
        }
        
        // Check advertiser balance
        if ($advertiser->balance <= 0) {
            return back()->withErrors(['error' => 'Cannot resume campaign. Insufficient balance. Please deposit funds first.']);
        }
        
        $campaignService = app(\App\Services\CampaignService::class);
        $result = $campaignService->resumeCampaign($campaign);
        
        if (!$result) {
            return back()->withErrors(['error' => 'Cannot resume campaign. Please check campaign dates, budget, and balance.']);
        }

        return back()->with('success', 'Campaign resumed successfully.');
    }

    /**
     * Stop campaign (permanent).
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function stop($id)
    {
        $user = Auth::user();
        $advertiser = $user->advertiser;
        
        if (!$advertiser) {
            return back()->withErrors(['error' => 'Advertiser profile not found.']);
        }
        
        $campaign = Campaign::where('advertiser_id', $advertiser->id)->findOrFail($id);
        
        $campaignService = app(\App\Services\CampaignService::class);
        $campaignService->stopCampaign($campaign);

        return back()->with('success', 'Campaign stopped permanently.');
    }
}
