@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">System Permissions</h1>
        <a href="{{ route('permissions.create') }}" class="btn btn-primary">
            <i class="bi bi-key-fill me-1"></i> Add Permission
        </a>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <form method="GET" action="{{ route('permissions.index') }}" class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search code or description..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="module" class="form-select">
                            <option value="">-- All Modules --</option>
                            @foreach($modules as $mod)
                                <option value="{{ $mod }}" {{ request('module') === $mod ? 'selected' : '' }}>{{ $mod }}</option>
                            @endforeach
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
                            <th>Module</th>
                            <th>Permission Code</th>
                            <th>Description</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($permissions as $perm)
                        <tr>
                            <td><span class="badge bg-secondary">{{ $perm->module }}</span></td>
                            <td><code>{{ $perm->code }}</code></td>
                            <td>{{ $perm->description ?? 'N/A' }}</td>
                            <td class="text-end">
                                <a href="{{ route('permissions.edit', $perm->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
                                <form action="{{ route('permissions.destroy', $perm->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure? This will remove this permission from all assigned roles.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No permissions found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $permissions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection