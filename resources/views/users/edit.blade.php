@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">Edit User: {{ $user->first_name }} {{ $user->last_name }}</h1>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Users
        </a>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <form action="{{ route('users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name', $user->first_name) }}" required>
                            @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Last Name *</label>
                            <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name', $user->last_name) }}" required>
                            @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Job Title</label>
                            <input type="text" name="job_title" class="form-control" value="{{ old('job_title', $user->job_title) }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Department</label>
                            <input type="text" name="department" class="form-control" value="{{ old('department', $user->department) }}">
                        </div>

                        <hr class="my-4">
                        <h5 class="text-secondary"><i class="bi bi-shield-lock me-1"></i> Security & Authentication</h5>

                        <div class="col-md-6">
                            <label class="form-label">New Password</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Leave blank to keep current password">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Re-enter new password">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Account Status *</label>
                            <select name="status" class="form-select" required>
                                <option value="ACTIVE" {{ old('status', $user->status) === 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                                <option value="SUSPENDED" {{ old('status', $user->status) === 'SUSPENDED' ? 'selected' : '' }}>SUSPENDED</option>
                                <option value="DEACTIVATED" {{ old('status', $user->status) === 'DEACTIVATED' ? 'selected' : '' }}>DEACTIVATED</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Timezone *</label>
                            <input type="text" name="timezone" class="form-control" value="{{ old('timezone', $user->timezone) }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Preferred Language *</label>
                            <input type="text" name="preferred_language" class="form-control" value="{{ old('preferred_language', $user->preferred_language) }}" required>
                        </div>

                        <div class="col-12 mt-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_super_admin" value="1" class="form-check-input" id="superAdminCheck" {{ old('is_super_admin', $user->is_super_admin) ? 'checked' : '' }}>
                                <label class="form-check-label text-danger fw-bold" for="superAdminCheck">Grant Global Super Admin Privileges</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Update User Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection