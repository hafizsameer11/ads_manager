@extends('dashboard.layouts.main')

@section('title', 'User Details - Admin Dashboard')

@section('content')
    <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
        <a href="{{ route('dashboard.admin.users') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Users
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

    <div class="row">
        <!-- User Information -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                        <h3 class="card-title" style="margin: 0;">
                            @if($user->isPublisher())
                                Publisher Information
                            @elseif($user->isAdvertiser())
                                Advertiser Information
                            @elseif($user->role === 'sub-admin' || $user->hasRole('sub-admin'))
                                Sub-Admin Information
                            @else
                                User Information
                            @endif
                        </h3>
                        @if(($user->isPublisher() && $user->publisher) || ($user->isAdvertiser() && $user->advertiser) || ($user->role === 'sub-admin' || $user->hasRole('sub-admin')))
                             <div style="display: flex; gap: 5px;">
                                 <a href="{{ route('dashboard.admin.users.edit', $user->id) }}" class="btn btn-primary">
                                     <i class="fas fa-edit"></i> Edit
                                 </a>
                                 @if($user->is_active == 1)
                                     <button type="button" class="btn btn-warning" onclick="showSuspendModal()">
                                         <i class="fas fa-ban"></i> Suspend
                                     </button>
                                 @endif
                                 @if($user->is_active != 0 && $user->is_active != 3)
                                     <button type="button" class="btn btn-danger" onclick="showBlockModal()">
                                         <i class="fas fa-times-circle"></i> Block
                                     </button>
                                 @endif
                                 {{-- @if($user->is_active == 1)
                                     <button type="button" class="btn btn-warning" onclick="showSuspendModal()">
                                         <i class="fas fa-ban"></i> Suspend
                                     </button>
                                 @endif
                                 @if($user->is_active != 0 && $user->is_active != 3)
                                     <button type="button" class="btn btn-danger" onclick="showBlockModal()">
                                         <i class="fas fa-times-circle"></i> Block
                                     </button>
                                 @endif --}}
                             </div>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th width="200">Name:</th>
                            <td><strong>{{ $user->name }}</strong></td>
                        </tr>
                        <tr>
                            <th>Email:</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Username:</th>
                            <td>{{ $user->username ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Phone:</th>
                            <td>{{ $user->phone ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Role:</th>
                            <td>
                                @if($user->hasRole('admin') && !$user->hasRole('sub-admin'))
                                    <span class="badge badge-danger">Admin</span>
                                @elseif($user->hasRole('sub-admin') || $user->role === 'sub-admin')
                                    <span class="badge badge-warning">Sub-Admin</span>
                                    @if($user->roles->count() > 0)
                                        <br><small class="text-muted">Assigned Role: 
                                            @foreach($user->roles as $role)
                                                {{ $role->name }}
                                            @endforeach
                                        </small>
                                    @endif
                                @else
                                    <span class="badge badge-primary">{{ ucfirst($user->role) }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Account Status:</th>
                            <td>
                                @if($user->is_active == 1)
                                    <span class="badge badge-success">Approved</span>
                                @elseif($user->is_active == 2)
                                    <span class="badge badge-warning">Pending</span>
                                @elseif($user->is_active == 3)
                                    <span class="badge badge-warning">Suspended</span>
                                @else
                                    <span class="badge badge-danger">Rejected</span>
                                @endif
                            </td>
                        </tr>
                        @if($user->isPublisher() && $user->publisher)
                            <tr>
                                <th>Tier:</th>
                                <td>
                                    <span class="badge badge-info">{{ strtoupper($user->publisher->tier) }}</span>
                                    @if($user->publisher->is_premium)
                                        <span class="badge badge-warning">Premium</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Balance:</th>
                                <td><strong>${{ number_format($user->publisher->balance, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <th>Total Earnings:</th>
                                <td><strong>${{ number_format($user->publisher->total_earnings, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <th>Pending Balance:</th>
                                <td>${{ number_format($user->publisher->pending_balance, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Paid Balance:</th>
                                <td>${{ number_format($user->publisher->paid_balance, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Minimum Payout:</th>
                                <td>${{ number_format($user->publisher->minimum_payout, 2) }}</td>
                            </tr>
                            @if($publisherStats)
                                <tr>
                                    <th>Websites:</th>
                                    <td>{{ $publisherStats['websites_count'] }}</td>
                                </tr>
                            @endif
                            @if($user->publisher->notes)
                                <tr>
                                    <th>Notes:</th>
                                    <td>{{ $user->publisher->notes }}</td>
                                </tr>
                            @endif
                            <tr>
                                <th>Approved At:</th>
                                <td>{{ $user->publisher->approved_at ? $user->publisher->approved_at->format('M d, Y H:i') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Joined:</th>
                                <td>{{ $user->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            @if($user->referral_code)
                                <tr>
                                    <th>Referral Code:</th>
                                    <td><code>{{ $user->referral_code }}</code></td>
                                </tr>
                            @endif
                        @elseif($user->isAdvertiser() && $user->advertiser)
                            <tr>
                                <th>Balance:</th>
                                <td><strong>${{ number_format($user->advertiser->balance, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <th>Total Spent:</th>
                                <td><strong>${{ number_format($user->advertiser->total_spent, 2) }}</strong></td>
                            </tr>
                            @if($advertiserStats)
                                <tr>
                                    <th>Campaigns:</th>
                                    <td>{{ $advertiserStats['campaigns_count'] }}</td>
                                </tr>
                            @endif
                            @if($user->advertiser->payment_email)
                                <tr>
                                    <th>Payment Email:</th>
                                    <td>{{ $user->advertiser->payment_email }}</td>
                                </tr>
                            @endif
                            @if($user->advertiser->payment_info)
                                <tr>
                                    <th>Payment Info:</th>
                                    <td>
                                        @php
                                            $paymentInfo = is_string($user->advertiser->payment_info)
                                                ? json_decode($user->advertiser->payment_info, true)
                                                : $user->advertiser->payment_info;
                                        @endphp
                                        @if(is_array($paymentInfo))
                                            <pre style="background: #f5f5f5; padding: 10px; border-radius: 4px; font-size: 12px;">{{ json_encode($paymentInfo, JSON_PRETTY_PRINT) }}</pre>
                                        @else
                                            {{ $user->advertiser->payment_info }}
                                        @endif
                                    </td>
                                </tr>
                            @endif
                            @if($user->advertiser->notes)
                                <tr>
                                    <th>Notes:</th>
                                    <td>{{ $user->advertiser->notes }}</td>
                                </tr>
                            @endif
                            <tr>
                                <th>Approved At:</th>
                                <td>{{ $user->advertiser->approved_at ? $user->advertiser->approved_at->format('M d, Y H:i') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Joined:</th>
                                <td>{{ $user->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            @if($user->referral_code)
                                <tr>
                                    <th>Referral Code:</th>
                                    <td><code>{{ $user->referral_code }}</code></td>
                                </tr>
                            @endif
                        @endif
                    </table>

                    @if($user->isPublisher())
                        <div class="mt-3">
                            <a href="{{ route('dashboard.admin.users.referrals', $user->id) }}" class="btn btn-info">
                                <i class="fas fa-users"></i> View Referrals
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stats Card -->
        @if($user->isPublisher() && $publisherStats)
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Statistics</h3>
                </div>
                <div class="card-body">
                    <div class="stat-item" style="margin-bottom: 20px;">
                        <div class="stat-label" style="font-size: 12px; color: #666; margin-bottom: 5px;">Websites</div>
                        <div class="stat-value" style="font-size: 24px; font-weight: bold;">{{ $publisherStats['websites_count'] }}</div>
                    </div>
                    <div class="stat-item" style="margin-bottom: 20px;">
                        <div class="stat-label" style="font-size: 12px; color: #666; margin-bottom: 5px;">Total Earnings</div>
                        <div class="stat-value" style="font-size: 24px; font-weight: bold; color: #28a745;">${{ number_format($publisherStats['total_earnings'], 2) }}</div>
                    </div>
                    <div class="stat-item" style="margin-bottom: 20px;">
                        <div class="stat-label" style="font-size: 12px; color: #666; margin-bottom: 5px;">Current Balance</div>
                        <div class="stat-value" style="font-size: 24px; font-weight: bold; color: #007bff;">${{ number_format($publisherStats['balance'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        @elseif($user->isAdvertiser() && $advertiserStats)
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Statistics</h3>
                </div>
                <div class="card-body">
                    <div class="stat-item" style="margin-bottom: 20px;">
                        <div class="stat-label" style="font-size: 12px; color: #666; margin-bottom: 5px;">Campaigns</div>
                        <div class="stat-value" style="font-size: 24px; font-weight: bold;">{{ $advertiserStats['campaigns_count'] }}</div>
                    </div>
                    <div class="stat-item" style="margin-bottom: 20px;">
                        <div class="stat-label" style="font-size: 12px; color: #666; margin-bottom: 5px;">Total Spent</div>
                        <div class="stat-value" style="font-size: 24px; font-weight: bold; color: #dc3545;">${{ number_format($advertiserStats['total_spent'], 2) }}</div>
                    </div>
                    <div class="stat-item" style="margin-bottom: 20px;">
                        <div class="stat-label" style="font-size: 12px; color: #666; margin-bottom: 5px;">Current Balance</div>
                        <div class="stat-value" style="font-size: 24px; font-weight: bold; color: #007bff;">${{ number_format($advertiserStats['balance'], 2) }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Suspend Modal -->
    <div class="modal fade" id="suspendModal" tabindex="-1" role="dialog" aria-labelledby="suspendModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 8px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                <div class="modal-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); border-bottom: none; padding: 20px; border-radius: 8px 8px 0 0;">
                    <h5 class="modal-title" id="suspendModalLabel" style="color: white; font-weight: 600; font-size: 18px; margin: 0;">
                        <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>
                        @if($user->isPublisher())
                            Suspend Publisher
                        @elseif($user->isAdvertiser())
                            Suspend Advertiser
                        @elseif($user->role === 'sub-admin' || $user->hasRole('sub-admin'))
                            Suspend Sub-Admin
                        @else
                            Suspend User
                        @endif
                    </h5>
                </div>
                <form action="{{ route('dashboard.admin.users.suspend', $user->id) }}" method="POST">
                    @csrf
                    <div class="modal-body" style="padding: 25px;">
                        <p style="margin-bottom: 20px; color: #495057; font-size: 14px;">
                            Are you sure you want to suspend <strong>{{ $user->name }}</strong>? The
                            @if($user->isPublisher())
                                publisher
                            @elseif($user->isAdvertiser())
                                advertiser
                            @elseif($user->role === 'sub-admin' || $user->hasRole('sub-admin'))
                                sub-admin
                            @else
                                user
                            @endif
                            will be temporarily suspended and will not be able to use the platform.
                        </p>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="suspend_reason" style="font-weight: 600; color: #495057; margin-bottom: 8px; display: block;">
                                Reason <span style="color: #6c757d; font-weight: 400;">(Optional)</span>
                            </label>
                            <textarea name="reason" id="suspend_reason" class="form-control" rows="4"
                                      placeholder="Enter reason for suspension..."
                                      style="border-radius: 6px; border: 1px solid #dee2e6; padding: 12px; font-size: 14px; resize: vertical; transition: border-color 0.3s;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 20px 25px; background: #f8f9fa; border-radius: 0 0 8px 8px;">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                                style="padding: 10px 20px; border-radius: 6px; font-weight: 500; border: 1px solid #dee2e6;">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-warning"
                                style="padding: 10px 20px; border-radius: 6px; font-weight: 600; background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); border: none; color: white; box-shadow: 0 2px 4px rgba(255,193,7,0.3);">
                            <i class="fas fa-ban" style="margin-right: 6px;"></i>
                            @if($user->isPublisher())
                                Suspend Publisher
                            @elseif($user->isAdvertiser())
                                Suspend Advertiser
                            @elseif($user->role === 'sub-admin' || $user->hasRole('sub-admin'))
                                Suspend Sub-Admin
                            @else
                                Suspend User
                            @endif
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Block Modal -->
    <div class="modal fade" id="blockModal" tabindex="-1" role="dialog" aria-labelledby="blockModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="margin-top:25px; border-radius: 8px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                <div class="modal-header" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border-bottom: none; padding: 20px; border-radius: 8px 8px 0 0;">
                    <h5 class="modal-title" id="blockModalLabel" style="color: white; font-weight: 600; font-size: 18px; margin: 0;">
                        <i class="fas fa-times-circle" style="margin-right: 8px;"></i>
                        @if($user->isPublisher())
                            Block Publisher
                        @elseif($user->isAdvertiser())
                            Block Advertiser
                        @elseif($user->role === 'sub-admin' || $user->hasRole('sub-admin'))
                            Block Sub-Admin
                        @else
                            Block User
                        @endif
                    </h5>
                </div>
                <form action="{{ route('dashboard.admin.users.block', $user->id) }}" method="POST">
                    @csrf
                    <div class="modal-body" style="padding: 25px;">
                        <div class="alert alert-warning" style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 6px; padding: 15px; margin-bottom: 20px; border-left: 4px solid #ffc107;">
                            <strong style="color: #856404; display: flex; align-items: center;">
                                <i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i>Warning!
                            </strong>
                            <p style="color: #856404; margin: 8px 0 0 0; font-size: 14px;">
                                This will block the
                                @if($user->isPublisher())
                                    publisher
                                @elseif($user->isAdvertiser())
                                    advertiser
                                @elseif($user->role === 'sub-admin' || $user->hasRole('sub-admin'))
                                    sub-admin
                                @else
                                    user
                                @endif
                                and set their account status to rejected. This action cannot be easily undone.
                            </p>
                        </div>
                        <p style="margin-bottom: 20px; color: #495057; font-size: 14px;">
                            Are you sure you want to block <strong>{{ $user->name }}</strong>? The
                            @if($user->isPublisher())
                                publisher
                            @elseif($user->isAdvertiser())
                                advertiser
                            @elseif($user->role === 'sub-admin' || $user->hasRole('sub-admin'))
                                sub-admin
                            @else
                                user
                            @endif
                            will be permanently blocked from using the platform.
                        </p>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="block_reason" style="font-weight: 600; color: #495057; margin-bottom: 8px; display: block;">
                                Reason <span style="color: #6c757d; font-weight: 400;">(Optional)</span>
                            </label>
                            <textarea name="reason" id="block_reason" class="form-control" rows="4"
                                      placeholder="Enter reason for blocking..."
                                      style="border-radius: 6px; border: 1px solid #dee2e6; padding: 12px; font-size: 14px; resize: vertical; transition: border-color 0.3s;"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 20px 25px; background: #f8f9fa; border-radius: 0 0 8px 8px;">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"
                                style="padding: 10px 20px; border-radius: 6px; font-weight: 500; border: 1px solid #dee2e6;">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-danger"
                                style="padding: 10px 20px; border-radius: 6px; font-weight: 600; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border: none; color: white; box-shadow: 0 2px 4px rgba(220,53,69,0.3);">
                            <i class="fas fa-times-circle" style="margin-right: 6px;"></i>
                            @if($user->isPublisher())
                                Block Publisher
                            @elseif($user->isAdvertiser())
                                Block Advertiser
                            @elseif($user->role === 'sub-admin' || $user->hasRole('sub-admin'))
                                Block Sub-Admin
                            @else
                                Block User
                            @endif
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function showSuspendModal() {
        $('#suspendModal').modal('show');
    }

    function showBlockModal() {
        $('#blockModal').modal('show');
    }
</script>
@endpush

