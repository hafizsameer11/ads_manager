@extends('dashboard.layouts.main')

@section('title', 'Two-Factor Authentication - Admin Dashboard')

@section('content')
    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 20px;">
        <a href="{{ route('dashboard.admin.profile') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Profile
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('recovery_codes'))
        <div class="alert alert-warning">
            <h5><strong>Save Your Recovery Codes</strong></h5>
            <p>Please save these recovery codes in a safe place. You can use them to access your account if you lose your device.</p>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; font-family: monospace; margin-top: 10px;">
                @foreach(session('recovery_codes') as $code)
                    <div>{{ $code }}</div>
                @endforeach
            </div>
            <p class="mt-3"><small>These codes will not be shown again. You can regenerate them from this page if needed.</small></p>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Two-Factor Authentication</h3>
        </div>
        <div class="card-body">
            @if($user->hasTwoFactorEnabled())
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <strong>Two-factor authentication is enabled</strong>
                </div>

                <p>Your account is protected with two-factor authentication. You'll be asked for a verification code when you sign in.</p>

                <div class="mt-4">
                    <a href="{{ route('dashboard.admin.security.two-factor.recovery-codes') }}" class="btn btn-info">
                        <i class="fas fa-key"></i> View / Regenerate Recovery Codes
                    </a>
                </div>

                <hr class="my-4">

                <h5>Disable Two-Factor Authentication</h5>
                <p class="text-muted">If you disable two-factor authentication, your account will be less secure.</p>
                
                <form method="POST" action="{{ route('dashboard.admin.security.two-factor.disable') }}" onsubmit="return confirm('Are you sure you want to disable two-factor authentication?');">
                    @csrf
                    <div class="form-group">
                        <label for="password">Confirm Your Password</label>
                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Disable Two-Factor Authentication
                    </button>
                </form>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Two-factor authentication is not enabled for your account.
                </div>

                <p>Two-factor authentication adds an extra layer of security to your account by requiring a verification code in addition to your password.</p>

                @if($qrCodeUrl && $secret)
                    <div class="mt-4">
                        <h5>Step 1: Scan QR Code</h5>
                        <p>Scan this QR code with your authenticator app (Google Authenticator, Authy, etc.):</p>
                        <div class="text-center mb-3" style="padding: 20px; background: white; border-radius: 8px; display: inline-block;">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrCodeUrl) }}" alt="QR Code" style="max-width: 200px;">
                        </div>
                        <p><small class="text-muted">Or enter this secret manually: <code>{{ $secret }}</code></small></p>

                        <h5 class="mt-4">Step 2: Enter Verification Code</h5>
                        <p>Enter the 6-digit code from your authenticator app to enable two-factor authentication:</p>

                        <form method="POST" action="{{ route('dashboard.admin.security.two-factor.enable') }}">
                            @csrf
                            <input type="hidden" name="secret" value="{{ $secret }}">
                            <div class="form-group">
                                <label for="otp">Verification Code</label>
                                <input type="text" id="otp" name="otp" class="form-control @error('otp') is-invalid @enderror" 
                                       placeholder="000000" maxlength="6" pattern="[0-9]{6}" required autocomplete="off">
                                @error('otp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-check"></i> Enable Two-Factor Authentication
                            </button>
                        </form>
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection

