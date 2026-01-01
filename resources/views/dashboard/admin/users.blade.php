@extends('dashboard.layouts.main')

@section('title', 'Users Management - Admin Dashboard')

@push('styles')
    <style>
        /* Import same styles from websites page */
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

        .dashboard-wrapper {
            overflow-x: hidden !important;
            width: 100%;
            max-width: 100vw;
        }

        .dashboard-content {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }

        /* Action buttons container */
        .action-buttons {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: nowrap;
            white-space: nowrap;
        }

        .action-buttons .btn {
            white-space: nowrap;
            flex-shrink: 0;
            padding: 6px 10px;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
            line-height: 1.2;
            min-width: auto;
        }

        .action-buttons .btn i {
            font-size: 11px;
        }

        .action-form {
            display: inline-flex;
            margin: 0;
            padding: 0;
        }

        .action-form .btn {
            margin: 0;
        }

        .table td:last-child {
            white-space: nowrap;
            width: 1%;
            min-width: 200px;
        }

        /* Enhanced Table Styles */
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table thead {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        }

        .table th {
            padding: 14px 16px;
            text-align: left;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            border-bottom: 2px solid var(--border-color);
        }

        .table td {
            padding: 16px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-secondary);
            vertical-align: middle;
        }

        .table tbody tr {
            transition: all 0.2s ease;
            background-color: var(--bg-primary);
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Bootstrap 5 Badge Overrides - Enforce Visibility */
        .badge {
            padding: 6px 12px !important;
            border-radius: 6px !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 4px !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        

        /* Bootstrap 5 Badge Color Overrides */
        .badge-success,
        .badge.bg-success {
            background: linear-gradient(135deg, #27ae60, #229954) !important;
            background-color: #198754 !important;
            color: #ffffff !important;
        }

        .badge-warning,
        .badge.bg-warning {
            background: linear-gradient(135deg, #f39c12, #e67e22) !important;
            background-color: #ffc107 !important;
            color: #000000 !important;
        }

        .badge-danger,
        .badge.bg-danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b) !important;
            background-color: #dc3545 !important;
            color: #ffffff !important;
        }

        .badge-info,
        .badge.bg-info {
            background: linear-gradient(135deg, #3498db, #2980b9) !important;
            background-color: #0dcaf0 !important;
            color: #000000 !important;
        }

        .badge-secondary,
        .badge.bg-secondary {
            background: linear-gradient(135deg, #95a5a6, #7f8c8d) !important;
            background-color: #6c757d !important;
            color: #ffffff !important;
        }

        .badge-primary,
        .badge.bg-primary {
            background-color: #0d6efd !important;
            color: #ffffff !important;
        }

        /* Ensure badges are not hidden by any theme overrides */
        .badge[style*="display: none"],
        .badge[style*="visibility: hidden"],
        .badge[style*="opacity: 0"] {
            display: inline-flex !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        /* Success Alert Styles */
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

        .success-alert .alert-icon {
            flex-shrink: 0;
            font-size: 24px;
            color: var(--success-color);
            margin-top: 2px;
        }

        .success-alert .alert-content {
            flex: 1;
        }

        .success-alert .alert-content strong {
            font-size: 16px;
            font-weight: 600;
            color: var(--success-color);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .success-alert .alert-content p {
            color: var(--text-primary);
            margin: 0;
        }

        .success-alert .close {
            position: absolute;
            top: 16px;
            right: 16px;
            font-size: 20px;
            opacity: 0.6;
            cursor: pointer;
        }

        .success-alert .close:hover {
            opacity: 1;
        }

        /* Filter Form Styling */
        .filter-form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
            margin-bottom: 16px;
        }

        .filter-field {
            flex: 0 0 auto;
            min-width: 140px;
            max-width: 100%;
        }

        .filter-field-search {
            flex: 1 1 auto;
            min-width: 200px;
        }

        .filter-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
        }

        .form-label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            font-size: 13px;
            color: var(--text-primary);
        }

        /* Table Responsive */
        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
            overflow-y: visible;
            -webkit-overflow-scrolling: touch;
            margin: 0;
            padding: 0;
        }

        .table-responsive .table {
            width: 100%;
            margin-bottom: 0;
            min-width: 600px;
            font-size: 13px;
        }

        /* Card Styles */
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
    </style>
@endpush

@section('content')
    <!-- Users Table -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title">Users List</h3>
            @if(auth()->user()->isAdmin() || auth()->user()->hasPermission('manage_users'))
            <a href="{{ route('dashboard.admin.users.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create User
            </a>
            @endif
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="success-alert">
                    <div class="alert-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="alert-content">
                        <strong><i class="fas fa-check"></i> Success!</strong>
                        <p style="margin: 4px 0 0 0;">{{ session('success') }}</p>
                    </div>
                    <button type="button" class="close" onclick="this.parentElement.style.display='none'">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="alert-content">
                        <strong><i class="fas fa-times-circle"></i> Error!</strong>
                        <ul class="mb-0 mt-2" style="list-style: none; padding-left: 0;">
                            @foreach ($errors->all() as $error)
                                <li style="padding: 4px 0;"><i class="fas fa-chevron-right"
                                        style="font-size: 10px; margin-right: 8px;"></i>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- Filters -->
            <form method="GET" action="{{ route('dashboard.admin.users') }}">
                <div class="filter-form-row">
                    <div class="filter-field">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-control">
                            <option value="">All Roles</option>
                            <option value="sub-admin" {{ request('role') == 'sub-admin' ? 'selected' : '' }}>Sub-Admin</option>
                            <option value="publisher" {{ request('role') == 'publisher' ? 'selected' : '' }}>Publisher</option>
                            <option value="advertiser" {{ request('role') == 'advertiser' ? 'selected' : '' }}>Advertiser</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved
                            </option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended
                            </option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected
                            </option>
                        </select>
                    </div>
                    <div class="filter-field filter-field-search">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by name or email..."
                            value="{{ request('search') }}">
                    </div>
                </div>
                <div class="filter-buttons">
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
                            {{-- <th>Account Status</th> --}}
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($users as $user)
                            @php
                                // Normalize role - check for sub-admin first
                                if ($user->hasRole('sub-admin') || $user->role === 'sub-admin') {
                                    $role = 'sub-admin';
                                } else {
                                    $role = strtolower(trim($user->role));
                                }

                                // Approval status (publisher / advertiser)
                                if ($role === 'publisher') {
                                    $approvalStatus = optional($user->publisher)->status;
                                } elseif ($role === 'advertiser') {
                                    $approvalStatus = optional($user->advertiser)->status;
                                } else {
                                    $approvalStatus = null;
                                }

                                // Account status: 1 = Approved, 0 = Rejected, 2 = Pending
                                // Ensure it's cast to integer for proper comparison
                                // Default to 2 (Pending) if NULL for backward compatibility
                                $accountStatus = $user->is_active !== null ? (int) $user->is_active : 2;
                            @endphp

                            <tr>
                                <td>#{{ $user->id }}</td>

                                <td>
                                    <strong>{{ $user->name }}</strong>
                                    @if ($user->referral_code)
                                        <br>
                                        <small class="text-muted">Ref: {{ $user->referral_code }}</small>
                                    @endif
                                </td>

                                <td>{{ $user->email }}</td>

                                {{-- ROLE --}}
                                <td>
                                    @if($user->hasRole('admin'))
                                        Admin
                                    @elseif($user->hasRole('sub-admin') || $user->role === 'sub-admin')
                                        Sub-Admin
                                    @else
                                        {{ ucfirst($user->role) }}
                                    @endif
                                </td>
                                {{-- <td>{{{}}</td> --}}

                                {{-- ACCOUNT STATUS --}}
                                <td>
                                    @php $status = (int) $user->is_active; @endphp

                                    @if ($status === 1)
                                        <span class="badge bg-success">Approved</span>
                                    @elseif ($status === 2)
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif ($status === 3)
                                        <span class="badge bg-warning text-dark">Suspended</span>
                                    @elseif ($status === 0)
                                        <span class="badge bg-danger">Rejected</span>
                                    @else
                                        <span class="badge bg-secondary">N/A</span>
                                    @endif
                                </td>


                                <td>{{ $user->created_at->format('M d, Y') }}</td>

                                {{-- ACTIONS --}}
                                <td>
                                    <div class="action-buttons">

                                        {{-- VIEW / EDIT (for publishers, advertisers, and sub-admins) --}}
                                        @if ($role === 'publisher' || $role === 'advertiser' || $role === 'sub-admin' || $user->hasRole('sub-admin'))
                                            <a href="{{ route('dashboard.admin.users.show', $user->id) }}" 
                                               class="btn btn-sm btn-info" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('dashboard.admin.users.edit', $user->id) }}" 
                                               class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif

                                        {{-- APPROVE / REJECT (for pending users) --}}
                                        @if ($accountStatus == 2)
                                            <form method="POST"
                                                action="{{ route('dashboard.admin.users.approve', $user->id) }}"
                                                class="action-form" onsubmit="return confirm('Approve this user?');">
                                                @csrf
                                                <button class="btn btn-sm btn-success" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>

                                            <form method="POST"
                                                action="{{ route('dashboard.admin.users.reject', $user->id) }}"
                                                class="action-form" onsubmit="return confirm('Reject this user?');">
                                                @csrf
                                                <button class="btn btn-sm btn-danger" title="Reject">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @endif


                                        {{-- TOGGLE ACCOUNT STATUS (only show when not pending, since pending has dedicated approve/reject buttons) --}}
                                        @if ($accountStatus != 2)
                                            <form method="POST"
                                                action="{{ route('dashboard.admin.users.toggle-status', $user->id) }}"
                                                class="action-form" onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                @if ($accountStatus == 1)
                                                    {{-- Approved -> Reject --}}
                                                    <button class="btn btn-sm btn-danger" title="Reject">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                @elseif ($accountStatus == 0)
                                                    {{-- Rejected -> Approve --}}
                                                    <button class="btn btn-sm btn-success" title="Approve">
                                                        <i class="fas fa-check-circle"></i>
                                                    </button>
                                                @endif
                                            </form>
                                        @endif

                                        {{-- DELETE --}}
                                        @if (!$user->isAdmin())
                                            <form method="POST"
                                                action="{{ route('dashboard.admin.users.destroy', $user->id) }}"
                                                class="action-form"
                                                onsubmit="return confirm('Delete this user permanently?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger" title="Delete">
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
