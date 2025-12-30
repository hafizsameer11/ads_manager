@extends('dashboard.layouts.main')

@section('title', 'Create Account Type - Admin Dashboard')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Create New Account Type</h3>
            <a href="{{ route('dashboard.admin.allowed-account-types.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.admin.allowed-account-types.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Account Type Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required placeholder="e.g., JazzCash, EasyPaisa, Bank">
                            <small class="text-muted">The name of the account type that publishers will see</small>
                            @error('name')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="sort_order">Sort Order</label>
                            <input type="number" id="sort_order" name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}" min="0">
                            <small class="text-muted">Lower numbers appear first</small>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="3" placeholder="Optional description for this account type">{{ old('description') }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="is_enabled" value="1" {{ old('is_enabled', true) ? 'checked' : '' }}>
                                Enable this account type
                            </label>
                            <small class="text-muted d-block">Only enabled account types will be shown to publishers</small>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Account Type
                        </button>
                        <a href="{{ route('dashboard.admin.allowed-account-types.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
