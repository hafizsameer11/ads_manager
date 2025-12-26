@extends('dashboard.layouts.main')

@section('title', 'Edit Website - Publisher Dashboard')

@section('content')
    <div class="page-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1>Edit Website</h1>
                <p class="text-muted">{{ $website->domain }}</p>
            </div>
            <div>
                <a href="{{ route('dashboard.publisher.sites.show', $website) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
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
            <h3 class="card-title">Edit Website Information</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.publisher.sites.update', $website) }}">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Website Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   value="{{ old('name', $website->name) }}" required>
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Domain</label>
                            <input type="text" class="form-control" value="{{ $website->domain }}" disabled>
                            <small class="text-muted">Domain cannot be changed after creation</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Verification Method</label>
                            <input type="text" class="form-control" 
                                   value="{{ ucfirst(str_replace('_', ' ', $website->verification_method)) }}" disabled>
                            <small class="text-muted">Verification method cannot be changed after creation</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Status</label>
                            @php
                                $statusText = 'Pending';
                                if (in_array($website->status, ['approved', 'verified'])) {
                                    $statusText = 'Approved';
                                } elseif ($website->status === 'rejected') {
                                    $statusText = 'Rejected';
                                }
                            @endphp
                            <input type="text" class="form-control" value="{{ $statusText }}" disabled>
                            <small class="text-muted">Status is managed by administrators</small>
                        </div>
                    </div>
                </div>

                @if($website->rejection_reason)
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-danger">
                            <strong>Rejection Reason:</strong> {{ $website->rejection_reason }}
                        </div>
                    </div>
                </div>
                @endif

                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <strong>Note:</strong> Only the website name can be edited. Domain and verification method cannot be changed after creation. If you need to change these, please delete this website and create a new one.
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Website
                        </button>
                        <a href="{{ route('dashboard.publisher.sites.show', $website) }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

