@extends('dashboard.layouts.main')

@section('title', 'Create Ad Unit - Admin Dashboard')

@section('content')
    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 20px;">
        <a href="{{ route('dashboard.admin.ad-units') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Ad Units
        </a>
    </div>

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

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Create New Ad Unit</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.admin.ad-units.store') }}">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="publisher_id">Publisher <span class="text-danger">*</span></label>
                            <select id="publisher_id" name="publisher_id" class="form-control" required onchange="updateWebsites()">
                                <option value="">Select Publisher</option>
                                @foreach($publishers as $publisher)
                                    <option value="{{ $publisher->id }}" {{ old('publisher_id') == $publisher->id ? 'selected' : '' }}>
                                        {{ $publisher->user->name ?? 'N/A' }} ({{ $publisher->user->email ?? 'N/A' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="website_id">Website <span class="text-danger">*</span></label>
                            <select id="website_id" name="website_id" class="form-control" required>
                                <option value="">Select Website (select publisher first)</option>
                                @foreach($websites as $website)
                                    <option value="{{ $website->id }}" 
                                            data-publisher-id="{{ $website->publisher_id }}"
                                            {{ old('website_id') == $website->id ? 'selected' : '' }}>
                                        {{ $website->domain }} {{ $website->name ? '(' . $website->name . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Only approved websites are shown</small>
                        </div>
                    </div>
                </div>

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
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select id="status" name="status" class="form-control" required>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="paused" {{ old('status') == 'paused' ? 'selected' : '' }}>Paused</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="is_anti_adblock">Anti-Adblock</label>
                            <div class="form-check">
                                <input type="checkbox" id="is_anti_adblock" name="is_anti_adblock" class="form-check-input" value="1" {{ old('is_anti_adblock') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_anti_adblock">
                                    Enable anti-adblock detection
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cpm_rate">CPM Rate</label>
                            <input type="number" id="cpm_rate" name="cpm_rate" class="form-control" 
                                   value="{{ old('cpm_rate') }}" step="0.0001" min="0" placeholder="0.0000">
                            <small class="text-muted">Cost per mille (per 1000 impressions)</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cpc_rate">CPC Rate</label>
                            <input type="number" id="cpc_rate" name="cpc_rate" class="form-control" 
                                   value="{{ old('cpc_rate') }}" step="0.0001" min="0" placeholder="0.0000">
                            <small class="text-muted">Cost per click</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Ad Unit
                        </button>
                        <a href="{{ route('dashboard.admin.ad-units') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
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

    function updateWebsites() {
        const publisherId = document.getElementById('publisher_id').value;
        const websiteSelect = document.getElementById('website_id');
        const options = websiteSelect.querySelectorAll('option');
        
        // Show/hide options based on publisher
        options.forEach(option => {
            if (option.value === '') {
                option.style.display = 'block';
            } else {
                const optionPublisherId = option.getAttribute('data-publisher-id');
                if (publisherId && optionPublisherId !== publisherId) {
                    option.style.display = 'none';
                    if (option.selected) {
                        option.selected = false;
                        websiteSelect.options[0].selected = true;
                    }
                } else {
                    option.style.display = 'block';
                }
            }
        });
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleFields();
        updateWebsites();
    });
</script>
@endpush





