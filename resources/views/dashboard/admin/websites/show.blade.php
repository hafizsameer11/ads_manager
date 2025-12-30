@extends('dashboard.layouts.main')

@section('title', 'Website Details - Admin Dashboard')

@section('content')
    <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
                <a href="{{ route('dashboard.admin.websites') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Websites
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
                            <div style="display: flex; gap: 5px;">
                                <button type="button" class="btn btn-warning" onclick="showDisableModal()">
                                    <i class="fas fa-ban"></i> Disable
                                </button>
                                <button type="button" class="btn btn-danger" onclick="showSuspendModal()">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </div>
                        @elseif($website->status === 'rejected' || $website->status === 'disabled')
                            <form action="{{ route('dashboard.admin.websites.enable', $website->id) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to enable and approve this website?')">
                                    <i class="fas fa-check"></i> Enable/Approve
                            </button>
                            </form>
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
                                @if(empty($website->status))
                                    <span class="badge badge-secondary">No Status</span>
                                @elseif($website->status === 'approved')
                                    <span class="badge badge-success">Approved</span>
                                @elseif($website->status === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($website->status === 'disabled')
                                    <span class="badge badge-secondary">Disabled</span>
                                @elseif($website->status === 'rejected')
                                    <span class="badge badge-danger">Rejected</span>
                                @else
                                    <span class="badge badge-info">{{ ucfirst($website->status) }}</span>
                                @endif
                            </td>
                        </tr>
                        @if($website->approved_at)
                        <tr>
                            <th>Approved At:</th>
                            <td>{{ $website->approved_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @endif
                        @if($website->rejected_at)
                        <tr>
                            <th>Rejected At:</th>
                            <td>{{ $website->rejected_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @endif
                        @if($website->rejection_reason)
                        <tr>
                            <th>Rejection Reason:</th>
                            <td class="text-danger">{{ $website->rejection_reason }}</td>
                        </tr>
                        @endif
                        @if($website->admin_note)
                        <tr>
                            <th>Admin Note:</th>
                            <td class="text-muted"><small>{{ $website->admin_note }}</small></td>
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
                        <div class="form-group">
                            <label for="reject_admin_note">Admin Note (Optional)</label>
                            <textarea id="reject_admin_note" name="admin_note" class="form-control" rows="2" placeholder="Internal note (not visible to publisher)..."></textarea>
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

    <!-- Disable Modal -->
    <div class="modal fade" id="disableModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('dashboard.admin.websites.disable', $website->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Disable Website</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to disable <strong>{{ $website->domain }}</strong>?</p>
                        <p class="text-warning"><small>This will pause all ad units on this website.</small></p>
                        <div class="form-group">
                            <label for="disable_admin_note">Admin Note (Optional)</label>
                            <textarea id="disable_admin_note" name="admin_note" class="form-control" rows="2" placeholder="Internal note (not visible to publisher)..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Disable Website</button>
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

    function showDisableModal() {
        $('#disableModal').modal('show');
    }

    function showSuspendModal() {
        $('#suspendModal').modal('show');
    }
</script>
@endpush

