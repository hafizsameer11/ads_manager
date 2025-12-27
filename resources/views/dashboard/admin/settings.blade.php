@extends('dashboard.layouts.main')

@section('title', 'Settings - Admin Dashboard')

@section('content')
    <div class="page-header">
        <h1>System Settings</h1>
        <p class="text-muted">Configure system-wide settings and preferences.</p>
    </div>

    <!-- Revenue Settings -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Revenue Share Settings</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.admin.settings.update') }}">
                @csrf
                <input type="hidden" name="section" value="revenue">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="admin_percentage">Admin Percentage (%)</label>
                            <input type="number" id="admin_percentage" name="admin_percentage" class="form-control" 
                                   value="{{ $settings['admin_percentage'] }}" min="0" max="100" step="0.01" required>
                            <small class="text-muted">Percentage of revenue that goes to admin</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="publisher_percentage">Publisher Percentage (%)</label>
                            <input type="number" id="publisher_percentage" name="publisher_percentage" class="form-control" 
                                   value="{{ $settings['publisher_percentage'] }}" min="0" max="100" step="0.01" required>
                            <small class="text-muted">Percentage of revenue that goes to publishers</small>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Save Revenue Settings</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Payout Settings -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Payout Settings</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.admin.settings.update') }}">
                @csrf
                <input type="hidden" name="section" value="payout">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="minimum_payout">Minimum Payout Amount</label>
                            <input type="number" id="minimum_payout" name="minimum_payout" class="form-control" 
                                   value="{{ $settings['minimum_payout'] }}" min="1" step="0.01" required>
                            <small class="text-muted">Minimum amount required for withdrawal requests</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="payout_cycle">Payout Cycle</label>
                            <select id="payout_cycle" name="payout_cycle" class="form-control" required>
                                <option value="net7" {{ $settings['payout_cycle'] === 'net7' ? 'selected' : '' }}>Net-7 (Weekly)</option>
                                <option value="net15" {{ $settings['payout_cycle'] === 'net15' ? 'selected' : '' }}>Net-15 (Bi-weekly)</option>
                                <option value="net30" {{ $settings['payout_cycle'] === 'net30' ? 'selected' : '' }}>Net-30 (Monthly)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Save Payout Settings</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Rate Settings -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Default Rate Settings</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.admin.settings.update') }}">
                @csrf
                <input type="hidden" name="section" value="rates">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="default_cpm_rate">Default CPM Rate ($)</label>
                            <input type="number" id="default_cpm_rate" name="default_cpm_rate" class="form-control" 
                                   value="{{ $settings['default_cpm_rate'] }}" min="0" step="0.01">
                            <small class="text-muted">Default cost per mille (per 1000 impressions) rate</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="default_cpc_rate">Default CPC Rate ($)</label>
                            <input type="number" id="default_cpc_rate" name="default_cpc_rate" class="form-control" 
                                   value="{{ $settings['default_cpc_rate'] }}" min="0" step="0.01">
                            <small class="text-muted">Default cost per click rate</small>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Save Rate Settings</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Payment Gateway Settings -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Payment Gateway Settings</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.admin.settings.update') }}">
                @csrf
                <input type="hidden" name="section" value="payment">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="payment_gateways[]" value="paypal" {{ in_array('paypal', $settings['payment_gateways']) ? 'checked' : '' }}> PayPal
                            </label>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="payment_gateways[]" value="coinpayment" {{ in_array('coinpayment', $settings['payment_gateways']) ? 'checked' : '' }}> CoinPayment
                            </label>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="payment_gateways[]" value="faucetpay" {{ in_array('faucetpay', $settings['payment_gateways']) ? 'checked' : '' }}> FaucetPay
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="payment_gateways[]" value="stripe" {{ in_array('stripe', $settings['payment_gateways']) ? 'checked' : '' }}> Stripe
                            </label>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="payment_gateways[]" value="bank_swift" {{ in_array('bank_swift', $settings['payment_gateways']) ? 'checked' : '' }}> Bank SWIFT
                            </label>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="payment_gateways[]" value="wise" {{ in_array('wise', $settings['payment_gateways']) ? 'checked' : '' }}> Wise
                            </label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Save Payment Settings</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Fraud Detection Settings -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Fraud Detection Settings</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.admin.settings.update') }}">
                @csrf
                <input type="hidden" name="section" value="fraud">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="click_limit_per_ip">Click Limit Per IP (per hour)</label>
                            <input type="number" id="click_limit_per_ip" name="click_limit_per_ip" class="form-control" 
                                   value="{{ $settings['click_limit_per_ip'] }}" min="1" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="block_vpn" value="1" {{ $settings['block_vpn'] ? 'checked' : '' }}> Block VPN/Proxy Traffic
                            </label>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="block_bots" value="1" {{ $settings['block_bots'] ? 'checked' : '' }}> Block Bot Traffic
                            </label>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Save Fraud Settings</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Ad Rotation & Frequency Settings -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Ad Rotation & Frequency Control</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.admin.settings.update') }}">
                @csrf
                <input type="hidden" name="section" value="rotation_frequency">
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="ad_rotation_mode">Ad Rotation Mode <span class="text-danger">*</span></label>
                            <select id="ad_rotation_mode" name="ad_rotation_mode" class="form-control" required>
                                <option value="round_robin" {{ $settings['ad_rotation_mode'] == 'round_robin' ? 'selected' : '' }}>Round Robin - Serve campaigns sequentially</option>
                                <option value="weighted" {{ $settings['ad_rotation_mode'] == 'weighted' ? 'selected' : '' }}>Weighted - Serve based on rotation weight</option>
                                <option value="random" {{ $settings['ad_rotation_mode'] == 'random' ? 'selected' : '' }}>Random - Random selection</option>
                            </select>
                            <small class="text-muted">How ads are rotated among multiple campaigns. Campaign-specific rotation weights can be set when creating/editing campaigns.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="global_max_impressions_per_ip_per_day">Global Max Impressions Per IP Per Day</label>
                            <input type="number" id="global_max_impressions_per_ip_per_day" name="global_max_impressions_per_ip_per_day" class="form-control" 
                                   value="{{ $settings['global_max_impressions_per_ip_per_day'] }}" min="1" placeholder="Leave empty for no limit">
                            <small class="text-muted">Global default for all campaigns. Campaign-specific limits override this.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="global_max_clicks_per_ip_per_day">Global Max Clicks Per IP Per Day</label>
                            <input type="number" id="global_max_clicks_per_ip_per_day" name="global_max_clicks_per_ip_per_day" class="form-control" 
                                   value="{{ $settings['global_max_clicks_per_ip_per_day'] }}" min="1" placeholder="Leave empty for no limit">
                            <small class="text-muted">Global default for all campaigns. Campaign-specific limits override this.</small>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <strong>Note:</strong> These are global defaults. Individual campaigns can have their own frequency limits which will override these settings. Frequency limits are applied per IP address to prevent ad fatigue and abuse.
                        </div>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Save Rotation & Frequency Settings</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Publisher Settings -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Publisher Website Approval Settings</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.admin.settings.update') }}">
                @csrf
                <input type="hidden" name="section" value="publisher">
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" id="auto_approve_publisher_websites" name="auto_approve_publisher_websites" class="form-check-input" 
                                       {{ $settings['auto_approve_publisher_websites'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="auto_approve_publisher_websites">
                                    <strong>Auto-approve Publisher Websites</strong>
                                </label>
                            </div>
                            <small class="text-muted d-block mt-2">
                                When enabled, newly added websites by publishers will be automatically approved. 
                                When disabled, admin must manually approve each website.
                            </small>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Save Publisher Settings</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
