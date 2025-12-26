<header class="main-header">
    <div class="container">
        <div class="header-content">
            <div class="logo-section">
                <a href="{{ route('website.home') }}" class="logo">
                    <div class="logo-icon">
                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="16" cy="16" r="15" stroke="currentColor" stroke-width="2"/>
                            <path d="M12 10 L20 16 L12 22 Z" fill="currentColor"/>
                        </svg>
                    </div>
                    <span class="logo-text">AdNetwork</span>
                </a>
            </div>

            <nav class="main-nav">
                <ul class="nav-list">
                    <li class="nav-item">
                        <a href="{{ route('website.home') }}" class="nav-link {{ request()->routeIs('website.home') ? 'active' : '' }}">
                            Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('website.advertiser') }}" class="nav-link {{ request()->routeIs('website.advertiser') ? 'active' : '' }}">
                            Advertiser
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('website.publisher') }}" class="nav-link {{ request()->routeIs('website.publisher') ? 'active' : '' }}">
                            Publisher
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('website.contact') }}" class="nav-link {{ request()->routeIs('website.contact') ? 'active' : '' }}">
                            Contact Us
                        </a>
                    </li>
                </ul>

                <div class="auth-buttons">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">Dashboard</a>
                        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-secondary">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                        <a href="{{ route('register') }}" class="btn btn-success">Sign Up</a>
                    @endauth
                </div>

                <button class="mobile-menu-toggle" aria-label="Toggle menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </nav>
        </div>
    </div>
</header>




