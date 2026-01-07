@extends('dashboard.layouts.main')

@section('title', 'Blog Categories - Admin Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Blog Categories</h3>
        @if(Auth::user()->isAdmin() || Auth::user()->hasPermission('manage_settings'))
        <a href="{{ route('dashboard.admin.blog-categories.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Category
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h4 class="card-title">All Categories</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Blogs Count</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>
                                    <strong>{{ $category->name }}</strong>
                                </td>
                                <td>
                                    {{ \Illuminate\Support\Str::limit($category->description ?? 'N/A', 50) }}
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $category->blogs_count }}</span>
                                </td>
                                <td>
                                    @if($category->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $category->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('dashboard.admin.blog-categories.show', $category) }}" class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(Auth::user()->isAdmin() || Auth::user()->hasPermission('manage_settings'))
                                        <a href="{{ route('dashboard.admin.blog-categories.edit', $category) }}" class="btn btn-sm btn-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('dashboard.admin.blog-categories.destroy', $category) }}" method="POST" class="action-form d-inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
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
                                <td colspan="6" class="text-center">No categories found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
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
