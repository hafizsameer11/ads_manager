@extends('dashboard.layouts.main')

@section('title', 'Manual Payment Accounts - Admin Dashboard')

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

    .card {
        overflow: visible;
        max-width: 100%;
        padding: 16px;
    }

    .card-header {
        padding: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-body {
        overflow-x: visible;
        max-width: 100%;
        padding: 16px;
    }

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
        min-width: 600px;
        font-size: 13px;
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

    .badge {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-block;
        align-items: center;
        gap: 4px;
        position: relative;
        vertical-align: middle;
    }

    .table td {
        position: relative;
    }

    .table td .badge {
        display: inline-block;
        position: relative;
        float: none;
        clear: both;
    }

    .badge-success {
        background: linear-gradient(135deg, #27ae60, #229954);
        color: white;
    }

    .badge-danger {
        background: linear-gradient(135deg, #e74c3c, #c0392b);
        color: white;
    }

    .badge-secondary {
        background: linear-gradient(135deg, #95a5a6, #7f8c8d);
        color: white;
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

    .account-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid var(--border-color);
    }

    .account-image-placeholder {
        width: 50px;
        height: 50px;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--text-secondary);
        font-size: 20px;
    }
</style>
@endpush

@section('content')
    <!-- Manual Payment Accounts Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Manual Payment Accounts</h3>
            <a href="{{ route('dashboard.admin.manual-payment-accounts.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Account
            </a>
        </div>
        <div class="card-body">
            @if(session('success') && (str_contains(session('success'), 'Manual payment account') || str_contains(session('success'), 'payment account')))
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
                            <th>Image</th>
                            <th>Account Name</th>
                            <th>Account Number</th>
                            <th>Account Type</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $account)
                            <tr>
                                <td>
                                    @if($account->account_image)
                                        <img src="{{ $account->image_url }}" alt="{{ $account->account_name }}" class="account-image">
                                    @else
                                        <div class="account-image-placeholder">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    @endif
                                </td>
                                <td><strong>{{ $account->account_name }}</strong></td>
                                <td>{{ $account->account_number }}</td>
                                <td>{{ $account->account_type }}</td>
                                <td>
                                    @if($account->is_enabled)
                                        <span class="badge badge-success">Enabled</span>
                                    @else
                                        <span class="badge badge-danger">Disabled</span>
                                    @endif
                                </td>
                                <td>{{ $account->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('dashboard.admin.manual-payment-accounts.edit', $account->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('dashboard.admin.manual-payment-accounts.toggle-status', $account->id) }}" method="POST" class="action-form">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-{{ $account->is_enabled ? 'warning' : 'success' }}" title="{{ $account->is_enabled ? 'Disable' : 'Enable' }}">
                                                <i class="fas fa-{{ $account->is_enabled ? 'ban' : 'check' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('dashboard.admin.manual-payment-accounts.destroy', $account->id) }}" method="POST" class="action-form" onsubmit="return confirm('Are you sure you want to delete this payment account? This action cannot be undone.');">
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
                                <td colspan="7" class="text-center text-muted">No manual payment accounts found. <a href="{{ route('dashboard.admin.manual-payment-accounts.create') }}">Create one now</a></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $accounts->links() }}
            </div>
        </div>
    </div>

    <!-- Allowed Account Types Table -->
    <div class="card" style="margin-top: 30px;">
        <div class="card-header">
            <h3 class="card-title">Allowed Account Types</h3>
            <a href="{{ route('dashboard.admin.allowed-account-types.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Account Type
            </a>
        </div>
        <div class="card-body">
            @if(session('success') && (str_contains(session('success'), 'Account type') || str_contains(session('success'), 'account type')))
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
                            <th>Description</th>
                            <th>Status</th>
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
                                <td>{{ $accountType->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('dashboard.admin.allowed-account-types.edit', $accountType->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('dashboard.admin.allowed-account-types.toggle-status', $accountType->id) }}" method="POST" class="action-form">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $accountType->is_enabled ? 'btn-warning' : 'btn-success' }}" title="{{ $accountType->is_enabled ? 'Disable' : 'Enable' }}">
                                                <i class="fas fa-{{ $accountType->is_enabled ? 'ban' : 'check' }}"></i>
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

