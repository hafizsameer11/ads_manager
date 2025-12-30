@extends('dashboard.layouts.main')

@section('title', 'Analytics - Advertiser Dashboard')

@section('content')
    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 20px;">
                <a href="{{ route('dashboard.advertiser.analytics.geo') }}" class="btn btn-primary">
                    <i class="fas fa-globe"></i> Geo Analytics
                </a>
                <a href="{{ route('dashboard.advertiser.analytics.device') }}" class="btn btn-primary">
                    <i class="fas fa-mobile-alt"></i> Device Analytics
                </a>
    </div>

    <!-- Date Range Filter -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.advertiser.analytics') }}">
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
                    <button type="submit" class="btn btn-primary">Update Analytics</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-label">Total Impressions</div>
            <div class="stat-value">{{ number_format($stats['total_impressions']) }}</div>
        </div>
        <div class="stat-card success">
            <div class="stat-label">Total Clicks</div>
            <div class="stat-value">{{ number_format($stats['total_clicks']) }}</div>
        </div>
        <div class="stat-card info">
            <div class="stat-label">CTR</div>
            <div class="stat-value">{{ number_format($stats['ctr'], 2) }}%</div>
        </div>
        <div class="stat-card warning">
            <div class="stat-label">Total Spend</div>
            <div class="stat-value">${{ number_format($stats['total_spend'], 2) }}</div>
        </div>
        <div class="stat-card secondary">
            <div class="stat-label">CPC</div>
            <div class="stat-value">${{ number_format($stats['cpc'], 4) }}</div>
        </div>
        <div class="stat-card danger">
            <div class="stat-label">CPM</div>
            <div class="stat-value">${{ number_format($stats['cpm'], 2) }}</div>
        </div>
    </div>

    <!-- Daily Stats Chart -->
    @if($dailyStats->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Performance Chart</h3>
        </div>
        <div class="card-body">
            <canvas id="analyticsChart" height="80"></canvas>
        </div>
    </div>
    @endif

    <!-- Campaign Performance Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Campaign Performance</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Campaign</th>
                            <th>Impressions</th>
                            <th>Clicks</th>
                            <th>CTR</th>
                            <th>Spend</th>
                            <th>CPC</th>
                            <th>CPM</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($campaignPerformance as $perf)
                        <tr>
                            <td>
                                <strong>{{ $perf['campaign']->name }}</strong>
                                <br>
                                <small class="text-muted">{{ ucfirst($perf['campaign']->ad_type) }}</small>
                            </td>
                            <td>{{ number_format($perf['impressions']) }}</td>
                            <td>{{ number_format($perf['clicks']) }}</td>
                            <td>
                                <span class="badge badge-success">{{ number_format($perf['ctr'], 2) }}%</span>
                            </td>
                            <td>${{ number_format($perf['spend'], 2) }}</td>
                            <td>${{ number_format($perf['cpc'], 4) }}</td>
                            <td>${{ number_format($perf['cpm'], 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No campaign data available</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    @if($dailyStats->count() > 0)
    const ctx = document.getElementById('analyticsChart').getContext('2d');
    const analyticsChart = new Chart(ctx, {
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
                },
                {
                    label: 'Spend ($)',
                    data: {!! json_encode($dailyStats->pluck('spend')) !!},
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toFixed(2);
                        }
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
    @endif
</script>
@endpush
