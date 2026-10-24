@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">Edit Tax Rate: {{ $taxRate->code }}</h1>
        <a href="{{ route('tax-rates.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Tax Rates
        </a>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <form action="{{ route('tax-rates.update', $taxRate->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Tax Code *</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $taxRate->code) }}" style="text-transform: uppercase;" required>
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Percentage Rate (%) *</label>
                            <input type="number" step="0.01" name="percentage_rate" class="form-control @error('percentage_rate') is-invalid @enderror" value="{{ old('percentage_rate', $taxRate->rate * 100) }}" required>
                            @error('percentage_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Linked Tax GL Account</label>
                            <select name="tax_account_id" class="form-select @error('tax_account_id') is-invalid @enderror">
                                <option value="">-- None --</option>
                                @foreach($taxAccounts as $acc)
                                    <option value="{{ $acc->id }}" {{ old('tax_account_id', $taxRate->tax_account_id) === $acc->id ? 'selected' : '' }}>
                                        {{ $acc->code }} - {{ $acc->name }} ({{ $acc->account_type }})
                                    </option>
                                @endforeach
                            </select>
                            @error('tax_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 mt-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="activeCheck" {{ old('is_active', $taxRate->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="activeCheck">Active for Calculation</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('tax-rates.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Update Tax Rate</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection