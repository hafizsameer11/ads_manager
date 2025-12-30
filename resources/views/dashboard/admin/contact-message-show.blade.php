@extends('dashboard.layouts.main')

@section('title', 'Contact Message - Admin Dashboard')

@push('styles')
<style>
    body {
        overflow-x: hidden !important;
    }

    .dashboard-main {
        overflow-x: hidden !important;
        width: 100%;
        max-width: 100%;
        padding: 20px;
        box-sizing: border-box;
    }

    .message-detail-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        padding: 24px;
        margin-bottom: 20px;
    }

    .message-header {
        border-bottom: 2px solid var(--border-color);
        padding-bottom: 20px;
        margin-bottom: 20px;
    }

    .message-header h2 {
        font-size: 24px;
        margin-bottom: 16px;
        color: var(--text-primary);
    }

    .message-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
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

    .message-body {
        padding: 20px 0;
    }

    .message-body h3 {
        font-size: 16px;
        margin-bottom: 12px;
        color: var(--text-primary);
    }

    .message-content {
        background: var(--bg-light);
        padding: 16px;
        border-radius: 6px;
        border-left: 4px solid var(--primary-color);
        white-space: pre-wrap;
        word-wrap: break-word;
        line-height: 1.6;
        color: var(--text-primary);
    }

    .message-actions {
        display: flex;
        gap: 12px;
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid var(--border-color);
    }

    .badge {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-success {
        background: linear-gradient(135deg, #27ae60, #229954);
        color: white;
    }

    .badge-warning {
        background: linear-gradient(135deg, #f39c12, #e67e22);
        color: white;
    }

    .success-alert {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        padding: 16px 20px;
        border-left: 4px solid var(--success-color);
        background-color: #f0fdf4;
        border-radius: var(--border-radius);
        margin-bottom: 24px;
        position: relative;
    }

    .success-alert .alert-icon {
        flex-shrink: 0;
        font-size: 24px;
        color: var(--success-color);
        margin-top: 2px;
    }

    .success-alert .alert-content {
        flex: 1;
    }

    .success-alert .alert-content strong {
        font-size: 16px;
        font-weight: 600;
        color: var(--success-color);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .success-alert .alert-content p {
        color: var(--text-primary);
        margin: 0;
    }

    .success-alert .close {
        position: absolute;
        top: 16px;
        right: 16px;
        font-size: 20px;
        opacity: 0.6;
        cursor: pointer;
    }

    .success-alert .close:hover {
        opacity: 1;
    }
</style>
@endpush

@section('content')
    @if(session('success'))
        <div class="success-alert">
            <div class="alert-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="alert-content">
                <strong><i class="fas fa-check"></i> Success!</strong>
                <p style="margin: 4px 0 0 0;">{{ session('success') }}</p>
            </div>
            <button type="button" class="close" onclick="this.parentElement.style.display='none'">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="message-detail-card">
        <div class="message-header">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h2>{{ $message->subject }}</h2>
                @if($message->is_read)
                    <span class="badge badge-success">Read</span>
                @else
                    <span class="badge badge-warning">Unread</span>
                @endif
            </div>
            <div class="message-meta">
                <div class="meta-item">
                    <div class="meta-label">From</div>
                    <div class="meta-value">{{ $message->name }}</div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Email</div>
                    <div class="meta-value">
                        <a href="mailto:{{ $message->email }}">{{ $message->email }}</a>
                    </div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Submitted</div>
                    <div class="meta-value">{{ $message->created_at->format('M d, Y H:i:s') }}</div>
                </div>
                @if($message->read_at)
                    <div class="meta-item">
                        <div class="meta-label">Read At</div>
                        <div class="meta-value">{{ $message->read_at->format('M d, Y H:i:s') }}</div>
                    </div>
                @endif
                @if($message->ip_address)
                    <div class="meta-item">
                        <div class="meta-label">IP Address</div>
                        <div class="meta-value">{{ $message->ip_address }}</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="message-body">
            <h3>Message</h3>
            <div class="message-content">{{ $message->message }}</div>
        </div>

        <div class="message-actions">
            <a href="{{ route('dashboard.admin.contact-messages') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Messages
            </a>
            @if($message->is_read)
                <form action="{{ route('dashboard.admin.contact-messages.mark-unread', $message->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-envelope"></i> Mark as Unread
                    </button>
                </form>
            @else
                <form action="{{ route('dashboard.admin.contact-messages.mark-read', $message->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Mark as Read
                    </button>
                </form>
            @endif
            <a href="mailto:{{ $message->email }}?subject=Re: {{ $message->subject }}" class="btn btn-primary">
                <i class="fas fa-reply"></i> Reply
            </a>
            <form action="{{ route('dashboard.admin.contact-messages.destroy', $message->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this message? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>
@endsection

