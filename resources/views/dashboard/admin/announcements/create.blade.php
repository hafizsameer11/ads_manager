@extends('dashboard.layouts.main')

@section('title', 'Create Announcement - Admin Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Create New Announcement</h3>
        <a href="{{ route('dashboard.admin.announcements.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Announcements
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
            <h4 class="card-title">Announcement Details</h4>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.admin.announcements.store') }}">
                @csrf

                <div class="form-group">
                    <label for="title">Title <span class="text-danger">*</span></label>
                    <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror" 
                           value="{{ old('title') }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="content">Content <span class="text-danger">*</span></label>
                    <textarea id="content" name="content" class="form-control @error('content') is-invalid @enderror" 
                              rows="6" required>{{ old('content') }}</textarea>
                    @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">You can use HTML formatting.</small>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="type">Type <span class="text-danger">*</span></label>
                            <select id="type" name="type" class="form-control @error('type') is-invalid @enderror" required>
                                <option value="info" {{ old('type') == 'info' ? 'selected' : '' }}>Info</option>
                                <option value="success" {{ old('type') == 'success' ? 'selected' : '' }}>Success</option>
                                <option value="warning" {{ old('type') == 'warning' ? 'selected' : '' }}>Warning</option>
                                <option value="danger" {{ old('type') == 'danger' ? 'selected' : '' }}>Danger</option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="target_audience">Target Audience <span class="text-danger">*</span></label>
                            <select id="target_audience" name="target_audience" class="form-control @error('target_audience') is-invalid @enderror" required>
                                <option value="all" {{ old('target_audience') == 'all' ? 'selected' : '' }}>All Users</option>
                                <option value="publishers" {{ old('target_audience') == 'publishers' ? 'selected' : '' }}>Publishers Only</option>
                                <option value="advertisers" {{ old('target_audience') == 'advertisers' ? 'selected' : '' }}>Advertisers Only</option>
                                <option value="admins" {{ old('target_audience') == 'admins' ? 'selected' : '' }}>Admins Only</option>
                            </select>
                            @error('target_audience')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="starts_at">Start Date (Optional)</label>
                            <input type="datetime-local" id="starts_at" name="starts_at" 
                                   class="form-control @error('starts_at') is-invalid @enderror" 
                                   value="{{ old('starts_at') }}">
                            @error('starts_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <strong>Leave empty to show immediately.</strong><br>
                                If set, announcement will only appear after this date/time. 
                                Setting a future date will schedule it (marked as "Scheduled").
                            </small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ends_at">End Date (Optional)</label>
                            <input type="datetime-local" id="ends_at" name="ends_at" 
                                   class="form-control @error('ends_at') is-invalid @enderror" 
                                   value="{{ old('ends_at') }}">
                            @error('ends_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <strong>Leave empty to show indefinitely.</strong><br>
                                If set, announcement will stop showing after this date/time.
                                Must be after Start Date if both are set.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" id="is_active" name="is_active" class="form-check-input" 
                               value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Active (Show this announcement)
                        </label>
                    </div>
                </div>

                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Announcement
                    </button>
                    <a href="{{ route('dashboard.admin.announcements.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection

