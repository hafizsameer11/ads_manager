<?php

namespace App\Http\Controllers\Dashboard\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

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
        ];
        
        return view('dashboard.admin.settings', compact('settings'));
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
                
            default:
                return back()->withErrors(['error' => 'Invalid section.']);
        }
    }
}
