@extends('dashboard.layouts.main')

@section('title', 'Edit Role - Admin Dashboard')

@section('content')
    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 20px;">
        <a href="{{ route('dashboard.admin.roles.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Roles
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Edit Role: {{ $role->name }}</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.admin.roles.update', $role) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Role Name <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $role->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="slug">Slug <span class="text-danger">*</span></label>
                    <input type="text" id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror" value="{{ old('slug', $role->slug) }}" required>
                    <small class="text-muted">Unique identifier (e.g., sub-admin, moderator)</small>
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $role->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Permissions</label>
                    <div class="permissions-container" style="max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 15px; border-radius: 4px;">
                        @if(count($permissions) > 0)
                            @foreach($permissions as $group => $groupPermissions)
                                <div class="permission-group mb-3">
                                    <strong style="text-transform: capitalize;">{{ $group }}</strong>
                                    <div class="mt-2 ml-3">
                                        @foreach($groupPermissions as $permission)
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="permission_{{ $permission->id }}" name="permissions[]" value="{{ $permission->id }}" {{ in_array($permission->id, $selectedPermissions) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                    {{ $permission->name }}
                                                    <small class="text-muted">({{ $permission->slug }})</small>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="alert alert-warning">
                                <strong><i class="fas fa-exclamation-triangle"></i> No Permissions Found!</strong>
                                <p class="mb-0 mt-2">The permissions table is empty. Please run the seeder to populate permissions:</p>
                                <code style="display: block; margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 4px;">
                                    php artisan db:seed --class=RolesAndPermissionsSeeder
                                </code>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Update Role</button>
                    <a href="{{ route('dashboard.admin.roles.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection




