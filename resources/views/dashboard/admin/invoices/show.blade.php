@extends('dashboard.layouts.main')

@section('title', 'Invoice Details - Admin Dashboard')

@push('styles')
<style>
    .invoice-container {
        max-width: 900px;
        margin: 0 auto;
        background: white;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .invoice-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 40px;
        padding-bottom: 20px;
        border-bottom: 2px solid #e9ecef;
    }

    .invoice-title {
        font-size: 32px;
        font-weight: bold;
        color: #333;
    }

    .invoice-number {
        font-size: 16px;
        color: #666;
        margin-top: 5px;
    }

    .invoice-info {
        text-align: right;
    }

    .invoice-info-item {
        margin-bottom: 8px;
        color: #666;
    }

    .invoice-info-label {
        font-weight: 600;
        color: #333;
    }

    .invoice-section {
        margin-bottom: 30px;
    }

    .invoice-section-title {
        font-size: 18px;
        font-weight: 600;
        color: #333;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e9ecef;
    }

    .invoice-company,
    .invoice-client {
        line-height: 1.8;
    }

    .invoice-company strong,
    .invoice-client strong {
        display: block;
        margin-bottom: 5px;
        color: #333;
    }

    .invoice-items-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    .invoice-items-table th,
    .invoice-items-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #e9ecef;
    }

    .invoice-items-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #333;
    }

    .invoice-items-table td {
        color: #666;
    }

    .invoice-items-table .text-right {
        text-align: right;
    }

    .invoice-summary {
        margin-top: 20px;
        float: right;
        width: 300px;
    }

    .invoice-summary-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .invoice-summary-row.total {
        border-top: 2px solid #333;
        border-bottom: 2px solid #333;
        font-size: 18px;
        font-weight: bold;
        margin-top: 10px;
        padding-top: 15px;
    }

    .invoice-summary-label {
        color: #666;
    }

    .invoice-summary-value {
        font-weight: 600;
        color: #333;
    }

    .invoice-actions {
        margin-top: 40px;
        padding-top: 20px;
        border-top: 2px solid #e9ecef;
        display: flex;
        gap: 10px;
    }

    .badge {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
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
</style>
@endpush

@section('content')
    <div style="margin-bottom: 20px;">
        <a href="{{ route('dashboard.admin.invoices') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Invoices
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="invoice-container">
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

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 40px;">
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
            <div style="clear: both;"></div>
        </div>

        @if($invoice->transaction)
        <div class="invoice-section">
            <div class="invoice-section-title">Payment Information</div>
            <div>
                <strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $invoice->invoice_data['payment_method'] ?? $invoice->transaction->payment_method ?? 'N/A')) }}<br>
                @if(!empty($invoice->invoice_data['transaction_id'] ?? $invoice->transaction->transaction_id))
                <strong>Transaction ID:</strong> <code>{{ $invoice->invoice_data['transaction_id'] ?? $invoice->transaction->transaction_id }}</code>
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

        <div class="invoice-actions">
            <a href="{{ route('dashboard.admin.invoices.download', $invoice->id) }}" class="btn btn-primary">
                <i class="fas fa-download"></i> Download PDF
            </a>
            @if($invoice->status === 'sent')
                <form action="{{ route('dashboard.admin.invoices.mark-paid', $invoice->id) }}" method="POST" onsubmit="return confirm('Mark this invoice as paid?');">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Mark as Paid
                    </button>
                </form>
            @endif
            @if($invoice->status === 'draft')
                <form action="{{ route('dashboard.admin.invoices.mark-sent', $invoice->id) }}" method="POST" onsubmit="return confirm('Mark this invoice as sent?');">
                    @csrf
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-paper-plane"></i> Mark as Sent
                    </button>
                </form>
            @endif
        </div>
    </div>
@endsection




