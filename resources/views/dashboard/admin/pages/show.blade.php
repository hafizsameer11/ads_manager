@extends('dashboard.layouts.main')

@section('title', $page->title . ' - Admin Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Page Details</h3>
        <div class="action-buttons">
            <a href="{{ route('website.page.show', $page->slug) }}" class="btn btn-info" target="_blank">
                <i class="fas fa-external-link-alt"></i> View Public Page
            </a>
            <a href="{{ route('dashboard.admin.pages.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Pages
            </a>
            @if(Auth::user()->hasPermission('manage_settings'))
            <a href="{{ route('dashboard.admin.pages.edit', $page) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endif
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h4 class="card-title">{{ $page->title }}</h4>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Slug:</strong> <code>{{ $page->slug }}</code>
                </div>
                <div class="col-md-6">
                    <strong>Status:</strong>
                    @if($page->is_active)
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-secondary">Inactive</span>
                    @endif
                    @if($page->is_published)
                        <span class="badge badge-primary">Published</span>
                    @else
                        <span class="badge badge-warning">Draft</span>
                    @endif
                </div>
            </div>

            @if($page->meta_description)
            <div class="mb-3">
                <strong>Meta Description:</strong>
                <p class="text-muted">{{ $page->meta_description }}</p>
            </div>
            @endif

            @if($page->meta_keywords)
            <div class="mb-3">
                <strong>Meta Keywords:</strong>
                <p class="text-muted">{{ $page->meta_keywords }}</p>
            </div>
            @endif

            <div class="mb-3">
                <strong>Content:</strong>
                <div class="border rounded p-3 bg-light" style="max-height: 500px; overflow-y: auto;">
                    {!! $page->content !!}
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <strong>Created By:</strong> {{ $page->creator->name ?? 'N/A' }}<br>
                    <strong>Created At:</strong> {{ $page->created_at->format('M d, Y h:i A') }}
                </div>
                <div class="col-md-6">
                    <strong>Last Updated By:</strong> {{ $page->updater->name ?? 'N/A' }}<br>
                    <strong>Updated At:</strong> {{ $page->updated_at->format('M d, Y h:i A') }}
                </div>
            </div>
        </div>
    </div>

    @if(Auth::user()->hasPermission('manage_settings'))
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Danger Zone</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('dashboard.admin.pages.destroy', $page) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this page? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete Page
                </button>
            </form>
        </div>
    </div>
    @endif
@endsection

