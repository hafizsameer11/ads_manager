@extends('dashboard.layouts.main')

@section('title', $announcement->title . ' - Admin Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Announcement Details</h3>
        <div class="action-buttons">
            <a href="{{ route('dashboard.admin.announcements.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Announcements
            </a>
            @if(Auth::user()->hasPermission('manage_settings'))
            <a href="{{ route('dashboard.admin.announcements.edit', $announcement) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h4 class="card-title">{{ $announcement->title }}</h4>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <strong>Type:</strong>
                @if($announcement->type == 'info')
                    <span class="badge badge-info">Info</span>
                @elseif($announcement->type == 'success')
                    <span class="badge badge-success">Success</span>
                @elseif($announcement->type == 'warning')
                    <span class="badge badge-warning">Warning</span>
                @elseif($announcement->type == 'danger')
                    <span class="badge badge-danger">Danger</span>
                @endif
            </div>

            <div class="mb-3">
                <strong>Target Audience:</strong> {{ ucfirst($announcement->target_audience) }}
            </div>

            <div class="mb-3">
                <strong>Status:</strong>
                @if($announcement->is_active)
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-secondary">Inactive</span>
                @endif
            </div>

            @if($announcement->starts_at)
                <div class="mb-3">
                    <strong>Starts At:</strong> {{ $announcement->starts_at->format('M d, Y H:i A') }}
                </div>
            @endif

            @if($announcement->ends_at)
                <div class="mb-3">
                    <strong>Ends At:</strong> {{ $announcement->ends_at->format('M d, Y H:i A') }}
                </div>
            @endif

            <div class="mb-3">
                <strong>Created By:</strong> {{ $announcement->creator->name ?? 'N/A' }}
            </div>

            <div class="mb-3">
                <strong>Created At:</strong> {{ $announcement->created_at->format('M d, Y H:i A') }}
            </div>

            <hr>

            <div>
                <strong>Content:</strong>
                <div class="mt-2 p-3 bg-light rounded">
                    {!! nl2br(e($announcement->content)) !!}
                </div>
            </div>
        </div>
    </div>
@endsection

