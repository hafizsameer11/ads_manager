@extends('dashboard.layouts.main')

@section('title', 'Support Tickets - Admin Dashboard')

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

    .action-buttons {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: nowrap;
    }

    .action-buttons .btn {
        margin: 0;
        padding: 6px 10px;
        min-width: 36px;
    }

    .action-buttons .action-form {
        display: inline-block;
        margin: 0;
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
</style>
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Support Tickets</h3>
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
            <form method="GET" action="{{ route('dashboard.admin.support-tickets.index') }}">
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
                    <div class="filter-field">
                        <label class="form-label">Assigned To</label>
                        <select name="assigned_to" class="form-control" id="assigned_to">
                            <option value="">All Admins</option>
                            @foreach($admins as $admin)
                                <option value="{{ $admin->id }}" {{ request('assigned_to') == $admin->id ? 'selected' : '' }}>
                                    {{ $admin->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-field">
                        <div class="form-check" style="margin-top: 28px;">
                            <input type="checkbox" name="unassigned" id="unassigned" value="1" class="form-check-input" {{ request('unassigned') == '1' ? 'checked' : '' }} onchange="if(this.checked) document.getElementById('assigned_to').value = '';">
                            <label class="form-check-label" for="unassigned" style="font-size: 13px; margin-left: 5px;">
                                Unassigned Only
                            </label>
                        </div>
                    </div>
                    <div class="filter-field filter-field-search">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by ticket number, subject, or user..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('dashboard.admin.support-tickets.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">All Support Tickets</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Ticket #</th>
                            <th>Subject</th>
                            <th>User</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Created At</th>
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
                                    <div class="text-muted" style="font-size: 12px; margin-top: 4px;">
                                        {{ \Illuminate\Support\Str::limit($ticket->description, 60) }}
                                    </div>
                                </td>
                                <td>
                                    {{ $ticket->user->name ?? 'N/A' }}
                                    <div class="text-muted" style="font-size: 12px;">
                                        {{ $ticket->user->email ?? '' }}
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
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('dashboard.admin.support-tickets.show', $ticket) }}" class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('dashboard.admin.support-tickets.destroy', $ticket) }}" method="POST" class="action-form d-inline" onsubmit="return confirm('Are you sure you want to delete this ticket?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No support tickets found.</td>
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

