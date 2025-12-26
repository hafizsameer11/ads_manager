@extends('dashboard.layouts.main')

@section('title', 'Sites Management - Publisher Dashboard')

@section('content')
    <div class="page-header">
        <h1>My Websites</h1>
        <p class="text-muted">Manage your websites and ad units.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Websites</div>
            <div class="stat-value">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Approved</div>
            <div class="stat-value">{{ number_format($stats['approved']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pending</div>
            <div class="stat-value">{{ number_format($stats['pending']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Rejected</div>
            <div class="stat-value">{{ number_format($stats['rejected']) }}</div>
        </div>
    </div>

    <!-- Add Website Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add New Website</h3>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
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

            <form method="POST" action="{{ route('dashboard.publisher.sites.store') }}">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Website Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="My Website" value="{{ old('name') }}" required>
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="domain">Domain <span class="text-danger">*</span></label>
                            <input type="text" id="domain" name="domain" class="form-control" placeholder="example.com" value="{{ old('domain') }}" required>
                            <small class="text-muted">Enter domain without http:// or https:// (e.g., example.com)</small>
                            @error('domain')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="verification_method">Verification Method <span class="text-danger">*</span></label>
                            <select id="verification_method" name="verification_method" class="form-control" required>
                                <option value="meta_tag" {{ old('verification_method') == 'meta_tag' ? 'selected' : '' }}>Meta Tag</option>
                                <option value="file_upload" {{ old('verification_method') == 'file_upload' ? 'selected' : '' }}>File Upload</option>
                                <option value="dns" {{ old('verification_method') == 'dns' ? 'selected' : '' }}>DNS Verification</option>
                            </select>
                            <small class="text-muted">Choose how you want to verify domain ownership</small>
                            @error('verification_method')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Website
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.publisher.sites') }}">
                <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; margin-bottom: 10px;">
                    <div style="flex: 0 0 auto; min-width: 150px; max-width: 180px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div style="flex: 1 1 auto; min-width: 250px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by domain or name..." value="{{ request('search') }}">
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('dashboard.publisher.sites') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Websites Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">My Websites</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Domain</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Ad Units</th>
                            <th>Verification Method</th>
                            <th>Added Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($websites as $website)
                        <tr>
                            <td>
                                <strong>{{ $website->domain }}</strong>
                            </td>
                            <td>{{ $website->name }}</td>
                            <td>
                                @if(in_array($website->status, ['approved', 'verified']))
                                    <span class="badge badge-success">Approved</span>
                                @elseif($website->status === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @else
                                    <span class="badge badge-danger">Rejected</span>
                                    @if($website->rejection_reason)
                                        <br><small class="text-muted">{{ $website->rejection_reason }}</small>
                                    @endif
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $website->adUnits->count() }}</span>
                            </td>
                            <td>{{ ucfirst(str_replace('_', ' ', $website->verification_method)) }}</td>
                            <td>{{ $website->created_at->format('M d, Y') }}</td>
                            <td>
                                <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                    <a href="{{ route('dashboard.publisher.sites.show', $website) }}" class="btn btn-sm btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($website->status === 'pending')
                                        @if($website->verification_method === 'meta_tag' && $website->verification_code)
                                            <button type="button" class="btn btn-sm btn-secondary" title="View Verification Code" onclick="showVerificationCode('{{ $website->verification_code }}', '{{ $website->domain }}')">
                                                <i class="fas fa-code"></i>
                                            </button>
                                        @endif
                                    @endif
                                    @if(in_array($website->status, ['approved', 'verified']))
                                        <a href="{{ route('dashboard.publisher.sites.ad-units.index', $website) }}" class="btn btn-sm btn-primary" title="Manage Ad Units">
                                            <i class="fas fa-ad"></i> Ad Units
                                        </a>
                                    @endif
                                    <a href="{{ route('dashboard.publisher.sites.edit', $website) }}" class="btn btn-sm btn-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('dashboard.publisher.sites.destroy', $website) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this website? This will also delete all associated ad units.');">
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
                            <td colspan="7" class="text-center text-muted">
                                <p>No websites yet. Add your first website using the form above.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-3">
                {{ $websites->links() }}
            </div>
        </div>
    </div>

    <!-- Verification Code Modal -->
    <div class="modal fade" id="verificationModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Domain Verification Code</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Add the following meta tag to the <code>&lt;head&gt;</code> section of your website:</p>
                    <div class="alert alert-info">
                        <code>&lt;meta name="ads-network-verification" content="<span id="verificationCode"></span>"&gt;</code>
                    </div>
                    <p class="mb-2"><strong>Domain:</strong> <span id="verificationDomain"></span></p>
                    <p class="text-muted small">After adding the meta tag, our system will automatically verify your domain ownership. This may take a few minutes to a few hours.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="copyVerificationCode()">
                        <i class="fas fa-copy"></i> Copy Code
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function showVerificationCode(code, domain) {
        document.getElementById('verificationCode').textContent = code;
        document.getElementById('verificationDomain').textContent = domain;
        $('#verificationModal').modal('show');
    }

    function copyVerificationCode() {
        const code = document.getElementById('verificationCode').textContent;
        const metaTag = '<meta name="ads-network-verification" content="' + code + '">';
        
        navigator.clipboard.writeText(metaTag).then(function() {
            alert('Verification code copied to clipboard!');
        }, function() {
            // Fallback for older browsers
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
@endpush
