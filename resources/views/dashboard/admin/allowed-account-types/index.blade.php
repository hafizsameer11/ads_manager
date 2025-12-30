@extends('dashboard.layouts.main')

@section('title', 'Allowed Account Types - Admin Dashboard')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Allowed Account Types</h3>
            <a href="{{ route('dashboard.admin.allowed-account-types.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Account Type
            </a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Sort Order</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accountTypes as $accountType)
                            <tr>
                                <td>#{{ $accountType->id }}</td>
                                <td><strong>{{ $accountType->name }}</strong></td>
                                <td>{{ $accountType->description ?? '-' }}</td>
                                <td>
                                    @if($accountType->is_enabled)
                                        <span class="badge badge-success">Enabled</span>
                                    @else
                                        <span class="badge badge-secondary">Disabled</span>
                                    @endif
                                </td>
                                <td>{{ $accountType->sort_order }}</td>
                                <td>{{ $accountType->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('dashboard.admin.allowed-account-types.edit', $accountType->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('dashboard.admin.allowed-account-types.toggle-status', $accountType->id) }}" method="POST" class="action-form">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $accountType->is_enabled ? 'btn-warning' : 'btn-success' }}" title="{{ $accountType->is_enabled ? 'Disable' : 'Enable' }}">
                                                <i class="fas fa-{{ $accountType->is_enabled ? 'toggle-on' : 'toggle-off' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('dashboard.admin.allowed-account-types.destroy', $accountType->id) }}" method="POST" class="action-form" onsubmit="return confirm('Are you sure you want to delete this account type?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No account types found. <a href="{{ route('dashboard.admin.allowed-account-types.create') }}">Create one</a></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
