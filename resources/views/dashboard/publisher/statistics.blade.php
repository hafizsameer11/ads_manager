@extends('dashboard.layouts.main')

@section('title', 'Statistics - Publisher Dashboard')

@section('content')
    <div class="page-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1>Statistics & Performance</h1>
                <p class="text-muted">Detailed statistics and performance metrics for your websites.</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('dashboard.publisher.analytics.geo') }}" class="btn btn-primary">
                    <i class="fas fa-globe"></i> Geo Analytics
                </a>
                <a href="{{ route('dashboard.publisher.analytics.device') }}" class="btn btn-primary">
                    <i class="fas fa-mobile-alt"></i> Device Analytics
                </a>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.publisher.statistics') }}">
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
                    <button type="submit" class="btn btn-primary">Update Statistics</button>
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
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">${{ number_format($stats['total_revenue'], 2) }}</div>
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
            <canvas id="statisticsChart" height="80"></canvas>
        </div>
    </div>
    @endif

    <!-- Website Performance Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Website Performance</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Website</th>
                            <th>Impressions</th>
                            <th>Clicks</th>
                            <th>CTR</th>
                            <th>Revenue</th>
                            <th>Ad Units</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($websiteStats as $site)
                        <tr>
                            <td>
                                <strong>{{ $site['website']->domain }}</strong>
                                <br>
                                <small class="text-muted">{{ $site['website']->name }}</small>
                            </td>
                            <td>{{ number_format($site['impressions']) }}</td>
                            <td>{{ number_format($site['clicks']) }}</td>
                            <td>
                                <span class="badge badge-success">{{ number_format($site['ctr'], 2) }}%</span>
                            </td>
                            <td>
                                <strong class="text-success">${{ number_format($site['revenue'], 2) }}</strong>
                            </td>
                            <td>{{ $site['website']->ad_units_count ?? 0 }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No website data available</td>
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
    const ctx = document.getElementById('statisticsChart').getContext('2d');
    const statisticsChart = new Chart(ctx, {
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
                    label: 'Revenue ($)',
                    data: {!! json_encode($dailyStats->pluck('revenue')) !!},
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
