<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdUnit;
use App\Models\Website;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdUnitsController extends Controller
{
    /**
     * Display a listing of ad units.
     */
    public function index(Request $request)
    {
        $query = AdUnit::with(['publisher.user', 'website']);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Filter by publisher
        if ($request->filled('publisher_id')) {
            $query->where('publisher_id', $request->publisher_id);
        }
        
        // Filter by website
        if ($request->filled('website_id')) {
            $query->where('website_id', $request->website_id);
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('unit_code', 'like', "%{$search}%")
                  ->orWhere('id', $search)
                  ->orWhereHas('publisher.user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('website', function($websiteQuery) use ($search) {
                      $websiteQuery->where('domain', 'like', "%{$search}%")
                                  ->orWhere('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $adUnits = $query->latest()->paginate(20);
        
        // Get publishers and websites for filters
        $publishers = Publisher::with('user')->get();
        $websites = Website::with('publisher.user')->where('status', 'approved')->get();
        
        // Stats
        $stats = [
            'total' => AdUnit::count(),
            'active' => AdUnit::where('status', 'active')->count(),
            'paused' => AdUnit::where('status', 'paused')->count(),
            'banner' => AdUnit::where('type', 'banner')->count(),
            'popup' => AdUnit::where('type', 'popup')->count(),
        ];
        
        return view('dashboard.admin.ad-units.index', compact('adUnits', 'publishers', 'websites', 'stats'));
    }

    /**
     * Show the form for creating a new ad unit.
     */
    public function create()
    {
        $publishers = Publisher::with('user')->get();
        $websites = Website::with('publisher.user')->where('status', 'approved')->get();
        
        return view('dashboard.admin.ad-units.create', compact('publishers', 'websites'));
    }

    /**
     * Store a newly created ad unit.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'publisher_id' => 'required|exists:publishers,id',
            'website_id' => 'required|exists:websites,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:banner,popup',
            'size' => 'required_if:type,banner|nullable|string|regex:/^\d+x\d+$/',
            'frequency' => 'required_if:type,popup|nullable|integer|min:1|max:3600',
            'status' => 'required|in:active,paused',
            'is_anti_adblock' => 'nullable|boolean',
            'cpm_rate' => 'nullable|numeric|min:0',
            'cpc_rate' => 'nullable|numeric|min:0',
        ]);

        // Verify website belongs to publisher
        $website = Website::findOrFail($validated['website_id']);
        if ($website->publisher_id != $validated['publisher_id']) {
            return back()->withErrors(['website_id' => 'The selected website does not belong to the selected publisher.'])->withInput();
        }

        // Verify website is approved
        if ($website->status !== 'approved') {
            return back()->withErrors(['website_id' => 'The selected website must be approved.'])->withInput();
        }

        // Parse size for width and height if banner
        $width = null;
        $height = null;
        if ($validated['type'] === 'banner' && isset($validated['size'])) {
            $sizeParts = explode('x', $validated['size']);
            if (count($sizeParts) === 2) {
                $width = (int)trim($sizeParts[0]);
                $height = (int)trim($sizeParts[1]);
            }
        }

        $adUnit = AdUnit::create([
            'publisher_id' => $validated['publisher_id'],
            'website_id' => $validated['website_id'],
            'name' => $validated['name'],
            'type' => $validated['type'],
            'size' => $validated['size'] ?? null,
            'frequency' => $validated['frequency'] ?? null,
            'width' => $width,
            'height' => $height,
            'status' => $validated['status'],
            'is_anti_adblock' => $request->has('is_anti_adblock') ? true : false,
            'cpm_rate' => $validated['cpm_rate'] ?? 0,
            'cpc_rate' => $validated['cpc_rate'] ?? 0,
        ]);

        return redirect()->route('dashboard.admin.ad-units.show', $adUnit->id)
            ->with('success', 'Ad unit created successfully!');
    }

    /**
     * Display the specified ad unit.
     */
    public function show($id)
    {
        $adUnit = AdUnit::with(['publisher.user', 'website'])->findOrFail($id);
        
        // Get stats
        $stats = [
            'impressions' => $adUnit->impressions()->count(),
            'clicks' => $adUnit->clicks()->count(),
            'total_revenue' => $adUnit->impressions()->sum('revenue') + $adUnit->clicks()->sum('revenue'),
        ];
        
        return view('dashboard.admin.ad-units.show', compact('adUnit', 'stats'));
    }

    /**
     * Show the form for editing the specified ad unit.
     */
    public function edit($id)
    {
        $adUnit = AdUnit::with(['publisher.user', 'website'])->findOrFail($id);
        $publishers = Publisher::with('user')->get();
        $websites = Website::with('publisher.user')->where('status', 'approved')->get();
        
        return view('dashboard.admin.ad-units.edit', compact('adUnit', 'publishers', 'websites'));
    }

    /**
     * Update the specified ad unit.
     */
    public function update(Request $request, $id)
    {
        $adUnit = AdUnit::findOrFail($id);
        
        $validated = $request->validate([
            'publisher_id' => 'required|exists:publishers,id',
            'website_id' => 'required|exists:websites,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:banner,popup',
            'size' => 'required_if:type,banner|nullable|string|regex:/^\d+x\d+$/',
            'frequency' => 'required_if:type,popup|nullable|integer|min:1|max:3600',
            'status' => 'required|in:active,paused',
            'is_anti_adblock' => 'nullable|boolean',
            'cpm_rate' => 'nullable|numeric|min:0',
            'cpc_rate' => 'nullable|numeric|min:0',
        ]);

        // Verify website belongs to publisher
        $website = Website::findOrFail($validated['website_id']);
        if ($website->publisher_id != $validated['publisher_id']) {
            return back()->withErrors(['website_id' => 'The selected website does not belong to the selected publisher.'])->withInput();
        }

        // Parse size for width and height if banner
        $width = $adUnit->width;
        $height = $adUnit->height;
        if ($validated['type'] === 'banner' && isset($validated['size'])) {
            $sizeParts = explode('x', $validated['size']);
            if (count($sizeParts) === 2) {
                $width = (int)trim($sizeParts[0]);
                $height = (int)trim($sizeParts[1]);
            }
        } else if ($validated['type'] === 'popup') {
            $width = null;
            $height = null;
        }

        $adUnit->update([
            'publisher_id' => $validated['publisher_id'],
            'website_id' => $validated['website_id'],
            'name' => $validated['name'],
            'type' => $validated['type'],
            'size' => $validated['size'] ?? null,
            'frequency' => $validated['frequency'] ?? null,
            'width' => $width,
            'height' => $height,
            'status' => $validated['status'],
            'is_anti_adblock' => $request->has('is_anti_adblock') ? true : false,
            'cpm_rate' => $validated['cpm_rate'] ?? $adUnit->cpm_rate,
            'cpc_rate' => $validated['cpc_rate'] ?? $adUnit->cpc_rate,
        ]);

        return redirect()->route('dashboard.admin.ad-units.show', $adUnit->id)
            ->with('success', 'Ad unit updated successfully!');
    }

    /**
     * Remove the specified ad unit.
     */
    public function destroy($id)
    {
        $adUnit = AdUnit::findOrFail($id);
        $adUnit->delete();

        return redirect()->route('dashboard.admin.ad-units')
            ->with('success', 'Ad unit deleted successfully!');
    }
}
