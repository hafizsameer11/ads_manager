@extends('dashboard.layouts.main')

@section('title', 'Settings - Admin Dashboard')

@push('styles')
<style>
    /* Success Alert Styles */
    .success-alert {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        padding: 16px 20px;
        border-left: 4px solid var(--success-color);
        background-color: #f0fdf4;
        border-radius: var(--border-radius);
        margin-bottom: 24px;
        position: relative;
    }

    .success-alert .alert-icon {
        flex-shrink: 0;
        font-size: 24px;
        color: var(--success-color);
        margin-top: 2px;
    }

    .success-alert .alert-content {
        flex: 1;
    }

    .success-alert .alert-content strong {
        font-size: 16px;
        font-weight: 600;
        color: var(--success-color);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .success-alert .alert-content p {
        color: var(--text-primary);
        margin: 0;
    }

    .success-alert .close {
        position: absolute;
        top: 16px;
        right: 16px;
        font-size: 20px;
        opacity: 0.6;
        cursor: pointer;
    }

    .success-alert .close:hover {
        opacity: 1;
    }
</style>
@endpush

@section('content')
    @if(session('success'))
        <div class="success-alert">
            <div class="alert-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="alert-content">
                <strong><i class="fas fa-check"></i> Success!</strong>
                <p style="margin: 4px 0 0 0;">{{ session('success') }}</p>
            </div>
            <button type="button" class="close" onclick="this.parentElement.style.display='none'">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="alert-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="alert-content">
                <strong><i class="fas fa-times-circle"></i> Error!</strong>
                <ul class="mb-0 mt-2" style="list-style: none; padding-left: 0;">
                    @foreach($errors->all() as $error)
                        <li style="padding: 4px 0;"><i class="fas fa-chevron-right" style="font-size: 10px; margin-right: 8px;"></i>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
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
            <h3 class="card-title">Payout Settings (Publishers)</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.admin.settings.update') }}">
                @csrf
                <input type="hidden" name="section" value="payout">
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="minimum_payout">Minimum Payout Amount</label>
                            <input type="number" id="minimum_payout" name="minimum_payout" class="form-control" 
                                   value="{{ $settings['minimum_payout'] }}" min="1" step="0.01" required>
                            <small class="text-muted">Minimum amount required for withdrawal requests</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="maximum_payout">Maximum Payout Amount</label>
                            <input type="number" id="maximum_payout" name="maximum_payout" class="form-control" 
                                   value="{{ $settings['maximum_payout'] ?? 10000 }}" min="1" step="0.01" required>
                            <small class="text-muted">Maximum amount allowed per withdrawal request</small>
                        </div>
                    </div>
                    <div class="col-md-4">
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

    <!-- Deposit Settings -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Deposit Settings (Advertisers)</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.admin.settings.update') }}">
                @csrf
                <input type="hidden" name="section" value="deposit">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="minimum_deposit">Minimum Deposit Amount</label>
                            <input type="number" id="minimum_deposit" name="minimum_deposit" class="form-control" 
                                   value="{{ $settings['minimum_deposit'] ?? 10 }}" min="1" step="0.01" required>
                            <small class="text-muted">Minimum amount required for deposit requests by advertisers</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="maximum_deposit">Maximum Deposit Amount</label>
                            <input type="number" id="maximum_deposit" name="maximum_deposit" class="form-control" 
                                   value="{{ $settings['maximum_deposit'] ?? 50000 }}" min="1" step="0.01" required>
                            <small class="text-muted">Maximum amount allowed per deposit request by advertisers</small>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Save Deposit Settings</button>
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

    <!-- Stripe Payment Gateway Configuration -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Stripe Payment Gateway Configuration</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.admin.settings.update') }}">
                @csrf
                <input type="hidden" name="section" value="stripe">
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="stripe_enabled" value="1" {{ $settings['stripe_enabled'] ? 'checked' : '' }}>
                        <strong>Enable Stripe Payment Gateway</strong>
                    </label>
                    <small class="form-text text-muted">When enabled, Stripe will be available as a payment option for advertisers.</small>
                </div>
                
                <hr style="margin: 20px 0;">
                
                <div class="form-group">
                    <label for="stripe_publishable_key">Stripe Publishable Key</label>
                    <input type="text" id="stripe_publishable_key" name="stripe_publishable_key" 
                           class="form-control" 
                           value="{{ $settings['stripe_publishable_key'] }}" 
                           placeholder="pk_test_... or pk_live_...">
                    <small class="form-text text-muted">Your Stripe publishable key (starts with pk_test_ or pk_live_)</small>
                </div>
                
                <div class="form-group">
                    <label for="stripe_secret_key">Stripe Secret Key</label>
                    <input type="password" id="stripe_secret_key" name="stripe_secret_key" 
                           class="form-control" 
                           value="{{ $settings['stripe_secret_key'] }}" 
                           placeholder="sk_test_... or sk_live_...">
                    <small class="form-text text-muted">Your Stripe secret key (starts with sk_test_ or sk_live_)</small>
                </div>
                
                <div class="form-group">
                    <label for="stripe_webhook_secret">Stripe Webhook Secret</label>
                    <input type="password" id="stripe_webhook_secret" name="stripe_webhook_secret" 
                           class="form-control" 
                           value="{{ $settings['stripe_webhook_secret'] }}" 
                           placeholder="whsec_...">
                    <small class="form-text text-muted">Your Stripe webhook signing secret (starts with whsec_). Used to verify webhook events.</small>
                </div>
                
                <div class="alert alert-info">
                    <strong><i class="fas fa-info-circle"></i> Note:</strong>
                    <ul style="margin-bottom: 0; padding-left: 20px;">
                        <li>Use test keys (pk_test_/sk_test_) for testing and live keys (pk_live_/sk_live_) for production.</li>
                        <li>Webhook endpoint URL: <code>{{ url('/webhooks/stripe') }}</code></li>
                        <li>Configure this URL in your Stripe Dashboard under Developers → Webhooks.</li>
                    </ul>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Stripe Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- PayPal Payment Gateway Configuration -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">PayPal Payment Gateway Configuration</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.admin.settings.update') }}">
                @csrf
                <input type="hidden" name="section" value="paypal">
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="paypal_enabled" value="1" {{ $settings['paypal_enabled'] ? 'checked' : '' }}>
                        <strong>Enable PayPal Payment Gateway</strong>
                    </label>
                    <small class="form-text text-muted">When enabled, PayPal will be available as a payment option for advertisers.</small>
                </div>
                
                <hr style="margin: 20px 0;">
                
                <div class="form-group">
                    <label for="paypal_mode">PayPal Mode</label>
                    <select id="paypal_mode" name="paypal_mode" class="form-control">
                        <option value="sandbox" {{ $settings['paypal_mode'] == 'sandbox' ? 'selected' : '' }}>Sandbox (Testing)</option>
                        <option value="live" {{ $settings['paypal_mode'] == 'live' ? 'selected' : '' }}>Live (Production)</option>
                    </select>
                    <small class="form-text text-muted">Use Sandbox for testing and Live for production.</small>
                </div>
                
                <div class="form-group">
                    <label for="paypal_client_id">PayPal Client ID</label>
                    <input type="text" id="paypal_client_id" name="paypal_client_id" 
                           class="form-control" 
                           value="{{ $settings['paypal_client_id'] }}" 
                           placeholder="Your PayPal Client ID">
                    <small class="form-text text-muted">Your PayPal application Client ID from PayPal Developer Dashboard.</small>
                </div>
                
                <div class="form-group">
                    <label for="paypal_secret">PayPal Secret</label>
                    <input type="password" id="paypal_secret" name="paypal_secret" 
                           class="form-control" 
                           value="{{ $settings['paypal_secret'] }}" 
                           placeholder="Your PayPal Secret">
                    <small class="form-text text-muted">Your PayPal application Secret from PayPal Developer Dashboard.</small>
                </div>
                
                <div class="alert alert-info">
                    <strong><i class="fas fa-info-circle"></i> Note:</strong>
                    <ul style="margin-bottom: 0; padding-left: 20px;">
                        <li>Get your credentials from <a href="https://developer.paypal.com/" target="_blank">PayPal Developer Dashboard</a>.</li>
                        <li>Create a new app in your PayPal developer account to get Client ID and Secret.</li>
                        <li>Webhook endpoint URL: <code>{{ url('/webhooks/paypal') }}</code></li>
                        <li>Configure this URL in your PayPal app settings for webhook notifications.</li>
                    </ul>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save PayPal Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- CoinPayments Payment Gateway Configuration -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">CoinPayments Payment Gateway Configuration</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.admin.settings.update') }}">
                @csrf
                <input type="hidden" name="section" value="coinpayments">
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="coinpayments_enabled" value="1" {{ $settings['coinpayments_enabled'] ? 'checked' : '' }}>
                        <strong>Enable CoinPayments Payment Gateway</strong>
                    </label>
                    <small class="form-text text-muted">When enabled, CoinPayments will be available as a payment option for advertisers.</small>
                </div>
                
                <hr style="margin: 20px 0;">
                
                <div class="form-group">
                    <label for="coinpayments_merchant_id">CoinPayments Merchant ID</label>
                    <input type="text" id="coinpayments_merchant_id" name="coinpayments_merchant_id" 
                           class="form-control" 
                           value="{{ $settings['coinpayments_merchant_id'] }}" 
                           placeholder="Your Merchant ID">
                    <small class="form-text text-muted">Your CoinPayments Merchant ID from your account.</small>
                </div>
                
                <div class="form-group">
                    <label for="coinpayments_public_key">CoinPayments Public Key</label>
                    <input type="text" id="coinpayments_public_key" name="coinpayments_public_key" 
                           class="form-control" 
                           value="{{ $settings['coinpayments_public_key'] }}" 
                           placeholder="Your Public Key">
                    <small class="form-text text-muted">Your CoinPayments API Public Key.</small>
                </div>
                
                <div class="form-group">
                    <label for="coinpayments_private_key">CoinPayments Private Key</label>
                    <input type="password" id="coinpayments_private_key" name="coinpayments_private_key" 
                           class="form-control" 
                           value="{{ $settings['coinpayments_private_key'] }}" 
                           placeholder="Your Private Key">
                    <small class="form-text text-muted">Your CoinPayments API Private Key (keep this secure).</small>
                </div>
                
                <div class="form-group">
                    <label for="coinpayments_ipn_secret">CoinPayments IPN Secret</label>
                    <input type="password" id="coinpayments_ipn_secret" name="coinpayments_ipn_secret" 
                           class="form-control" 
                           value="{{ $settings['coinpayments_ipn_secret'] }}" 
                           placeholder="Your IPN Secret">
                    <small class="form-text text-muted">Your CoinPayments IPN (Instant Payment Notification) Secret for verifying IPN requests.</small>
                </div>
                
                <div class="alert alert-info">
                    <strong><i class="fas fa-info-circle"></i> Note:</strong>
                    <ul style="margin-bottom: 0; padding-left: 20px;">
                        <li>Get your credentials from <a href="https://www.coinpayments.net/" target="_blank">CoinPayments Account</a>.</li>
                        <li>Navigate to Account → API Keys to generate your Public and Private keys.</li>
                        <li>IPN endpoint URL: <code>{{ url('/webhooks/coinpayments') }}</code></li>
                        <li>Configure this URL in your CoinPayments account settings for IPN notifications.</li>
                    </ul>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save CoinPayments Settings
                    </button>
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

    <!-- Referral / Affiliate Program Settings -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Referral / Affiliate Program Settings</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.admin.settings.update') }}">
                @csrf
                <input type="hidden" name="section" value="referral">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="referral_commission_rate">Referral Commission Rate (%) <span class="text-danger">*</span></label>
                            <input type="number" id="referral_commission_rate" name="referral_commission_rate" class="form-control" 
                                   value="{{ $settings['referral_commission_rate'] }}" min="0" max="100" step="0.01" required>
                            <small class="text-muted">
                                Percentage of publisher earnings that the referrer receives as commission. 
                                For example, if set to 5%, and a referred publisher earns $100, the referrer gets $5.
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="referral_deposit_bonus_rate">Referral Deposit Bonus Rate (%) <span class="text-danger">*</span></label>
                            <input type="number" id="referral_deposit_bonus_rate" name="referral_deposit_bonus_rate" class="form-control" 
                                   value="{{ $settings['referral_deposit_bonus_rate'] }}" min="0" max="100" step="0.01" required>
                            <small class="text-muted">
                                Percentage of advertiser deposits that the referrer receives as a bonus. 
                                For example, if set to 5%, and a referred advertiser deposits $1000, the referrer gets $50.
                            </small>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <strong><i class="fas fa-info-circle"></i> How Referral Program Works:</strong>
                            <ul style="margin-bottom: 0; padding-left: 20px; margin-top: 10px;">
                                <li><strong>Publisher Referrals:</strong> When a publisher signs up using a referral link, the referrer earns a commission on all future earnings of the referred publisher.</li>
                                <li><strong>Advertiser Referrals:</strong> When an advertiser signs up using a referral link and makes a deposit, the referrer earns a one-time bonus based on the deposit amount.</li>
                                <li><strong>Earnings:</strong> All referral earnings are credited to the referrer's publisher balance (if they are a publisher).</li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Referral Settings
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- SMTP Settings -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">SMTP Email Settings</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.admin.settings.update') }}">
                @csrf
                <input type="hidden" name="section" value="smtp">
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_active" value="1" {{ $smtpSettings->is_active ?? false ? 'checked' : '' }}>
                        <strong>Activate SMTP Configuration</strong>
                    </label>
                    <small class="form-text text-muted d-block">When enabled, this SMTP configuration will be used for sending emails. Only one SMTP configuration can be active at a time.</small>
                </div>
                
                <hr style="margin: 20px 0;">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="mailer">Mailer <span class="text-danger">*</span></label>
                            <select id="mailer" name="mailer" class="form-control" required>
                                <option value="smtp" {{ ($smtpSettings->mailer ?? 'smtp') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                                <option value="sendmail" {{ ($smtpSettings->mailer ?? '') == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                <option value="mailgun" {{ ($smtpSettings->mailer ?? '') == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                <option value="ses" {{ ($smtpSettings->mailer ?? '') == 'ses' ? 'selected' : '' }}>Amazon SES</option>
                                <option value="postmark" {{ ($smtpSettings->mailer ?? '') == 'postmark' ? 'selected' : '' }}>Postmark</option>
                                <option value="log" {{ ($smtpSettings->mailer ?? '') == 'log' ? 'selected' : '' }}>Log (Testing)</option>
                                <option value="array" {{ ($smtpSettings->mailer ?? '') == 'array' ? 'selected' : '' }}>Array (Testing)</option>
                            </select>
                            <small class="form-text text-muted">The mail transport driver to use</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="host">SMTP Host</label>
                            <input type="text" id="host" name="host" class="form-control" 
                                   value="{{ $smtpSettings->host ?? '' }}" 
                                   placeholder="smtp.mailtrap.io">
                            <small class="form-text text-muted">SMTP server hostname (e.g., smtp.gmail.com, smtp.mailtrap.io)</small>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="port">SMTP Port</label>
                            <input type="number" id="port" name="port" class="form-control" 
                                   value="{{ $smtpSettings->port ?? 587 }}" 
                                   min="1" max="65535" placeholder="587">
                            <small class="form-text text-muted">Common ports: 587 (TLS), 465 (SSL), 25</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="encryption">Encryption</label>
                            <select id="encryption" name="encryption" class="form-control">
                                <option value="">None</option>
                                <option value="tls" {{ ($smtpSettings->encryption ?? 'tls') == 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ ($smtpSettings->encryption ?? '') == 'ssl' ? 'selected' : '' }}>SSL</option>
                            </select>
                            <small class="form-text text-muted">Encryption method (leave as None for unencrypted connections)</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="timeout">Timeout (seconds)</label>
                            <input type="number" id="timeout" name="timeout" class="form-control" 
                                   value="{{ $smtpSettings->timeout ?? '' }}" 
                                   min="1" placeholder="30">
                            <small class="form-text text-muted">Connection timeout in seconds</small>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="username">SMTP Username</label>
                            <input type="text" id="username" name="username" class="form-control" 
                                   value="{{ $smtpSettings->username ?? '' }}" 
                                   placeholder="your-email@example.com">
                            <small class="form-text text-muted">SMTP authentication username</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password">SMTP Password</label>
                            <input type="password" id="password" name="password" class="form-control" 
                                   value="{{ $smtpSettings->password ?? '' }}" 
                                   placeholder="Your SMTP password">
                            <small class="form-text text-muted">SMTP authentication password (leave blank to keep current)</small>
                        </div>
                    </div>
                </div>
                
                <hr style="margin: 20px 0;">
                
                <h5>From Address Settings</h5>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="from_address">From Email Address</label>
                            <input type="email" id="from_address" name="from_address" class="form-control" 
                                   value="{{ $smtpSettings->from_address ?? '' }}" 
                                   placeholder="noreply@example.com">
                            <small class="form-text text-muted">Default sender email address</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="from_name">From Name</label>
                            <input type="text" id="from_name" name="from_name" class="form-control" 
                                   value="{{ $smtpSettings->from_name ?? '' }}" 
                                   placeholder="Your Company Name">
                            <small class="form-text text-muted">Default sender name</small>
                        </div>
                    </div>
                </div>
                
                <h5 style="margin-top: 20px;">Reply-To Settings (Optional)</h5>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="reply_to_address">Reply-To Email Address</label>
                            <input type="email" id="reply_to_address" name="reply_to_address" class="form-control" 
                                   value="{{ $smtpSettings->reply_to_address ?? '' }}" 
                                   placeholder="support@example.com">
                            <small class="form-text text-muted">Email address for replies (optional)</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="reply_to_name">Reply-To Name</label>
                            <input type="text" id="reply_to_name" name="reply_to_name" class="form-control" 
                                   value="{{ $smtpSettings->reply_to_name ?? '' }}" 
                                   placeholder="Support Team">
                            <small class="form-text text-muted">Name for reply-to address (optional)</small>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="local_domain">Local Domain</label>
                    <input type="text" id="local_domain" name="local_domain" class="form-control" 
                           value="{{ $smtpSettings->local_domain ?? '' }}" 
                           placeholder="example.com">
                    <small class="form-text text-muted">The domain name to use for EHLO/HELO commands (optional)</small>
                </div>
                
                <div class="alert alert-info">
                    <strong><i class="fas fa-info-circle"></i> Important Notes:</strong>
                    <ul style="margin-bottom: 0; padding-left: 20px; margin-top: 10px;">
                        <li>These SMTP settings are stored in the database and will override .env file settings when active.</li>
                        <li>Only one SMTP configuration can be active at a time.</li>
                        <li>For Gmail, you may need to use an "App Password" instead of your regular password.</li>
                        <li>For security, passwords are stored in plain text in the database. Ensure your database is properly secured.</li>
                        <li>Test your SMTP configuration after saving to ensure emails are sent correctly.</li>
                    </ul>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save SMTP Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
