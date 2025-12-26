@extends('website.layouts.main')

@section('title', 'Register - ' . config('app.name'))

@section('description', 'Create a new account')

@section('content')
    <section class="hero-section" style="min-height: 80vh; display: flex; align-items: center; position: relative;">
        <div class="container" style="position: relative; z-index: 10;">
            <div style="max-width: 600px; margin: 0 auto; position: relative; z-index: 10;">
                <div class="login-panel" style="position: relative; z-index: 10;">
                    <h3>Create Account</h3>
                    
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

                    <form action="{{ route('register.submit') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Enter your full name" required autofocus>
                        </div>
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" value="{{ old('username') }}" placeholder="Enter your username" required>
                            <small style="display: block; margin-top: 5px; font-size: 12px; color: rgba(255, 255, 255, 0.7);">Choose a unique username</small>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Enter your email" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        </div>
                        <div class="form-group">
                            <label for="password_confirmation">Confirm Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirm your password" required>
                        </div>
                        <div class="form-group">
                            <label for="role">I want to register as:</label>
                            <select id="role" name="role" class="form-control" style="width: 100%; padding: 12px 16px; background-color: #2c3e50; border: 1px solid #3a556b; border-radius: 6px; color: white; font-size: 14px; font-family: inherit; cursor: pointer;" required>
                                <option value="">Select your role</option>
                                <option value="publisher" {{ old('role') == 'publisher' ? 'selected' : '' }}>Publisher (I want to monetize my website)</option>
                                <option value="advertiser" {{ old('role') == 'advertiser' ? 'selected' : '' }}>Advertiser (I want to advertise)</option>
                            </select>
                        </div>
                        @if(isset($referralCode) && $referralCode)
                            <div class="form-group">
                                <label for="referral_code">Referral Code (Optional)</label>
                                <input type="text" id="referral_code" name="referral_code" value="{{ $referralCode }}" placeholder="Enter referral code" readonly style="background-color: #2c3e50; opacity: 0.7;">
                                <small style="display: block; margin-top: 5px; font-size: 12px; color: rgba(255, 255, 255, 0.7);">You were referred by someone!</small>
                            </div>
                        @else
                            <div class="form-group">
                                <label for="referral_code">Referral Code (Optional)</label>
                                <input type="text" id="referral_code" name="referral_code" value="{{ old('referral_code') }}" placeholder="Enter referral code if you have one">
                                <small style="display: block; margin-top: 5px; font-size: 12px; color: rgba(255, 255, 255, 0.7;">Have a referral code? Enter it here to get rewards!</small>
                            </div>
                        @endif
                        <div style="margin-bottom: 20px;">
                            <div class="checkbox-group">
                                <input type="checkbox" id="terms" name="terms" required>
                                <label for="terms" style="font-size: 14px;">I agree to the <a href="#" style="color: var(--primary-light);">Terms of Service</a> and <a href="#" style="color: var(--primary-light);">Privacy Policy</a></label>
                            </div>
                        </div>
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">Register</button>
                            <a href="{{ route('login') }}" class="btn btn-secondary">Already have account? Login</a>
                        </div>
                    </form>

                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1); text-align: center;">
                        <p style="font-size: 14px; color: rgba(255, 255, 255, 0.8);">
                            Already have an account? 
                            <a href="{{ route('login') }}" style="color: var(--primary-light); text-decoration: none; font-weight: 500;">Login here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

