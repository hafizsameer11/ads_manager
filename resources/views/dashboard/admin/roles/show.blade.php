@extends('dashboard.layouts.main')

@section('title', 'Role Details - Admin Dashboard')

@section('content')
    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 20px;">
        <a href="{{ route('dashboard.admin.roles.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Roles
        </a>
        @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('manage_roles'))
        @if($role->slug !== 'admin')
            <a href="{{ route('dashboard.admin.roles.edit', $role) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Role
            </a>
        @endif
        @endif
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Role Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="150">Name:</th>
                            <td><strong>{{ $role->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Slug:</th>
                            <td><code>{{ $role->slug }}</code></td>
                        </tr>
                        <tr>
                            <th>Description:</th>
                            <td>{{ $role->description ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Users:</th>
                            <td><span class="badge badge-info">{{ $role->users->count() }}</span></td>
                        </tr>
                        <tr>
                            <th>Permissions:</th>
                            <td><span class="badge badge-success">{{ $role->permissions->count() }}</span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Permissions</h3>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @forelse($role->permissions as $permission)
                        <div class="mb-2">
                            <strong>{{ $permission->name }}</strong>
                            <br>
                            <small class="text-muted">{{ $permission->slug }}</small>
                        </div>
                    @empty
                        <p class="text-muted">No permissions assigned</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Users with this Role</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($role->users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if($user->is_active == 1)
                                        <span class="badge badge-success">Approved</span>
                                    @elseif($user->is_active == 2)
                                        <span class="badge badge-warning">Pending</span>
                                    @elseif($user->is_active == 3)
                                        <span class="badge badge-warning">Suspended</span>
                                    @else
                                        <span class="badge badge-danger">Rejected</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No users with this role</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

