@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">Counterparties (Customers & Vendors)</h1>
        <a href="{{ route('parties.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i> Add Counterparty
        </a>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <form method="GET" action="{{ route('parties.index') }}" class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search name or tax ID..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="party_type" class="form-select">
                            <option value="">-- All Sub-ledger Types --</option>
                            <option value="CUSTOMER" {{ request('party_type') === 'CUSTOMER' ? 'selected' : '' }}>CUSTOMER</option>
                            <option value="VENDOR" {{ request('party_type') === 'VENDOR' ? 'selected' : '' }}>VENDOR</option>
                            <option value="BOTH" {{ request('party_type') === 'BOTH' ? 'selected' : '' }}>BOTH</option>
                        </select>
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
                            <th>Legal Name</th>
                            <th>Type</th>
                            <th>Tax Identification Number</th>
                            <th>Country</th>
                            <th>Default Currency</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($parties as $party)
                        <tr>
                            <td><strong>{{ $party->legal_name }}</strong></td>
                            <td>
                                @if($party->party_type === 'CUSTOMER')
                                    <span class="badge bg-success">CUSTOMER</span>
                                @elseif($party->party_type === 'VENDOR')
                                    <span class="badge bg-warning text-dark">VENDOR</span>
                                @else
                                    <span class="badge bg-info text-dark">CUSTOMER & VENDOR</span>
                                @endif
                            </td>
                            <td><code>{{ $party->tax_number ?? 'N/A' }}</code></td>
                            <td><span class="badge bg-light text-dark border">{{ $party->country_code }}</span></td>
                            <td><code>{{ $party->currency_code }}</code></td>
                            <td class="text-end">
                                <a href="{{ route('parties.edit', $party->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
                                <form action="{{ route('parties.destroy', $party->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this counterparty?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No counterparties defined for active legal entity.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $parties->links() }}
            </div>
        </div>
    </div>
</div>
@endsection