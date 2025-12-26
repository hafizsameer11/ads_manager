@extends('website.layouts.main')

@section('title', 'For Advertisers - ' . config('app.name'))

@section('description', 'Reach your target audience with our powerful advertising platform. Start your campaign today!')

@section('content')
    <section class="section" style="padding-top: 100px;">
        <div class="container">
            <h1 class="section-title">For Advertisers</h1>
            <p class="section-subtitle">
                Maximize your reach and achieve your marketing goals with our comprehensive advertising solutions
            </p>

            <div style="max-width: 900px; margin: 0 auto;">
                <div style="background: white; padding: 40px; border-radius: 12px; box-shadow: var(--shadow-md); margin-bottom: 30px;">
                    <h2 style="font-size: 28px; margin-bottom: 20px; color: var(--text-dark);">Why Advertise With Us?</h2>
                    <ul style="list-style: none; padding: 0;">
                        <li style="padding: 15px 0; border-bottom: 1px solid var(--border-color); display: flex; align-items: start; gap: 15px;">
                            <span style="color: var(--primary-color); font-size: 24px;">✓</span>
                            <div>
                                <h3 style="font-size: 18px; margin-bottom: 8px; color: var(--text-dark);">Advanced Targeting</h3>
                                <p style="color: var(--text-light); line-height: 1.6;">Reach your exact target audience with our sophisticated targeting options</p>
                            </div>
                        </li>
                        <li style="padding: 15px 0; border-bottom: 1px solid var(--border-color); display: flex; align-items: start; gap: 15px;">
                            <span style="color: var(--primary-color); font-size: 24px;">✓</span>
                            <div>
                                <h3 style="font-size: 18px; margin-bottom: 8px; color: var(--text-dark);">Real-Time Analytics</h3>
                                <p style="color: var(--text-light); line-height: 1.6;">Track your campaigns with detailed analytics and reporting tools</p>
                            </div>
                        </li>
                        <li style="padding: 15px 0; border-bottom: 1px solid var(--border-color); display: flex; align-items: start; gap: 15px;">
                            <span style="color: var(--primary-color); font-size: 24px;">✓</span>
                            <div>
                                <h3 style="font-size: 18px; margin-bottom: 8px; color: var(--text-dark);">Competitive Pricing</h3>
                                <p style="color: var(--text-light); line-height: 1.6;">Get the best value for your advertising budget</p>
                            </div>
                        </li>
                        <li style="padding: 15px 0; display: flex; align-items: start; gap: 15px;">
                            <span style="color: var(--primary-color); font-size: 24px;">✓</span>
                            <div>
                                <h3 style="font-size: 18px; margin-bottom: 8px; color: var(--text-dark);">24/7 Support</h3>
                                <p style="color: var(--text-light); line-height: 1.6;">Our dedicated support team is always ready to help you</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <div style="text-align: center; margin-top: 40px;">
                    <a href="{{ route('register') }}" class="btn btn-primary" style="font-size: 16px; padding: 15px 40px;">Get Started Today</a>
                </div>
            </div>
        </div>
    </section>
@endsection




