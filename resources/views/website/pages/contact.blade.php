@extends('website.layouts.main')

@section('title', 'Contact Us - ' . config('app.name'))

@section('description', 'Get in touch with our team for support, inquiries, or partnerships')

@section('content')
    <section class="section" style="padding-top: 100px;">
        <div class="container" style="width: 90%; max-width: none; margin: 0 auto;">
            <h1 class="section-title">Contact Us</h1>
            <p class="section-subtitle">
                We're here to help! Get in touch with our team for any questions or support
            </p>

            <div style="width: 100%;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; margin-bottom: 50px;">
                    <div style="text-align: center; padding: 30px; background: var(--bg-light); border-radius: 12px;">
                        <div style="width: 60px; height: 60px; margin: 0 auto 20px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">📧</div>
                        <h3 style="font-size: 18px; margin-bottom: 10px; color: var(--text-dark);">Email</h3>
                        <p style="color: var(--text-light);">support@example.com</p>
                    </div>

                    <div style="text-align: center; padding: 30px; background: var(--bg-light); border-radius: 12px;">
                        <div style="width: 60px; height: 60px; margin: 0 auto 20px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">💬</div>
                        <h3 style="font-size: 18px; margin-bottom: 10px; color: var(--text-dark);">Live Chat</h3>
                        <p style="color: var(--text-light);">Available 24/7</p>
                    </div>

                    <div style="text-align: center; padding: 30px; background: var(--bg-light); border-radius: 12px;">
                        <div style="width: 60px; height: 60px; margin: 0 auto 20px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">⏰</div>
                        <h3 style="font-size: 18px; margin-bottom: 10px; color: var(--text-dark);">Response Time</h3>
                        <p style="color: var(--text-light);">Within 24 hours</p>
                    </div>
                </div>

                <div style="background: white; padding: 40px; border-radius: 12px; box-shadow: var(--shadow-md);">
                    <h2 style="font-size: 24px; margin-bottom: 30px; color: var(--text-dark);">Send us a Message</h2>
                    
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

                    <form method="POST" action="{{ route('website.contact.store') }}" style="display: flex; flex-direction: column; gap: 20px;">
                        @csrf
                        <div class="form-group">
                            <label for="name">Your Name</label>
                            <input type="text" id="name" name="name" placeholder="Enter your name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" placeholder="Enter your email" required>
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" placeholder="Enter subject" required>
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" rows="6" style="width: 100%; padding: 12px 16px; background-color: var(--bg-light); border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit; font-size: 14px; resize: vertical;" placeholder="Enter your message" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="align-self: flex-start;">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
