@extends('dashboard.layouts.main')

@section('title', 'Support Ticket - Admin Dashboard')

@push('styles')
<style>
    .ticket-header {
        border-bottom: 2px solid var(--border-color);
        padding-bottom: 20px;
        margin-bottom: 20px;
    }

    .ticket-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-top: 16px;
    }

    .meta-item {
        display: flex;
        flex-direction: column;
    }

    .meta-label {
        font-size: 12px;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .meta-value {
        font-size: 14px;
        color: var(--text-primary);
        font-weight: 500;
    }

    .ticket-description {
        background: var(--bg-light);
        padding: 16px;
        border-radius: 6px;
        border-left: 4px solid var(--primary-color);
        white-space: pre-wrap;
        word-wrap: break-word;
        line-height: 1.6;
        color: var(--text-primary);
        margin-bottom: 24px;
    }

    .replies-section {
        margin-top: 24px;
    }

    .reply-item {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 16px;
        margin-bottom: 16px;
    }

    .reply-item.internal {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
    }

    .reply-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--border-color);
    }

    .reply-author {
        font-weight: 600;
        color: var(--text-primary);
    }

    .reply-date {
        font-size: 12px;
        color: var(--text-secondary);
    }

    .reply-message {
        color: var(--text-primary);
        line-height: 1.6;
        white-space: pre-wrap;
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

    .form-group {
        margin-bottom: 16px;
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

    textarea.form-control {
        min-height: 120px;
        resize: vertical;
    }
</style>
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Support Ticket: {{ $supportTicket->ticket_number }}</h3>
        <a href="{{ route('dashboard.admin.support-tickets.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Tickets
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

    <!-- Ticket Details -->
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ $supportTicket->subject }}</h4>
        </div>
        <div class="card-body">
            <div class="ticket-header">
                <div class="ticket-meta">
                    <div class="meta-item">
                        <span class="meta-label">Status</span>
                        <span class="meta-value">
                            @if($supportTicket->status == 'open')
                                <span class="badge badge-info">Open</span>
                            @elseif($supportTicket->status == 'in_progress')
                                <span class="badge badge-warning">In Progress</span>
                            @elseif($supportTicket->status == 'resolved')
                                <span class="badge badge-success">Resolved</span>
                            @elseif($supportTicket->status == 'closed')
                                <span class="badge badge-secondary">Closed</span>
                            @endif
                        </span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Priority</span>
                        <span class="meta-value">
                            <span class="badge priority-{{ $supportTicket->priority }}">
                                {{ ucfirst($supportTicket->priority) }}
                            </span>
                        </span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">User</span>
                        <span class="meta-value">{{ $supportTicket->user->name ?? 'N/A' }}</span>
                        <small class="text-muted">{{ $supportTicket->user->email ?? '' }}</small>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Assigned To</span>
                        <span class="meta-value">{{ $supportTicket->assignedTo->name ?? 'Unassigned' }}</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Created At</span>
                        <span class="meta-value">{{ $supportTicket->created_at->format('M d, Y H:i A') }}</span>
                    </div>
                    @if($supportTicket->resolved_at)
                    <div class="meta-item">
                        <span class="meta-label">Resolved At</span>
                        <span class="meta-value">{{ $supportTicket->resolved_at->format('M d, Y H:i A') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="ticket-description">
                <strong>Description:</strong><br>
                {{ $supportTicket->description }}
            </div>

            <!-- Update Ticket Form -->
            <div class="card" style="margin-top: 24px;">
                <div class="card-header">
                    <h5 class="card-title">Update Ticket</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('dashboard.admin.support-tickets.update', $supportTicket) }}">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-control">
                                        <option value="open" {{ $supportTicket->status == 'open' ? 'selected' : '' }}>Open</option>
                                        <option value="in_progress" {{ $supportTicket->status == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                        <option value="resolved" {{ $supportTicket->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                                        <option value="closed" {{ $supportTicket->status == 'closed' ? 'selected' : '' }}>Closed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Priority</label>
                                    <select name="priority" class="form-control">
                                        <option value="low" {{ $supportTicket->priority == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="medium" {{ $supportTicket->priority == 'medium' ? 'selected' : '' }}>Medium</option>
                                        <option value="high" {{ $supportTicket->priority == 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ $supportTicket->priority == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Assign To</label>
                                    <select name="assigned_to" class="form-control">
                                        <option value="">Unassigned</option>
                                        @foreach($admins as $admin)
                                            <option value="{{ $admin->id }}" {{ $supportTicket->assigned_to == $admin->id ? 'selected' : '' }}>
                                                {{ $admin->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Ticket</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Replies Section -->
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Replies ({{ $supportTicket->replies->count() }})</h4>
        </div>
        <div class="card-body">
            <div class="replies-section">
                @forelse($supportTicket->replies as $reply)
                    <div class="reply-item {{ $reply->is_internal ? 'internal' : '' }}">
                        <div class="reply-header">
                            <div>
                                <span class="reply-author">
                                    {{ $reply->user->name ?? 'Unknown' }}
                                    @if($reply->is_internal)
                                        <span class="badge badge-warning">Internal Note</span>
                                    @endif
                                </span>
                            </div>
                            <span class="reply-date">{{ $reply->created_at->format('M d, Y H:i A') }}</span>
                        </div>
                        <div class="reply-message">{{ $reply->message }}</div>
                    </div>
                @empty
                    <p class="text-muted">No replies yet.</p>
                @endforelse
            </div>

            <!-- Add Reply Form -->
            <div class="card" style="margin-top: 24px;">
                <div class="card-header">
                    <h5 class="card-title">Add Reply</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('dashboard.admin.support-tickets.reply', $supportTicket) }}">
                        @csrf
                        <div class="form-group">
                            <label class="form-label">Message</label>
                            <textarea name="message" class="form-control" rows="5" required placeholder="Enter your reply..."></textarea>
                        </div>
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" name="is_internal" id="is_internal" value="1" class="form-check-input">
                                <label class="form-check-label" for="is_internal">
                                    Internal Note (only visible to admins)
                                </label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Send Reply</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

