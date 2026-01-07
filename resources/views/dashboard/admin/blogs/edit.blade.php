@extends('dashboard.layouts.main')

@section('title', 'Edit Blog Post - Admin Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Edit Blog Post</h3>
        <a href="{{ route('dashboard.admin.blogs.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Blog Posts
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
            <h4 class="card-title">Blog Post Details</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.admin.blogs.update', $blog) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="title">Title <span class="text-danger">*</span></label>
                    <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" 
                           value="{{ old('title', $blog->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="slug">Slug</label>
                    <input type="text" id="slug" name="slug" class="form-control @error('slug') is-invalid @enderror" 
                           value="{{ old('slug', $blog->slug) }}" placeholder="Auto-generated from title">
                    @error('slug')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Leave empty to auto-generate from title.</small>
                </div>

                <div class="form-group">
                    <label for="featured_image">Featured Image</label>
                    @if($blog->featured_image)
                        <div style="margin-bottom: 15px;">
                            <img src="{{ $blog->featured_image_url }}" alt="Current featured image" style="max-width: 300px; max-height: 200px; border-radius: 8px; border: 1px solid var(--border-color);">
                            <p class="text-muted" style="margin-top: 10px; font-size: 13px;">Current featured image</p>
                        </div>
                    @endif
                    <input type="file" id="featured_image" name="featured_image" class="form-control-file @error('featured_image') is-invalid @enderror" 
                           accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                    @error('featured_image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Leave empty to keep current image. Recommended size: 1200x630px. Max size: 5MB.</small>
                    <div id="imagePreview" style="margin-top: 15px; display: none;">
                        <img src="" alt="Preview" style="max-width: 300px; max-height: 200px; border-radius: 8px; border: 1px solid var(--border-color);">
                        <p class="text-muted" style="margin-top: 10px; font-size: 13px;">New image preview</p>
                    </div>
                </div>

                <div class="form-group">
                    <label for="short_description">Short Description</label>
                    <textarea id="short_description" name="short_description" class="form-control @error('short_description') is-invalid @enderror" 
                              rows="3" maxlength="500">{{ old('short_description', $blog->short_description) }}</textarea>
                    @error('short_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Brief summary shown on listing pages (max 500 characters).</small>
                </div>

                <div class="form-group">
                    <label for="content">Content <span class="text-danger">*</span></label>
                    <textarea id="content" name="content" class="form-control @error('content') is-invalid @enderror" 
                              rows="15" required>{{ old('content', $blog->content) }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">You can use HTML formatting.</small>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id" class="form-control @error('category_id') is-invalid @enderror">
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $blog->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="tags">Tags</label>
                            <input type="text" id="tags" name="tags" class="form-control @error('tags') is-invalid @enderror" 
                                   value="{{ old('tags', $blog->tags->pluck('name')->join(', ')) }}" placeholder="tag1, tag2, tag3">
                            @error('tags')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Comma-separated list of tags.</small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Status <span class="text-danger">*</span></label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="status_draft" value="draft" {{ old('status', $blog->status) === 'draft' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="status_draft">Draft</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="status_published" value="published" {{ old('status', $blog->status) === 'published' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="status_published">Published</label>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="meta_title">Meta Title (SEO)</label>
                            <input type="text" id="meta_title" name="meta_title" class="form-control @error('meta_title') is-invalid @enderror" 
                                   value="{{ old('meta_title', $blog->meta_title) }}" maxlength="255">
                            @error('meta_title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">SEO meta title (defaults to blog title if empty).</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="meta_description">Meta Description (SEO)</label>
                            <textarea id="meta_description" name="meta_description" class="form-control @error('meta_description') is-invalid @enderror" 
                                      rows="3" maxlength="500">{{ old('meta_description', $blog->meta_description) }}</textarea>
                            @error('meta_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">SEO meta description (max 500 characters).</small>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Blog Post
                    </button>
                    <a href="{{ route('dashboard.admin.blogs.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Image preview
    document.getElementById('featured_image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('imagePreview');
                preview.style.display = 'block';
                preview.querySelector('img').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endpush
