@extends('dashboard.layouts.main')

@section('title', 'Create Ad Unit - Publisher Dashboard')

@section('content')
    <div class="page-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1>Create Ad Unit</h1>
                <p class="text-muted">Add a new ad unit for {{ $website->domain }}</p>
            </div>
            <div>
                <a href="{{ route('dashboard.publisher.sites.ad-units.index', $website) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    @if(!in_array($website->status, ['approved', 'verified']))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong>Warning:</strong> This website must be approved before you can create ad units. 
            Current status: <strong>{{ ucfirst($website->status) }}</strong>
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

    @if(!in_array($website->status, ['approved', 'verified']))
        <div class="card">
            <div class="card-body text-center py-5">
                <p class="text-muted">You cannot create ad units for this website because it has not been approved yet.</p>
                <a href="{{ route('dashboard.publisher.sites.show', $website) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Website
                </a>
            </div>
        </div>
    @else
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Ad Unit Details</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.publisher.sites.ad-units.store', $website) }}">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Ad Unit Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   value="{{ old('name') }}" placeholder="e.g., Homepage Banner" required>
                            <small class="text-muted">A descriptive name for this ad unit</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="type">Ad Type <span class="text-danger">*</span></label>
                            <select id="type" name="type" class="form-control" required onchange="toggleFields()">
                                <option value="">Select Ad Type</option>
                                <option value="banner" {{ old('type') == 'banner' ? 'selected' : '' }}>Banner</option>
                                <option value="popup" {{ old('type') == 'popup' ? 'selected' : '' }}>Popup</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Banner Fields -->
                <div id="bannerFields" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="size">Banner Size <span class="text-danger">*</span></label>
                                <input type="text" id="size" name="size" class="form-control" 
                                       value="{{ old('size') }}" placeholder="e.g., 300x250" 
                                       pattern="\d+x\d+">
                                <small class="text-muted">Format: width x height (e.g., 300x250, 728x90, 320x50)</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Popup Fields -->
                <div id="popupFields" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="frequency">Frequency (seconds) <span class="text-danger">*</span></label>
                                <input type="number" id="frequency" name="frequency" class="form-control" 
                                       value="{{ old('frequency') }}" min="1" max="3600" 
                                       placeholder="e.g., 30">
                                <small class="text-muted">How often the popup should appear (1-3600 seconds)</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Ad Unit
                        </button>
                        <a href="{{ route('dashboard.publisher.sites.ad-units.index', $website) }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    @endif
@endsection

@push('scripts')
<script>
    function toggleFields() {
        const type = document.getElementById('type').value;
        const bannerFields = document.getElementById('bannerFields');
        const popupFields = document.getElementById('popupFields');
        
        if (type === 'banner') {
            bannerFields.style.display = 'block';
            popupFields.style.display = 'none';
            document.getElementById('size').required = true;
            document.getElementById('frequency').required = false;
        } else if (type === 'popup') {
            bannerFields.style.display = 'none';
            popupFields.style.display = 'block';
            document.getElementById('size').required = false;
            document.getElementById('frequency').required = true;
        } else {
            bannerFields.style.display = 'none';
            popupFields.style.display = 'none';
            document.getElementById('size').required = false;
            document.getElementById('frequency').required = false;
        }
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleFields();
    });
</script>
@endpush

