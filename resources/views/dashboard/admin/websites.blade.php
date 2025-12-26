@extends('dashboard.layouts.main')

@section('title', 'Websites Management - Admin Dashboard')

@section('content')
    <div class="page-header">
        <h1>Websites Management</h1>
        <p class="text-muted">Manage and approve publisher websites.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Websites</div>
            <div class="stat-value">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Approved</div>
            <div class="stat-value">{{ number_format($stats['approved']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pending</div>
            <div class="stat-value">{{ number_format($stats['pending']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Rejected</div>
            <div class="stat-value">{{ number_format($stats['rejected']) }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.admin.websites') }}">
                <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; margin-bottom: 10px;">
                    <div style="flex: 0 0 auto; min-width: 150px; max-width: 180px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div style="flex: 1 1 auto; min-width: 250px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by domain, name, or publisher..." value="{{ request('search') }}">
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('dashboard.admin.websites') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Websites Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Websites</h3>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Domain</th>
                            <th>Name</th>
                            <th>Publisher</th>
                            <th>Ad Units</th>
                            <th>Verification Method</th>
                            <th>Status</th>
                            <th>Added Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($websites as $website)
                            <tr>
                                <td>#{{ $website->id }}</td>
                                <td><strong>{{ $website->domain }}</strong></td>
                                <td>{{ $website->name }}</td>
                                <td>
                                    @if($website->publisher && $website->publisher->user)
                                        <div>{{ $website->publisher->user->name }}</div>
                                        <small class="text-muted">{{ $website->publisher->user->email }}</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $website->adUnits->count() }}</span>
                                </td>
                                <td>{{ ucfirst(str_replace('_', ' ', $website->verification_method)) }}</td>
                                <td>
                                    @if($website->status === 'approved')
                                        <span class="badge badge-success">Approved</span>
                                    @elseif($website->status === 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @else
                                        <span class="badge badge-danger">Rejected</span>
                                        @if($website->rejection_reason)
                                            <br><small class="text-muted" title="{{ $website->rejection_reason }}">{{ \Illuminate\Support\Str::limit($website->rejection_reason, 30) }}</small>
                                        @endif
                                    @endif
                                </td>
                                <td>{{ $website->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                        <a href="{{ route('dashboard.admin.websites.show', $website->id) }}" class="btn btn-sm btn-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($website->status === 'pending')
                                            <form action="{{ route('dashboard.admin.websites.approve', $website->id) }}" method="POST" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success" title="Approve" onclick="return confirm('Are you sure you want to approve this website?')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-danger" title="Reject" onclick="showRejectModal({{ $website->id }}, '{{ $website->domain }}')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @elseif($website->status === 'approved')
                                            <button type="button" class="btn btn-sm btn-warning" title="Suspend" onclick="showSuspendModal({{ $website->id }}, '{{ $website->domain }}')">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">No websites found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $websites->links() }}
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
                        <h5 class="modal-title">Reject Website</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to reject <strong id="rejectDomain"></strong>?</p>
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
                <form id="suspendForm" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Suspend Website</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to suspend <strong id="suspendDomain"></strong>?</p>
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
    function showRejectModal(id, domain) {
        document.getElementById('rejectDomain').textContent = domain;
        document.getElementById('rejectForm').action = '{{ route("dashboard.admin.websites.reject", ":id") }}'.replace(':id', id);
        $('#rejectModal').modal('show');
    }

    function showSuspendModal(id, domain) {
        document.getElementById('suspendDomain').textContent = domain;
        document.getElementById('suspendForm').action = '{{ route("dashboard.admin.websites.suspend", ":id") }}'.replace(':id', id);
        $('#suspendModal').modal('show');
    }
</script>
@endpush

