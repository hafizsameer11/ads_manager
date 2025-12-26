@extends('dashboard.layouts.main')

@section('title', 'Withdrawals Management - Admin Dashboard')

@section('content')
    <div class="page-header">
        <h1>Withdrawals Management</h1>
        <p class="text-muted">Process publisher withdrawal requests.</p>
    </div>

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
            <div class="stat-label">Total Amount</div>
            <div class="stat-value">${{ number_format($stats['total_amount'], 2) }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.admin.withdrawals') }}">
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
                    <div style="flex: 0 0 auto; min-width: 150px; max-width: 180px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">Payment Method</label>
                        <select name="payment_method" class="form-control">
                            <option value="">All Methods</option>
                            <option value="paypal" {{ request('payment_method') == 'paypal' ? 'selected' : '' }}>PayPal</option>
                            <option value="coinpayment" {{ request('payment_method') == 'coinpayment' ? 'selected' : '' }}>CoinPayment</option>
                            <option value="faucetpay" {{ request('payment_method') == 'faucetpay' ? 'selected' : '' }}>FaucetPay</option>
                            <option value="bank_swift" {{ request('payment_method') == 'bank_swift' ? 'selected' : '' }}>Bank SWIFT</option>
                        </select>
                    </div>
                    <div style="flex: 1 1 auto; min-width: 250px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by ID, publisher name or email..." value="{{ request('search') }}">
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('dashboard.admin.withdrawals') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Withdrawals Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Withdrawals</h3>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
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
                            <th>Payment Method</th>
                            <th>Payment Details</th>
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
                                <td>{{ ucfirst(str_replace('_', ' ', $withdrawal->payment_method ?? 'N/A')) }}</td>
                                <td>
                                    @if($withdrawal->payment_details)
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
                                    <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                        @if($withdrawal->status === 'pending')
                                            <form action="{{ route('dashboard.admin.withdrawals.approve', $withdrawal->id) }}" method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Approve" onclick="return confirm('Are you sure you want to approve this withdrawal?')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-danger" title="Reject" onclick="showRejectModal({{ $withdrawal->id }}, '{{ number_format($withdrawal->amount, 2) }}')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @elseif($withdrawal->status === 'approved')
                                            <button type="button" class="btn btn-sm btn-primary" title="Mark as Paid" onclick="showMarkPaidModal({{ $withdrawal->id }}, '{{ number_format($withdrawal->amount, 2) }}')">
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

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Withdrawal</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to reject withdrawal for <strong id="rejectAmount"></strong>?</p>
                        <p class="text-info"><small>The amount will be refunded to the publisher's balance.</small></p>
                        <div class="form-group">
                            <label for="rejection_reason">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea id="rejection_reason" name="rejection_reason" class="form-control" rows="3" required placeholder="Enter reason for rejection..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Withdrawal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Mark Paid Modal -->
    <div class="modal fade" id="markPaidModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="markPaidForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Mark Withdrawal as Paid</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Mark withdrawal for <strong id="paidAmount"></strong> as processed/paid?</p>
                        <div class="form-group">
                            <label for="transaction_id">Transaction ID (Optional)</label>
                            <input type="text" id="transaction_id" name="transaction_id" class="form-control" placeholder="Enter transaction ID or reference number...">
                            <small class="text-muted">Optional: Enter the payment gateway transaction ID or reference number.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Mark as Paid</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function showRejectModal(id, amount) {
        document.getElementById('rejectAmount').textContent = '$' + amount;
        document.getElementById('rejectForm').action = '{{ route("dashboard.admin.withdrawals.reject", ":id") }}'.replace(':id', id);
        $('#rejectModal').modal('show');
    }

    function showMarkPaidModal(id, amount) {
        document.getElementById('paidAmount').textContent = '$' + amount;
        document.getElementById('markPaidForm').action = '{{ route("dashboard.admin.withdrawals.mark-paid", ":id") }}'.replace(':id', id);
        $('#markPaidModal').modal('show');
    }
</script>
@endpush

