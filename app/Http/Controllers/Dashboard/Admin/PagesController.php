<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PagesController extends Controller
{
    /**
     * Display a listing of pages.
     */
    public function index(Request $request)
    {
        $query = Page::with(['creator', 'updater'])->latest();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'published') {
                $query->where('is_published', true)->where('is_active', true);
            } elseif ($request->status === 'draft') {
                $query->where('is_published', false);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $pages = $query->paginate(20);
        return view('dashboard.admin.pages.index', compact('pages'));
    }

    /**
     * Show the form for creating a new page.
     */
    public function create()
    {
        return view('dashboard.admin.pages.create');
    }

    /**
     * Store a newly created page.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:255|unique:pages,slug|alpha_dash',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'is_published' => 'boolean',
        ]);

        $page = Page::create([
            'slug' => $validated['slug'],
            'title' => $validated['title'],
            'content' => $validated['content'],
            'meta_description' => $validated['meta_description'] ?? null,
            'meta_keywords' => $validated['meta_keywords'] ?? null,
            'is_active' => $request->has('is_active'),
            'is_published' => $request->has('is_published'),
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        ActivityLogService::log('page.created', "Page '{$page->title}' was created", $page, [
            'page_id' => $page->id,
            'slug' => $page->slug,
        ]);

        return redirect()->route('dashboard.admin.pages.index')
            ->with('success', 'Page created successfully.');
    }

    /**
     * Display the specified page.
     */
    public function show(Page $page)
    {
        $page->load(['creator', 'updater']);
        return view('dashboard.admin.pages.show', compact('page'));
    }

    /**
     * Show the form for editing the specified page.
     */
    public function edit(Page $page)
    {
        return view('dashboard.admin.pages.edit', compact('page'));
    }

    /**
     * Update the specified page.
     */
    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'slug' => 'required|string|max:255|unique:pages,slug,' . $page->id . '|alpha_dash',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'is_published' => 'nullable|boolean',
        ]);

        $page->update([
            'slug' => $validated['slug'],
            'title' => $validated['title'],
            'content' => $validated['content'],
            'meta_description' => $validated['meta_description'] ?? null,
            'meta_keywords' => $validated['meta_keywords'] ?? null,
            'is_active' => $request->has('is_active'),
            'is_published' => $request->has('is_published'),
            'updated_by' => Auth::id(),
        ]);

        ActivityLogService::log('page.updated', "Page '{$page->title}' was updated", $page, [
            'page_id' => $page->id,
            'slug' => $page->slug,
        ]);

        return redirect()->route('dashboard.admin.pages.index')
            ->with('success', 'Page updated successfully.');
    }

    /**
     * Remove the specified page.
     */
    public function destroy(Page $page)
    {
        $title = $page->title;
        $slug = $page->slug;
        $page->delete();

        ActivityLogService::log('page.deleted', "Page '{$title}' was deleted", null, [
            'slug' => $slug,
        ]);

        return redirect()->route('dashboard.admin.pages.index')
            ->with('success', 'Page deleted successfully.');
    }
}
