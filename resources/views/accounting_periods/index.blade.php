@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">Sub-Periods: {{ $fiscalYear->name }}</h1>
        <a href="{{ route('fiscal-years.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Fiscal Years
        </a>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="bi bi-calendar3 me-1"></i> Reporting Sub-Period Schedule</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Period #</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Hard Lock Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($periods as $period)
                        <tr>
                            <td><strong>Period {{ $period->period_number }}</strong></td>
                            <td>{{ $period->start_date }}</td>
                            <td>{{ $period->end_date }}</td>
                            <td>
                                @if($period->is_closed)
                                    <span class="badge bg-danger"><i class="bi bi-lock-fill me-1"></i> Closed</span>
                                @else
                                    <span class="badge bg-success"><i class="bi bi-unlock-fill me-1"></i> Open for Posting</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <!-- Edit Period Route -->
                                <a href="{{ route('accounting-periods.edit', $period->id) }}" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>

                                <!-- Toggle Close/Lock Period Route -->
                                <form action="{{ route('accounting-periods.toggle-close', $period->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $period->is_closed ? 'btn-outline-success' : 'btn-outline-warning' }}">
                                        <i class="bi {{ $period->is_closed ? 'bi-unlock' : 'bi-lock' }}"></i>
                                        {{ $period->is_closed ? 'Reopen' : 'Lock Period' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No sub-periods configured for this fiscal year.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection