@extends('dashboard.layouts.main')

@section('title', 'Withdrawals Management - Admin Dashboard')

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
        min-width: 180px;
    }

    /* Enhanced Modal Styling */
    .modal {
        display: none !important;
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow-y: auto;
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
        display: flex;
        align-items: center;
        min-height: calc(100% - 3.5rem);
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
        max-height: 90vh;
        pointer-events: auto;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid rgba(0, 0, 0, 0.2);
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        outline: 0;
        overflow: hidden;
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

    .modal-title {
        font-size: 20px;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }

    .modal-body {
        padding: 30px;
        margin-top: 24px;
        margin-bottom: 24px;
        overflow-y: auto;
        max-height: calc(90vh - 200px);
    }

    #markPaidModal .modal-body {
        max-height: calc(90vh - 180px);
    }

    #screenshotPreview {
        max-height: 300px;
        overflow-y: auto;
        overflow-x: hidden;
    }

    #screenshotPreview img {
        max-width: 100%;
        height: auto;
        display: block;
    }

    .modal-footer {
        border-top: 1px solid var(--border-color);
        padding: 20px 30px;
        background-color: var(--bg-secondary);
        border-radius: 0 0 12px 12px;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 24px;
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

    /* Success/Error Alerts */
    .alert {
        padding: 16px 20px;
        border-radius: var(--border-radius);
        margin-bottom: 20px;
        position: relative;
    }

    .alert .close {
        position: absolute;
        top: 16px;
        right: 16px;
        font-size: 20px;
        opacity: 0.6;
        cursor: pointer;
    }

    .alert .close:hover {
        opacity: 1;
    }
</style>
@endpush

@section('content')
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Withdrawals</div>
            <div class="stat-value">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pending</div>
            <div class="stat-value">{{ number_format($stats['pending']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pending Amount</div>
            <div class="stat-value">${{ number_format($stats['pending_amount'], 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Payout</div>
            <div class="stat-value">${{ number_format($stats['total_paid_out'], 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Amount</div>
            <div class="stat-value">${{ number_format($stats['total_amount'], 2) }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.admin.withdrawals') }}">
                <div class="filter-form-row">
                    <div class="filter-field">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="processed" {{ request('status') == 'processed' ? 'selected' : '' }}>Processed</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="filter-field filter-field-search">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by ID, publisher name or email..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('dashboard.admin.withdrawals') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Withdrawals Table -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title">All Withdrawals</h3>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('dashboard.admin.withdrawals.export.csv', request()->query()) }}" class="btn btn-sm" style="background-color: #28a745; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fas fa-file-csv"></i> CSV
                </a>
                <a href="{{ route('dashboard.admin.withdrawals.export.excel', request()->query()) }}" class="btn btn-sm" style="background-color: #217346; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
                <a href="{{ route('dashboard.admin.withdrawals.export.pdf', request()->query()) }}" class="btn btn-sm" style="background-color: #dc3545; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <div class="alert-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="alert-content">
                        <strong><i class="fas fa-check"></i> Success!</strong>
                        <p>{{ session('success') }}</p>
                    </div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="alert-content">
                        <strong><i class="fas fa-times-circle"></i> Error!</strong>
                        <ul class="mb-0 mt-2" style="list-style: none; padding-left: 0;">
                            @foreach($errors->all() as $error)
                                <li style="padding: 4px 0;"><i class="fas fa-chevron-right" style="font-size: 10px; margin-right: 8px;"></i>{{ $error }}</li>
                            @endforeach
                        </ul>
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
                            <th>Publisher</th>
                            <th>Amount</th>
                            <th>Account Type</th>
                            <th>Account Details</th>
                            <th>Status</th>
                            <th>Requested Date</th>
                            <th>Processed Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($withdrawals as $withdrawal)
                            <tr>
                                <td>#{{ $withdrawal->id }}</td>
                                <td>
                                    @if($withdrawal->publisher && $withdrawal->publisher->user)
                                        <div><strong>{{ $withdrawal->publisher->user->name }}</strong></div>
                                        <small class="text-muted">{{ $withdrawal->publisher->user->email }}</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td><strong>${{ number_format($withdrawal->amount, 2) }}</strong></td>
                                <td>
                                    @if($withdrawal->account_type)
                                        <span class="badge badge-info">{{ $withdrawal->account_type }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($withdrawal->account_name || $withdrawal->account_number)
                                        @if($withdrawal->account_name)
                                            <div><strong>Name:</strong> {{ $withdrawal->account_name }}</div>
                                        @endif
                                        @if($withdrawal->account_number)
                                            <div><strong>Number:</strong> {{ $withdrawal->account_number }}</div>
                                        @endif
                                    @elseif($withdrawal->payment_details)
                                        @if(isset($withdrawal->payment_details['account']))
                                            <small>{{ \Illuminate\Support\Str::limit($withdrawal->payment_details['account'], 30) }}</small>
                                        @else
                                            <small class="text-muted">Details available</small>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($withdrawal->status === 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @elseif($withdrawal->status === 'approved')
                                        <span class="badge badge-info">Approved</span>
                                    @elseif($withdrawal->status === 'processed')
                                        <span class="badge badge-success">Processed</span>
                                    @else
                                        <span class="badge badge-danger">Rejected</span>
                                        @if($withdrawal->rejection_reason)
                                            <br><small class="text-muted" title="{{ $withdrawal->rejection_reason }}">{{ \Illuminate\Support\Str::limit($withdrawal->rejection_reason, 30) }}</small>
                                        @endif
                                    @endif
                                </td>
                                <td>{{ $withdrawal->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    @if($withdrawal->processed_at)
                                        {{ $withdrawal->processed_at->format('M d, Y H:i') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        @if($withdrawal->status === 'pending')
                                            <button type="button" class="btn btn-sm btn-success" title="Approve" onclick="showApproveModal({{ $withdrawal->id }}, '{{ number_format($withdrawal->amount, 2) }}', '{{ $withdrawal->publisher->user->name ?? 'N/A' }}', {{ ($withdrawal->account_type || $withdrawal->account_name || $withdrawal->account_number) ? 'true' : 'false' }}, '{{ $withdrawal->account_type ?? '' }}', '{{ $withdrawal->account_name ?? '' }}', '{{ $withdrawal->account_number ?? '' }}')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            <button type="button" class="btn btn-sm btn-danger" title="Reject" onclick="showRejectModal({{ $withdrawal->id }}, '{{ number_format($withdrawal->amount, 2) }}')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @elseif($withdrawal->status === 'approved')
                                            <button type="button" class="btn btn-sm btn-primary" title="Mark as Paid" onclick="showMarkPaidModal({{ $withdrawal->id }}, '{{ number_format($withdrawal->amount, 2) }}', true)">
                                                <i class="fas fa-money-check"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">No withdrawals found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $withdrawals->links() }}
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="approveForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="approveModalLabel">Approve Withdrawal</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p style="margin-bottom: 16px;">Are you sure you want to approve withdrawal of <strong id="approveAmount"></strong> for <strong id="approvePublisher"></strong>?</p>
                        
                        <!-- Account Details Section -->
                        <div id="approveAccountInfo" style="display: none; margin-bottom: 20px;">
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; border-left: 4px solid #007bff;">
                                <strong style="display: block; margin-bottom: 10px;">Account Details:</strong>
                                <div style="margin-bottom: 8px;">
                                    <strong>Account Type:</strong> <span id="approveAccountType"></span>
                                </div>
                                <div style="margin-bottom: 8px;">
                                    <strong>Account Name:</strong> <span id="approveAccountName"></span>
                                </div>
                                <div style="margin-bottom: 8px;">
                                    <strong>Account Number:</strong> <code id="approveAccountNumber" style="background: white; padding: 4px 8px; border-radius: 4px;"></code>
                                </div>
                            </div>
                        </div>
                        
                        <p class="text-success" style="margin-bottom: 24px; padding: 12px; background-color: #d4edda; border-left: 4px solid #28a745; border-radius: 4px;">
                            <i class="fas fa-info-circle"></i> <small>The withdrawal will be marked as approved and ready for processing.</small>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-circle"></i> Approve Withdrawal
                        </button>
                    </div>
                </form>
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
                        <h5 class="modal-title" id="rejectModalLabel">Reject Withdrawal</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p style="margin-bottom: 16px;">Are you sure you want to reject withdrawal for <strong id="rejectAmount"></strong>?</p>
                        <p class="text-info" style="margin-bottom: 24px; padding: 12px; background-color: #d1ecf1; border-left: 4px solid #17a2b8; border-radius: 4px;">
                            <i class="fas fa-info-circle"></i> <small>The amount will be refunded to the publisher's balance.</small>
                        </p>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="rejection_reason">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea id="rejection_reason" name="rejection_reason" class="form-control" rows="3" required placeholder="Enter reason for rejection..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times-circle"></i> Reject Withdrawal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Mark Paid Modal -->
    <div class="modal fade" id="markPaidModal" tabindex="-1" role="dialog" aria-labelledby="markPaidModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="markPaidForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="markPaidModalLabel">Mark Withdrawal as Paid</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p style="margin-bottom: 24px;">Mark withdrawal for <strong id="paidAmount"></strong> as processed/paid?</p>
                        
                        <!-- Payment Screenshot Section -->
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="payment_screenshot">Payment Screenshot <span class="text-danger">*</span></label>
                            <input type="file" id="payment_screenshot" name="payment_screenshot" class="form-control" accept="image/*" required>
                            <small class="text-muted">Upload a screenshot of the payment transaction</small>
                            <div id="screenshotPreview" style="margin-top: 10px; display: none;">
                                <img id="previewImg" src="" alt="Preview" style="max-width: 100%; max-height: 200px; border-radius: 6px; border: 1px solid #ddd;">
                            </div>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="transaction_id">Transaction ID (Optional)</label>
                            <input type="text" id="transaction_id" name="transaction_id" class="form-control" placeholder="Enter transaction ID or reference number...">
                            <small class="text-muted">Optional: Enter the payment gateway transaction ID or reference number.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check-circle"></i> Mark as Paid
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Modal functionality with vanilla JavaScript
    document.addEventListener('DOMContentLoaded', function() {
        const modals = ['rejectModal', 'markPaidModal', 'approveModal'];
        modals.forEach(function(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.style.display = 'none';
                modal.classList.remove('show');
            }
        });

        document.querySelectorAll('.modal .close, .modal [data-dismiss="modal"]').forEach(function(button) {
            button.addEventListener('click', function() {
                const modal = this.closest('.modal');
                if (modal) hideModal(modal);
            });
        });

        document.querySelectorAll('.modal').forEach(function(modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) hideModal(modal);
            });
        });

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
            // Prevent body scroll but allow modal scroll
            document.body.style.overflow = 'hidden';
            // Scroll modal to top
            modal.scrollTop = 0;
            const modalDialog = modal.querySelector('.modal-dialog');
            if (modalDialog) {
                modalDialog.scrollTop = 0;
            }
        }
    }

    function hideModal(modal) {
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    function showApproveModal(id, amount, publisher, hasAccountDetails, accountType, accountName, accountNumber) {
        const form = document.getElementById('approveForm');
        if (form) {
            form.reset();
        }
        
        const amountElement = document.getElementById('approveAmount');
        if (amountElement) {
            amountElement.textContent = '$' + amount;
        }
        
        const publisherElement = document.getElementById('approvePublisher');
        if (publisherElement) {
            publisherElement.textContent = publisher;
        }
        
        // Show/hide account details
        const accountInfo = document.getElementById('approveAccountInfo');
        if (hasAccountDetails && accountInfo) {
            accountInfo.style.display = 'block';
            document.getElementById('approveAccountType').textContent = accountType || 'N/A';
            document.getElementById('approveAccountName').textContent = accountName || 'N/A';
            document.getElementById('approveAccountNumber').textContent = accountNumber || 'N/A';
        } else if (accountInfo) {
            accountInfo.style.display = 'none';
        }
        
        if (form) {
            form.action = '{{ route("dashboard.admin.withdrawals.approve", ":id") }}'.replace(':id', id);
        }
        
        showModal('approveModal');
    }

    function showRejectModal(id, amount) {
        const form = document.getElementById('rejectForm');
        if (form) {
            form.reset();
        }
        const reasonField = document.getElementById('rejection_reason');
        if (reasonField) reasonField.value = '';
        
        const amountElement = document.getElementById('rejectAmount');
        if (amountElement) {
            amountElement.textContent = '$' + amount;
        }
        if (form) {
            form.action = '{{ route("dashboard.admin.withdrawals.reject", ":id") }}'.replace(':id', id);
        }
        
        showModal('rejectModal');
    }

    function showMarkPaidModal(id, amount) {
        const form = document.getElementById('markPaidForm');
        if (form) {
            form.reset();
        }
        const transactionField = document.getElementById('transaction_id');
        if (transactionField) transactionField.value = '';
        
        const amountElement = document.getElementById('paidAmount');
        if (amountElement) {
            amountElement.textContent = '$' + amount;
        }
        
        // Reset preview
        const preview = document.getElementById('screenshotPreview');
        const previewImg = document.getElementById('previewImg');
        if (preview) preview.style.display = 'none';
        if (previewImg) previewImg.src = '';
        
        if (form) {
            form.action = '{{ route("dashboard.admin.withdrawals.mark-paid", ":id") }}'.replace(':id', id);
        }
        
        showModal('markPaidModal');
    }

    // Preview screenshot
    document.addEventListener('DOMContentLoaded', function() {
        const screenshotInput = document.getElementById('payment_screenshot');
        if (screenshotInput) {
            screenshotInput.addEventListener('change', function(e) {
                const preview = document.getElementById('screenshotPreview');
                const previewImg = document.getElementById('previewImg');
                
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImg.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(e.target.files[0]);
                } else {
                    preview.style.display = 'none';
                }
            });
        }
    });
</script>
@endpush

