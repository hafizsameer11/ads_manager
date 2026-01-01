@extends('website.layouts.main')

@section('title', 'For Publishers - ' . config('app.name'))

@section('description', 'Monetize your website traffic with our premium advertising network. Start earning today!')

@section('content')
    <section class="section" style="padding-top: 100px;">
        <div class="container" style="width: 90%; max-width: none; margin: 0 auto;">
            <h1 class="section-title">For Publishers</h1>
            <p class="section-subtitle">
                Maximize your revenue and grow your business with our publisher-friendly platform
            </p>

            <div style="width: 100%;">
                <div style="background: white; padding: 40px; border-radius: 12px; box-shadow: var(--shadow-md); margin-bottom: 30px;">
                    <h2 style="font-size: 28px; margin-bottom: 20px; color: var(--text-dark);">Why Publish With Us?</h2>
                    <ul style="list-style: none; padding: 0;">
                        <li style="padding: 15px 0; border-bottom: 1px solid var(--border-color); display: flex; align-items: start; gap: 15px;">
                            <span style="color: var(--success-color); font-size: 24px;">$</span>
                            <div>
                                <h3 style="font-size: 18px; margin-bottom: 8px; color: var(--text-dark);">Highest Payout Rates</h3>
                                <p style="color: var(--text-light); line-height: 1.6;">Earn more with our industry-leading payment rates</p>
                            </div>
                        </li>
                        <li style="padding: 15px 0; border-bottom: 1px solid var(--border-color); display: flex; align-items: start; gap: 15px;">
                            <span style="color: var(--success-color); font-size: 24px;">⚡</span>
                            <div>
                                <h3 style="font-size: 18px; margin-bottom: 8px; color: var(--text-dark);">Fast Payments</h3>
                                <p style="color: var(--text-light); line-height: 1.6;">Get paid on time, every time with reliable payment processing</p>
                            </div>
                        </li>
                        <li style="padding: 15px 0; border-bottom: 1px solid var(--border-color); display: flex; align-items: start; gap: 15px;">
                            <span style="color: var(--success-color); font-size: 24px;">📊</span>
                            <div>
                                <h3 style="font-size: 18px; margin-bottom: 8px; color: var(--text-dark);">Detailed Reports</h3>
                                <p style="color: var(--text-light); line-height: 1.6;">Monitor your earnings with comprehensive analytics dashboard</p>
                            </div>
                        </li>
                        <li style="padding: 15px 0; display: flex; align-items: start; gap: 15px;">
                            <span style="color: var(--success-color); font-size: 24px;">🔧</span>
                            <div>
                                <h3 style="font-size: 18px; margin-bottom: 8px; color: var(--text-dark);">Easy Integration</h3>
                                <p style="color: var(--text-light); line-height: 1.6;">Simple setup and integration with your existing website</p>
                            </div>
                        </li>
                    </ul>
                </div>

                <div style="text-align: center; margin-top: 40px;">
                    <a href="{{ route('register') }}" class="btn btn-success" style="font-size: 16px; padding: 15px 40px;">Start Earning Today</a>
                </div>
            </div>
        </div>
    </section>
@endsection




