<?php

namespace App\Http\Controllers\Dashboard\Publisher;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SitesController extends Controller
{
    /**
     * Display the sites management page.
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
        
        $query = Website::where('publisher_id', $publisher->id)
            ->with(['adUnits']);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('domain', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }
        
        $websites = $query->latest()->paginate(20);
        
        // Stats - handle both 'approved' and 'verified' status for backward compatibility
        $stats = [
            'total' => Website::where('publisher_id', $publisher->id)->count(),
            'approved' => Website::where('publisher_id', $publisher->id)
                ->whereIn('status', ['approved', 'verified'])->count(),
            'pending' => Website::where('publisher_id', $publisher->id)->where('status', 'pending')->count(),
            'rejected' => Website::where('publisher_id', $publisher->id)->where('status', 'rejected')->count(),
        ];
        
        return view('dashboard.publisher.sites', compact('websites', 'stats'));
    }

    /**
     * Store a newly created website.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $publisher = $user->publisher;

        if (!$publisher) {
            return back()->withErrors(['error' => 'Publisher profile not found.']);
        }

        $request->validate([
            'domain' => [
                'required',
                'string',
                'max:255',
                'regex:/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/i',
                function ($attribute, $value, $fail) use ($publisher) {
                    $exists = Website::where('publisher_id', $publisher->id)
                        ->where('domain', $value)
                        ->exists();
                    if ($exists) {
                        $fail('You have already added this domain.');
                    }
                },
            ],
            'name' => 'required|string|max:255',
            'verification_method' => 'required|in:meta_tag,file_upload,dns',
        ]);

        // Generate verification code
        $verificationCode = Website::generateVerificationCode();

        $website = Website::create([
            'publisher_id' => $publisher->id,
            'domain' => strtolower(trim($request->domain)),
            'name' => $request->name,
            'verification_method' => $request->verification_method,
            'verification_code' => $verificationCode,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Website added successfully. Please verify your domain ownership to get approved.');
    }

    /**
     * Display the specified website.
     *
     * @param  Website  $website
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(Website $website)
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
        
        $website->load(['adUnits', 'publisher']);
        
        return view('dashboard.publisher.sites.show', compact('website'));
    }

    /**
     * Show the form for editing the specified website.
     *
     * @param  Website  $website
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(Website $website)
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
        
        return view('dashboard.publisher.sites.edit', compact('website'));
    }

    /**
     * Update the specified website.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Website  $website
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Website $website)
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
        
        // Only allow editing name if website is pending or rejected
        // Domain and verification method cannot be changed after creation
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        
        $website->update([
            'name' => $request->name,
        ]);
        
        return redirect()->route('dashboard.publisher.sites.show', $website)
            ->with('success', 'Website updated successfully.');
    }

    /**
     * Delete a website.
     *
     * @param  Website  $website
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Website $website)
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

        $website->delete();

        return redirect()->route('dashboard.publisher.sites')
            ->with('success', 'Website deleted successfully.');
    }
}
