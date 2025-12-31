<?php

namespace App\Http\Controllers\Dashboard\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationsController extends Controller
{
    /**
     * Get recent notifications for the bell dropdown (max 10).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recent()
    {
        $user = Auth::user();
        
        // Only show notifications from admin actions
        $adminActionTypes = [
            'deposit_approved',
            'deposit_rejected',
            'campaign_approved',
            'campaign_rejected',
            'advertiser_approved',
            'advertiser_rejected',
        ];
        
        $notifications = Notification::where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', $user->id)
            ->whereIn('type', $adminActionTypes)
            ->recent(10)
            ->get();

        $unreadCount = Notification::where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', $user->id)
            ->whereIn('type', $adminActionTypes)
            ->unread()
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Display all notifications page.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Only mark admin action notifications as read
        $adminActionTypes = [
            'deposit_approved',
            'deposit_rejected',
            'campaign_approved',
            'campaign_rejected',
            'advertiser_approved',
            'advertiser_rejected',
        ];
        
        // If coming from "View All Notifications" link, mark all as read
        if ($request->has('mark_all_read')) {
            Notification::where('notifiable_type', \App\Models\User::class)
                ->where('notifiable_id', $user->id)
                ->whereIn('type', $adminActionTypes)
                ->unread()
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        }
        
        // Only show notifications from admin actions (deposit approval/rejection, campaign approval/rejection, advertiser approval/rejection)
        $adminActionTypes = [
            'deposit_approved',
            'deposit_rejected',
            'campaign_approved',
            'campaign_rejected',
            'advertiser_approved',
            'advertiser_rejected',
        ];
        
        $query = Notification::where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', $user->id)
            ->whereIn('type', $adminActionTypes);

        // Filter by category
        if ($request->filled('category')) {
            $query->category($request->category);
        }

        // Filter by read status
        if ($request->filled('status')) {
            if ($request->status === 'read') {
                $query->read();
            } elseif ($request->status === 'unread') {
                $query->unread();
            }
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);

        // Only count notifications from admin actions
        $adminActionTypes = [
            'deposit_approved',
            'deposit_rejected',
            'campaign_approved',
            'campaign_rejected',
            'advertiser_approved',
            'advertiser_rejected',
        ];
        
        $baseQuery = Notification::where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', $user->id)
            ->whereIn('type', $adminActionTypes);

        // Get notification counts by category
        $categoryCounts = (clone $baseQuery)
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category');

        $unreadCount = (clone $baseQuery)->unread()->count();
        $readCount = (clone $baseQuery)->read()->count();
        $totalCount = (clone $baseQuery)->count();

        // Get counts by category for stats
        $campaignCount = (clone $baseQuery)
            ->category('campaign')
            ->count();
        
        $paymentCount = (clone $baseQuery)
            ->category('payment')
            ->count();
        
        $userCount = (clone $baseQuery)
            ->category('user')
            ->count();

        $stats = [
            'total' => $totalCount,
            'unread' => $unreadCount,
            'read' => $readCount,
            'campaign' => $campaignCount,
            'user' => $userCount,
            'payment' => $paymentCount,
        ];

        return view('dashboard.advertiser.notifications.index', compact('notifications', 'categoryCounts', 'unreadCount', 'stats'));
    }

    /**
     * Mark a notification as read.
     *
     * @param  Notification  $notification
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Notification $notification)
    {
        $user = Auth::user();
        
        // Verify the notification belongs to the current user
        if ($notification->notifiable_type !== \App\Models\User::class || 
            $notification->notifiable_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        
        // Only mark admin action notifications as read
        $adminActionTypes = [
            'deposit_approved',
            'deposit_rejected',
            'campaign_approved',
            'campaign_rejected',
            'advertiser_approved',
            'advertiser_rejected',
        ];
        
        Notification::where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', $user->id)
            ->whereIn('type', $adminActionTypes)
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    /**
     * Mark a notification as unread.
     *
     * @param  Notification  $notification
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function markAsUnread(Notification $notification)
    {
        $user = Auth::user();
        
        // Verify the notification belongs to the current user
        if ($notification->notifiable_type !== \App\Models\User::class || 
            $notification->notifiable_id !== $user->id) {
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            return back()->withErrors(['error' => 'Unauthorized']);
        }

        $notification->markAsUnread();

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification marked as unread.');
    }
}
