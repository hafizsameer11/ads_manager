@extends('dashboard.layouts.main')

@section('title', 'Create User - Admin Dashboard')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Create New User</h2>
        <a href="{{ route('dashboard.admin.users') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Users
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
            <h3 class="card-title">User Information</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dashboard.admin.users.store') }}">
                @csrf

                <div class="form-group">
                    <label for="name">Full Name <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" 
                           value="{{ old('name') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="username">Username <span class="text-danger">*</span></label>
                    <input type="text" id="username" name="username" class="form-control @error('username') is-invalid @enderror" 
                           value="{{ old('username') }}" required>
                    <small class="form-text text-muted">Only letters, numbers, dashes and underscores allowed</small>
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email">Email Address <span class="text-danger">*</span></label>
                    <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                           value="{{ old('email') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                           value="{{ old('phone') }}">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Password <span class="text-danger">*</span></label>
                    <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                           required minlength="8">
                    <small class="form-text text-muted">Minimum 8 characters</small>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" 
                           required minlength="8">
                </div>

                <hr style="margin: 30px 0;">

                <div class="form-group">
                    <label for="role">User Type <span class="text-danger">*</span></label>
                    <select id="role" name="role" class="form-control @error('role') is-invalid @enderror" required onchange="toggleRoleSelection()">
                        <option value="">Select User Type</option>
                        <option value="sub-admin" {{ old('role') == 'sub-admin' ? 'selected' : '' }}>Sub-Admin</option>
                        <option value="publisher" {{ old('role') == 'publisher' ? 'selected' : '' }}>Publisher</option>
                        <option value="advertiser" {{ old('role') == 'advertiser' ? 'selected' : '' }}>Advertiser</option>
                    </select>
                    <small class="form-text text-muted">Select the type of user you want to create</small>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" id="role-selection-group" style="display: none;">
                    <label for="role_id">Assign Role <span class="text-danger">*</span></label>
                    <select id="role_id" name="role_id" class="form-control @error('role_id') is-invalid @enderror">
                        <option value="">Select a Role</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                {{ $role->name }} - {{ $role->description }}
                            </option>
                        @endforeach
                    </select>
                    <small class="form-text text-muted">Select a predefined role. Permissions will be automatically assigned based on the selected role.</small>
                    @error('role_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="alert alert-info" id="role-info" style="display: none;">
                    <strong><i class="fas fa-info-circle"></i> Note:</strong>
                    <ul class="mb-0 mt-2" id="role-info-content">
                    </ul>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create User
                    </button>
                    <a href="{{ route('dashboard.admin.users') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleRoleSelection() {
            const roleSelect = document.getElementById('role');
            const roleSelectionGroup = document.getElementById('role-selection-group');
            const roleInfo = document.getElementById('role-info');
            const roleInfoContent = document.getElementById('role-info-content');
            const roleIdSelect = document.getElementById('role_id');

            if (roleSelect.value === 'sub-admin') {
                roleSelectionGroup.style.display = 'block';
                roleIdSelect.required = true;
                roleInfo.style.display = 'block';
                roleInfoContent.innerHTML = '<li>Sub-admins are created with predefined roles that contain specific permissions.</li><li>Select a role from the dropdown above to assign permissions automatically.</li><li>The user will be able to log in immediately with the credentials you provide.</li>';
            } else {
                roleSelectionGroup.style.display = 'none';
                roleIdSelect.required = false;
                roleIdSelect.value = '';
                if (roleSelect.value === 'publisher') {
                    roleInfo.style.display = 'block';
                    roleInfoContent.innerHTML = '<li>Publishers can register websites and earn revenue from displaying ads.</li><li>The user will be automatically approved and can log in immediately.</li>';
                } else if (roleSelect.value === 'advertiser') {
                    roleInfo.style.display = 'block';
                    roleInfoContent.innerHTML = '<li>Advertisers can create campaigns and manage their advertising budget.</li><li>The user will be automatically approved and can log in immediately.</li>';
                } else {
                    roleInfo.style.display = 'none';
                }
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleRoleSelection();
        });
    </script>
@endsection

