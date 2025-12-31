@extends('dashboard.layouts.main')

@section('title', 'Edit Account Type - Admin Dashboard')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Account Type</h3>
            <a href="{{ route('dashboard.admin.allowed-account-types.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.admin.allowed-account-types.update', $allowedAccountType->id) }}">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="name">Account Type Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $allowedAccountType->name) }}" required placeholder="e.g., JazzCash, EasyPaisa, Bank">
                            <small class="text-muted">The name of the account type that publishers will see</small>
                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="3" placeholder="Optional description for this account type">{{ old('description', $allowedAccountType->description) }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="is_enabled" value="1" {{ old('is_enabled', $allowedAccountType->is_enabled) ? 'checked' : '' }}>
                                Enable this account type
                            </label>
                            <small class="text-muted d-block">Only enabled account types will be shown to publishers</small>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Account Type
                        </button>
                        <a href="{{ route('dashboard.admin.allowed-account-types.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

