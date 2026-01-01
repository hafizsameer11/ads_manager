<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\AbuseReport;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbuseReportsController extends Controller
{
    /**
     * Display the abuse reports page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Mark all abuse report notifications as read when visiting this page
        if (Auth::check() && Auth::user()->isAdmin()) {
            Notification::where('notifiable_type', \App\Models\User::class)
                ->where('notifiable_id', Auth::id())
                ->where('category', 'general')
                ->where('type', 'abuse_report_received')
                ->unread()
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        }
        
        $query = AbuseReport::query();
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%");
            });
        }
        
        $reports = $query->latest()->paginate(20);
        
        // Stats
        $stats = [
            'total' => AbuseReport::count(),
            'pending' => AbuseReport::where('status', 'pending')->count(),
            'reviewed' => AbuseReport::where('status', 'reviewed')->count(),
            'resolved' => AbuseReport::where('status', 'resolved')->count(),
            'dismissed' => AbuseReport::where('status', 'dismissed')->count(),
        ];
        
        return view('dashboard.admin.abuse-reports', compact('reports', 'stats'));
    }

    /**
     * Display a single abuse report.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $report = AbuseReport::findOrFail($id);
        
        return view('dashboard.admin.abuse-report-show', compact('report'));
    }

    /**
     * Update abuse report status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,reviewed,resolved,dismissed',
            'admin_notes' => 'nullable|string|max:1000',
        ]);
        
        $report = AbuseReport::findOrFail($id);
        
        $method = 'markAs' . ucfirst($request->status === 'pending' ? 'Reviewed' : $request->status);
        if (method_exists($report, $method)) {
            $report->$method($request->admin_notes);
        } else {
            $report->update([
                'status' => $request->status,
                'admin_notes' => $request->admin_notes,
                'reviewed_at' => now(),
            ]);
        }
        
        return back()->with('success', 'Report status updated successfully.');
    }

    /**
     * Delete an abuse report.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $report = AbuseReport::findOrFail($id);
        $report->delete();
        
        return redirect()->route('dashboard.admin.abuse-reports')
            ->with('success', 'Report deleted successfully.');
    }
}

