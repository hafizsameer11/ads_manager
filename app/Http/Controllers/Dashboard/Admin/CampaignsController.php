<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;

class CampaignsController extends Controller
{
    /**
     * Display the campaigns management page.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Campaign::with(['advertiser.user', 'targeting']);
        
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
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('advertiser.user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        $campaigns = $query->latest()->paginate(20);
        
        // Stats
        $stats = [
            'total' => Campaign::count(),
            'active' => Campaign::where('status', 'active')->where('approval_status', 'approved')->count(),
            'pending' => Campaign::where('approval_status', 'pending')->count(),
            'paused' => Campaign::where('status', 'paused')->count(),
            'total_spent' => Campaign::sum('total_spent'),
        ];
        
        return view('dashboard.admin.campaigns', compact('campaigns', 'stats'));
    }

    /**
     * Approve campaign.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve($id)
    {
        $campaign = Campaign::findOrFail($id);
        
        $campaignService = app(\App\Services\CampaignService::class);
        $campaignService->approveCampaign($campaign);

        return back()->with('success', 'Campaign approved successfully.');
    }

    /**
     * Reject campaign.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);
        
        $campaign = Campaign::findOrFail($id);
        
        $campaignService = app(\App\Services\CampaignService::class);
        $campaignService->rejectCampaign($campaign, $request->rejection_reason);

        return back()->with('success', 'Campaign rejected.');
    }

    /**
     * Pause campaign.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function pause($id)
    {
        $campaign = Campaign::findOrFail($id);
        
        $campaignService = app(\App\Services\CampaignService::class);
        $campaignService->pauseCampaign($campaign);

        return back()->with('success', 'Campaign paused.');
    }

    /**
     * Resume campaign.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resume($id)
    {
        $campaign = Campaign::findOrFail($id);
        
        $campaignService = app(\App\Services\CampaignService::class);
        $campaignService->resumeCampaign($campaign);

        return back()->with('success', 'Campaign resumed.');
    }
}
