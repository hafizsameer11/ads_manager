<?php

namespace App\Http\Controllers\Dashboard\Publisher;

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
        
        $notifications = Notification::where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', $user->id)
            ->recent(10)
            ->get();

        $unreadCount = Notification::where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', $user->id)
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
        
        // If coming from "View All Notifications" link, mark all as read
        if ($request->has('mark_all_read')) {
            Notification::where('notifiable_type', \App\Models\User::class)
                ->where('notifiable_id', $user->id)
                ->unread()
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        }
        
        $query = Notification::where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', $user->id);

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

        // Get notification counts by category
        $categoryCounts = Notification::where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', $user->id)
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category');

        $unreadCount = Notification::where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', $user->id)
            ->unread()
            ->count();

        $readCount = Notification::where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', $user->id)
            ->read()
            ->count();

        $totalCount = Notification::where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', $user->id)
            ->count();

        // Get counts by category for stats
        $withdrawalCount = Notification::where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', $user->id)
            ->category('withdrawal')
            ->count();
        
        $campaignCount = Notification::where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', $user->id)
            ->category('campaign')
            ->count();
        
        $userCount = Notification::where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', $user->id)
            ->category('user')
            ->count();
        
        $paymentCount = Notification::where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', $user->id)
            ->category('payment')
            ->count();
        
        $generalCount = Notification::where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', $user->id)
            ->category('general')
            ->count();

        $stats = [
            'total' => $totalCount,
            'unread' => $unreadCount,
            'read' => $readCount,
            'withdrawal' => $withdrawalCount,
            'campaign' => $campaignCount,
            'user' => $userCount,
            'payment' => $paymentCount,
            'general' => $generalCount,
        ];

        return view('dashboard.publisher.notifications.index', compact('notifications', 'categoryCounts', 'unreadCount', 'stats'));
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
        
        Notification::where('notifiable_type', \App\Models\User::class)
            ->where('notifiable_id', $user->id)
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
