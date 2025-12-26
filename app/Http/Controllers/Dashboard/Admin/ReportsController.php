<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\Publisher;
use App\Models\Campaign;
use App\Models\Impression;
use App\Models\Click;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
    /**
     * Display the reports page.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $startDate = $request->start_date ?? now()->subDays(30)->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');
        
        // Revenue reports - use stored values for accuracy
        $totalRevenue = Impression::whereBetween('impression_at', [$startDate, $endDate])
            ->where('is_bot', false)
            ->sum('revenue') +
            Click::where('is_fraud', false)
            ->where('is_bot', false)
            ->whereBetween('clicked_at', [$startDate, $endDate])
            ->sum('revenue');
        
        $adminProfit = Impression::whereBetween('impression_at', [$startDate, $endDate])
            ->where('is_bot', false)
            ->sum('admin_profit') +
            Click::where('is_fraud', false)
            ->where('is_bot', false)
            ->whereBetween('clicked_at', [$startDate, $endDate])
            ->sum('admin_profit');
        
        $publisherEarnings = Impression::whereBetween('impression_at', [$startDate, $endDate])
            ->where('is_bot', false)
            ->sum('publisher_earning') +
            Click::where('is_fraud', false)
            ->where('is_bot', false)
            ->whereBetween('clicked_at', [$startDate, $endDate])
            ->sum('publisher_earning');
        
        $revenueData = [
            'total' => round((float)$totalRevenue, 2),
            'admin_share' => round((float)$adminProfit, 2),
            'publisher_share' => round((float)$publisherEarnings, 2),
        ];
        
        // Performance metrics
        $impressions = Impression::whereBetween('impression_at', [$startDate, $endDate])->count();
        $clicks = Click::where('is_fraud', false)->whereBetween('clicked_at', [$startDate, $endDate])->count();
        $ctr = $impressions > 0 ? ($clicks / $impressions) * 100 : 0;
        
        // Publisher performance
        $publisherPerformance = Publisher::with('user')
            ->orderBy('total_earnings', 'desc')
            ->limit(10)
            ->get();
        
        // Campaign performance
        $campaignPerformance = Campaign::with('advertiser.user')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('impressions', 'desc')
            ->limit(10)
            ->get();
        
        // Daily stats for chart
        $dailyStats = Impression::select(
                DB::raw('DATE(impression_at) as date'),
                DB::raw('COUNT(*) as impressions'),
                DB::raw('SUM(revenue) as revenue')
            )
            ->whereBetween('impression_at', [$startDate, $endDate])
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
        
        return view('dashboard.admin.reports', compact(
            'startDate',
            'endDate',
            'revenueData',
            'impressions',
            'clicks',
            'ctr',
            'publisherPerformance',
            'campaignPerformance',
            'dailyStats'
        ));
    }

    /**
     * Display geo analytics page.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function geo(Request $request)
    {
        $startDate = $request->start_date ?? now()->subDays(30)->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');
        
        // Build base query for impressions
        $impressionsQuery = Impression::whereBetween('impression_at', [$startDate, $endDate])
            ->where('is_bot', false);
        
        // Build base query for clicks
        $clicksQuery = Click::whereBetween('clicked_at', [$startDate, $endDate])
            ->where('is_fraud', false)
            ->where('is_bot', false);
        
        // Get geo analytics grouped by country
        $geoStats = DB::table('impressions')
            ->select(
                'impressions.country_code',
                DB::raw('COUNT(DISTINCT impressions.id) as impressions'),
                DB::raw('COALESCE(SUM(impressions.revenue), 0) as revenue')
            )
            ->whereBetween('impressions.impression_at', [$startDate, $endDate])
            ->where('impressions.is_bot', false)
            ->groupBy('impressions.country_code')
            ->get()
            ->map(function ($stat) use ($startDate, $endDate) {
                $clicks = Click::where('country_code', $stat->country_code)
                    ->whereBetween('clicked_at', [$startDate, $endDate])
                    ->where('is_fraud', false)
                    ->where('is_bot', false)
                    ->count();
                
                $clicksRevenue = Click::where('country_code', $stat->country_code)
                    ->whereBetween('clicked_at', [$startDate, $endDate])
                    ->where('is_fraud', false)
                    ->where('is_bot', false)
                    ->sum('revenue');
                
                $impressions = (int)$stat->impressions;
                $totalClicks = (int)$clicks;
                $ctr = $impressions > 0 ? ($totalClicks / $impressions) * 100 : 0;
                
                return [
                    'country_code' => $stat->country_code ?? 'Unknown',
                    'impressions' => $impressions,
                    'clicks' => $totalClicks,
                    'ctr' => round($ctr, 2),
                    'spend' => round((float)$stat->revenue + (float)$clicksRevenue, 2),
                ];
            })
            ->sortByDesc('impressions')
            ->values();
        
        return view('dashboard.admin.analytics.geo', compact('geoStats', 'startDate', 'endDate'));
    }

    /**
     * Display device analytics page.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function device(Request $request)
    {
        $startDate = $request->start_date ?? now()->subDays(30)->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');
        $groupBy = $request->group_by ?? 'device'; // device, os, browser
        
        // Determine column name based on group_by
        $column = $groupBy === 'device' ? 'device_type' : $groupBy;
        
        // Get device analytics
        $deviceStats = Impression::whereBetween('impression_at', [$startDate, $endDate])
            ->where('is_bot', false)
            ->whereNotNull($column)
            ->select(
                "{$column} as group_value",
                DB::raw('COUNT(*) as impressions'),
                DB::raw('COALESCE(SUM(revenue), 0) as revenue')
            )
            ->groupBy($column)
            ->get()
            ->map(function ($stat) use ($startDate, $endDate, $column) {
                $clicks = Click::where($column, $stat->group_value)
                    ->whereBetween('clicked_at', [$startDate, $endDate])
                    ->where('is_fraud', false)
                    ->where('is_bot', false)
                    ->count();
                
                $clicksRevenue = Click::where($column, $stat->group_value)
                    ->whereBetween('clicked_at', [$startDate, $endDate])
                    ->where('is_fraud', false)
                    ->where('is_bot', false)
                    ->sum('revenue');
                
                $impressions = (int)$stat->impressions;
                $totalClicks = (int)$clicks;
                $ctr = $impressions > 0 ? ($totalClicks / $impressions) * 100 : 0;
                
                return [
                    'group_value' => $stat->group_value ?? 'Unknown',
                    'impressions' => $impressions,
                    'clicks' => $totalClicks,
                    'ctr' => round($ctr, 2),
                    'spend' => round((float)$stat->revenue + (float)$clicksRevenue, 2),
                ];
            })
            ->sortByDesc('impressions')
            ->values();
        
        return view('dashboard.admin.analytics.device', compact('deviceStats', 'startDate', 'endDate', 'groupBy'));
    }
}
