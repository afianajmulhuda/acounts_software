@extends('layouts.app')

@section('title', 'Companies Management')
@section('page_title', 'Legal Entities')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Master Data</a></li>
    <li class="breadcrumb-item active">Legal Entities</li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        
        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $errors->first() }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card card-outline card-primary shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Registered Companies</h3>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createCompanyModal">
                    <i class="bi bi-building-add me-1"></i> Add Company
                </button>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Legal Name</th>
                                <th>Tax Identifier (VAT/EIN)</th>
                                <th>Base Currency</th>
                                <th>Country</th>
                                <th>Created At</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($companies as $company)
                                <tr>
                                    <td class="fw-bold text-primary">{{ $company->legal_name }}</td>
                                    <td><code>{{ $company->tax_identifier }}</code></td>
                                    <td><span class="badge bg-info text-dark">{{ $company->base_currency_code }}</span></td>
                                    <td><span class="badge bg-secondary">{{ $company->country_code }}</span></td>
                                    <td>{{ \Carbon\Carbon::parse($company->created_at)->format('Y-m-d H:i') }}</td>
                                    <td class="text-center">
                                        <!-- Edit Trigger -->
                                        <button class="btn btn-sm btn-outline-warning me-1" 
                                                onclick="openEditModal('{{ $company->id }}', '{{ $company->legal_name }}', '{{ $company->tax_identifier }}', '{{ $company->base_currency_code }}', '{{ $company->country_code }}')">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        
                                        <!-- Delete Trigger -->
                                        <button class="btn btn-sm btn-outline-danger" 
                                                onclick="openDeleteModal('{{ $company->id }}', '{{ $company->legal_name }}')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-building-x fs-2 d-block mb-2"></i>
                                        No companies registered yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if($companies->hasPages())
                <div class="card-footer clearfix">
                    <div class="float-end">{{ $companies->links() }}</div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: Create Company -->
<div class="modal fade" id="createCompanyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('companies.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Legal Entity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Legal Name</label>
                        <input type="text" name="legal_name" class="form-control" placeholder="e.g. Acme Global Inc." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tax Identifier</label>
                        <input type="text" name="tax_identifier" class="form-control" placeholder="e.g. US123456789 / VAT98765" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Base Currency Code</label>
                            <input type="text" name="base_currency_code" class="form-control text-uppercase" placeholder="USD" maxlength="3" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Country Code</label>
                            <input type="text" name="country_code" class="form-control text-uppercase" placeholder="US" maxlength="2" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Company</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Company -->
<div class="modal fade" id="editCompanyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="editCompanyForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Legal Entity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Legal Name</label>
                        <input type="text" name="legal_name" id="edit_legal_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tax Identifier</label>
                        <input type="text" name="tax_identifier" id="edit_tax_identifier" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Base Currency Code</label>
                            <input type="text" name="base_currency_code" id="edit_base_currency_code" class="form-control text-uppercase" maxlength="3" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Country Code</label>
                            <input type="text" name="country_code" id="edit_country_code" class="form-control text-uppercase" maxlength="2" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update Company</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Delete Company -->
<div class="modal fade" id="deleteCompanyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="deleteCompanyForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title fw-bold">Confirm Deletion</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the legal entity <strong id="delete_company_name"></strong>?</p>
                    <small class="text-muted">This action cannot be undone and will fail if journal entries exist for this entity.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Entity</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openEditModal(id, name, taxId, currency, country) {
        document.getElementById('editCompanyForm').action = `/companies/${id}`;
        document.getElementById('edit_legal_name').value = name;
        document.getElementById('edit_tax_identifier').value = taxId;
        document.getElementById('edit_base_currency_code').value = currency;
        document.getElementById('edit_country_code').value = country;
        
        new bootstrap.Modal(document.getElementById('editCompanyModal')).show();
    }

    function openDeleteModal(id, name) {
        document.getElementById('deleteCompanyForm').action = `/companies/${id}`;
        document.getElementById('delete_company_name').innerText = name;
        
        new bootstrap.Modal(document.getElementById('deleteCompanyModal')).show();
    }
</script>
@endpush