<?php

namespace App\Http\Controllers\Dashboard\Publisher;

use App\Http\Controllers\Controller;
use App\Models\AdUnit;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdUnitController extends Controller
{
    /**
     * Display a listing of ad units for a website.
     */
    public function index(Website $website)
    {
        $user = Auth::user();
        $publisher = $user->publisher;
        
        if (!$publisher) {
            return redirect()->route('dashboard.publisher.home')->with('error', 'Publisher profile not found.');
        }
        
        // Ensure website belongs to publisher
        if ($website->publisher_id !== $publisher->id) {
            abort(403, 'Unauthorized access to this website.');
        }
        
        $adUnits = AdUnit::where('website_id', $website->id)
            ->where('publisher_id', $publisher->id)
            ->latest()
            ->paginate(20);
        
        return view('dashboard.publisher.ad-units.index', compact('website', 'adUnits'));
    }

    /**
     * Show the form for creating a new ad unit.
     */
    public function create(Website $website)
    {
        $user = Auth::user();
        $publisher = $user->publisher;
        
        if (!$publisher) {
            return redirect()->route('dashboard.publisher.home')->with('error', 'Publisher profile not found.');
        }
        
        // Ensure website belongs to publisher
        if ($website->publisher_id !== $publisher->id) {
            abort(403, 'Unauthorized access to this website.');
        }
        
        // Only verified and approved websites can create ad units
        if ($website->verification_status !== 'verified') {
            return redirect()->route('dashboard.publisher.sites.show', $website)
                ->with('error', 'Website must be verified before creating ad units. Please complete verification first.');
        }
        
        if (!in_array($website->status, ['approved', 'verified'])) {
            return redirect()->route('dashboard.publisher.sites.show', $website)
                ->with('error', 'Website must be approved before creating ad units.');
        }
        
        return view('dashboard.publisher.ad-units.create', compact('website'));
    }

    /**
     * Store a newly created ad unit.
     */
    public function store(Request $request, Website $website)
    {
        $user = Auth::user();
        $publisher = $user->publisher;
        
        if (!$publisher) {
            return back()->withErrors(['error' => 'Publisher profile not found.']);
        }
        
        // Ensure website belongs to publisher
        if ($website->publisher_id !== $publisher->id) {
            abort(403, 'Unauthorized access to this website.');
        }
        
        // Only verified and approved websites can create ad units - BACKEND VALIDATION
        if ($website->verification_status !== 'verified') {
            return back()->withErrors(['error' => 'Website must be verified before creating ad units. Please complete verification first.']);
        }
        
        if (!in_array($website->status, ['approved', 'verified'])) {
            return back()->withErrors(['error' => 'Website must be approved before creating ad units.']);
        }
        
        // Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'type' => 'required|in:banner,popup',
        ];
        
        // Banner requires size
        if ($request->type === 'banner') {
            $rules['size'] = 'required|string|regex:/^\d+x\d+$/';
        }
        
        // Popup requires frequency
        if ($request->type === 'popup') {
            $rules['frequency'] = 'required|integer|min:1|max:3600';
        }
        
        $validated = $request->validate($rules);
        
        // Parse size for width and height if banner
        $width = null;
        $height = null;
        if ($request->type === 'banner' && isset($validated['size'])) {
            $sizeParts = explode('x', $validated['size']);
            if (count($sizeParts) === 2) {
                $width = (int)trim($sizeParts[0]);
                $height = (int)trim($sizeParts[1]);
            }
        }
        
        $adUnit = AdUnit::create([
            'publisher_id' => $publisher->id,
            'website_id' => $website->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'size' => $validated['size'] ?? null,
            'frequency' => $validated['frequency'] ?? null,
            'width' => $width,
            'height' => $height,
            'status' => 'active',
        ]);
        
        return redirect()->route('dashboard.publisher.ad-units.show', $adUnit)
            ->with('success', 'Ad unit created successfully!');
    }

    /**
     * Display the specified ad unit.
     */
    public function show(AdUnit $adUnit)
    {
        $user = Auth::user();
        $publisher = $user->publisher;
        
        if (!$publisher) {
            return redirect()->route('dashboard.publisher.home')->with('error', 'Publisher profile not found.');
        }
        
        // Ensure ad unit belongs to publisher
        if ($adUnit->publisher_id !== $publisher->id) {
            abort(403, 'Unauthorized access to this ad unit.');
        }
        
        $adUnit->load('website');
        
        // Check if website is approved - BACKEND VALIDATION
        if ($adUnit->website->status !== 'approved') {
            return redirect()->route('dashboard.publisher.sites')
                ->with('error', 'Website must be approved to view ad unit details.');
        }
        
        return view('dashboard.publisher.ad-units.show', compact('adUnit'));
    }

    /**
     * Show the form for editing the specified ad unit.
     */
    public function edit(AdUnit $adUnit)
    {
        $user = Auth::user();
        $publisher = $user->publisher;
        
        if (!$publisher) {
            return redirect()->route('dashboard.publisher.home')->with('error', 'Publisher profile not found.');
        }
        
        // Ensure ad unit belongs to publisher
        if ($adUnit->publisher_id !== $publisher->id) {
            abort(403, 'Unauthorized access to this ad unit.');
        }
        
        $adUnit->load('website');
        
        return view('dashboard.publisher.ad-units.edit', compact('adUnit'));
    }

    /**
     * Update the specified ad unit.
     */
    public function update(Request $request, AdUnit $adUnit)
    {
        $user = Auth::user();
        $publisher = $user->publisher;
        
        if (!$publisher) {
            return back()->withErrors(['error' => 'Publisher profile not found.']);
        }
        
        // Ensure ad unit belongs to publisher
        if ($adUnit->publisher_id !== $publisher->id) {
            abort(403, 'Unauthorized access to this ad unit.');
        }
        
        // Validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,paused',
        ];
        
        // Banner requires size
        if ($adUnit->type === 'banner') {
            $rules['size'] = 'required|string|regex:/^\d+x\d+$/';
        }
        
        // Popup requires frequency
        if ($adUnit->type === 'popup') {
            $rules['frequency'] = 'required|integer|min:1|max:3600';
        }
        
        $validated = $request->validate($rules);
        
        // Parse size for width and height if banner
        $width = $adUnit->width;
        $height = $adUnit->height;
        if ($adUnit->type === 'banner' && isset($validated['size'])) {
            $sizeParts = explode('x', $validated['size']);
            if (count($sizeParts) === 2) {
                $width = (int)trim($sizeParts[0]);
                $height = (int)trim($sizeParts[1]);
            }
        }
        
        $adUnit->update([
            'name' => $validated['name'],
            'size' => $validated['size'] ?? $adUnit->size,
            'frequency' => $validated['frequency'] ?? $adUnit->frequency,
            'width' => $width,
            'height' => $height,
            'status' => $validated['status'],
        ]);
        
        return redirect()->route('dashboard.publisher.ad-units.show', $adUnit)
            ->with('success', 'Ad unit updated successfully!');
    }

    /**
     * Remove the specified ad unit.
     */
    public function destroy(AdUnit $adUnit)
    {
        $user = Auth::user();
        $publisher = $user->publisher;
        
        if (!$publisher) {
            return back()->withErrors(['error' => 'Publisher profile not found.']);
        }
        
        // Ensure ad unit belongs to publisher
        if ($adUnit->publisher_id !== $publisher->id) {
            abort(403, 'Unauthorized access to this ad unit.');
        }
        
        $website = $adUnit->website;
        $adUnit->delete();
        
        return redirect()->route('dashboard.publisher.sites.ad-units.index', $website)
            ->with('success', 'Ad unit deleted successfully!');
    }
}
