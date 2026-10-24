@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Create New User</h1>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name *</label>
                            <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Last Name *</label>
                            <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror" value="{{ old('last_name') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email Address *</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Job Title</label>
                            <input type="text" name="job_title" class="form-control" value="{{ old('job_title') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Department</label>
                            <input type="text" name="department" class="form-control" value="{{ old('department') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Password *</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Confirm Password *</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Status *</label>
                            <select name="status" class="form-select" required>
                                <option value="ACTIVE" selected>ACTIVE</option>
                                <option value="SUSPENDED">SUSPENDED</option>
                                <option value="DEACTIVATED">DEACTIVATED</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Timezone *</label>
                            <input type="text" name="timezone" class="form-control" value="UTC" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Preferred Language *</label>
                            <input type="text" name="preferred_language" class="form-control" value="en" required>
                        </div>

                        <div class="col-12 mt-4">
                            <div class="form-check">
                                <input type="checkbox" name="is_super_admin" value="1" class="form-check-input" id="superAdminCheck">
                                <label class="form-check-label text-danger fw-bold" for="superAdminCheck">Grant Global Super Admin Privileges</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('users.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection