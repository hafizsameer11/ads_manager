<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Revenue Settings
            ['key' => 'admin_share_percentage', 'value' => '20', 'type' => 'float', 'group' => 'revenue', 'description' => 'Admin share percentage (20%)'],
            ['key' => 'publisher_share_percentage', 'value' => '80', 'type' => 'float', 'group' => 'revenue', 'description' => 'Publisher share percentage (80%)'],
            
            // Payment Settings
            ['key' => 'minimum_deposit', 'value' => '10', 'type' => 'float', 'group' => 'payment', 'description' => 'Minimum deposit amount'],
            ['key' => 'minimum_payout', 'value' => '50', 'type' => 'float', 'group' => 'payment', 'description' => 'Minimum payout amount'],
            ['key' => 'payout_cycle', 'value' => 'net7', 'type' => 'string', 'group' => 'payment', 'description' => 'Payout cycle (net7, net15, net30)'],
            
            // Referral Settings
            ['key' => 'referral_commission_rate', 'value' => '5', 'type' => 'float', 'group' => 'referral', 'description' => 'Referral commission rate (%)'],
            ['key' => 'referral_deposit_bonus_rate', 'value' => '5', 'type' => 'float', 'group' => 'referral', 'description' => 'Referral deposit bonus rate (%)'],
            
            // Campaign Settings
            ['key' => 'campaign_auto_approval', 'value' => 'false', 'type' => 'boolean', 'group' => 'campaign', 'description' => 'Auto-approve campaigns'],
            ['key' => 'default_cpm_rate', 'value' => '1.00', 'type' => 'float', 'group' => 'campaign', 'description' => 'Default CPM rate'],
            ['key' => 'default_cpc_rate', 'value' => '0.10', 'type' => 'float', 'group' => 'campaign', 'description' => 'Default CPC rate'],
            
            // Fraud Detection Settings
            ['key' => 'max_clicks_per_ip_per_hour', 'value' => '10', 'type' => 'integer', 'group' => 'fraud', 'description' => 'Maximum clicks per IP per hour'],
            ['key' => 'max_clicks_per_ip_per_campaign_per_hour', 'value' => '5', 'type' => 'integer', 'group' => 'fraud', 'description' => 'Maximum clicks per IP per campaign per hour'],
            ['key' => 'enable_vpn_detection', 'value' => 'true', 'type' => 'boolean', 'group' => 'fraud', 'description' => 'Enable VPN detection'],
            ['key' => 'enable_proxy_detection', 'value' => 'true', 'type' => 'boolean', 'group' => 'fraud', 'description' => 'Enable proxy detection'],
            
            // General Settings
            ['key' => 'site_name', 'value' => 'Ad Network', 'type' => 'string', 'group' => 'general', 'description' => 'Site name'],
            ['key' => 'site_email', 'value' => 'admin@adsnetwork.com', 'type' => 'string', 'group' => 'general', 'description' => 'Site email'],
            ['key' => 'currency', 'value' => 'USD', 'type' => 'string', 'group' => 'general', 'description' => 'Default currency'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('Settings seeded successfully.');
    }
}
