<nav class="app-header navbar navbar-expand bg-body shadow-sm">
    <div class="container-fluid">
        <!-- Sidebar Toggle -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                    <i class="bi bi-list fs-4"></i>
                </a>
            </li>
        </ul>

        <!-- Multi-Entity Switcher Form -->
        @if(isset($userCompanies) && $userCompanies->count() > 0)
        <form action="{{ route('company.switch') }}" method="POST" class="d-flex align-items-center ms-3">
            @csrf
            <i class="bi bi-building me-2 text-primary fs-5"></i>
            <select name="company_id" onchange="this.form.submit()" class="form-select form-select-sm fw-bold border-primary">
                @foreach($userCompanies as $company)
                    <option value="{{ $company->id }}" {{ session('active_company_id') == $company->id ? 'selected' : '' }}>
                        {{ $company->legal_name }} ({{ $company->base_currency_code }})
                    </option>
                @endforeach
            </select>
        </form>
        @endif

        <ul class="navbar-nav ms-auto">
            <!-- Fullscreen Toggle -->
            <li class="nav-item">
                <a class="nav-link" href="#" data-lte-toggle="fullscreen">
                    <i data-lte-icon="maximize" class="bi bi-arrows-fullscreen"></i>
                    <i data-lte-icon="minimize" class="bi bi-fullscreen-exit" style="display: none"></i>
                </a>
            </li>

            <!-- User Menu -->
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle fs-5 me-1"></i>
                    <span class="d-none d-md-inline fw-semibold">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <li class="user-header text-bg-primary">
                        <h5>{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</h5>
                        <p class="mb-0">{{ auth()->user()->job_title ?? 'Financial Accountant' }}</p>
                        <small>{{ auth()->user()->department ?? 'Finance & Accounting' }}</small>
                    </li>
                    <li class="user-footer">
                        <a href="#" class="btn btn-default btn-flat">Profile</a>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline float-end">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-flat">Sign Out</button>
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>