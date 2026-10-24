<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JournalEntryController extends Controller
{
    public function index()
    {
        $companyId = session('active_company_id');

        $entries = DB::table('journal_entries')
            ->where('company_id', $companyId)
            ->orderBy('posting_date', 'desc')
            ->paginate(15);

        return view('accounting.journals.index', compact('entries'));
    }

    public function create()
    {
        $companyId = session('active_company_id');

        $accounts = DB::table('accounts')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $parties = DB::table('parties')->where('company_id', $companyId)->get();
        $taxRates = DB::table('tax_rates')->where('company_id', $companyId)->where('is_active', true)->get();

        return view('accounting.journals.create', compact('accounts', 'parties', 'taxRates'));
    }

    public function store(Request $request)
    {
        $totalDebit = 0;
        $totalCredit = 0;
        $companyId = session('active_company_id');

        $request->validate([
            'posting_date' => 'required|date',
            'description' => 'required|string|max:1000',
            'lines' => 'required|array|min:2', // Double-entry requires min 2 lines
            'lines.*.account_id' => 'required|uuid',
            'lines.*.txn_debit' => 'required|numeric|min:0',
            'lines.*.txn_credit' => 'required|numeric|min:0',
        ]);

        // 1. Verify Active Fiscal Period
        $period = DB::table('accounting_periods')
            ->join('fiscal_years', 'accounting_periods.fiscal_year_id', '=', 'fiscal_years.id')
            ->where('fiscal_years.company_id', $companyId)
            ->where('accounting_periods.is_closed', false)
            ->whereDate('accounting_periods.start_date', '<=', $request->posting_date)
            ->whereDate('accounting_periods.end_date', '>=', $request->posting_date)
            ->select('accounting_periods.id as period_id')
            ->first();

        if (!$period) {
            return back()->withInput()->withErrors(['posting_date' => 'Posting date falls within a closed or unconfigured accounting period.']);
        }

        // 2. Validate Double-Entry Mathematical Balance (Debits = Credits)
        $totalDebit = array_sum(array_column($request->lines, 'txn_debit'));
        $totalCredit = array_sum(array_column($request->lines, 'txn_credit'));

        if (abs($totalDebit - $totalCredit) > 0.0001) {
            return back()->withInput()->withErrors(['lines' => 'Unbalanced Journal Entry! Total Debits must equal Total Credits.']);
        }

        // 3. Save Entry and Lines transactional execution
        // FIXED: Added $totalDebit to the 'use' clause below
        DB::transaction(function () use ($request, $companyId, $period, $totalDebit) {
            $entryId = Str::uuid();
            $entryNumber = 'JE-' . date('Ym') . '-' . rand(1000, 9999);

            // Insert Header
            DB::table('journal_entries')->insert([
                'id' => $entryId,
                'company_id' => $companyId,
                'period_id' => $period->period_id,
                'entry_number' => $entryNumber,
                'posting_date' => $request->posting_date,
                'document_date' => $request->posting_date,
                'source_document_type' => 'MANUAL',
                'status' => 'POSTED',
                'description' => $request->description,
                'created_by_user_id' => auth()->id(),
                'created_at' => now(),
            ]);

            // Get Company Base Currency Code
            $company = DB::table('companies')->where('id', $companyId)->first();

            // Insert Lines
            foreach ($request->lines as $line) {
                $fxRate = $line['fx_rate'] ?? 1.000000;
                
                DB::table('journal_lines')->insert([
                    'id' => Str::uuid(),
                    'entry_id' => $entryId,
                    'account_id' => $line['account_id'],
                    'party_id' => $line['party_id'] ?? null,
                    'tax_rate_id' => $line['tax_rate_id'] ?? null,
                    'txn_currency_code' => $line['txn_currency_code'] ?? $company->base_currency_code,
                    'txn_debit' => $line['txn_debit'],
                    'txn_credit' => $line['txn_credit'],
                    'fx_rate' => $fxRate,
                    'base_debit' => $line['txn_debit'] * $fxRate,
                    'base_credit' => $line['txn_credit'] * $fxRate,
                    'line_description' => $line['line_description'] ?? $request->description,
                ]);
            }

            // Record Audit Log (SOX Compliance)
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => $companyId,
                'user_id' => auth()->id(),
                'action_type' => 'POST_JOURNAL',
                'table_name' => 'journal_entries',
                'record_id' => $entryId,
                'new_state' => json_encode(['entry_number' => $entryNumber, 'amount' => $totalDebit]),
                'ip_address' => request()->ip(),
                'timestamp' => now(),
            ]);
        });

        return redirect()->route('accounting.journals.index')->with('success', 'Journal Entry posted successfully!');
    }
    public function details($id)
    {
        $lines = DB::table('journal_lines')
            ->join('accounts', 'journal_lines.account_id', '=', 'accounts.id')
            ->leftJoin('parties', 'journal_lines.party_id', '=', 'parties.id')
            ->where('journal_lines.entry_id', $id)
            ->select(
                'accounts.code as account_code',
                'accounts.name as account_name',
                'parties.legal_name as party_name',
                'journal_lines.txn_debit',
                'journal_lines.txn_credit',
                'journal_lines.line_description'
            )
            ->get();

        return response()->json(['lines' => $lines]);
    }
}