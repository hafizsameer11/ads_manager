<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Notification;
use App\Services\NotificationService;
use App\Services\ActivityLogService;
use App\Mail\UserApprovedMail;
use App\Mail\UserRejectedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Get all roles except admin (for sub-admin selection)
        $roles = \App\Models\Role::where('slug', '!=', 'admin')->get();
        
        return view('dashboard.admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users|alpha_dash',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:sub-admin,publisher,advertiser',
            'role_id' => 'required_if:role,sub-admin|exists:roles,id',
            'phone' => 'nullable|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role, // Store actual role: 'sub-admin', 'publisher', or 'advertiser'
                'phone' => $request->phone,
                'referral_code' => User::generateReferralCode(),
                'is_active' => 1, // Auto-approve admin-created users
            ]);

            // Handle sub-admin: assign role from dropdown
            if ($request->role === 'sub-admin') {
                $role = \App\Models\Role::findOrFail($request->role_id);
                $user->assignRole($role->slug);
            }

            // Create role-specific profile
            if ($request->role === 'publisher') {
                \App\Models\Publisher::create([
                    'user_id' => $user->id,
                    'status' => 'approved',
                    'tier' => 'tier3',
                    'approved_at' => now(),
                ]);
            } elseif ($request->role === 'advertiser') {
                \App\Models\Advertiser::create([
                    'user_id' => $user->id,
                    'status' => 'approved',
                    'balance' => 0.00,
                    'total_spent' => 0.00,
                    'approved_at' => now(),
                ]);
            }

            // Log activity
            ActivityLogService::log('user.created', "User '{$user->name}' ({$request->role}) was created by admin", $user, [
                'user_name' => $user->name,
                'user_email' => $user->email,
                'user_role' => $request->role,
            ]);

            DB::commit();

            return redirect()->route('dashboard.admin.users')
                ->with('success', ucfirst($request->role) . ' created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create user: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the users management page.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Mark all user category notifications as read when visiting this page
        if (Auth::check() && Auth::user()->hasPermission('manage_users')) {
            Notification::where('notifiable_type', \App\Models\User::class)
                ->where('notifiable_id', Auth::id())
                ->where('category', 'user')
                ->unread()
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        }
        
        // Exclude actual admins from the list, but include sub-admins, publishers, and advertisers
        $query = User::with(['publisher', 'advertiser', 'roles'])
            ->where(function($q) {
                // Include sub-admins, publishers, and advertisers
                $q->where('role', 'sub-admin')
                  ->orWhere('role', 'publisher')
                  ->orWhere('role', 'advertiser');
            })
            // Exclude actual admins (users with admin role but NOT sub-admin)
            ->whereDoesntHave('roles', function($q) {
                $q->where('slug', 'admin');
            });
        
        // Filter by role (but never allow admin role filter)
        if ($request->filled('role') && $request->role !== 'admin') {
            $query->where('role', $request->role);
        }
        
        // Filter by account status: 1 = Approved, 0 = Rejected, 2 = Pending, 3 = Suspended
        if ($request->filled('status')) {
            if ($request->status === 'approved') {
                $query->where('is_active', 1);
            } elseif ($request->status === 'rejected') {
                $query->where('is_active', 0);
            } elseif ($request->status === 'pending') {
                $query->where('is_active', 2);
            } elseif ($request->status === 'suspended') {
                $query->where('is_active', 3);
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
        
        // Stats (excluding users with admin role from total count)
        $adminRole = \App\Models\Role::where('slug', 'admin')->first();
        $stats = [
            'total' => User::whereDoesntHave('roles', function($q) { $q->where('slug', 'admin'); })->count(),
            'publishers' => User::where('role', 'publisher')->count(),
            'advertisers' => User::where('role', 'advertiser')->count(),
            'admins' => $adminRole ? $adminRole->users()->count() : 0,
            'approved' => User::whereDoesntHave('roles', function($q) { $q->where('slug', 'admin'); })->where('is_active', 1)->count(),
            'rejected' => User::whereDoesntHave('roles', function($q) { $q->where('slug', 'admin'); })->where('is_active', 0)->count(),
            'pending' => User::whereDoesntHave('roles', function($q) { $q->where('slug', 'admin'); })->where('is_active', 2)->count(),
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

        // Update account status to approved
        $user->update(['is_active' => 1]);
        
        // Update publisher/advertiser approved_at timestamp if they exist
        if ($user->role === 'publisher' && $user->publisher) {
            $user->publisher->update(['approved_at' => now()]);
            
            // Send email notification
            Mail::to($user->email)->send(new UserApprovedMail($user));
            
            // Also create in-app notification
            $this->notificationService->notifyPublisherApproval($user, 'approved');
            
            // Log activity
            ActivityLogService::logUserApproved($user, Auth::user());
            
            return back()->with('success', 'Publisher approved successfully.');
        } elseif ($user->role === 'advertiser' && $user->advertiser) {
            $user->advertiser->update(['approved_at' => now()]);
            
            // Send email notification
            Mail::to($user->email)->send(new UserApprovedMail($user));
            
            // Also create in-app notification
            \App\Services\NotificationService::notifyAdvertiserApproval($user, 'approved');
            
            // Log activity
            ActivityLogService::logUserApproved($user, Auth::user());
            
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

        // Update account status to rejected
        $user->update(['is_active' => 0]);
        
        if ($user->role === 'publisher' && $user->publisher) {
            // Send email notification
            Mail::to($user->email)->send(new UserRejectedMail($user));
            
            // Also create in-app notification
            $this->notificationService->notifyPublisherApproval($user, 'rejected');
            
            // Log activity
            ActivityLogService::logUserRejected($user, Auth::user());
            
            return back()->with('success', 'Publisher rejected.');
        } elseif ($user->role === 'advertiser' && $user->advertiser) {
            // Send email notification
            Mail::to($user->email)->send(new UserRejectedMail($user));
            
            // Also create in-app notification
            \App\Services\NotificationService::notifyAdvertiserApproval($user, 'rejected');
            
            // Log activity
            ActivityLogService::logUserRejected($user, Auth::user());
            
            return back()->with('success', 'Advertiser rejected.');
        }

        return back()->withErrors(['error' => 'User cannot be rejected.']);
    }

    /**
     * Toggle user account status.
     * Cycles through: Pending (2) -> Approved (1) -> Suspended (3) -> Rejected (0) -> Approved (1)
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

        // Cycle through statuses: 2 (Pending) -> 1 (Approved) -> 3 (Suspended) -> 0 (Rejected) -> 1 (Approved)
        if ($user->is_active == 2) {
            // Pending -> Approved
            $newStatus = 1;
            $statusText = 'approved';
        } elseif ($user->is_active == 1) {
            // Approved -> Suspended
            $newStatus = 3;
            $statusText = 'suspended';
        } elseif ($user->is_active == 3) {
            // Suspended -> Rejected
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

        // Prevent deleting users with admin role
        if ($user->hasRole('admin')) {
            return back()->withErrors(['error' => 'Users with admin role cannot be deleted.']);
        }

        $user->delete();
        
        return back()->with('success', 'User deleted successfully.');
    }

    /**
     * Show user details and edit page (publisher, advertiser, or sub-admin).
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $user = User::with(['publisher', 'advertiser', 'roles'])->findOrFail($id);
        
        // Load publisher stats if publisher
        $publisherStats = null;
        if ($user->isPublisher() && $user->publisher) {
            $publisherStats = [
                'websites_count' => $user->publisher->websites()->count(),
                'total_earnings' => $user->publisher->total_earnings,
                'balance' => $user->publisher->balance,
                'pending_balance' => $user->publisher->pending_balance,
                'paid_balance' => $user->publisher->paid_balance,
            ];
        }
        
        // Load advertiser stats if advertiser
        $advertiserStats = null;
        if ($user->isAdvertiser() && $user->advertiser) {
            $advertiserStats = [
                'campaigns_count' => $user->advertiser->campaigns()->count(),
                'total_spent' => $user->advertiser->total_spent,
                'balance' => $user->advertiser->balance,
            ];
        }
        
        return view('dashboard.admin.users.show', compact('user', 'publisherStats', 'advertiserStats'));
    }

    /**
     * Edit user (show edit form for publisher, advertiser, or sub-admin).
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $user = User::with(['publisher', 'advertiser', 'roles'])->findOrFail($id);
        
        // Allow editing for publishers, advertisers, and sub-admins
        if (($user->isPublisher() && $user->publisher) || 
            ($user->isAdvertiser() && $user->advertiser) || 
            ($user->role === 'sub-admin' || $user->hasRole('sub-admin'))) {
            return view('dashboard.admin.users.edit', compact('user'));
        }
        
        return redirect()->route('dashboard.admin.users')
            ->withErrors(['error' => 'User profile not found.']);
    }

    /**
     * Update user information (publisher, advertiser, or sub-admin).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $user = User::with(['publisher', 'advertiser', 'roles'])->findOrFail($id);
        
        // Handle sub-admin update
        if ($user->role === 'sub-admin' || $user->hasRole('sub-admin')) {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
                'username' => 'nullable|string|max:255|unique:users,username,' . $user->id,
                'phone' => 'nullable|string|max:20',
                'is_active' => 'required|in:0,1,2,3', // 0=rejected, 1=approved, 2=pending, 3=suspended
            ]);

            // Update user information including account status
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'phone' => $request->phone,
                'is_active' => $request->is_active,
            ]);

            return redirect()->route('dashboard.admin.users.show', $user->id)
                ->with('success', 'Sub-admin updated successfully.');
        }
        
        // Handle publisher update
        if ($user->isPublisher() && $user->publisher) {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
                'username' => 'nullable|string|max:255|unique:users,username,' . $user->id,
                'phone' => 'nullable|string|max:20',
                'tier' => 'required|in:tier1,tier2,tier3',
                'is_premium' => 'nullable|boolean',
                'minimum_payout' => 'nullable|numeric|min:0',
                'is_active' => 'required|in:0,1,2,3', // 0=rejected, 1=approved, 2=pending, 3=suspended
                'notes' => 'nullable|string|max:1000',
            ]);

            // Update user information including account status
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'phone' => $request->phone,
                'is_active' => $request->is_active,
            ]);

            // Update publisher information
            $user->publisher->update([
                'tier' => $request->tier,
                'is_premium' => $request->has('is_premium'),
                'minimum_payout' => $request->minimum_payout ?? $user->publisher->minimum_payout,
                'notes' => $request->notes,
            ]);
            
            // Update approved_at if status is being set to approved
            if ($request->is_active == 1 && !$user->publisher->approved_at) {
                $user->publisher->update(['approved_at' => now()]);
            }

            return redirect()->route('dashboard.admin.users.show', $user->id)
                ->with('success', 'Publisher updated successfully.');
        }
        
        // Handle advertiser update
        if ($user->isAdvertiser() && $user->advertiser) {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
                'username' => 'nullable|string|max:255|unique:users,username,' . $user->id,
                'phone' => 'nullable|string|max:20',
                'is_active' => 'required|in:0,1,2,3', // 0=rejected, 1=approved, 2=pending, 3=suspended
                'payment_email' => 'nullable|email|max:255',
                'payment_info' => 'nullable|string|max:2000',
                'notes' => 'nullable|string|max:1000',
            ]);

            // Update user information including account status
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'username' => $request->username,
                'phone' => $request->phone,
                'is_active' => $request->is_active,
            ]);

            // Update advertiser information
            $updateData = [
                'payment_email' => $request->payment_email,
                'notes' => $request->notes,
            ];
            
            // Handle payment_info (can be JSON string)
            if ($request->filled('payment_info')) {
                $paymentInfo = $request->payment_info;
                // Try to decode to validate JSON, if valid store as array, otherwise store as string
                $decoded = json_decode($paymentInfo, true);
                $updateData['payment_info'] = json_last_error() === JSON_ERROR_NONE ? $decoded : $paymentInfo;
            }
            
            $user->advertiser->update($updateData);
            
            // Update approved_at if status is being set to approved
            if ($request->is_active == 1 && !$user->advertiser->approved_at) {
                $user->advertiser->update(['approved_at' => now()]);
            }

            return redirect()->route('dashboard.admin.users.show', $user->id)
                ->with('success', 'Advertiser updated successfully.');
        }

        return redirect()->route('dashboard.admin.users')
            ->withErrors(['error' => 'User profile not found.']);
    }

    /**
     * Suspend user (publisher, advertiser, or sub-admin).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function suspend(Request $request, $id)
    {
        $user = User::with(['publisher', 'advertiser', 'roles'])->findOrFail($id);
        
        // Prevent suspending admin
        if ($user->hasRole('admin') && !$user->hasRole('sub-admin')) {
            return back()->withErrors(['error' => 'Admin users cannot be suspended.']);
        }
        
        if (!$user->isPublisher() && !$user->isAdvertiser() && $user->role !== 'sub-admin' && !$user->hasRole('sub-admin')) {
            return back()->withErrors(['error' => 'User must be a publisher, advertiser, or sub-admin.']);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        // Update account status to suspended (3 = suspended)
        $user->update(['is_active' => 3]);

        $userType = '';
        
        // Handle sub-admin
        if ($user->role === 'sub-admin' || $user->hasRole('sub-admin')) {
            $userType = 'sub-admin';
            // Sub-admins don't have publisher/advertiser profiles, so we just update the user status
        }
        
        // Handle publisher
        if ($user->isPublisher() && $user->publisher) {
            $userType = 'publisher';
            $notes = $user->publisher->notes ?? '';
            if ($request->reason) {
                $notes .= ($notes ? "\n\n" : "") . "[Suspended " . now()->format('Y-m-d H:i') . "] " . $request->reason;
                $user->publisher->update(['notes' => $notes]);
            }
            $this->notificationService->notifyPublisherApproval($user, 'suspended', $request->reason);
        }
        
        // Handle advertiser
        if ($user->isAdvertiser() && $user->advertiser) {
            $userType = 'advertiser';
            $notes = $user->advertiser->notes ?? '';
            if ($request->reason) {
                $notes .= ($notes ? "\n\n" : "") . "[Suspended " . now()->format('Y-m-d H:i') . "] " . $request->reason;
                $user->advertiser->update(['notes' => $notes]);
            }
            // Use notifyAdvertiserApproval - we'll need to update it to support suspended status
            NotificationService::notifyAdvertiserApproval($user, 'suspended', $request->reason);
        }

        // Log activity
        ActivityLogService::logUserSuspended($user, Auth::user(), $request->reason);

        return back()->with('success', ucfirst($userType) . ' suspended successfully.');
    }

    /**
     * Block user (publisher, advertiser, or sub-admin).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function block(Request $request, $id)
    {
        $user = User::with(['publisher', 'advertiser', 'roles'])->findOrFail($id);
        
        // Prevent blocking admin
        if ($user->hasRole('admin') && !$user->hasRole('sub-admin')) {
            return back()->withErrors(['error' => 'Admin users cannot be blocked.']);
        }
        
        if (!$user->isPublisher() && !$user->isAdvertiser() && $user->role !== 'sub-admin' && !$user->hasRole('sub-admin')) {
            return back()->withErrors(['error' => 'User must be a publisher, advertiser, or sub-admin.']);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        // Update account status to rejected
        $user->update(['is_active' => 0]);

        $userType = '';
        
        // Handle publisher
        if ($user->isPublisher() && $user->publisher) {
            $userType = 'publisher';
            $notes = $user->publisher->notes ?? '';
            if ($request->reason) {
                $notes .= ($notes ? "\n\n" : "") . "[Blocked " . now()->format('Y-m-d H:i') . "] " . $request->reason;
                $user->publisher->update(['notes' => $notes]);
            }
            $this->notificationService->notifyPublisherApproval($user, 'rejected', $request->reason);
        }
        
        // Handle advertiser
        if ($user->isAdvertiser() && $user->advertiser) {
            $userType = 'advertiser';
            $notes = $user->advertiser->notes ?? '';
            if ($request->reason) {
                $notes .= ($notes ? "\n\n" : "") . "[Blocked " . now()->format('Y-m-d H:i') . "] " . $request->reason;
                $user->advertiser->update(['notes' => $notes]);
            }
            NotificationService::notifyAdvertiserApproval($user, 'rejected', $request->reason);
        }

        // Log activity (blocked is treated as rejected)
        ActivityLogService::logUserRejected($user, Auth::user(), $request->reason);

        return back()->with('success', ucfirst($userType) . ' blocked successfully.');
    }

    /**
     * Show publisher referrals.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function referrals($id)
    {
        $user = User::with('publisher')->findOrFail($id);
        
        if (!$user->isPublisher()) {
            return redirect()->route('dashboard.admin.users')
                ->withErrors(['error' => 'User is not a publisher.']);
        }

        // Get referrals where this user is the referrer
        $referrals = \App\Models\Referral::with(['referred.publisher', 'referred.advertiser'])
            ->where('referrer_id', $user->id)
            ->latest()
            ->paginate(20);

        // Get referral statistics
        $totalEarnings = \App\Models\Referral::where('referrer_id', $user->id)->sum('total_earnings') ?? 0;
        $paidEarnings = \App\Models\Referral::where('referrer_id', $user->id)->sum('paid_earnings') ?? 0;
        $pendingEarnings = $totalEarnings - $paidEarnings;
        
        $referralStats = [
            'total_referrals' => \App\Models\Referral::where('referrer_id', $user->id)->count(),
            'total_earnings' => $totalEarnings,
            'paid_earnings' => $paidEarnings,
            'pending_earnings' => max(0, $pendingEarnings), // Ensure non-negative
        ];

        return view('dashboard.admin.users.referrals', compact('user', 'referrals', 'referralStats'));
    }
}
