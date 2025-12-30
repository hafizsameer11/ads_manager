@extends('dashboard.layouts.main')

@section('title', 'Contact Messages - Admin Dashboard')

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

    /* Reduce stats grid gap */
    .stats-grid {
        gap: 16px;
        margin-bottom: 20px;
    }

    .stat-card {
        padding: 16px;
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

    .table tbody tr.unread {
        background-color: #f0f9ff;
        font-weight: 500;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    .badge {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-success {
        background: linear-gradient(135deg, #27ae60, #229954);
        color: white;
    }

    .badge-warning {
        background: linear-gradient(135deg, #f39c12, #e67e22);
        color: white;
    }

    .badge-danger {
        background: linear-gradient(135deg, #e74c3c, #c0392b);
        color: white;
    }

    .badge-info {
        background: linear-gradient(135deg, #3498db, #2980b9);
        color: white;
    }

    .badge-secondary {
        background: linear-gradient(135deg, #95a5a6, #7f8c8d);
        color: white;
    }

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

    /* Table Responsive - scroll only on table container */
    .table-responsive {
        display: block;
        width: 100%;
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
        margin: 0;
        padding: 0;
        position: relative;
    }

    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .table-responsive .table {
        width: 100%;
        margin-bottom: 0;
        min-width: 600px; /* Minimum width to maintain table structure */
        font-size: 13px;
    }

    .table th,
    .table td {
        padding: 12px 10px;
        font-size: 13px;
    }

    .table th {
        font-size: 11px;
        padding: 10px 8px;
    }

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

    .message-preview {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Responsive adjustments */
    @media (max-width: 1400px) {
        .dashboard-main {
            padding: 16px;
        }
        
        .card {
            padding: 14px;
        }
        
        .card-header,
        .card-body {
            padding: 14px;
        }

        .action-buttons .btn {
            padding: 6px 8px;
            font-size: 11px;
        }

        .table th,
        .table td {
            padding: 10px 8px;
            font-size: 12px;
        }
    }

    @media (max-width: 1200px) {
        .dashboard-main {
            padding: 12px;
        }

        .card {
            padding: 12px;
            margin-bottom: 16px;
        }

        .card-header,
        .card-body {
            padding: 12px;
        }

        .stats-grid {
            gap: 12px;
        }

        .stat-card {
            padding: 12px;
        }

        .action-buttons {
            gap: 4px;
        }
        
        .action-buttons .btn {
            padding: 5px 7px;
            font-size: 11px;
        }
        
        .action-buttons .btn i {
            font-size: 10px;
        }
        
        .table td:last-child {
            min-width: 160px;
        }

        .table th,
        .table td {
            padding: 8px 6px;
            font-size: 11px;
        }

        .table-responsive .table {
            min-width: 500px;
        }
    }

    @media (max-width: 992px) {
        .dashboard-main {
            padding: 10px;
        }

        .card {
            padding: 10px;
        }

        .card-header,
        .card-body {
            padding: 10px;
        }

        .table td:last-child {
            min-width: 140px;
        }

        .table th,
        .table td {
            padding: 8px 4px;
            font-size: 11px;
        }

        .table-responsive .table {
            min-width: 450px;
        }
    }

    @media (max-width: 768px) {
        .dashboard-main {
            padding: 8px;
        }

        .card {
            padding: 8px;
            margin-bottom: 12px;
        }

        .card-header,
        .card-body {
            padding: 8px;
        }

        .stats-grid {
            gap: 8px;
            margin-bottom: 16px;
        }

        .stat-card {
            padding: 10px;
        }

        .filter-form-row {
            flex-direction: column;
            gap: 12px;
        }

        .filter-field {
            width: 100%;
            min-width: 100%;
        }

        .filter-buttons {
            width: 100%;
            justify-content: stretch;
        }

        .filter-buttons .btn {
            flex: 1;
        }

        .table-responsive .table {
            min-width: 400px;
            font-size: 10px;
        }

        .table th,
        .table td {
            padding: 6px 4px;
            font-size: 10px;
        }
    }
</style>
@endpush

@section('content')
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Messages</div>
            <div class="stat-value">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Unread</div>
            <div class="stat-value" style="color: #f39c12;">{{ number_format($stats['unread']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Read</div>
            <div class="stat-value" style="color: #27ae60;">{{ number_format($stats['read']) }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.admin.contact-messages') }}">
                <div class="filter-form-row">
                    <div class="filter-field">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Messages</option>
                            <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>Unread</option>
                            <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Read</option>
                        </select>
                    </div>
                    <div class="filter-field filter-field-search">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by name, email, subject, or message..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('dashboard.admin.contact-messages') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Messages Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Contact Messages</h3>
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
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($messages as $message)
                            <tr class="{{ !$message->is_read ? 'unread' : '' }}">
                                <td>#{{ $message->id }}</td>
                                <td><strong>{{ $message->name }}</strong></td>
                                <td>{{ $message->email }}</td>
                                <td>{{ $message->subject }}</td>
                                <td>
                                    <div class="message-preview" title="{{ $message->message }}">
                                        {{ \Illuminate\Support\Str::limit($message->message, 50) }}
                                    </div>
                                </td>
                                <td>
                                    @if($message->is_read)
                                        <span class="badge badge-success">Read</span>
                                    @else
                                        <span class="badge badge-warning">Unread</span>
                                    @endif
                                </td>
                                <td>{{ $message->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('dashboard.admin.contact-messages.show', $message->id) }}" class="btn btn-sm btn-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($message->is_read)
                                            <form action="{{ route('dashboard.admin.contact-messages.mark-unread', $message->id) }}" method="POST" class="action-form">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning" title="Mark as Unread">
                                                    <i class="fas fa-envelope"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('dashboard.admin.contact-messages.mark-read', $message->id) }}" method="POST" class="action-form">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Mark as Read">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form action="{{ route('dashboard.admin.contact-messages.destroy', $message->id) }}" method="POST" class="action-form" onsubmit="return confirm('Are you sure you want to delete this message? This action cannot be undone.');">
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
                                <td colspan="8" class="text-center text-muted">No messages found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $messages->links() }}
            </div>
        </div>
    </div>
@endsection

