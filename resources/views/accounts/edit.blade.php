@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">Edit Account: {{ $account->code }} - {{ $account->name }}</h1>
        <a href="{{ route('accounts.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Chart of Accounts
        </a>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <form action="{{ route('accounts.update', $account->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Account Code *</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $account->code) }}" required>
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Account Name *</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $account->name) }}" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Account Type *</label>
                            <select name="account_type" class="form-select @error('account_type') is-invalid @enderror" required>
                                <option value="ASSET" {{ old('account_type', $account->account_type) === 'ASSET' ? 'selected' : '' }}>ASSET</option>
                                <option value="LIABILITY" {{ old('account_type', $account->account_type) === 'LIABILITY' ? 'selected' : '' }}>LIABILITY</option>
                                <option value="EQUITY" {{ old('account_type', $account->account_type) === 'EQUITY' ? 'selected' : '' }}>EQUITY</option>
                                <option value="REVENUE" {{ old('account_type', $account->account_type) === 'REVENUE' ? 'selected' : '' }}>REVENUE</option>
                                <option value="EXPENSE" {{ old('account_type', $account->account_type) === 'EXPENSE' ? 'selected' : '' }}>EXPENSE</option>
                            </select>
                            @error('account_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Normal Balance *</label>
                            <select name="normal_balance" class="form-select @error('normal_balance') is-invalid @enderror" required>
                                <option value="DEBIT" {{ old('normal_balance', $account->normal_balance) === 'DEBIT' ? 'selected' : '' }}>DEBIT</option>
                                <option value="CREDIT" {{ old('normal_balance', $account->normal_balance) === 'CREDIT' ? 'selected' : '' }}>CREDIT</option>
                            </select>
                            @error('normal_balance') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Parent Account (Hierarchy)</label>
                            <select name="parent_account_id" class="form-select @error('parent_account_id') is-invalid @enderror">
                                <option value="">-- None (Header Level) --</option>
                                @foreach($parentAccounts as $parent)
                                    <option value="{{ $parent->id }}" {{ old('parent_account_id', $account->parent_account_id) === $parent->id ? 'selected' : '' }}>
                                        {{ $parent->code }} - {{ $parent->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('parent_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 mt-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="activeCheck" {{ old('is_active', $account->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="activeCheck">Active for Posting</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('accounts.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Update Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection