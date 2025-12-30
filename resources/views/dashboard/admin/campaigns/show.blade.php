@extends('dashboard.layouts.main')

@section('title', 'Campaign Details - Admin Dashboard')

@section('content')
    <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
                <a href="{{ route('dashboard.admin.campaigns') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Campaigns
                </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="alert-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="alert-content">
                <strong><i class="fas fa-check"></i> Success!</strong>
                <p>{{ session('success') }}</p>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <!-- Campaign Information -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 class="card-title">Campaign Information</h3>
                        @if($campaign->approval_status === 'pending')
                            <div style="display: flex; gap: 5px;">
                                <form action="{{ route('dashboard.admin.campaigns.approve', $campaign->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to approve this campaign?')">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>
                                <button type="button" class="btn btn-danger" onclick="showRejectModal()">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </div>
                        @elseif($campaign->status === 'active' && $campaign->approval_status === 'approved')
                            <form action="{{ route('dashboard.admin.campaigns.pause', $campaign->id) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-warning" onclick="return confirm('Are you sure you want to pause this campaign?')">
                                    <i class="fas fa-pause"></i> Pause
                                </button>
                            </form>
                        @elseif($campaign->status === 'paused')
                            <form action="{{ route('dashboard.admin.campaigns.resume', $campaign->id) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to resume this campaign?')">
                                    <i class="fas fa-play"></i> Resume
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Campaign Name:</th>
                            <td><strong>{{ $campaign->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Advertiser:</th>
                            <td>
                                @if($campaign->advertiser && $campaign->advertiser->user)
                                    <div><strong>{{ $campaign->advertiser->user->name }}</strong></div>
                                    <small class="text-muted">{{ $campaign->advertiser->user->email }}</small>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Ad Type:</th>
                            <td><span class="badge badge-info">{{ ucfirst($campaign->ad_type) }}</span></td>
                        </tr>
                        <tr>
                            <th>Pricing Model:</th>
                            <td>{{ strtoupper($campaign->pricing_model) }}</td>
                        </tr>
                        <tr>
                            <th>Bid Amount:</th>
                            <td><strong>${{ number_format($campaign->bid_amount, 4) }}</strong></td>
                        </tr>
                        <tr>
                            <th>Budget:</th>
                            <td><strong>${{ number_format($campaign->budget, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <th>Daily Budget:</th>
                            <td>
                                @if($campaign->daily_budget)
                                    ${{ number_format($campaign->daily_budget, 2) }}
                                @else
                                    <span class="text-muted">Not set</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Total Spent:</th>
                            <td>
                                <strong>${{ number_format($campaign->total_spent, 2) }}</strong>
                                @if($campaign->budget > 0)
                                    <br><small class="text-muted">{{ number_format(($campaign->total_spent / $campaign->budget) * 100, 1) }}% of budget used</small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Remaining Budget:</th>
                            <td><strong>${{ number_format($campaign->budget - $campaign->total_spent, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <th>Target URL:</th>
                            <td><a href="{{ $campaign->target_url }}" target="_blank">{{ $campaign->target_url }}</a></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                @if($campaign->status === 'active')
                                    <span class="badge badge-success">Active</span>
                                @elseif($campaign->status === 'paused')
                                    <span class="badge badge-warning">Paused</span>
                                @elseif($campaign->status === 'pending')
                                    <span class="badge badge-info">Pending</span>
                                @elseif($campaign->status === 'stopped')
                                    <span class="badge badge-secondary">Stopped</span>
                                @elseif($campaign->status === 'completed')
                                    <span class="badge badge-info">Completed</span>
                                @else
                                    <span class="badge badge-secondary">{{ ucfirst($campaign->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Approval Status:</th>
                            <td>
                                @if($campaign->approval_status === 'approved')
                                    <span class="badge badge-success">Approved</span>
                                    @if($campaign->approved_at)
                                        <br><small class="text-muted">Approved on {{ $campaign->approved_at->format('M d, Y H:i') }}</small>
                                    @endif
                                @elseif($campaign->approval_status === 'pending')
                                    <span class="badge badge-warning">Pending Approval</span>
                                @elseif($campaign->approval_status === 'rejected')
                                    <span class="badge badge-danger">Rejected</span>
                                    @if($campaign->rejection_reason)
                                        <br><small class="text-danger">{{ $campaign->rejection_reason }}</small>
                                    @endif
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Start Date:</th>
                            <td>{{ $campaign->start_date->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <th>End Date:</th>
                            <td>
                                @if($campaign->end_date)
                                    {{ $campaign->end_date->format('M d, Y') }}
                                @else
                                    <span class="text-muted">No end date (unlimited)</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td>{{ $campaign->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td>{{ $campaign->updated_at->format('M d, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Ad Content -->
            @if($campaign->ad_content)
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Ad Content</h3>
                </div>
                <div class="card-body">
                    @php
                        $adContent = is_array($campaign->ad_content) ? $campaign->ad_content : json_decode($campaign->ad_content, true);
                    @endphp
                    @if(isset($adContent['title']))
                        <p><strong>Title:</strong> {{ $adContent['title'] }}</p>
                    @endif
                    @if(isset($adContent['description']))
                        <p><strong>Description:</strong> {{ $adContent['description'] }}</p>
                    @endif
                    @if(isset($adContent['image_url']))
                        <p><strong>Image URL:</strong> <a href="{{ $adContent['image_url'] }}" target="_blank">{{ $adContent['image_url'] }}</a></p>
                        <img src="{{ $adContent['image_url'] }}" alt="Ad Preview" style="max-width: 100%; height: auto; border: 1px solid #ddd; border-radius: 4px; margin-top: 10px;">
                    @endif
                    @if(isset($adContent['html']))
                        <p><strong>HTML Content:</strong></p>
                        <div style="border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                            {!! $adContent['html'] !!}
                        </div>
                    @endif
                    @if(isset($adContent['text']))
                        <p><strong>Text:</strong> {{ $adContent['text'] }}</p>
                    @endif
                </div>
            </div>
            @endif

            <!-- Targeting -->
            @if($campaign->targeting)
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Targeting Settings</h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        @if($campaign->targeting->countries && !empty($campaign->targeting->countries))
                        <tr>
                            <th width="200">Countries:</th>
                            <td>
                                @foreach($campaign->targeting->countries as $country)
                                    <span class="badge badge-info">{{ $country }}</span>
                                @endforeach
                            </td>
                        </tr>
                        @endif
                        @if($campaign->targeting->devices && !empty($campaign->targeting->devices))
                        <tr>
                            <th>Devices:</th>
                            <td>
                                @foreach($campaign->targeting->devices as $device)
                                    <span class="badge badge-info">{{ ucfirst($device) }}</span>
                                @endforeach
                            </td>
                        </tr>
                        @endif
                        @if($campaign->targeting->operating_systems && !empty($campaign->targeting->operating_systems))
                        <tr>
                            <th>Operating Systems:</th>
                            <td>
                                @foreach($campaign->targeting->operating_systems as $os)
                                    <span class="badge badge-info">{{ ucfirst($os) }}</span>
                                @endforeach
                            </td>
                        </tr>
                        @endif
                        @if($campaign->targeting->browsers && !empty($campaign->targeting->browsers))
                        <tr>
                            <th>Browsers:</th>
                            <td>
                                @foreach($campaign->targeting->browsers as $browser)
                                    <span class="badge badge-info">{{ ucfirst($browser) }}</span>
                                @endforeach
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <th>VPN Allowed:</th>
                            <td>
                                @if($campaign->targeting->is_vpn_allowed)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-danger">No</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Proxy Allowed:</th>
                            <td>
                                @if($campaign->targeting->is_proxy_allowed)
                                    <span class="badge badge-success">Yes</span>
                                @else
                                    <span class="badge badge-danger">No</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            @endif
        </div>

        <!-- Statistics -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Statistics</h3>
                </div>
                <div class="card-body">
                    <div style="margin-bottom: 20px;">
                        <div style="font-size: 12px; color: #6b7280; margin-bottom: 5px;">Impressions</div>
                        <div style="font-size: 24px; font-weight: 700; color: #1f2937;">{{ number_format($campaign->impressions) }}</div>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <div style="font-size: 12px; color: #6b7280; margin-bottom: 5px;">Clicks</div>
                        <div style="font-size: 24px; font-weight: 700; color: #1f2937;">{{ number_format($campaign->clicks) }}</div>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <div style="font-size: 12px; color: #6b7280; margin-bottom: 5px;">CTR (Click-Through Rate)</div>
                        <div style="font-size: 24px; font-weight: 700; color: #27ae60;">{{ number_format($campaign->calculateCTR(), 2) }}%</div>
                    </div>
                    <div style="margin-bottom: 20px;">
                        <div style="font-size: 12px; color: #6b7280; margin-bottom: 5px;">Total Spent</div>
                        <div style="font-size: 24px; font-weight: 700; color: #e74c3c;">${{ number_format($campaign->total_spent, 2) }}</div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: #6b7280; margin-bottom: 5px;">Remaining Budget</div>
                        <div style="font-size: 24px; font-weight: 700; color: #3498db;">${{ number_format($campaign->budget - $campaign->total_spent, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form action="{{ route('dashboard.admin.campaigns.reject', $campaign->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">Reject Campaign</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p style="margin-bottom: 16px;">Are you sure you want to reject campaign <strong>{{ $campaign->name }}</strong>?</p>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="rejection_reason">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea id="rejection_reason" name="rejection_reason" class="form-control" rows="3" required placeholder="Enter reason for rejection..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times-circle"></i> Reject Campaign
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function showRejectModal() {
        const modal = document.getElementById('rejectModal');
        if (modal) {
            modal.style.display = 'block';
            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('rejectModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }

        document.querySelectorAll('.modal .close, .modal [data-dismiss="modal"]').forEach(function(button) {
            button.addEventListener('click', function() {
                const modal = this.closest('.modal');
                if (modal) {
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    document.body.style.overflow = '';
                }
            });
        });

        document.querySelectorAll('.modal').forEach(function(modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                    modal.classList.remove('show');
                    document.body.style.overflow = '';
                }
            });
        });
    });
</script>
@endpush


