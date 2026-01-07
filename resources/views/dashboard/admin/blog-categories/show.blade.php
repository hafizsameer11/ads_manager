@extends('dashboard.layouts.main')

@section('title', 'View Blog Category - Admin Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Category Details</h3>
        <div>
            @if(Auth::user()->isAdmin() || Auth::user()->hasPermission('manage_settings'))
            <a href="{{ route('dashboard.admin.blog-categories.edit', $blogCategory) }}" class="btn btn-secondary">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endif
            <a href="{{ route('dashboard.admin.blog-categories.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ $blogCategory->name }}</h4>
                </div>
                <div class="card-body">
                    @if($blogCategory->description)
                        <div style="margin-bottom: 20px;">
                            <strong>Description:</strong>
                            <p style="margin-top: 5px; color: var(--text-secondary);">{{ $blogCategory->description }}</p>
                        </div>
                    @endif

                    <div style="margin-bottom: 20px;">
                        <strong>Status:</strong>
                        <p style="margin-top: 5px;">
                            @if($blogCategory->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </p>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <strong>Blogs Count:</strong>
                        <p style="margin-top: 5px;">
                            <span class="badge badge-info">{{ $blogCategory->blogs_count }}</span>
                        </p>
                    </div>
                </div>
            </div>

            @if($blogCategory->blogs_count > 0)
                <div class="card" style="margin-top: 20px;">
                    <div class="card-header">
                        <h4 class="card-title">Blogs in this Category</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Status</th>
                                        <th>Published At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($blogCategory->blogs()->latest()->limit(10)->get() as $blog)
                                        <tr>
                                            <td>{{ $blog->title }}</td>
                                            <td>
                                                @if($blog->status === 'published')
                                                    <span class="badge badge-success">Published</span>
                                                @else
                                                    <span class="badge badge-warning">Draft</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($blog->published_at)
                                                    {{ $blog->published_at->format('M d, Y') }}
                                                @else
                                                    <span class="text-muted">Not published</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('dashboard.admin.blogs.show', $blog) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($blogCategory->blogs_count > 10)
                            <div class="text-center" style="margin-top: 15px;">
                                <a href="{{ route('dashboard.admin.blogs.index', ['category_id' => $blogCategory->id]) }}" class="btn btn-secondary">
                                    View All ({{ $blogCategory->blogs_count }})
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Category Information</h4>
                </div>
                <div class="card-body">
                    <div style="margin-bottom: 15px;">
                        <strong>Created At:</strong>
                        <p style="margin-top: 5px;">{{ $blogCategory->created_at->format('F d, Y h:i A') }}</p>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <strong>Updated At:</strong>
                        <p style="margin-top: 5px;">{{ $blogCategory->updated_at->format('F d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
