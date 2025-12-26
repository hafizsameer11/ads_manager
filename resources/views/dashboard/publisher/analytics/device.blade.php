@extends('dashboard.layouts.main')

@section('title', 'Device Analytics - Publisher Dashboard')

@section('content')
    <div class="page-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1>Device Analytics</h1>
                <p class="text-muted">View earnings performance by device, OS, or browser.</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('dashboard.publisher.statistics') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Statistics
                </a>
                <a href="{{ route('dashboard.publisher.analytics.geo') }}" class="btn btn-primary">
                    <i class="fas fa-globe"></i> Geo Analytics
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.publisher.analytics.device') }}">
                <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end; margin-bottom: 10px;">
                    <div style="flex: 0 0 auto; min-width: 150px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">Group By</label>
                        <select name="group_by" class="form-control">
                            <option value="device" {{ $groupBy == 'device' ? 'selected' : '' }}>Device Type</option>
                            <option value="os" {{ $groupBy == 'os' ? 'selected' : '' }}>Operating System</option>
                            <option value="browser" {{ $groupBy == 'browser' ? 'selected' : '' }}>Browser</option>
                        </select>
                    </div>
                    <div style="flex: 0 0 auto; min-width: 200px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">Website</label>
                        <select name="website_id" class="form-control" id="website_filter">
                            <option value="">All Websites</option>
                            @foreach($websites as $website)
                                <option value="{{ $website->id }}" {{ $websiteId == $website->id ? 'selected' : '' }}>{{ $website->name }} ({{ $website->domain }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="flex: 0 0 auto; min-width: 200px;">
                        <label class="form-label" style="margin-bottom: 5px; display: block;">Ad Unit</label>
                        <select name="ad_unit_id" class="form-control" id="ad_unit_filter">
                            <option value="">All Ad Units</option>
                            @foreach($adUnits as $adUnit)
                                <option value="{{ $adUnit->id }}" {{ $adUnitId == $adUnit->id ? 'selected' : '' }} data-website-id="{{ $adUnit->website_id }}">{{ $adUnit->name }}</option>
                            @endforeach
                        </select>
                    </div>
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
                    <a href="{{ route('dashboard.publisher.analytics.device') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Device Analytics Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Earnings Performance by {{ ucfirst($groupBy === 'device' ? 'Device Type' : ($groupBy === 'os' ? 'Operating System' : 'Browser')) }}</h3>
        </div>
        <div class="card-body">
            @if($deviceStats->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>{{ ucfirst($groupBy === 'device' ? 'Device Type' : ($groupBy === 'os' ? 'OS' : 'Browser')) }}</th>
                                <th>Impressions</th>
                                <th>Clicks</th>
                                <th>CTR (%)</th>
                                <th>Total Earnings</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($deviceStats as $stat)
                            <tr>
                                <td><strong>{{ ucfirst($stat['group_value']) }}</strong></td>
                                <td>{{ number_format($stat['impressions']) }}</td>
                                <td>{{ number_format($stat['clicks']) }}</td>
                                <td>{{ number_format($stat['ctr'], 2) }}%</td>
                                <td>${{ number_format($stat['earnings'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center py-4">No analytics data available for the selected filters.</p>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Filter ad units based on selected website
    document.getElementById('website_filter').addEventListener('change', function() {
        const websiteId = this.value;
        const adUnitSelect = document.getElementById('ad_unit_filter');
        const options = adUnitSelect.getElementsByTagName('option');
        
        for (let i = 1; i < options.length; i++) {
            const option = options[i];
            const optionWebsiteId = option.getAttribute('data-website-id');
            
            if (!websiteId || optionWebsiteId == websiteId) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
                if (option.selected) {
                    adUnitSelect.value = '';
                }
            }
        }
    });
</script>
@endpush

