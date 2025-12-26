@extends('website.layouts.main')

@section('title', 'Frequently Asked Questions - ' . config('app.name'))

@section('description', 'Find answers to common questions about our advertising network platform')

@section('content')
    <section class="section" style="padding-top: 100px;">
        <div class="container">
            <h1 class="section-title">Frequently Asked Questions</h1>
            <p class="section-subtitle">
                Find answers to the most common questions about our platform
            </p>

            <div style="max-width: 800px; margin: 0 auto;">
                <div style="background: white; padding: 40px; border-radius: 12px; box-shadow: var(--shadow-md); margin-bottom: 20px;">
                    <h2 style="font-size: 20px; margin-bottom: 15px; color: var(--text-dark);">How do I get started as an advertiser?</h2>
                    <p style="color: var(--text-light); line-height: 1.8;">
                        Simply register for an account, add funds to your account, create your campaign, and start advertising. 
                        Our platform provides easy-to-use tools to manage your campaigns effectively.
                    </p>
                </div>

                <div style="background: white; padding: 40px; border-radius: 12px; box-shadow: var(--shadow-md); margin-bottom: 20px;">
                    <h2 style="font-size: 20px; margin-bottom: 15px; color: var(--text-dark);">What are the payment terms for publishers?</h2>
                    <p style="color: var(--text-light); line-height: 1.8;">
                        Publishers receive payments on a weekly basis, with a minimum threshold. We support multiple payment methods 
                        including PayPal, wire transfer, and cryptocurrency.
                    </p>
                </div>

                <div style="background: white; padding: 40px; border-radius: 12px; box-shadow: var(--shadow-md); margin-bottom: 20px;">
                    <h2 style="font-size: 20px; margin-bottom: 15px; color: var(--text-dark);">How do I report abusive content?</h2>
                    <p style="color: var(--text-light); line-height: 1.8;">
                        If you encounter any abusive or inappropriate content, please use our Report Abuse page to submit a detailed report. 
                        Our team reviews all reports and takes appropriate action.
                    </p>
                </div>

                <div style="background: white; padding: 40px; border-radius: 12px; box-shadow: var(--shadow-md); margin-bottom: 20px;">
                    <h2 style="font-size: 20px; margin-bottom: 15px; color: var(--text-dark);">What targeting options are available?</h2>
                    <p style="color: var(--text-light); line-height: 1.8;">
                        We offer advanced targeting options including geographic targeting, device targeting, browser targeting, 
                        time-based targeting, and more to help you reach your ideal audience.
                    </p>
                </div>

                <div style="background: white; padding: 40px; border-radius: 12px; box-shadow: var(--shadow-md);">
                    <h2 style="font-size: 20px; margin-bottom: 15px; color: var(--text-dark);">How can I contact support?</h2>
                    <p style="color: var(--text-light); line-height: 1.8;">
                        You can reach our support team through the Contact page or via email. We strive to respond to all inquiries 
                        within 24 hours.
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection




