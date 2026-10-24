@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">Entity Access: {{ $user->first_name }} {{ $user->last_name }}</h1>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Users
        </a>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <!-- Assign Access Form -->
        <div class="card card-primary card-outline mb-4">
            <div class="card-header">
                <h5 class="card-title m-0"><i class="bi bi-building-add me-1"></i> Grant Company Scope & Authority</h5>
            </div>
            <form action="{{ route('users.access.store', $user->id) }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Target Legal Entity *</label>
                            <select name="company_id" class="form-select @error('company_id') is-invalid @enderror" required>
                                <option value="">-- Select Company --</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->legal_name }} ({{ $company->base_currency_code }})</option>
                                @endforeach
                            </select>
                            @error('company_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Assigned Role *</label>
                            <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                                <option value="">-- Select Role --</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                            @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Max Approval Limit ($) *</label>
                            <input type="number" step="0.0001" name="max_approval_limit" class="form-control @error('max_approval_limit') is-invalid @enderror" value="0.0000" required>
                            @error('max_approval_limit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="can_post_journals" value="1" class="form-check-input" id="postJournalsCheck">
                                <label class="form-check-label fw-bold" for="postJournalsCheck">Authorize Journal Entry Posting Rights</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-success"><i class="bi bi-shield-check me-1"></i> Grant Access</button>
                </div>
            </form>
        </div>

        <!-- Active Access Table -->
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title"><i class="bi bi-shield-lock me-1"></i> Existing Granted Permissions</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Assigned Role</th>
                            <th>Approval Limit</th>
                            <th>Posting Authority</th>
                            <th>Granted At</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accessRecords as $access)
                        <tr>
                            <td><strong>{{ $access->company_name }}</strong></td>
                            <td><span class="badge bg-info text-dark">{{ $access->role_name }}</span></td>
                            <td>{{ number_format($access->max_approval_limit, 2) }} {{ $access->base_currency_code }}</td>
                            <td>
                                @if($access->can_post_journals)
                                    <span class="badge bg-success"><i class="bi bi-check-lg"></i> Authorized</span>
                                @else
                                    <span class="badge bg-secondary">Disabled</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($access->granted_at)->format('Y-m-d H:i') }}</td>
                            <td class="text-end">
                                <form action="{{ route('users.access.destroy', $access->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Revoke company access for this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-shield-x me-1"></i> Revoke</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No specific company access granted yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection