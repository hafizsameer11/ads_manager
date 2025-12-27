@extends('dashboard.layouts.main')

@section('title', 'Sites Management - Publisher Dashboard')

@section('content')
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
        <div class="stat-card">
            <div class="stat-label">Disabled</div>
            <div class="stat-value">{{ number_format($stats['disabled'] ?? 0) }}</div>
        </div>
    </div>

    <!-- Add Website Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add New Website</h3>
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

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show validation-alert" role="alert">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="alert-content">
                        <h6 class="alert-heading">
                            <i class="fas fa-times-circle"></i> Please correct the following errors:
                        </h6>
                        <ul class="validation-errors">
                            @foreach($errors->all() as $error)
                                <li><i class="fas fa-chevron-right"></i> {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
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
                            <input type="text" id="domain" name="domain" class="form-control" placeholder="example.com or http://127.0.0.1:5500/index.html" value="{{ old('domain') }}" required>
                            <small class="text-muted">Enter domain (e.g., example.com) or full URL for localhost (e.g., http://127.0.0.1:5500/index.html)</small>
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
                            <option value="disabled" {{ request('status') == 'disabled' ? 'selected' : '' }}>Disabled</option>
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
                                @php
                                    $status = $website->status ?? 'pending';
                                @endphp
                                @if(in_array($status, ['approved', 'verified']))
                                    <span class="badge badge-success">Approved</span>
                                    @if($website->verification_status === 'verified')
                                        <br><small class="text-success"><i class="fas fa-check-circle"></i> Verified</small>
                                    @else
                                        <br><small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Not Verified</small>
                                    @endif
                                @elseif($status === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                    @if($website->verification_status === 'verified')
                                        <br><small class="text-success"><i class="fas fa-check-circle"></i> Verified - Awaiting Approval</small>
                                    @else
                                        <br><small class="text-warning"><i class="fas fa-exclamation-triangle"></i> Verification Required</small>
                                    @endif
                                @elseif($status === 'disabled')
                                    <span class="badge badge-secondary">Disabled</span>
                                    <br><small class="text-muted"><i class="fas fa-ban"></i> Website disabled by admin</small>
                                @elseif($status === 'rejected')
                                    <span class="badge badge-danger">Rejected</span>
                                    @if($website->rejection_reason)
                                        <br><small class="text-muted"><i class="fas fa-exclamation-triangle"></i> {{ $website->rejection_reason }}</small>
                                    @endif
                                    @if($website->admin_note)
                                        <br><small class="text-muted"><i class="fas fa-sticky-note"></i> Admin note available</small>
                                    @endif
                                @else
                                    <span class="badge badge-info">{{ ucfirst($status) }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-info">{{ $website->adUnits->count() }}</span>
                            </td>
                            <td>{{ ucfirst(str_replace('_', ' ', $website->verification_method)) }}</td>
                            <td>{{ $website->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('dashboard.publisher.sites.show', $website) }}" class="btn btn-sm btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($website->verification_status !== 'verified' && $website->verification_code)
                                        <a href="{{ route('dashboard.publisher.sites.show', $website) }}" class="btn btn-sm btn-warning" title="Verify Website">
                                            <i class="fas fa-check-circle"></i> Verify
                                        </a>
                                    @endif
                                    @if($website->verification_status === 'verified' && in_array($website->status, ['approved', 'verified']))
                                        <a href="{{ route('dashboard.publisher.sites.ad-units.index', $website) }}" class="btn btn-sm btn-primary" title="Manage Ad Units">
                                            <i class="fas fa-ad"></i> Ad Units
                                        </a>
                                    @endif
                                    <a href="{{ route('dashboard.publisher.sites.edit', $website) }}" class="btn btn-sm btn-secondary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('dashboard.publisher.sites.destroy', $website) }}" method="POST" class="action-form" onsubmit="return confirm('Are you sure you want to delete this website? This will also delete all associated ad units.');">
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
    <div class="modal fade" id="verificationModal" tabindex="-1" role="dialog" aria-labelledby="verificationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content verification-modal">
                <div class="modal-header">
                    <div class="modal-header-content">
                        <div class="modal-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div>
                            <h5 class="modal-title" id="verificationModalLabel">Domain Verification Code</h5>
                            <p class="modal-subtitle">Verify your domain ownership</p>
                        </div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="verification-instructions">
                        <p class="instruction-text">
                            <i class="fas fa-info-circle"></i> Add the following meta tag to the <code>&lt;head&gt;</code> section of your website:
                        </p>
                        <div class="code-block">
                            <div class="code-header">
                                <span class="code-label">Meta Tag</span>
                                <button type="button" class="btn-copy-code" onclick="copyVerificationCode(event)" title="Copy to clipboard">
                                    <i class="fas fa-copy"></i> Copy
                                </button>
                            </div>
                            <pre class="code-content"><code>&lt;meta name="ads-network-verification" content="<span id="verificationCode"></span>"&gt;</code></pre>
                        </div>
                        <div class="domain-info">
                            <div class="info-item">
                                <i class="fas fa-globe"></i>
                                <div>
                                    <span class="info-label">Domain:</span>
                                    <span class="info-value" id="verificationDomain"></span>
                                </div>
                            </div>
                        </div>
                        <div class="verification-note">
                            <i class="fas fa-clock"></i>
                            <p>After adding the meta tag, our system will automatically verify your domain ownership. This may take a few minutes to a few hours.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                    <button type="button" class="btn btn-primary" onclick="copyVerificationCode(event)">
                        <i class="fas fa-copy"></i> Copy Code
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    /* Success Alert Display */
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
    }

    .success-alert .close:hover {
        opacity: 1;
    }

    /* Validation Error Display */
    .validation-alert {
        display: flex;
        align-items: flex-start;
        gap: 15px;
        padding: 16px 20px;
        border-left: 4px solid var(--danger-color);
        background-color: #fff5f5;
        border-radius: var(--border-radius);
        margin-bottom: 24px;
        position: relative;
    }

    .validation-alert .alert-icon {
        flex-shrink: 0;
        font-size: 24px;
        color: var(--danger-color);
        margin-top: 2px;
    }

    .validation-alert .alert-content {
        flex: 1;
    }

    .validation-alert .alert-heading {
        font-size: 16px;
        font-weight: 600;
        color: var(--danger-color);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .validation-alert .validation-errors {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .validation-alert .validation-errors li {
        padding: 8px 0;
        color: var(--text-primary);
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 14px;
        line-height: 1.5;
    }

    .validation-alert .validation-errors li i {
        color: var(--danger-color);
        font-size: 10px;
        margin-top: 6px;
        flex-shrink: 0;
    }

    .validation-alert .close {
        position: absolute;
        top: 16px;
        right: 16px;
        font-size: 20px;
        opacity: 0.6;
    }

    .validation-alert .close:hover {
        opacity: 1;
    }

    /* Verification Modal Styling */
    .verification-modal .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    }

    .verification-modal .modal-header {
        border-bottom: 1px solid var(--border-color);
        padding: 24px 30px;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-radius: 12px 12px 0 0;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .verification-modal .modal-header .close {
        position: absolute;
        top: 20px;
        right: 20px;
        padding: 0;
        background: transparent;
        border: none;
        font-size: 24px;
        font-weight: 300;
        line-height: 1;
        color: var(--text-secondary);
        opacity: 0.5;
        cursor: pointer;
        z-index: 1;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        transition: all 0.2s;
    }

    .verification-modal .modal-header .close:hover {
        opacity: 1;
        background-color: rgba(0, 0, 0, 0.05);
        color: var(--text-primary);
    }

    .verification-modal .modal-header .close span {
        font-size: 28px;
        line-height: 1;
    }

    .modal-header-content {
        display: flex;
        align-items: center;
        gap: 16px;
        flex: 1;
        padding-right: 50px;
    }

    .modal-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        flex-shrink: 0;
    }

    .modal-title {
        font-size: 20px;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }

    .modal-subtitle {
        font-size: 13px;
        color: var(--text-secondary);
        margin: 4px 0 0 0;
    }

    .verification-modal .modal-body {
        padding: 30px;
    }

    .verification-instructions {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .instruction-text {
        font-size: 14px;
        color: var(--text-secondary);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .instruction-text i {
        color: var(--info-color);
    }

    .code-block {
        background-color: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        overflow: hidden;
    }

    .code-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        background-color: #2c3e50;
        border-bottom: 1px solid var(--border-color);
    }

    .code-label {
        color: white;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-copy-code {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-copy-code:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.3);
    }

    .code-content {
        padding: 16px;
        margin: 0;
        background-color: #1e293b;
        color: #e2e8f0;
        font-size: 13px;
        line-height: 1.6;
        overflow-x: auto;
    }

    .code-content code {
        color: #38bdf8;
        font-family: 'Courier New', Courier, monospace;
        white-space: pre-wrap;
        word-break: break-all;
    }

    .domain-info {
        padding: 16px;
        background-color: var(--bg-secondary);
        border-radius: 8px;
        border: 1px solid var(--border-color);
    }

    .info-item {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .info-item i {
        color: var(--info-color);
        font-size: 18px;
        flex-shrink: 0;
    }

    .info-label {
        font-weight: 600;
        color: var(--text-primary);
        margin-right: 8px;
    }

    .info-value {
        color: var(--text-secondary);
        font-family: 'Courier New', Courier, monospace;
        font-size: 13px;
    }

    .verification-note {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 16px;
        background-color: #fef3c7;
        border-left: 4px solid var(--warning-color);
        border-radius: 8px;
    }

    .verification-note i {
        color: var(--warning-color);
        font-size: 18px;
        margin-top: 2px;
        flex-shrink: 0;
    }

    .verification-note p {
        margin: 0;
        font-size: 13px;
        color: var(--text-secondary);
        line-height: 1.6;
    }

    .verification-modal .modal-footer {
        border-top: 1px solid var(--border-color);
        padding: 20px 30px;
        background-color: var(--bg-secondary);
        border-radius: 0 0 12px 12px;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    /* Action buttons container - keep all buttons in one row */
    .action-buttons {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: nowrap;
        white-space: nowrap;
    }

    /* Action buttons styling */
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

    /* Form buttons inline styling */
    .action-form {
        display: inline-flex;
        margin: 0;
        padding: 0;
    }

    .action-form .btn {
        margin: 0;
    }

    /* Ensure table actions column doesn't wrap and takes minimum space */
    .table td:last-child {
        white-space: nowrap;
        width: 1%;
        min-width: 250px;
    }

    /* Compact button text on smaller screens */
    @media (max-width: 1400px) {
        .action-buttons .btn {
            padding: 6px 8px;
        }
    }

    @media (max-width: 1200px) {
        .action-buttons {
            gap: 4px;
        }
        
        .action-buttons .btn {
            padding: 5px 8px;
            font-size: 11px;
        }
        
        .action-buttons .btn i {
            font-size: 10px;
        }
        
        .table td:last-child {
            min-width: 220px;
        }
    }

    @media (max-width: 992px) {
        .table td:last-child {
            min-width: 200px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function showVerificationCode(code, domain) {
        document.getElementById('verificationCode').textContent = code;
        document.getElementById('verificationDomain').textContent = domain;
        $('#verificationModal').modal('show');
    }

    function copyVerificationCode(event) {
        const code = document.getElementById('verificationCode').textContent;
        const metaTag = '<meta name="ads-network-verification" content="' + code + '">';
        const clickedBtn = event ? event.target.closest('button') : null;
        
        navigator.clipboard.writeText(metaTag).then(function() {
            // Show success feedback on clicked button
            if (clickedBtn) {
                const originalText = clickedBtn.innerHTML;
                clickedBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                clickedBtn.style.background = clickedBtn.classList.contains('btn-copy-code') 
                    ? 'rgba(39, 174, 96, 0.2)' 
                    : 'var(--success-color)';
                setTimeout(function() {
                    clickedBtn.innerHTML = originalText;
                    clickedBtn.style.background = '';
                }, 2000);
            }
        }, function() {
            // Fallback for older browsers
            const textarea = document.createElement('textarea');
            textarea.value = metaTag;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            
            if (clickedBtn) {
                const originalText = clickedBtn.innerHTML;
                clickedBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                setTimeout(function() {
                    clickedBtn.innerHTML = originalText;
                }, 2000);
            } else {
                alert('Verification code copied to clipboard!');
            }
        });
    }
</script>
@endpush
