@extends('layouts.app')

@section('title', 'Journal Entries')
@section('page_title', 'General Journal Entries')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accounting</a></li>
    <li class="breadcrumb-item active">Journal Entries</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="card-title mb-0">Posted Transactions</h3>
                <div class="card-tools">
                    <a href="{{ route('accounting.journals.create') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i> Post New Journal
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Entry #</th>
                                <th>Posting Date</th>
                                <th>Description / Memo</th>
                                <th>Source</th>
                                <th>Status</th>
                                <th class="text-end">Total Amount</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($entries as $entry)
                                @php
                                    // Fetch line items summary for this entry
                                    $lineTotals = DB::table('journal_lines')
                                        ->where('entry_id', $entry->id)
                                        ->selectRaw('SUM(base_debit) as total_debit, txn_currency_code')
                                        ->groupBy('txn_currency_code')
                                        ->first();
                                @endphp
                                <tr>
                                    <td class="fw-bold text-primary">{{ $entry->entry_number }}</td>
                                    <td>{{ \Carbon\Carbon::parse($entry->posting_date)->format('Y-m-d') }}</td>
                                    <td>
                                        <span class="d-inline-block text-truncate" style="max-width: 300px;">
                                            {{ $entry->description }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $entry->source_document_type }}</span>
                                    </td>
                                    <td>
                                        @if($entry->status === 'POSTED')
                                            <span class="badge bg-success"><i class="bi bi-check-all me-1"></i> POSTED</span>
                                        @elseif($entry->status === 'DRAFT')
                                            <span class="badge bg-warning text-dark"><i class="bi bi-pencil me-1"></i> DRAFT</span>
                                        @else
                                            <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i> {{ $entry->status }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold">
                                        {{ number_format($lineTotals->total_debit ?? 0, 2) }} {{ $lineTotals->txn_currency_code ?? '' }}
                                    </td>
                                    <td class="text-center">
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-info" 
                                                onclick="viewJournalDetails('{{ $entry->id }}', '{{ $entry->entry_number }}')"
                                                title="View Lines">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="bi bi-journal-x fs-2 d-block mb-2"></i>
                                        No journal entries recorded for the current active entity.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($entries->hasPages())
            <div class="card-footer clearfix">
                <div class="float-end">
                    {{ $entries->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal for Viewing Journal Line Items -->
<div class="modal fade" id="journalModal" tabindex="-1" aria-labelledby="journalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="journalModalLabel">Journal Entry: <span id="modalEntryNum"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-secondary">
                            <tr>
                                <th>Account</th>
                                <th>Party</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Credit</th>
                                <th>Memo</th>
                            </tr>
                        </thead>
                        <tbody id="modalLinesBody">
                            <tr>
                                <td colspan="5" class="text-center">Loading details...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function viewJournalDetails(entryId, entryNumber) {
        document.getElementById('modalEntryNum').innerText = entryNumber;
        const tbody = document.getElementById('modalLinesBody');
        tbody.innerHTML = '<tr><td colspan="5" class="text-center"><div class="spinner-border spinner-border-sm text-primary"></div> Loading...</td></tr>';
        
        const journalModal = new bootstrap.Modal(document.getElementById('journalModal'));
        journalModal.show();

        // Optional API fetching for asynchronous detail loading
        fetch(`/accounting/journals/${entryId}/details`)
            .then(response => response.json())
            .then(data => {
                let html = '';
                data.lines.forEach(line => {
                    html += `
                        <tr>
                            <td><strong>${line.account_code}</strong> - ${line.account_name}</td>
                            <td>${line.party_name ?? '-'}</td>
                            <td class="text-end text-success">${parseFloat(line.txn_debit).toFixed(2)}</td>
                            <td class="text-end text-danger">${parseFloat(line.txn_credit).toFixed(2)}</td>
                            <td><small>${line.line_description ?? ''}</small></td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html;
            })
            .catch(() => {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Failed to load line item details.</td></tr>';
            });
    }
</script>
@endpush