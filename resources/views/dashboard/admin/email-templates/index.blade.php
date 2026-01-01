@extends('dashboard.layouts.main')

@section('title', 'Email Templates - Admin Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Email Templates Management</h3>
        @if(Auth::user()->isAdmin() || Auth::user()->hasPermission('manage_settings'))
        <a href="{{ route('dashboard.admin.email-templates.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Template
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

    <div class="card">
        <div class="card-header">
            <h4 class="card-title">All Email Templates</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Subject</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Variables</th>
                            <th>Updated At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                            <tr>
                                <td><strong>{{ $template->name }}</strong></td>
                                <td>{{ Str::limit($template->subject, 50) }}</td>
                                <td>{{ $template->description ?? 'N/A' }}</td>
                                <td>
                                    @if($template->is_active)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    @if($template->variables && count($template->variables) > 0)
                                        <span class="badge badge-info">{{ count($template->variables) }} variables</span>
                                    @else
                                        <span class="text-muted">None</span>
                                    @endif
                                </td>
                                <td>{{ $template->updated_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('dashboard.admin.email-templates.show', $template) }}" class="btn btn-sm btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(Auth::user()->isAdmin() || Auth::user()->hasPermission('manage_settings'))
                                        <a href="{{ route('dashboard.admin.email-templates.edit', $template) }}" class="btn btn-sm btn-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('dashboard.admin.email-templates.destroy', $template) }}" method="POST" class="action-form d-inline" onsubmit="return confirm('Are you sure you want to delete this template?');">
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
                                <td colspan="7" class="text-center">No email templates found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $templates->links() }}
            </div>
        </div>
    </div>
@endsection




