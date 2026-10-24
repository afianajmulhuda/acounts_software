@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">Manage Permissions: {{ $role->name }}</h1>
        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Roles
        </a>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <form action="{{ route('roles.permissions.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="card card-primary card-outline mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0"><i class="bi bi-shield-lock me-1"></i> Permission Allocation Matrix</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="selectAllBtn">Select All</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAllBtn">Deselect All</button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-4">
                        @forelse($permissionsByModule as $module => $permissions)
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 border">
                                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                        <strong class="text-uppercase">{{ $module }}</strong>
                                        <input type="checkbox" class="form-check-input module-toggle" data-module="{{ Str::slug($module) }}">
                                    </div>
                                    <div class="card-body">
                                        @foreach($permissions as $perm)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input perm-check module-{{ Str::slug($module) }}" 
                                                       type="checkbox" 
                                                       name="permissions[]" 
                                                       value="{{ $perm->id }}" 
                                                       id="perm_{{ $perm->id }}"
                                                       {{ in_array($perm->id, $assignedPermissionIds) ? 'checked' : '' }}>
                                                <label class="form-check-label ms-1" for="perm_{{ $perm->id }}">
                                                    <code>{{ $perm->code }}</code>
                                                    @if($perm->description)
                                                        <br><small class="text-muted">{{ $perm->description }}</small>
                                                    @endif
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-4 text-muted">
                                No permissions exist in the database. Add permissions first.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="card-footer text-end">
                    <form action="{{ route('roles.permissions.update', $role->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Matrix inputs here -->

                        <button type="submit" class="btn btn-primary">Save Permission Matrix</button>
                    </form>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Select / Deselect All
    document.getElementById('selectAllBtn').addEventListener('click', () => {
        document.querySelectorAll('.perm-check').forEach(cb => cb.checked = true);
        document.querySelectorAll('.module-toggle').forEach(cb => cb.checked = true);
    });

    document.getElementById('deselectAllBtn').addEventListener('click', () => {
        document.querySelectorAll('.perm-check').forEach(cb => cb.checked = false);
        document.querySelectorAll('.module-toggle').forEach(cb => cb.checked = false);
    });

    // Module-level Bulk Checkbox Toggle
    document.querySelectorAll('.module-toggle').forEach(toggle => {
        toggle.addEventListener('change', function () {
            const moduleClass = `.module-${this.dataset.module}`;
            document.querySelectorAll(moduleClass).forEach(cb => cb.checked = this.checked);
        });
    });
});
</script>
@endsection