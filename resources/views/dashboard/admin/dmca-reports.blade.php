@extends('dashboard.layouts.main')

@section('title', 'DMCA Reports - Admin Dashboard')

@push('styles')
<style>
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

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }

    .stat-card {
        background: white;
        border-radius: 8px;
        padding: 16px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .stat-label {
        font-size: 13px;
        color: var(--text-secondary);
        margin-bottom: 8px;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 600;
        color: var(--text-primary);
    }

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
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--text-secondary);
        border-bottom: 2px solid var(--border-color);
    }

    .table td {
        padding: 12px 16px;
        border-bottom: 1px solid var(--border-color);
        font-size: 13px;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .table tbody tr.unread {
        background-color: #fff9e6;
    }

    .badge {
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-pending {
        background: #f39c12;
        color: white;
    }

    .badge-reviewed {
        background: #3498db;
        color: white;
    }

    .badge-resolved {
        background: #27ae60;
        color: white;
    }

    .badge-dismissed {
        background: #95a5a6;
        color: white;
    }

    .table-responsive {
        display: block;
        width: 100%;
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
        margin: 0;
        padding: 0;
    }

    .filter-form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 16px;
    }

    .filter-field {
        display: flex;
        flex-direction: column;
    }

    .filter-field-search {
        grid-column: span 2;
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
        padding: 10px 12px;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-size: 14px;
        background: white;
    }

    .filter-buttons {
        display: flex;
        gap: 10px;
    }

    .success-alert {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        padding: 16px 20px;
        border-left: 4px solid var(--success-color);
        background-color: #f0fdf4;
        border-radius: 6px;
        margin-bottom: 24px;
        position: relative;
    }

    .message-preview {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>
@endpush

@section('content')
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Reports</div>
            <div class="stat-value">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pending</div>
            <div class="stat-value" style="color: #f39c12;">{{ number_format($stats['pending']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Reviewed</div>
            <div class="stat-value" style="color: #3498db;">{{ number_format($stats['reviewed']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Resolved</div>
            <div class="stat-value" style="color: #27ae60;">{{ number_format($stats['resolved']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Dismissed</div>
            <div class="stat-value" style="color: #95a5a6;">{{ number_format($stats['dismissed']) }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.admin.dmca-reports') }}">
                <div class="filter-form-row">
                    <div class="filter-field">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="reviewed" {{ request('status') == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                            <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="dismissed" {{ request('status') == 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                        </select>
                    </div>
                    <div class="filter-field filter-field-search">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by copyright owner, contact name, email, or URL..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('dashboard.admin.dmca-reports') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Reports Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">DMCA Reports</h3>
        </div>
        <div class="card-body">
            @if(session('success'))
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

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Copyright Owner</th>
                            <th>Contact Name</th>
                            <th>Contact Email</th>
                            <th>Infringing URL</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            <tr class="{{ $report->status == 'pending' ? 'unread' : '' }}">
                                <td>#{{ $report->id }}</td>
                                <td><strong>{{ $report->copyright_owner }}</strong></td>
                                <td>{{ $report->contact_name }}</td>
                                <td>{{ $report->contact_email }}</td>
                                <td>
                                    <a href="{{ $report->infringing_url }}" target="_blank" rel="noopener noreferrer" style="color: #3498db; text-decoration: none;">
                                        {{ \Illuminate\Support\Str::limit($report->infringing_url, 30) }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $report->status }}">{{ ucfirst($report->status) }}</span>
                                </td>
                                <td>{{ $report->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('dashboard.admin.dmca-reports.show', $report->id) }}" class="btn btn-sm btn-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('dashboard.admin.dmca-reports.destroy', $report->id) }}" method="POST" class="action-form" onsubmit="return confirm('Are you sure you want to delete this report?');">
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
                                <td colspan="8" style="text-align: center; padding: 40px; color: var(--text-secondary);">
                                    <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;"></i>
                                    <p>No DMCA reports found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($reports->hasPages())
                <div style="margin-top: 20px; display: flex; justify-content: center;">
                    {{ $reports->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

