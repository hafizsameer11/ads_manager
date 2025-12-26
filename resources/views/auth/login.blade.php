@extends('website.layouts.main')

@section('title', 'Login - ' . config('app.name'))

@section('description', 'Login to your account')

@section('content')
    <section class="hero-section" style="min-height: 80vh; display: flex; align-items: center; position: relative;">
        <div class="container" style="position: relative; z-index: 10;">
            <div style="max-width: 500px; margin: 0 auto; position: relative; z-index: 10;">
                <div class="login-panel" style="position: relative; z-index: 10;">
                    <h3>Member's Dashboard</h3>
                    
                    @if(session('success'))
                        <div style="padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 6px; color: #155724; margin-bottom: 20px;">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div style="padding: 15px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 6px; color: #721c24; margin-bottom: 20px;">
                            <ul style="margin: 0; padding-left: 20px;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('login.submit') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Enter your email" required autofocus>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" placeholder="Enter your password" required>
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

                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1); text-align: center;">
                        <p style="font-size: 14px; color: rgba(255, 255, 255, 0.8);">
                            Don't have an account? 
                            <a href="{{ route('register') }}" style="color: var(--primary-light); text-decoration: none; font-weight: 500;">Sign up here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

