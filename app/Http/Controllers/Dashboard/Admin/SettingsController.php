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
            'payout_cycle' => Setting::get('payout_cycle', 'net30'),
            'default_cpm_rate' => Setting::get('default_cpm_rate', 1.00),
            'default_cpc_rate' => Setting::get('default_cpc_rate', 0.10),
            'click_limit_per_ip' => Setting::get('click_limit_per_ip', 10),
            'block_vpn' => Setting::get('block_vpn', true),
            'block_bots' => Setting::get('block_bots', true),
            'payment_gateways' => Setting::get('payment_gateways', ['paypal', 'coinpayment', 'faucetpay']),
            // Rotation & Frequency settings
            'ad_rotation_mode' => Setting::get('ad_rotation_mode', 'weighted'),
            'global_max_impressions_per_ip_per_day' => Setting::get('global_max_impressions_per_ip_per_day', null),
            'global_max_clicks_per_ip_per_day' => Setting::get('global_max_clicks_per_ip_per_day', null),
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
                    'payout_cycle' => 'required|in:net7,net15,net30',
                ]);
                
                Setting::set('minimum_payout', $request->minimum_payout, 'float', 'payout');
                Setting::set('payout_cycle', $request->payout_cycle, 'string', 'payout');
                
                return back()->with('success', 'Payout settings updated successfully.');
                
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
                
            default:
                return back()->withErrors(['error' => 'Invalid section.']);
        }
    }
}
