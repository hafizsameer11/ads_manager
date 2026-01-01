@extends('dashboard.layouts.main')

@section('title', 'DMCA Report - Admin Dashboard')

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
        flex-wrap: wrap;
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

    .badge-pending {
        background: #f39c12;
        color: white;
    }

    .badge-reviewed {
        background: #3498db;
        color: white;
    }

    .badge-resolved {
        background: #27ae60;
        color: white;
    }

    .badge-dismissed {
        background: #95a5a6;
        color: white;
    }

    .status-form {
        margin-top: 20px;
        padding: 20px;
        background: var(--bg-light);
        border-radius: 6px;
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

    .form-control, .form-select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--border-color);
        border-radius: 6px;
        font-size: 14px;
        background: white;
    }

    textarea.form-control {
        resize: vertical;
        min-height: 100px;
    }

    .success-alert {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        padding: 16px 20px;
        border-left: 4px solid var(--success-color);
        background-color: #f0fdf4;
        border-radius: 6px;
        margin-bottom: 24px;
        position: relative;
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
                <h2>DMCA Report #{{ $report->id }}</h2>
                <span class="badge badge-{{ $report->status }}">{{ ucfirst($report->status) }}</span>
            </div>
            <div class="message-meta">
                <div class="meta-item">
                    <div class="meta-label">Copyright Owner</div>
                    <div class="meta-value">{{ $report->copyright_owner }}</div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Contact Name</div>
                    <div class="meta-value">{{ $report->contact_name }}</div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Contact Email</div>
                    <div class="meta-value">
                        <a href="mailto:{{ $report->contact_email }}">{{ $report->contact_email }}</a>
                    </div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Contact Phone</div>
                    <div class="meta-value">{{ $report->contact_phone }}</div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Infringing URL</div>
                    <div class="meta-value">
                        <a href="{{ $report->infringing_url }}" target="_blank" rel="noopener noreferrer">{{ $report->infringing_url }}</a>
                    </div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Submitted</div>
                    <div class="meta-value">{{ $report->created_at->format('M d, Y H:i:s') }}</div>
                </div>
                @if($report->reviewed_at)
                    <div class="meta-item">
                        <div class="meta-label">Reviewed At</div>
                        <div class="meta-value">{{ $report->reviewed_at->format('M d, Y H:i:s') }}</div>
                    </div>
                @endif
                @if($report->ip_address)
                    <div class="meta-item">
                        <div class="meta-label">IP Address</div>
                        <div class="meta-value">{{ $report->ip_address }}</div>
                    </div>
                @endif
            </div>
        </div>

        <div class="message-body">
            <h3>Description of Original Work</h3>
            <div class="message-content">{{ $report->original_work }}</div>
        </div>

        <div class="message-body">
            <h3>Good Faith Statement</h3>
            <div class="message-content">{{ $report->statement }}</div>
        </div>

        <div class="message-body">
            <h3>Accuracy Confirmation</h3>
            <div class="message-content">
                @if($report->accuracy_confirmed)
                    <i class="fas fa-check-circle" style="color: #27ae60;"></i> The submitter has confirmed under penalty of perjury that the information is accurate.
                @else
                    <i class="fas fa-times-circle" style="color: #e74c3c;"></i> Not confirmed.
                @endif
            </div>
        </div>

        @if($report->admin_notes)
            <div class="message-body">
                <h3>Admin Notes</h3>
                <div class="message-content" style="border-left-color: #3498db;">{{ $report->admin_notes }}</div>
            </div>
        @endif

        <div class="status-form">
            <h3 style="font-size: 16px; margin-bottom: 16px;">Update Status</h3>
            <form action="{{ route('dashboard.admin.dmca-reports.update-status', $report->id) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        <option value="pending" {{ $report->status == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="reviewed" {{ $report->status == 'reviewed' ? 'selected' : '' }}>Reviewed</option>
                        <option value="resolved" {{ $report->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="dismissed" {{ $report->status == 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Admin Notes (Optional)</label>
                    <textarea name="admin_notes" class="form-control" placeholder="Add notes about this report...">{{ $report->admin_notes }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Status
                </button>
            </form>
        </div>

        <div class="message-actions">
            <a href="{{ route('dashboard.admin.dmca-reports') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Reports
            </a>
            <a href="mailto:{{ $report->contact_email }}?subject=Re: DMCA Report #{{ $report->id }}" class="btn btn-primary">
                <i class="fas fa-reply"></i> Reply
            </a>
            <form action="{{ route('dashboard.admin.dmca-reports.destroy', $report->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this report? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>
@endsection

