@extends('website.layouts.main')

@section('title', 'Reset Password - ' . config('app.name'))

@section('description', 'Reset your password')

@section('content')
    <section class="hero-section" style="min-height: 80vh; display: flex; align-items: center; position: relative;">
        <div class="container" style="position: relative; z-index: 10;">
            <div style="max-width: 500px; margin: 0 auto; position: relative; z-index: 10;">
                <div class="login-panel" style="position: relative; z-index: 10;">
                    <h3>Reset Password</h3>
                    
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

                    <p style="color: rgba(255, 255, 255, 0.9); margin-bottom: 25px; line-height: 1.6;">
                        Enter your new password below.
                    </p>

                    <form action="{{ route('password.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="{{ $email ?? old('email') }}" placeholder="Enter your email address" required readonly style="background-color: #2c3e50; opacity: 0.7;">
                        </div>
                        
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" id="password" name="password" placeholder="Enter your new password" required autofocus>
                        </div>
                        
                        <div class="form-group">
                            <label for="password_confirmation">Confirm New Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirm your new password" required>
                        </div>
                        
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">Reset Password</button>
                        </div>
                    </form>

                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1); text-align: center;">
                        <p style="font-size: 14px; color: rgba(255, 255, 255, 0.8);">
                            Remember your password? 
                            <a href="{{ route('login') }}" style="color: var(--primary-light); text-decoration: none; font-weight: 500;">Login here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

