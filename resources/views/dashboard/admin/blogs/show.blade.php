@extends('dashboard.layouts.main')

@section('title', 'View Blog Post - Admin Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Blog Post Details</h3>
        <div>
            <a href="{{ route('blog.show', $blog->slug) }}" class="btn btn-info" target="_blank">
                <i class="fas fa-external-link-alt"></i> View Public Page
            </a>
            @if(Auth::user()->isAdmin() || Auth::user()->hasPermission('manage_settings'))
            <a href="{{ route('dashboard.admin.blogs.edit', $blog) }}" class="btn btn-secondary">
                <i class="fas fa-edit"></i> Edit
            </a>
            @endif
            <a href="{{ route('dashboard.admin.blogs.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">{{ $blog->title }}</h4>
                </div>
                <div class="card-body">
                    @if($blog->featured_image)
                        <img src="{{ $blog->featured_image_url }}" alt="{{ $blog->title }}" style="width: 100%; border-radius: 8px; margin-bottom: 20px;">
                    @endif

                    <div style="margin-bottom: 20px;">
                        <strong>Status:</strong>
                        @if($blog->status === 'published')
                            <span class="badge badge-success">Published</span>
                        @else
                            <span class="badge badge-warning">Draft</span>
                        @endif
                    </div>

                    @if($blog->short_description)
                        <div style="margin-bottom: 20px;">
                            <strong>Short Description:</strong>
                            <p style="margin-top: 5px; color: var(--text-secondary);">{{ $blog->short_description }}</p>
                        </div>
                    @endif

                    <div style="margin-bottom: 20px;">
                        <strong>Content:</strong>
                        <div style="margin-top: 15px; padding: 20px; background: var(--bg-secondary); border-radius: 8px;">
                            {!! $blog->content !!}
                        </div>
                    </div>

                    @if($blog->tags->count() > 0)
                        <div style="margin-bottom: 20px;">
                            <strong>Tags:</strong>
                            <div style="margin-top: 10px;">
                                @foreach($blog->tags as $tag)
                                    <span class="badge badge-primary" style="margin-right: 5px;">{{ $tag->name }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Post Information</h4>
                </div>
                <div class="card-body">
                    <div style="margin-bottom: 15px;">
                        <strong>Slug:</strong>
                        <p style="margin-top: 5px;"><code>{{ $blog->slug }}</code></p>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <strong>Category:</strong>
                        <p style="margin-top: 5px;">
                            @if($blog->category)
                                <span class="badge badge-primary">{{ $blog->category->name }}</span>
                            @else
                                <span class="text-muted">No category</span>
                            @endif
                        </p>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <strong>Author:</strong>
                        <p style="margin-top: 5px;">{{ $blog->author->name ?? 'N/A' }}</p>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <strong>Views:</strong>
                        <p style="margin-top: 5px;">{{ number_format($blog->views_count) }}</p>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <strong>Published At:</strong>
                        <p style="margin-top: 5px;">
                            @if($blog->published_at)
                                {{ $blog->published_at->format('F d, Y h:i A') }}
                            @else
                                <span class="text-muted">Not published</span>
                            @endif
                        </p>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <strong>Created At:</strong>
                        <p style="margin-top: 5px;">{{ $blog->created_at->format('F d, Y h:i A') }}</p>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <strong>Updated At:</strong>
                        <p style="margin-top: 5px;">{{ $blog->updated_at->format('F d, Y h:i A') }}</p>
                    </div>
                </div>
            </div>

            @if($blog->meta_title || $blog->meta_description)
                <div class="card" style="margin-top: 20px;">
                    <div class="card-header">
                        <h4 class="card-title">SEO Information</h4>
                    </div>
                    <div class="card-body">
                        @if($blog->meta_title)
                            <div style="margin-bottom: 15px;">
                                <strong>Meta Title:</strong>
                                <p style="margin-top: 5px;">{{ $blog->meta_title }}</p>
                            </div>
                        @endif

                        @if($blog->meta_description)
                            <div style="margin-bottom: 15px;">
                                <strong>Meta Description:</strong>
                                <p style="margin-top: 5px; color: var(--text-secondary);">{{ $blog->meta_description }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
