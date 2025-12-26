@extends('dashboard.layouts.main')

@section('title', 'Website Details - Admin Dashboard')

@section('content')
    <div class="page-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1>Website Details</h1>
                <p class="text-muted">{{ $website->domain }}</p>
            </div>
            <div>
                <a href="{{ route('dashboard.admin.websites') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Websites
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <!-- Website Information -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 class="card-title">Website Information</h3>
                        @if($website->status === 'pending')
                            <div style="display: flex; gap: 5px;">
                                <form action="{{ route('dashboard.admin.websites.approve', $website->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to approve this website?')">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>
                                <button type="button" class="btn btn-danger" onclick="showRejectModal()">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </div>
                        @elseif($website->status === 'approved')
                            <button type="button" class="btn btn-warning" onclick="showSuspendModal()">
                                <i class="fas fa-ban"></i> Suspend
                            </button>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Domain:</th>
                            <td><strong>{{ $website->domain }}</strong></td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td>{{ $website->name }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                @if($website->status === 'approved')
                                    <span class="badge badge-success">Approved</span>
                                @elseif($website->status === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @else
                                    <span class="badge badge-danger">Rejected</span>
                                @endif
                            </td>
                        </tr>
                        @if($website->rejection_reason)
                        <tr>
                            <th>Rejection Reason:</th>
                            <td class="text-danger">{{ $website->rejection_reason }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Verification Method:</th>
                            <td>{{ ucfirst(str_replace('_', ' ', $website->verification_method)) }}</td>
                        </tr>
                        @if($website->verification_code)
                        <tr>
                            <th>Verification Code:</th>
                            <td><code>{{ $website->verification_code }}</code></td>
                        </tr>
                        @endif
                        @if($website->verified_at)
                        <tr>
                            <th>Verified At:</th>
                            <td>{{ $website->verified_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Created:</th>
                            <td>{{ $website->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td>{{ $website->updated_at->format('M d, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Ad Units -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Ad Units ({{ $website->adUnits->count() }})</h3>
                </div>
                <div class="card-body">
                    @if($website->adUnits->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($website->adUnits as $adUnit)
                                    <tr>
                                        <td><strong>{{ $adUnit->name }}</strong></td>
                                        <td><span class="badge badge-info">{{ ucfirst($adUnit->type) }}</span></td>
                                        <td>
                                            @if($adUnit->status === 'active')
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-warning">Paused</span>
                                            @endif
                                        </td>
                                        <td>{{ $adUnit->created_at->format('M d, Y') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-3">No ad units created yet.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Publisher Information -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Publisher Information</h3>
                </div>
                <div class="card-body">
                    @if($website->publisher && $website->publisher->user)
                        <table class="table table-borderless">
                            <tr>
                                <th>Name:</th>
                                <td>{{ $website->publisher->user->name }}</td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td>{{ $website->publisher->user->email }}</td>
                            </tr>
                            <tr>
                                <th>Username:</th>
                                <td>{{ $website->publisher->user->username }}</td>
                            </tr>
                            <tr>
                                <th>Publisher Status:</th>
                                <td>
                                    @if($website->publisher->status === 'approved')
                                        <span class="badge badge-success">Approved</span>
                                    @elseif($website->publisher->status === 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @else
                                        <span class="badge badge-danger">{{ ucfirst($website->publisher->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Total Websites:</th>
                                <td>{{ $website->publisher->websites->count() }}</td>
                            </tr>
                        </table>
                        <div class="mt-3">
                            <a href="{{ route('dashboard.admin.users') }}?search={{ $website->publisher->user->email }}" class="btn btn-sm btn-primary btn-block">
                                <i class="fas fa-user"></i> View Publisher Profile
                            </a>
                        </div>
                    @else
                        <p class="text-muted text-center py-3">Publisher information not available.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('dashboard.admin.websites.reject', $website->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Website</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to reject <strong>{{ $website->domain }}</strong>?</p>
                        <div class="form-group">
                            <label for="rejection_reason">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea id="rejection_reason" name="rejection_reason" class="form-control" rows="3" required placeholder="Enter reason for rejection..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Website</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Suspend Modal -->
    <div class="modal fade" id="suspendModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('dashboard.admin.websites.suspend', $website->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Suspend Website</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to suspend <strong>{{ $website->domain }}</strong>?</p>
                        <p class="text-warning"><small>This will pause all ad units on this website.</small></p>
                        <div class="form-group">
                            <label for="suspend_reason">Reason (Optional)</label>
                            <textarea id="suspend_reason" name="reason" class="form-control" rows="2" placeholder="Enter reason for suspension..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Suspend Website</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function showRejectModal() {
        $('#rejectModal').modal('show');
    }

    function showSuspendModal() {
        $('#suspendModal').modal('show');
    }
</script>
@endpush

