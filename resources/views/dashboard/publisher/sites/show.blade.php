@extends('dashboard.layouts.main')

@section('title', 'Website Details - Publisher Dashboard')

@section('content')
    <div class="page-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1>Website Details</h1>
                <p class="text-muted">{{ $website->domain }}</p>
            </div>
            <div>
                <a href="{{ route('dashboard.publisher.sites') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Websites
                </a>
                <a href="{{ route('dashboard.publisher.sites.edit', $website) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Edit
                </a>
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

    <div class="row">
        <!-- Website Information -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Website Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Domain:</th>
                            <td><strong>{{ $website->domain }}</strong></td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td>{{ $website->name }}</td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                @if(in_array($website->status, ['approved', 'verified']))
                                    <span class="badge badge-success">Approved</span>
                                @elseif($website->status === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @else
                                    <span class="badge badge-danger">Rejected</span>
                                @endif
                            </td>
                        </tr>
                        @if($website->rejection_reason)
                        <tr>
                            <th>Rejection Reason:</th>
                            <td class="text-danger">{{ $website->rejection_reason }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Verification Method:</th>
                            <td>{{ ucfirst(str_replace('_', ' ', $website->verification_method)) }}</td>
                        </tr>
                        @if($website->verification_code)
                        <tr>
                            <th>Verification Code:</th>
                            <td><code>{{ $website->verification_code }}</code></td>
                        </tr>
                        @endif
                        @if($website->verified_at)
                        <tr>
                            <th>Verified At:</th>
                            <td>{{ $website->verified_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Created:</th>
                            <td>{{ $website->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td>{{ $website->updated_at->format('M d, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Ad Units -->
            <div class="card mt-4">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 class="card-title">Ad Units ({{ $website->adUnits->count() }})</h3>
                        @if(in_array($website->status, ['approved', 'verified']))
                            <a href="{{ route('dashboard.publisher.sites.ad-units.create', $website) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> Create Ad Unit
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if($website->adUnits->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($website->adUnits as $adUnit)
                                    <tr>
                                        <td><strong>{{ $adUnit->name }}</strong></td>
                                        <td><span class="badge badge-info">{{ ucfirst($adUnit->type) }}</span></td>
                                        <td>
                                            @if($adUnit->status === 'active')
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-warning">Paused</span>
                                            @endif
                                        </td>
                                        <td>{{ $adUnit->created_at->format('M d, Y') }}</td>
                                        <td>
                                            <a href="{{ route('dashboard.publisher.ad-units.show', $adUnit) }}" class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center py-3">
                            @if(in_array($website->status, ['approved', 'verified']))
                                No ad units yet. <a href="{{ route('dashboard.publisher.sites.ad-units.create', $website) }}">Create your first ad unit</a>
                            @else
                                Website must be approved before creating ad units.
                            @endif
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Verification Info -->
        @if($website->status === 'pending' && $website->verification_method === 'meta_tag' && $website->verification_code)
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Verification Instructions</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Add the following meta tag to the <code>&lt;head&gt;</code> section of your website:</p>
                    <div class="alert alert-info">
                        <code>&lt;meta name="ads-network-verification" content="{{ $website->verification_code }}"&gt;</code>
                    </div>
                    <button type="button" class="btn btn-primary btn-block" onclick="copyVerificationCode()">
                        <i class="fas fa-copy"></i> Copy Verification Code
                    </button>
                    <p class="text-muted small mt-3">
                        After adding the meta tag, our system will automatically verify your domain ownership. This may take a few minutes to a few hours.
                    </p>
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection

@push('scripts')
@if($website->status === 'pending' && $website->verification_method === 'meta_tag' && $website->verification_code)
<script>
    function copyVerificationCode() {
        const code = '{{ $website->verification_code }}';
        const metaTag = '<meta name="ads-network-verification" content="' + code + '">';
        
        navigator.clipboard.writeText(metaTag).then(function() {
            alert('Verification code copied to clipboard!');
        }, function() {
            const textarea = document.createElement('textarea');
            textarea.value = metaTag;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            alert('Verification code copied to clipboard!');
        });
    }
</script>
@endif
@endpush




