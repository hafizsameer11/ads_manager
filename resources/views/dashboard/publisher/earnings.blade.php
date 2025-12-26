@extends('dashboard.layouts.main')

@section('title', 'Earnings - Publisher Dashboard')

@section('content')
    <div class="page-header">
        <h1>My Earnings</h1>
        <p class="text-muted">Track your earnings and revenue.</p>
    </div>

    <!-- Summary Cards -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-label">Total Earnings</div>
            <div class="stat-value">${{ number_format($summary['total_earnings'], 2) }}</div>
        </div>
        <div class="stat-card success">
            <div class="stat-label">Available Balance</div>
            <div class="stat-value">${{ number_format($summary['available_balance'], 2) }}</div>
        </div>
        <div class="stat-card warning">
            <div class="stat-label">Pending Balance</div>
            <div class="stat-value">${{ number_format($summary['pending_balance'], 2) }}</div>
        </div>
        <div class="stat-card info">
            <div class="stat-label">This Month</div>
            <div class="stat-value">${{ number_format($summary['this_month'], 2) }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.publisher.earnings') }}">
                <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; margin-bottom: 10px;">
                    <div style="flex: 0 0 auto; min-width: 200px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div style="flex: 0 0 auto; min-width: 200px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('dashboard.publisher.earnings') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Earnings Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Earnings History</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($earnings as $earning)
                        <tr>
                            <td>#{{ $earning->id }}</td>
                            <td>{{ $earning->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                <strong class="text-success">${{ number_format($earning->amount ?? 0, 2) }}</strong>
                            </td>
                            <td>
                                @if($earning->payment_method)
                                    <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $earning->payment_method)) }}</span>
                                @else
                                    <span class="badge badge-secondary">Ad Revenue</span>
                                @endif
                            </td>
                            <td>
                                @if(($earning->status ?? '') === 'completed')
                                    <span class="badge badge-success">Completed</span>
                                @else
                                    <span class="badge badge-warning">Pending</span>
                                @endif
                            </td>
                            <td>{{ $earning->notes ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No earnings yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-3">
                {{ $earnings->links() }}
            </div>
        </div>
    </div>
@endsection
