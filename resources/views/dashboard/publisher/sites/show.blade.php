@extends('dashboard.layouts.main')

@section('title', 'Website Details - Publisher Dashboard')

@section('content')
    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 20px;">
        <a href="{{ route('dashboard.publisher.sites') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Websites
        </a>
        <a href="{{ route('dashboard.publisher.sites.edit', $website) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <div class="alert-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="alert-content">
                <strong><i class="fas fa-check"></i> Success!</strong>
                <p>{{ session('success') }}</p>
            </div>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if($website->verification_status !== 'verified' && $website->verification_status !== null)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> <strong>Website Verification Required</strong>
            <p class="mb-0 mt-2">Please verify your website ownership before creating ad units. Follow the verification instructions below.</p>
        </div>
    @elseif($website->status === 'pending')
        <div class="alert alert-info">
            <i class="fas fa-clock"></i> <strong>Website Verified - Awaiting Admin Approval</strong>
            <p class="mb-0 mt-2">Your website has been verified. It is currently pending admin approval. You will be notified once it's reviewed.</p>
        </div>
    @elseif($website->status === 'rejected')
        <div class="alert alert-danger">
            <i class="fas fa-times-circle"></i> <strong>Website Rejected</strong>
            @if($website->rejection_reason)
                <p class="mb-0 mt-2">Reason: {{ $website->rejection_reason }}</p>
            @endif
        </div>
    @elseif($website->status === 'disabled')
        <div class="alert alert-secondary">
            <i class="fas fa-ban"></i> <strong>Website Disabled</strong>
            <p class="mb-0 mt-2">Your website has been disabled by admin. Please contact support for more information.</p>
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
                                @if(empty($website->status))
                                    <span class="badge badge-secondary">No Status</span>
                                @elseif(in_array($website->status, ['approved', 'verified']))
                                    <span class="badge badge-success">Approved</span>
                                    @if($website->approved_at)
                                        <br><small class="text-muted">Approved on {{ $website->approved_at->format('M d, Y H:i') }}</small>
                                    @endif
                                @elseif($website->status === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($website->status === 'disabled')
                                    <span class="badge badge-secondary">Disabled</span>
                                @elseif($website->status === 'rejected')
                                    <span class="badge badge-danger">Rejected</span>
                                    @if($website->rejected_at)
                                        <br><small class="text-muted">Rejected on {{ $website->rejected_at->format('M d, Y H:i') }}</small>
                                    @endif
                                @else
                                    <span class="badge badge-info">{{ ucfirst($website->status) }}</span>
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
                        <tr>
                            <th>Verification Status:</th>
                            <td>
                                @if($website->verification_status === 'verified')
                                    <span class="badge badge-success">Verified</span>
                                    @if($website->verified_at)
                                        <br><small class="text-muted">Verified on {{ $website->verified_at->format('M d, Y H:i') }}</small>
                                    @endif
                                @elseif($website->verification_status === 'failed')
                                    <span class="badge badge-danger">Verification Failed</span>
                                @elseif($website->verification_status === 'pending' || empty($website->verification_status))
                                    <span class="badge badge-warning">Pending Verification</span>
                                    <br><small class="text-muted">Click "Verify Website" button below to verify</small>
                                @else
                                    <span class="badge badge-warning">Not Verified</span>
                                @endif
                            </td>
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
                        @if($website->verification_status === 'verified' && in_array($website->status, ['approved', 'verified']))
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
                            @if($website->verification_status !== 'verified')
                                Website must be verified before creating ad units. Please complete verification first.
                            @elseif(!in_array($website->status, ['approved', 'verified']))
                                Website must be approved before creating ad units.
                            @else
                                No ad units yet. <a href="{{ route('dashboard.publisher.sites.ad-units.create', $website) }}">Create your first ad unit</a>
                            @endif
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Verification Info -->
        @if($website->verification_code && $website->verification_status !== 'verified')
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Verify Website Ownership</h3>
                </div>
                <div class="card-body">
                    @if($website->verification_method === 'meta_tag')
                        <p class="text-muted small mb-3">Add the following meta tag to the <code>&lt;head&gt;</code> section of your website:</p>
                    <div class="alert alert-info">
                        <code>&lt;meta name="ads-network-verification" content="{{ $website->verification_code }}"&gt;</code>
                    </div>
                        <button type="button" class="btn btn-primary btn-block mb-2" onclick="copyVerificationCode()">
                            <i class="fas fa-copy"></i> Copy Meta Tag
                        </button>
                        <hr>
                        <p class="text-muted small mb-2"><strong>Steps:</strong></p>
                        <ol class="text-muted small" style="padding-left: 20px;">
                            <li>Copy the meta tag above</li>
                            <li>Open your website's HTML file</li>
                            <li>Paste it in the <code>&lt;head&gt;</code> section</li>
                            <li>Save and upload your file</li>
                            <li>Click "Verify Website" button below</li>
                        </ol>
                    @elseif($website->verification_method === 'file_upload')
                        <p class="text-muted small mb-3">Upload the verification file to your website root directory:</p>
                        <div class="alert alert-info">
                            <strong>Filename:</strong><br>
                            <code>ads-network-verification-{{ $website->verification_code }}.html</code>
                        </div>
                        <a href="{{ route('dashboard.publisher.sites.verification-file', $website) }}" class="btn btn-primary btn-block mb-2" download>
                            <i class="fas fa-download"></i> Download Verification File
                        </a>
                        <hr>
                        <p class="text-muted small mb-2"><strong>Steps:</strong></p>
                        <ol class="text-muted small" style="padding-left: 20px;">
                            <li>Download the verification file</li>
                            <li>Upload it to your website root directory</li>
                            <li>Ensure it's accessible at: <code>http://{{ $website->domain }}/ads-network-verification-{{ $website->verification_code }}.html</code></li>
                            <li>Click "Verify Website" button below</li>
                        </ol>
                    @endif
                    
                    <hr>
                    
                    @if(session('error') || $errors->has('verification'))
                        <div class="alert alert-danger">
                            {{ session('error') ?: $errors->first('verification') }}
                        </div>
                    @endif
                    
                    <form action="{{ route('dashboard.publisher.sites.verify', $website) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success btn-block">
                            <i class="fas fa-check-circle"></i> Verify Website
                    </button>
                    </form>
                    
                    <p class="text-muted small mt-3 mb-0">
                        <i class="fas fa-info-circle"></i> After verification, you can create ad units for this website.
                    </p>
                </div>
            </div>
        </div>
        @elseif($website->verification_status === 'verified')
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-check-circle"></i> Website Verified
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-success mb-2">
                        <strong>✓ Website ownership verified</strong>
                    </p>
                    @if($website->verified_at)
                        <p class="text-muted small mb-0">
                            Verified on {{ $website->verified_at->format('M d, Y H:i') }}
                        </p>
                    @endif
                    @if(in_array($website->status, ['approved', 'verified']))
                        <hr>
                        <p class="mb-0">
                            <a href="{{ route('dashboard.publisher.sites.ad-units.create', $website) }}" class="btn btn-primary btn-block">
                                <i class="fas fa-plus"></i> Create Ad Unit
                            </a>
                        </p>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection

@push('scripts')
@if($website->verification_code && $website->verification_method === 'meta_tag')
<script>
    function copyVerificationCode() {
        const code = '{{ $website->verification_code }}';
        const metaTag = '<meta name="ads-network-verification" content="' + code + '">';
        
        navigator.clipboard.writeText(metaTag).then(function() {
            alert('Meta tag copied to clipboard!');
        }, function() {
            const textarea = document.createElement('textarea');
            textarea.value = metaTag;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            alert('Meta tag copied to clipboard!');
        });
    }
</script>
@endif
@endpush





