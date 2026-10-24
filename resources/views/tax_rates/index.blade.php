@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">Tax Rates Engine</h1>
        <a href="{{ route('tax-rates.create') }}" class="btn btn-primary">
            <i class="bi bi-percent me-1"></i> Add Tax Rate
        </a>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <form method="GET" action="{{ route('tax-rates.index') }}" class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search tax code..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-filter me-1"></i> Filter</button>
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th>Tax Code</th>
                            <th>Effective Rate</th>
                            <th>Decimal Multiplier</th>
                            <th>Linked Tax GL Account</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($taxRates as $tax)
                        <tr>
                            <td><code>{{ $tax->code }}</code></td>
                            <td><strong>{{ number_format($tax->rate * 100, 2) }}%</strong></td>
                            <td><code>{{ number_format($tax->rate, 4) }}</code></td>
                            <td>
                                @if($tax->account_code)
                                    <small><code>{{ $tax->account_code }}</code> - {{ $tax->account_name }}</small>
                                @else
                                    <span class="text-muted">&mdash;</span>
                                @endif
                            </td>
                            <td>
                                @if($tax->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('tax-rates.edit', $tax->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
                                <form action="{{ route('tax-rates.destroy', $tax->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this tax rate configuration?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No tax rates defined for active legal entity.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $taxRates->links() }}
            </div>
        </div>
    </div>
</div>
@endsection