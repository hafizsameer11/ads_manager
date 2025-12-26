<?php

namespace App\Http\Controllers\Dashboard\Publisher;

use App\Http\Controllers\Controller;
use App\Models\Publisher;
use App\Models\Website;
use App\Models\AdUnit;
use App\Models\Impression;
use App\Models\Click;
use App\Models\Transaction;
use App\Models\Referral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PublisherController extends Controller
{
    /**
     * Display the publisher dashboard home page.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        $user = Auth::user();
        $publisher = $user->publisher;
        
        if (!$publisher) {
            return redirect()->route('dashboard.publisher.home')->with('error', 'Publisher profile not found.');
        }
        
        // Earnings stats
        $totalEarnings = $publisher->total_earnings ?? 0;
        $availableBalance = $publisher->balance ?? 0;
        $pendingBalance = $publisher->pending_balance ?? 0;
        $paidBalance = $publisher->paid_balance ?? 0;
        
        // Website & Ad Unit stats
        $totalSites = Website::where('publisher_id', $publisher->id)
            ->whereIn('status', ['approved', 'verified'])
            ->count();
        $totalAdUnits = AdUnit::whereHas('website', function($query) use ($publisher) {
            $query->where('publisher_id', $publisher->id);
        })->count();
        
        // Traffic stats (today)
        $todayImpressions = Impression::whereHas('adUnit.website', function($query) use ($publisher) {
            $query->where('publisher_id', $publisher->id);
        })->whereDate('impression_at', today())->count();
        
        $todayClicks = Click::whereHas('adUnit.website', function($query) use ($publisher) {
            $query->where('publisher_id', $publisher->id);
        })->where('is_fraud', false)
        ->whereDate('clicked_at', today())->count();
        
        // Traffic stats (this month)
        $monthImpressions = Impression::whereHas('adUnit.website', function($query) use ($publisher) {
            $query->where('publisher_id', $publisher->id);
        })->whereMonth('impression_at', now()->month)
        ->whereYear('impression_at', now()->year)->count();
        
        $monthClicks = Click::whereHas('adUnit.website', function($query) use ($publisher) {
            $query->where('publisher_id', $publisher->id);
        })->where('is_fraud', false)
        ->whereMonth('clicked_at', now()->month)
        ->whereYear('clicked_at', now()->year)->count();
        
        $monthCTR = $monthImpressions > 0 ? ($monthClicks / $monthImpressions) * 100 : 0;
        
        // Monthly earnings
        $monthEarnings = Impression::whereHas('adUnit.website', function($query) use ($publisher) {
            $query->where('publisher_id', $publisher->id);
        })->whereMonth('impression_at', now()->month)
        ->whereYear('impression_at', now()->year)->sum('revenue') +
        Click::whereHas('adUnit.website', function($query) use ($publisher) {
            $query->where('publisher_id', $publisher->id);
        })->where('is_fraud', false)
        ->whereMonth('clicked_at', now()->month)
        ->whereYear('clicked_at', now()->year)->sum('revenue');
        
        // Recent websites
        $recentWebsites = Website::where('publisher_id', $publisher->id)
            ->latest()
            ->limit(5)
            ->get();
        
        // Recent earnings transactions
        $recentEarnings = Transaction::where('transactionable_type', \App\Models\Publisher::class)
            ->where('transactionable_id', $publisher->id)
            ->where('type', 'earnings')
            ->latest()
            ->limit(10)
            ->get();
        
        // Referral stats
        $referrals = Referral::where('referrer_id', $user->id)->count();
        $activeReferrals = Referral::where('referrer_id', $user->id)
            ->where('status', 'active')
            ->count();
        
        // Daily stats for chart (last 30 days)
        $dailyStats = Impression::select(
                DB::raw('DATE(impression_at) as date'),
                DB::raw('COUNT(*) as impressions'),
                DB::raw('SUM(revenue) as revenue')
            )
            ->whereHas('adUnit.website', function($query) use ($publisher) {
                $query->where('publisher_id', $publisher->id);
            })
            ->where('impression_at', '>=', now()->subDays(30))
            ->groupBy(DB::raw('DATE(impression_at)'))
            ->orderBy('date')
            ->get()
            ->map(function($stat) use ($publisher) {
                // Get clicks for this date separately
                $clicks = Click::whereHas('adUnit.website', function($query) use ($publisher) {
                    $query->where('publisher_id', $publisher->id);
                })
                ->where('is_fraud', false)
                ->whereDate('clicked_at', $stat->date)
                ->count();
                
                return [
                    'date' => $stat->date,
                    'impressions' => $stat->impressions,
                    'clicks' => $clicks,
                    'revenue' => $stat->revenue ?? 0,
                ];
            });
        
        return view('dashboard.publisher.index', compact(
            'publisher',
            'totalEarnings',
            'availableBalance',
            'pendingBalance',
            'paidBalance',
            'totalSites',
            'totalAdUnits',
            'todayImpressions',
            'todayClicks',
            'monthImpressions',
            'monthClicks',
            'monthCTR',
            'monthEarnings',
            'recentWebsites',
            'recentEarnings',
            'referrals',
            'activeReferrals',
            'dailyStats'
        ));
    }
}
