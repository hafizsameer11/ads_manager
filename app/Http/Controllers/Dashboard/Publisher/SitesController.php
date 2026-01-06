<?php

namespace App\Http\Controllers\Dashboard\Publisher;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Models\Setting;
use App\Services\WebsiteVerificationService;
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
            'disabled' => Website::where('publisher_id', $publisher->id)->where('status', 'disabled')->count(),
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
                'max:500',
                function ($attribute, $value, $fail) use ($publisher) {
                    // Allow standard domains, localhost, IP addresses, and URLs with ports/paths
                    $pattern = '/^(https?:\/\/)?([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}(\:[0-9]+)?(\/.*)?$|^(https?:\/\/)?(localhost|127\.0\.0\.1|0\.0\.0\.0)(\:[0-9]+)?(\/.*)?$/i';
                    
                    if (!preg_match($pattern, trim($value))) {
                        $fail('Please enter a valid domain, localhost URL, or IP address (e.g., example.com, http://127.0.0.1:5500/index.html, localhost:8000).');
                    }
                },
                function ($attribute, $value, $fail) use ($publisher) {
                    // Normalize domain for duplicate check
                    $normalized = self::normalizeDomainForStorage($value);
                    $exists = Website::where('publisher_id', $publisher->id)
                        ->where('domain', $normalized)
                        ->exists();
                    if ($exists) {
                        $fail('You have already added this domain.');
                    }
                },
            ],
            'name' => 'required|string|max:255',
            'verification_method' => 'required|in:meta_tag,file_upload',
        ]);

        // Generate verification code
        $verificationCode = Website::generateVerificationCode();
        
        // Normalize domain for storage
        $normalizedDomain = self::normalizeDomainForStorage($request->domain);
        
        // Websites always require admin approval
        $websiteData = [
            'publisher_id' => $publisher->id,
            'domain' => $normalizedDomain,
            'name' => $request->name,
            'verification_method' => $request->verification_method,
            'verification_code' => $verificationCode,
            'status' => 'pending', // Always pending - requires admin approval
        ];

        $website = Website::create($websiteData);

        // Notify admin about new website submission
        \App\Services\NotificationService::notifyAdmins(
            'website_added',
            'general',
            'New Website Added',
            "A new website '{$website->name}' ({$website->domain}) has been added by {$publisher->user->name} and is pending approval.",
            ['website_id' => $website->id, 'publisher_id' => $publisher->id, 'domain' => $website->domain]
        );
        
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
     * Verify website ownership.
     *
     * @param  Website  $website
     * @param  WebsiteVerificationService  $verificationService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Website $website, WebsiteVerificationService $verificationService)
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
        
        // Perform verification
        $result = $verificationService->verify($website);
        
        if ($result['verified']) {
            return back()->with('success', $result['message']);
        } else {
            return back()->withErrors(['verification' => $result['message']]);
        }
    }

    /**
     * Get verification file content for download.
     *
     * @param  Website  $website
     * @param  WebsiteVerificationService  $verificationService
     * @return \Illuminate\Http\Response
     */
    public function downloadVerificationFile(Website $website, WebsiteVerificationService $verificationService)
    {
        $user = Auth::user();
        $publisher = $user->publisher;
        
        if (!$publisher) {
            abort(403, 'Unauthorized.');
        }
        
        // Ensure website belongs to publisher
        if ($website->publisher_id !== $publisher->id) {
            abort(403, 'Unauthorized access to this website.');
        }
        
        if ($website->verification_method !== 'file_upload') {
            abort(400, 'This website does not use file upload verification.');
        }
        
        $filename = "ads-network-verification-{$website->verification_code}.html";
        $content = $verificationService->getVerificationFileContent($website);
        
        return response($content, 200)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
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

    /**
     * Normalize domain for storage.
     * Handles URLs, localhost, IP addresses, ports, and paths.
     *
     * @param string $domain
     * @return string
     */
    private static function normalizeDomainForStorage(string $domain): string
    {
        $domain = trim($domain);
        
        // Remove protocol if present
        $domain = preg_replace('#^https?://#', '', $domain);
        
        // Remove trailing slash
        $domain = rtrim($domain, '/');
        
        // For localhost/127.0.0.1 with paths, keep the full path
        // For regular domains, extract just the hostname
        if (preg_match('/^(localhost|127\.0\.0\.1|0\.0\.0\.0)/i', $domain)) {
            // Keep localhost with port and path as-is
            return strtolower($domain);
        } else {
            // For regular domains, extract hostname only (remove path)
            $parsed = parse_url('http://' . $domain);
            $host = $parsed['host'] ?? $domain;
            $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
            return strtolower($host . $port);
        }
    }
}
