@extends('dashboard.layouts.main')

@section('title', 'Roles & Permissions - Admin Dashboard')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Roles & Permissions</h2>
        @if(auth()->user()->hasPermission('manage_roles'))
        <a href="{{ route('dashboard.admin.roles.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Role
        </a>
        @endif
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
            {{ $errors->first() }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Roles</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Description</th>
                            <th>Users</th>
                            <th>Permissions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td><strong>{{ $role->name }}</strong></td>
                                <td><code>{{ $role->slug }}</code></td>
                                <td>{{ $role->description ?? 'N/A' }}</td>
                                <td><span class="badge badge-info">{{ $role->users_count }}</span></td>
                                <td><span class="badge badge-success">{{ $role->permissions_count }}</span></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('dashboard.admin.roles.show', $role) }}" class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(auth()->user()->hasPermission('manage_roles'))
                                        @if($role->slug !== 'admin')
                                            <a href="{{ route('dashboard.admin.roles.edit', $role) }}" class="btn btn-sm btn-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('dashboard.admin.roles.destroy', $role) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Are you sure you want to delete this role?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No roles found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $roles->links() }}
            </div>
        </div>
    </div>
@endsection

