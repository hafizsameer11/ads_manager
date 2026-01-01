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
use App\Http\Controllers\Dashboard\Admin\ContactMessagesController;
use App\Http\Controllers\Dashboard\Admin\AbuseReportsController;
use App\Http\Controllers\Dashboard\Admin\DmcaReportsController;
use App\Http\Controllers\Dashboard\Admin\ManualPaymentAccountsController;
use App\Http\Controllers\Dashboard\Admin\AllowedAccountTypesController;
use App\Http\Controllers\Dashboard\Admin\TargetCountriesController;
use App\Http\Controllers\Dashboard\Admin\NotificationsController;
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

// Webhook routes (no auth required)
Route::post('/webhooks/stripe', [\App\Http\Controllers\Webhook\StripeWebhookController::class, 'handle'])->name('webhooks.stripe');
Route::post('/webhooks/paypal', [\App\Http\Controllers\Webhook\PayPalWebhookController::class, 'handle'])->name('webhooks.paypal');
Route::match(['get', 'post'], '/webhooks/coinpayments', [\App\Http\Controllers\Webhook\CoinPaymentsWebhookController::class, 'handle'])->name('webhooks.coinpayments');

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

    // Dynamic pages (Terms, Privacy Policy, etc.)
    Route::get('/page/{slug}', [\App\Http\Controllers\Website\PageController::class, 'show'])->name('page.show');
});

// Authentication Routes (only for guests)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');

    // Two-Factor Authentication Routes
    Route::get('/two-factor/verify', [\App\Http\Controllers\Auth\TwoFactorVerificationController::class, 'show'])->name('two-factor.verify');
    Route::post('/two-factor/verify', [\App\Http\Controllers\Auth\TwoFactorVerificationController::class, 'verify'])->name('two-factor.verify.submit');

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
    // Admin Dashboard Routes (permission-based access)
    Route::prefix('admin')->name('admin.')->group(function () {
        // Dashboard home - accessible to anyone with any admin permission
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('/home', [AdminController::class, 'index'])->name('home');

        // User management routes
        Route::middleware('permission:manage_users')->group(function () {
            Route::get('/users', [UsersController::class, 'index'])->name('users');
            Route::get('/users/{id}', [UsersController::class, 'show'])->name('users.show');
            Route::get('/users/{id}/edit', [UsersController::class, 'edit'])->name('users.edit');
            Route::put('/users/{id}', [UsersController::class, 'update'])->name('users.update');
            Route::delete('/users/{id}', [UsersController::class, 'destroy'])->name('users.destroy');
            Route::get('/users/{id}/referrals', [UsersController::class, 'referrals'])->name('users.referrals');
        });

        // User approval/status routes
        Route::middleware('permission:approve_users')->group(function () {
            Route::post('/users/{id}/approve', [UsersController::class, 'approve'])->name('users.approve');
            Route::post('/users/{id}/reject', [UsersController::class, 'reject'])->name('users.reject');
            Route::post('/users/{id}/suspend', [UsersController::class, 'suspend'])->name('users.suspend');
            Route::post('/users/{id}/block', [UsersController::class, 'block'])->name('users.block');
            Route::post('/users/{id}/toggle-status', [UsersController::class, 'toggleStatus'])->name('users.toggle-status');
        });

        // Website management routes
        Route::middleware('permission:manage_websites')->group(function () {
            Route::get('/websites', [AdminWebsitesController::class, 'index'])->name('websites');
            Route::get('/websites/{id}', [AdminWebsitesController::class, 'show'])->name('websites.show');
            Route::post('/websites/{id}/approve', [AdminWebsitesController::class, 'approve'])->name('websites.approve');
            Route::post('/websites/{id}/reject', [AdminWebsitesController::class, 'reject'])->name('websites.reject');
            Route::post('/websites/{id}/disable', [AdminWebsitesController::class, 'disable'])->name('websites.disable');
            Route::post('/websites/{id}/enable', [AdminWebsitesController::class, 'enable'])->name('websites.enable');
            Route::post('/websites/{id}/suspend', [AdminWebsitesController::class, 'suspend'])->name('websites.suspend');
        });

        // Ad units management routes
        Route::middleware('permission:manage_ad_units')->group(function () {
            Route::get('/ad-units', [\App\Http\Controllers\Dashboard\Admin\AdUnitsController::class, 'index'])->name('ad-units');
            Route::get('/ad-units/create', [\App\Http\Controllers\Dashboard\Admin\AdUnitsController::class, 'create'])->name('ad-units.create');
            Route::post('/ad-units', [\App\Http\Controllers\Dashboard\Admin\AdUnitsController::class, 'store'])->name('ad-units.store');
            Route::get('/ad-units/{id}', [\App\Http\Controllers\Dashboard\Admin\AdUnitsController::class, 'show'])->name('ad-units.show');
            Route::get('/ad-units/{id}/edit', [\App\Http\Controllers\Dashboard\Admin\AdUnitsController::class, 'edit'])->name('ad-units.edit');
            Route::put('/ad-units/{id}', [\App\Http\Controllers\Dashboard\Admin\AdUnitsController::class, 'update'])->name('ad-units.update');
            Route::delete('/ad-units/{id}', [\App\Http\Controllers\Dashboard\Admin\AdUnitsController::class, 'destroy'])->name('ad-units.destroy');
        });

        // Activity logs routes
        Route::middleware('permission:view_activity_logs')->group(function () {
            Route::get('/activity-logs', [\App\Http\Controllers\Dashboard\Admin\ActivityLogsController::class, 'index'])->name('activity-logs');
            Route::get('/activity-logs/{id}', [\App\Http\Controllers\Dashboard\Admin\ActivityLogsController::class, 'show'])->name('activity-logs.show');
        });

        // Roles & permissions routes
        Route::middleware('permission:manage_roles')->group(function () {
            Route::resource('roles', \App\Http\Controllers\Dashboard\Admin\RolesController::class);
            Route::post('/users/{user}/assign-role', [\App\Http\Controllers\Dashboard\Admin\RolesController::class, 'assignRole'])->name('users.assign-role');
            Route::delete('/users/{user}/remove-role/{role}', [\App\Http\Controllers\Dashboard\Admin\RolesController::class, 'removeRole'])->name('users.remove-role');
        });

        // Campaign management routes
        Route::middleware('permission:manage_campaigns')->group(function () {
            Route::get('/campaigns', [AdminCampaignsController::class, 'index'])->name('campaigns');
            Route::get('/campaigns/{id}', [AdminCampaignsController::class, 'show'])->name('campaigns.show');
            Route::post('/campaigns/{id}/approve', [AdminCampaignsController::class, 'approve'])->name('campaigns.approve');
            Route::post('/campaigns/{id}/reject', [AdminCampaignsController::class, 'reject'])->name('campaigns.reject');
            Route::post('/campaigns/{id}/pause', [AdminCampaignsController::class, 'pause'])->name('campaigns.pause');
            Route::post('/campaigns/{id}/resume', [AdminCampaignsController::class, 'resume'])->name('campaigns.resume');
            Route::delete('/campaigns/{id}', [AdminCampaignsController::class, 'destroy'])->name('campaigns.destroy');
        });

        // Deposit management routes
        Route::middleware('permission:manage_deposits')->group(function () {
            Route::get('/deposits', [\App\Http\Controllers\Dashboard\Admin\DepositsController::class, 'index'])->name('deposits');
            Route::post('/deposits/{id}/approve', [\App\Http\Controllers\Dashboard\Admin\DepositsController::class, 'approve'])->name('deposits.approve');
            Route::post('/deposits/{id}/reject', [\App\Http\Controllers\Dashboard\Admin\DepositsController::class, 'reject'])->name('deposits.reject');
            Route::get('/deposits/export/csv', [\App\Http\Controllers\Dashboard\Admin\DepositsController::class, 'exportCsv'])->name('deposits.export.csv');
            Route::get('/deposits/export/excel', [\App\Http\Controllers\Dashboard\Admin\DepositsController::class, 'exportExcel'])->name('deposits.export.excel');
            Route::get('/deposits/export/pdf', [\App\Http\Controllers\Dashboard\Admin\DepositsController::class, 'exportPdf'])->name('deposits.export.pdf');
            Route::get('/invoices', [\App\Http\Controllers\Dashboard\Admin\InvoicesController::class, 'index'])->name('invoices');
            Route::get('/invoices/{id}', [\App\Http\Controllers\Dashboard\Admin\InvoicesController::class, 'show'])->name('invoices.show');
            Route::post('/invoices/generate/{transactionId}', [\App\Http\Controllers\Dashboard\Admin\InvoicesController::class, 'generate'])->name('invoices.generate');
            Route::get('/invoices/{id}/download', [\App\Http\Controllers\Dashboard\Admin\InvoicesController::class, 'download'])->name('invoices.download');
            Route::post('/invoices/{id}/mark-paid', [\App\Http\Controllers\Dashboard\Admin\InvoicesController::class, 'markAsPaid'])->name('invoices.mark-paid');
            Route::post('/invoices/{id}/mark-sent', [\App\Http\Controllers\Dashboard\Admin\InvoicesController::class, 'markAsSent'])->name('invoices.mark-sent');
        });

        // Withdrawal management routes
        Route::middleware('permission:manage_withdrawals')->group(function () {
            Route::get('/withdrawals', [AdminWithdrawalsController::class, 'index'])->name('withdrawals');
            Route::post('/withdrawals/{id}/approve', [AdminWithdrawalsController::class, 'approve'])->name('withdrawals.approve');
            Route::post('/withdrawals/{id}/reject', [AdminWithdrawalsController::class, 'reject'])->name('withdrawals.reject');
            Route::post('/withdrawals/{id}/mark-paid', [AdminWithdrawalsController::class, 'markPaid'])->name('withdrawals.mark-paid');
            Route::get('/withdrawals/export/csv', [AdminWithdrawalsController::class, 'exportCsv'])->name('withdrawals.export.csv');
            Route::get('/withdrawals/export/excel', [AdminWithdrawalsController::class, 'exportExcel'])->name('withdrawals.export.excel');
            Route::get('/withdrawals/export/pdf', [AdminWithdrawalsController::class, 'exportPdf'])->name('withdrawals.export.pdf');
        });

        // Reports routes (accessible with any admin permission)
        Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
        Route::get('/analytics/geo', [ReportsController::class, 'geo'])->name('analytics.geo');
        Route::get('/analytics/device', [ReportsController::class, 'device'])->name('analytics.device');

        // Contact messages routes (accessible with any admin permission)
        Route::get('/contact-messages', [ContactMessagesController::class, 'index'])->name('contact-messages');
        Route::get('/contact-messages/{id}', [ContactMessagesController::class, 'show'])->name('contact-messages.show');
        Route::post('/contact-messages/{id}/mark-read', [ContactMessagesController::class, 'markAsRead'])->name('contact-messages.mark-read');
        Route::post('/contact-messages/{id}/mark-unread', [ContactMessagesController::class, 'markAsUnread'])->name('contact-messages.mark-unread');
        Route::delete('/contact-messages/{id}', [ContactMessagesController::class, 'destroy'])->name('contact-messages.destroy');

        // Abuse reports routes (accessible with any admin permission)
        Route::get('/abuse-reports', [AbuseReportsController::class, 'index'])->name('abuse-reports');
        Route::get('/abuse-reports/{id}', [AbuseReportsController::class, 'show'])->name('abuse-reports.show');
        Route::post('/abuse-reports/{id}/update-status', [AbuseReportsController::class, 'updateStatus'])->name('abuse-reports.update-status');
        Route::delete('/abuse-reports/{id}', [AbuseReportsController::class, 'destroy'])->name('abuse-reports.destroy');

        // DMCA reports routes (accessible with any admin permission)
        Route::get('/dmca-reports', [DmcaReportsController::class, 'index'])->name('dmca-reports');
        Route::get('/dmca-reports/{id}', [DmcaReportsController::class, 'show'])->name('dmca-reports.show');
        Route::post('/dmca-reports/{id}/update-status', [DmcaReportsController::class, 'updateStatus'])->name('dmca-reports.update-status');
        Route::delete('/dmca-reports/{id}', [DmcaReportsController::class, 'destroy'])->name('dmca-reports.destroy');

        // Settings and configuration routes
        Route::middleware('permission:manage_settings')->group(function () {
            Route::resource('manual-payment-accounts', ManualPaymentAccountsController::class);
            Route::resource('allowed-account-types', AllowedAccountTypesController::class);
            Route::post('allowed-account-types/{allowedAccountType}/toggle-status', [AllowedAccountTypesController::class, 'toggleStatus'])->name('allowed-account-types.toggle-status');
            Route::post('/manual-payment-accounts/{id}/toggle-status', [ManualPaymentAccountsController::class, 'toggleStatus'])->name('manual-payment-accounts.toggle-status');
            Route::resource('target-countries', TargetCountriesController::class);
            Route::post('/target-countries/{targetCountry}/toggle-status', [TargetCountriesController::class, 'toggleStatus'])->name('target-countries.toggle-status');
            Route::get('/target-countries/devices/create', [TargetCountriesController::class, 'createDevice'])->name('target-countries.create-device');
            Route::post('/target-countries/devices', [TargetCountriesController::class, 'storeDevice'])->name('target-countries.store-device');
            Route::get('/target-countries/devices/{targetDevice}/edit', [TargetCountriesController::class, 'editDevice'])->name('target-countries.edit-device');
            Route::put('/target-countries/devices/{targetDevice}', [TargetCountriesController::class, 'updateDevice'])->name('target-countries.update-device');
            Route::delete('/target-countries/devices/{targetDevice}', [TargetCountriesController::class, 'destroyDevice'])->name('target-countries.destroy-device');
            Route::post('/target-countries/devices/{targetDevice}/toggle-status', [TargetCountriesController::class, 'toggleDeviceStatus'])->name('target-countries.toggle-device-status');
            Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
            Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
        });

        // Notifications routes (accessible with any admin permission)
        Route::get('/notifications', [NotificationsController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/recent', [NotificationsController::class, 'recent'])->name('notifications.recent');
        Route::post('/notifications/{notification}/read', [NotificationsController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::post('/notifications/{notification}/unread', [NotificationsController::class, 'markAsUnread'])->name('notifications.mark-unread');
        Route::post('/notifications/mark-all-read', [NotificationsController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

        // Profile routes (accessible with any admin permission)
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

        // Security / 2FA routes (accessible with any admin permission)
        Route::get('/security/two-factor', [\App\Http\Controllers\Dashboard\Admin\SecurityController::class, 'show'])->name('security.two-factor');
        Route::post('/security/two-factor/enable', [\App\Http\Controllers\Dashboard\Admin\SecurityController::class, 'enable'])->name('security.two-factor.enable');
        Route::post('/security/two-factor/disable', [\App\Http\Controllers\Dashboard\Admin\SecurityController::class, 'disable'])->name('security.two-factor.disable');
        Route::get('/security/two-factor/recovery-codes', [\App\Http\Controllers\Dashboard\Admin\SecurityController::class, 'recoveryCodes'])->name('security.two-factor.recovery-codes');

        // CMS routes (accessible with manage_settings permission)
        Route::middleware('permission:manage_settings')->group(function () {
            Route::resource('announcements', \App\Http\Controllers\Dashboard\Admin\AnnouncementsController::class);
            Route::resource('email-templates', \App\Http\Controllers\Dashboard\Admin\EmailTemplatesController::class);
            Route::resource('pages', \App\Http\Controllers\Dashboard\Admin\PagesController::class);
        });

        // Support tickets routes (accessible with any admin permission)
        Route::prefix('support-tickets')->name('support-tickets.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Dashboard\Admin\SupportTicketsController::class, 'index'])->name('index');
            Route::get('/{supportTicket}', [\App\Http\Controllers\Dashboard\Admin\SupportTicketsController::class, 'show'])->name('show');
            Route::put('/{supportTicket}', [\App\Http\Controllers\Dashboard\Admin\SupportTicketsController::class, 'update'])->name('update');
            Route::post('/{supportTicket}/reply', [\App\Http\Controllers\Dashboard\Admin\SupportTicketsController::class, 'reply'])->name('reply');
            Route::delete('/{supportTicket}', [\App\Http\Controllers\Dashboard\Admin\SupportTicketsController::class, 'destroy'])->name('destroy');
        });
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
        // Stripe payment routes
        Route::get('/stripe/checkout', [\App\Http\Controllers\Dashboard\Advertiser\StripeController::class, 'checkout'])->name('stripe.checkout');
        Route::get('/stripe/success', [\App\Http\Controllers\Dashboard\Advertiser\StripeController::class, 'success'])->name('stripe.success');
        Route::get('/stripe/cancel', [\App\Http\Controllers\Dashboard\Advertiser\StripeController::class, 'cancel'])->name('stripe.cancel');
        // PayPal payment routes
        Route::get('/paypal/checkout', [\App\Http\Controllers\Dashboard\Advertiser\PayPalController::class, 'checkout'])->name('paypal.checkout');
        Route::get('/paypal/success', [\App\Http\Controllers\Dashboard\Advertiser\PayPalController::class, 'success'])->name('paypal.success');
        Route::get('/paypal/cancel', [\App\Http\Controllers\Dashboard\Advertiser\PayPalController::class, 'cancel'])->name('paypal.cancel');
        // CoinPayments payment routes
        Route::get('/coinpayments/checkout', [\App\Http\Controllers\Dashboard\Advertiser\CoinPaymentsController::class, 'checkout'])->name('coinpayments.checkout');
        Route::get('/coinpayments/success', [\App\Http\Controllers\Dashboard\Advertiser\CoinPaymentsController::class, 'success'])->name('coinpayments.success');
        Route::get('/coinpayments/cancel', [\App\Http\Controllers\Dashboard\Advertiser\CoinPaymentsController::class, 'cancel'])->name('coinpayments.cancel');
        Route::get('/notifications', [\App\Http\Controllers\Dashboard\Advertiser\NotificationsController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/recent', [\App\Http\Controllers\Dashboard\Advertiser\NotificationsController::class, 'recent'])->name('notifications.recent');
        Route::post('/notifications/{notification}/read', [\App\Http\Controllers\Dashboard\Advertiser\NotificationsController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::post('/notifications/{notification}/unread', [\App\Http\Controllers\Dashboard\Advertiser\NotificationsController::class, 'markAsUnread'])->name('notifications.mark-unread');
        Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Dashboard\Advertiser\NotificationsController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::get('/profile', [AdvertiserProfileController::class, 'index'])->name('profile');
        Route::post('/profile', [AdvertiserProfileController::class, 'update'])->name('profile.update');
        // Support Tickets Routes
        Route::prefix('support-tickets')->name('support-tickets.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Dashboard\User\SupportTicketsController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Dashboard\User\SupportTicketsController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Dashboard\User\SupportTicketsController::class, 'store'])->name('store');
            Route::get('/{supportTicket}', [\App\Http\Controllers\Dashboard\User\SupportTicketsController::class, 'show'])->name('show');
            Route::post('/{supportTicket}/reply', [\App\Http\Controllers\Dashboard\User\SupportTicketsController::class, 'reply'])->name('reply');
        });
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
        Route::get('/notifications', [\App\Http\Controllers\Dashboard\Publisher\NotificationsController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/recent', [\App\Http\Controllers\Dashboard\Publisher\NotificationsController::class, 'recent'])->name('notifications.recent');
        Route::post('/notifications/{notification}/read', [\App\Http\Controllers\Dashboard\Publisher\NotificationsController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::post('/notifications/{notification}/unread', [\App\Http\Controllers\Dashboard\Publisher\NotificationsController::class, 'markAsUnread'])->name('notifications.mark-unread');
        Route::post('/notifications/mark-all-read', [\App\Http\Controllers\Dashboard\Publisher\NotificationsController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::get('/profile', [PublisherProfileController::class, 'index'])->name('profile');
        Route::post('/profile', [PublisherProfileController::class, 'update'])->name('profile.update');
        // Support Tickets Routes
        Route::prefix('support-tickets')->name('support-tickets.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Dashboard\User\SupportTicketsController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Dashboard\User\SupportTicketsController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Dashboard\User\SupportTicketsController::class, 'store'])->name('store');
            Route::get('/{supportTicket}', [\App\Http\Controllers\Dashboard\User\SupportTicketsController::class, 'show'])->name('show');
            Route::post('/{supportTicket}/reply', [\App\Http\Controllers\Dashboard\User\SupportTicketsController::class, 'reply'])->name('reply');
        });
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
