@extends('dashboard.layouts.main')

@section('title', 'Support Ticket - Dashboard')

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

    .reply-item.user-reply {
        background: #e3f2fd;
        border-left: 4px solid #2196f3;
    }

    .reply-item.admin-reply {
        background: #f1f8e9;
        border-left: 4px solid #8bc34a;
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

    .alert-warning {
        background-color: #fff3cd;
        border-color: #ffc107;
        color: #856404;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 20px;
    }
</style>
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Support Ticket: {{ $supportTicket->ticket_number }}</h3>
        <a href="{{ route('dashboard.' . (Auth::user()->isPublisher() ? 'publisher' : 'advertiser') . '.support-tickets.index') }}" class="btn btn-secondary">
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
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
        </div>
    </div>

    <!-- Replies Section -->
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Conversation ({{ $supportTicket->replies->count() }} replies)</h4>
        </div>
        <div class="card-body">
            <div class="replies-section">
                @forelse($supportTicket->replies as $reply)
                    <div class="reply-item {{ $reply->user_id == Auth::id() ? 'user-reply' : 'admin-reply' }}">
                        <div class="reply-header">
                            <div>
                                <span class="reply-author">
                                    @if($reply->user_id == Auth::id())
                                        You
                                    @else
                                        {{ $reply->user->name ?? 'Support Team' }}
                                    @endif
                                </span>
                            </div>
                            <span class="reply-date">{{ $reply->created_at->format('M d, Y H:i A') }}</span>
                        </div>
                        <div class="reply-message">{{ $reply->message }}</div>
                    </div>
                @empty
                    <p class="text-muted">No replies yet. Waiting for support team response.</p>
                @endforelse
            </div>

            @if($supportTicket->status === 'closed')
                <div class="alert-warning">
                    <strong><i class="fas fa-info-circle"></i> This ticket is closed.</strong> If you need further assistance, please create a new support ticket.
                </div>
            @else
                <!-- Add Reply Form -->
                <div class="card" style="margin-top: 24px; background: #f8f9fa;">
                    <div class="card-header">
                        <h5 class="card-title">Add Reply</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('dashboard.' . (Auth::user()->isPublisher() ? 'publisher' : 'advertiser') . '.support-tickets.reply', $supportTicket) }}">
                            @csrf
                            <div class="form-group">
                                <label class="form-label">Your Message</label>
                                <textarea name="message" class="form-control" rows="5" required placeholder="Type your reply here..."></textarea>
                                @error('message')
                                    <div class="text-danger" style="font-size: 12px; margin-top: 4px;">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Send Reply
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection





