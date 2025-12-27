<?php

use App\Http\Controllers\Website\HomeController;
use App\Http\Controllers\Website\AdvertiserController;
use App\Http\Controllers\Website\PublisherController;
use App\Http\Controllers\Website\FaqController;
use App\Http\Controllers\Website\ContactController;
use App\Http\Controllers\Website\ReportAbuseController;
use App\Http\Controllers\Website\ReportDmcaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Dashboard\Admin\AdminController;
use App\Http\Controllers\Dashboard\Admin\UsersController;
use App\Http\Controllers\Dashboard\Admin\WebsitesController as AdminWebsitesController;
use App\Http\Controllers\Dashboard\Admin\CampaignsController as AdminCampaignsController;
use App\Http\Controllers\Dashboard\Admin\WithdrawalsController as AdminWithdrawalsController;
use App\Http\Controllers\Dashboard\Admin\ReportsController;
use App\Http\Controllers\Dashboard\Admin\SettingsController;
use App\Http\Controllers\Dashboard\Admin\ProfileController;
use App\Http\Controllers\Dashboard\Advertiser\AdvertiserController as DashboardAdvertiserController;
use App\Http\Controllers\Dashboard\Advertiser\CampaignsController as AdvertiserCampaignsController;
use App\Http\Controllers\Dashboard\Advertiser\CreateCampaignController;
use App\Http\Controllers\Dashboard\Advertiser\AnalyticsController;
use App\Http\Controllers\Dashboard\Advertiser\BillingController;
use App\Http\Controllers\Dashboard\Advertiser\ProfileController as AdvertiserProfileController;
use App\Http\Controllers\Dashboard\Publisher\PublisherController as DashboardPublisherController;
use App\Http\Controllers\Dashboard\Publisher\SitesController;
use App\Http\Controllers\Dashboard\Publisher\AdUnitController;
use App\Http\Controllers\Dashboard\Publisher\EarningsController;
use App\Http\Controllers\Dashboard\Publisher\StatisticsController;
use App\Http\Controllers\Dashboard\Publisher\PaymentsController;
use App\Http\Controllers\Dashboard\Publisher\ProfileController as PublisherProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Website Routes
Route::prefix('')->name('website.')->group(function () {
    // Homepage
    Route::get('/', [HomeController::class, 'index'])->name('home');
    
    // Ad Script (Public - for publishers to embed)
    Route::get('/js/ads-network.js', [\App\Http\Controllers\Website\AdScriptController::class, 'serveScript'])->name('ad-script');
    
    // Advertiser Page
    Route::get('/advertiser', [AdvertiserController::class, 'index'])->name('advertiser');
    
    // Publisher Page
    Route::get('/publisher', [PublisherController::class, 'index'])->name('publisher');
    
    // FAQ Page
    Route::get('/faq', [FaqController::class, 'index'])->name('faq');
    
    // Contact Page
    Route::get('/contact', [ContactController::class, 'index'])->name('contact');
    Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');
    
    // Report Abuse Page
    Route::get('/report-abuse', [ReportAbuseController::class, 'index'])->name('report-abuse');
    Route::post('/report-abuse', [ReportAbuseController::class, 'store'])->name('report-abuse.store');
    
    // Report DMCA Page
    Route::get('/report-dmca', [ReportDmcaController::class, 'index'])->name('report-dmca');
    Route::post('/report-dmca', [ReportDmcaController::class, 'store'])->name('report-dmca.store');
});

// Authentication Routes (only for guests)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');
    
    // Password Reset Routes
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');
});

// Logout (requires authentication)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// Pending Approval Page (accessible to authenticated users)
Route::middleware('auth')->get('/pending-approval', [\App\Http\Controllers\Auth\PendingApprovalController::class, 'index'])->name('pending-approval');

// Dashboard Routes (requires authentication, active status, and approval)
Route::prefix('dashboard')->name('dashboard.')->middleware(['auth', 'active', 'approved'])->group(function () {
    // Admin Dashboard Routes (only for admin role)
    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/home', [AdminController::class, 'index'])->name('home');
        Route::get('/users', [UsersController::class, 'index'])->name('users');
        Route::post('/users/{id}/approve', [UsersController::class, 'approve'])->name('users.approve');
        Route::post('/users/{id}/reject', [UsersController::class, 'reject'])->name('users.reject');
        Route::post('/users/{id}/toggle-status', [UsersController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::delete('/users/{id}', [UsersController::class, 'destroy'])->name('users.destroy');
        Route::get('/websites', [AdminWebsitesController::class, 'index'])->name('websites');
        Route::get('/websites/{id}', [AdminWebsitesController::class, 'show'])->name('websites.show');
        Route::post('/websites/{id}/approve', [AdminWebsitesController::class, 'approve'])->name('websites.approve');
        Route::post('/websites/{id}/reject', [AdminWebsitesController::class, 'reject'])->name('websites.reject');
        Route::post('/websites/{id}/disable', [AdminWebsitesController::class, 'disable'])->name('websites.disable');
        Route::post('/websites/{id}/enable', [AdminWebsitesController::class, 'enable'])->name('websites.enable');
        Route::post('/websites/{id}/suspend', [AdminWebsitesController::class, 'suspend'])->name('websites.suspend');
        Route::get('/campaigns', [AdminCampaignsController::class, 'index'])->name('campaigns');
        Route::get('/campaigns/{id}', [AdminCampaignsController::class, 'show'])->name('campaigns.show');
        Route::get('/deposits', [\App\Http\Controllers\Dashboard\Admin\DepositsController::class, 'index'])->name('deposits');
        Route::post('/deposits/{id}/approve', [\App\Http\Controllers\Dashboard\Admin\DepositsController::class, 'approve'])->name('deposits.approve');
        Route::post('/deposits/{id}/reject', [\App\Http\Controllers\Dashboard\Admin\DepositsController::class, 'reject'])->name('deposits.reject');
        Route::get('/withdrawals', [AdminWithdrawalsController::class, 'index'])->name('withdrawals');
        Route::post('/withdrawals/{id}/approve', [AdminWithdrawalsController::class, 'approve'])->name('withdrawals.approve');
        Route::post('/withdrawals/{id}/reject', [AdminWithdrawalsController::class, 'reject'])->name('withdrawals.reject');
        Route::post('/withdrawals/{id}/mark-paid', [AdminWithdrawalsController::class, 'markPaid'])->name('withdrawals.mark-paid');
        Route::post('/campaigns/{id}/approve', [AdminCampaignsController::class, 'approve'])->name('campaigns.approve');
        Route::post('/campaigns/{id}/reject', [AdminCampaignsController::class, 'reject'])->name('campaigns.reject');
        Route::post('/campaigns/{id}/pause', [AdminCampaignsController::class, 'pause'])->name('campaigns.pause');
        Route::post('/campaigns/{id}/resume', [AdminCampaignsController::class, 'resume'])->name('campaigns.resume');
        Route::delete('/campaigns/{id}', [AdminCampaignsController::class, 'destroy'])->name('campaigns.destroy');
        Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
        Route::get('/analytics/geo', [ReportsController::class, 'geo'])->name('analytics.geo');
        Route::get('/analytics/device', [ReportsController::class, 'device'])->name('analytics.device');
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    });
    
    // Advertiser Dashboard Routes (only for advertiser role)
    Route::prefix('advertiser')->name('advertiser.')->middleware('role:advertiser')->group(function () {
        Route::get('/', [DashboardAdvertiserController::class, 'index'])->name('index');
        Route::get('/home', [DashboardAdvertiserController::class, 'index'])->name('home');
        Route::get('/campaigns', [AdvertiserCampaignsController::class, 'index'])->name('campaigns');
        Route::post('/campaigns/{id}/pause', [AdvertiserCampaignsController::class, 'pause'])->name('campaigns.pause');
        Route::post('/campaigns/{id}/resume', [AdvertiserCampaignsController::class, 'resume'])->name('campaigns.resume');
        Route::post('/campaigns/{id}/stop', [AdvertiserCampaignsController::class, 'stop'])->name('campaigns.stop');
        Route::get('/create-campaign', [CreateCampaignController::class, 'index'])->name('create-campaign');
        Route::post('/create-campaign', [CreateCampaignController::class, 'store'])->name('create-campaign.store');
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');
        Route::get('/analytics/geo', [AnalyticsController::class, 'geo'])->name('analytics.geo');
        Route::get('/analytics/device', [AnalyticsController::class, 'device'])->name('analytics.device');
        Route::get('/billing', [BillingController::class, 'index'])->name('billing');
        Route::post('/billing', [BillingController::class, 'store'])->name('billing.store');
        Route::get('/profile', [AdvertiserProfileController::class, 'index'])->name('profile');
        Route::post('/profile', [AdvertiserProfileController::class, 'update'])->name('profile.update');
    });
    
    // Publisher Dashboard Routes (only for publisher role)
    Route::prefix('publisher')->name('publisher.')->middleware('role:publisher')->group(function () {
        Route::get('/', [DashboardPublisherController::class, 'index'])->name('index');
        Route::get('/home', [DashboardPublisherController::class, 'index'])->name('home');
        Route::get('/sites', [SitesController::class, 'index'])->name('sites');
        Route::post('/sites', [SitesController::class, 'store'])->name('sites.store');
        Route::get('/sites/{website}', [SitesController::class, 'show'])->name('sites.show');
        Route::get('/sites/{website}/edit', [SitesController::class, 'edit'])->name('sites.edit');
        Route::put('/sites/{website}', [SitesController::class, 'update'])->name('sites.update');
        Route::post('/sites/{website}/verify', [SitesController::class, 'verify'])->name('sites.verify');
        Route::get('/sites/{website}/verification-file', [SitesController::class, 'downloadVerificationFile'])->name('sites.verification-file');
        Route::delete('/sites/{website}', [SitesController::class, 'destroy'])->name('sites.destroy');
        
        // Ad Units Routes
        Route::get('/sites/{website}/ad-units', [AdUnitController::class, 'index'])->name('sites.ad-units.index');
        Route::get('/sites/{website}/ad-units/create', [AdUnitController::class, 'create'])->name('sites.ad-units.create');
        Route::post('/sites/{website}/ad-units', [AdUnitController::class, 'store'])->name('sites.ad-units.store');
        Route::get('/ad-units/{adUnit}', [AdUnitController::class, 'show'])->name('ad-units.show');
        Route::get('/ad-units/{adUnit}/edit', [AdUnitController::class, 'edit'])->name('ad-units.edit');
        Route::put('/ad-units/{adUnit}', [AdUnitController::class, 'update'])->name('ad-units.update');
        Route::delete('/ad-units/{adUnit}', [AdUnitController::class, 'destroy'])->name('ad-units.destroy');
        
        Route::get('/earnings', [EarningsController::class, 'index'])->name('earnings');
        Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics');
        Route::get('/analytics/geo', [StatisticsController::class, 'geo'])->name('analytics.geo');
        Route::get('/analytics/device', [StatisticsController::class, 'device'])->name('analytics.device');
        Route::get('/payments', [PaymentsController::class, 'index'])->name('payments');
        Route::post('/payments', [PaymentsController::class, 'store'])->name('payments.store');
        Route::get('/profile', [PublisherProfileController::class, 'index'])->name('profile');
        Route::post('/profile', [PublisherProfileController::class, 'update'])->name('profile.update');
    });
});

// Default dashboard route (redirects based on user role, requires approval)
Route::middleware(['auth', 'active', 'approved'])->get('/dashboard', function () {
    $user = auth()->user();
    
    if ($user->isAdmin()) {
        return redirect()->route('dashboard.admin.home');
    } elseif ($user->isPublisher()) {
        return redirect()->route('dashboard.publisher.home');
    } elseif ($user->isAdvertiser()) {
        return redirect()->route('dashboard.advertiser.home');
    }
    
    return redirect()->route('website.home');
})->name('dashboard');
