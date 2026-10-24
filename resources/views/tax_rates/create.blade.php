@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Create Tax Rate</h1>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <form action="{{ route('tax-rates.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Tax Code *</label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="e.g. VAT_20, GST_18" style="text-transform: uppercase;" required>
                            @error('code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Percentage Rate (%) *</label>
                            <input type="number" step="0.01" name="percentage_rate" class="form-control @error('percentage_rate') is-invalid @enderror" value="{{ old('percentage_rate', '20.00') }}" placeholder="20.00" required>
                            <small class="text-muted">Will be stored internally as decimal (e.g. 0.2000)</small>
                            @error('percentage_rate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Linked Tax GL Account</label>
                            <select name="tax_account_id" class="form-select @error('tax_account_id') is-invalid @enderror">
                                <option value="">-- None --</option>
                                @foreach($taxAccounts as $acc)
                                    <option value="{{ $acc->id }}" {{ old('tax_account_id') === $acc->id ? 'selected' : '' }}>
                                        {{ $acc->code }} - {{ $acc->name }} ({{ $acc->account_type }})
                                    </option>
                                @endforeach
                            </select>
                            @error('tax_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12 mt-3">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input" id="activeCheck" checked>
                                <label class="form-check-label fw-bold" for="activeCheck">Active for Calculation</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('tax-rates.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Tax Rate</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection