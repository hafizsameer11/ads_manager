@extends('dashboard.layouts.main')

@section('title', 'Edit Email Template - Admin Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Edit Email Template</h3>
        <a href="{{ route('dashboard.admin.email-templates.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Templates
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
            <h4 class="card-title">Template Details</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.admin.email-templates.update', $emailTemplate->id) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name">Template Name <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name', $emailTemplate->name) }}" placeholder="e.g., user_approved, withdrawal_processed" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Use lowercase with underscores (e.g., user_approved)</small>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <input type="text" id="description" name="description" class="form-control @error('description') is-invalid @enderror" 
                           value="{{ old('description', $emailTemplate->description) }}" placeholder="Brief description of this template">
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="subject">Email Subject <span class="text-danger">*</span></label>
                    <input type="text" id="subject" name="subject" class="form-control @error('subject') is-invalid @enderror" 
                           value="{{ old('subject', $emailTemplate->subject) }}" required>
                    @error('subject')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Use @{{variable_name}} for dynamic content</small>
                </div>

                <div class="form-group">
                    <label for="body_html">HTML Body <span class="text-danger">*</span></label>
                    <textarea id="body_html" name="body_html" class="form-control @error('body_html') is-invalid @enderror" 
                              rows="15" required>{{ old('body_html', $emailTemplate->body_html) }}</textarea>
                    @error('body_html')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Use @{{variable_name}} for dynamic content. HTML is supported.</small>
                </div>

                <div class="form-group">
                    <label for="body_text">Plain Text Body (Optional)</label>
                    <textarea id="body_text" name="body_text" class="form-control @error('body_text') is-invalid @enderror" 
                              rows="10">{{ old('body_text', $emailTemplate->body_text) }}</textarea>
                    @error('body_text')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">If empty, will be auto-generated from HTML body</small>
                </div>

                <div class="form-group">
                    <label>Available Variables (Optional)</label>
                    <div class="p-3 bg-light rounded">
                        <p class="mb-2"><small>Enter variable names (one per line) that can be used in this template:</small></p>
                        <textarea name="variables" class="form-control" rows="5" placeholder="user_name&#10;user_email&#10;amount&#10;date">{{ old('variables', is_array($emailTemplate->variables) ? implode("\n", $emailTemplate->variables) : '') }}</textarea>
                        <small class="form-text text-muted">One variable per line. These will be available as @{{variable_name}} in the template.</small>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" id="is_active" name="is_active" class="form-check-input" 
                               {{ old('is_active', $emailTemplate->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Active (Enable this template)
                        </label>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Template
                    </button>
                    <a href="{{ route('dashboard.admin.email-templates.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

