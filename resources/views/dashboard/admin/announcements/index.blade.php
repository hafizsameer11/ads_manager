@extends('dashboard.layouts.main')

@section('title', 'Announcements - Admin Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Announcements Management</h3>
        @if(Auth::user()->isAdmin() || Auth::user()->hasPermission('manage_settings'))
        <a href="{{ route('dashboard.admin.announcements.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Announcement
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

    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.admin.announcements.index') }}">
                <div class="filter-form-row">
                    <div class="filter-field">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-control">
                            <option value="">All Types</option>
                            <option value="info" {{ request('type') == 'info' ? 'selected' : '' }}>Info</option>
                            <option value="success" {{ request('type') == 'success' ? 'selected' : '' }}>Success</option>
                            <option value="warning" {{ request('type') == 'warning' ? 'selected' : '' }}>Warning</option>
                            <option value="danger" {{ request('type') == 'danger' ? 'selected' : '' }}>Danger</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label class="form-label">Audience</label>
                        <select name="audience" class="form-control">
                            <option value="">All Audiences</option>
                            <option value="all" {{ request('audience') == 'all' ? 'selected' : '' }}>All</option>
                            <option value="publishers" {{ request('audience') == 'publishers' ? 'selected' : '' }}>Publishers</option>
                            <option value="advertisers" {{ request('audience') == 'advertisers' ? 'selected' : '' }}>Advertisers</option>
                            <option value="admins" {{ request('audience') == 'admins' ? 'selected' : '' }}>Admins</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="filter-field filter-field-search">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by title or content..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('dashboard.admin.announcements.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Announcements Table -->
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">All Announcements</h4>
        </div>
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Target Audience</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($announcements as $announcement)
                            <tr>
                                <td>
                                    <strong>{{ $announcement->title }}</strong>
                                    @if($announcement->starts_at && $announcement->starts_at > now())
                                        <span class="badge badge-info">Scheduled</span>
                                    @endif
                                    @if($announcement->ends_at && $announcement->ends_at < now())
                                        <span class="badge badge-secondary">Expired</span>
                                    @endif
                                </td>
                                <td>
                                    @if($announcement->type == 'info')
                                        <span class="badge badge-info">Info</span>
                                    @elseif($announcement->type == 'success')
                                        <span class="badge badge-success">Success</span>
                                    @elseif($announcement->type == 'warning')
                                        <span class="badge badge-warning">Warning</span>
                                    @elseif($announcement->type == 'danger')
                                        <span class="badge badge-danger">Danger</span>
                                    @endif
                                </td>
                                <td>{{ ucfirst($announcement->target_audience) }}</td>
                                <td>
                                    @if($announcement->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $announcement->creator->name ?? 'N/A' }}</td>
                                <td>{{ $announcement->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('dashboard.admin.announcements.show', $announcement) }}" class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(Auth::user()->isAdmin() || Auth::user()->hasPermission('manage_settings'))
                                        <a href="{{ route('dashboard.admin.announcements.edit', $announcement) }}" class="btn btn-sm btn-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('dashboard.admin.announcements.destroy', $announcement) }}" method="POST" class="action-form d-inline" onsubmit="return confirm('Are you sure you want to delete this announcement?');">
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
                                <td colspan="7" class="text-center">No announcements found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $announcements->links() }}
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
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

    /* Action Buttons Styling */
    .action-buttons {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: nowrap;
    }

    .action-buttons .btn {
        margin: 0;
        padding: 6px 10px;
        min-width: 36px;
    }

    .action-buttons .action-form {
        display: inline-block;
        margin: 0;
    }
</style>
@endpush

