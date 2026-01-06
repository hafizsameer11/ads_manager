@extends('dashboard.layouts.main')

@section('title', 'Invoices Management - Admin Dashboard')

@push('styles')
<style>
    /* Same styles as deposits page */
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

    .table {
        width: 100%;
        margin-bottom: 0;
        border-collapse: collapse;
    }

    .table thead th {
        padding: 16px;
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
</style>
@endpush

@section('content')
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Invoices</div>
            <div class="stat-value">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Draft</div>
            <div class="stat-value">{{ number_format($stats['draft']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Sent</div>
            <div class="stat-value">{{ number_format($stats['sent']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Paid</div>
            <div class="stat-value">{{ number_format($stats['paid']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Paid</div>
            <div class="stat-value">${{ number_format($stats['total_amount'], 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pending Amount</div>
            <div class="stat-value">${{ number_format($stats['pending_amount'], 2) }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.admin.invoices') }}">
                <div class="filter-form-row">
                    <div class="filter-field">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-control">
                            <option value="">All Types</option>
                            <option value="deposit" {{ request('type') == 'deposit' ? 'selected' : '' }}>Deposit</option>
                            <option value="withdrawal" {{ request('type') == 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="filter-field">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="filter-field filter-field-search">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by invoice number, ID, client name or email..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('dashboard.admin.invoices') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card">
        <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3 class="card-title">All Invoices</h3>
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

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <strong>Info!</strong>
                    <p>{{ session('info') }}</p>
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
                            <th>Invoice #</th>
                            <th>Client</th>
                            <th>Amount</th>
                            <th>Tax</th>
                            <th>Total</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Invoice Date</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td><code>{{ $invoice->invoice_number }}</code></td>
                                <td>
                                    @if($invoice->invoiceable && $invoice->invoiceable->user)
                                        <div><strong>{{ $invoice->invoiceable->user->name }}</strong></div>
                                        <small class="text-muted">{{ $invoice->invoiceable->user->email }}</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>${{ number_format($invoice->amount, 2) }}</td>
                                <td>${{ number_format($invoice->tax_amount, 2) }}</td>
                                <td><strong>${{ number_format($invoice->total_amount, 2) }}</strong></td>
                                <td>{{ ucfirst(str_replace('_', ' ', $invoice->type)) }}</td>
                                <td>
                                    @if($invoice->status === 'paid')
                                        <span class="badge badge-success">Paid</span>
                                    @elseif($invoice->status === 'sent')
                                        <span class="badge badge-info">Sent</span>
                                    @elseif($invoice->status === 'draft')
                                        <span class="badge badge-secondary">Draft</span>
                                    @else
                                        <span class="badge badge-danger">Cancelled</span>
                                    @endif
                                </td>
                                <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                                <td>
                                    @if($invoice->due_date)
                                        {{ $invoice->due_date->format('M d, Y') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('dashboard.admin.invoices.show', $invoice->id) }}" class="btn btn-sm btn-info" title="View Invoice">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="{{ route('dashboard.admin.invoices.download', $invoice->id) }}" class="btn btn-sm btn-primary" title="Download PDF">
                                            <i class="fas fa-download"></i> PDF
                                        </a>
                                        @if($invoice->status === 'sent')
                                            <form action="{{ route('dashboard.admin.invoices.mark-paid', $invoice->id) }}" method="POST" class="action-form" onsubmit="return confirm('Mark this invoice as paid?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Mark as Paid">
                                                    <i class="fas fa-check"></i> Paid
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">No invoices found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $invoices->links() }}
            </div>
        </div>
    </div>
@endsection





