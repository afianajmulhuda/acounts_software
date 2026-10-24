<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
    <!-- Brand / Logo -->
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}" class="brand-link">
            <i class="bi bi-calculator-fill text-warning me-2 fs-4"></i>
            <span class="brand-text fw-bold">Enterprise ERP</span>
        </a>
    </div>

    <!-- Sidebar Scrollable Container -->
    <div class="sidebar-wrapper overflow-y-auto" style="max-height: calc(100vh - 3.5rem); overflow-x: hidden; scroll-behavior: smooth;">
        <nav class="mt-2 mb-4">
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu">
                
                <!-- Dashboard (All Authenticated Users) -->
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-speedometer2"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <!-- Organization Master Section -->
                @hasAnyPermission(['companies:view', 'users:view', 'roles:view', 'fiscal_years:view'])
                <li class="nav-header text-uppercase fs-7 text-muted">Organization Master</li>

                @hasPermission('companies:view')
                <li class="nav-item">
                    <a href="{{ route('companies.index') }}" class="nav-link {{ request()->routeIs('companies.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-building"></i>
                        <p>Legal Entities</p>
                    </a>
                </li>
                @endhasPermission

                @hasPermission('users:view')
                <li class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-people"></i>
                        <p>User Management</p>
                    </a>
                </li>
                @endhasPermission

                @hasPermission('roles:view')
                <li class="nav-item">
                    <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-shield-lock"></i>
                        <p>Roles & Access Control</p>
                    </a>
                </li>
                @endhasPermission

                @hasPermission('fiscal_years:view')
                <li class="nav-item">
                    <a href="{{ route('fiscal-years.index') }}" class="nav-link {{ request()->routeIs('fiscal-years.*', 'accounting-periods.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-calendar-range"></i>
                        <p>Fiscal Years & Periods</p>
                    </a>
                </li>
                @endhasPermission
                @endhasAnyPermission

                <!-- General Ledger Section -->
                @hasAnyPermission(['accounts:view', 'journal_entries:view'])
                <li class="nav-header text-uppercase fs-7 text-muted">General Ledger</li>

                @hasPermission('accounts:view')
                <li class="nav-item">
                    <a href="{{ route('accounts.index') }}" class="nav-link {{ request()->routeIs('accounts.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-diagram-3-fill"></i>
                        <p>Chart of Accounts</p>
                    </a>
                </li>
                @endhasPermission

                @hasPermission('journal_entries:view')
                <li class="nav-item">
                    <a href="{{ route('journal-entries.index') }}" class="nav-link {{ request()->routeIs('journal-entries.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-journal-text"></i>
                        <p>Journal Entries</p>
                    </a>
                </li>
                @endhasPermission
                @endhasAnyPermission

                <!-- Financial Reports Section -->
                @hasAnyPermission(['reports:view'])
                <li class="nav-header text-uppercase fs-7 text-muted">Financial Reports</li>

                <li class="nav-item">
                    <a href="{{ route('reports.trial-balance') }}" class="nav-link {{ request()->routeIs('reports.trial-balance*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-file-earmark-bar-graph"></i>
                        <p>Trial Balance</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('reports.general-ledger') }}" class="nav-link {{ request()->routeIs('reports.general-ledger*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-journal-bookmark-fill"></i>
                        <p>General Ledger Drilldown</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('reports.balance-sheet') }}" class="nav-link {{ request()->routeIs('reports.balance-sheet*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-pie-chart-fill"></i>
                        <p>Balance Sheet</p>
                    </a>
                </li>
                @endhasAnyPermission

                <!-- Sub-Ledgers Section -->
                @hasAnyPermission(['parties:view', 'tax_rates:view'])
                <li class="nav-header text-uppercase fs-7 text-muted">Sub-Ledgers</li>

                @hasPermission('parties:view')
                <li class="nav-item">
                    <a href="{{ route('parties.index') }}" class="nav-link {{ request()->routeIs('parties.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-people-fill"></i>
                        <p>Customers & Vendors</p>
                    </a>
                </li>
                @endhasPermission

                @hasPermission('tax_rates:view')
                <li class="nav-item">
                    <a href="{{ route('tax-rates.index') }}" class="nav-link {{ request()->routeIs('tax-rates.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-percent"></i>
                        <p>Tax Rates Engine</p>
                    </a>
                </li>
                @endhasPermission
                @endhasAnyPermission

                <!-- System & SOX Compliance Trail -->
                @if(auth()->user()->is_super_admin || App\Services\PermissionService::hasPermission('audit_logs:view'))
                <li class="nav-header text-uppercase fs-7 text-muted">System & Audit</li>

                <li class="nav-item">
                    <a href="{{ route('auditlogs.index') }}" class="nav-link {{ request()->routeIs('auditlogs.*') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-shield-check"></i>
                        <p>Audit Logs (SOX)</p>
                    </a>
                </li>
                @endif

            </ul>
        </nav>
    </div>
</aside>