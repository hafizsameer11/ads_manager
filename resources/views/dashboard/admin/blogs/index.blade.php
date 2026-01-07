@extends('dashboard.layouts.main')

@section('title', 'Blog Posts - Admin Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Blog Posts</h3>
        @if(Auth::user()->isAdmin() || Auth::user()->hasPermission('manage_settings'))
        <a href="{{ route('dashboard.admin.blogs.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Blog Post
        </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Filters -->
    <div class="card">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.admin.blogs.index') }}">
                <div class="filter-form-row">
                    <div class="filter-field">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        </select>
                    </div>
                    <div class="filter-field">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-control">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-field filter-field-search">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search by title or slug..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('dashboard.admin.blogs.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Blogs Table -->
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">All Blog Posts</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Featured Image</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th>Published At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($blogs as $blog)
                            <tr>
                                <td>
                                    @if($blog->featured_image)
                                        <img src="{{ $blog->featured_image_url }}" alt="{{ $blog->title }}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px;">
                                    @else
                                        <div style="width: 60px; height: 60px; background: var(--bg-gray); border-radius: 6px; display: flex; align-items: center; justify-content: center; color: var(--text-light);">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $blog->title }}</strong>
                                    <br>
                                    <small class="text-muted">/blog/{{ $blog->slug }}</small>
                                </td>
                                <td>
                                    @if($blog->category)
                                        <span class="badge badge-primary">{{ $blog->category->name }}</span>
                                    @else
                                        <span class="text-muted">No category</span>
                                    @endif
                                </td>
                                <td>{{ $blog->author->name ?? 'N/A' }}</td>
                                <td>
                                    @if($blog->status === 'published')
                                        <span class="badge badge-success">Published</span>
                                    @else
                                        <span class="badge badge-warning">Draft</span>
                                    @endif
                                </td>
                                <td>{{ number_format($blog->views_count) }}</td>
                                <td>
                                    @if($blog->published_at)
                                        {{ $blog->published_at->format('M d, Y') }}
                                    @else
                                        <span class="text-muted">Not published</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('blog.show', $blog->slug) }}" class="btn btn-sm btn-info" title="View Public" target="_blank">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                        <a href="{{ route('dashboard.admin.blogs.show', $blog) }}" class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(Auth::user()->isAdmin() || Auth::user()->hasPermission('manage_settings'))
                                        <a href="{{ route('dashboard.admin.blogs.edit', $blog) }}" class="btn btn-sm btn-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('dashboard.admin.blogs.toggle-status', $blog) }}" method="POST" class="action-form d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-{{ $blog->status === 'published' ? 'warning' : 'success' }}" title="Toggle Status">
                                                <i class="fas fa-{{ $blog->status === 'published' ? 'eye-slash' : 'eye' }}"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('dashboard.admin.blogs.destroy', $blog) }}" method="POST" class="action-form d-inline" onsubmit="return confirm('Are you sure you want to delete this blog post?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No blog posts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $blogs->links() }}
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .filter-form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: flex-end;
        margin-bottom: 16px;
    }

    .filter-field {
        flex: 0 0 auto;
        min-width: 140px;
        max-width: 100%;
    }

    .filter-field-search {
        flex: 1 1 auto;
        min-width: 200px;
    }

    .filter-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }

    .form-label {
        display: block;
        margin-bottom: 6px;
        font-weight: 500;
        font-size: 13px;
        color: var(--text-primary);
    }

    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        font-size: 14px;
        transition: var(--transition);
        background-color: var(--bg-primary);
        color: var(--text-primary);
    }

    .form-control:focus {
        outline: none;
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(255, 102, 0, 0.1);
    }

    .action-buttons {
        display: flex;
        gap: 5px;
        flex-wrap: wrap;
    }

    .action-form {
        display: inline-block;
    }
</style>
@endpush
