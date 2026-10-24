@extends('layouts.app')

@section('title', 'Chart of Accounts')
@section('page_title', 'Chart of Accounts (CoA)')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accounting</a></li>
    <li class="breadcrumb-item active">Chart of Accounts</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Master Ledger Accounts</h3>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createAccountModal">
                    <i class="bi bi-plus-lg me-1"></i> Add Account
                </button>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Account Name</th>
                                <th>Type</th>
                                <th>Normal Balance</th>
                                <th>Parent Account</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($accounts as $account)
                                <tr>
                                    <td class="fw-bold text-primary">{{ $account->code }}</td>
                                    <td>{{ $account->name }}</td>
                                    <td>
                                        <span class="badge 
                                            @if($account->account_type == 'ASSET') bg-info
                                            @elseif($account->account_type == 'LIABILITY') bg-warning text-dark
                                            @elseif($account->account_type == 'EQUITY') bg-primary
                                            @elseif($account->account_type == 'REVENUE') bg-success
                                            @else bg-danger @endif">
                                            {{ $account->account_type }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-outline-secondary">{{ $account->normal_balance }}</span>
                                    </td>
                                    <td>
                                        @if($account->parent_code)
                                            <small class="text-muted">{{ $account->parent_code }} - {{ $account->parent_name }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($account->is_active)
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No accounts configured yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add New Account -->
<div class="modal fade" id="createAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('accounting.coa.index') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Chart of Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Account Code</label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. 1010" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Account Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Operating Cash Account" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Account Type</label>
                            <select name="account_type" class="form-select" required>
                                <option value="ASSET">ASSET</option>
                                <option value="LIABILITY">LIABILITY</option>
                                <option value="EQUITY">EQUITY</option>
                                <option value="REVENUE">REVENUE</option>
                                <option value="EXPENSE">EXPENSE</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Normal Balance</label>
                            <select name="normal_balance" class="form-select" required>
                                <option value="DEBIT">DEBIT</option>
                                <option value="CREDIT">CREDIT</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Parent Account (Optional)</label>
                        <select name="parent_account_id" class="form-select">
                            <option value="">-- None (Top Level) --</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Account</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection