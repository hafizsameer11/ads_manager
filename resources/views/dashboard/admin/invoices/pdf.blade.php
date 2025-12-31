<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 40px;
            color: #333;
        }
        
        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 3px solid #333;
        }
        
        .invoice-title {
            font-size: 36px;
            font-weight: bold;
            color: #333;
        }
        
        .invoice-number {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        
        .invoice-info {
            text-align: right;
            line-height: 1.8;
        }
        
        .invoice-info-item {
            margin-bottom: 5px;
        }
        
        .invoice-info-label {
            font-weight: bold;
            color: #333;
        }
        
        .invoice-section {
            margin-bottom: 30px;
        }
        
        .invoice-section-title {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #ccc;
        }
        
        .invoice-company,
        .invoice-client {
            line-height: 1.8;
        }
        
        .invoice-company strong,
        .invoice-client strong {
            display: block;
            margin-bottom: 5px;
            font-size: 14px;
            color: #333;
        }
        
        .invoice-items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .invoice-items-table th,
        .invoice-items-table td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        
        .invoice-items-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            color: #333;
        }
        
        .invoice-items-table .text-right {
            text-align: right;
        }
        
        .invoice-summary {
            margin-top: 20px;
            float: right;
            width: 280px;
        }
        
        .invoice-summary-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #ddd;
        }
        
        .invoice-summary-row.total {
            border-top: 2px solid #333;
            border-bottom: 2px solid #333;
            font-size: 16px;
            font-weight: bold;
            margin-top: 10px;
            padding-top: 12px;
        }
        
        .invoice-summary-label {
            color: #666;
        }
        
        .invoice-summary-value {
            font-weight: bold;
            color: #333;
        }
        
        .clearfix {
            clear: both;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .badge-success {
            background: #28a745;
            color: white;
        }
        
        .badge-info {
            background: #17a2b8;
            color: white;
        }
        
        .badge-secondary {
            background: #6c757d;
            color: white;
        }
        
        .badge-danger {
            background: #dc3545;
            color: white;
        }
        
        @page {
            margin: 2cm;
        }
    </style>
</head>
<body>
    <div class="invoice-header">
        <div>
            <div class="invoice-title">INVOICE</div>
            <div class="invoice-number">{{ $invoice->invoice_number }}</div>
        </div>
        <div class="invoice-info">
            <div class="invoice-info-item">
                <span class="invoice-info-label">Status:</span>
                @if($invoice->status === 'paid')
                    <span class="badge badge-success">Paid</span>
                @elseif($invoice->status === 'sent')
                    <span class="badge badge-info">Sent</span>
                @elseif($invoice->status === 'draft')
                    <span class="badge badge-secondary">Draft</span>
                @else
                    <span class="badge badge-danger">Cancelled</span>
                @endif
            </div>
            <div class="invoice-info-item">
                <span class="invoice-info-label">Invoice Date:</span>
                {{ $invoice->invoice_date->format('M d, Y') }}
            </div>
            @if($invoice->due_date)
            <div class="invoice-info-item">
                <span class="invoice-info-label">Due Date:</span>
                {{ $invoice->due_date->format('M d, Y') }}
            </div>
            @endif
            @if($invoice->paid_at)
            <div class="invoice-info-item">
                <span class="invoice-info-label">Paid Date:</span>
                {{ $invoice->paid_at->format('M d, Y') }}
            </div>
            @endif
        </div>
    </div>

    <div style="display: table; width: 100%; margin-bottom: 40px;">
        <div style="display: table-cell; width: 50%; padding-right: 40px;">
            <div class="invoice-section">
                <div class="invoice-section-title">From</div>
                <div class="invoice-company">
                    @php
                        $company = $invoice->invoice_data['company'] ?? [];
                    @endphp
                    <strong>{{ $company['name'] ?? config('app.name') }}</strong>
                    @if(!empty($company['address']))
                        <div>{{ $company['address'] }}</div>
                    @endif
                    @if(!empty($company['phone']))
                        <div>Phone: {{ $company['phone'] }}</div>
                    @endif
                    @if(!empty($company['email']))
                        <div>Email: {{ $company['email'] }}</div>
                    @endif
                </div>
            </div>
        </div>
        <div style="display: table-cell; width: 50%;">
            <div class="invoice-section">
                <div class="invoice-section-title">Bill To</div>
                <div class="invoice-client">
                    @php
                        $client = $invoice->invoice_data['client'] ?? [];
                        $user = $invoice->invoiceable->user ?? null;
                    @endphp
                    <strong>{{ $client['name'] ?? $user->name ?? 'N/A' }}</strong>
                    @if(!empty($client['email'] ?? $user->email))
                        <div>{{ $client['email'] ?? $user->email }}</div>
                    @endif
                    @if(!empty($client['phone'] ?? $user->phone))
                        <div>Phone: {{ $client['phone'] ?? $user->phone }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="invoice-section">
        <div class="invoice-section-title">Items</div>
        <table class="invoice-items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-right">Quantity</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $items = $invoice->invoice_data['items'] ?? [];
                @endphp
                @foreach($items as $item)
                    <tr>
                        <td>{{ $item['description'] ?? 'N/A' }}</td>
                        <td class="text-right">{{ $item['quantity'] ?? 1 }}</td>
                        <td class="text-right">${{ number_format($item['unit_price'] ?? 0, 2) }}</td>
                        <td class="text-right">${{ number_format($item['total'] ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="invoice-summary">
            <div class="invoice-summary-row">
                <span class="invoice-summary-label">Subtotal:</span>
                <span class="invoice-summary-value">${{ number_format($invoice->amount, 2) }}</span>
            </div>
            @if($invoice->tax_amount > 0)
            <div class="invoice-summary-row">
                <span class="invoice-summary-label">Tax:</span>
                <span class="invoice-summary-value">${{ number_format($invoice->tax_amount, 2) }}</span>
            </div>
            @endif
            <div class="invoice-summary-row total">
                <span class="invoice-summary-label">Total:</span>
                <span class="invoice-summary-value">${{ number_format($invoice->total_amount, 2) }}</span>
            </div>
        </div>
        <div class="clearfix"></div>
    </div>

    @if($invoice->transaction)
    <div class="invoice-section">
        <div class="invoice-section-title">Payment Information</div>
        <div>
            <strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $invoice->invoice_data['payment_method'] ?? $invoice->transaction->payment_method ?? 'N/A')) }}<br>
            @if(!empty($invoice->invoice_data['transaction_id'] ?? $invoice->transaction->transaction_id))
            <strong>Transaction ID:</strong> {{ $invoice->invoice_data['transaction_id'] ?? $invoice->transaction->transaction_id }}
            @endif
        </div>
    </div>
    @endif

    @if($invoice->notes)
    <div class="invoice-section">
        <div class="invoice-section-title">Notes</div>
        <div>{{ $invoice->notes }}</div>
    </div>
    @endif
</body>
</html>

