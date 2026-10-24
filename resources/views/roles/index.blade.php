@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">Role Management</h1>
        <a href="{{ route('roles.create') }}" class="btn btn-primary">
            <i class="bi bi-shield-plus me-1"></i> Create Role
        </a>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <form method="GET" action="{{ route('roles.index') }}" class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search role name..." value="{{ request('search') }}">
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
                            <th>Role Name</th>
                            <th>Description</th>
                            <th>Type</th>
                            <th>Created At</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                        <tr>
                            <td><strong>{{ $role->name }}</strong></td>
                            <td>{{ $role->description ?? 'N/A' }}</td>
                            <td>
                                @if($role->is_system_role)
                                    <span class="badge bg-danger">System Locked</span>
                                @else
                                    <span class="badge bg-info text-dark">Custom</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($role->created_at)->format('Y-m-d H:i') }}</td>
                            <td class="text-end">
                                <!-- Permissions Matrix Link -->
                                <a href="{{ route('roles.permissions.edit', $role->id) }}" class="btn btn-sm btn-outline-info me-1" title="Manage Permissions">
                                    <i class="bi bi-key-fill"></i> Permissions
                                </a>

                                <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>

                                @if(!$role->is_system_role)
                                <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this role?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No roles defined.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $roles->links() }}
            </div>
        </div>
    </div>
</div>
@endsection