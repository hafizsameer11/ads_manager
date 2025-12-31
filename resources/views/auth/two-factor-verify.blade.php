@extends('website.layouts.main')

@section('title', 'Two-Factor Authentication - ' . config('app.name'))

@section('description', 'Verify your two-factor authentication code')

@section('content')
    <section class="hero-section" style="min-height: 80vh; display: flex; align-items: center; position: relative;">
        <div class="container" style="position: relative; z-index: 10;">
            <div style="max-width: 500px; margin: 0 auto; position: relative; z-index: 10;">
                <div class="login-panel" style="position: relative; z-index: 10;">
                    <h3>Two-Factor Authentication</h3>
                    <p style="color: rgba(255, 255, 255, 0.8); margin-bottom: 20px;">Please enter the 6-digit code from your authenticator app, or use a recovery code.</p>
                    
                    @if($errors->any())
                        <div style="padding: 15px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 6px; color: #721c24; margin-bottom: 20px;">
                            <ul style="margin: 0; padding-left: 20px;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('two-factor.verify.submit') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="code">Verification Code</label>
                            <input type="text" 
                                   id="code" 
                                   name="code" 
                                   value="{{ old('code') }}" 
                                   placeholder="000000" 
                                   maxlength="10" 
                                   pattern="[0-9]{6}|[A-Z0-9]{10}"
                                   required 
                                   autofocus
                                   autocomplete="off">
                            @error('code')
                                <span style="color: #dc3545; font-size: 14px; margin-top: 5px; display: block;">{{ $message }}</span>
                            @enderror
                            <small style="display: block; margin-top: 5px; font-size: 12px; color: rgba(255, 255, 255, 0.7);">Enter the 6-digit code from your authenticator app, or a recovery code</small>
                        </div>
                        <div class="btn-group">
                            <button type="submit" class="btn btn-primary">Verify</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
