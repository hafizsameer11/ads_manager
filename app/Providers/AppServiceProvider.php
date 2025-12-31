<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Notification;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share unread notification counts with sidebar
        // Share announcements with dashboard layouts
        View::composer('dashboard.layouts.main', function ($view) {
            if (Auth::check()) {
                $user = Auth::user();
                
                // Determine audience type based on user role
                $audience = 'all';
                if ($user->isPublisher()) {
                    $audience = 'publishers';
                } elseif ($user->isAdvertiser()) {
                    $audience = 'advertisers';
                } elseif ($user->hasAdminPermissions()) {
                    $audience = 'admins';
                }
                
                // Get active announcements for this user's audience
                $announcements = Announcement::active()
                    ->forAudience($audience)
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                $view->with('announcements', $announcements);
            } else {
                $view->with('announcements', collect([]));
            }
        });
        
        View::composer('dashboard.layouts.sidebar', function ($view) {
            if (Auth::check() && Auth::user()->hasAdminPermissions()) {
                $userId = Auth::id();
                
                // Get unread notification counts by category
                $notificationCounts = Notification::where('notifiable_type', \App\Models\User::class)
                    ->where('notifiable_id', $userId)
                    ->unread()
                    ->selectRaw('category, COUNT(*) as count')
                    ->groupBy('category')
                    ->pluck('count', 'category')
                    ->toArray();
                
                // Count contact message notifications specifically (they use 'general' category but 'contact_message_received' type)
                $contactNotifications = Notification::where('notifiable_type', \App\Models\User::class)
                    ->where('notifiable_id', $userId)
                    ->where('category', 'general')
                    ->where('type', 'contact_message_received')
                    ->unread()
                    ->count();
                
                // Count website notifications specifically (they use 'general' category but 'website_added' type)
                $websiteNotifications = Notification::where('notifiable_type', \App\Models\User::class)
                    ->where('notifiable_id', $userId)
                    ->where('category', 'general')
                    ->where('type', 'website_added')
                    ->unread()
                    ->count();
                
                $view->with([
                    'withdrawalNotifications' => $notificationCounts['withdrawal'] ?? 0,
                    'campaignNotifications' => $notificationCounts['campaign'] ?? 0,
                    'userNotifications' => $notificationCounts['user'] ?? 0,
                    'paymentNotifications' => $notificationCounts['payment'] ?? 0,
                    'contactNotifications' => $contactNotifications,
                    'websiteNotifications' => $websiteNotifications,
                ]);
            } else {
                $view->with([
                    'withdrawalNotifications' => 0,
                    'campaignNotifications' => 0,
                    'userNotifications' => 0,
                    'paymentNotifications' => 0,
                    'contactNotifications' => 0,
                    'websiteNotifications' => 0,
                ]);
            }
        });
    }
}
