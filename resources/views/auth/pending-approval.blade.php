@extends('website.layouts.main')

@section('title', 'Account Pending Approval - ' . config('app.name'))

@section('description', 'Your account is under review')

@section('content')
    <section class="hero-section" style="min-height: 80vh; display: flex; align-items: center; position: relative;">
        <div class="container" style="position: relative; z-index: 10;">
            <div style="max-width: 600px; margin: 0 auto; position: relative; z-index: 10;">
                <div class="login-panel" style="position: relative; z-index: 10; text-align: center;">
                    <div style="margin-bottom: 30px;">
                        <i class="fas fa-clock" style="font-size: 64px; color: #ffc107; margin-bottom: 20px;"></i>
                    </div>
                    
                    <h2 style="color: #333; margin-bottom: 20px;">Account Under Review</h2>
                    
                    <div style="padding: 20px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; margin-bottom: 30px;">
                        <p style="margin: 0; color: #856404; font-size: 16px; line-height: 1.6;">
                            Your account is currently under review by our administration team.
                        </p>
                    </div>
                    
                    <p style="color: #666; margin-bottom: 30px; line-height: 1.6;">
                        We have received your registration and are processing your account. You will be notified by email once an administrator verifies your account.
                    </p>
                    
                    <p style="color: #666; margin-bottom: 30px; line-height: 1.6;">
                        Please check your email inbox (and spam folder) for updates regarding your account status.
                    </p>
                    
                    @if(auth()->check())
                        <form action="{{ route('logout') }}" method="POST" style="margin-top: 30px;">
                            @csrf
                            <button type="submit" class="btn btn-primary" style="padding: 12px 30px; background: #6c757d; border: none; color: #fff; border-radius: 4px; cursor: pointer; font-size: 16px;">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </form>
                    @else
                        <div style="margin-top: 30px;">
                            <a href="{{ route('login') }}" class="btn btn-primary" style="display: inline-block; padding: 12px 30px; background: #007bff; border: none; color: #fff; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 16px;">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

