@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Create GL Account</h1>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <form action="{{ route('accounts.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Account Code *</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="e.g. 1010" required>
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Account Name *</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Operating Cash Account" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Account Type *</label>
                            <select name="account_type" id="accountTypeSelect" class="form-select @error('account_type') is-invalid @enderror" required>
                                <option value="">-- Select Type --</option>
                                <option value="ASSET" {{ old('account_type') === 'ASSET' ? 'selected' : '' }}>ASSET</option>
                                <option value="LIABILITY" {{ old('account_type') === 'LIABILITY' ? 'selected' : '' }}>LIABILITY</option>
                                <option value="EQUITY" {{ old('account_type') === 'EQUITY' ? 'selected' : '' }}>EQUITY</option>
                                <option value="REVENUE" {{ old('account_type') === 'REVENUE' ? 'selected' : '' }}>REVENUE</option>
                                <option value="EXPENSE" {{ old('account_type') === 'EXPENSE' ? 'selected' : '' }}>EXPENSE</option>
                            </select>
                            @error('account_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Normal Balance *</label>
                            <select name="normal_balance" id="normalBalanceSelect" class="form-select @error('normal_balance') is-invalid @enderror" required>
                                <option value="DEBIT" {{ old('normal_balance') === 'DEBIT' ? 'selected' : '' }}>DEBIT</option>
                                <option value="CREDIT" {{ old('normal_balance') === 'CREDIT' ? 'selected' : '' }}>CREDIT</option>
                            </select>
                            @error('normal_balance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Parent Account (Hierarchy)</label>
                            <select name="parent_account_id" class="form-select @error('parent_account_id') is-invalid @enderror">
                                <option value="">-- None (Header Level) --</option>
                                @foreach($parentAccounts as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_account_id') === $parent->id ? 'selected' : '' }}>
                                        {{ $parent->code }} - {{ $parent->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 mt-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="activeCheck" checked>
                                <label class="form-check-label fw-bold" for="activeCheck">Active for Posting</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('accounts.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('accountTypeSelect');
    const balanceSelect = document.getElementById('normalBalanceSelect');

    // Auto-set standard accounting rules for normal balances
    typeSelect.addEventListener('change', function () {
        if (this.value === 'ASSET' || this.value === 'EXPENSE') {
            balanceSelect.value = 'DEBIT';
        } else if (this.value === 'LIABILITY' || this.value === 'EQUITY' || this.value === 'REVENUE') {
            balanceSelect.value = 'CREDIT';
        }
    });
});
</script>
@endsection