@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">SOX Audit Trail & System Logs</h1>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <!-- Filters Form -->
        <div class="card card-outline card-primary mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('auditlogs.index') }}" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Action Type</label>
                        <input type="text" name="action_type" value="{{ request('action_type') }}" placeholder="e.g. POST_JOURNAL_ENTRY" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Target Table</label>
                        <input type="text" name="table_name" value="{{ request('table_name') }}" placeholder="e.g. journal_entries" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter me-1"></i> Filter</button>
                        <a href="{{ route('auditlogs.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Audit Logs Data Table -->
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title"><i class="bi bi-shield-check me-1"></i> Data Mutation Log</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action Type</th>
                            <th>Target Table / ID</th>
                            <th>IP Address</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($logs as $log)
                            <tr>
                                <td><small>{{ $log->timestamp }}</small></td>
                                <td><strong>{{ $log->first_name ?? 'System' }} {{ $log->last_name ?? '' }}</strong></td>
                                <td>
                                    <span class="badge bg-info text-dark">{{ $log->action_type }}</span>
                                </td>
                                <td>
                                    <code>{{ $log->table_name }}</code> 
                                    <span class="text-muted small">({{ Str::limit($log->record_id, 8) }})</span>
                                </td>
                                <td><code>{{ $log->ip_address ?? 'N/A' }}</code></td>
                                <td class="text-end">
                                    <a href="{{ route('auditlogs.show', ['auditLog' => $log->id]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-search me-1"></i> Inspect Diff
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No audit logs found matching criteria.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $logs->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
</div>
@endsection