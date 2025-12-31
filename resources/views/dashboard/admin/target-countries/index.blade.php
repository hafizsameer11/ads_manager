@extends('dashboard.layouts.main')

@section('title', 'Target Countries & Devices - Admin Dashboard')

@push('styles')
<style>
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

    .action-buttons .action-form {
        display: inline-block;
        margin: 0;
    }

    .action-buttons form {
        display: inline-block;
        margin: 0;
    }
</style>
@endpush

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Target Countries</h3>
            <a href="{{ route('dashboard.admin.target-countries.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Country
            </a>
        </div>
        <div class="card-body">
            @if(session('success') && str_contains(strtolower(session('success')), 'country'))
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
                            <th>Name</th>
                            <th>Code</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($countries as $country)
                            <tr>
                                <td>#{{ $country->id }}</td>
                                <td><strong>{{ $country->name }}</strong></td>
                                <td><code>{{ $country->code }}</code></td>
                                <td>
                                    @if($country->is_enabled)
                                        <span class="badge bg-success">Enabled</span>
                                    @else
                                        <span class="badge bg-secondary">Disabled</span>
                                    @endif
                                </td>
                                <td>{{ $country->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('dashboard.admin.target-countries.edit', $country->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('dashboard.admin.target-countries.toggle-status', $country->id) }}" method="POST" class="action-form">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $country->is_enabled ? 'btn-warning' : 'btn-success' }}" title="{{ $country->is_enabled ? 'Disable' : 'Enable' }}">
                                                <i class="fas fa-{{ $country->is_enabled ? 'toggle-on' : 'toggle-off' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('dashboard.admin.target-countries.destroy', $country->id) }}" method="POST" class="action-form" onsubmit="return confirm('Are you sure you want to delete this country?');">
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
                                <td colspan="6" class="text-center text-muted">No countries found. <a href="{{ route('dashboard.admin.target-countries.create') }}">Create one</a></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($countries->hasPages())
                <div class="mt-3">
                    {{ $countries->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Target Devices Table -->
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Target Devices</h3>
            <a href="{{ route('dashboard.admin.target-countries.create-device') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Device
            </a>
        </div>
        <div class="card-body">
            @if(session('success') && str_contains(strtolower(session('success')), 'device'))
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
                            <th>Name</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($devices as $device)
                            <tr>
                                <td>#{{ $device->id }}</td>
                                <td><strong>{{ $device->name }}</strong></td>
                                <td>
                                    @if($device->is_enabled)
                                        <span class="badge bg-success">Enabled</span>
                                    @else
                                        <span class="badge bg-secondary">Disabled</span>
                                    @endif
                                </td>
                                <td>{{ $device->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('dashboard.admin.target-countries.edit-device', $device->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('dashboard.admin.target-countries.toggle-device-status', $device->id) }}" method="POST" class="action-form">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $device->is_enabled ? 'btn-warning' : 'btn-success' }}" title="{{ $device->is_enabled ? 'Disable' : 'Enable' }}">
                                                <i class="fas fa-{{ $device->is_enabled ? 'toggle-on' : 'toggle-off' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('dashboard.admin.target-countries.destroy-device', $device->id) }}" method="POST" class="action-form" onsubmit="return confirm('Are you sure you want to delete this device?');">
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
                                <td colspan="5" class="text-center text-muted">No devices found. <a href="{{ route('dashboard.admin.target-countries.create-device') }}">Create one</a></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($devices->hasPages())
                <div class="mt-3">
                    {{ $devices->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

