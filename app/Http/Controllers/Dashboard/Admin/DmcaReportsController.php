<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\DmcaReport;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DmcaReportsController extends Controller
{
    /**
     * Display the DMCA reports page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Mark all DMCA report notifications as read when visiting this page
        if (Auth::check() && Auth::user()->isAdmin()) {
            Notification::where('notifiable_type', \App\Models\User::class)
                ->where('notifiable_id', Auth::id())
                ->where('category', 'general')
                ->where('type', 'dmca_report_received')
                ->unread()
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        }
        
        $query = DmcaReport::query();
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('copyright_owner', 'like', "%{$search}%")
                  ->orWhere('contact_name', 'like', "%{$search}%")
                  ->orWhere('contact_email', 'like', "%{$search}%")
                  ->orWhere('infringing_url', 'like', "%{$search}%")
                  ->orWhere('original_work', 'like', "%{$search}%");
            });
        }
        
        $reports = $query->latest()->paginate(20);
        
        // Stats
        $stats = [
            'total' => DmcaReport::count(),
            'pending' => DmcaReport::where('status', 'pending')->count(),
            'reviewed' => DmcaReport::where('status', 'reviewed')->count(),
            'resolved' => DmcaReport::where('status', 'resolved')->count(),
            'dismissed' => DmcaReport::where('status', 'dismissed')->count(),
        ];
        
        return view('dashboard.admin.dmca-reports', compact('reports', 'stats'));
    }

    /**
     * Display a single DMCA report.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $report = DmcaReport::findOrFail($id);
        
        return view('dashboard.admin.dmca-report-show', compact('report'));
    }

    /**
     * Update DMCA report status.
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
        
        $report = DmcaReport::findOrFail($id);
        
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
     * Delete a DMCA report.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $report = DmcaReport::findOrFail($id);
        $report->delete();
        
        return redirect()->route('dashboard.admin.dmca-reports')
            ->with('success', 'Report deleted successfully.');
    }
}

