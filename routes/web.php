<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Accounting\ChartOfAccountsController;
use App\Http\Controllers\AccountingPeriodController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\JournalEntryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanySwitchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FiscalYearController;
use App\Http\Controllers\PartyController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\TaxRateController;
use App\Http\Controllers\UserCompanyAccessController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return view('welcome');
});
// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::middleware(['auth', \App\Http\Middleware\SetCurrentCompany::class])->group(function () {
        Route::post('/company/switch', [CompanySwitchController::class, 'switch'])->name('company.switch');
        // Company CRUD Routes
        Route::get('/companies', [CompanyController::class, 'index'])->name('companies.index');
        Route::post('/companies', [CompanyController::class, 'store'])->name('companies.store');
        Route::put('/companies/{id}', [CompanyController::class, 'update'])->name('companies.update');
        Route::delete('/companies/{id}', [CompanyController::class, 'destroy'])->name('companies.destroy');

        // user
        Route::resource('users', UserController::class);
        Route::resource('roles', RoleController::class)->except(['show']);

        // User Access Management Routes
        Route::get('/users/{user}/access', [UserCompanyAccessController::class, 'edit'])->name('users.access.edit');
        Route::post('/users/{user}/access', [UserCompanyAccessController::class, 'store'])->name('users.access.store');
        Route::delete('/access/{access}', [UserCompanyAccessController::class, 'destroy'])->name('users.access.destroy');

        Route::resource('permissions', PermissionController::class)->except(['show']);
        // Role Permission Matrix Routes
        Route::get('/roles/{role}/permissions', [RolePermissionController::class, 'edit'])->name('roles.permissions.edit');
        Route::put('/roles/{role}/permissions', [RolePermissionController::class, 'update'])->name('roles.permissions.update');

        // 1. Fiscal Years Resource (Includes edit and update)
        Route::resource('fiscal-years', FiscalYearController::class)->except(['show']);
        Route::patch('/fiscal-years/{fiscal_year}/toggle-close', [FiscalYearController::class, 'toggleClose'])->name('fiscal-years.toggle-close');

        // 2. Sub-Periods Navigation
        Route::get('/fiscal-years/{fiscal_year}/periods', [AccountingPeriodController::class, 'index'])->name('fiscal-years.periods.index');

        // 3. Accounting Period Individual Controls
        Route::get('/accounting-periods/{period}/edit', [AccountingPeriodController::class, 'edit'])->name('accounting-periods.edit');
        Route::put('/accounting-periods/{period}', [AccountingPeriodController::class, 'update'])->name('accounting-periods.update');
        Route::patch('/accounting-periods/{period}/toggle-close', [AccountingPeriodController::class, 'toggleClose'])->name('accounting-periods.toggle-close');

        Route::resource('accounts', AccountController::class)->except(['show']);

        Route::resource('parties', PartyController::class)->except(['show']);

        Route::resource('tax-rates', TaxRateController::class)->except(['show']);

        Route::get('/journal-entries', [JournalEntryController::class, 'index'])->name('journal-entries.index');
        Route::get('/journal-entries/create', [JournalEntryController::class, 'create'])->name('journal-entries.create');
        Route::post('/journal-entries', [JournalEntryController::class, 'store'])->name('journal-entries.store');
        Route::get('/journal-entries/{journal_entry}', [JournalEntryController::class, 'show'])->name('journal-entries.show');

        // Audit Log & Compliance Trail Routes
        Route::get('/auditlogs', [AuditLogController::class, 'index'])->name('auditlogs.index');
        Route::get('/audit/logs/{auditLog}', [AuditLogController::class, 'show'])->name('auditlogs.show');
        // reports
        Route::get('/reports/trial-balance', [ReportController::class, 'trialBalance'])->name('reports.trial-balance');
        Route::get('/reports/general-ledger', [ReportController::class, 'generalLedger'])->name('reports.general-ledger');
        Route::get('/reports/balance-sheet', [ReportController::class, 'balanceSheet'])->name('reports.balance-sheet');

        // Trial Balance Exports
        Route::get('/reports/trial-balance/csv', [ReportController::class, 'exportTrialBalanceCsv'])->name('reports.trial-balance.csv');
        Route::get('/reports/trial-balance/pdf', [ReportController::class, 'exportTrialBalancePdf'])->name('reports.trial-balance.pdf');

        // General Ledger Exports
        Route::get('/reports/general-ledger/csv', [ReportController::class, 'exportGeneralLedgerCsv'])->name('reports.general-ledger.csv');
    });
});
