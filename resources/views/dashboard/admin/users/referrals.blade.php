@extends('dashboard.layouts.main')

@section('title', 'Publisher Referrals - Admin Dashboard')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <a href="{{ route('dashboard.admin.users.show', $user->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Publisher Details
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
        <!-- Referral Statistics -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Referral Statistics for {{ $user->name }}</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-card" style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">
                                <div style="font-size: 12px; color: #666; margin-bottom: 8px;">Total Referrals</div>
                                <div style="font-size: 32px; font-weight: bold; color: #333;">{{ $referralStats['total_referrals'] }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card" style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">
                                <div style="font-size: 12px; color: #666; margin-bottom: 8px;">Total Earnings</div>
                                <div style="font-size: 32px; font-weight: bold; color: #28a745;">${{ number_format($referralStats['total_earnings'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card" style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">
                                <div style="font-size: 12px; color: #666; margin-bottom: 8px;">Paid Earnings</div>
                                <div style="font-size: 32px; font-weight: bold; color: #007bff;">${{ number_format($referralStats['paid_earnings'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card" style="background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;">
                                <div style="font-size: 12px; color: #666; margin-bottom: 8px;">Pending Earnings</div>
                                <div style="font-size: 32px; font-weight: bold; color: #ffc107;">${{ number_format($referralStats['pending_earnings'], 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Referral List</h3>
        </div>
        <div class="card-body">
            @if($referrals->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Referred User</th>
                                <th>Type</th>
                                <th>Commission Rate</th>
                                <th>Total Earnings</th>
                                <th>Paid Earnings</th>
                                <th>Pending Earnings</th>
                                <th>Status</th>
                                <th>Referred Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($referrals as $referral)
                                <tr>
                                    <td>#{{ $referral->id }}</td>
                                    <td>
                                        <strong>{{ $referral->referred->name }}</strong><br>
                                        <small class="text-muted">{{ $referral->referred->email }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ ucfirst($referral->referred_type) }}</span>
                                    </td>
                                    <td>{{ number_format($referral->commission_rate, 2) }}%</td>
                                    <td><strong>${{ number_format($referral->total_earnings, 2) }}</strong></td>
                                    <td>${{ number_format($referral->paid_earnings, 2) }}</td>
                                    <td>${{ number_format($referral->total_earnings - $referral->paid_earnings, 2) }}</td>
                                    <td>
                                        @if($referral->status === 'active')
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $referral->created_at->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $referrals->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No referrals found for this publisher.</p>
                </div>
            @endif
        </div>
    </div>
@endsection

