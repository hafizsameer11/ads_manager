@extends('dashboard.layouts.main')

@section('title', 'Advertiser Dashboard')

@push('styles')
<style>

    /* Stats Grid - Matching Admin Dashboard */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }

    /* Stat Cards - Matching Admin Dashboard */
    .stat-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: 12px;
        padding: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07), 0 1px 3px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-color), var(--info-color));
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12), 0 4px 8px rgba(0, 0, 0, 0.08);
    }

    .stat-card.primary::before {
        background: linear-gradient(90deg, #667eea, #764ba2);
    }

    .stat-card.success::before {
        background: linear-gradient(90deg, #11998e, #38ef7d);
    }

    .stat-card.info::before {
        background: linear-gradient(90deg, #3498db, #2980b9);
    }

    .stat-card.warning::before {
        background: linear-gradient(90deg, #f39c12, #e67e22);
    }

    .stat-card.danger::before {
        background: linear-gradient(90deg, #e74c3c, #c0392b);
    }

    .stat-card.secondary::before {
        background: linear-gradient(90deg, #2c3e50, #34495e);
    }

    .stat-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }

    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: white;
        background: linear-gradient(135deg, var(--primary-color), var(--info-color));
    }

    .stat-card.primary .stat-icon {
        background: linear-gradient(135deg, #667eea, #764ba2);
    }

    .stat-card.success .stat-icon {
        background: linear-gradient(135deg, #11998e, #38ef7d);
    }

    .stat-card.info .stat-icon {
        background: linear-gradient(135deg, #3498db, #2980b9);
    }

    .stat-card.warning .stat-icon {
        background: linear-gradient(135deg, #f39c12, #e67e22);
    }

    .stat-card.danger .stat-icon {
        background: linear-gradient(135deg, #e74c3c, #c0392b);
    }

    .stat-card.secondary .stat-icon {
        background: linear-gradient(135deg, #2c3e50, #34495e);
    }

    .stat-content {
        flex: 1;
    }

    .stat-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
        font-weight: 500;
        margin-bottom: 0.375rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.375rem;
        line-height: 1.2;
    }

    .stat-change {
        font-size: 0.75rem;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }

    /* Enhanced Card Styles */
    .card {
        background-color: var(--bg-primary);
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(0, 0, 0, 0.05);
        padding: 0;
        margin-bottom: 24px;
        overflow: hidden;
    }

    .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 24px;
        border-bottom: 1px solid var(--border-color);
        background: linear-gradient(135deg, #fafbfc 0%, #ffffff 100%);
    }

    .card-title {
        font-size: 20px;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }

    .card-body {
        padding: 24px;
        color: var(--text-secondary);
    }

    /* Enhanced Table Styles */
    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table thead {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    }

    .table th {
        padding: 14px 16px;
        text-align: left;
        font-weight: 600;
        color: var(--text-primary);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        border-bottom: 2px solid var(--border-color);
    }

    .table td {
        padding: 16px;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-secondary);
        vertical-align: middle;
    }

    .table tbody tr {
        transition: all 0.2s ease;
        background-color: var(--bg-primary);
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
        transform: scale(1.01);
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    /* Badge Enhancements */
    .badge {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-success {
        background: linear-gradient(135deg, #27ae60, #229954);
        color: white;
    }

    .badge-warning {
        background: linear-gradient(135deg, #f39c12, #e67e22);
        color: white;
    }

    .badge-danger {
        background: linear-gradient(135deg, #e74c3c, #c0392b);
        color: white;
    }

    .badge-info {
        background: linear-gradient(135deg, #3498db, #2980b9);
        color: white;
    }

    .badge-secondary {
        background: linear-gradient(135deg, #95a5a6, #7f8c8d);
        color: white;
    }

    /* Grid Layout */
    .grid-2 {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 24px;
    }

    /* Chart Container */
    #performanceChart {
        max-height: 400px;
    }

    /* Button Enhancements */
    .btn-sm {
        padding: 8px 16px;
        font-size: 13px;
        border-radius: 8px;
        font-weight: 500;
    }

    /* Responsive Design */
    @media (max-width: 1400px) {
        .stats-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    @media (max-width: 1024px) {
        .grid-2 {
            grid-template-columns: 1fr;
        }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="fas fa-bullhorn"></i>
                </div>
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
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="fas fa-eye"></i>
                </div>
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
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="fas fa-mouse-pointer"></i>
                </div>
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
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="fas fa-wallet"></i>
                </div>
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
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
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
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
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
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: {
                            size: 12,
                            weight: '500'
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: {
                        size: 13,
                        weight: '600'
                    },
                    bodyFont: {
                        size: 12
                    },
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: true
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        },
                        color: '#6b7280'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                        drawBorder: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        },
                        color: '#6b7280'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toFixed(2);
                        },
                        font: {
                            size: 11
                        },
                        color: '#6b7280'
                    },
                    grid: {
                        drawOnChartArea: false,
                        drawBorder: false
                    }
                }
            },
            elements: {
                point: {
                    radius: 4,
                    hoverRadius: 6,
                    borderWidth: 2
                },
                line: {
                    borderWidth: 2,
                    tension: 0.4
                }
            }
        }
    });
    @endif
</script>
@endpush
