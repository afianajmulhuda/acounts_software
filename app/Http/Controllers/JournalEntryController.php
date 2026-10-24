<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class JournalEntryController extends Controller
{
    /**
     * Display a listing of journal entries.
     */
    public function index(Request $request)
    {
        $companyId = session('active_company_id');

        $query = DB::table('journal_entries')
            ->join('users', 'journal_entries.created_by_user_id', '=', 'users.id')
            ->join('accounting_periods', 'journal_entries.period_id', '=', 'accounting_periods.id')
            ->where('journal_entries.company_id', $companyId)
            ->select(
                'journal_entries.*',
                'users.first_name',
                'users.last_name',
                'accounting_periods.period_number'
            );

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('journal_entries.entry_number', 'ILIKE', "%{$search}%")
                  ->orWhere('journal_entries.description', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('journal_entries.status', $request->status);
        }

        $entries = $query->orderBy('journal_entries.posting_date', 'desc')
            ->orderBy('journal_entries.created_at', 'desc')
            ->paginate(15);

        return view('journal_entries.index', compact('entries'));
    }

    /**
     * Show form to create a new double-entry journal entry.
     */
    public function create()
    {
        $companyId = session('active_company_id');

        $accounts = DB::table('accounts')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('code', 'asc')
            ->get();

        $parties = DB::table('parties')
            ->where('company_id', $companyId)
            ->orderBy('legal_name', 'asc')
            ->get();

        $taxRates = DB::table('tax_rates')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('code', 'asc')
            ->get();

        return view('journal_entries.create', compact('accounts', 'parties', 'taxRates'));
    }

    /**
     * Store and validate a double-entry journal post.
     */
    public function store(Request $request)
    {
        $companyId = session('active_company_id');

        $validated = $request->validate([
            'posting_date' => 'required|date',
            'document_date' => 'required|date',
            'source_document_type' => 'nullable|string|max:50',
            'source_document_id' => 'nullable|uuid',
            'description' => 'required|string',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|uuid|exists:accounts,id',
            'lines.*.party_id' => 'nullable|uuid|exists:parties,id',
            'lines.*.tax_rate_id' => 'nullable|uuid|exists:tax_rates,id',
            'lines.*.description' => 'nullable|string',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
        ]);

        // 1. Verify Active Accounting Period
        $postingDate = $validated['posting_date'];
        $period = DB::table('accounting_periods')
            ->join('fiscal_years', 'accounting_periods.fiscal_year_id', '=', 'fiscal_years.id')
            ->where('fiscal_years.company_id', $companyId)
            ->where('accounting_periods.start_date', '<=', $postingDate)
            ->where('accounting_periods.end_date', '>=', $postingDate)
            ->where('accounting_periods.is_closed', false)
            ->where('fiscal_years.is_closed', false)
            ->select('accounting_periods.id')
            ->first();

        if (!$period) {
            return back()->withInput()->withErrors(['posting_date' => 'Posting date falls outside an open accounting period or fiscal year.']);
        }

        // 2. Validate Double-Entry Balance (Total Debit must equal Total Credit)
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($validated['lines'] as $line) {
            $totalDebit += (float) $line['debit'];
            $totalCredit += (float) $line['credit'];
        }

        if (abs($totalDebit - $totalCredit) > 0.0001) {
            return back()->withInput()->withErrors([
                'error' => "Double-entry out of balance! Total Debits (" . number_format($totalDebit, 2) . ") must equal Total Credits (" . number_format($totalCredit, 2) . ")."
            ]);
        }

        $journalId = Str::uuid();

        // 3. Generate Sequential Entry Number (JE-YYYY-XXXX)
        $year = Carbon::parse($postingDate)->format('Y');
        $lastSequence = DB::table('journal_entries')
            ->where('company_id', $companyId)
            ->where('entry_number', 'LIKE', "JE-{$year}-%")
            ->count();

        $entryNumber = sprintf("JE-%s-%04d", $year, $lastSequence + 1);

        DB::transaction(function () use ($validated, $journalId, $entryNumber, $period, $companyId, $request) {
            // Insert Header
            DB::table('journal_entries')->insert([
                'id' => $journalId,
                'company_id' => $companyId,
                'period_id' => $period->id,
                'entry_number' => $entryNumber,
                'posting_date' => $validated['posting_date'],
                'document_date' => $validated['document_date'],
                'source_document_type' => $validated['source_document_type'] ?? 'MANUAL',
                'source_document_id' => $validated['source_document_id'] ?? null,
                'status' => 'POSTED',
                'description' => $validated['description'],
                'created_by_user_id' => auth()->id(),
                'created_at' => now(),
            ]);
            $fxRate =1.00000 ?? 1.000000;
            // Insert Line Items
            foreach ($validated['lines'] as $index => $line) {
                DB::table('journal_lines')->insert([
                    'id' => Str::uuid(),
                    'entry_id' => $journalId,
                    'account_id' => $line['account_id'],
                    'party_id' => $line['party_id'] ?? null,
                    'tax_rate_id' => $line['tax_rate_id'] ?? null,
                    'line_number' => $index + 1,
                    'line_description' => $line['description'] ?? $validated['description'],
                    'txn_debit' => $line['debit'],
                    'txn_credit' => $line['credit'],
                    'txn_currency_code' => $line['txn_currency_code'] ?? 'USD',
                    'fx_rate' => $fxRate,
                    'base_debit' => $line['debit'] * $fxRate,
                    'base_credit' => $line['credit'] * $fxRate
                ]);
            }

            // SOX Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => $companyId,
                'user_id' => auth()->id(),
                'action_type' => 'POST_JOURNAL_ENTRY',
                'table_name' => 'journal_entries',
                'record_id' => $journalId,
                'new_state' => json_encode([
                    'entry_number' => $entryNumber,
                    'posting_date' => $validated['posting_date'],
                    'total_amount' => $totalDebit ?? 0,
                ]),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        return redirect()->route('journal-entries.index')->with('success', "Journal Entry {$entryNumber} posted successfully.");
    }

    /**
     * Display detailed journal entry with line items.
     */
    public function show(string $id)
    {
        $companyId = session('active_company_id');

        $entry = DB::table('journal_entries')
            ->join('users', 'journal_entries.created_by_user_id', '=', 'users.id')
            ->join('accounting_periods', 'journal_entries.period_id', '=', 'accounting_periods.id')
            ->where('journal_entries.id', $id)
            ->where('journal_entries.company_id', $companyId)
            ->select(
                'journal_entries.*',
                'users.first_name',
                'users.last_name',
                'accounting_periods.period_number'
            )
            ->first();

        if (!$entry) {
            abort(404, 'Journal Entry not found.');
        }

        $lines = DB::table('journal_lines')
            ->join('accounts', 'journal_lines.account_id', '=', 'accounts.id')
            ->leftJoin('parties', 'journal_lines.party_id', '=', 'parties.id')
            ->leftJoin('tax_rates', 'journal_lines.tax_rate_id', '=', 'tax_rates.id')
            ->where('journal_lines.entry_id', $id)
            ->select(
                'journal_lines.*',
                'accounts.code as account_code',
                'accounts.name as account_name',
                'parties.legal_name as party_name',
                'tax_rates.code as tax_code'
            )
            ->orderBy('journal_lines.line_number', 'asc')
            ->get();

        return view('journal_entries.show', compact('entry', 'lines'));
    }
}