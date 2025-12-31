<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Notification;
use App\Services\NotificationService;
use App\Mail\UserApprovedMail;
use App\Mail\UserRejectedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display the users management page.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Mark all user category notifications as read when visiting this page
        if (Auth::check() && Auth::user()->isAdmin()) {
            Notification::where('notifiable_type', \App\Models\User::class)
                ->where('notifiable_id', Auth::id())
                ->where('category', 'user')
                ->unread()
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        }
        
        $query = User::with(['publisher', 'advertiser'])
            ->where('role', '!=', 'admin'); // Exclude admin users from the list
        
        // Filter by role (but never allow admin role filter)
        if ($request->filled('role') && $request->role !== 'admin') {
            $query->where('role', $request->role);
        }
        
        // Filter by account status: 1 = Approved, 0 = Rejected, 2 = Pending
        if ($request->filled('status')) {
            if ($request->status === 'approved') {
                $query->where('is_active', 1);
            } elseif ($request->status === 'rejected') {
                $query->where('is_active', 0);
            } elseif ($request->status === 'pending') {
                $query->where('is_active', 2);
            }
        }
        
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $users = $query->latest()->paginate(20);
        
        // Ensure relationships are loaded
        $users->load(['publisher', 'advertiser']);
        
        // Stats (excluding admins from total count)
        $stats = [
            'total' => User::where('role', '!=', 'admin')->count(),
            'publishers' => User::where('role', 'publisher')->count(),
            'advertisers' => User::where('role', 'advertiser')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'approved' => User::where('role', '!=', 'admin')->where('is_active', 1)->count(),
            'rejected' => User::where('role', '!=', 'admin')->where('is_active', 0)->count(),
            'pending' => User::where('role', '!=', 'admin')->where('is_active', 2)->count(),
        ];
        
        return view('dashboard.admin.users', compact('users', 'stats'));
    }

    /**
     * Approve publisher or advertiser.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function approve($id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'publisher' && $user->publisher) {
            $user->publisher->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);
            
            // Send email notification
            Mail::to($user->email)->send(new UserApprovedMail($user));
            
            // Also create in-app notification
            $this->notificationService->notifyPublisherApproval($user, 'approved');
            
            return back()->with('success', 'Publisher approved successfully.');
        } elseif ($user->role === 'advertiser' && $user->advertiser) {
            $user->advertiser->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);
            
            // Send email notification
            Mail::to($user->email)->send(new UserApprovedMail($user));
            
            // Also create in-app notification
            \App\Services\NotificationService::notifyAdvertiserApproval($user, 'approved');
            
            return back()->with('success', 'Advertiser approved successfully.');
        }

        return back()->withErrors(['error' => 'User cannot be approved.']);
    }

    /**
     * Reject publisher or advertiser.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reject($id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'publisher' && $user->publisher) {
            $user->publisher->update(['status' => 'rejected']);
            
            // Send email notification
            Mail::to($user->email)->send(new UserRejectedMail($user));
            
            // Also create in-app notification
            $this->notificationService->notifyPublisherApproval($user, 'rejected');
            
            return back()->with('success', 'Publisher rejected.');
        } elseif ($user->role === 'advertiser' && $user->advertiser) {
            $user->advertiser->update(['status' => 'rejected']);
            
            // Send email notification
            Mail::to($user->email)->send(new UserRejectedMail($user));
            
            // Also create in-app notification
            \App\Services\NotificationService::notifyAdvertiserApproval($user, 'rejected');
            
            return back()->with('success', 'Advertiser rejected.');
        }

        return back()->withErrors(['error' => 'User cannot be rejected.']);
    }

    /**
     * Toggle user account status.
     * Cycles through: Pending (2) -> Approved (1) -> Rejected (0) -> Approved (1)
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent changing your own status
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot change your own account status.']);
        }

        // Cycle through statuses: 2 (Pending) -> 1 (Approved) -> 0 (Rejected) -> 1 (Approved)
        if ($user->is_active == 2) {
            // Pending -> Approved
            $newStatus = 1;
            $statusText = 'approved';
        } elseif ($user->is_active == 1) {
            // Approved -> Rejected
            $newStatus = 0;
            $statusText = 'rejected';
        } else {
            // Rejected -> Approved
            $newStatus = 1;
            $statusText = 'approved';
        }
        
        $user->update(['is_active' => $newStatus]);
        
        return back()->with('success', "User {$statusText} successfully.");
    }

    /**
     * Delete user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        // Prevent deleting admin users
        if ($user->isAdmin()) {
            return back()->withErrors(['error' => 'Admin users cannot be deleted.']);
        }

        $user->delete();
        
        return back()->with('success', 'User deleted successfully.');
    }
}
