@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">New Double-Entry Journal Post</h1>
        <a href="{{ route('journal-entries.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <form action="{{ route('journal-entries.store') }}" method="POST" id="journalForm">
            @csrf
            <div class="card card-primary card-outline mb-4">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Posting Date *</label>
                            <input type="date" name="posting_date" class="form-control" value="{{ old('posting_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Document Date *</label>
                            <input type="date" name="document_date" class="form-control" value="{{ old('document_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Source Document Type</label>
                            <select name="source_document_type" class="form-select">
                                <option value="MANUAL">MANUAL</option>
                                <option value="INVOICE">INVOICE</option>
                                <option value="PAYMENT">PAYMENT</option>
                                <option value="REVALUATION">REVALUATION</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Header Description *</label>
                            <input type="text" name="description" class="form-control" placeholder="e.g. Monthly accrual adjustment entry" value="{{ old('description') }}" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Double-Entry Transaction Lines -->
            <div class="card card-outline card-secondary mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0"><i class="bi bi-list-check me-1"></i> Line Items (Double-Entry Balance Required)</h5>
                    <button type="button" class="btn btn-sm btn-success" id="addLineBtn"><i class="bi bi-plus-lg"></i> Add Line</button>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered align-middle mb-0" id="linesTable">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 25%;">Account *</th>
                                <th style="width: 20%;">Sub-ledger Party</th>
                                <th style="width: 15%;">Tax Rate</th>
                                <th style="width: 15%;">Debit ($)</th>
                                <th style="width: 15%;">Credit ($)</th>
                                <th style="width: 10%;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="linesContainer">
                            <!-- JS injects default rows -->
                        </tbody>
                        <tfoot class="fw-bold bg-light">
                            <tr>
                                <td colspan="3" class="text-end">Totals:</td>
                                <td id="totalDebitCell">$0.00</td>
                                <td id="totalCreditCell">$0.00</td>
                                <td id="balanceStatusCell" class="text-center text-danger">Unbalanced</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled><i class="bi bi-check-circle me-1"></i> Post Journal Entry</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let lineIndex = 0;

    const accountsOptions = `@foreach($accounts as $acc)<option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>@endforeach`;
    const partiesOptions = `<option value="">-- None --</option>@foreach($parties as $p)<option value="{{ $p->id }}">{{ $p->legal_name }}</option>@endforeach`;
    const taxOptions = `<option value="">-- None --</option>@foreach($taxRates as $t)<option value="{{ $t->id }}">{{ $t->code }} ({{ $t->rate * 100 }}%)</option>@endforeach`;

    function addRow(debitVal = 0, creditVal = 0) {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>
                <select name="lines[${lineIndex}][account_id]" class="form-select" required>
                    <option value="">-- Select GL Account --</option>
                    ${accountsOptions}
                </select>
            </td>
            <td>
                <select name="lines[${lineIndex}][party_id]" class="form-select">
                    ${partiesOptions}
                </select>
            </td>
            <td>
                <select name="lines[${lineIndex}][tax_rate_id]" class="form-select">
                    ${taxOptions}
                </select>
            </td>
            <td>
                <input type="number" step="0.0001" min="0" name="lines[${lineIndex}][debit]" class="form-control debit-input" value="${debitVal.toFixed(4)}" required>
            </td>
            <td>
                <input type="number" step="0.0001" min="0" name="lines[${lineIndex}][credit]" class="form-control credit-input" value="${creditVal.toFixed(4)}" required>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-line-btn"><i class="bi bi-trash"></i></button>
            </td>
        `;
        document.getElementById('linesContainer').appendChild(tr);
        lineIndex++;
        recalculateTotals();
    }

    function recalculateTotals() {
        let totalDebit = 0;
        let totalCredit = 0;

        document.querySelectorAll('.debit-input').forEach(i => totalDebit += parseFloat(i.value) || 0);
        document.querySelectorAll('.credit-input').forEach(i => totalCredit += parseFloat(i.value) || 0);

        document.getElementById('totalDebitCell').textContent = `$${totalDebit.toFixed(2)}`;
        document.getElementById('totalCreditCell').textContent = `$${totalCredit.toFixed(2)}`;

        const statusCell = document.getElementById('balanceStatusCell');
        const submitBtn = document.getElementById('submitBtn');

        if (Math.abs(totalDebit - totalCredit) < 0.0001 && totalDebit > 0) {
            statusCell.textContent = 'Balanced';
            statusCell.className = 'text-center text-success fw-bold';
            submitBtn.disabled = false;
        } else {
            statusCell.textContent = 'Unbalanced';
            statusCell.className = 'text-center text-danger fw-bold';
            submitBtn.disabled = true;
        }
    }

    document.getElementById('addLineBtn').addEventListener('click', () => addRow());

    document.getElementById('linesContainer').addEventListener('input', function (e) {
        if (e.target.classList.contains('debit-input') || e.target.classList.contains('credit-input')) {
            recalculateTotals();
        }
    });

    document.getElementById('linesContainer').addEventListener('click', function (e) {
        if (e.target.closest('.remove-line-btn')) {
            if (document.querySelectorAll('#linesContainer tr').length > 2) {
                e.target.closest('tr').remove();
                recalculateTotals();
            } else {
                alert('A double-entry transaction must contain at least 2 lines.');
            }
        }
    });

    // Add initial 2 rows for double-entry rule
    addRow();
    addRow();
});
</script>
@endsection