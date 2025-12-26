<?php

namespace App\Http\Controllers\Dashboard\Publisher;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Models\Impression;
use App\Models\Click;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    /**
     * Display the statistics page.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $publisher = $user->publisher;
        
        if (!$publisher) {
            return redirect()->route('dashboard.publisher.home')->with('error', 'Publisher profile not found.');
        }
        
        $startDate = $request->start_date ?? now()->subDays(30)->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');
        
        // Overall stats
        $stats = [
            'total_impressions' => Impression::whereHas('adUnit.website', function($query) use ($publisher) {
                $query->where('publisher_id', $publisher->id);
            })->whereBetween('impression_at', [$startDate, $endDate])->count(),
            'total_clicks' => Click::whereHas('adUnit.website', function($query) use ($publisher) {
                $query->where('publisher_id', $publisher->id);
            })->where('is_fraud', false)
            ->whereBetween('clicked_at', [$startDate, $endDate])->count(),
            'total_revenue' => Impression::whereHas('adUnit.website', function($query) use ($publisher) {
                $query->where('publisher_id', $publisher->id);
            })->whereBetween('impression_at', [$startDate, $endDate])->sum('revenue') +
            Click::whereHas('adUnit.website', function($query) use ($publisher) {
                $query->where('publisher_id', $publisher->id);
            })->where('is_fraud', false)
            ->whereBetween('clicked_at', [$startDate, $endDate])->sum('revenue'),
        ];
        
        $stats['ctr'] = $stats['total_impressions'] > 0 ? ($stats['total_clicks'] / $stats['total_impressions']) * 100 : 0;
        $stats['cpc'] = $stats['total_clicks'] > 0 ? ($stats['total_revenue'] / $stats['total_clicks']) : 0;
        $stats['cpm'] = $stats['total_impressions'] > 0 ? (($stats['total_revenue'] / $stats['total_impressions']) * 1000) : 0;
        
        // Stats by website
        $websiteStats = Website::where('publisher_id', $publisher->id)
            ->withCount(['adUnits'])
            ->get()
            ->map(function($website) use ($startDate, $endDate) {
                $impressions = Impression::whereHas('adUnit', function($query) use ($website) {
                    $query->where('website_id', $website->id);
                })->whereBetween('impression_at', [$startDate, $endDate])->count();
                
                $clicks = Click::whereHas('adUnit', function($query) use ($website) {
                    $query->where('website_id', $website->id);
                })->where('is_fraud', false)
                ->whereBetween('clicked_at', [$startDate, $endDate])->count();
                
                $revenue = Impression::whereHas('adUnit', function($query) use ($website) {
                    $query->where('website_id', $website->id);
                })->whereBetween('impression_at', [$startDate, $endDate])->sum('revenue') +
                Click::whereHas('adUnit', function($query) use ($website) {
                    $query->where('website_id', $website->id);
                })->where('is_fraud', false)
                ->whereBetween('clicked_at', [$startDate, $endDate])->sum('revenue');
                
                return [
                    'website' => $website,
                    'impressions' => $impressions,
                    'clicks' => $clicks,
                    'revenue' => $revenue,
                    'ctr' => $impressions > 0 ? ($clicks / $impressions) * 100 : 0,
                ];
            });
        
        // Daily stats for chart
        $dailyStats = Impression::select(
                DB::raw('DATE(impression_at) as date'),
                DB::raw('COUNT(*) as impressions'),
                DB::raw('SUM(revenue) as revenue')
            )
            ->whereHas('adUnit.website', function($query) use ($publisher) {
                $query->where('publisher_id', $publisher->id);
            })
            ->whereBetween('impression_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(impression_at)'))
            ->orderBy('date')
            ->get()
            ->map(function($stat) use ($startDate, $endDate, $publisher) {
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
        
        return view('dashboard.publisher.statistics', compact('stats', 'websiteStats', 'dailyStats', 'startDate', 'endDate'));
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
        $publisher = $user->publisher;
        
        if (!$publisher) {
            return redirect()->route('dashboard.publisher.home')->with('error', 'Publisher profile not found.');
        }
        
        $startDate = $request->start_date ?? now()->subDays(30)->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');
        $websiteId = $request->website_id;
        $adUnitId = $request->ad_unit_id;
        
        // Build filter for publisher's websites/ad units
        $websiteFilter = function($query) use ($publisher, $websiteId) {
            $query->where('publisher_id', $publisher->id);
            if ($websiteId) {
                $query->where('id', $websiteId);
            }
        };
        
        // Build ad unit filter for clicks
        $clickAdUnitFilter = function($query) use ($publisher, $websiteId, $adUnitId) {
            $query->whereHas('website', function($q) use ($publisher, $websiteId) {
                $q->where('publisher_id', $publisher->id);
                if ($websiteId) {
                    $q->where('id', $websiteId);
                }
            });
            if ($adUnitId) {
                $query->where('id', $adUnitId);
            }
        };
        
        // Get geo analytics grouped by country
        $impressionsQuery = Impression::whereHas('adUnit.website', $websiteFilter);
        if ($adUnitId) {
            $impressionsQuery->where('ad_unit_id', $adUnitId);
        }
        
        $geoStats = $impressionsQuery
            ->whereBetween('impression_at', [$startDate, $endDate])
            ->where('is_bot', false)
            ->select(
                'country_code',
                DB::raw('COUNT(*) as impressions'),
                DB::raw('COALESCE(SUM(publisher_earning), 0) as earnings')
            )
            ->groupBy('country_code')
            ->get()
            ->map(function ($stat) use ($startDate, $endDate, $clickAdUnitFilter, $adUnitId) {
                $clicksQuery = Click::whereHas('adUnit', $clickAdUnitFilter);
                if ($adUnitId) {
                    $clicksQuery->where('ad_unit_id', $adUnitId);
                }
                
                $clicks = $clicksQuery
                    ->where('country_code', $stat->country_code)
                    ->whereBetween('clicked_at', [$startDate, $endDate])
                    ->where('is_fraud', false)
                    ->where('is_bot', false)
                    ->count();
                
                $clicksEarningsQuery = Click::whereHas('adUnit', $clickAdUnitFilter);
                if ($adUnitId) {
                    $clicksEarningsQuery->where('ad_unit_id', $adUnitId);
                }
                
                $clicksEarnings = $clicksEarningsQuery
                    ->where('country_code', $stat->country_code)
                    ->whereBetween('clicked_at', [$startDate, $endDate])
                    ->where('is_fraud', false)
                    ->where('is_bot', false)
                    ->sum('publisher_earning');
                
                $impressions = (int)$stat->impressions;
                $totalClicks = (int)$clicks;
                $ctr = $impressions > 0 ? ($totalClicks / $impressions) * 100 : 0;
                
                return [
                    'country_code' => $stat->country_code ?? 'Unknown',
                    'impressions' => $impressions,
                    'clicks' => $totalClicks,
                    'ctr' => round($ctr, 2),
                    'earnings' => round((float)$stat->earnings + (float)$clicksEarnings, 2),
                ];
            })
            ->sortByDesc('impressions')
            ->values();
        
        // Get websites and ad units for filter dropdowns
        $websites = Website::where('publisher_id', $publisher->id)
            ->orderBy('name')
            ->get();
        
        $adUnits = \App\Models\AdUnit::whereHas('website', function($query) use ($publisher, $websiteId) {
                $query->where('publisher_id', $publisher->id);
                if ($websiteId) {
                    $query->where('id', $websiteId);
                }
            })
            ->orderBy('name')
            ->get();
        
        return view('dashboard.publisher.analytics.geo', compact('geoStats', 'startDate', 'endDate', 'websiteId', 'adUnitId', 'websites', 'adUnits'));
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
        $publisher = $user->publisher;
        
        if (!$publisher) {
            return redirect()->route('dashboard.publisher.home')->with('error', 'Publisher profile not found.');
        }
        
        $startDate = $request->start_date ?? now()->subDays(30)->format('Y-m-d');
        $endDate = $request->end_date ?? now()->format('Y-m-d');
        $websiteId = $request->website_id;
        $adUnitId = $request->ad_unit_id;
        $groupBy = $request->group_by ?? 'device'; // device, os, browser
        
        // Build filter for publisher's websites/ad units
        $websiteFilter = function($query) use ($publisher, $websiteId) {
            $query->where('publisher_id', $publisher->id);
            if ($websiteId) {
                $query->where('id', $websiteId);
            }
        };
        
        // Build ad unit filter for clicks
        $clickAdUnitFilter = function($query) use ($publisher, $websiteId, $adUnitId) {
            $query->whereHas('website', function($q) use ($publisher, $websiteId) {
                $q->where('publisher_id', $publisher->id);
                if ($websiteId) {
                    $q->where('id', $websiteId);
                }
            });
            if ($adUnitId) {
                $query->where('id', $adUnitId);
            }
        };
        
        // Determine column name based on group_by
        $column = $groupBy === 'device' ? 'device_type' : $groupBy;
        
        // Get device analytics
        $impressionsQuery = Impression::whereHas('adUnit.website', $websiteFilter);
        if ($adUnitId) {
            $impressionsQuery->where('ad_unit_id', $adUnitId);
        }
        
        $deviceStats = $impressionsQuery
            ->whereBetween('impression_at', [$startDate, $endDate])
            ->where('is_bot', false)
            ->whereNotNull($column)
            ->select(
                "{$column} as group_value",
                DB::raw('COUNT(*) as impressions'),
                DB::raw('COALESCE(SUM(publisher_earning), 0) as earnings')
            )
            ->groupBy($column)
            ->get()
            ->map(function ($stat) use ($startDate, $endDate, $column, $clickAdUnitFilter, $adUnitId) {
                $clicksQuery = Click::whereHas('adUnit', $clickAdUnitFilter);
                if ($adUnitId) {
                    $clicksQuery->where('ad_unit_id', $adUnitId);
                }
                
                $clicks = $clicksQuery
                    ->where($column, $stat->group_value)
                    ->whereBetween('clicked_at', [$startDate, $endDate])
                    ->where('is_fraud', false)
                    ->where('is_bot', false)
                    ->count();
                
                $clicksEarningsQuery = Click::whereHas('adUnit', $clickAdUnitFilter);
                if ($adUnitId) {
                    $clicksEarningsQuery->where('ad_unit_id', $adUnitId);
                }
                
                $clicksEarnings = $clicksEarningsQuery
                    ->where($column, $stat->group_value)
                    ->whereBetween('clicked_at', [$startDate, $endDate])
                    ->where('is_fraud', false)
                    ->where('is_bot', false)
                    ->sum('publisher_earning');
                
                $impressions = (int)$stat->impressions;
                $totalClicks = (int)$clicks;
                $ctr = $impressions > 0 ? ($totalClicks / $impressions) * 100 : 0;
                
                return [
                    'group_value' => $stat->group_value ?? 'Unknown',
                    'impressions' => $impressions,
                    'clicks' => $totalClicks,
                    'ctr' => round($ctr, 2),
                    'earnings' => round((float)$stat->earnings + (float)$clicksEarnings, 2),
                ];
            })
            ->sortByDesc('impressions')
            ->values();
        
        // Get websites and ad units for filter dropdowns
        $websites = Website::where('publisher_id', $publisher->id)
            ->orderBy('name')
            ->get();
        
        $adUnits = \App\Models\AdUnit::whereHas('website', function($query) use ($publisher, $websiteId) {
                $query->where('publisher_id', $publisher->id);
                if ($websiteId) {
                    $query->where('id', $websiteId);
                }
            })
            ->orderBy('name')
            ->get();
        
        return view('dashboard.publisher.analytics.device', compact('deviceStats', 'startDate', 'endDate', 'groupBy', 'websiteId', 'adUnitId', 'websites', 'adUnits'));
    }
}
