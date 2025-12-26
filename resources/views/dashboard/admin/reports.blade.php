@extends('dashboard.layouts.main')

@section('title', 'Reports - Admin Dashboard')

@section('content')
    <div class="page-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1>Reports & Analytics</h1>
                <p class="text-muted">View detailed reports and analytics.</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('dashboard.admin.analytics.geo') }}" class="btn btn-primary">
                    <i class="fas fa-globe"></i> Geo Analytics
                </a>
                <a href="{{ route('dashboard.admin.analytics.device') }}" class="btn btn-primary">
                    <i class="fas fa-mobile-alt"></i> Device Analytics
                </a>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.admin.reports') }}">
                <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; margin-bottom: 10px;">
                    <div style="flex: 0 0 auto; min-width: 200px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}" required>
                    </div>
                    <div style="flex: 0 0 auto; min-width: 200px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}" required>
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Revenue Summary -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">${{ number_format($revenueData['total'], 2) }}</div>
        </div>
        <div class="stat-card success">
            <div class="stat-label">Admin Share (20%)</div>
            <div class="stat-value">${{ number_format($revenueData['admin_share'], 2) }}</div>
        </div>
        <div class="stat-card info">
            <div class="stat-label">Publisher Share (80%)</div>
            <div class="stat-value">${{ number_format($revenueData['publisher_share'], 2) }}</div>
        </div>
        <div class="stat-card warning">
            <div class="stat-label">CTR</div>
            <div class="stat-value">{{ number_format($ctr, 2) }}%</div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Performance Metrics</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="text-center">
                        <h4>{{ number_format($impressions) }}</h4>
                        <p class="text-muted">Total Impressions</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h4>{{ number_format($clicks) }}</h4>
                        <p class="text-muted">Total Clicks</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h4>{{ number_format($ctr, 2) }}%</h4>
                        <p class="text-muted">Click-Through Rate</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <h4>${{ number_format($revenueData['total'], 2) }}</h4>
                        <p class="text-muted">Total Revenue</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Stats Chart -->
    @if($dailyStats->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daily Performance Chart</h3>
        </div>
        <div class="card-body">
            <canvas id="dailyStatsChart" height="80"></canvas>
        </div>
    </div>
    @endif

    <div class="grid-2">
        <!-- Top Publishers -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Top Publishers</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Publisher</th>
                                <th>Email</th>
                                <th>Total Earnings</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($publisherPerformance as $pub)
                            <tr>
                                <td>{{ $pub->user->name }}</td>
                                <td>{{ $pub->user->email }}</td>
                                <td>${{ number_format($pub->total_earnings ?? 0, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Campaigns -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Top Campaigns</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Campaign</th>
                                <th>Advertiser</th>
                                <th>Impressions</th>
                                <th>Spent</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($campaignPerformance as $campaign)
                            <tr>
                                <td>{{ $campaign->name }}</td>
                                <td>{{ $campaign->advertiser->user->name ?? 'N/A' }}</td>
                                <td>{{ number_format($campaign->impressions) }}</td>
                                <td>${{ number_format($campaign->total_spent, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No data available</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    @if($dailyStats->count() > 0)
    const ctx = document.getElementById('dailyStatsChart').getContext('2d');
    const dailyStatsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: {!! json_encode($dailyStats->pluck('date')->map(function($date) {
                return \Carbon\Carbon::parse($date)->format('M d');
            })) !!},
            datasets: [
                {
                    label: 'Impressions',
                    data: {!! json_encode($dailyStats->pluck('impressions')) !!},
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y',
                },
                {
                    label: 'Clicks',
                    data: {!! json_encode($dailyStats->pluck('clicks')) !!},
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                }
            }
        }
    });
    @endif
</script>
@endpush
