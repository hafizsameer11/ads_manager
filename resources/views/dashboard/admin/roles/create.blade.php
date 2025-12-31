@extends('dashboard.layouts.main')

@section('title', 'Create Role - Admin Dashboard')

@section('content')
    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 20px;">
        <a href="{{ route('dashboard.admin.roles.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Roles
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Create New Role</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.admin.roles.store') }}">
                @csrf

                <div class="form-group">
                    <label for="name">Role Name <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="slug">Slug <span class="text-danger">*</span></label>
                    <input type="text" id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug') }}" required>
                    <small class="text-muted">Unique identifier (e.g., sub-admin, moderator)</small>
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Permissions</label>
                    <div class="permissions-container" style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 15px; border-radius: 4px;">
                        @foreach($permissions as $group => $groupPermissions)
                            <div class="permission-group mb-3">
                                <strong style="text-transform: capitalize;">{{ $group }}</strong>
                                <div class="mt-2 ml-3">
                                    @foreach($groupPermissions as $permission)
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="permission_{{ $permission->id }}" name="permissions[]" value="{{ $permission->id }}" {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                {{ $permission->name }}
                                                <small class="text-muted">({{ $permission->slug }})</small>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Create Role</button>
                    <a href="{{ route('dashboard.admin.roles.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

