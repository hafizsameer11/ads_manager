@extends('dashboard.layouts.main')

@section('title', 'Profile - Publisher Dashboard')

@section('content')
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

    <!-- Profile Information -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Profile Information</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.publisher.profile.update') }}">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" name="username" class="form-control" 
                                   value="{{ old('username', $user->username) }}" required>
                            @error('username')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="{{ old('email', $user->email) }}" required>
                            @error('email')
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
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="role">Role</label>
                            <input type="text" id="role" class="form-control" 
                                   value="{{ ucfirst($user->role) }}" disabled>
                            <small class="text-muted">Role cannot be changed</small>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <hr>
                        <h5 class="mb-3">Change Password</h5>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" id="password" name="password" class="form-control" 
                                   placeholder="Leave blank to keep current password">
                            @error('password')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                            <small class="text-muted">Leave blank if you don't want to change password</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password_confirmation">Confirm New Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" 
                                   placeholder="Confirm new password">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Account Information -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Account Information</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Account Status</label>
                        <div>
                            @if($user->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Publisher Status</label>
                        <div>
                            @if($publisher)
                                @if($publisher->status === 'approved')
                                    <span class="badge badge-success">Approved</span>
                                @elseif($publisher->status === 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @else
                                    <span class="badge badge-danger">Rejected</span>
                                @endif
                            @else
                                <span class="badge badge-secondary">N/A</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Member Since</label>
                        <div>
                            <span class="text-muted">{{ $user->created_at->format('F d, Y') }}</span>
                        </div>
                    </div>
                </div>
                @if($publisher)
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Publisher Tier</label>
                        <div>
                            <span class="badge badge-info">{{ ucfirst($publisher->tier) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Available Balance</label>
                        <div>
                            <span class="text-muted">${{ number_format($publisher->balance, 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Total Earnings</label>
                        <div>
                            <span class="text-muted">${{ number_format($publisher->total_earnings ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>
                @endif
                @if($user->referral_code)
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Referral Code</label>
                        <div>
                            <code>{{ $user->referral_code }}</code>
                        </div>
                    </div>
                </div>
                @endif
                @if($user->last_login_at)
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Last Login</label>
                        <div>
                            <span class="text-muted">{{ $user->last_login_at->format('F d, Y g:i A') }}</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection

