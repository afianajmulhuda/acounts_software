@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h1 class="m-0">Chart of Accounts (CoA)</h1>
        <a href="{{ route('accounts.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add Account
        </a>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <form method="GET" action="{{ route('accounts.index') }}" class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search account code or name..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="account_type" class="form-select">
                            <option value="">-- All Types --</option>
                            <option value="ASSET" {{ request('account_type') === 'ASSET' ? 'selected' : '' }}>ASSET</option>
                            <option value="LIABILITY" {{ request('account_type') === 'LIABILITY' ? 'selected' : '' }}>LIABILITY</option>
                            <option value="EQUITY" {{ request('account_type') === 'EQUITY' ? 'selected' : '' }}>EQUITY</option>
                            <option value="REVENUE" {{ request('account_type') === 'REVENUE' ? 'selected' : '' }}>REVENUE</option>
                            <option value="EXPENSE" {{ request('account_type') === 'EXPENSE' ? 'selected' : '' }}>EXPENSE</option>
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
                            <th>Code</th>
                            <th>Account Name</th>
                            <th>Type</th>
                            <th>Parent Account</th>
                            <th>Normal Balance</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($accounts as $acc)
                        <tr>
                            <td><code>{{ $acc->code }}</code></td>
                            <td><strong>{{ $acc->name }}</strong></td>
                            <td>
                                @switch($acc->account_type)
                                    @case('ASSET') <span class="badge bg-primary">ASSET</span> @break
                                    @case('LIABILITY') <span class="badge bg-warning text-dark">LIABILITY</span> @break
                                    @case('EQUITY') <span class="badge bg-info text-dark">EQUITY</span> @break
                                    @case('REVENUE') <span class="badge bg-success">REVENUE</span> @break
                                    @case('EXPENSE') <span class="badge bg-danger">EXPENSE</span> @break
                                @endswitch
                            </td>
                            <td>
                                @if($acc->parent_code)
                                    <small class="text-muted"><code>{{ $acc->parent_code }}</code> - {{ $acc->parent_name }}</small>
                                @else
                                    <span class="text-muted">&mdash;</span>
                                @endif
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $acc->normal_balance }}</span></td>
                            <td>
                                @if($acc->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('accounts.edit', $acc->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i> Edit</a>
                                <form action="{{ route('accounts.destroy', $acc->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this account from Chart of Accounts?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No GL Accounts configured for active legal entity.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $accounts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection