@extends('dashboard.layouts.main')

@section('title', 'Recovery Codes - Admin Dashboard')

@section('content')
    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 20px;">
        <a href="{{ route('dashboard.admin.security.two-factor') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to 2FA Settings
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Recovery Codes</h3>
        </div>
        <div class="card-body">
            <div class="alert alert-warning">
                <h5><strong>Save These Recovery Codes</strong></h5>
                <p>Please save these recovery codes in a safe place. You can use them to access your account if you lose your device.</p>
                <p><strong>Warning:</strong> These codes will not be shown again. Make sure to save them now.</p>
            </div>

            <div style="background: #f8f9fa; padding: 20px; border-radius: 4px; border: 2px dashed #dee2e6; margin: 20px 0;">
                <div style="font-family: monospace; font-size: 16px; line-height: 2; text-align: center;">
                    @foreach($recoveryCodes as $code)
                        <div style="padding: 8px 0; border-bottom: 1px solid #dee2e6;">{{ $code }}</div>
                    @endforeach
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('dashboard.admin.security.two-factor') }}" class="btn btn-primary">
                    <i class="fas fa-check"></i> I've Saved My Recovery Codes
                </a>
            </div>
        </div>
    </div>
@endsection




