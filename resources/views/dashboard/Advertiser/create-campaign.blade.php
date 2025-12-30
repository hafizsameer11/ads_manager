@extends('dashboard.layouts.main')

@section('title', 'Create Campaign - Advertiser Dashboard')

@section('content')
    <!-- Create Campaign Form -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Campaign Details</h3>
        </div>
        <div class="card-body">
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

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="alert-content">
                        <strong><i class="fas fa-times-circle"></i> Error!</strong>
                        <ul class="mb-0 mt-2" style="list-style: none; padding-left: 0;">
                            @foreach($errors->all() as $error)
                                <li style="padding: 4px 0;"><i class="fas fa-chevron-right" style="font-size: 10px; margin-right: 8px;"></i>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <form method="POST" action="{{ route('dashboard.advertiser.create-campaign.store') }}">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Campaign Name *</label>
                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   placeholder="My Campaign Name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ad_type">Ad Type *</label>
                            <select id="ad_type" name="ad_type" class="form-control @error('ad_type') is-invalid @enderror" required>
                                <option value="">Select Ad Type</option>
                                <option value="banner" {{ old('ad_type') == 'banner' ? 'selected' : '' }}>Banner</option>
                                <option value="popup" {{ old('ad_type') == 'popup' ? 'selected' : '' }}>Popup</option>
                                <option value="popunder" {{ old('ad_type') == 'popunder' ? 'selected' : '' }}>Popunder</option>
                            </select>
                            @error('ad_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="pricing_model">Pricing Model *</label>
                            <select id="pricing_model" name="pricing_model" class="form-control" required>
                                <option value="">Select Pricing Model</option>
                                <option value="cpc">CPC (Cost Per Click)</option>
                                <option value="cpm">CPM (Cost Per Mille/1000 impressions)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="bid_amount">Bid Amount *</label>
                            <input type="number" id="bid_amount" name="bid_amount" class="form-control" 
                                   step="0.01" min="0.01" placeholder="0.00" required>
                            <small class="text-muted">Enter your bid per click (CPC) or per 1000 impressions (CPM)</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="budget">Total Budget *</label>
                            <input type="number" id="budget" name="budget" class="form-control" 
                                   step="0.01" min="1" placeholder="1000.00" required>
                            <small class="text-muted">Total amount you want to spend on this campaign</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="daily_budget">Daily Budget (Optional)</label>
                            <input type="number" id="daily_budget" name="daily_budget" class="form-control" 
                                   step="0.01" min="0.01" placeholder="100.00">
                            <small class="text-muted">Maximum amount to spend per day (optional)</small>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="target_url">Target URL *</label>
                            <input type="url" id="target_url" name="target_url" class="form-control" 
                                   placeholder="https://example.com" required>
                            <small class="text-muted">The URL where users will be redirected when they click your ad</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" id="start_date" name="start_date" class="form-control" 
                                   value="{{ date('Y-m-d') }}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_date">End Date (Optional)</label>
                            <input type="date" id="end_date" name="end_date" class="form-control">
                            <small class="text-muted">Leave empty for unlimited duration</small>
                        </div>
                    </div>
                </div>

                <hr>

                <h4>Ad Content</h4>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="ad_title">Ad Title</label>
                            <input type="text" id="ad_title" name="ad_title" class="form-control" 
                                   placeholder="Enter ad title">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="ad_description">Ad Description</label>
                            <textarea id="ad_description" name="ad_description" class="form-control" rows="4" 
                                      placeholder="Enter ad description"></textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="ad_image">Ad Image URL</label>
                            <input type="url" id="ad_image" name="ad_image" class="form-control" 
                                   placeholder="https://example.com/image.jpg">
                            <small class="text-muted">URL to your ad image/banner</small>
                        </div>
                    </div>
                </div>

                <hr>

                <h4>Targeting (Optional)</h4>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="target_countries">Target Countries</label>
                            <select id="target_countries" name="target_countries[]" class="form-control" multiple>
                                <option value="US">United States</option>
                                <option value="GB">United Kingdom</option>
                                <option value="CA">Canada</option>
                                <option value="AU">Australia</option>
                                <!-- Add more countries as needed -->
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple countries</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="target_devices">Target Devices</label>
                            <select id="target_devices" name="target_devices[]" class="form-control" multiple>
                                <option value="desktop">Desktop</option>
                                <option value="mobile">Mobile</option>
                                <option value="tablet">Tablet</option>
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple devices</small>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Current Balance:</strong> ${{ number_format($advertiser->balance ?? 0, 2) }}
                            @if(($advertiser->balance ?? 0) < 100)
                                <br><a href="{{ route('dashboard.advertiser.billing') }}">Add funds</a> to your account to create campaigns.
                            @endif
                        </div>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Create Campaign
                        </button>
                        <a href="{{ route('dashboard.advertiser.campaigns') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
