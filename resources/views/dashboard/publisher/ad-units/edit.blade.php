@extends('dashboard.layouts.main')

@section('title', 'Edit Ad Unit - Publisher Dashboard')

@section('content')
    <div class="page-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1>Edit Ad Unit</h1>
                <p class="text-muted">{{ $adUnit->name }}</p>
            </div>
            <div>
                <a href="{{ route('dashboard.publisher.ad-units.show', $adUnit) }}" class="btn btn-secondary">
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
            <h3 class="card-title">Edit Ad Unit</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.publisher.ad-units.update', $adUnit) }}">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Ad Unit Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   value="{{ old('name', $adUnit->name) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select id="status" name="status" class="form-control" required>
                                <option value="active" {{ old('status', $adUnit->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="paused" {{ old('status', $adUnit->status) == 'paused' ? 'selected' : '' }}>Paused</option>
                            </select>
                        </div>
                    </div>
                </div>

                @if($adUnit->type === 'banner')
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="size">Banner Size <span class="text-danger">*</span></label>
                            <input type="text" id="size" name="size" class="form-control" 
                                   value="{{ old('size', $adUnit->size ?? ($adUnit->width . 'x' . $adUnit->height)) }}" 
                                   pattern="\d+x\d+" required>
                            <small class="text-muted">Format: width x height (e.g., 300x250)</small>
                        </div>
                    </div>
                </div>
                @else
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="frequency">Frequency (seconds) <span class="text-danger">*</span></label>
                            <input type="number" id="frequency" name="frequency" class="form-control" 
                                   value="{{ old('frequency', $adUnit->frequency) }}" 
                                   min="1" max="3600" required>
                            <small class="text-muted">How often the popup should appear (1-3600 seconds)</small>
                        </div>
                    </div>
                </div>
                @endif

                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <strong>Note:</strong> Ad type cannot be changed after creation. Unit code is automatically generated and cannot be modified.
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Ad Unit
                        </button>
                        <a href="{{ route('dashboard.publisher.ad-units.show', $adUnit) }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection




