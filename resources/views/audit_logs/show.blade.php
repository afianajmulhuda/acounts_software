@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">Audit Record Inspection</h1>
        <a href="{{ route('auditlogs.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Audit Trail
        </a>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card card-outline card-info mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3"><strong>Action Type:</strong> <span class="badge bg-info text-dark">{{ $log->action_type }}</span></div>
                    <div class="col-md-3"><strong>Table Name:</strong> <code>{{ $log->table_name }}</code></div>
                    <div class="col-md-3"><strong>Record UUID:</strong> <code>{{ $log->record_id }}</code></div>
                    <div class="col-md-3"><strong>IP Address:</strong> <code>{{ $log->ip_address }}</code></div>
                    <div class="col-md-6"><strong>User:</strong> {{ $log->first_name ?? 'System' }} {{ $log->last_name ?? '' }}</div>
                    <div class="col-md-6"><strong>Timestamp:</strong> {{ $log->timestamp }}</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card card-outline card-danger">
                    <div class="card-header"><h5 class="card-title m-0">Old State (Before)</h5></div>
                    <div class="card-body">
                        <pre class="bg-light p-3 rounded"><code>{{ json_encode(json_decode($log->previous_state), JSON_PRETTY_PRINT) ?? 'N/A' }}</code></pre>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-outline card-success">
                    <div class="card-header"><h5 class="card-title m-0">New State (After)</h5></div>
                    <div class="card-body">
                        <pre class="bg-light p-3 rounded"><code>{{ json_encode(json_decode($log->new_state), JSON_PRETTY_PRINT) ?? 'N/A' }}</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection