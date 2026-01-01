@extends('website.layouts.main')

@section('title', 'Report DMCA - ' . config('app.name'))

@section('description', 'Submit a DMCA takedown notice for copyright infringement')

@section('content')
    <section class="section" style="padding-top: 100px;">
        <div class="container" style="width: 90%; max-width: none; margin: 0 auto;">
            <h1 class="section-title">Report DMCA</h1>
            <p class="section-subtitle">
                Submit a DMCA takedown notice for copyright infringement
            </p>

            <div style="width: 100%;">
                <div style="background: white; padding: 40px; border-radius: 12px; box-shadow: var(--shadow-md);">
                    <p style="color: var(--text-light); line-height: 1.8; margin-bottom: 30px;">
                        If you believe that content on our platform infringes your copyright, please submit a DMCA takedown notice. 
                        Please note that false or fraudulent DMCA notices may result in liability for damages.
                    </p>

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

                    <form method="POST" action="{{ route('website.report-dmca.store') }}" style="display: flex; flex-direction: column; gap: 20px;">
                        @csrf
                        <div class="form-group">
                            <label for="copyright_owner">Copyright Owner Name</label>
                            <input type="text" id="copyright_owner" name="copyright_owner" placeholder="Enter the name of the copyright owner" required>
                        </div>
                        <div class="form-group">
                            <label for="contact_name">Your Name</label>
                            <input type="text" id="contact_name" name="contact_name" placeholder="Enter your full name" required>
                        </div>
                        <div class="form-group">
                            <label for="contact_email">Your Email</label>
                            <input type="email" id="contact_email" name="contact_email" placeholder="Enter your email address" required>
                        </div>
                        <div class="form-group">
                            <label for="contact_phone">Your Phone</label>
                            <input type="tel" id="contact_phone" name="contact_phone" placeholder="Enter your phone number" required>
                        </div>
                        <div class="form-group">
                            <label for="infringing_url">Infringing URL</label>
                            <input type="url" id="infringing_url" name="infringing_url" placeholder="Enter the URL of the infringing content" required>
                        </div>
                        <div class="form-group">
                            <label for="original_work">Description of Original Work</label>
                            <textarea id="original_work" name="original_work" rows="4" style="width: 100%; padding: 12px 16px; background-color: var(--bg-light); border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit; font-size: 14px; resize: vertical;" placeholder="Describe the original copyrighted work" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="statement">Good Faith Statement</label>
                            <textarea id="statement" name="statement" rows="4" style="width: 100%; padding: 12px 16px; background-color: var(--bg-light); border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit; font-size: 14px; resize: vertical;" placeholder="I have a good faith belief that use of the copyrighted material described above is not authorized..." required></textarea>
                        </div>
                        <div class="checkbox-group" style="margin-bottom: 20px;">
                            <input type="checkbox" id="accuracy" name="accuracy" required>
                            <label for="accuracy" style="margin: 0;">I swear, under penalty of perjury, that the information in this notification is accurate and that I am the copyright owner or authorized to act on behalf of the owner.</label>
                        </div>
                        <button type="submit" class="btn btn-primary" style="align-self: flex-start;">Submit DMCA Notice</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
