@extends('dashboard.layouts.main')

@section('title', 'Ad Units Management - Admin Dashboard')

@push('styles')
<style>
    /* Same styles as other admin pages */
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

    .action-buttons {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: nowrap;
        white-space: nowrap;
    }

    .action-buttons .btn {
        white-space: nowrap;
        flex-shrink: 0;
        padding: 6px 10px;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        line-height: 1.2;
        min-width: auto;
    }

    .action-buttons .btn i {
        font-size: 11px;
    }

    .table {
        width: 100%;
        border-collapse: collapse;
    }

    .table thead th {
        padding: 16px;
        text-align: left;
        font-weight: 600;
        color: var(--text-primary);
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        border-bottom: 2px solid var(--border-color);
    }

    .table td {
        padding: 16px;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-secondary);
        vertical-align: middle;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
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

    .badge-info {
        background: linear-gradient(135deg, #3498db, #2980b9);
        color: white;
    }

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

    .table-responsive {
        display: block;
        width: 100%;
        overflow-x: auto;
        overflow-y: visible;
        -webkit-overflow-scrolling: touch;
    }

    .table-responsive .table {
        width: 100%;
        margin-bottom: 0;
        min-width: 800px;
        font-size: 13px;
    }

    .card {
        overflow: visible;
        max-width: 100%;
        padding: 16px;
    }

    .card-header {
        padding: 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-body {
        overflow-x: visible;
        max-width: 100%;
        padding: 16px;
    }

    .alert {
        padding: 16px 20px;
        border-radius: var(--border-radius);
        margin-bottom: 20px;
        position: relative;
    }

    .alert .close {
        position: absolute;
        top: 16px;
        right: 16px;
        font-size: 20px;
        opacity: 0.6;
        cursor: pointer;
    }

    .alert .close:hover {
        opacity: 1;
    }

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

    .stat-label {
        font-size: 13px;
        color: var(--text-secondary);
        margin-bottom: 8px;
        font-weight: 500;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1.2;
    }
</style>
@endpush

@section('content')
    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Ad Units</div>
            <div class="stat-value">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Active</div>
            <div class="stat-value">{{ number_format($stats['active']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Paused</div>
            <div class="stat-value">{{ number_format($stats['paused']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Banners</div>
            <div class="stat-value">{{ number_format($stats['banner']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Popups</div>
            <div class="stat-value">{{ number_format($stats['popup']) }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.admin.ad-units') }}">
                <div class="filter-form-row">
                    <div class="filter-field">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="paused" {{ request('status') == 'paused' ? 'selected' : '' }}>Paused</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-control">
                            <option value="">All Types</option>
                            <option value="banner" {{ request('type') == 'banner' ? 'selected' : '' }}>Banner</option>
                            <option value="popup" {{ request('type') == 'popup' ? 'selected' : '' }}>Popup</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label class="form-label">Publisher</label>
                        <select name="publisher_id" class="form-control">
                            <option value="">All Publishers</option>
                            @foreach($publishers as $publisher)
                                <option value="{{ $publisher->id }}" {{ request('publisher_id') == $publisher->id ? 'selected' : '' }}>
                                    {{ $publisher->user->name ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-field filter-field-search">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by name, unit code, publisher, or website..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('dashboard.admin.ad-units') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Ad Units Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">All Ad Units</h3>
            <a href="{{ route('dashboard.admin.ad-units.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Ad Unit
            </a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success!</strong> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error!</strong>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
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
                            <th>Name</th>
                            <th>Type</th>
                            <th>Publisher</th>
                            <th>Website</th>
                            <th>Status</th>
                            <th>Unit Code</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adUnits as $adUnit)
                            <tr>
                                <td>#{{ $adUnit->id }}</td>
                                <td><strong>{{ $adUnit->name }}</strong></td>
                                <td>
                                    <span class="badge badge-info">{{ ucfirst($adUnit->type) }}</span>
                                    @if($adUnit->type === 'banner' && $adUnit->size)
                                        <br><small class="text-muted">{{ $adUnit->size }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($adUnit->publisher && $adUnit->publisher->user)
                                        <div><strong>{{ $adUnit->publisher->user->name }}</strong></div>
                                        <small class="text-muted">{{ $adUnit->publisher->user->email }}</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($adUnit->website)
                                        <div><strong>{{ $adUnit->website->domain }}</strong></div>
                                        @if($adUnit->website->name)
                                            <small class="text-muted">{{ $adUnit->website->name }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($adUnit->status === 'active')
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-warning">Paused</span>
                                    @endif
                                </td>
                                <td><code style="font-size: 11px;">{{ $adUnit->unit_code }}</code></td>
                                <td>{{ $adUnit->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('dashboard.admin.ad-units.show', $adUnit->id) }}" class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('dashboard.admin.ad-units.edit', $adUnit->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('dashboard.admin.ad-units.destroy', $adUnit->id) }}" method="POST" class="action-form" onsubmit="return confirm('Are you sure you want to delete this ad unit?');">
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
                                <td colspan="9" class="text-center text-muted">No ad units found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $adUnits->links() }}
            </div>
        </div>
    </div>
@endsection




