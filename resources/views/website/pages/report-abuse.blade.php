@extends('website.layouts.main')

@section('title', 'Report Abuse - ' . config('app.name'))

@section('description', 'Report abusive or inappropriate content or behavior on our platform')

@push('styles')
<style>
    /* Form input fields - consistent styling */
    .form-group input[type="text"],
    .form-group input[type="email"],
    .form-group input[type="url"] {
        width: 100%;
        padding: 12px 16px;
        background-color: var(--bg-light);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-family: inherit;
        font-size: 14px;
        color: var(--text-dark);
        transition: var(--transition);
    }

    .form-group input[type="text"]:focus,
    .form-group input[type="email"]:focus,
    .form-group input[type="url"]:focus {
        outline: none;
        border-color: var(--primary-color);
        background-color: white;
        box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.1);
    }

    .form-group input[type="text"]::placeholder,
    .form-group input[type="email"]::placeholder,
    .form-group input[type="url"]::placeholder {
        color: var(--text-light);
    }

    /* Select dropdown styling */
    .form-select {
        width: 100%;
        padding: 12px 16px;
        padding-right: 40px;
        background-color: var(--bg-light);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-family: inherit;
        font-size: 14px;
        color: var(--text-dark);
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 12px;
        transition: var(--transition);
    }

    .form-select:hover {
        border-color: var(--primary-color);
    }

    .form-select:focus {
        outline: none;
        border-color: var(--primary-color);
        background-color: white;
        box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.1);
    }

    .form-select option {
        background-color: white;
        color: var(--text-dark);
        padding: 10px;
    }
</style>
@endpush

@section('content')
    <section class="section" style="padding-top: 100px;">
        <div class="container" style="width: 90%; max-width: none; margin: 0 auto;">
            <h1 class="section-title">Report Abuse</h1>
            <p class="section-subtitle">
                Help us maintain a safe and professional platform by reporting any abusive content or behavior
            </p>

            <div style="width: 100%;">
                <div style="background: white; padding: 40px; border-radius: 12px; box-shadow: var(--shadow-md);">
                    <p style="color: var(--text-light); line-height: 1.8; margin-bottom: 30px;">
                        If you have encountered any abusive, inappropriate, or fraudulent content on our platform,
                        please fill out the form below. All reports are reviewed by our team and appropriate action will be taken.
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

                    <form method="POST" action="{{ route('website.report-abuse.store') }}" style="display: flex; flex-direction: column; gap: 20px;">
                        @csrf
                        <div class="form-group">
                            <label for="type">Type of Abuse</label>
                            <select id="type" name="type" class="form-select" required>
                                <option value="">Select type of abuse</option>
                                <option value="fraud">Fraud</option>
                                <option value="spam">Spam</option>
                                <option value="inappropriate">Inappropriate Content</option>
                                <option value="copyright">Copyright Infringement</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="url">URL (if applicable)</label>
                            <input type="url" id="url" name="url" placeholder="Enter the URL of the abusive content">
                        </div>
                        <div class="form-group">
                            <label for="email">Your Email</label>
                            <input type="email" id="email" name="email" placeholder="Enter your email address" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="6" style="width: 100%; padding: 12px 16px; background-color: var(--bg-light); border: 1px solid var(--border-color); border-radius: 6px; font-family: inherit; font-size: 14px; resize: vertical;" placeholder="Please provide a detailed description of the abuse" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="align-self: flex-start;">Submit Report</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
