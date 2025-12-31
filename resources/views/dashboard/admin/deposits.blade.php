@extends('dashboard.layouts.main')

@section('title', 'Deposits Management - Admin Dashboard')

@push('styles')
<style>
    /* Import same styles from withdrawals page */
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
        overflow-x: hidden;
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
        max-width: 600px;
        margin: 1.75rem auto;
        pointer-events: none;
    }

    .modal-dialog-centered {
        display: flex;
        align-items: center;
        min-height: calc(100% - 3.5rem);
    }

    .modal.fade .modal-dialog {
        transition: transform 0.3s ease-out, opacity 0.3s ease-out;
        transform: translate(0, -50px);
        opacity: 0;
    }

    .modal.show .modal-dialog {
        transform: translate(0, 0);
        opacity: 1;
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
        flex: 1 1 auto;
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

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07), 0 1px 3px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-color), var(--info-color));
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12), 0 4px 8px rgba(0, 0, 0, 0.08);
    }

    .stat-label {
        font-size: 13px;
        color: var(--text-secondary);
        margin-bottom: 8px;
        font-weight: 500;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1.2;
    }

    .page-header {
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--border-color);
    }

    .page-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .page-header .text-muted {
        color: var(--text-secondary);
        font-size: 14px;
    }
</style>
@endpush

@section('content')
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Deposits</div>
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
            <div class="stat-label">Completed</div>
            <div class="stat-value">{{ number_format($stats['completed']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Amount</div>
            <div class="stat-value">${{ number_format($stats['total_amount'], 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Failed</div>
            <div class="stat-value">{{ number_format($stats['failed']) }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.admin.deposits') }}">
                <div class="filter-form-row">
                    <div class="filter-field">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-control">
                            <option value="">All Methods</option>
                            <option value="paypal" {{ request('payment_method') == 'paypal' ? 'selected' : '' }}>PayPal</option>
                            <option value="coinpayment" {{ request('payment_method') == 'coinpayment' ? 'selected' : '' }}>CoinPayment</option>
                            <option value="faucetpay" {{ request('payment_method') == 'faucetpay' ? 'selected' : '' }}>FaucetPay</option>
                            <option value="stripe" {{ request('payment_method') == 'stripe' ? 'selected' : '' }}>Stripe</option>
                            <option value="bank_swift" {{ request('payment_method') == 'bank_swift' ? 'selected' : '' }}>Bank SWIFT</option>
                            <option value="wise" {{ request('payment_method') == 'wise' ? 'selected' : '' }}>Wise</option>
                            <option value="manual" {{ request('payment_method') == 'manual' ? 'selected' : '' }}>Manual Payment</option>
                        </select>
                    </div>
                    <div class="filter-field filter-field-search">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by ID, transaction ID, advertiser name or email..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('dashboard.admin.deposits') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Deposits Table -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title">All Deposits</h3>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('dashboard.admin.deposits.export.csv', request()->query()) }}" class="btn btn-sm" style="background-color: #28a745; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fas fa-file-csv"></i> CSV
                </a>
                <a href="{{ route('dashboard.admin.deposits.export.excel', request()->query()) }}" class="btn btn-sm" style="background-color: #217346; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
                <a href="{{ route('dashboard.admin.deposits.export.pdf', request()->query()) }}" class="btn btn-sm" style="background-color: #dc3545; color: white; padding: 8px 16px; border-radius: 4px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
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
                            <th>Advertiser</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Transaction ID</th>
                            <th>Screenshot</th>
                            <th>Status</th>
                            <th>Requested Date</th>
                            <th>Processed Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deposits as $deposit)
                            <tr>
                                <td>#{{ $deposit->id }}</td>
                                <td>
                                    @if($deposit->transactionable && $deposit->transactionable->user)
                                        <div><strong>{{ $deposit->transactionable->user->name }}</strong></div>
                                        <small class="text-muted">{{ $deposit->transactionable->user->email }}</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td><strong>${{ number_format($deposit->amount, 2) }}</strong></td>
                                <td>{{ ucfirst(str_replace('_', ' ', $deposit->payment_method ?? 'N/A')) }}</td>
                                <td>
                                    <code style="font-size: 11px;">{{ $deposit->transaction_id ?? 'N/A' }}</code>
                                </td>
                                <td>
                                    @if($deposit->payment_method === 'manual' && $deposit->payment_screenshot)
                                        <button type="button" class="btn btn-sm btn-info" onclick="showScreenshotModal('{{ addslashes($deposit->screenshot_url) }}', '{{ addslashes($deposit->transaction_id ?? 'N/A') }}')" title="View Screenshot">
                                            <i class="fas fa-image"></i> View
                                        </button>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($deposit->status === 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @elseif($deposit->status === 'completed')
                                        <span class="badge badge-success">Completed</span>
                                    @else
                                        <span class="badge badge-danger">Failed</span>
                                        @if($deposit->notes)
                                            <br><small class="text-muted" title="{{ $deposit->notes }}">{{ \Illuminate\Support\Str::limit($deposit->notes, 30) }}</small>
                                        @endif
                                    @endif
                                </td>
                                <td>{{ $deposit->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    @if($deposit->processed_at)
                                        {{ $deposit->processed_at->format('M d, Y H:i') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        @if($deposit->status === 'pending')
                                            <button type="button" class="btn btn-sm btn-success" title="Approve" onclick="showApproveModal({{ $deposit->id }}, '{{ number_format($deposit->amount, 2) }}', '{{ addslashes($deposit->transactionable->user->name ?? 'N/A') }}', {{ $deposit->payment_method === 'manual' && $deposit->payment_screenshot ? 'true' : 'false' }}, '{{ $deposit->payment_method === 'manual' && $deposit->payment_screenshot ? addslashes($deposit->screenshot_url) : '' }}', '{{ addslashes($deposit->transaction_id ?? '') }}')">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" title="Reject" onclick="showRejectModal({{ $deposit->id }}, '{{ number_format($deposit->amount, 2) }}')">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        @else
                                            <span class="text-muted small">No actions</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">No deposits found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $deposits->links() }}
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
                        <h5 class="modal-title" id="approveModalLabel">Approve Deposit</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p style="margin-bottom: 16px;">Are you sure you want to approve deposit of <strong id="approveAmount"></strong> from <strong id="approveAdvertiser"></strong>?</p>
                        
                        <!-- Manual Payment Screenshot Section -->
                        <div id="manualPaymentInfo" style="display: none; margin-bottom: 20px;">
                            <div style="background: #f8f9fa; padding: 15px; border-radius: 6px; border-left: 4px solid #007bff;">
                                <strong style="display: block; margin-bottom: 10px;">Manual Payment Details:</strong>
                                <div style="margin-bottom: 10px;">
                                    <strong>Transaction ID:</strong> <code id="approveTransactionId" style="background: white; padding: 4px 8px; border-radius: 4px;"></code>
                                </div>
                                <div style="margin-bottom: 10px;">
                                    <strong>Payment Screenshot:</strong>
                                    <div style="margin-top: 10px; max-height: 300px; overflow-y: auto; border: 1px solid #ddd; border-radius: 6px; padding: 10px; background: white;">
                                        <img id="approveScreenshot" src="" alt="Payment Screenshot" style="max-width: 100%; border-radius: 6px; cursor: pointer; display: block;" onclick="showScreenshotModal(this.src, document.getElementById('approveTransactionId').textContent)">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <p class="text-success" style="margin-bottom: 24px; padding: 12px; background-color: #d4edda; border-left: 4px solid #28a745; border-radius: 4px;">
                            <i class="fas fa-info-circle"></i> <small>The amount will be added to the advertiser's balance.</small>
                        </p>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="approve_notes">Admin Notes (Optional)</label>
                            <textarea id="approve_notes" name="notes" class="form-control" rows="3" placeholder="Enter any notes about this approval..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check-circle"></i> Approve Deposit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Screenshot Modal -->
    <div class="modal fade" id="screenshotModal" tabindex="-1" role="dialog" aria-labelledby="screenshotModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="screenshotModalLabel">Payment Screenshot</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="text-align: center;">
                    <p style="margin-bottom: 15px;"><strong>Transaction ID:</strong> <code id="screenshotModalTid"></code></p>
                    <img id="screenshotModalImg" src="" alt="Payment Screenshot" style="max-width: 100%; border-radius: 6px; border: 1px solid #ddd;">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
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
                        <h5 class="modal-title" id="rejectModalLabel">Reject Deposit</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p style="margin-bottom: 16px;">Are you sure you want to reject deposit of <strong id="rejectAmount"></strong>?</p>
                        <p class="text-danger" style="margin-bottom: 24px; padding: 12px; background-color: #f8d7da; border-left: 4px solid #dc3545; border-radius: 4px;">
                            <i class="fas fa-exclamation-triangle"></i> <small>The deposit will be marked as failed and the advertiser will be notified.</small>
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
                            <i class="fas fa-times-circle"></i> Reject Deposit
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
        const modals = ['approveModal', 'rejectModal', 'screenshotModal'];
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
            // Scroll modal to top
            modal.scrollTop = 0;
            // Prevent body scroll but allow modal scroll
            document.body.style.overflow = 'hidden';
            // Ensure modal dialog is visible
            const modalDialog = modal.querySelector('.modal-dialog');
            if (modalDialog) {
                modalDialog.style.marginTop = '1.75rem';
                modalDialog.style.marginBottom = '1.75rem';
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

    function showApproveModal(id, amount, advertiser, hasScreenshot = false, screenshotUrl = '', transactionId = '') {
        const form = document.getElementById('approveForm');
        if (form) {
            form.reset();
        }
        const notesField = document.getElementById('approve_notes');
        if (notesField) notesField.value = '';
        
        const amountElement = document.getElementById('approveAmount');
        if (amountElement) {
            amountElement.textContent = '$' + amount;
        }
        
        const advertiserElement = document.getElementById('approveAdvertiser');
        if (advertiserElement) {
            advertiserElement.textContent = advertiser;
        }
        
        // Handle manual payment screenshot
        const manualPaymentInfo = document.getElementById('manualPaymentInfo');
        if (hasScreenshot && screenshotUrl) {
            manualPaymentInfo.style.display = 'block';
            document.getElementById('approveScreenshot').src = screenshotUrl;
            document.getElementById('approveTransactionId').textContent = transactionId || 'N/A';
        } else {
            manualPaymentInfo.style.display = 'none';
        }
        
        if (form) {
            form.action = '{{ route("dashboard.admin.deposits.approve", ":id") }}'.replace(':id', id);
        }
        
        showModal('approveModal');
    }

    function showScreenshotModal(screenshotUrl, transactionId) {
        document.getElementById('screenshotModalImg').src = screenshotUrl;
        document.getElementById('screenshotModalTid').textContent = transactionId || 'N/A';
        showModal('screenshotModal');
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
            form.action = '{{ route("dashboard.admin.deposits.reject", ":id") }}'.replace(':id', id);
        }
        
        showModal('rejectModal');
    }
</script>
@endpush
