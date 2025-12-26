@extends('dashboard.layouts.main')

@section('title', 'Ad Units - Publisher Dashboard')

@section('content')
    <div class="page-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1>Ad Units</h1>
                <p class="text-muted">Manage ad units for {{ $website->domain }}</p>
            </div>
            <div>
                <a href="{{ route('dashboard.publisher.sites') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Websites
                </a>
                @if(in_array($website->status, ['approved', 'verified']))
                <a href="{{ route('dashboard.publisher.sites.ad-units.create', $website) }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Ad Unit
                </a>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Website Info Card -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">{{ $website->name }}</h5>
            <p class="card-text">
                <strong>Domain:</strong> {{ $website->domain }}<br>
                <strong>Status:</strong> 
                @if(in_array($website->status, ['approved', 'verified']))
                    <span class="badge badge-success">Approved</span>
                @elseif($website->status === 'pending')
                    <span class="badge badge-warning">Pending</span>
                @else
                    <span class="badge badge-danger">Rejected</span>
                @endif
            </p>
        </div>
    </div>

    <!-- Ad Units Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Ad Units ({{ $adUnits->total() }})</h3>
        </div>
        <div class="card-body">
            @if($adUnits->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Size/Frequency</th>
                                <th>Status</th>
                                <th>Unit Code</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($adUnits as $adUnit)
                            <tr>
                                <td>
                                    <strong>{{ $adUnit->name }}</strong>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ ucfirst($adUnit->type) }}</span>
                                </td>
                                <td>
                                    @if($adUnit->type === 'banner')
                                        {{ $adUnit->size ?? ($adUnit->width . 'x' . $adUnit->height) }}
                                    @else
                                        {{ $adUnit->frequency }}s
                                    @endif
                                </td>
                                <td>
                                    @if($adUnit->status === 'active')
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-warning">Paused</span>
                                    @endif
                                </td>
                                <td>
                                    <code>{{ $adUnit->unit_code }}</code>
                                </td>
                                <td>{{ $adUnit->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                        <a href="{{ route('dashboard.publisher.ad-units.show', $adUnit) }}" class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('dashboard.publisher.ad-units.edit', $adUnit) }}" class="btn btn-sm btn-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('dashboard.publisher.ad-units.destroy', $adUnit) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this ad unit?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="mt-3">
                    {{ $adUnits->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    @if(!in_array($website->status, ['approved', 'verified']))
                        <div class="alert alert-warning mb-3">
                            <strong>Website Not Approved:</strong> This website must be approved before you can create ad units.
                            Current status: <strong>{{ ucfirst($website->status) }}</strong>
                        </div>
                    @else
                        <p class="text-muted">No ad units yet.</p>
                        <a href="{{ route('dashboard.publisher.sites.ad-units.create', $website) }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Your First Ad Unit
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection

