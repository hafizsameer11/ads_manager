@extends('dashboard.layouts.main')

@section('title', 'Campaigns Management - Admin Dashboard')

@section('content')
    <div class="page-header">
        <h1>Campaigns Management</h1>
        <p class="text-muted">Manage and monitor all campaigns.</p>
    </div>

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
            <div class="stat-label">Pending</div>
            <div class="stat-value">{{ number_format($stats['pending']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Spent</div>
            <div class="stat-value">${{ number_format($stats['total_spent'], 2) }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.admin.campaigns') }}">
                <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; margin-bottom: 10px;">
                    <div style="flex: 0 0 auto; min-width: 150px; max-width: 160px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="paused" {{ request('status') == 'paused' ? 'selected' : '' }}>Paused</option>
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
                    <a href="{{ route('dashboard.admin.campaigns') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Campaigns Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Campaigns</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Campaign Name</th>
                            <th>Advertiser</th>
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
                            <td>#{{ $campaign->id }}</td>
                            <td>
                                <strong>{{ $campaign->name }}</strong>
                                <br>
                                <small class="text-muted">{{ $campaign->target_url }}</small>
                            </td>
                            <td>
                                {{ $campaign->advertiser->user->name ?? 'N/A' }}
                                <br>
                                <small class="text-muted">{{ $campaign->advertiser->user->email ?? '' }}</small>
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
                            <td>${{ number_format($campaign->total_spent, 2) }}</td>
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
                                @elseif($campaign->status === 'active')
                                    <span class="badge badge-success">Active</span>
                                @elseif($campaign->status === 'paused')
                                    <span class="badge badge-secondary">Paused</span>
                                @else
                                    <span class="badge badge-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                    <button class="btn btn-sm btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @if($campaign->approval_status === 'pending')
                                        <form action="{{ route('dashboard.admin.campaigns.approve', $campaign->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Approve" onclick="return confirm('Are you sure you want to approve this campaign?')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-danger" title="Reject" onclick="showRejectModal({{ $campaign->id }}, '{{ $campaign->name }}')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                    @if($campaign->status === 'active' && $campaign->approval_status === 'approved')
                                        <form action="{{ route('dashboard.admin.campaigns.pause', $campaign->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-warning" title="Pause" onclick="return confirm('Are you sure you want to pause this campaign?')">
                                                <i class="fas fa-pause"></i>
                                            </button>
                                        </form>
                                    @elseif($campaign->status === 'paused' && $campaign->approval_status === 'approved')
                                        <form action="{{ route('dashboard.admin.campaigns.resume', $campaign->id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Resume" onclick="return confirm('Are you sure you want to resume this campaign?')">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <button class="btn btn-sm btn-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted">No campaigns found</td>
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

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Campaign</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to reject <strong id="rejectCampaignName"></strong>?</p>
                        <div class="form-group">
                            <label for="rejection_reason">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea id="rejection_reason" name="rejection_reason" class="form-control" rows="3" placeholder="Enter reason for rejection..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Campaign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function showRejectModal(id, name) {
        document.getElementById('rejectCampaignName').textContent = name;
        document.getElementById('rejectForm').action = '{{ route("dashboard.admin.campaigns.reject", ":id") }}'.replace(':id', id);
        $('#rejectModal').modal('show');
    }
</script>
@endpush
