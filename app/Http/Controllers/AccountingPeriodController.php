<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccountingPeriodController extends Controller
{
    /**
     * Display listing of accounting periods for a given Fiscal Year.
     */
    public function index(string $fiscalYearId)
    {
        $companyId = session('active_company_id');

        $fiscalYear = DB::table('fiscal_years')
            ->where('id', $fiscalYearId)
            ->where('company_id', $companyId)
            ->first();

        if (!$fiscalYear) {
            abort(404, 'Fiscal Year not found.');
        }

        $periods = DB::table('accounting_periods')
            ->where('fiscal_year_id', $fiscalYearId)
            ->orderBy('period_number', 'asc')
            ->get();

        return view('accounting_periods.index', compact('fiscalYear', 'periods'));
    }

    /**
     * Show form to edit an accounting period's details.
     */
    public function edit(string $id)
    {
        $companyId = session('active_company_id');

        $period = DB::table('accounting_periods')
            ->join('fiscal_years', 'accounting_periods.fiscal_year_id', '=', 'fiscal_years.id')
            ->where('accounting_periods.id', $id)
            ->where('fiscal_years.company_id', $companyId)
            ->select('accounting_periods.*', 'fiscal_years.name as fiscal_year_name')
            ->first();

        if (!$period) {
            abort(404, 'Accounting Period not found.');
        }

        return view('accounting_periods.edit', compact('period'));
    }

    /**
     * Update accounting period date range and number.
     */
    public function update(Request $request, string $id)
    {
        $companyId = session('active_company_id');

        $period = DB::table('accounting_periods')
            ->join('fiscal_years', 'accounting_periods.fiscal_year_id', '=', 'fiscal_years.id')
            ->where('accounting_periods.id', $id)
            ->where('fiscal_years.company_id', $companyId)
            ->select('accounting_periods.*')
            ->first();

        if (!$period) {
            abort(404);
        }

        $validated = $request->validate([
            'period_number' => 'required|integer|min:1|max:13',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        DB::transaction(function () use ($validated, $id, $period, $companyId, $request) {
            DB::table('accounting_periods')->where('id', $id)->update([
                'period_number' => $validated['period_number'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
            ]);

            // SOX Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => $companyId,
                'user_id' => auth()->id(),
                'action_type' => 'UPDATE_ACCOUNTING_PERIOD',
                'table_name' => 'accounting_periods',
                'record_id' => $id,
                'previous_state' => json_encode(['start_date' => $period->start_date, 'end_date' => $period->end_date]),
                'new_state' => json_encode($validated),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        return redirect()->route('fiscal-years.periods.index', $period->fiscal_year_id)
            ->with('success', 'Accounting period updated successfully.');
    }

    /**
     * Toggle lock status on an individual accounting period.
     */
    public function toggleClose(Request $request, string $id)
    {
        $companyId = session('active_company_id');

        $period = DB::table('accounting_periods')
            ->join('fiscal_years', 'accounting_periods.fiscal_year_id', '=', 'fiscal_years.id')
            ->where('accounting_periods.id', $id)
            ->where('fiscal_years.company_id', $companyId)
            ->select('accounting_periods.*')
            ->first();

        if (!$period) {
            abort(404);
        }

        $newStatus = !$period->is_closed;

        DB::transaction(function () use ($id, $newStatus, $period, $companyId, $request) {
            DB::table('accounting_periods')->where('id', $id)->update(['is_closed' => $newStatus]);

            // SOX Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => $companyId,
                'user_id' => auth()->id(),
                'action_type' => $newStatus ? 'CLOSE_PERIOD' : 'REOPEN_PERIOD',
                'table_name' => 'accounting_periods',
                'record_id' => $id,
                'previous_state' => json_encode(['is_closed' => $period->is_closed]),
                'new_state' => json_encode(['is_closed' => $newStatus]),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        $msg = $newStatus ? "Period #{$period->period_number} locked." : "Period #{$period->period_number} reopened for posting.";
        return back()->with('success', $msg);
    }
}