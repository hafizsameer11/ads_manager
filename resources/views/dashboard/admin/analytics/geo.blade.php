@extends('dashboard.layouts.main')

@section('title', 'Geo Analytics - Admin Dashboard')

@section('content')
    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 20px;">
                <a href="{{ route('dashboard.admin.reports') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Reports
                </a>
                <a href="{{ route('dashboard.admin.analytics.device') }}" class="btn btn-primary">
                    <i class="fas fa-mobile-alt"></i> Device Analytics
                </a>
    </div>

    <!-- Date Range Filter -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.admin.analytics.geo') }}">
                <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; margin-bottom: 10px;">
                    <div style="flex: 0 0 auto; min-width: 200px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}" required>
                    </div>
                    <div style="flex: 0 0 auto; min-width: 200px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}" required>
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="{{ route('dashboard.admin.analytics.geo') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Geo Analytics Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Analytics by Country</h3>
        </div>
        <div class="card-body">
            @if($geoStats->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Country Code</th>
                                <th>Impressions</th>
                                <th>Clicks</th>
                                <th>CTR (%)</th>
                                <th>Total Spend</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($geoStats as $stat)
                            <tr>
                                <td><strong>{{ $stat['country_code'] }}</strong></td>
                                <td>{{ number_format($stat['impressions']) }}</td>
                                <td>{{ number_format($stat['clicks']) }}</td>
                                <td>{{ number_format($stat['ctr'], 2) }}%</td>
                                <td>${{ number_format($stat['spend'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center py-4">No analytics data available for the selected date range.</p>
            @endif
        </div>
    </div>
@endsection

