<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NotificationService;
use App\Mail\UserApprovedMail;
use App\Mail\UserRejectedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
        $query = User::with(['publisher', 'advertiser'])
            ->where('role', '!=', 'admin'); // Exclude admin users from the list
        
        // Filter by role (but never allow admin role filter)
        if ($request->filled('role') && $request->role !== 'admin') {
            $query->where('role', $request->role);
        }
        
        // Filter by status
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
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $users = $query->latest()->paginate(20);
        
        // Stats (excluding admins from total count)
        $stats = [
            'total' => User::where('role', '!=', 'admin')->count(),
            'publishers' => User::where('role', 'publisher')->count(),
            'advertisers' => User::where('role', 'advertiser')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'active' => User::where('role', '!=', 'admin')->where('is_active', true)->count(),
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
            $this->notificationService->notifyAdvertiserApproval($user, 'approved');
            
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
            $this->notificationService->notifyAdvertiserApproval($user, 'rejected');
            
            return back()->with('success', 'Advertiser rejected.');
        }

        return back()->withErrors(['error' => 'User cannot be rejected.']);
    }

    /**
     * Toggle user active status.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deactivating yourself
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot deactivate your own account.']);
        }

        $user->update(['is_active' => !$user->is_active]);
        
        $status = $user->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "User {$status} successfully.");
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
