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
            <form method="POST" action="{{ route('dashboard.publisher.payments') }}">
                @csrf
                <input type="hidden" name="action" value="withdraw">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="amount">Amount</label>
                            <input type="number" id="amount" name="amount" class="form-control" 
                                   step="0.01" min="{{ $summary['minimum_payout'] }}" 
                                   max="{{ $summary['available_balance'] }}" 
                                   value="{{ $summary['available_balance'] }}" required>
                            <small class="text-muted">Min: ${{ number_format($summary['minimum_payout'], 2) }}</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="payment_method">Payment Method</label>
                            <select id="payment_method" name="payment_method" class="form-control" required>
                                <option value="">Select Method</option>
                                <option value="paypal">PayPal</option>
                                <option value="coinpayment">CoinPayment</option>
                                <option value="faucetpay">FaucetPay</option>
                                <option value="bank_swift">Bank SWIFT</option>
                                <option value="stripe">Stripe</option>
                                <option value="wise">Wise</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="payment_account">Payment Account</label>
                            <input type="text" id="payment_account" name="payment_account" class="form-control" 
                                   placeholder="Email, account number, etc." required>
                        </div>
                    </div>
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
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Processed At</th>
                            <th>Actions</th>
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
                                <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $withdrawal->payment_method ?? 'N/A')) }}</span>
                                @if($withdrawal->payment_account)
                                    <br><small class="text-muted">{{ $withdrawal->payment_account }}</small>
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
                                @if($withdrawal->processed_at)
                                    {{ $withdrawal->processed_at->format('M d, Y H:i') }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No withdrawals yet</td>
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
@endsection
