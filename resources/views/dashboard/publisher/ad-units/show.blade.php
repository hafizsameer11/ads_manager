@extends('dashboard.layouts.main')

@section('title', 'Ad Unit Details - Publisher Dashboard')

@section('content')
    <div class="page-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1>Ad Unit Details</h1>
                <p class="text-muted">{{ $adUnit->name }}</p>
            </div>
            <div>
                <a href="{{ route('dashboard.publisher.sites.ad-units.index', $adUnit->website) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Ad Units
                </a>
                <a href="{{ route('dashboard.publisher.ad-units.edit', $adUnit) }}" class="btn btn-primary">
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
                            Place this iframe code where you want the banner to appear on your website.
                        @else
                            Place this script tag in the &lt;head&gt; section or before the closing &lt;/body&gt; tag of your website.
                        @endif
                    </div>
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




