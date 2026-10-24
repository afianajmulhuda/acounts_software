<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FiscalYearController extends Controller
{
    /**
     * Display listing of fiscal years for active company.
     */
    public function index(Request $request)
    {
        $companyId = session('active_company_id');

        $query = DB::table('fiscal_years')
            ->where('company_id', $companyId);

        if ($request->filled('search')) {
            $query->where('name', 'ILIKE', "%{$request->search}%");
        }

        $fiscalYears = $query->orderBy('start_date', 'desc')->paginate(15);

        return view('fiscal_years.index', compact('fiscalYears'));
    }

    /**
     * Show form to create new fiscal year.
     */
    public function create()
    {
        return view('fiscal_years.create');
    }

    /**
     * Store fiscal year and generate monthly accounting periods.
     */
    public function store(Request $request)
    {
        $companyId = session('active_company_id');

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        // Verify date overlap within the same company entity
        $overlap = DB::table('fiscal_years')
            ->where('company_id', $companyId)
            ->where(function ($q) use ($validated) {
                $q->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                  ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']]);
            })
            ->exists();

        if ($overlap) {
            return back()->withInput()->withErrors(['start_date' => 'Fiscal year dates overlap with an existing reporting window.']);
        }

        $fiscalYearId = Str::uuid();

        DB::transaction(function () use ($validated, $fiscalYearId, $companyId, $request) {
            // Insert Fiscal Year
            DB::table('fiscal_years')->insert([
                'id' => $fiscalYearId,
                'company_id' => $companyId,
                'name' => $validated['name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'is_closed' => false,
            ]);

            // Auto-generate 12 Monthly Accounting Periods
            $startDate = Carbon::parse($validated['start_date']);
            $endDate = Carbon::parse($validated['end_date']);
            $periodNumber = 1;

            $currentStart = $startDate->copy();
            while ($currentStart->lt($endDate)) {
                $currentEnd = $currentStart->copy()->endOfMonth();
                if ($currentEnd->gt($endDate)) {
                    $currentEnd = $endDate->copy();
                }

                DB::table('accounting_periods')->insert([
                    'id' => Str::uuid(),
                    'fiscal_year_id' => $fiscalYearId,
                    'period_number' => $periodNumber++,
                    'start_date' => $currentStart->format('Y-m-d'),
                    'end_date' => $currentEnd->format('Y-m-d'),
                    'is_closed' => false,
                ]);

                $currentStart = $currentStart->addMonth()->startOfMonth();
            }

            // SOX Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => $companyId,
                'user_id' => auth()->id(),
                'action_type' => 'CREATE_FISCAL_YEAR',
                'table_name' => 'fiscal_years',
                'record_id' => $fiscalYearId,
                'new_state' => json_encode($validated),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        return redirect()->route('fiscal-years.index')->with('success', 'Fiscal Year and monthly accounting periods created successfully.');
    }

    /**
     * Toggle close/re-open status of fiscal year.
     */
    public function toggleClose(Request $request, string $id)
    {
        $companyId = session('active_company_id');
        $fiscalYear = DB::table('fiscal_years')
            ->where('id', $id)
            ->where('company_id', $companyId)
            ->first();

        if (!$fiscalYear) {
            abort(404);
        }

        $newStatus = !$fiscalYear->is_closed;

        DB::transaction(function () use ($id, $newStatus, $companyId, $fiscalYear, $request) {
            DB::table('fiscal_years')->where('id', $id)->update(['is_closed' => $newStatus]);

            // Cascade close/reopen to underlying periods
            DB::table('accounting_periods')->where('fiscal_year_id', $id)->update(['is_closed' => $newStatus]);

            // SOX Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => $companyId,
                'user_id' => auth()->id(),
                'action_type' => $newStatus ? 'CLOSE_FISCAL_YEAR' : 'REOPEN_FISCAL_YEAR',
                'table_name' => 'fiscal_years',
                'record_id' => $id,
                'previous_state' => json_encode(['is_closed' => $fiscalYear->is_closed]),
                'new_state' => json_encode(['is_closed' => $newStatus]),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        $msg = $newStatus ? 'Fiscal year and all underlying periods closed.' : 'Fiscal year reopened.';
        return redirect()->route('fiscal-years.index')->with('success', $msg);
    }

    /**
     * Delete fiscal year.
     */
    public function destroy(Request $request, string $id)
    {
        $companyId = session('active_company_id');
        $fiscalYear = DB::table('fiscal_years')->where('id', $id)->where('company_id', $companyId)->first();

        if (!$fiscalYear) {
            abort(404);
        }

        // Check if journal entries are linked to any underlying accounting period
        $hasEntries = DB::table('journal_entries')
            ->join('accounting_periods', 'journal_entries.period_id', '=', 'accounting_periods.id')
            ->where('accounting_periods.fiscal_year_id', $id)
            ->exists();

        if ($hasEntries) {
            return back()->withErrors(['error' => 'Cannot delete a fiscal year with posted journal entries.']);
        }

        DB::transaction(function () use ($id, $companyId, $fiscalYear, $request) {
            DB::table('accounting_periods')->where('fiscal_year_id', $id)->delete();
            DB::table('fiscal_years')->where('id', $id)->delete();

            // SOX Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => $companyId,
                'user_id' => auth()->id(),
                'action_type' => 'DELETE_FISCAL_YEAR',
                'table_name' => 'fiscal_years',
                'record_id' => $id,
                'previous_state' => json_encode(['name' => $fiscalYear->name]),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        return redirect()->route('fiscal-years.index')->with('success', 'Fiscal year removed successfully.');
    }
    /**
     * Show form to edit a fiscal year.
     */
    public function edit(string $id)
    {
        $companyId = session('active_company_id');
        $fiscalYear = DB::table('fiscal_years')
            ->where('id', $id)
            ->where('company_id', $companyId)
            ->first();

        if (!$fiscalYear) {
            abort(404, 'Fiscal Year not found.');
        }

        return view('fiscal_years.edit', compact('fiscalYear'));
    }

    /**
     * Update fiscal year details.
     */
    public function update(Request $request, string $id)
    {
        $companyId = session('active_company_id');

        $fiscalYear = DB::table('fiscal_years')
            ->where('id', $id)
            ->where('company_id', $companyId)
            ->first();

        if (!$fiscalYear) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        DB::transaction(function () use ($validated, $id, $fiscalYear, $companyId, $request) {
            DB::table('fiscal_years')->where('id', $id)->update([
                'name' => $validated['name'],
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
            ]);

            // SOX Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => $companyId,
                'user_id' => auth()->id(),
                'action_type' => 'UPDATE_FISCAL_YEAR',
                'table_name' => 'fiscal_years',
                'record_id' => $id,
                'previous_state' => json_encode(['name' => $fiscalYear->name, 'start' => $fiscalYear->start_date, 'end' => $fiscalYear->end_date]),
                'new_state' => json_encode($validated),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        return redirect()->route('fiscal-years.index')->with('success', 'Fiscal year updated successfully.');
    }
}