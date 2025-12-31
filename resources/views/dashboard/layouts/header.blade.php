<header class="dashboard-header">
    <div class="header-left">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="breadcrumb">
            @yield('breadcrumb', 'Dashboard')
        </div>
    </div>

    <div class="header-right">
        <div class="header-actions">
            @if(auth()->user()->isAdmin() || auth()->user()->isPublisher() || auth()->user()->isAdvertiser())
            <div class="notification-dropdown">
                <button class="header-icon-btn" id="notificationBtn">
                    <i class="fas fa-bell"></i>
                    <span class="badge" id="notificationBadge" style="display: none;">0</span>
                </button>
                <div class="notification-dropdown-menu" id="notificationDropdown">
                    <div class="notification-header">
                        <h4>Notifications</h4>
                        <button class="mark-all-read-btn" id="markAllReadBtn">Mark all as read</button>
                    </div>
                    <div class="notification-list" id="notificationList">
                        <div class="notification-loading">Loading...</div>
                    </div>
                    <div class="notification-footer">
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('dashboard.admin.notifications.index', ['mark_all_read' => 1]) }}" class="view-all-link" id="viewAllNotificationsLink">View all notifications</a>
                        @elseif(auth()->user()->isPublisher())
                            <a href="{{ route('dashboard.publisher.notifications.index', ['mark_all_read' => 1]) }}" class="view-all-link" id="viewAllNotificationsLink">View all notifications</a>
                        @elseif(auth()->user()->isAdvertiser())
                            <a href="{{ route('dashboard.advertiser.notifications.index', ['mark_all_read' => 1]) }}" class="view-all-link" id="viewAllNotificationsLink">View all notifications</a>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <div class="user-dropdown">
                <button class="user-menu-btn" id="userMenuBtn">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <span class="user-name">{{ auth()->user()->name }}</span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="user-dropdown-menu" id="userDropdown">
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('dashboard.admin.profile') }}" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                        <a href="{{ route('dashboard.admin.settings') }}" class="dropdown-item">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                    @elseif(auth()->user()->isPublisher())
                        <a href="{{ route('dashboard.publisher.profile') }}" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                    @elseif(auth()->user()->isAdvertiser())
                        <a href="{{ route('dashboard.advertiser.profile') }}" class="dropdown-item">
                            <i class="fas fa-user"></i>
                            <span>Profile</span>
                        </a>
                    @endif
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="dropdown-item logout-btn">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>
