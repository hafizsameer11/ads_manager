@extends('dashboard.layouts.main')

@section('title', 'Websites Management - Admin Dashboard')

@section('content')
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Websites</div>
            <div class="stat-value">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Approved</div>
            <div class="stat-value">{{ number_format($stats['approved']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pending</div>
            <div class="stat-value">{{ number_format($stats['pending']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Rejected</div>
            <div class="stat-value">{{ number_format($stats['rejected']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Disabled</div>
            <div class="stat-value">{{ number_format($stats['disabled']) }}</div>
        </div>
    </div>

    <!-- Website Statistics Chart -->
    @if($dailyStats->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Website Performance (Last 30 Days)</h3>
        </div>
        <div class="card-body">
            <div style="position: relative; height: 400px;">
                <canvas id="websiteStatsChart"></canvas>
            </div>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.admin.websites') }}">
                <div class="filter-form-row">
                    <div class="filter-field">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="disabled" {{ request('status') == 'disabled' ? 'selected' : '' }}>Disabled</option>
                        </select>
                    </div>
                    <div class="filter-field filter-field-search">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by domain, name, or publisher..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('dashboard.admin.websites') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Websites Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Websites</h3>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show success-alert" role="alert">
                    <div class="alert-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="alert-content">
                        <strong><i class="fas fa-check"></i> Success!</strong>
                        <p style="margin: 4px 0 0 0;">{{ session('success') }}</p>
                    </div>
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
                            <th>Domain</th>
                            <th>Name</th>
                            <th>Publisher</th>
                            <th>Ad Units</th>
                            <th>Verification Method</th>
                            <th>Status</th>
                            <th>Added Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($websites as $website)
                            <tr>
                                <td>#{{ $website->id }}</td>
                                <td><strong>{{ $website->domain }}</strong></td>
                                <td>{{ $website->name }}</td>
                                <td>
                                    @if($website->publisher && $website->publisher->user)
                                        <div>{{ $website->publisher->user->name }}</div>
                                        <small class="text-muted">{{ $website->publisher->user->email }}</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $website->adUnits->count() }}</span>
                                </td>
                                <td>{{ ucfirst(str_replace('_', ' ', $website->verification_method)) }}</td>
                                <td>
                                    @php
                                        $status = $website->status ?? 'pending';
                                    @endphp
                                    @if($status === 'approved')
                                        <span class="badge badge-success">Approved</span>
                                    @elseif($status === 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @elseif($status === 'disabled')
                                        <span class="badge badge-secondary">Disabled</span>
                                    @elseif($status === 'rejected')
                                        <span class="badge badge-danger">Rejected</span>
                                        @if($website->rejection_reason)
                                            <br><small class="text-muted" title="{{ $website->rejection_reason }}">{{ \Illuminate\Support\Str::limit($website->rejection_reason, 30) }}</small>
                                        @endif
                                    @else
                                        <span class="badge badge-info">{{ ucfirst($status) }}</span>
                                    @endif
                                </td>
                                <td>{{ $website->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('dashboard.admin.websites.show', $website->id) }}" class="btn btn-sm btn-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($website->status === 'pending')
                                            <form action="{{ route('dashboard.admin.websites.approve', $website->id) }}" method="POST" class="action-form" onsubmit="return confirm('Are you sure you want to approve this website?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-danger" title="Reject" onclick="showRejectModal({{ $website->id }}, '{{ $website->domain }}')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @elseif($website->status === 'approved')
                                            <button type="button" class="btn btn-sm btn-warning" title="Disable" onclick="showDisableModal({{ $website->id }}, '{{ $website->domain }}')">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" title="Suspend" onclick="showSuspendModal({{ $website->id }}, '{{ $website->domain }}')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @elseif($website->status === 'rejected' || $website->status === 'disabled')
                                            <form action="{{ route('dashboard.admin.websites.enable', $website->id) }}" method="POST" class="action-form" onsubmit="return confirm('Are you sure you want to enable and approve this website?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Enable/Approve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">No websites found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $websites->links() }}
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">Reject Website</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p style="margin-bottom: 24px;">Are you sure you want to reject <strong id="rejectDomain"></strong>?</p>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="rejection_reason">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea id="rejection_reason" name="rejection_reason" class="form-control" rows="3" required placeholder="Enter reason for rejection..."></textarea>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="reject_admin_note">Admin Note (Optional)</label>
                            <textarea id="reject_admin_note" name="admin_note" class="form-control" rows="2" placeholder="Internal note (not visible to publisher)..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times-circle"></i> Reject Website
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Disable Modal -->
    <div class="modal fade" id="disableModal" tabindex="-1" role="dialog" aria-labelledby="disableModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="disableForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="disableModalLabel">Disable Website</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p style="margin-bottom: 16px;">Are you sure you want to disable <strong id="disableDomain"></strong>?</p>
                        <p class="text-warning" style="margin-bottom: 24px; padding: 12px; background-color: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                            <i class="fas fa-exclamation-triangle"></i> <small>This will pause all ad units on this website.</small>
                        </p>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="disable_admin_note">Admin Note (Optional)</label>
                            <textarea id="disable_admin_note" name="admin_note" class="form-control" rows="2" placeholder="Internal note (not visible to publisher)..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-ban"></i> Disable Website
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Suspend Modal -->
    <div class="modal fade" id="suspendModal" tabindex="-1" role="dialog" aria-labelledby="suspendModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="suspendForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="suspendModalLabel">Suspend Website</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p style="margin-bottom: 16px;">Are you sure you want to suspend <strong id="suspendDomain"></strong>?</p>
                        <p class="text-warning" style="margin-bottom: 24px; padding: 12px; background-color: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                            <i class="fas fa-exclamation-triangle"></i> <small>This will pause all ad units on this website.</small>
                        </p>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="suspend_reason">Reason (Optional)</label>
                            <textarea id="suspend_reason" name="reason" class="form-control" rows="2" placeholder="Enter reason for suspension..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-ban"></i> Suspend Website
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    /* Prevent horizontal scroll on page */
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

    /* Reduce card padding for better fit */
    .card {
        padding: 16px;
    }

    .card-header {
        padding: 16px;
    }

    .card-body {
        padding: 16px;
    }

    /* Reduce stats grid gap */
    .stats-grid {
        gap: 16px;
        margin-bottom: 20px;
    }

    .stat-card {
        padding: 16px;
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

    /* Ensure card doesn't overflow */
    .card {
        overflow: visible;
        max-width: 100%;
    }

    .card-body {
        overflow-x: visible;
        max-width: 100%;
    }

    /* Success Alert Display */
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
    }

    .success-alert .close:hover {
        opacity: 1;
    }

    /* Action buttons container - keep all buttons in one row */
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

    /* Form buttons inline styling */
    .action-form {
        display: inline-flex;
        margin: 0;
        padding: 0;
    }

    .action-form .btn {
        margin: 0;
    }

    /* Ensure table actions column doesn't wrap and takes minimum space */
    .table td:last-child {
        white-space: nowrap;
        width: 1%;
        min-width: 200px;
    }

    /* Enhanced Modal Styling - Hidden by default */
    .modal {
        display: none !important;
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        outline: 0;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal.fade {
        transition: opacity 0.15s linear;
        display: none !important;
    }

    .modal.show,
    .modal.fade.show {
        display: block !important;
        opacity: 1;
    }

    .modal.fade:not(.show) {
        opacity: 0;
        display: none !important;
    }

    .modal-dialog {
        position: relative;
        width: auto;
        max-width: 500px;
        margin: 1.75rem auto;
        pointer-events: none;
    }

    .modal.fade .modal-dialog {
        transition: transform 0.3s ease-out;
        transform: translate(0, -50px);
    }

    .modal.show .modal-dialog {
        transform: none;
    }

    .modal-content {
        position: relative;
        display: flex;
        flex-direction: column;
        width: 100%;
        pointer-events: auto;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid rgba(0, 0, 0, 0.2);
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        outline: 0;
    }

    .modal-header {
        border-bottom: 1px solid var(--border-color);
        padding: 24px 30px;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-radius: 12px 12px 0 0;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-header .close {
        position: absolute;
        top: 20px;
        right: 20px;
        padding: 0;
        background: transparent;
        border: none;
        font-size: 24px;
        font-weight: 300;
        line-height: 1;
        color: var(--text-secondary);
        opacity: 0.5;
        cursor: pointer;
        z-index: 1;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        transition: all 0.2s;
    }

    .modal-header .close:hover {
        opacity: 1;
        background-color: rgba(0, 0, 0, 0.05);
        color: var(--text-primary);
    }

    .modal-header .close span {
        font-size: 28px;
        line-height: 1;
    }

    .modal-title {
        font-size: 20px;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }

    .modal-body {
        padding: 30px;
        margin-top: 20px;
        margin-bottom: 20px;
    }

    #rejectModal .modal-body,
    #disableModal .modal-body,
    #suspendModal .modal-body {
        margin-top: 24px;
        margin-bottom: 24px;
    }

    .modal-footer {
        border-top: 1px solid var(--border-color);
        padding: 20px 30px;
        background-color: var(--bg-secondary);
        border-radius: 0 0 12px 12px;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 20px;
    }

    #rejectModal .modal-footer,
    #disableModal .modal-footer,
    #suspendModal .modal-footer {
        margin-top: 24px;
        margin-bottom: 0;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--text-primary);
        font-size: 14px;
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

    /* Badge Enhancements */
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

@push('scripts')
<script>
    // Modal functionality with vanilla JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        // Ensure all modals are hidden on page load
        const modals = ['rejectModal', 'disableModal', 'suspendModal'];
        modals.forEach(function(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
                modal.classList.remove('show');
            }
        });

        // Close modals when clicking close button or cancel
        document.querySelectorAll('.modal .close, .modal [data-dismiss="modal"]').forEach(function(button) {
            button.addEventListener('click', function() {
                const modal = this.closest('.modal');
                if (modal) {
                    hideModal(modal);
                }
            });
        });

        // Close modal when clicking outside
        document.querySelectorAll('.modal').forEach(function(modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    hideModal(modal);
                }
            });
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal.show').forEach(function(modal) {
                    hideModal(modal);
                });
            }
        });
    });

    function showModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'block';
            modal.classList.add('show');
            document.body.style.overflow = 'hidden'; // Prevent background scrolling
        }
    }

    function hideModal(modal) {
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
            document.body.style.overflow = ''; // Restore scrolling
        }
    }

    function showRejectModal(id, domain) {
        // Clear previous form data
        const form = document.getElementById('rejectForm');
        if (form) {
            form.reset();
        }
        const reasonField = document.getElementById('rejection_reason');
        const noteField = document.getElementById('reject_admin_note');
        if (reasonField) reasonField.value = '';
        if (noteField) noteField.value = '';
        
        // Set domain and form action
        const domainElement = document.getElementById('rejectDomain');
        if (domainElement) {
            domainElement.textContent = domain;
        }
        if (form) {
            form.action = '{{ route("dashboard.admin.websites.reject", ":id") }}'.replace(':id', id);
        }
        
        // Show modal
        showModal('rejectModal');
    }

    function showDisableModal(id, domain) {
        // Clear previous form data
        const form = document.getElementById('disableForm');
        if (form) {
            form.reset();
        }
        const noteField = document.getElementById('disable_admin_note');
        if (noteField) noteField.value = '';
        
        // Set domain and form action
        const domainElement = document.getElementById('disableDomain');
        if (domainElement) {
            domainElement.textContent = domain;
        }
        if (form) {
            form.action = '{{ route("dashboard.admin.websites.disable", ":id") }}'.replace(':id', id);
        }
        
        // Show modal
        showModal('disableModal');
    }

    function showSuspendModal(id, domain) {
        // Clear previous form data
        const form = document.getElementById('suspendForm');
        if (form) {
            form.reset();
        }
        const reasonField = document.getElementById('suspend_reason');
        if (reasonField) reasonField.value = '';
        
        // Set domain and form action
        const domainElement = document.getElementById('suspendDomain');
        if (domainElement) {
            domainElement.textContent = domain;
        }
        if (form) {
            form.action = '{{ route("dashboard.admin.websites.suspend", ":id") }}'.replace(':id', id);
        }
        
        // Show modal
        showModal('suspendModal');
    }
</script>

@if(isset($dailyStats) && $dailyStats->count() > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Chart.js default configuration
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#6b7280';
    Chart.defaults.borderColor = '#e5e7eb';
    
    // Website Statistics Chart
    const websiteStatsCtx = document.getElementById('websiteStatsChart').getContext('2d');
    new Chart(websiteStatsCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($dailyStats->pluck('date')->map(function($date) {
                return \Carbon\Carbon::parse($date)->format('M d');
            })) !!},
            datasets: [
                {
                    label: 'Impressions',
                    data: {!! json_encode($dailyStats->pluck('impressions')) !!},
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y',
                },
                {
                    label: 'Clicks',
                    data: {!! json_encode($dailyStats->pluck('clicks')) !!},
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y',
                },
                {
                    label: 'Revenue ($)',
                    data: {!! json_encode($dailyStats->pluck('revenue')) !!},
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: false,
                }
            },
            scales: {
                x: {
                    display: true,
                    grid: {
                        display: false,
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Impressions & Clicks'
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Revenue ($)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
</script>
@endif
@endpush

