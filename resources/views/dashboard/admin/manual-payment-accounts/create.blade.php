@extends('dashboard.layouts.main')

@section('title', 'Create Manual Payment Account - Admin Dashboard')

@push('styles')
<style>
    body {
        overflow-x: hidden !important;
    }

    .dashboard-main {
        overflow-x: hidden !important;
        width: 100%;
        max-width: 100%;
        padding: 20px;
        box-sizing: border-box;
    }

    .card {
        overflow: visible;
        max-width: 100%;
        padding: 16px;
    }

    .card-header {
        padding: 16px;
    }

    .card-body {
        overflow-x: visible;
        max-width: 100%;
        padding: 16px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--text-primary);
        font-size: 14px;
    }

    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        font-size: 14px;
        transition: var(--transition);
        background-color: var(--bg-primary);
        color: var(--text-primary);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.1);
    }

    .form-check {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-check-input {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }

    .form-check-label {
        margin: 0;
        cursor: pointer;
    }

    .image-preview {
        margin-top: 10px;
        max-width: 200px;
    }

    .image-preview img {
        max-width: 100%;
        height: auto;
        border-radius: 6px;
        border: 1px solid var(--border-color);
    }

    .success-alert {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        padding: 16px 20px;
        border-left: 4px solid var(--success-color);
        background-color: #f0fdf4;
        border-radius: var(--border-radius);
        margin-bottom: 24px;
        position: relative;
    }

    .validation-alert {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        padding: 16px 20px;
        border-left: 4px solid var(--danger-color);
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        border-radius: var(--border-radius);
        margin-bottom: 24px;
        position: relative;
    }
</style>
@endpush

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Create Manual Payment Account</h3>
            <a href="{{ route('dashboard.admin.manual-payment-accounts.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="validation-alert">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="alert-content">
                        <strong>Validation Errors:</strong>
                        <ul style="margin: 8px 0 0 0; padding-left: 20px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('dashboard.admin.manual-payment-accounts.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="account_name" class="form-label">Account Name <span class="text-danger">*</span></label>
                            <input type="text" id="account_name" name="account_name" class="form-control" value="{{ old('account_name') }}" required placeholder="e.g., Main JazzCash Account">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="account_type" class="form-label">Account Type <span class="text-danger">*</span></label>
                            <input type="text" id="account_type" name="account_type" class="form-control" value="{{ old('account_type') }}" required placeholder="e.g., JazzCash, EasyPaisa, Bank Transfer">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="account_number" class="form-label">Account Number <span class="text-danger">*</span></label>
                            <input type="text" id="account_number" name="account_number" class="form-control" value="{{ old('account_number') }}" required placeholder="Enter account number">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="account_image" class="form-label">Account Image</label>
                    <input type="file" id="account_image" name="account_image" class="form-control" accept="image/*" onchange="previewImage(this)">
                    <small class="text-muted">Recommended: 200x200px. Max size: 2MB. Formats: JPEG, PNG, JPG, GIF, WEBP</small>
                    <div id="imagePreview" class="image-preview" style="display: none;">
                        <img id="previewImg" src="" alt="Preview">
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" id="is_enabled" name="is_enabled" class="form-check-input" {{ old('is_enabled', true) ? 'checked' : '' }}>
                        <label for="is_enabled" class="form-check-label">
                            <strong>Enable this payment account</strong>
                        </label>
                    </div>
                    <small class="text-muted">If enabled, this payment method will be visible to advertisers on their dashboard.</small>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Account
                    </button>
                    <a href="{{ route('dashboard.admin.manual-payment-accounts.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const previewImg = document.getElementById('previewImg');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                }
                
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
@endsection

