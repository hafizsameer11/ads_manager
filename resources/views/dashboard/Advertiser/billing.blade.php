@extends('dashboard.layouts.main')

@section('title', 'Billing - Advertiser Dashboard')

@section('content')
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
            <form method="POST" action="{{ route('dashboard.advertiser.billing.store') }}" enctype="multipart/form-data">
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
                            <select id="payment_method" name="payment_method" class="form-control" required onchange="toggleManualPaymentFields(); toggleStripeFields();">
                                <option value="">Select Method</option>
                                @if($paypalEnabled)
                                    <option value="paypal">PayPal (Automatic)</option>
                                @endif
                                @if($coinpaymentsEnabled)
                                    <option value="coinpayment">CoinPayments (Automatic)</option>
                                @endif
                                @if($stripeEnabled)
                                    <option value="stripe">Stripe (Automatic)</option>
                                @endif
                                @if($faucetpayEnabled)
                                <option value="faucetpay">FaucetPay</option>
                                @endif
                                @if($bankSwiftEnabled)
                                <option value="bank_swift">Bank SWIFT</option>
                                @endif
                                @if($wiseEnabled)
                                <option value="wise">Wise</option>
                                @endif
                                @if($manualPaymentAccounts->count() > 0)
                                    <option value="manual">Manual Payment</option>
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="transaction_id" id="transaction_id_label">Transaction ID <span id="transaction_id_required" style="display: none;" class="text-danger">*</span><span id="transaction_id_optional">(Optional)</span></label>
                            <input type="text" id="transaction_id" name="transaction_id" class="form-control" 
                                   placeholder="Payment reference">
                        </div>
                    </div>
                </div>

                <!-- Manual Payment Account Selection (shown only when manual is selected) -->
                <div class="row" id="manual_payment_account_section" style="display: none;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="manual_payment_account_id">Select Payment Account <span class="text-danger">*</span></label>
                            <select id="manual_payment_account_id" name="manual_payment_account_id" class="form-control">
                                <option value="">Select Account</option>
                                @foreach($manualPaymentAccounts as $account)
                                    <option value="{{ $account->id }}" data-account-number="{{ $account->account_number }}" data-account-type="{{ $account->account_type }}">
                                        {{ $account->account_name }} ({{ $account->account_type }}) - {{ $account->account_number }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Select the payment account you used for this deposit</small>
                        </div>
                    </div>
                </div>

                <!-- Payment Screenshot (shown only when manual is selected) -->
                <div class="row" id="payment_screenshot_section" style="display: none;">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="payment_screenshot">Payment Screenshot <span class="text-danger">*</span></label>
                            <input type="file" id="payment_screenshot" name="payment_screenshot" class="form-control" accept="image/*" onchange="previewScreenshot(this)">
                            <small class="text-muted">Upload a screenshot of your payment confirmation. Max size: 5MB. Formats: JPEG, PNG, JPG, GIF, WEBP</small>
                            <div id="screenshotPreview" class="mt-2" style="display: none;">
                                <img id="previewImg" src="" alt="Screenshot Preview" style="max-width: 300px; border-radius: 6px; border: 1px solid #ddd;">
                            </div>
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
                            <i class="fas fa-credit-card"></i> Process Deposit
                        </button>
                    </div>
                </div>
            </form>

            <script>
                function toggleManualPaymentFields() {
                    const paymentMethod = document.getElementById('payment_method').value;
                    const manualSection = document.getElementById('manual_payment_account_section');
                    const screenshotSection = document.getElementById('payment_screenshot_section');
                    const transactionIdField = document.getElementById('transaction_id');
                    const transactionIdLabel = document.getElementById('transaction_id_label');
                    const transactionIdRequired = document.getElementById('transaction_id_required');
                    const transactionIdOptional = document.getElementById('transaction_id_optional');
                    const manualAccountSelect = document.getElementById('manual_payment_account_id');
                    const screenshotInput = document.getElementById('payment_screenshot');

                    if (paymentMethod === 'manual') {
                        manualSection.style.display = 'block';
                        screenshotSection.style.display = 'block';
                        transactionIdField.required = true;
                        transactionIdRequired.style.display = 'inline';
                        transactionIdOptional.style.display = 'none';
                        manualAccountSelect.required = true;
                        screenshotInput.required = true;
                    } else {
                        manualSection.style.display = 'none';
                        screenshotSection.style.display = 'none';
                        transactionIdField.required = false;
                        transactionIdRequired.style.display = 'none';
                        transactionIdOptional.style.display = 'inline';
                        manualAccountSelect.required = false;
                        manualAccountSelect.value = '';
                        screenshotInput.required = false;
                        screenshotInput.value = '';
                        document.getElementById('screenshotPreview').style.display = 'none';
                    }
                }

                function toggleStripeFields() {
                    const paymentMethod = document.getElementById('payment_method').value;
                    const submitButton = document.querySelector('button[type="submit"]');
                    const automaticGateways = ['stripe', 'paypal', 'coinpayment'];
                    
                    if (automaticGateways.includes(paymentMethod)) {
                        // For automatic gateways, we'll redirect to checkout, so change button text
                        if (submitButton) {
                            let gatewayName = paymentMethod.charAt(0).toUpperCase() + paymentMethod.slice(1);
                            if (paymentMethod === 'coinpayment') {
                                gatewayName = 'CoinPayments';
                            }
                            submitButton.innerHTML = '<i class="fas fa-credit-card"></i> Proceed to ' + gatewayName + ' Checkout';
                        }
                    } else {
                        if (submitButton) {
                            submitButton.innerHTML = '<i class="fas fa-credit-card"></i> Process Deposit';
                        }
                    }
                }

                function previewScreenshot(input) {
                    const preview = document.getElementById('screenshotPreview');
                    const previewImg = document.getElementById('previewImg');
                    
                    if (input.files && input.files[0]) {
                        const reader = new FileReader();
                        
                        reader.onload = function(e) {
                            previewImg.src = e.target.result;
                            preview.style.display = 'block';
                        }
                        
                        reader.readAsDataURL(input.files[0]);
                    } else {
                        preview.style.display = 'none';
                    }
                }

                // Handle form submission for automatic payment gateways
                document.querySelector('form').addEventListener('submit', function(e) {
                    const paymentMethod = document.getElementById('payment_method').value;
                    const automaticGateways = ['stripe', 'paypal', 'coinpayment'];
                    
                    if (automaticGateways.includes(paymentMethod)) {
                        e.preventDefault();
                        const amount = document.getElementById('amount').value;
                        if (!amount || parseFloat(amount) < 10) {
                            alert('Please enter a valid amount (minimum $10.00)');
                            return;
                        }
                        
                        // Redirect to appropriate checkout
                        let checkoutUrl = '';
                        if (paymentMethod === 'stripe') {
                            checkoutUrl = '{{ route("dashboard.advertiser.stripe.checkout") }}?amount=' + amount;
                        } else if (paymentMethod === 'paypal') {
                            checkoutUrl = '{{ route("dashboard.advertiser.paypal.checkout") }}?amount=' + amount;
                        } else if (paymentMethod === 'coinpayment') {
                            checkoutUrl = '{{ route("dashboard.advertiser.coinpayments.checkout") }}?amount=' + amount;
                        }
                        
                        if (checkoutUrl) {
                            window.location.href = checkoutUrl;
                        }
                    }
                });
            </script>
            
            @if($stripeEnabled && $stripePublishableKey)
                <script src="https://js.stripe.com/v3/"></script>
            @endif
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
