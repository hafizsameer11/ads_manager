<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class WebsitesController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display the websites management page.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $query = Website::with(['publisher.user', 'adUnits']);
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by publisher
        if ($request->filled('publisher_id')) {
            $query->where('publisher_id', $request->publisher_id);
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('domain', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhereHas('publisher.user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        // Order by pending first, then by latest
        $websites = $query->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
                          ->latest()
                          ->paginate(20);
        
        // Stats
        $stats = [
            'total' => Website::count(),
            'approved' => Website::where('status', 'approved')->count(),
            'pending' => Website::where('status', 'pending')->count(),
            'rejected' => Website::where('status', 'rejected')->count(),
            'disabled' => Website::where('status', 'disabled')->count(),
        ];
        
        return view('dashboard.admin.websites', compact('websites', 'stats'));
    }

    /**
     * Display the specified website.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $website = Website::with(['publisher.user', 'adUnits'])->findOrFail($id);
        
        return view('dashboard.admin.websites.show', compact('website'));
    }

    /**
     * Approve website.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve($id)
    {
        $website = Website::with('publisher.user')->findOrFail($id);
        
        $website->update([
            'status' => 'approved',
            'verified_at' => now(),
            'approved_at' => now(),
            'rejection_reason' => null,
            'rejected_at' => null,
            'admin_note' => null,
        ]);
        
        // Send notification to publisher
        if ($website->publisher && $website->publisher->user) {
            $this->notificationService->create(
                $website->publisher->user,
                'website_approved',
                'Website Approved',
                "Your website '{$website->domain}' has been approved!"
            );
        }

        return back()->with('success', 'Website approved successfully.');
    }

    /**
     * Reject website.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject(Request $request, $id)
    {
        $website = Website::with('publisher.user')->findOrFail($id);
        
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
            'admin_note' => 'nullable|string|max:1000',
        ]);
        
        $website->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'rejected_at' => now(),
            'admin_note' => $request->admin_note,
        ]);
        
        // Disable all ad units for this website
        $website->adUnits()->update(['status' => 'paused']);
        
        // Send notification to publisher
        if ($website->publisher && $website->publisher->user) {
            $this->notificationService->create(
                $website->publisher->user,
                'website_rejected',
                'Website Rejected',
                "Your website '{$website->domain}' has been rejected. Reason: {$request->rejection_reason}"
            );
        }

        return back()->with('success', 'Website rejected.');
    }

    /**
     * Disable website.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function disable(Request $request, $id)
    {
        $website = Website::with('publisher.user')->findOrFail($id);
        
        $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);
        
        $website->update([
            'status' => 'disabled',
            'admin_note' => $request->admin_note,
        ]);
        
        // Disable all ad units for this website
        $website->adUnits()->update(['status' => 'paused']);
        
        // Send notification to publisher
        if ($website->publisher && $website->publisher->user) {
            $this->notificationService->create(
                $website->publisher->user,
                'website_disabled',
                'Website Disabled',
                "Your website '{$website->domain}' has been disabled by admin."
            );
        }

        return back()->with('success', 'Website disabled.');
    }

    /**
     * Enable website (re-approve a disabled or rejected website).
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function enable($id)
    {
        $website = Website::with('publisher.user')->findOrFail($id);
        
        $website->update([
            'status' => 'approved',
            'approved_at' => now(),
            'verified_at' => now(),
            'rejection_reason' => null,
            'rejected_at' => null,
        ]);
        
        // Send notification to publisher
        if ($website->publisher && $website->publisher->user) {
            $this->notificationService->create(
                $website->publisher->user,
                'website_approved',
                'Website Enabled',
                "Your website '{$website->domain}' has been enabled and approved!"
            );
        }

        return back()->with('success', 'Website enabled and approved.');
    }

    /**
     * Suspend website (change status to rejected temporarily).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function suspend(Request $request, $id)
    {
        $website = Website::with('publisher.user')->findOrFail($id);
        
        $website->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason ?? 'Website suspended by admin',
            'rejected_at' => now(),
        ]);
        
        // Disable all ad units for this website
        $website->adUnits()->update(['status' => 'paused']);
        
        // Send notification to publisher
        if ($website->publisher && $website->publisher->user) {
            $this->notificationService->create(
                $website->publisher->user,
                'website_suspended',
                'Website Suspended',
                "Your website '{$website->domain}' has been suspended."
            );
        }

        return back()->with('success', 'Website suspended.');
    }
}

