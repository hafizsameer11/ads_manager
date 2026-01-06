@extends('dashboard.layouts.main')

@section('title', 'Support Tickets - Dashboard')

@push('styles')
<style>
    .filter-form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: flex-end;
        margin-bottom: 16px;
    }

    .filter-field {
        flex: 0 0 auto;
        min-width: 140px;
        max-width: 100%;
    }

    .filter-field-search {
        flex: 1 1 auto;
        min-width: 200px;
    }

    .filter-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }

    .form-label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        font-size: 13px;
        color: var(--text-primary);
    }

    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        font-size: 14px;
        transition: var(--transition);
        background-color: var(--bg-primary);
        color: var(--text-primary);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.1);
    }

    .badge {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .badge-success {
        background-color: #27ae60;
        color: white;
    }

    .badge-warning {
        background-color: #f39c12;
        color: white;
    }

    .badge-danger {
        background-color: #e74c3c;
        color: white;
    }

    .badge-info {
        background-color: #3498db;
        color: white;
    }

    .badge-secondary {
        background-color: #95a5a6;
        color: white;
    }

    .priority-low {
        background-color: #27ae60;
    }

    .priority-medium {
        background-color: #f39c12;
    }

    .priority-high {
        background-color: #e67e22;
    }

    .priority-urgent {
        background-color: #e74c3c;
    }

    .ticket-preview {
        max-width: 300px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: var(--text-secondary);
        font-size: 13px;
    }
</style>
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">My Support Tickets</h3>
        <a href="{{ route('dashboard.' . (Auth::user()->isPublisher() ? 'publisher' : 'advertiser') . '.support-tickets.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create New Ticket
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.' . (Auth::user()->isPublisher() ? 'publisher' : 'advertiser') . '.support-tickets.index') }}">
                <div class="filter-form-row">
                    <div class="filter-field">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label class="form-label">Priority</label>
                        <select name="priority" class="form-control">
                            <option value="">All Priorities</option>
                            <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                            <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        </select>
                    </div>
                    <div class="filter-field filter-field-search">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by ticket number, subject..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('dashboard.' . (Auth::user()->isPublisher() ? 'publisher' : 'advertiser') . '.support-tickets.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">All Tickets</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Ticket #</th>
                            <th>Subject</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Created At</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td>
                                    <strong>{{ $ticket->ticket_number }}</strong>
                                </td>
                                <td>
                                    <strong>{{ $ticket->subject }}</strong>
                                    <div class="ticket-preview" title="{{ $ticket->description }}">
                                        {{ \Illuminate\Support\Str::limit($ticket->description, 50) }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge priority-{{ $ticket->priority }}">
                                        {{ ucfirst($ticket->priority) }}
                                    </span>
                                </td>
                                <td>
                                    @if($ticket->status == 'open')
                                        <span class="badge badge-info">Open</span>
                                    @elseif($ticket->status == 'in_progress')
                                        <span class="badge badge-warning">In Progress</span>
                                    @elseif($ticket->status == 'resolved')
                                        <span class="badge badge-success">Resolved</span>
                                    @elseif($ticket->status == 'closed')
                                        <span class="badge badge-secondary">Closed</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $ticket->assignedTo->name ?? 'Unassigned' }}
                                </td>
                                <td>{{ $ticket->created_at->format('M d, Y H:i') }}</td>
                                <td>{{ $ticket->updated_at->format('M d, Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('dashboard.' . (Auth::user()->isPublisher() ? 'publisher' : 'advertiser') . '.support-tickets.show', $ticket) }}" class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    <p class="text-muted">No support tickets found.</p>
                                    <a href="{{ route('dashboard.' . (Auth::user()->isPublisher() ? 'publisher' : 'advertiser') . '.support-tickets.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Create Your First Ticket
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $tickets->links() }}
            </div>
        </div>
    </div>
@endsection





