<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Publisher;
use App\Models\Advertiser;
use App\Models\Campaign;
use App\Models\Impression;
use App\Models\Click;
use App\Models\Transaction;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard home page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Total counts
        $totalPublishers = Publisher::count();
        $totalAdvertisers = Advertiser::count();
        $totalUsers = User::count();
        
        // Campaign stats
        $activeCampaigns = Campaign::where('status', 'active')
            ->where('approval_status', 'approved')
            ->count();
        $pendingCampaigns = Campaign::where('approval_status', 'pending')->count();
        
        // Impression & Click stats
        $totalImpressions = Impression::count();
        $totalClicks = Click::where('is_fraud', false)->count();
        $totalCTR = $totalImpressions > 0 ? ($totalClicks / $totalImpressions) * 100 : 0;
        
        // Revenue stats - use stored values for accuracy
        $totalRevenue = Impression::where('is_bot', false)->sum('revenue') + 
            Click::where('is_fraud', false)->where('is_bot', false)->sum('revenue');
        $adminRevenue = Impression::where('is_bot', false)->sum('admin_profit') + 
            Click::where('is_fraud', false)->where('is_bot', false)->sum('admin_profit');
        $publisherPayouts = Impression::where('is_bot', false)->sum('publisher_earning') + 
            Click::where('is_fraud', false)->where('is_bot', false)->sum('publisher_earning');
        
        // Monthly stats
        $monthlyRevenue = Impression::where('is_bot', false)
            ->whereMonth('impression_at', now()->month)
            ->whereYear('impression_at', now()->year)
            ->sum('revenue') + 
            Click::where('is_fraud', false)
            ->where('is_bot', false)
            ->whereMonth('clicked_at', now()->month)
            ->whereYear('clicked_at', now()->year)
            ->sum('revenue');
        
        // Pending withdrawals
        $pendingWithdrawals = Withdrawal::where('status', 'pending')->count();
        $pendingWithdrawalAmount = Withdrawal::where('status', 'pending')->sum('amount');
        
        // Recent transactions
        $recentTransactions = Transaction::with('transactionable')
            ->latest()
            ->limit(10)
            ->get();
        
        // Top campaigns
        $topCampaigns = Campaign::with('advertiser.user')
            ->orderBy('impressions', 'desc')
            ->limit(5)
            ->get();
        
        // Top publishers
        $topPublishers = Publisher::with('user')
            ->orderBy('total_earnings', 'desc')
            ->limit(5)
            ->get();
        
        // Daily stats for chart (last 30 days) - Enhanced with clicks
        $dailyStats = Impression::select(
                DB::raw('DATE(impression_at) as date'),
                DB::raw('COUNT(*) as impressions'),
                DB::raw('SUM(revenue) as revenue')
            )
            ->where('impression_at', '>=', now()->subDays(30))
            ->groupBy(DB::raw('DATE(impression_at)'))
            ->orderBy('date')
            ->get()
            ->map(function($stat) {
                // Get clicks for this date separately
                $clicks = Click::where('is_fraud', false)
                    ->whereDate('clicked_at', $stat->date)
                    ->count();
                
                return [
                    'date' => $stat->date,
                    'impressions' => $stat->impressions,
                    'clicks' => $clicks,
                    'revenue' => $stat->revenue ?? 0,
                ];
            });
        
        // User growth stats (last 12 months)
        $userGrowthStats = User::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
            ->orderBy('month')
            ->get();
        
        // Revenue distribution by type
        $impressionRevenue = Impression::sum('revenue');
        $clickRevenue = Click::where('is_fraud', false)->sum('revenue');
        
        // Campaign status distribution
        $campaignStatusData = [
            'active' => Campaign::where('status', 'active')->where('approval_status', 'approved')->count(),
            'paused' => Campaign::where('status', 'paused')->count(),
            'pending' => Campaign::where('approval_status', 'pending')->count(),
            'rejected' => Campaign::where('approval_status', 'rejected')->count(),
        ];
        
        // Monthly revenue comparison (last 6 months)
        $monthlyRevenueStats = Impression::select(
                DB::raw('DATE_FORMAT(impression_at, "%Y-%m") as month'),
                DB::raw('SUM(revenue) as revenue')
            )
            ->where('impression_at', '>=', now()->subMonths(6))
            ->groupBy(DB::raw('DATE_FORMAT(impression_at, "%Y-%m")'))
            ->orderBy('month')
            ->get()
            ->map(function($stat) {
                $clickRev = Click::where('is_fraud', false)
                    ->whereRaw('DATE_FORMAT(clicked_at, "%Y-%m") = ?', [$stat->month])
                    ->sum('revenue');
                
                return [
                    'month' => $stat->month,
                    'revenue' => ($stat->revenue ?? 0) + $clickRev,
                ];
            });
        
        return view('dashboard.admin.index', compact(
            'totalPublishers',
            'totalAdvertisers',
            'totalUsers',
            'activeCampaigns',
            'pendingCampaigns',
            'totalImpressions',
            'totalClicks',
            'totalCTR',
            'totalRevenue',
            'adminRevenue',
            'publisherPayouts',
            'monthlyRevenue',
            'pendingWithdrawals',
            'pendingWithdrawalAmount',
            'recentTransactions',
            'topCampaigns',
            'topPublishers',
            'dailyStats',
            'userGrowthStats',
            'impressionRevenue',
            'clickRevenue',
            'campaignStatusData',
            'monthlyRevenueStats'
        ));
    }
}
