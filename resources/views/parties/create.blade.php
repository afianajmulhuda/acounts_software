@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Create Counterparty</h1>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <form action="{{ route('parties.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Legal Name *</label>
                            <input type="text" name="legal_name" class="form-control @error('legal_name') is-invalid @enderror" value="{{ old('legal_name') }}" placeholder="e.g. Acme International LLC" required>
                            @error('legal_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Sub-ledger Entity Type *</label>
                            <select name="party_type" class="form-select @error('party_type') is-invalid @enderror" required>
                                <option value="CUSTOMER" {{ old('party_type') === 'CUSTOMER' ? 'selected' : '' }}>CUSTOMER</option>
                                <option value="VENDOR" {{ old('party_type') === 'VENDOR' ? 'selected' : '' }}>VENDOR</option>
                                <option value="BOTH" {{ old('party_type') === 'BOTH' ? 'selected' : '' }}>BOTH</option>
                            </select>
                            @error('party_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tax ID / VAT Registration Number</label>
                            <input type="text" name="tax_number" class="form-control @error('tax_number') is-invalid @enderror" value="{{ old('tax_number') }}" placeholder="e.g. US987654321">
                            @error('tax_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Country Code (ISO-2) *</label>
                            <input type="text" name="country_code" class="form-control @error('country_code') is-invalid @enderror" value="{{ old('country_code', 'US') }}" maxlength="2" placeholder="US" style="text-transform: uppercase;" required>
                            @error('country_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Default Currency (ISO-3) *</label>
                            <input type="text" name="currency_code" class="form-control @error('currency_code') is-invalid @enderror" value="{{ old('currency_code', 'USD') }}" maxlength="3" placeholder="USD" style="text-transform: uppercase;" required>
                            @error('currency_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('parties.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Counterparty</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection