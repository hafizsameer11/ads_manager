@extends('dashboard.layouts.main')

@section('title', 'Create Support Ticket - Dashboard')

@push('styles')
<style>
    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        font-size: 14px;
        color: var(--text-primary);
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
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

    textarea.form-control {
        min-height: 150px;
        resize: vertical;
    }

    .help-text {
        font-size: 12px;
        color: var(--text-secondary);
        margin-top: 6px;
    }

    .priority-info {
        background: #f8f9fa;
        border-left: 4px solid var(--primary-color);
        padding: 12px;
        margin-bottom: 20px;
        border-radius: 4px;
    }

    .priority-info h6 {
        margin-bottom: 8px;
        font-weight: 600;
    }

    .priority-info ul {
        margin: 0;
        padding-left: 20px;
    }

    .priority-info li {
        margin-bottom: 4px;
        font-size: 13px;
    }
</style>
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Create Support Ticket</h3>
        <a href="{{ route('dashboard.' . (Auth::user()->isPublisher() ? 'publisher' : 'advertiser') . '.support-tickets.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Tickets
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
            <h4 class="card-title">Ticket Information</h4>
        </div>
        <div class="card-body">
            <div class="priority-info">
                <h6>Priority Guidelines:</h6>
                <ul>
                    <li><strong>Low:</strong> General questions or non-urgent requests</li>
                    <li><strong>Medium:</strong> Standard support requests</li>
                    <li><strong>High:</strong> Issues affecting your account or operations</li>
                    <li><strong>Urgent:</strong> Critical issues requiring immediate attention</li>
                </ul>
            </div>

            <form method="POST" action="{{ route('dashboard.' . (Auth::user()->isPublisher() ? 'publisher' : 'advertiser') . '.support-tickets.store') }}">
                @csrf

                <div class="form-group">
                    <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                    <input type="text" id="subject" name="subject" class="form-control @error('subject') is-invalid @enderror" 
                           value="{{ old('subject') }}" required placeholder="Brief description of your issue">
                    @error('subject')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="help-text">Provide a clear, concise subject line for your ticket.</div>
                </div>

                <div class="form-group">
                    <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                    <select id="priority" name="priority" class="form-control @error('priority') is-invalid @enderror" required>
                        <option value="">Select Priority</option>
                        <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                    @error('priority')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror" 
                              rows="8" required placeholder="Please provide detailed information about your issue...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="help-text">Please include as much detail as possible to help us assist you quickly. Minimum 10 characters required.</div>
                </div>

                <div class="form-group mb-0">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit Ticket
                    </button>
                    <a href="{{ route('dashboard.' . (Auth::user()->isPublisher() ? 'publisher' : 'advertiser') . '.support-tickets.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection





