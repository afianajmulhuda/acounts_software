@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">Fiscal Years (IAS 1 Window)</h1>
        <a href="{{ route('fiscal-years.create') }}" class="btn btn-primary">
            <i class="bi bi-calendar-plus me-1"></i> New Fiscal Year
        </a>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <form method="GET" action="{{ route('fiscal-years.index') }}" class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search fiscal year name..." value="{{ request('search') }}">
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
                            <th>Fiscal Year Name</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Posting Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fiscalYears as $fy)
                        <tr>
                            <td><strong>{{ $fy->name }}</strong></td>
                            <td>{{ $fy->start_date }}</td>
                            <td>{{ $fy->end_date }}</td>
                            <td>
                                @if($fy->is_closed)
                                    <span class="badge bg-danger"><i class="bi bi-lock-fill me-1"></i> Closed</span>
                                @else
                                    <span class="badge bg-success"><i class="bi bi-unlock-fill me-1"></i> Open for Posting</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <!-- Sub-Periods Navigation Link -->
                                <a href="{{ route('fiscal-years.periods.index', $fy->id) }}" class="btn btn-sm btn-outline-info me-1" title="Manage Sub-Periods">
                                    <i class="bi bi-calendar3"></i> Periods
                                </a>

                                <!-- Toggle Close Button -->
                                <form action="{{ route('fiscal-years.toggle-close', $fy->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $fy->is_closed ? 'btn-outline-success' : 'btn-outline-warning' }}">
                                        <i class="bi {{ $fy->is_closed ? 'bi-unlock' : 'bi-lock' }}"></i>
                                        {{ $fy->is_closed ? 'Reopen' : 'Close Year' }}
                                    </button>
                                </form>

                                <!-- Delete Button -->
                                <form action="{{ route('fiscal-years.destroy', $fy->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this fiscal year and all associated accounting periods?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No fiscal years defined for current legal entity.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $fiscalYears->links() }}
            </div>
        </div>
    </div>
</div>
@endsection