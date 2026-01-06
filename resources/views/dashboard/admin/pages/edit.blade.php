@extends('dashboard.layouts.main')

@section('title', 'Edit Page - Admin Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Edit Page</h3>
        <a href="{{ route('dashboard.admin.pages.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Pages
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Page Details</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.admin.pages.update', $page) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="slug">Slug <span class="text-danger">*</span></label>
                    <input type="text" id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror" 
                           value="{{ old('slug', $page->slug) }}" placeholder="e.g., terms-of-service" required>
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">URL-friendly identifier (letters, numbers, dashes, and underscores only).</small>
                </div>

                <div class="form-group">
                    <label for="title">Title <span class="text-danger">*</span></label>
                    <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" 
                           value="{{ old('title', $page->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="content">Content <span class="text-danger">*</span></label>
                    <textarea id="content" name="content" class="form-control @error('content') is-invalid @enderror" 
                              rows="12" required>{{ old('content', $page->content) }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">You can use HTML formatting.</small>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="meta_description">Meta Description</label>
                            <textarea id="meta_description" name="meta_description" class="form-control @error('meta_description') is-invalid @enderror" 
                                      rows="3" maxlength="500">{{ old('meta_description', $page->meta_description) }}</textarea>
                            @error('meta_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">SEO meta description (max 500 characters).</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="meta_keywords">Meta Keywords</label>
                            <input type="text" id="meta_keywords" name="meta_keywords" class="form-control @error('meta_keywords') is-invalid @enderror" 
                                   value="{{ old('meta_keywords', $page->meta_keywords) }}" placeholder="keyword1, keyword2, keyword3">
                            @error('meta_keywords')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Comma-separated keywords for SEO.</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" id="is_active" name="is_active" class="form-check-input" 
                                       {{ old('is_active', $page->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                            <small class="form-text text-muted">Inactive pages won't be displayed.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" id="is_published" name="is_published" class="form-check-input" 
                                       {{ old('is_published', $page->is_published) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_published">
                                    Published
                                </label>
                            </div>
                            <small class="form-text text-muted">Draft pages are saved but not publicly visible.</small>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Page
                    </button>
                    <a href="{{ route('dashboard.admin.pages.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection





