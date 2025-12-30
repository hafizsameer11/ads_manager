@extends('website.layouts.main')

@section('title', 'Home - ' . config('app.name'))

@section('description', 'Professional advertising network connecting advertisers with publishers worldwide. Start your journey today!')

@section('content')
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <div class="hero-text">
                    <h1>Smart Network, Powerful Solutions</h1>
                    <p class="subtitle">See what is possible with our platform, re-discover potential</p>
                    <p>
                        We are simply the best paying advertising network specialized in online advertising on the Internet. 
                        We guarantee you that no other ad network will pay better than us! Just register and see for yourself. 
                        Prepare to be astonished!
                    </p>
                </div>

                <div class="login-panel">
                    <h3>Member's Dashboard</h3>
                    @if(session('info'))
                        <div style="padding: 15px; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 6px; color: #0c5460; margin-bottom: 20px;">
                            {{ session('info') }}
                        </div>
                    @endif
                    @if($errors->any())
                        <div style="padding: 15px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 6px; color: #721c24; margin-bottom: 20px;">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif
                    <form action="{{ route('login.submit') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="login">Username or Email</label>
                            <input type="text" id="login" name="login" value="{{ old('login') }}" placeholder="Enter your username or email" required>
                            @error('login')
                                <span style="color: #dc3545; font-size: 14px; margin-top: 5px; display: block;">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" placeholder="Enter your password" required>
                            @error('password')
                                <span style="color: #dc3545; font-size: 14px; margin-top: 5px; display: block;">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-options">
                            <div class="checkbox-group">
                                <input type="checkbox" id="remember" name="remember">
                                <label for="remember">Remember me</label>
                            </div>
                            <a href="{{ route('password.request') }}" class="forgot-password">Reset password</a>
                        </div>
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">Log In</button>
                            <a href="{{ route('register') }}" class="btn btn-success">Register</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Why Choose Us</h2>
            <p class="section-subtitle">
                Discover the advantages that make us the preferred choice for advertisers and publishers
            </p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; margin-top: 50px;">
                <div style="padding: 30px; background: var(--bg-light); border-radius: 12px; text-align: center;">
                    <div style="width: 60px; height: 60px; margin: 0 auto 20px; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">✓</div>
                    <h3 style="font-size: 20px; margin-bottom: 15px; color: var(--text-dark);">High Earnings</h3>
                    <p style="color: var(--text-light); line-height: 1.6;">Competitive rates and reliable payments for publishers</p>
                </div>

                <div style="padding: 30px; background: var(--bg-light); border-radius: 12px; text-align: center;">
                    <div style="width: 60px; height: 60px; margin: 0 auto 20px; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">⚡</div>
                    <h3 style="font-size: 20px; margin-bottom: 15px; color: var(--text-dark);">Fast Performance</h3>
                    <p style="color: var(--text-light); line-height: 1.6;">Lightning-fast ad delivery and optimized campaigns</p>
                </div>

                <div style="padding: 30px; background: var(--bg-light); border-radius: 12px; text-align: center;">
                    <div style="width: 60px; height: 60px; margin: 0 auto 20px; background: linear-gradient(135deg, var(--primary-color), var(--primary-dark)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">🎯</div>
                    <h3 style="font-size: 20px; margin-bottom: 15px; color: var(--text-dark);">Advanced Targeting</h3>
                    <p style="color: var(--text-light); line-height: 1.6;">Reach your target audience with precision and efficiency</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="section" style="background: var(--bg-light);">
        <div class="container">
            <div style="text-align: center; max-width: 700px; margin: 0 auto;">
                <h2 style="font-size: 36px; font-weight: 700; margin-bottom: 20px; color: var(--text-dark);">Ready to Get Started?</h2>
                <p style="font-size: 18px; color: var(--text-light); margin-bottom: 30px;">
                    Join thousands of advertisers and publishers who trust our platform. Start your journey today!
                </p>
                <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
                    <a href="{{ route('website.advertiser') }}" class="btn btn-primary">For Advertisers</a>
                    <a href="{{ route('website.publisher') }}" class="btn btn-success">For Publishers</a>
                </div>
            </div>
        </div>
    </section>
@endsection
