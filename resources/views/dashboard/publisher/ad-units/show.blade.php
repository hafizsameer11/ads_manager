@extends('dashboard.layouts.main')

@section('title', 'Ad Unit Details - Publisher Dashboard')

@section('content')
    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 20px;">
        <a href="{{ route('dashboard.publisher.sites.ad-units.index', $adUnit->website) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Ad Units
        </a>
        <a href="{{ route('dashboard.publisher.ad-units.edit', $adUnit) }}" class="btn btn-primary">
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

    <div class="row">
        <!-- Ad Unit Info -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ad Unit Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Name:</th>
                            <td><strong>{{ $adUnit->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Type:</th>
                            <td><span class="badge badge-info">{{ ucfirst($adUnit->type) }}</span></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                @if($adUnit->status === 'active')
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-warning">Paused</span>
                                @endif
                            </td>
                        </tr>
                        @if($adUnit->type === 'banner')
                        <tr>
                            <th>Size:</th>
                            <td>{{ $adUnit->size ?? ($adUnit->width . 'x' . $adUnit->height) }}</td>
                        </tr>
                        @else
                        <tr>
                            <th>Frequency:</th>
                            <td>{{ $adUnit->frequency }} seconds</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Unit Code:</th>
                            <td><code>{{ $adUnit->unit_code }}</code></td>
                        </tr>
                        <tr>
                            <th>Website:</th>
                            <td>{{ $adUnit->website->domain }}</td>
                        </tr>
                        <tr>
                            <th>Created:</th>
                            <td>{{ $adUnit->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Last Updated:</th>
                            <td>{{ $adUnit->updated_at->format('M d, Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Embed Code -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Embed Code</h3>
                </div>
                <div class="card-body">
                    @if($adUnit->website->verification_status !== 'verified')
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> <strong>Website Verification Required</strong>
                            <p class="mb-0 small mt-2">Your website must be verified before you can generate ad code. <a href="{{ route('dashboard.publisher.sites.show', $adUnit->website) }}">Go to website verification</a></p>
                        </div>
                    @elseif(!in_array($adUnit->website->status, ['approved', 'verified']))
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> <strong>Website is under review</strong>
                            <p class="mb-0 small mt-2">Your website must be approved before you can generate ad code.</p>
                        </div>
                    @elseif($adUnit->status !== 'active')
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> <strong>Ad unit is disabled by admin</strong>
                            <p class="mb-0 small mt-2">This ad unit is currently paused or disabled. Please contact support if you believe this is an error.</p>
                        </div>
                    @else
                    <p class="text-muted small mb-3">Copy and paste this code into your website to display the ad unit.</p>
                    
                    <div class="form-group">
                        <label>Embed Code:</label>
                        <textarea id="embedCode" class="form-control" rows="6" readonly>{{ $adUnit->embed_code }}</textarea>
                    </div>
                    
                    <button type="button" class="btn btn-primary btn-block" onclick="copyEmbedCode()">
                        <i class="fas fa-copy"></i> Copy Embed Code
                    </button>
                    
                    <div class="alert alert-info mt-3 small">
                        <strong>Instructions:</strong><br>
                        @if($adUnit->type === 'banner')
                                Place this code where you want the banner to appear on your website. The script will automatically load and display ads.
                        @else
                                Place this script tag in the &lt;head&gt; section or before the closing &lt;/body&gt; tag of your website. The script will automatically handle popup/popunder ads based on frequency settings.
                        @endif
                            <br><br>
                            <strong>Features:</strong>
                            <ul class="mb-0 mt-2" style="padding-left: 20px;">
                                <li>Automatic ad loading and display</li>
                                <li>Impression tracking (counts when ad is visible)</li>
                                <li>Click tracking</li>
                                <li>Mobile responsive</li>
                                <li>Fast loading (async script)</li>
                            </ul>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function copyEmbedCode() {
        const embedCode = document.getElementById('embedCode');
        embedCode.select();
        embedCode.setSelectionRange(0, 99999); // For mobile devices
        
        try {
            navigator.clipboard.writeText(embedCode.value).then(function() {
                alert('Embed code copied to clipboard!');
            }, function() {
                // Fallback for older browsers
                document.execCommand('copy');
                alert('Embed code copied to clipboard!');
            });
        } catch (err) {
            // Fallback
            document.execCommand('copy');
            alert('Embed code copied to clipboard!');
        }
    }
</script>
@endpush





