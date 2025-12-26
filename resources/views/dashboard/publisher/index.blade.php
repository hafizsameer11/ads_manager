@extends('dashboard.layouts.main')

@section('title', 'Publisher Dashboard')

@section('content')
    <div class="page-header">
        <h1>Publisher Dashboard</h1>
        <p class="text-muted">Manage your websites, track earnings, and view statistics.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Earnings</div>
                <div class="stat-value">${{ number_format($totalEarnings, 2) }}</div>
                <div class="stat-change">
                    <i class="fas fa-calendar"></i>
                    <span>${{ number_format($monthEarnings, 2) }} this month</span>
                </div>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Available Balance</div>
                <div class="stat-value">${{ number_format($availableBalance, 2) }}</div>
                <div class="stat-change">
                    <i class="fas fa-clock"></i>
                    <span>${{ number_format($pendingBalance, 2) }} pending</span>
                </div>
            </div>
        </div>

        <div class="stat-card info">
            <div class="stat-icon">
                <i class="fas fa-eye"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Today's Impressions</div>
                <div class="stat-value">{{ number_format($todayImpressions) }}</div>
                <div class="stat-change">
                    <i class="fas fa-mouse-pointer"></i>
                    <span>{{ number_format($todayClicks) }} clicks today</span>
                </div>
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-globe"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Active Sites</div>
                <div class="stat-value">{{ number_format($totalSites) }}</div>
                <div class="stat-change">
                    <i class="fas fa-info-circle"></i>
                    <span>{{ number_format($totalAdUnits) }} ad units</span>
                </div>
            </div>
        </div>

        <div class="stat-card secondary">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Monthly Impressions</div>
                <div class="stat-value">{{ number_format($monthImpressions) }}</div>
                <div class="stat-change">
                    <i class="fas fa-percentage"></i>
                    <span>{{ number_format($monthCTR, 2) }}% CTR</span>
                </div>
            </div>
        </div>

        <div class="stat-card danger">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Referrals</div>
                <div class="stat-value">{{ number_format($referrals) }}</div>
                <div class="stat-change">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ number_format($activeReferrals) }} active</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Chart -->
    @if($dailyStats->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Performance Trend (Last 30 Days)</h3>
        </div>
        <div class="card-body">
            <canvas id="performanceChart" height="80"></canvas>
        </div>
    </div>
    @endif

    <div class="grid-2">
        <!-- Recent Websites -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Websites</h3>
                <a href="{{ route('dashboard.publisher.sites') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Domain</th>
                                <th>Status</th>
                                <th>Ad Units</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentWebsites as $website)
                            <tr>
                                <td>
                                    <strong>{{ $website->domain }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $website->name }}</small>
                                </td>
                                <td>
                                    @if(in_array($website->status, ['approved', 'verified']))
                                        <span class="badge badge-success">Approved</span>
                                    @elseif($website->status === 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @else
                                        <span class="badge badge-danger">Rejected</span>
                                    @endif
                                </td>
                                <td>{{ $website->adUnits->count() }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">
                                    <p>No websites yet. <a href="{{ route('dashboard.publisher.sites') }}">Add your first website</a></p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Earnings -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Earnings</h3>
                <a href="{{ route('dashboard.publisher.earnings') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentEarnings as $earning)
                            <tr>
                                <td>{{ $earning->created_at->format('M d, Y') }}</td>
                                <td>${{ number_format($earning->amount ?? 0, 2) }}</td>
                                <td>
                                    @if(($earning->status ?? '') === 'completed')
                                        <span class="badge badge-success">Completed</span>
                                    @else
                                        <span class="badge badge-warning">Pending</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No earnings yet</td>
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
    const ctx = document.getElementById('performanceChart').getContext('2d');
    const performanceChart = new Chart(ctx, {
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
