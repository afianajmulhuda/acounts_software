@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">User Management</h1>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i> Add User
        </a>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <form method="GET" action="{{ route('users.index') }}" class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search name or email..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">-- All Statuses --</option>
                            <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                            <option value="SUSPENDED" {{ request('status') === 'SUSPENDED' ? 'selected' : '' }}>SUSPENDED</option>
                            <option value="DEACTIVATED" {{ request('status') === 'DEACTIVATED' ? 'selected' : '' }}>DEACTIVATED</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-filter me-1"></i> Filter</button>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Role & Title</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>
                                <strong>{{ $user->first_name }} {{ $user->last_name }}</strong><br>
                                <small class="text-muted">{{ $user->email }}</small>
                            </td>
                            <td>
                                @if($user->is_super_admin)
                                    <span class="badge bg-danger">Super Admin</span>
                                @else
                                    <span class="badge bg-secondary">Standard User</span>
                                @endif
                                <div><small class="text-muted">{{ $user->job_title ?? 'N/A' }}</small></div>
                            </td>
                            <td>{{ $user->department ?? 'N/A' }}</td>
                            <td>
                                @if($user->status === 'ACTIVE')
                                    <span class="badge bg-success">Active</span>
                                @elseif($user->status === 'SUSPENDED')
                                    <span class="badge bg-warning text-dark">Suspended</span>
                                @else
                                    <span class="badge bg-secondary">Deactivated</span>
                                @endif
                            </td>
                            <td>{{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() : 'Never' }}</td>
                            <td class="text-end">
                                <!-- Entity Access Management Button -->
                                <a href="{{ route('users.access.edit', $user->id) }}" class="btn btn-sm btn-outline-info me-1" title="Manage Company Access">
                                    <i class="bi bi-building-lock"></i> Access
                                </a>

                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
                                
                                @if(auth()->id() !== $user->id)
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No system users found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection