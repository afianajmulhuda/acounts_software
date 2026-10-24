@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">Trial Balance Report</h1>
        <button onclick="window.print();" class="btn btn-outline-secondary"><i class="bi bi-printer me-1"></i> Print / PDF</button>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card card-outline card-primary mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('reports.trial-balance') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">As of Date</label>
                        <input type="date" name="as_of_date" value="{{ $asOfDate }}" class="form-control">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-arrow-repeat me-1"></i> Generate</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card card-outline card-secondary">
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Account Code</th>
                            <th>Account Name</th>
                            <th>Type</th>
                            <th class="text-end">Debit Balance ($)</th>
                            <th class="text-end">Credit Balance ($)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $acc)
                        <tr>
                            <td><code>{{ $acc->code }}</code></td>
                            <td>
                                <a href="{{ route('reports.general-ledger', ['account_id' => $acc->id, 'to_date' => $asOfDate]) }}">
                                    {{ $acc->name }}
                                </a>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $acc->account_type }}</span></td>
                            <td class="text-end">{{ number_format($acc->total_debit, 2) }}</td>
                            <td class="text-end">{{ number_format($acc->total_credit, 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No journal postings recorded up to selected date.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="fw-bold bg-light">
                        <tr>
                            <td colspan="3" class="text-end">Total Live Balances:</td>
                            <td class="text-end text-success">${{ number_format($grandTotalDebit, 2) }}</td>
                            <td class="text-end text-success">${{ number_format($grandTotalCredit, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection