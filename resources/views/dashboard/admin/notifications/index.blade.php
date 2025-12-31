@extends('dashboard.layouts.main')

@section('title', 'Notifications - Admin Dashboard')

@push('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07), 0 1px 3px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--primary-color), var(--info-color));
    }
    
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12), 0 4px 8px rgba(0, 0, 0, 0.08);
    }
    
    .stat-card.primary::before { background: linear-gradient(90deg, #667eea, #764ba2); }
    .stat-card.success::before { background: linear-gradient(90deg, #11998e, #38ef7d); }
    .stat-card.info::before { background: linear-gradient(90deg, #3498db, #2980b9); }
    .stat-card.warning::before { background: linear-gradient(90deg, #f39c12, #e67e22); }
    .stat-card.danger::before { background: linear-gradient(90deg, #e74c3c, #c0392b); }
    .stat-card.secondary::before { background: linear-gradient(90deg, #2c3e50, #34495e); }
    
    .stat-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.75rem;
    }
    
    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: white;
        background: linear-gradient(135deg, var(--primary-color), var(--info-color));
    }
    
    .stat-card.primary .stat-icon { background: linear-gradient(135deg, #667eea, #764ba2); }
    .stat-card.success .stat-icon { background: linear-gradient(135deg, #11998e, #38ef7d); }
    .stat-card.info .stat-icon { background: linear-gradient(135deg, #3498db, #2980b9); }
    .stat-card.warning .stat-icon { background: linear-gradient(135deg, #f39c12, #e67e22); }
    .stat-card.danger .stat-icon { background: linear-gradient(135deg, #e74c3c, #c0392b); }
    .stat-card.secondary .stat-icon { background: linear-gradient(135deg, #2c3e50, #34495e); }
    
    .stat-content {
        flex: 1;
    }
    
    .stat-label {
        font-size: 0.75rem;
        color: var(--text-secondary);
        font-weight: 500;
        margin-bottom: 0.375rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.375rem;
        line-height: 1.2;
    }
    
    .filter-form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    .filter-field {
        display: flex;
        flex-direction: column;
    }
    
    .filter-field-search {
        grid-column: span 2;
    }
    
    .form-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }
    
    .filter-buttons {
        display: flex;
        gap: 0.75rem;
    }
    
    .notification-unread {
        background-color: #f0f9ff;
        font-weight: 500;
    }
    
    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .filter-field-search {
            grid-column: span 1;
        }
    }
</style>
@endpush

@section('content')
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="fas fa-bell"></i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Notifications</div>
                <div class="stat-value">{{ number_format($stats['total'] ?? 0) }}</div>
            </div>
        </div>

        <div class="stat-card danger">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="fas fa-envelope"></i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Unread</div>
                <div class="stat-value">{{ number_format($stats['unread'] ?? 0) }}</div>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Read</div>
                <div class="stat-value">{{ number_format($stats['read'] ?? 0) }}</div>
            </div>
        </div>

        <div class="stat-card info">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Withdrawal</div>
                <div class="stat-value">{{ number_format($stats['withdrawal'] ?? 0) }}</div>
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="fas fa-bullhorn"></i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Campaign</div>
                <div class="stat-value">{{ number_format($stats['campaign'] ?? 0) }}</div>
            </div>
        </div>

        <div class="stat-card secondary">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">User</div>
                <div class="stat-value">{{ number_format($stats['user'] ?? 0) }}</div>
            </div>
        </div>

        <div class="stat-card info">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">Payment</div>
                <div class="stat-value">{{ number_format($stats['payment'] ?? 0) }}</div>
            </div>
        </div>

        <div class="stat-card secondary">
            <div class="stat-card-header">
                <div class="stat-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
            </div>
            <div class="stat-content">
                <div class="stat-label">General</div>
                <div class="stat-value">{{ number_format($stats['general'] ?? 0) }}</div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.admin.notifications.index') }}">
                <div class="filter-form-row">
                    <div class="filter-field">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-control">
                            <option value="">All Categories</option>
                            <option value="withdrawal" {{ request('category') == 'withdrawal' ? 'selected' : '' }}>Withdrawal</option>
                            <option value="campaign" {{ request('category') == 'campaign' ? 'selected' : '' }}>Campaign</option>
                            <option value="user" {{ request('category') == 'user' ? 'selected' : '' }}>User</option>
                            <option value="payment" {{ request('category') == 'payment' ? 'selected' : '' }}>Payment</option>
                            <option value="general" {{ request('category') == 'general' ? 'selected' : '' }}>General</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>Unread</option>
                            <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Read</option>
                        </select>
                    </div>
                </div>
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('dashboard.admin.notifications.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Notifications Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Notifications</h3>
            <div class="header-actions">
                <form action="{{ route('dashboard.admin.notifications.mark-all-read') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-check-double"></i> Mark All as Read
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show success-alert" role="alert">
                    <div class="alert-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="alert-content">
                        <strong><i class="fas fa-check"></i> Success!</strong>
                        <p style="margin: 4px 0 0 0;">{{ session('success') }}</p>
                    </div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Category</th>
                            <th>Title</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notifications as $notification)
                            <tr class="{{ $notification->is_read ? '' : 'notification-unread' }}">
                                <td>
                                    @if($notification->is_read)
                                        <span class="badge badge-success">Read</span>
                                    @else
                                        <span class="badge badge-warning">Unread</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ ucfirst($notification->category) }}</span>
                                </td>
                                <td><strong>{{ $notification->title }}</strong></td>
                                <td>{{ Str::limit($notification->message, 100) }}</td>
                                <td>{{ $notification->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    <div class="action-buttons">
                                        @if(!$notification->is_read)
                                            <form action="{{ route('dashboard.admin.notifications.mark-read', $notification->id) }}" method="POST" class="action-form">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary" title="Mark as Read">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('dashboard.admin.notifications.mark-unread', $notification->id) }}" method="POST" class="action-form">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning" title="Mark as Unread">
                                                    <i class="fas fa-envelope"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No notifications found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($notifications->hasPages())
                <div class="mt-3">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
