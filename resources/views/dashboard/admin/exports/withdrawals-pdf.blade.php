<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Withdrawals Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h1>Withdrawals Report</h1>
    <p><strong>Generated:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Publisher</th>
                <th>Email</th>
                <th class="text-right">Amount</th>
                <th>Payment Method</th>
                <th>Account Type</th>
                <th>Status</th>
                <th>Transaction ID</th>
                <th>Created At</th>
                <th>Processed At</th>
            </tr>
        </thead>
        <tbody>
            @forelse($withdrawals as $withdrawal)
                @php
                    $publisher = $withdrawal->publisher;
                    $user = $publisher && $publisher->user ? $publisher->user : null;
                @endphp
                <tr>
                    <td>{{ $withdrawal->id }}</td>
                    <td>{{ $user ? $user->name : 'N/A' }}</td>
                    <td>{{ $user ? $user->email : 'N/A' }}</td>
                    <td class="text-right">${{ number_format($withdrawal->amount, 2) }}</td>
                    <td>{{ $withdrawal->payment_method ?? 'N/A' }}</td>
                    <td>{{ $withdrawal->account_type ?? 'N/A' }}</td>
                    <td>{{ ucfirst($withdrawal->status) }}</td>
                    <td>{{ $withdrawal->transaction_id ?? 'N/A' }}</td>
                    <td>{{ $withdrawal->created_at->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $withdrawal->processed_at ? $withdrawal->processed_at->format('Y-m-d H:i:s') : 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center;">No withdrawals found</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="summary">
        <strong>Summary:</strong><br>
        Total Records: {{ $withdrawals->count() }}<br>
        Total Amount: ${{ number_format($withdrawals->sum('amount'), 2) }}
    </div>
</body>
</html>

