@extends('dashboard.layouts.main')

@section('title', 'Payments - Publisher Dashboard')

@section('content')
    <!-- Summary Cards -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-label">Available Balance</div>
            <div class="stat-value">${{ number_format($summary['available_balance'], 2) }}</div>
        </div>
        <div class="stat-card warning">
            <div class="stat-label">Pending Balance</div>
            <div class="stat-value">${{ number_format($summary['pending_balance'], 2) }}</div>
        </div>
        <div class="stat-card info">
            <div class="stat-label">Minimum Payout</div>
            <div class="stat-value">${{ number_format($summary['minimum_payout'], 2) }}</div>
        </div>
        <div class="stat-card success">
            <div class="stat-label">Total Withdrawn</div>
            <div class="stat-value">${{ number_format($summary['total_withdrawn'], 2) }}</div>
        </div>
    </div>

    <!-- Withdrawal Request Form -->
    @if($summary['can_withdraw'])
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Request Withdrawal</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.publisher.payments') }}" id="withdrawalForm">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="amount">Amount</label>
                            <input type="number" id="amount" name="amount" class="form-control" 
                                   step="0.01" min="{{ $summary['minimum_payout'] }}" 
                                   max="{{ min($summary['available_balance'], $summary['maximum_payout']) }}" 
                                   value="{{ min($summary['available_balance'], $summary['maximum_payout']) }}" required>
                            <small class="text-muted">
                                Min: ${{ number_format($summary['minimum_payout'], 2) }} | 
                                Max: ${{ number_format($summary['maximum_payout'], 2) }}
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="account_type_id">Account Type <span class="text-danger">*</span></label>
                            <select id="account_type_id" name="account_type_id" class="form-control" required>
                                <option value="">Select Account Type</option>
                                @foreach($allowedAccountTypes as $accountType)
                                    <option value="{{ $accountType->id }}">{{ $accountType->name }}</option>
                                @endforeach
                            </select>
                            @if($allowedAccountTypes->isEmpty())
                                <small class="text-danger">No account types available. Please contact admin.</small>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="account_name">Account Name <span class="text-danger">*</span></label>
                            <input type="text" id="account_name" name="account_name" class="form-control" 
                                   placeholder="Enter account holder name" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="account_number">Account Number <span class="text-danger">*</span></label>
                            <input type="text" id="account_number" name="account_number" class="form-control" 
                                   placeholder="Enter account number" required>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="notes">Notes (Optional)</label>
                            <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-money-bill-wave"></i> Request Withdrawal
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @else
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        You need at least ${{ number_format($summary['minimum_payout'], 2) }} in your balance to request a withdrawal.
        Current balance: ${{ number_format($summary['available_balance'], 2) }}
    </div>
    @endif

    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.publisher.payments') }}">
                <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; margin-bottom: 10px;">
                    <div style="flex: 0 0 auto; min-width: 150px; max-width: 180px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="processed" {{ request('status') == 'processed' ? 'selected' : '' }}>Processed</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('dashboard.publisher.payments') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Withdrawals Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Withdrawal History</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Account Type</th>
                            <th>Account Details</th>
                            <th>Status</th>
                            <th>Screenshot</th>
                            <th>Processed At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($withdrawals as $withdrawal)
                        <tr>
                            <td>#{{ $withdrawal->id }}</td>
                            <td>{{ $withdrawal->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                <strong class="text-primary">${{ number_format($withdrawal->amount, 2) }}</strong>
                            </td>
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
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($withdrawal->status === 'processed')
                                    <span class="badge badge-success">Processed</span>
                                @elseif($withdrawal->status === 'approved')
                                    <span class="badge badge-info">Approved</span>
                                @elseif($withdrawal->status === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @else
                                    <span class="badge badge-danger">Rejected</span>
                                    @if($withdrawal->rejection_reason)
                                        <br><small class="text-muted">{{ $withdrawal->rejection_reason }}</small>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if($withdrawal->payment_screenshot)
                                    <button type="button" class="btn btn-sm btn-info" onclick="showScreenshotModal('{{ addslashes($withdrawal->screenshot_url) }}', '{{ addslashes($withdrawal->transaction_id ?? 'N/A') }}')" title="View Screenshot">
                                        <i class="fas fa-image"></i> View
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($withdrawal->processed_at)
                                    {{ $withdrawal->processed_at->format('M d, Y H:i') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No withdrawals yet</td>
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
                <div class="modal-body">
                    <p style="margin-bottom: 15px;"><strong>Transaction ID:</strong> <code id="screenshotModalTid"></code></p>
                    <div style="text-align: center;">
                        <img id="screenshotModalImg" src="" alt="Payment Screenshot" style="max-width: 100%; border-radius: 6px; border: 1px solid #ddd;">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
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
        max-width: 800px;
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
        overflow-y: auto;
        max-height: calc(90vh - 180px);
    }

    .modal-footer {
        border-top: 1px solid var(--border-color);
        padding: 20px 30px;
        background-color: var(--bg-secondary);
        border-radius: 0 0 12px 12px;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    #screenshotModalImg {
        max-width: 100%;
        height: auto;
        display: block;
        margin: 0 auto;
    }
</style>
@endpush

@push('scripts')
<script>
    // Modal functionality
    document.addEventListener('DOMContentLoaded', function() {
        const modals = ['screenshotModal'];
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
            document.body.style.overflow = 'hidden';
            modal.scrollTop = 0;
        }
    }

    function hideModal(modal) {
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    function showScreenshotModal(screenshotUrl, transactionId) {
        document.getElementById('screenshotModalImg').src = screenshotUrl;
        document.getElementById('screenshotModalTid').textContent = transactionId || 'N/A';
        showModal('screenshotModal');
    }
</script>
@endpush
