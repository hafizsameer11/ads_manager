@extends('dashboard.layouts.main')

@section('title', 'Users Management - Admin Dashboard')

@section('content')
    {{-- <div class="page-header">
        <h1>Users Management</h1>
        <p class="text-muted">Manage all users including publishers and advertisers.</p>
    </div> --}}

    <!-- Users Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Users List</h3>
        </div>
        <div class="card-body">
            <!-- Filters -->

            <form method="GET" action="{{ route('dashboard.admin.users') }}">
                <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; margin-bottom: 10px;">
                    <div style="flex: 0 0 auto; min-width: 150px; max-width: 180px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">Role</label>
                        <select name="role" class="form-control">
                            <option value="">All Roles</option>
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="publisher" {{ request('role') == 'publisher' ? 'selected' : '' }}>Publisher</option>
                            <option value="advertiser" {{ request('role') == 'advertiser' ? 'selected' : '' }}>Advertiser</option>
                        </select>
                    </div>
                    <div style="flex: 0 0 auto; min-width: 150px; max-width: 180px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div style="flex: 1 1 auto; min-width: 250px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="{{ request('search') }}">
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('dashboard.admin.users') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">

                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Account Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>#{{ $user->id }}</td>
                                <td>
                                    <strong>{{ $user->name }}</strong>
                                    @if ($user->referral_code)
                                        <br><small class="text-muted">Ref: {{ $user->referral_code }}</small>
                                    @endif
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge badge-info">{{ ucfirst($user->role) }}</span>
                                </td>
                                <td>
                                    @if ($user->role === 'publisher' && $user->publisher)
                                        @if ($user->publisher->status === 'approved')
                                            <span class="badge badge-success">Approved</span>
                                        @elseif($user->publisher->status === 'pending')
                                            <span class="badge badge-warning">Pending</span>
                                        @else
                                            <span class="badge badge-danger">Rejected</span>
                                        @endif
                                    @elseif($user->role === 'advertiser' && $user->advertiser)
                                        @if ($user->advertiser->status === 'approved')
                                            <span class="badge badge-success">Approved</span>
                                        @elseif($user->advertiser->status === 'pending')
                                            <span class="badge badge-warning">Pending</span>
                                        @else
                                            <span class="badge badge-danger">Rejected</span>
                                        @endif
                                    @else
                                        <span class="badge badge-secondary">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($user->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                        @if ($user->role === 'publisher' && $user->publisher)
                                            @if ($user->publisher->status === 'pending')
                                                <form action="{{ route('dashboard.admin.users.approve', $user->id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Approve" onclick="return confirm('Are you sure you want to approve this publisher?')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('dashboard.admin.users.reject', $user->id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Reject" onclick="return confirm('Are you sure you want to reject this publisher?')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @elseif($user->role === 'advertiser' && $user->advertiser)
                                            @if ($user->advertiser->status === 'pending')
                                                <form action="{{ route('dashboard.admin.users.approve', $user->id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success" title="Approve" onclick="return confirm('Are you sure you want to approve this advertiser?')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('dashboard.admin.users.reject', $user->id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Reject" onclick="return confirm('Are you sure you want to reject this advertiser?')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                        <form action="{{ route('dashboard.admin.users.toggle-status', $user->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-{{ $user->is_active ? 'warning' : 'success' }}" title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}" onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-{{ $user->is_active ? 'ban' : 'check-circle' }}"></i>
                                            </button>
                                        </form>
                                        @if (!$user->isAdmin())
                                            <form action="{{ route('dashboard.admin.users.destroy', $user->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">No users found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>
@endsection
