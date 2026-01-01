<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SmtpSetting;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Load current settings
        $settings = [
            'admin_percentage' => Setting::get('admin_percentage', 20),
            'publisher_percentage' => Setting::get('publisher_percentage', 80),
            'minimum_payout' => Setting::get('minimum_payout', 50),
            'maximum_payout' => Setting::get('maximum_payout', 10000),
            'payout_cycle' => Setting::get('payout_cycle', 'net30'),
            // Deposit settings
            'minimum_deposit' => Setting::get('minimum_deposit', 10),
            'maximum_deposit' => Setting::get('maximum_deposit', 50000),
            'default_cpm_rate' => Setting::get('default_cpm_rate', 1.00),
            'default_cpc_rate' => Setting::get('default_cpc_rate', 0.10),
            'click_limit_per_ip' => Setting::get('click_limit_per_ip', 10),
            'block_vpn' => Setting::get('block_vpn', true),
            'block_bots' => Setting::get('block_bots', true),
            'payment_gateways' => Setting::get('payment_gateways', ['paypal', 'coinpayment', 'faucetpay']),
            // Stripe settings
            'stripe_enabled' => Setting::get('stripe_enabled', false),
            'stripe_publishable_key' => Setting::get('stripe_publishable_key', ''),
            'stripe_secret_key' => Setting::get('stripe_secret_key', ''),
            'stripe_webhook_secret' => Setting::get('stripe_webhook_secret', ''),
            // PayPal settings
            'paypal_enabled' => Setting::get('paypal_enabled', false),
            'paypal_client_id' => Setting::get('paypal_client_id', ''),
            'paypal_secret' => Setting::get('paypal_secret', ''),
            'paypal_mode' => Setting::get('paypal_mode', 'sandbox'), // sandbox or live
            // CoinPayments settings
            'coinpayments_enabled' => Setting::get('coinpayments_enabled', false),
            'coinpayments_public_key' => Setting::get('coinpayments_public_key', ''),
            'coinpayments_private_key' => Setting::get('coinpayments_private_key', ''),
            'coinpayments_merchant_id' => Setting::get('coinpayments_merchant_id', ''),
            'coinpayments_ipn_secret' => Setting::get('coinpayments_ipn_secret', ''),
            // Rotation & Frequency settings
            'ad_rotation_mode' => Setting::get('ad_rotation_mode', 'weighted'),
            'global_max_impressions_per_ip_per_day' => Setting::get('global_max_impressions_per_ip_per_day', null),
            'global_max_clicks_per_ip_per_day' => Setting::get('global_max_clicks_per_ip_per_day', null),
            // Publisher website approval
            'auto_approve_publisher_websites' => Setting::get('auto_approve_publisher_websites', false),
            // Referral settings
            'referral_commission_rate' => Setting::get('referral_commission_rate', 5.00),
            'referral_deposit_bonus_rate' => Setting::get('referral_deposit_bonus_rate', 5.00),
        ];
        
        // Load SMTP settings
        $smtpSettings = SmtpSetting::getDefault();
        
        return view('dashboard.admin.settings', compact('settings', 'smtpSettings'));
    }

    /**
     * Update settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $section = $request->input('section');
        
        switch ($section) {
            case 'revenue':
                $request->validate([
                    'admin_percentage' => 'required|numeric|min:0|max:100',
                    'publisher_percentage' => 'required|numeric|min:0|max:100',
                ]);
                
                $adminPercent = $request->admin_percentage;
                $publisherPercent = $request->publisher_percentage;
                
                // Ensure they add up to 100%
                if (abs(($adminPercent + $publisherPercent) - 100) > 0.01) {
                    return back()->withErrors(['error' => 'Admin and Publisher percentages must add up to 100%.']);
                }
                
                Setting::set('admin_percentage', $adminPercent, 'float', 'revenue');
                Setting::set('publisher_percentage', $publisherPercent, 'float', 'revenue');
                
                // Log activity
                ActivityLogService::logSettingsUpdate('revenue', [
                    'admin_percentage' => $adminPercent,
                    'publisher_percentage' => $publisherPercent,
                ], Auth::user());
                
                return back()->with('success', 'Revenue share settings updated successfully.');
                
            case 'payout':
                $request->validate([
                    'minimum_payout' => 'required|numeric|min:1',
                    'maximum_payout' => 'required|numeric|min:1',
                    'payout_cycle' => 'required|in:net7,net15,net30',
                ]);
                
                if ($request->maximum_payout < $request->minimum_payout) {
                    return back()->withErrors(['error' => 'Maximum payout must be greater than or equal to minimum payout.']);
                }
                
                Setting::set('minimum_payout', $request->minimum_payout, 'float', 'payout');
                Setting::set('maximum_payout', $request->maximum_payout, 'float', 'payout');
                Setting::set('payout_cycle', $request->payout_cycle, 'string', 'payout');
                
                return back()->with('success', 'Payout settings updated successfully.');
                
            case 'deposit':
                $request->validate([
                    'minimum_deposit' => 'required|numeric|min:1',
                    'maximum_deposit' => 'required|numeric|min:1',
                ]);
                
                if ($request->maximum_deposit < $request->minimum_deposit) {
                    return back()->withErrors(['error' => 'Maximum deposit must be greater than or equal to minimum deposit.']);
                }
                
                Setting::set('minimum_deposit', $request->minimum_deposit, 'float', 'deposit');
                Setting::set('maximum_deposit', $request->maximum_deposit, 'float', 'deposit');
                
                return back()->with('success', 'Deposit settings updated successfully.');
                
            case 'rates':
                $request->validate([
                    'default_cpm_rate' => 'nullable|numeric|min:0',
                    'default_cpc_rate' => 'nullable|numeric|min:0',
                ]);
                
                if ($request->filled('default_cpm_rate')) {
                    Setting::set('default_cpm_rate', $request->default_cpm_rate, 'float', 'rates');
                }
                if ($request->filled('default_cpc_rate')) {
                    Setting::set('default_cpc_rate', $request->default_cpc_rate, 'float', 'rates');
                }
                
                return back()->with('success', 'Rate settings updated successfully.');
                
            case 'fraud':
                $request->validate([
                    'click_limit_per_ip' => 'required|integer|min:1',
                    'block_vpn' => 'nullable|boolean',
                    'block_bots' => 'nullable|boolean',
                ]);
                
                Setting::set('click_limit_per_ip', $request->click_limit_per_ip, 'integer', 'fraud');
                Setting::set('block_vpn', $request->has('block_vpn'), 'boolean', 'fraud');
                Setting::set('block_bots', $request->has('block_bots'), 'boolean', 'fraud');
                
                return back()->with('success', 'Fraud detection settings updated successfully.');
                
            case 'payment':
                Setting::set('payment_gateways', $request->input('payment_gateways', []), 'json', 'payment');
                
                return back()->with('success', 'Payment gateway settings updated successfully.');
                
            case 'stripe':
                $request->validate([
                    'stripe_enabled' => 'nullable|boolean',
                    'stripe_publishable_key' => 'nullable|string|max:255',
                    'stripe_secret_key' => 'nullable|string|max:255',
                    'stripe_webhook_secret' => 'nullable|string|max:255',
                ]);
                
                Setting::set('stripe_enabled', $request->has('stripe_enabled'), 'boolean', 'payment');
                
                if ($request->filled('stripe_publishable_key')) {
                    Setting::set('stripe_publishable_key', $request->stripe_publishable_key, 'string', 'payment');
                }
                
                if ($request->filled('stripe_secret_key')) {
                    Setting::set('stripe_secret_key', $request->stripe_secret_key, 'string', 'payment');
                }
                
                if ($request->filled('stripe_webhook_secret')) {
                    Setting::set('stripe_webhook_secret', $request->stripe_webhook_secret, 'string', 'payment');
                }
                
                return back()->with('success', 'Stripe settings updated successfully.');
                
            case 'paypal':
                $request->validate([
                    'paypal_enabled' => 'nullable|boolean',
                    'paypal_client_id' => 'nullable|string|max:255',
                    'paypal_secret' => 'nullable|string|max:255',
                    'paypal_mode' => 'nullable|in:sandbox,live',
                ]);
                
                Setting::set('paypal_enabled', $request->has('paypal_enabled'), 'boolean', 'payment');
                Setting::set('paypal_mode', $request->input('paypal_mode', 'sandbox'), 'string', 'payment');
                
                if ($request->filled('paypal_client_id')) {
                    Setting::set('paypal_client_id', $request->paypal_client_id, 'string', 'payment');
                }
                
                if ($request->filled('paypal_secret')) {
                    Setting::set('paypal_secret', $request->paypal_secret, 'string', 'payment');
                }
                
                return back()->with('success', 'PayPal settings updated successfully.');
                
            case 'coinpayments':
                $request->validate([
                    'coinpayments_enabled' => 'nullable|boolean',
                    'coinpayments_public_key' => 'nullable|string|max:255',
                    'coinpayments_private_key' => 'nullable|string|max:255',
                    'coinpayments_merchant_id' => 'nullable|string|max:255',
                    'coinpayments_ipn_secret' => 'nullable|string|max:255',
                ]);
                
                Setting::set('coinpayments_enabled', $request->has('coinpayments_enabled'), 'boolean', 'payment');
                
                if ($request->filled('coinpayments_public_key')) {
                    Setting::set('coinpayments_public_key', $request->coinpayments_public_key, 'string', 'payment');
                }
                
                if ($request->filled('coinpayments_private_key')) {
                    Setting::set('coinpayments_private_key', $request->coinpayments_private_key, 'string', 'payment');
                }
                
                if ($request->filled('coinpayments_merchant_id')) {
                    Setting::set('coinpayments_merchant_id', $request->coinpayments_merchant_id, 'string', 'payment');
                }
                
                if ($request->filled('coinpayments_ipn_secret')) {
                    Setting::set('coinpayments_ipn_secret', $request->coinpayments_ipn_secret, 'string', 'payment');
                }
                
                return back()->with('success', 'CoinPayments settings updated successfully.');
                
            case 'rotation_frequency':
                $request->validate([
                    'ad_rotation_mode' => 'required|in:round_robin,weighted,random',
                    'global_max_impressions_per_ip_per_day' => 'nullable|integer|min:1',
                    'global_max_clicks_per_ip_per_day' => 'nullable|integer|min:1',
                ]);
                
                Setting::set('ad_rotation_mode', $request->ad_rotation_mode, 'string', 'ad_rotation');
                Setting::set('global_max_impressions_per_ip_per_day', $request->global_max_impressions_per_ip_per_day, 'integer', 'ad_rotation');
                Setting::set('global_max_clicks_per_ip_per_day', $request->global_max_clicks_per_ip_per_day, 'integer', 'ad_rotation');
                
                return back()->with('success', 'Rotation & Frequency settings updated successfully.');
                
            case 'publisher':
                $request->validate([
                    'auto_approve_publisher_websites' => 'nullable|boolean',
                ]);
                
                Setting::set('auto_approve_publisher_websites', $request->has('auto_approve_publisher_websites'), 'boolean', 'publisher');
                
                return back()->with('success', 'Publisher settings updated successfully.');
                
            case 'referral':
                $request->validate([
                    'referral_commission_rate' => 'required|numeric|min:0|max:100',
                    'referral_deposit_bonus_rate' => 'required|numeric|min:0|max:100',
                ]);
                
                Setting::set('referral_commission_rate', $request->referral_commission_rate, 'float', 'referral');
                Setting::set('referral_deposit_bonus_rate', $request->referral_deposit_bonus_rate, 'float', 'referral');
                
                // Log activity
                ActivityLogService::logSettingsUpdate('referral', [
                    'referral_commission_rate' => $request->referral_commission_rate,
                    'referral_deposit_bonus_rate' => $request->referral_deposit_bonus_rate,
                ], Auth::user());
                
                return back()->with('success', 'Referral program settings updated successfully.');
                
            case 'smtp':
                $request->validate([
                    'mailer' => 'required|string|in:smtp,sendmail,mailgun,ses,postmark,log,array',
                    'host' => 'nullable|string|max:255',
                    'port' => 'nullable|integer|min:1|max:65535',
                    'encryption' => 'nullable|string|in:tls,ssl',
                    'username' => 'nullable|string|max:255',
                    'password' => 'nullable|string|max:255',
                    'from_address' => 'nullable|email|max:255',
                    'from_name' => 'nullable|string|max:255',
                    'reply_to_address' => 'nullable|email|max:255',
                    'reply_to_name' => 'nullable|string|max:255',
                    'timeout' => 'nullable|integer|min:1',
                    'local_domain' => 'nullable|string|max:255',
                    'is_active' => 'nullable|boolean',
                ]);
                
                // Get existing SMTP settings or create new
                $smtpSetting = SmtpSetting::first();
                
                if (!$smtpSetting) {
                    $smtpSetting = new SmtpSetting();
                }
                
                // If activating this SMTP setting, deactivate all others
                if ($request->has('is_active') && $request->is_active) {
                    SmtpSetting::where('id', '!=', $smtpSetting->id ?? 0)->update(['is_active' => false]);
                }
                
                $smtpSetting->mailer = $request->mailer;
                $smtpSetting->host = $request->host;
                $smtpSetting->port = $request->port ?? 587;
                $smtpSetting->encryption = $request->encryption;
                $smtpSetting->username = $request->username;
                // Only update password if it's provided (not empty)
                if ($request->filled('password')) {
                    $smtpSetting->password = $request->password;
                }
                $smtpSetting->from_address = $request->from_address;
                $smtpSetting->from_name = $request->from_name;
                $smtpSetting->reply_to_address = $request->reply_to_address;
                $smtpSetting->reply_to_name = $request->reply_to_name;
                $smtpSetting->timeout = $request->timeout;
                $smtpSetting->local_domain = $request->local_domain;
                $smtpSetting->is_active = $request->has('is_active') ? true : false;
                $smtpSetting->save();
                
                // Log activity
                ActivityLogService::logSettingsUpdate('smtp', [
                    'mailer' => $request->mailer,
                    'host' => $request->host,
                    'is_active' => $smtpSetting->is_active,
                ], Auth::user());
                
                return back()->with('success', 'SMTP settings updated successfully.');
                
            default:
                return back()->withErrors(['error' => 'Invalid section.']);
        }
    }
}
