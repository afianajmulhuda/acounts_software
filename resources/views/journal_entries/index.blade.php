@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">General Journal Entries</h1>
        <a href="{{ route('journal-entries.create') }}" class="btn btn-primary">
            <i class="bi bi-journal-plus me-1"></i> New Journal Post
        </a>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <form method="GET" action="{{ route('journal-entries.index') }}" class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search entry # or description..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">-- All Statuses --</option>
                            <option value="POSTED" {{ request('status') === 'POSTED' ? 'selected' : '' }}>POSTED</option>
                            <option value="DRAFT" {{ request('status') === 'DRAFT' ? 'selected' : '' }}>DRAFT</option>
                            <option value="VOIDED" {{ request('status') === 'VOIDED' ? 'selected' : '' }}>VOIDED</option>
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
                            <th>Entry Number</th>
                            <th>Posting Date</th>
                            <th>Description</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Posted By</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                        <tr>
                            <td><code>{{ $entry->entry_number }}</code></td>
                            <td>{{ $entry->posting_date }}</td>
                            <td>{{ Str::limit($entry->description, 40) }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $entry->source_document_type ?? 'MANUAL' }}</span></td>
                            <td>
                                @if($entry->status === 'POSTED')
                                    <span class="badge bg-success">POSTED</span>
                                @elseif($entry->status === 'DRAFT')
                                    <span class="badge bg-warning text-dark">DRAFT</span>
                                @else
                                    <span class="badge bg-danger">VOIDED</span>
                                @endif
                            </td>
                            <td><small>{{ $entry->first_name }} {{ $entry->last_name }}</small></td>
                            <td class="text-end">
                                <a href="{{ route('journal-entries.show', $entry->id) }}" class="btn btn-sm btn-outline-info">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No journal entries posted in current active entity.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $entries->links() }}
            </div>
        </div>
    </div>
</div>
@endsection