<?php

namespace App\Http\Controllers\Dashboard\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Impression;
use App\Models\Click;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Display the analytics page.
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
        
        $startDate = $request->start_date ?? now()->subDays(30)->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');
        
        // Overall stats
        $stats = [
            'total_impressions' => Impression::whereHas('campaign', function($query) use ($advertiser) {
                $query->where('advertiser_id', $advertiser->id);
            })->whereBetween('impression_at', [$startDate, $endDate])->count(),
            'total_clicks' => Click::whereHas('campaign', function($query) use ($advertiser) {
                $query->where('advertiser_id', $advertiser->id);
            })->where('is_fraud', false)
            ->whereBetween('clicked_at', [$startDate, $endDate])->count(),
            'total_spend' => Impression::whereHas('campaign', function($query) use ($advertiser) {
                $query->where('advertiser_id', $advertiser->id);
            })->whereBetween('impression_at', [$startDate, $endDate])->sum('revenue') +
            Click::whereHas('campaign', function($query) use ($advertiser) {
                $query->where('advertiser_id', $advertiser->id);
            })->where('is_fraud', false)
            ->whereBetween('clicked_at', [$startDate, $endDate])->sum('revenue'),
        ];
        
        $stats['ctr'] = $stats['total_impressions'] > 0 ? ($stats['total_clicks'] / $stats['total_impressions']) * 100 : 0;
        $stats['cpc'] = $stats['total_clicks'] > 0 ? ($stats['total_spend'] / $stats['total_clicks']) : 0;
        $stats['cpm'] = $stats['total_impressions'] > 0 ? (($stats['total_spend'] / $stats['total_impressions']) * 1000) : 0;
        
        // Campaign performance
        $campaignPerformance = Campaign::where('advertiser_id', $advertiser->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with(['targeting'])
            ->get()
            ->map(function($campaign) use ($startDate, $endDate) {
                $impressions = Impression::where('campaign_id', $campaign->id)
                    ->whereBetween('impression_at', [$startDate, $endDate])->count();
                
                $clicks = Click::where('campaign_id', $campaign->id)
                    ->where('is_fraud', false)
                    ->whereBetween('clicked_at', [$startDate, $endDate])->count();
                
                $spend = Impression::where('campaign_id', $campaign->id)
                    ->whereBetween('impression_at', [$startDate, $endDate])->sum('revenue') +
                Click::where('campaign_id', $campaign->id)
                    ->where('is_fraud', false)
                    ->whereBetween('clicked_at', [$startDate, $endDate])->sum('revenue');
                
                return [
                    'campaign' => $campaign,
                    'impressions' => $impressions,
                    'clicks' => $clicks,
                    'spend' => $spend,
                    'ctr' => $impressions > 0 ? ($clicks / $impressions) * 100 : 0,
                    'cpc' => $clicks > 0 ? ($spend / $clicks) : 0,
                    'cpm' => $impressions > 0 ? (($spend / $impressions) * 1000) : 0,
                ];
            })
            ->sortByDesc('spend')
            ->take(10);
        
        // Daily stats for chart
        $dailyStats = Impression::select(
                DB::raw('DATE(impression_at) as date'),
                DB::raw('COUNT(*) as impressions'),
                DB::raw('SUM(revenue) as spend')
            )
            ->whereHas('campaign', function($query) use ($advertiser) {
                $query->where('advertiser_id', $advertiser->id);
            })
            ->whereBetween('impression_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(impression_at)'))
            ->orderBy('date')
            ->get()
            ->map(function($stat) use ($startDate, $endDate, $advertiser) {
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
        
        return view('dashboard.advertiser.analytics', compact('stats', 'campaignPerformance', 'dailyStats', 'startDate', 'endDate'));
    }

    /**
     * Display geo analytics page.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function geo(Request $request)
    {
        $user = Auth::user();
        $advertiser = $user->advertiser;
        
        if (!$advertiser) {
            return redirect()->route('dashboard.advertiser.home')->with('error', 'Advertiser profile not found.');
        }
        
        $startDate = $request->start_date ?? now()->subDays(30)->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');
        $campaignId = $request->campaign_id;
        
        // Build campaign filter
        $campaignFilter = function($query) use ($advertiser, $campaignId) {
            $query->where('advertiser_id', $advertiser->id);
            if ($campaignId) {
                $query->where('id', $campaignId);
            }
        };
        
        // Get geo analytics grouped by country
        $geoStats = Impression::whereHas('campaign', $campaignFilter)
            ->whereBetween('impression_at', [$startDate, $endDate])
            ->where('is_bot', false)
            ->select(
                'country_code',
                DB::raw('COUNT(*) as impressions'),
                DB::raw('COALESCE(SUM(revenue), 0) as revenue')
            )
            ->groupBy('country_code')
            ->get()
            ->map(function ($stat) use ($startDate, $endDate, $campaignFilter) {
                $clicks = Click::whereHas('campaign', $campaignFilter)
                    ->where('country_code', $stat->country_code)
                    ->whereBetween('clicked_at', [$startDate, $endDate])
                    ->where('is_fraud', false)
                    ->where('is_bot', false)
                    ->count();
                
                $clicksRevenue = Click::whereHas('campaign', $campaignFilter)
                    ->where('country_code', $stat->country_code)
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
        
        // Get campaigns for filter dropdown
        $campaigns = Campaign::where('advertiser_id', $advertiser->id)
            ->orderBy('name')
            ->get();
        
        return view('dashboard.advertiser.analytics.geo', compact('geoStats', 'startDate', 'endDate', 'campaignId', 'campaigns'));
    }

    /**
     * Display device analytics page.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function device(Request $request)
    {
        $user = Auth::user();
        $advertiser = $user->advertiser;
        
        if (!$advertiser) {
            return redirect()->route('dashboard.advertiser.home')->with('error', 'Advertiser profile not found.');
        }
        
        $startDate = $request->start_date ?? now()->subDays(30)->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');
        $campaignId = $request->campaign_id;
        $groupBy = $request->group_by ?? 'device'; // device, os, browser
        
        // Build campaign filter
        $campaignFilter = function($query) use ($advertiser, $campaignId) {
            $query->where('advertiser_id', $advertiser->id);
            if ($campaignId) {
                $query->where('id', $campaignId);
            }
        };
        
        // Determine column name based on group_by
        $column = $groupBy === 'device' ? 'device_type' : $groupBy;
        
        // Get device analytics
        $deviceStats = Impression::whereHas('campaign', $campaignFilter)
            ->whereBetween('impression_at', [$startDate, $endDate])
            ->where('is_bot', false)
            ->whereNotNull($column)
            ->select(
                "{$column} as group_value",
                DB::raw('COUNT(*) as impressions'),
                DB::raw('COALESCE(SUM(revenue), 0) as revenue')
            )
            ->groupBy($column)
            ->get()
            ->map(function ($stat) use ($startDate, $endDate, $column, $campaignFilter) {
                $clicks = Click::whereHas('campaign', $campaignFilter)
                    ->where($column, $stat->group_value)
                    ->whereBetween('clicked_at', [$startDate, $endDate])
                    ->where('is_fraud', false)
                    ->where('is_bot', false)
                    ->count();
                
                $clicksRevenue = Click::whereHas('campaign', $campaignFilter)
                    ->where($column, $stat->group_value)
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
        
        // Get campaigns for filter dropdown
        $campaigns = Campaign::where('advertiser_id', $advertiser->id)
            ->orderBy('name')
            ->get();
        
        return view('dashboard.advertiser.analytics.device', compact('deviceStats', 'startDate', 'endDate', 'groupBy', 'campaignId', 'campaigns'));
    }
}
