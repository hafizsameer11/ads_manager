@extends('dashboard.layouts.main')

@section('title', 'Admin Dashboard')

@push('styles')
<style>
    .page-header {
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--border-color);
    }
    
    .page-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--info-color) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
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
    
    .stat-card.primary::before { background: linear-gradient(90deg, #667eea, #764ba2); }
    .stat-card.success::before { background: linear-gradient(90deg, #11998e, #38ef7d); }
    .stat-card.info::before { background: linear-gradient(90deg, #3498db, #2980b9); }
    .stat-card.warning::before { background: linear-gradient(90deg, #f39c12, #e67e22); }
    .stat-card.danger::before { background: linear-gradient(90deg, #e74c3c, #c0392b); }
    .stat-card.secondary::before { background: linear-gradient(90deg, #2c3e50, #34495e); }
    
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
    
    .stat-card.primary .stat-icon { background: linear-gradient(135deg, #667eea, #764ba2); }
    .stat-card.success .stat-icon { background: linear-gradient(135deg, #11998e, #38ef7d); }
    .stat-card.info .stat-icon { background: linear-gradient(135deg, #3498db, #2980b9); }
    .stat-card.warning .stat-icon { background: linear-gradient(135deg, #f39c12, #e67e22); }
    .stat-card.danger .stat-icon { background: linear-gradient(135deg, #e74c3c, #c0392b); }
    .stat-card.secondary .stat-icon { background: linear-gradient(135deg, #2c3e50, #34495e); }
    
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
    
    .charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .chart-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07), 0 1px 3px rgba(0, 0, 0, 0.06);
        border: 1px solid var(--border-color);
    }
    
    .chart-card-header {
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-color);
    }
    
    .chart-card-header h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
    }
    
    .chart-container.pie-chart {
        height: 250px;
    }
    
    .grid-2 {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07), 0 1px 3px rgba(0, 0, 0, 0.06);
        border: 1px solid var(--border-color);
        overflow: hidden;
    }
    
    .card-header {
        padding: 1.5rem;
        border-bottom: 1px solid var(--border-color);
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    }
    
    .card-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    .table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table thead th {
        background: #f8f9fa;
        padding: 0.75rem;
        text-align: left;
        font-weight: 600;
        color: var(--text-primary);
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid var(--border-color);
    }
    
    .table tbody td {
        padding: 1rem 0.75rem;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-primary);
    }
    
    .table tbody tr:hover {
        background: #f8f9fa;
    }
    
    .badge {
        padding: 0.375rem 0.75rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .badge-success {
        background: linear-gradient(135deg, #11998e, #38ef7d);
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
    
    @media (max-width: 1400px) {
        .stats-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }
    
    @media (max-width: 1024px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .charts-grid {
            grid-template-columns: 1fr;
        }
    }
    
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .grid-2 {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
    {{-- <div class="page-header">
        <h1>Dashboard Overview</h1>
        <p class="text-muted">Welcome back! Here's what's happening with your ad network.</p>
    </div> --}}

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Publishers</div>
                <div class="stat-value">{{ number_format($totalPublishers) }}</div>
                <div class="stat-change">
                    <i class="fas fa-info-circle"></i>
                    <span>Active network</span>
                </div>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Advertisers</div>
                <div class="stat-value">{{ number_format($totalAdvertisers) }}</div>
                <div class="stat-change">
                    <i class="fas fa-info-circle"></i>
                    <span>Active accounts</span>
                </div>
            </div>
        </div>

        <div class="stat-card info">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="fas fa-bullhorn"></i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Active Campaigns</div>
                <div class="stat-value">{{ number_format($activeCampaigns) }}</div>
                <div class="stat-change">
                    <i class="fas fa-clock"></i>
                    <span>{{ $pendingCampaigns }} pending approval</span>
                </div>
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="fas fa-eye"></i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Impressions</div>
                <div class="stat-value">{{ number_format($totalImpressions) }}</div>
                <div class="stat-change">
                    <i class="fas fa-mouse-pointer"></i>
                    <span>{{ number_format($totalClicks) }} clicks ({{ number_format($totalCTR, 2) }}% CTR)</span>
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
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value">${{ number_format($totalRevenue, 2) }}</div>
                <div class="stat-change">
                    <i class="fas fa-calendar"></i>
                    <span>${{ number_format($monthlyRevenue, 2) }} this month</span>
                </div>
            </div>
        </div>

        <div class="stat-card secondary">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Admin Revenue (20%)</div>
                <div class="stat-value">${{ number_format($adminRevenue, 2) }}</div>
                <div class="stat-change">
                    <i class="fas fa-info-circle"></i>
                    <span>${{ number_format($publisherPayouts, 2) }} to publishers (80%)</span>
                </div>
            </div>
        </div>

        <div class="stat-card info">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Pending Withdrawals</div>
                <div class="stat-value">{{ number_format($pendingWithdrawals) }}</div>
                <div class="stat-change">
                    <i class="fas fa-dollar-sign"></i>
                    <span>${{ number_format($pendingWithdrawalAmount, 2) }} pending</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-grid">
        <!-- Revenue & Traffic Trend Chart -->
        @if($dailyStats->count() > 0)
        <div class="chart-card">
            <div class="chart-card-header">
                <h3><i class="fas fa-chart-line text-primary"></i> Revenue & Traffic Trend (Last 30 Days)</h3>
            </div>
            <div class="chart-container">
                <canvas id="revenueTrafficChart"></canvas>
            </div>
        </div>
        @endif

        <!-- Revenue Distribution Pie Chart -->
        @if($impressionRevenue > 0 || $clickRevenue > 0)
        <div class="chart-card">
            <div class="chart-card-header">
                <h3><i class="fas fa-chart-pie text-danger"></i> Revenue Distribution</h3>
            </div>
            <div class="chart-container pie-chart">
                <canvas id="revenueDistributionChart"></canvas>
            </div>
        </div>
        @endif

        <!-- Campaign Status Distribution -->
        @if(array_sum($campaignStatusData) > 0)
        <div class="chart-card">
            <div class="chart-card-header">
                <h3><i class="fas fa-chart-pie text-info"></i> Campaign Status Distribution</h3>
            </div>
            <div class="chart-container pie-chart">
                <canvas id="campaignStatusChart"></canvas>
            </div>
        </div>
        @endif

        <!-- Monthly Revenue Comparison -->
        @if($monthlyRevenueStats->count() > 0)
        <div class="chart-card">
            <div class="chart-card-header">
                <h3><i class="fas fa-chart-bar text-success"></i> Monthly Revenue (Last 6 Months)</h3>
            </div>
            <div class="chart-container">
                <canvas id="monthlyRevenueChart"></canvas>
            </div>
        </div>
        @endif

        <!-- User Growth Chart -->
        @if($userGrowthStats->count() > 0)
        <div class="chart-card">
            <div class="chart-card-header">
                <h3><i class="fas fa-users text-warning"></i> User Growth (Last 12 Months)</h3>
            </div>
            <div class="chart-container">
                <canvas id="userGrowthChart"></canvas>
            </div>
        </div>
        @endif
    </div>

    <!-- Top Campaigns -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-trophy text-warning"></i> Top Performing Campaigns</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Campaign</th>
                            <th>Advertiser</th>
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
                                <br>
                                <small class="text-muted">{{ ucfirst($campaign->ad_type) }}</small>
                            </td>
                            <td>{{ $campaign->advertiser->user->name ?? 'N/A' }}</td>
                            <td>{{ number_format($campaign->impressions) }}</td>
                            <td>{{ number_format($campaign->clicks) }}</td>
                            <td>
                                <span class="badge badge-success">{{ number_format($campaign->calculateCTR(), 2) }}%</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No campaigns yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Top Publishers -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-star text-success"></i> Top Publishers</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Publisher</th>
                            <th>Email</th>
                            <th>Total Earnings</th>
                            <th>Balance</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topPublishers as $publisher)
                        <tr>
                            <td>
                                <strong>{{ $publisher->user->name }}</strong>
                            </td>
                            <td>{{ $publisher->user->email }}</td>
                            <td>${{ number_format($publisher->total_earnings ?? 0, 2) }}</td>
                            <td>${{ number_format($publisher->balance ?? 0, 2) }}</td>
                            <td>
                                @if(($publisher->status ?? '') === 'approved')
                                    <span class="badge badge-success">Approved</span>
                                @elseif(($publisher->status ?? '') === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @else
                                    <span class="badge badge-danger">Rejected</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No publishers yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-exchange-alt text-info"></i> Recent Transactions</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentTransactions as $transaction)
                        <tr>
                            <td>#{{ $transaction->id }}</td>
                            <td>
                                <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $transaction->type)) }}</span>
                            </td>
                            <td>
                                @if($transaction->transactionable)
                                    {{ $transaction->transactionable->user->name ?? 'N/A' }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>${{ number_format($transaction->amount ?? 0, 2) }}</td>
                            <td>
                                @if(($transaction->status ?? '') === 'completed')
                                    <span class="badge badge-success">Completed</span>
                                @elseif(($transaction->status ?? '') === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @else
                                    <span class="badge badge-danger">{{ ucfirst($transaction->status ?? 'Unknown') }}</span>
                                @endif
                            </td>
                            <td>{{ $transaction->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No transactions yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Chart.js default configuration
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.font.size = 12;
    Chart.defaults.color = '#6b7280';
    Chart.defaults.borderColor = '#e5e7eb';
    
    // Revenue & Traffic Trend Chart
    @if($dailyStats->count() > 0)
    const revenueTrafficCtx = document.getElementById('revenueTrafficChart').getContext('2d');
    new Chart(revenueTrafficCtx, {
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
                    fill: true,
                    yAxisID: 'y',
                },
                {
                    label: 'Clicks',
                    data: {!! json_encode($dailyStats->pluck('clicks')) !!},
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y',
                },
                {
                    label: 'Revenue ($)',
                    data: {!! json_encode($dailyStats->pluck('revenue')) !!},
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 },
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString();
                        }
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toFixed(2);
                        }
                    }
                }
            }
        }
    });
    @endif

    // Revenue Distribution Pie Chart
    @if($impressionRevenue > 0 || $clickRevenue > 0)
    const revenueDistCtx = document.getElementById('revenueDistributionChart').getContext('2d');
    new Chart(revenueDistCtx, {
        type: 'doughnut',
        data: {
            labels: ['Impression Revenue', 'Click Revenue'],
            datasets: [{
                data: [{{ $impressionRevenue }}, {{ $clickRevenue }}],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                ],
                borderColor: [
                    'rgb(54, 162, 235)',
                    'rgb(255, 99, 132)',
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': $';
                            }
                            label += context.parsed.toFixed(2);
                            return label;
                        }
                    }
                }
            }
        }
    });
    @endif

    // Campaign Status Distribution
    @if(array_sum($campaignStatusData) > 0)
    const campaignStatusCtx = document.getElementById('campaignStatusChart').getContext('2d');
    new Chart(campaignStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Paused', 'Pending', 'Rejected'],
            datasets: [{
                data: [
                    {{ $campaignStatusData['active'] }},
                    {{ $campaignStatusData['paused'] }},
                    {{ $campaignStatusData['pending'] }},
                    {{ $campaignStatusData['rejected'] }}
                ],
                backgroundColor: [
                    'rgba(39, 174, 96, 0.8)',
                    'rgba(243, 156, 18, 0.8)',
                    'rgba(52, 152, 219, 0.8)',
                    'rgba(231, 76, 60, 0.8)',
                ],
                borderColor: [
                    'rgb(39, 174, 96)',
                    'rgb(243, 156, 18)',
                    'rgb(52, 152, 219)',
                    'rgb(231, 76, 60)',
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });
    @endif

    // Monthly Revenue Comparison
    @if($monthlyRevenueStats->count() > 0)
    const monthlyRevenueCtx = document.getElementById('monthlyRevenueChart').getContext('2d');
    new Chart(monthlyRevenueCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($monthlyRevenueStats->pluck('month')->map(function($month) {
                return \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y');
            })) !!},
            datasets: [{
                label: 'Revenue ($)',
                data: {!! json_encode($monthlyRevenueStats->pluck('revenue')) !!},
                backgroundColor: 'rgba(75, 192, 192, 0.8)',
                borderColor: 'rgb(75, 192, 192)',
                borderWidth: 2,
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Revenue: $' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toFixed(2);
                        }
                    }
                }
            }
        }
    });
    @endif

    // User Growth Chart
    @if($userGrowthStats->count() > 0)
    const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
    new Chart(userGrowthCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($userGrowthStats->pluck('month')->map(function($month) {
                return \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M Y');
            })) !!},
            datasets: [{
                label: 'New Users',
                data: {!! json_encode($userGrowthStats->pluck('count')) !!},
                borderColor: 'rgb(255, 159, 64)',
                backgroundColor: 'rgba(255, 159, 64, 0.1)',
                tension: 0.4,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    @endif
</script>
@endpush
