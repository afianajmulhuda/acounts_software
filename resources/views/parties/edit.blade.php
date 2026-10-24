@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">Edit Counterparty: {{ $party->legal_name }}</h1>
        <a href="{{ route('parties.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Parties
        </a>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <form action="{{ route('parties.update', $party->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Legal Name *</label>
                            <input type="text" name="legal_name" class="form-control @error('legal_name') is-invalid @enderror" value="{{ old('legal_name', $party->legal_name) }}" required>
                            @error('legal_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Sub-ledger Entity Type *</label>
                            <select name="party_type" class="form-select @error('party_type') is-invalid @enderror" required>
                                <option value="CUSTOMER" {{ old('party_type', $party->party_type) === 'CUSTOMER' ? 'selected' : '' }}>CUSTOMER</option>
                                <option value="VENDOR" {{ old('party_type', $party->party_type) === 'VENDOR' ? 'selected' : '' }}>VENDOR</option>
                                <option value="BOTH" {{ old('party_type', $party->party_type) === 'BOTH' ? 'selected' : '' }}>BOTH</option>
                            </select>
                            @error('party_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tax ID / VAT Registration Number</label>
                            <input type="text" name="tax_number" class="form-control @error('tax_number') is-invalid @enderror" value="{{ old('tax_number', $party->tax_number) }}">
                            @error('tax_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Country Code (ISO-2) *</label>
                            <input type="text" name="country_code" class="form-control @error('country_code') is-invalid @enderror" value="{{ old('country_code', $party->country_code) }}" maxlength="2" style="text-transform: uppercase;" required>
                            @error('country_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Default Currency (ISO-3) *</label>
                            <input type="text" name="currency_code" class="form-control @error('currency_code') is-invalid @enderror" value="{{ old('currency_code', $party->currency_code) }}" maxlength="3" style="text-transform: uppercase;" required>
                            @error('currency_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('parties.index') }}" class="btn btn-secondary me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i> Update Counterparty</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection