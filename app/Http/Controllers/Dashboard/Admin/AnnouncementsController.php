<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementsController extends Controller
{
    /**
     * Display a listing of announcements.
     */
    public function index(Request $request)
    {
        $query = Announcement::with('creator')->latest();

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by target audience
        if ($request->filled('audience')) {
            $query->where('target_audience', $request->audience);
        }

        // Filter by active status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $announcements = $query->paginate(20);

        return view('dashboard.admin.announcements.index', compact('announcements'));
    }

    /**
     * Show the form for creating a new announcement.
     */
    public function create()
    {
        return view('dashboard.admin.announcements.create');
    }

    /**
     * Store a newly created announcement.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:info,success,warning,danger',
            'target_audience' => 'required|in:all,publishers,advertisers,admins',
            'is_active' => 'nullable|in:0,1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);
        
        // Convert checkbox value to boolean (checkbox sends "1" when checked, "0" when unchecked)
        $validated['is_active'] = (bool) ($validated['is_active'] ?? 0);

        $announcement = Announcement::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'type' => $validated['type'],
            'target_audience' => $validated['target_audience'],
            'is_active' => $validated['is_active'],
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'created_by' => Auth::id(),
        ]);

        ActivityLogService::log('announcement.created', "Announcement '{$announcement->title}' was created", $announcement, [
            'announcement_id' => $announcement->id,
            'title' => $announcement->title,
            'target_audience' => $announcement->target_audience,
        ]);

        return redirect()->route('dashboard.admin.announcements.index')
            ->with('success', 'Announcement created successfully.');
    }

    /**
     * Display the specified announcement.
     */
    public function show(Announcement $announcement)
    {
        $announcement->load('creator');
        return view('dashboard.admin.announcements.show', compact('announcement'));
    }

    /**
     * Show the form for editing the specified announcement.
     */
    public function edit(Announcement $announcement)
    {
        return view('dashboard.admin.announcements.edit', compact('announcement'));
    }

    /**
     * Update the specified announcement.
     */
    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:info,success,warning,danger',
            'target_audience' => 'required|in:all,publishers,advertisers,admins',
            'is_active' => 'nullable|in:0,1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);
        
        // Convert checkbox value to boolean (checkbox sends "1" when checked, "0" when unchecked)
        $validated['is_active'] = (bool) ($validated['is_active'] ?? 0);

        $announcement->update([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'type' => $validated['type'],
            'target_audience' => $validated['target_audience'],
            'is_active' => $validated['is_active'],
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
        ]);

        ActivityLogService::log('announcement.updated', "Announcement '{$announcement->title}' was updated", $announcement, [
            'announcement_id' => $announcement->id,
            'title' => $announcement->title,
        ]);

        return redirect()->route('dashboard.admin.announcements.index')
            ->with('success', 'Announcement updated successfully.');
    }

    /**
     * Remove the specified announcement.
     */
    public function destroy(Announcement $announcement)
    {
        $title = $announcement->title;
        $announcement->delete();

        ActivityLogService::log('announcement.deleted', "Announcement '{$title}' was deleted", null, [
            'title' => $title,
        ]);

        return redirect()->route('dashboard.admin.announcements.index')
            ->with('success', 'Announcement deleted successfully.');
    }
}
