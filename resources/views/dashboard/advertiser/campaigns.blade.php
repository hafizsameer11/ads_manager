@extends('dashboard.layouts.main')

@section('title', 'My Campaigns - Advertiser Dashboard')

@section('content')
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Campaigns</div>
            <div class="stat-value">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Active</div>
            <div class="stat-value">{{ number_format($stats['active']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Paused</div>
            <div class="stat-value">{{ number_format($stats['paused']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Spent</div>
            <div class="stat-value">${{ number_format($stats['total_spent'], 2) }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.advertiser.campaigns') }}">
                <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; margin-bottom: 10px;">
                    <div style="flex: 0 0 auto; min-width: 150px; max-width: 160px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="paused" {{ request('status') == 'paused' ? 'selected' : '' }}>Paused</option>
                            <option value="stopped" {{ request('status') == 'stopped' ? 'selected' : '' }}>Stopped</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                    <div style="flex: 0 0 auto; min-width: 150px; max-width: 160px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">Approval Status</label>
                        <select name="approval_status" class="form-control">
                            <option value="">All</option>
                            <option value="pending" {{ request('approval_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('approval_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('approval_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div style="flex: 0 0 auto; min-width: 150px; max-width: 160px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">Ad Type</label>
                        <select name="ad_type" class="form-control">
                            <option value="">All Types</option>
                            <option value="banner" {{ request('ad_type') == 'banner' ? 'selected' : '' }}>Banner</option>
                            <option value="popup" {{ request('ad_type') == 'popup' ? 'selected' : '' }}>Popup</option>
                            <option value="popunder" {{ request('ad_type') == 'popunder' ? 'selected' : '' }}>Popunder</option>
                        </select>
                    </div>
                    <div style="flex: 1 1 auto; min-width: 200px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search campaigns..." value="{{ request('search') }}">
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('dashboard.advertiser.campaigns') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Campaigns Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Campaigns</h3>
            <a href="{{ route('dashboard.advertiser.create-campaign') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Create New Campaign
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Campaign Name</th>
                            <th>Type</th>
                            <th>Pricing</th>
                            <th>Budget</th>
                            <th>Spent</th>
                            <th>Impressions</th>
                            <th>Clicks</th>
                            <th>CTR</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($campaigns as $campaign)
                        <tr>
                            <td>
                                <strong>{{ $campaign->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $campaign->target_url }}</small>
                            </td>
                            <td>
                                <span class="badge badge-info">{{ ucfirst($campaign->ad_type) }}</span>
                            </td>
                            <td>
                                {{ strtoupper($campaign->pricing_model) }}
                                <br>
                                <small>${{ number_format($campaign->bid_amount, 2) }}</small>
                            </td>
                            <td>${{ number_format($campaign->budget, 2) }}</td>
                            <td>
                                <strong>${{ number_format($campaign->total_spent, 2) }}</strong>
                                <br>
                                <small class="text-muted">{{ number_format(($campaign->total_spent / $campaign->budget) * 100, 1) }}%</small>
                            </td>
                            <td>{{ number_format($campaign->impressions) }}</td>
                            <td>{{ number_format($campaign->clicks) }}</td>
                            <td>
                                <span class="badge badge-success">{{ number_format($campaign->calculateCTR(), 2) }}%</span>
                            </td>
                            <td>
                                @if($campaign->approval_status === 'pending')
                                    <span class="badge badge-warning">Pending Approval</span>
                                @elseif($campaign->approval_status === 'rejected')
                                    <span class="badge badge-danger">Rejected</span>
                                    @if($campaign->rejection_reason)
                                        <br><small class="text-muted">{{ $campaign->rejection_reason }}</small>
                                    @endif
                                @elseif($campaign->status === 'active')
                                    <span class="badge badge-success">Active</span>
                                @elseif($campaign->status === 'paused')
                                    <span class="badge badge-secondary">Paused</span>
                                @elseif($campaign->status === 'stopped')
                                    <span class="badge badge-dark">Stopped</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    @if($campaign->approval_status === 'approved')
                                        @if($campaign->status === 'active')
                                        <form action="{{ route('dashboard.advertiser.campaigns.pause', $campaign->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning" title="Pause" onclick="return confirm('Are you sure you want to pause this campaign?')">
                                                <i class="fas fa-pause"></i>
                                            </button>
                                        </form>
                                            <form action="{{ route('dashboard.advertiser.campaigns.stop', $campaign->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger" title="Stop (Permanent)" onclick="return confirm('Are you sure you want to stop this campaign permanently? This action cannot be undone.')">
                                                    <i class="fas fa-stop"></i>
                                                </button>
                                            </form>
                                        @elseif($campaign->status === 'paused')
                                        <form action="{{ route('dashboard.advertiser.campaigns.resume', $campaign->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Resume" onclick="return confirm('Are you sure you want to resume this campaign?')">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        </form>
                                            <form action="{{ route('dashboard.advertiser.campaigns.stop', $campaign->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-danger" title="Stop (Permanent)" onclick="return confirm('Are you sure you want to stop this campaign permanently? This action cannot be undone.')">
                                                    <i class="fas fa-stop"></i>
                                        </button>
                                            </form>
                                        @elseif($campaign->status === 'stopped')
                                            <span class="badge badge-secondary">Stopped</span>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted">
                                <p>No campaigns yet. <a href="{{ route('dashboard.advertiser.create-campaign') }}">Create your first campaign</a></p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-3">
                {{ $campaigns->links() }}
            </div>
        </div>
    </div>
@endsection
