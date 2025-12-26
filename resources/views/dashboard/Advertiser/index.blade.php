@extends('dashboard.layouts.main')

@section('title', 'Advertiser Dashboard')

@section('content')
    <div class="page-header">
        <h1>Advertiser Dashboard</h1>
        <p class="text-muted">Manage your campaigns, track performance, and analyze results.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-icon">
                <i class="fas fa-bullhorn"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Active Campaigns</div>
                <div class="stat-value">{{ number_format($activeCampaigns) }}</div>
                <div class="stat-change">
                    <i class="fas fa-pause"></i>
                    <span>{{ number_format($pausedCampaigns) }} paused, {{ number_format($pendingCampaigns) }} pending</span>
                </div>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-icon">
                <i class="fas fa-eye"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Impressions</div>
                <div class="stat-value">{{ number_format($totalImpressions) }}</div>
                <div class="stat-change">
                    <i class="fas fa-calendar"></i>
                    <span>{{ number_format($monthImpressions) }} this month</span>
                </div>
            </div>
        </div>

        <div class="stat-card info">
            <div class="stat-icon">
                <i class="fas fa-mouse-pointer"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Clicks</div>
                <div class="stat-value">{{ number_format($totalClicks) }}</div>
                <div class="stat-change">
                    <i class="fas fa-percentage"></i>
                    <span>{{ number_format($totalCTR, 2) }}% CTR</span>
                </div>
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-icon">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Remaining Budget</div>
                <div class="stat-value">${{ number_format($remainingBudget, 2) }}</div>
                <div class="stat-change">
                    <i class="fas fa-chart-pie"></i>
                    <span>{{ number_format($budgetPercentage, 1) }}% spent</span>
                </div>
            </div>
        </div>

        <div class="stat-card danger">
            <div class="stat-icon">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Spent</div>
                <div class="stat-value">${{ number_format($totalSpent, 2) }}</div>
                <div class="stat-change">
                    <i class="fas fa-info-circle"></i>
                    <span>of ${{ number_format($totalBudget, 2) }} total</span>
                </div>
            </div>
        </div>

        <div class="stat-card secondary">
            <div class="stat-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <div class="stat-label">Monthly Clicks</div>
                <div class="stat-value">{{ number_format($monthClicks) }}</div>
                <div class="stat-change">
                    <i class="fas fa-arrow-up"></i>
                    <span>This month</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Chart -->
    @if($dailyStats->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Campaign Performance (Last 30 Days)</h3>
        </div>
        <div class="card-body">
            <canvas id="performanceChart" height="80"></canvas>
        </div>
    </div>
    @endif

    <div class="grid-2">
        <!-- Recent Campaigns -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Recent Campaigns</h3>
                <div>
                    <a href="{{ route('dashboard.advertiser.campaigns') }}" class="btn btn-sm btn-secondary">View All</a>
                    <a href="{{ route('dashboard.advertiser.create-campaign') }}" class="btn btn-sm btn-primary">Create New</a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Campaign</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Impressions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentCampaigns as $campaign)
                            <tr>
                                <td>
                                    <strong>{{ $campaign->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $campaign->pricing_model === 'cpc' ? 'CPC' : 'CPM' }}: ${{ number_format($campaign->bid_amount, 2) }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ ucfirst($campaign->ad_type) }}</span>
                                </td>
                                <td>
                                    @if($campaign->status === 'active' && $campaign->approval_status === 'approved')
                                        <span class="badge badge-success">Active</span>
                                    @elseif($campaign->approval_status === 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @elseif($campaign->status === 'paused')
                                        <span class="badge badge-secondary">Paused</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ number_format($campaign->impressions) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    <p>No campaigns yet. <a href="{{ route('dashboard.advertiser.create-campaign') }}">Create your first campaign</a></p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Top Performing Campaigns -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Top Performing Campaigns</h3>
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
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topCampaigns as $campaign)
                            <tr>
                                <td>
                                    <strong>{{ $campaign->name }}</strong>
                                </td>
                                <td>{{ number_format($campaign->impressions) }}</td>
                                <td>{{ number_format($campaign->clicks) }}</td>
                                <td>
                                    <span class="badge badge-success">{{ number_format($campaign->calculateCTR(), 2) }}%</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">No campaign data yet</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Budget Overview -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Budget Overview</h3>
        </div>
        <div class="card-body">
            <div class="progress mb-3" style="height: 30px;">
                <div class="progress-bar bg-{{ $budgetPercentage > 80 ? 'danger' : ($budgetPercentage > 50 ? 'warning' : 'success') }}" 
                     role="progressbar" 
                     style="width: {{ $budgetPercentage }}%"
                     aria-valuenow="{{ $budgetPercentage }}" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                    {{ number_format($budgetPercentage, 1) }}% Used
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <div class="text-center">
                        <h5>${{ number_format($remainingBudget, 2) }}</h5>
                        <small class="text-muted">Remaining Budget</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <h5>${{ number_format($totalSpent, 2) }}</h5>
                        <small class="text-muted">Total Spent</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <h5>${{ number_format($totalBudget, 2) }}</h5>
                        <small class="text-muted">Total Budget</small>
                    </div>
                </div>
            </div>
            @if($remainingBudget < 100)
            <div class="alert alert-warning mt-3">
                <i class="fas fa-exclamation-triangle"></i>
                Your budget is running low. <a href="{{ route('dashboard.advertiser.billing') }}">Add funds</a> to continue your campaigns.
            </div>
            @endif
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
