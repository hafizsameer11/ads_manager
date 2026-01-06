@extends('dashboard.layouts.main')

@section('title', 'Activity Log Details - Admin Dashboard')

@section('content')
    <div style="display: flex; justify-content: flex-end; gap: 10px; margin-bottom: 20px;">
        <a href="{{ route('dashboard.admin.activity-logs') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Activity Logs
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Activity Log Details</h3>
        </div>
        <div class="card-body">
            <table class="table table-borderless">
                <tr>
                    <th width="200">ID:</th>
                    <td>#{{ $log->id }}</td>
                </tr>
                <tr>
                    <th>Action:</th>
                    <td><span class="badge badge-info">{{ $log->action }}</span></td>
                </tr>
                <tr>
                    <th>Description:</th>
                    <td>{{ $log->description }}</td>
                </tr>
                <tr>
                    <th>User:</th>
                    <td>
                        @if($log->user)
                            <div><strong>{{ $log->user->name }}</strong></div>
                            <small class="text-muted">{{ $log->user->email }}</small>
                        @else
                            <span class="text-muted">System / Unknown</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>IP Address:</th>
                    <td><code>{{ $log->ip_address ?? 'N/A' }}</code></td>
                </tr>
                <tr>
                    <th>User Agent:</th>
                    <td><small>{{ $log->user_agent ?? 'N/A' }}</small></td>
                </tr>
                <tr>
                    <th>Request Method:</th>
                    <td><span class="badge badge-info">{{ $log->request_method ?? 'N/A' }}</span></td>
                </tr>
                <tr>
                    <th>Request URL:</th>
                    <td><small><a href="{{ $log->request_url }}" target="_blank">{{ $log->request_url }}</a></small></td>
                </tr>
                <tr>
                    <th>Subject:</th>
                    <td>
                        @if($log->subject_type && $log->subject_id)
                            <div><strong>Type:</strong> {{ class_basename($log->subject_type) }}</div>
                            <div><strong>ID:</strong> #{{ $log->subject_id }}</div>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Properties:</th>
                    <td>
                        @if($log->properties && count($log->properties) > 0)
                            <pre style="background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 12px;">{{ json_encode($log->properties, JSON_PRETTY_PRINT) }}</pre>
                        @else
                            <span class="text-muted">No additional properties</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Created At:</th>
                    <td>{{ $log->created_at->format('M d, Y H:i:s') }}</td>
                </tr>
            </table>
        </div>
    </div>
@endsection





