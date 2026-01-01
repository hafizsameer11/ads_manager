@php
    $userRole = 'admin'; // Default to admin for now - can be set based on auth user role later
    $currentRoute = request()->route()->getName() ?? '';
    
    // Determine role from route if available
    if (str_contains($currentRoute, 'dashboard.admin')) {
        $userRole = 'admin';
    } elseif (str_contains($currentRoute, 'dashboard.advertiser')) {
        $userRole = 'advertiser';
    } elseif (str_contains($currentRoute, 'dashboard.publisher')) {
        $userRole = 'publisher';
    }
@endphp

<aside class="dashboard-sidebar">
    <div class="sidebar-header">
        <a href="{{ route('dashboard.' . $userRole . '.home') }}" class="sidebar-logo">
            <div class="logo-icon">
                <svg width="28" height="28" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="16" cy="16" r="15" stroke="currentColor" stroke-width="2"/>
                    <path d="M12 10 L20 16 L12 22 Z" fill="currentColor"/>
                </svg>
            </div>
            <span class="logo-text">AdNetwork</span>
        </a>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-menu">
            @if($userRole === 'admin')
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.admin.home') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.admin.home') }}" class="nav-link">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.admin.users') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.admin.users') }}" class="nav-link">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                        @if(isset($userNotifications) && $userNotifications > 0)
                            <span class="sidebar-badge">New</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.admin.websites') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.admin.websites') }}" class="nav-link">
                        <i class="fas fa-globe"></i>
                        <span>Websites</span>
                        @if(isset($websiteNotifications) && $websiteNotifications > 0)
                            <span class="sidebar-badge">New</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.admin.ad-units') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.admin.ad-units') }}" class="nav-link">
                        <i class="fas fa-ad"></i>
                        <span>Ad Units</span>
                    </a>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.admin.campaigns') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.admin.campaigns') }}" class="nav-link">
                        <i class="fas fa-bullhorn"></i>
                        <span>Campaigns</span>
                        @if(isset($campaignNotifications) && $campaignNotifications > 0)
                            <span class="sidebar-badge">New</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.admin.deposits') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.admin.deposits') }}" class="nav-link">
                        <i class="fas fa-money-bill-alt"></i>
                        <span>Deposits</span>
                        @if(isset($paymentNotifications) && $paymentNotifications > 0)
                            <span class="sidebar-badge">New</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.admin.withdrawals') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.admin.withdrawals') }}" class="nav-link">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Withdrawals</span>
                        @if(isset($withdrawalNotifications) && $withdrawalNotifications > 0)
                            <span class="sidebar-badge">New</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.admin.invoices') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.admin.invoices') }}" class="nav-link">
                        <i class="fas fa-file-invoice"></i>
                        <span>Invoices</span>
                    </a>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.admin.activity-logs') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.admin.activity-logs') }}" class="nav-link">
                        <i class="fas fa-history"></i>
                        <span>Activity Logs</span>
                    </a>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.admin.reports') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.admin.reports') }}" class="nav-link">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports</span>
                    </a>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.admin.contact-messages') || str_contains($currentRoute, 'dashboard.admin.abuse-reports') || str_contains($currentRoute, 'dashboard.admin.dmca-reports') ? 'active open' : '' }}">
                    <a href="#" class="nav-link" onclick="event.preventDefault(); const parent = this.closest('.nav-item'); parent.classList.toggle('open');">
                        <i class="fas fa-comments"></i>
                        <span>User Messages</span>
                        <i class="fas fa-chevron-down" style="margin-left: auto; font-size: 10px; transition: transform 0.3s;"></i>
                    </a>
                    <ul class="nav-submenu">
                        <li class="nav-subitem {{ str_contains($currentRoute, 'dashboard.admin.contact-messages') ? 'active' : '' }}">
                            <a href="{{ route('dashboard.admin.contact-messages') }}" class="nav-sublink">
                                <i class="fas fa-envelope"></i>
                                <span>Contact Messages</span>
                                @if(isset($contactNotifications) && $contactNotifications > 0)
                                    <span class="sidebar-badge">New</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-subitem {{ str_contains($currentRoute, 'dashboard.admin.abuse-reports') ? 'active' : '' }}">
                            <a href="{{ route('dashboard.admin.abuse-reports') }}" class="nav-sublink">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Abuse Reports</span>
                            </a>
                        </li>
                        <li class="nav-subitem {{ str_contains($currentRoute, 'dashboard.admin.dmca-reports') ? 'active' : '' }}">
                            <a href="{{ route('dashboard.admin.dmca-reports') }}" class="nav-sublink">
                                <i class="fas fa-copyright"></i>
                                <span>DMCA Reports</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.admin.manual-payment-accounts') || str_contains($currentRoute, 'dashboard.admin.allowed-account-types') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.admin.manual-payment-accounts.index') }}" class="nav-link">
                        <i class="fas fa-credit-card"></i>
                        <span>Payment Accounts</span>
                    </a>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.admin.target-countries') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.admin.target-countries.index') }}" class="nav-link">
                        <i class="fas fa-globe-americas"></i>
                        <span>Target Countries</span>
                    </a>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.admin.notifications') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.admin.notifications.index') }}" class="nav-link">
                        <i class="fas fa-bell"></i>
                        <span>Notifications</span>
                    </a>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.admin.roles') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.admin.roles.index') }}" class="nav-link">
                        <i class="fas fa-user-shield"></i>
                        <span>Roles & Permissions</span>
                    </a>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.admin.security') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.admin.security.two-factor') }}" class="nav-link">
                        <i class="fas fa-shield-alt"></i>
                        <span>Security (2FA)</span>
                    </a>
                </li>
                @if(Auth::check() && Auth::user()->hasPermission('manage_settings'))
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.admin.announcements') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.admin.announcements.index') }}" class="nav-link">
                        <i class="fas fa-bullhorn"></i>
                        <span>Announcements</span>
                    </a>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.admin.email-templates') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.admin.email-templates.index') }}" class="nav-link">
                        <i class="fas fa-envelope-open-text"></i>
                        <span>Email Templates</span>
                    </a>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.admin.pages') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.admin.pages.index') }}" class="nav-link">
                        <i class="fas fa-file-alt"></i>
                        <span>Pages</span>
                    </a>
                </li>
                @endif
                @if(Auth::check() && Auth::user()->hasAnyPermission(['manage_users', 'manage_settings', 'view_activity_logs']))
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.admin.support-tickets') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.admin.support-tickets.index') }}" class="nav-link">
                        <i class="fas fa-ticket-alt"></i>
                        <span>Support Tickets</span>
                    </a>
                </li>
                @endif
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.admin.settings') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.admin.settings') }}" class="nav-link">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
            @elseif($userRole === 'advertiser')
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.advertiser.home') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.advertiser.home') }}" class="nav-link">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.advertiser.campaigns') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.advertiser.campaigns') }}" class="nav-link">
                        <i class="fas fa-bullhorn"></i>
                        <span>Campaigns</span>
                    </a>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.advertiser.create-campaign') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.advertiser.create-campaign') }}" class="nav-link">
                        <i class="fas fa-plus-circle"></i>
                        <span>Create Campaign</span>
                    </a>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.advertiser.analytics') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.advertiser.analytics') }}" class="nav-link">
                        <i class="fas fa-chart-line"></i>
                        <span>Analytics</span>
                    </a>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.advertiser.billing') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.advertiser.billing') }}" class="nav-link">
                        <i class="fas fa-credit-card"></i>
                        <span>Billing</span>
                    </a>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.advertiser.support-tickets') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.advertiser.support-tickets.index') }}" class="nav-link">
                        <i class="fas fa-ticket-alt"></i>
                        <span>Support Tickets</span>
                    </a>
                </li>
            @elseif($userRole === 'publisher')
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.publisher.home') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.publisher.home') }}" class="nav-link">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.publisher.sites') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.publisher.sites') }}" class="nav-link">
                        <i class="fas fa-globe"></i>
                        <span>Sites</span>
                    </a>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.publisher.earnings') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.publisher.earnings') }}" class="nav-link">
                        <i class="fas fa-dollar-sign"></i>
                        <span>Earnings</span>
                    </a>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.publisher.statistics') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.publisher.statistics') }}" class="nav-link">
                        <i class="fas fa-chart-pie"></i>
                        <span>Statistics</span>
                    </a>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.publisher.payments') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.publisher.payments') }}" class="nav-link">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Payments</span>
                    </a>
                </li>
                <li class="nav-item {{ str_contains($currentRoute, 'dashboard.publisher.support-tickets') ? 'active' : '' }}">
                    <a href="{{ route('dashboard.publisher.support-tickets.index') }}" class="nav-link">
                        <i class="fas fa-ticket-alt"></i>
                        <span>Support Tickets</span>
                    </a>
                </li>
            @endif
        </ul>
    </nav>

    <div class="sidebar-footer">
        <a href="{{ route('website.home') }}" class="sidebar-link">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Website</span>
        </a>
    </div>
</aside>
