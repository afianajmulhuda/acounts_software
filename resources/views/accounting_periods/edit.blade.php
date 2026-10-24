@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">Edit Sub-Period #{{ $period->period_number }} ({{ $period->fiscal_year_name }})</h1>
        <a href="{{ route('fiscal-years.periods.index', $period->fiscal_year_id) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Periods
        </a>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <form action="{{ route('accounting-periods.update', $period->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Period Number *</label>
                            <input type="number" name="period_number" class="form-control @error('period_number') is-invalid @enderror" value="{{ old('period_number', $period->period_number) }}" min="1" max="13" required>
                            @error('period_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Start Date *</label>
                            <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $period->start_date) }}" required>
                            @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">End Date *</label>
                            <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', $period->end_date) }}" required>
                            @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                   <form action="{{ route('accounting-periods.update', $period->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Inputs here -->

                        <button type="submit" class="btn btn-primary">Update Sub-Period</button>
                    </form>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection