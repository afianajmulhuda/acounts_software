@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">Journal Entry: {{ $entry->entry_number }}</h1>
        <a href="{{ route('journal-entries.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card card-outline card-primary mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3"><strong>Posting Date:</strong> {{ $entry->posting_date }}</div>
                    <div class="col-md-3"><strong>Document Date:</strong> {{ $entry->document_date }}</div>
                    <div class="col-md-3"><strong>Period #:</strong> {{ $entry->period_number }}</div>
                    <div class="col-md-3">
                        <strong>Status:</strong> 
                        <span class="badge bg-success">{{ $entry->status }}</span>
                    </div>
                    <div class="col-md-6"><strong>Description:</strong> {{ $entry->description }}</div>
                    <div class="col-md-6"><strong>Posted By:</strong> {{ $entry->first_name }} {{ $entry->last_name }} ({{ $entry->created_at }})</div>
                </div>
            </div>
        </div>

        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h5 class="card-title m-0">Line Items Ledger Audit</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Account Code</th>
                            <th>Account Name</th>
                            <th>Counterparty</th>
                            <th>Tax Code</th>
                            <th class="text-end">Debit ($)</th>
                            <th class="text-end">Credit ($)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lines as $line)
                        <tr>
                            <td>{{ $line->line_number }}</td>
                            <td><code>{{ $line->account_code }}</code></td>
                            <td><strong>{{ $line->account_name }}</strong></td>
                            <td>{{ $line->party_name ?? '—' }}</td>
                            <td><code>{{ $line->tax_code ?? '—' }}</code></td>
                            <td class="text-end fw-bold">{{ number_format($line->txn_debit, 2) }}</td>
                            <td class="text-end fw-bold">{{ number_format($line->txn_credit, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection