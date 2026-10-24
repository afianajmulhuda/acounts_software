@extends('layouts.app')

@section('title', 'Post Journal Entry')
@section('page_title', 'New Double-Entry Journal')

@section('content')
<div class="card card-primary card-outline shadow-sm">
    <div class="card-header">
        <h3 class="card-title">Transaction Information</h3>
    </div>
    
    <form action="{{ route('accounting.journals.store') }}" method="POST" id="journalForm">
        @csrf
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Posting Date</label>
                    <input type="date" name="posting_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-9">
                    <label class="form-label fw-bold">Header Description / Memo</label>
                    <input type="text" name="description" class="form-control" placeholder="e.g., Monthly Depreciation Expense Allocation" required>
                </div>
            </div>

            <hr>
            <h5 class="fw-bold mb-3">Journal Lines</h5>

            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="linesTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 30%;">Account</th>
                            <th style="width: 20%;">Sub-Ledger Party</th>
                            <th style="width: 15%;">Debit Amount</th>
                            <th style="width: 15%;">Credit Amount</th>
                            <th style="width: 15%;">Line Description</th>
                            <th style="width: 5%;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="lineRows">
                        <!-- Line Row 1 -->
                        <tr>
                            <td>
                                <select name="lines[0][account_id]" class="form-select" required>
                                    <option value="">-- Select Account --</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }} ({{ $acc->account_type }})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="lines[0][party_id]" class="form-select">
                                    <option value="">-- None --</option>
                                    @foreach($parties as $party)
                                        <option value="{{ $party->id }}">{{ $party->legal_name }} ({{ $party->party_type }})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" step="0.0001" name="lines[0][txn_debit]" class="form-control debit-input" value="0.0000" oninput="calculateTotals()"></td>
                            <td><input type="number" step="0.0001" name="lines[0][txn_credit]" class="form-control credit-input" value="0.0000" oninput="calculateTotals()"></td>
                            <td><input type="text" name="lines[0][line_description]" class="form-control"></td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)"><i class="bi bi-trash"></i></button></td>
                        </tr>
                        <!-- Line Row 2 -->
                        <tr>
                            <td>
                                <select name="lines[1][account_id]" class="form-select" required>
                                    <option value="">-- Select Account --</option>
                                    @foreach($accounts as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }} ({{ $acc->account_type }})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="lines[1][party_id]" class="form-select">
                                    <option value="">-- None --</option>
                                    @foreach($parties as $party)
                                        <option value="{{ $party->id }}">{{ $party->legal_name }} ({{ $party->party_type }})</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" step="0.0001" name="lines[1][txn_debit]" class="form-control debit-input" value="0.0000" oninput="calculateTotals()"></td>
                            <td><input type="number" step="0.0001" name="lines[1][txn_credit]" class="form-control credit-input" value="0.0000" oninput="calculateTotals()"></td>
                            <td><input type="text" name="lines[1][line_description]" class="form-control"></td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)"><i class="bi bi-trash"></i></button></td>
                        </tr>
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="2" class="text-end">Total:</td>
                            <td id="totalDebit" class="text-success">0.0000</td>
                            <td id="totalCredit" class="text-danger">0.0000</td>
                            <td colspan="2" id="balanceStatus">
                                <span class="badge bg-secondary">Unbalanced</span>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <button type="button" class="btn btn-outline-primary btn-sm mb-3" onclick="addRow()">
                <i class="bi bi-plus-circle me-1"></i> Add Line
            </button>
        </div>

        <div class="card-footer text-end">
            <a href="{{ route('accounting.journals.index') }}" class="btn btn-secondary me-2">Cancel</a>
            <button type="submit" id="submitBtn" class="btn btn-success" disabled>Post Transaction</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    let rowIndex = 2;

    function calculateTotals() {
        let totalDebit = 0;
        let totalCredit = 0;

        document.querySelectorAll('.debit-input').forEach(el => totalDebit += parseFloat(el.value) || 0);
        document.querySelectorAll('.credit-input').forEach(el => totalCredit += parseFloat(el.value) || 0);

        document.getElementById('totalDebit').innerText = totalDebit.toFixed(4);
        document.getElementById('totalCredit').innerText = totalCredit.toFixed(4);

        const diff = Math.abs(totalDebit - totalCredit);
        const balanceStatus = document.getElementById('balanceStatus');
        const submitBtn = document.getElementById('submitBtn');

        if (diff < 0.0001 && totalDebit > 0) {
            balanceStatus.innerHTML = '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Balanced</span>';
            submitBtn.removeAttribute('disabled');
        } else {
            balanceStatus.innerHTML = `<span class="badge bg-danger">Out of Balance (${diff.toFixed(4)})</span>`;
            submitBtn.setAttribute('disabled', 'disabled');
        }
    }

    function addRow() {
        const rowHTML = `
            <tr>
                <td>
                    <select name="lines[${rowIndex}][account_id]" class="form-select" required>
                        <option value="">-- Select Account --</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }} ({{ $acc->account_type }})</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <select name="lines[${rowIndex}][party_id]" class="form-select">
                        <option value="">-- None --</option>
                        @foreach($parties as $party)
                            <option value="{{ $party->id }}">{{ $party->legal_name }} ({{ $party->party_type }})</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="number" step="0.0001" name="lines[${rowIndex}][txn_debit]" class="form-control debit-input" value="0.0000" oninput="calculateTotals()"></td>
                <td><input type="number" step="0.0001" name="lines[${rowIndex}][txn_credit]" class="form-control credit-input" value="0.0000" oninput="calculateTotals()"></td>
                <td><input type="text" name="lines[${rowIndex}][line_description]" class="form-control"></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)"><i class="bi bi-trash"></i></button></td>
            </tr>`;
        
        document.getElementById('lineRows').insertAdjacentHTML('beforeend', rowHTML);
        rowIndex++;
    }

    function removeRow(btn) {
        if (document.querySelectorAll('#lineRows tr').length > 2) {
            btn.closest('tr').remove();
            calculateTotals();
        } else {
            alert('A double-entry transaction requires at least two lines.');
        }
    }
</script>
@endpush