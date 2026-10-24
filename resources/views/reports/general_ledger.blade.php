@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">General Ledger Drill-Down</h1>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card card-outline card-primary mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('reports.general-ledger') }}" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Target Account *</label>
                        <select name="account_id" class="form-select" required>
                            <option value="">-- Select GL Account --</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}" {{ $accountId === $acc->id ? 'selected' : '' }}>
                                    {{ $acc->code }} - {{ $acc->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">From Date</label>
                        <input type="date" name="from_date" value="{{ $fromDate }}" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">To Date</label>
                        <input type="date" name="to_date" value="{{ $toDate }}" class="form-control">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Query</button>
                    </div>
                </form>
            </div>
        </div>

        @if($selectedAccount)
        <div class="card card-outline card-info">
            <div class="card-header">
                <h5 class="card-title m-0">Ledger Statement: <code>{{ $selectedAccount->code }}</code> - {{ $selectedAccount->name }}</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Posting Date</th>
                            <th>Entry #</th>
                            <th>Description</th>
                            <th>Party</th>
                            <th class="text-end">Debit ($)</th>
                            <th class="text-end">Credit ($)</th>
                            <th class="text-end">Running Balance ($)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="table-warning">
                            <td colspan="6"><strong>Opening Balance (Prior to {{ $fromDate }}):</strong></td>
                            <td class="text-end fw-bold">${{ number_format($openingBalance, 2) }}</td>
                        </tr>
                        @php $running = $openingBalance; @endphp
                        @forelse($lines as $line)
                            @php
                                $movement = ($selectedAccount->normal_balance === 'DEBIT')
                                    ? ($line->txn_debit - $line->txn_credit)
                                    : ($line->txn_credit - $line->txn_debit);
                                $running += $movement;
                            @endphp
                            <tr>
                                <td>{{ $line->posting_date }}</td>
                                <td><code>{{ $line->entry_number }}</code></td>
                                <td>{{ $line->line_description ?? $line->header_description }}</td>
                                <td>{{ $line->party_name ?? '—' }}</td>
                                <td class="text-end">{{ number_format($line->txn_debit, 2) }}</td>
                                <td class="text-end">{{ number_format($line->txn_credit, 2) }}</td>
                                <td class="text-end fw-bold">{{ number_format($running, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-3 text-muted">No line item movements recorded in selected range.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection