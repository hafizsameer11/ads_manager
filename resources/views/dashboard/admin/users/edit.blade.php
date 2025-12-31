@extends('dashboard.layouts.main')

@section('title', 'Edit User - Admin Dashboard')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <a href="{{ route('dashboard.admin.users.show', $user->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Details
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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong>
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
            <h3 class="card-title">
                @if($user->isPublisher())
                    Edit Publisher: {{ $user->name }}
                @elseif($user->isAdvertiser())
                    Edit Advertiser: {{ $user->name }}
                @else
                    Edit User: {{ $user->name }}
                @endif
            </h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.admin.users.update', $user->id) }}">
                @csrf
                @method('PUT')

                <h5 class="mb-3" style="border-bottom: 1px solid #ddd; padding-bottom: 10px;">User Information</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Full Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email Address <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" class="form-control" 
                                   value="{{ old('username', $user->username) }}">
                            @error('username')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="text" id="phone" name="phone" class="form-control" 
                                   value="{{ old('phone', $user->phone) }}">
                            @error('phone')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                @if($user->isPublisher() && $user->publisher)
                    <h5 class="mb-3" style="border-bottom: 1px solid #ddd; padding-bottom: 10px;">Publisher Settings</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="is_active">Account Status <span class="text-danger">*</span></label>
                                <select id="is_active" name="is_active" class="form-control" required>
                                    <option value="2" {{ old('is_active', $user->is_active) == 2 ? 'selected' : '' }}>Pending</option>
                                    <option value="1" {{ old('is_active', $user->is_active) == 1 ? 'selected' : '' }}>Approved</option>
                                    <option value="3" {{ old('is_active', $user->is_active) == 3 ? 'selected' : '' }}>Suspended</option>
                                    <option value="0" {{ old('is_active', $user->is_active) == 0 ? 'selected' : '' }}>Rejected/Blocked</option>
                                </select>
                                <small class="text-muted">This controls the account access status</small>
                                @error('is_active')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tier">Tier <span class="text-danger">*</span></label>
                                <select id="tier" name="tier" class="form-control" required>
                                    <option value="tier1" {{ old('tier', $user->publisher->tier) === 'tier1' ? 'selected' : '' }}>Tier 1 (Highest)</option>
                                    <option value="tier2" {{ old('tier', $user->publisher->tier) === 'tier2' ? 'selected' : '' }}>Tier 2 (Medium)</option>
                                    <option value="tier3" {{ old('tier', $user->publisher->tier) === 'tier3' ? 'selected' : '' }}>Tier 3 (Standard)</option>
                                </select>
                                <small class="text-muted">Tier determines revenue share percentage and benefits</small>
                                @error('tier')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="minimum_payout">Minimum Payout</label>
                                <input type="number" id="minimum_payout" name="minimum_payout" class="form-control" 
                                       value="{{ old('minimum_payout', $user->publisher->minimum_payout) }}" step="0.01" min="0">
                                <small class="text-muted">Minimum amount required for withdrawal</small>
                                @error('minimum_payout')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="is_premium" value="1" 
                                           {{ old('is_premium', $user->publisher->is_premium) ? 'checked' : '' }}>
                                    Premium Publisher
                                </label>
                                <small class="text-muted d-block">Premium publishers receive additional benefits and priority support</small>
                                @error('is_premium')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="notes">Admin Notes</label>
                                <textarea id="notes" name="notes" class="form-control" rows="4" 
                                          placeholder="Internal notes about this publisher...">{{ old('notes', $user->publisher->notes) }}</textarea>
                                <small class="text-muted">These notes are only visible to admins</small>
                                @error('notes')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                @elseif($user->isAdvertiser() && $user->advertiser)
                    <h5 class="mb-3" style="border-bottom: 1px solid #ddd; padding-bottom: 10px;">Advertiser Settings</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="is_active">Account Status <span class="text-danger">*</span></label>
                                <select id="is_active" name="is_active" class="form-control" required>
                                    <option value="2" {{ old('is_active', $user->is_active) == 2 ? 'selected' : '' }}>Pending</option>
                                    <option value="1" {{ old('is_active', $user->is_active) == 1 ? 'selected' : '' }}>Approved</option>
                                    <option value="3" {{ old('is_active', $user->is_active) == 3 ? 'selected' : '' }}>Suspended</option>
                                    <option value="0" {{ old('is_active', $user->is_active) == 0 ? 'selected' : '' }}>Rejected/Blocked</option>
                                </select>
                                <small class="text-muted">This controls the account access status</small>
                                @error('is_active')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="payment_email">Payment Email</label>
                                <input type="email" id="payment_email" name="payment_email" class="form-control" 
                                       value="{{ old('payment_email', $user->advertiser->payment_email) }}">
                                <small class="text-muted">Email address for payment notifications</small>
                                @error('payment_email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="payment_info">Payment Info</label>
                                <textarea id="payment_info" name="payment_info" class="form-control" rows="6" 
                                          placeholder='Enter payment information as JSON, e.g., {"gateway": "stripe", "account_id": "acc_123"}'>{{ old('payment_info', is_array($user->advertiser->payment_info) ? json_encode($user->advertiser->payment_info, JSON_PRETTY_PRINT) : $user->advertiser->payment_info) }}</textarea>
                                <small class="text-muted">Payment gateway information (JSON format recommended)</small>
                                @error('payment_info')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="notes">Admin Notes</label>
                                <textarea id="notes" name="notes" class="form-control" rows="4" 
                                          placeholder="Internal notes about this advertiser...">{{ old('notes', $user->advertiser->notes) }}</textarea>
                                <small class="text-muted">These notes are only visible to admins</small>
                                @error('notes')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                @endif

                <hr class="my-4">

                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> 
                            @if($user->isPublisher())
                                Update Publisher
                            @elseif($user->isAdvertiser())
                                Update Advertiser
                            @else
                                Update User
                            @endif
                        </button>
                        <a href="{{ route('dashboard.admin.users.show', $user->id) }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
