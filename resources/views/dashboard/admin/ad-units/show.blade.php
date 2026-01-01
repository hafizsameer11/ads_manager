@extends('dashboard.layouts.main')

@section('title', 'Ad Unit Details - Admin Dashboard')

@section('content')
    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 20px;">
        <a href="{{ route('dashboard.admin.ad-units') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Ad Units
        </a>
        <a href="{{ route('dashboard.admin.ad-units.edit', $adUnit->id) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Edit
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Success!</strong> {{ session('success') }}
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
                            <th>Publisher:</th>
                            <td>
                                @if($adUnit->publisher && $adUnit->publisher->user)
                                    <strong>{{ $adUnit->publisher->user->name }}</strong><br>
                                    <small class="text-muted">{{ $adUnit->publisher->user->email }}</small>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Website:</th>
                            <td>
                                @if($adUnit->website)
                                    <strong>{{ $adUnit->website->domain }}</strong>
                                    @if($adUnit->website->name)
                                        <br><small class="text-muted">{{ $adUnit->website->name }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Anti-Adblock:</th>
                            <td>
                                @if($adUnit->is_anti_adblock)
                                    <span class="badge badge-success">Enabled</span>
                                @else
                                    <span class="badge badge-secondary">Disabled</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>CPM Rate:</th>
                            <td>${{ number_format($adUnit->cpm_rate ?? 0, 4) }}</td>
                        </tr>
                        <tr>
                            <th>CPC Rate:</th>
                            <td>${{ number_format($adUnit->cpc_rate ?? 0, 4) }}</td>
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

            <!-- Statistics -->
            @if(isset($stats))
            <div class="card" style="margin-top: 20px;">
                <div class="card-header">
                    <h3 class="card-title">Statistics</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="stat-item" style="margin-bottom: 20px;">
                                <div class="stat-label" style="font-size: 12px; color: #666; margin-bottom: 5px;">Total Impressions</div>
                                <div class="stat-value" style="font-size: 24px; font-weight: bold;">{{ number_format($stats['impressions']) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-item" style="margin-bottom: 20px;">
                                <div class="stat-label" style="font-size: 12px; color: #666; margin-bottom: 5px;">Total Clicks</div>
                                <div class="stat-value" style="font-size: 24px; font-weight: bold;">{{ number_format($stats['clicks']) }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-item" style="margin-bottom: 20px;">
                                <div class="stat-label" style="font-size: 12px; color: #666; margin-bottom: 5px;">Total Revenue</div>
                                <div class="stat-value" style="font-size: 24px; font-weight: bold; color: #28a745;">${{ number_format($stats['total_revenue'], 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Embed Code -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Embed Code</h3>
                </div>
                <div class="card-body">
                    @if($adUnit->website && $adUnit->website->status !== 'approved')
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> <strong>Website Not Approved</strong>
                            <p class="mb-0 small mt-2">The website must be approved for the embed code to work properly.</p>
                        </div>
                    @elseif($adUnit->status !== 'active')
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> <strong>Ad Unit is Paused</strong>
                            <p class="mb-0 small mt-2">This ad unit is currently paused and will not display ads.</p>
                        </div>
                    @else
                    <p class="text-muted small mb-3">Copy and paste this code into the website to display the ad unit.</p>
                    
                    <div class="form-group">
                        <label>Embed Code:</label>
                        <textarea id="embedCode" class="form-control" rows="8" readonly>{{ $adUnit->embed_code }}</textarea>
                    </div>
                    
                    <button type="button" class="btn btn-primary btn-block" onclick="copyEmbedCode()">
                        <i class="fas fa-copy"></i> Copy Embed Code
                    </button>
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
        embedCode.setSelectionRange(0, 99999);
        
        try {
            navigator.clipboard.writeText(embedCode.value).then(function() {
                alert('Embed code copied to clipboard!');
            }, function() {
                document.execCommand('copy');
                alert('Embed code copied to clipboard!');
            });
        } catch (err) {
            document.execCommand('copy');
            alert('Embed code copied to clipboard!');
        }
    }
</script>
@endpush




