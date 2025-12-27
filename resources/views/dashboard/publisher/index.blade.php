@extends('dashboard.layouts.main')

@section('title', 'Publisher Dashboard')

@push('styles')
<style>
    /* Enhanced Page Header */
    .page-header {
        margin-bottom: 2.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 2px solid var(--border-color);
    }

    .page-header h1 {
        font-size: 2.25rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.75rem;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        letter-spacing: -0.5px;
    }

    .page-header .text-muted {
        font-size: 1rem;
        color: var(--text-secondary);
    }

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
        .page-header h1 {
            font-size: 1.75rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
    {{-- <div class="page-header">
        <h1>Publisher Dashboard</h1>
        <p class="text-muted">Manage your websites, track earnings, and view statistics.</p>
    </div> --}}

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
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
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="fas fa-wallet"></i>
                </div>
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
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="fas fa-eye"></i>
                </div>
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
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="fas fa-globe"></i>
                </div>
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
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
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
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
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
