@extends('website.layouts.main')

@section('title', 'Forgot Password - ' . config('app.name'))

@section('description', 'Reset your password')

@section('content')
    <section class="hero-section" style="min-height: 80vh; display: flex; align-items: center; position: relative;">
        <div class="container" style="position: relative; z-index: 10;">
            <div style="max-width: 500px; margin: 0 auto; position: relative; z-index: 10;">
                <div class="login-panel" style="position: relative; z-index: 10;">
                    <h3>Reset Password</h3>
                    
                    @if(session('status'))
                        <div style="padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 6px; color: #155724; margin-bottom: 20px;">
                            {{ session('status') }}
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
                        Enter your email address and we'll send you a link to reset your password.
                    </p>

                    <form action="{{ route('password.email') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="Enter your email address" required autofocus>
                        </div>
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">Send Reset Link</button>
                        </div>
                    </form>

                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1); text-align: center;">
                        <p style="font-size: 14px; color: rgba(255, 255, 255, 0.8);">
                            Remember your password? 
                            <a href="{{ route('login') }}" style="color: var(--primary-light); text-decoration: none; font-weight: 500;">Login here</a>
                        </p>
                        <p style="font-size: 14px; color: rgba(255, 255, 255, 0.8); margin-top: 10px;">
                            Don't have an account? 
                            <a href="{{ route('register') }}" style="color: var(--primary-light); text-decoration: none; font-weight: 500;">Sign up here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

