<?php

namespace App\Http\Controllers\Dashboard\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Advertiser;
use App\Models\Campaign;
use App\Models\Impression;
use App\Models\Click;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdvertiserController extends Controller
{
    /**
     * Display the advertiser dashboard home page.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $user = Auth::user();
        $advertiser = $user->advertiser;
        
        if (!$advertiser) {
            return redirect()->route('dashboard.advertiser.home')->with('error', 'Advertiser profile not found.');
        }
        
        // Campaign stats
        $activeCampaigns = Campaign::where('advertiser_id', $advertiser->id)
            ->where('status', 'active')
            ->where('approval_status', 'approved')
            ->count();
        $pausedCampaigns = Campaign::where('advertiser_id', $advertiser->id)
            ->where('status', 'paused')
            ->count();
        $pendingCampaigns = Campaign::where('advertiser_id', $advertiser->id)
            ->where('approval_status', 'pending')
            ->count();
        
        // Traffic stats (all time)
        $totalImpressions = Impression::whereHas('campaign', function($query) use ($advertiser) {
            $query->where('advertiser_id', $advertiser->id);
        })->count();
        
        $totalClicks = Click::whereHas('campaign', function($query) use ($advertiser) {
            $query->where('advertiser_id', $advertiser->id);
        })->where('is_fraud', false)->count();
        
        $totalCTR = $totalImpressions > 0 ? ($totalClicks / $totalImpressions) * 100 : 0;
        
        // Traffic stats (this month)
        $monthImpressions = Impression::whereHas('campaign', function($query) use ($advertiser) {
            $query->where('advertiser_id', $advertiser->id);
        })->whereMonth('impression_at', now()->month)
        ->whereYear('impression_at', now()->year)->count();
        
        $monthClicks = Click::whereHas('campaign', function($query) use ($advertiser) {
            $query->where('advertiser_id', $advertiser->id);
        })->where('is_fraud', false)
        ->whereMonth('clicked_at', now()->month)
        ->whereYear('clicked_at', now()->year)->count();
        
        // Budget stats
        $remainingBudget = $advertiser->balance ?? 0;
        $totalSpent = $advertiser->total_spent ?? 0;
        $totalBudget = $remainingBudget + $totalSpent;
        $budgetPercentage = $totalBudget > 0 ? ($totalSpent / $totalBudget) * 100 : 0;
        
        // Recent campaigns
        $recentCampaigns = Campaign::where('advertiser_id', $advertiser->id)
            ->with(['targeting'])
            ->latest()
            ->limit(5)
            ->get();
        
        // Top performing campaigns
        $topCampaigns = Campaign::where('advertiser_id', $advertiser->id)
            ->orderBy('impressions', 'desc')
            ->limit(5)
            ->get();
        
        // Daily stats for chart (last 30 days)
        $dailyStats = Impression::select(
                DB::raw('DATE(impression_at) as date'),
                DB::raw('COUNT(*) as impressions'),
                DB::raw('SUM(revenue) as spend')
            )
            ->whereHas('campaign', function($query) use ($advertiser) {
                $query->where('advertiser_id', $advertiser->id);
            })
            ->where('impression_at', '>=', now()->subDays(30))
            ->groupBy(DB::raw('DATE(impression_at)'))
            ->orderBy('date')
            ->get()
            ->map(function($stat) use ($advertiser) {
                // Get clicks for this date separately
                $clicks = Click::whereHas('campaign', function($query) use ($advertiser) {
                    $query->where('advertiser_id', $advertiser->id);
                })
                ->where('is_fraud', false)
                ->whereDate('clicked_at', $stat->date)
                ->count();
                
                return [
                    'date' => $stat->date,
                    'impressions' => $stat->impressions,
                    'clicks' => $clicks,
                    'spend' => $stat->spend ?? 0,
                ];
            });
        
        return view('dashboard.advertiser.index', compact(
            'advertiser',
            'activeCampaigns',
            'pausedCampaigns',
            'pendingCampaigns',
            'totalImpressions',
            'totalClicks',
            'totalCTR',
            'monthImpressions',
            'monthClicks',
            'remainingBudget',
            'totalSpent',
            'totalBudget',
            'budgetPercentage',
            'recentCampaigns',
            'topCampaigns',
            'dailyStats'
        ));
    }
}
