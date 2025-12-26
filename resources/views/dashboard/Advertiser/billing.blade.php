@extends('dashboard.layouts.main')

@section('title', 'Billing - Advertiser Dashboard')

@section('content')
    <div class="page-header">
        <h1>Billing & Transactions</h1>
        <p class="text-muted">Manage your account balance and view transaction history.</p>
    </div>

    <!-- Summary Cards -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-label">Current Balance</div>
            <div class="stat-value">${{ number_format($summary['balance'], 2) }}</div>
        </div>
        <div class="stat-card danger">
            <div class="stat-label">Total Spent</div>
            <div class="stat-value">${{ number_format($summary['total_spent'], 2) }}</div>
        </div>
        <div class="stat-card success">
            <div class="stat-label">Total Deposits</div>
            <div class="stat-value">${{ number_format($summary['total_deposits'], 2) }}</div>
        </div>
        <div class="stat-card warning">
            <div class="stat-label">This Month Spend</div>
            <div class="stat-value">${{ number_format($summary['this_month_spend'], 2) }}</div>
        </div>
    </div>

    <!-- Deposit Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Deposit Funds</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.advertiser.billing.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="amount">Amount</label>
                            <input type="number" id="amount" name="amount" class="form-control" 
                                   step="0.01" min="10" required>
                            <small class="text-muted">Minimum: $10.00</small>
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
                                <option value="stripe">Stripe</option>
                                <option value="bank_swift">Bank SWIFT</option>
                                <option value="wise">Wise</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="transaction_id">Transaction ID (Optional)</label>
                            <input type="text" id="transaction_id" name="transaction_id" class="form-control" 
                                   placeholder="Payment reference">
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
                            <i class="fas fa-credit-card"></i> Process Deposit
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.advertiser.billing') }}">
                <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; margin-bottom: 10px;">
                    <div style="flex: 0 0 auto; min-width: 150px; max-width: 180px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">Type</label>
                        <select name="type" class="form-control">
                            <option value="">All Types</option>
                            <option value="deposit" {{ request('type') == 'deposit' ? 'selected' : '' }}>Deposit</option>
                            <option value="campaign_spend" {{ request('type') == 'campaign_spend' ? 'selected' : '' }}>Campaign Spend</option>
                            <option value="refund" {{ request('type') == 'refund' ? 'selected' : '' }}>Refund</option>
                        </select>
                    </div>
                    <div style="flex: 0 0 auto; min-width: 150px; max-width: 180px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <div style="flex: 0 0 auto; min-width: 150px; max-width: 180px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div style="flex: 0 0 auto; min-width: 150px; max-width: 180px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('dashboard.advertiser.billing') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Transaction History</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $transaction)
                        <tr>
                            <td>#{{ $transaction->id }}</td>
                            <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                @if($transaction->type === 'deposit')
                                    <span class="badge badge-success">Deposit</span>
                                @elseif($transaction->type === 'campaign_spend')
                                    <span class="badge badge-danger">Campaign Spend</span>
                                @elseif($transaction->type === 'refund')
                                    <span class="badge badge-info">Refund</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst(str_replace('_', ' ', $transaction->type)) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($transaction->type === 'deposit' || $transaction->type === 'refund')
                                    <strong class="text-success">+${{ number_format($transaction->amount ?? 0, 2) }}</strong>
                                @else
                                    <strong class="text-danger">-${{ number_format($transaction->amount ?? 0, 2) }}</strong>
                                @endif
                            </td>
                            <td>
                                @if($transaction->payment_method)
                                    <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $transaction->payment_method)) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if(($transaction->status ?? '') === 'completed')
                                    <span class="badge badge-success">Completed</span>
                                @elseif(($transaction->status ?? '') === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif(($transaction->status ?? '') === 'failed')
                                    <span class="badge badge-danger">Failed</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($transaction->status ?? 'Unknown') }}</span>
                                @endif
                            </td>
                            <td>{{ $transaction->notes ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No transactions yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-3">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
@endsection
