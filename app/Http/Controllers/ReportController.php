<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Display Trial Balance Report.
     */
    public function trialBalance(Request $request)
    {
        $companyId = session('active_company_id');
        $asOfDate = $request->input('as_of_date', date('Y-m-d'));

        // Query accounts with net debit and credit sums up to selected as-of date
        $accounts = DB::table('accounts')
            ->leftJoin('journal_lines', 'accounts.id', '=', 'journal_lines.account_id')
            ->leftJoin('journal_entries', 'journal_lines.entry_id', '=', 'journal_entries.id')
            ->where('accounts.company_id', $companyId)
            ->where(function ($q) use ($asOfDate) {
                $q->whereNull('journal_entries.id')
                  ->orWhere(function ($sub) use ($asOfDate) {
                      $sub->where('journal_entries.status', 'POSTED')
                          ->where('journal_entries.posting_date', '<=', $asOfDate);
                  });
            })
            ->select(
                'accounts.id',
                'accounts.code',
                'accounts.name',
                'accounts.account_type',
                'accounts.normal_balance',
                DB::raw('COALESCE(SUM(journal_lines.txn_debit), 0) as total_debit'),
                DB::raw('COALESCE(SUM(journal_lines.txn_credit), 0) as total_credit')
            )
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name', 'accounts.account_type', 'accounts.normal_balance')
            ->orderBy('accounts.code', 'asc')
            ->get();

        $grandTotalDebit = $accounts->sum('total_debit');
        $grandTotalCredit = $accounts->sum('total_credit');

        return view('reports.trial_balance', compact('accounts', 'asOfDate', 'grandTotalDebit', 'grandTotalCredit'));
    }

    /**
     * Display General Ledger Detail for a specific account.
     */
    public function generalLedger(Request $request)
    {
        $companyId = session('active_company_id');
        $accountId = $request->input('account_id');
        $fromDate = $request->input('from_date', date('Y-01-01'));
        $toDate = $request->input('to_date', date('Y-m-d'));

        $accounts = DB::table('accounts')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('code', 'asc')
            ->get();

        $selectedAccount = null;
        $lines = collect();
        $openingBalance = 0;

        if ($accountId) {
            $selectedAccount = DB::table('accounts')
                ->where('id', $accountId)
                ->where('company_id', $companyId)
                ->first();

            if ($selectedAccount) {
                // Calculate Opening Balance prior to from_date
                $openingSums = DB::table('journal_lines')
                    ->join('journal_entries', 'journal_lines.entry_id', '=', 'journal_entries.id')
                    ->where('journal_lines.account_id', $accountId)
                    ->where('journal_entries.company_id', $companyId)
                    ->where('journal_entries.status', 'POSTED')
                    ->where('journal_entries.posting_date', '<', $fromDate)
                    ->select(
                        DB::raw('COALESCE(SUM(journal_lines.txn_debit), 0) as dr'),
                        DB::raw('COALESCE(SUM(journal_lines.txn_credit), 0) as cr')
                    )
                    ->first();

                $openingBalance = ($selectedAccount->normal_balance === 'DEBIT')
                    ? ($openingSums->dr - $openingSums->cr)
                    : ($openingSums->cr - $openingSums->dr);

                // Ledger Movement within date range
                $lines = DB::table('journal_lines')
                    ->join('journal_entries', 'journal_lines.entry_id', '=', 'journal_entries.id')
                    ->leftJoin('parties', 'journal_lines.party_id', '=', 'parties.id')
                    ->where('journal_lines.account_id', $accountId)
                    ->where('journal_entries.company_id', $companyId)
                    ->where('journal_entries.status', 'POSTED')
                    ->whereBetween('journal_entries.posting_date', [$fromDate, $toDate])
                    ->select(
                        'journal_entries.entry_number',
                        'journal_entries.posting_date',
                        'journal_entries.description as header_description',
                        'journal_lines.line_description as line_description',
                        'journal_lines.txn_debit',
                        'journal_lines.txn_credit',
                        'parties.legal_name as party_name'
                    )
                    ->orderBy('journal_entries.posting_date', 'asc')
                    ->get();
            }
        }

        return view('reports.general_ledger', compact('accounts', 'selectedAccount', 'lines', 'openingBalance', 'fromDate', 'toDate', 'accountId'));
    }

    /**
     * Display Financial Statements (Balance Sheet & Income Statement).
     */
    public function balanceSheet(Request $request)
    {
        $companyId = session('active_company_id');
        $asOfDate = $request->input('as_of_date', date('Y-m-d'));

        $balances = DB::table('accounts')
            ->leftJoin('journal_lines', 'accounts.id', '=', 'journal_lines.account_id')
            ->leftJoin('journal_entries', 'journal_lines.entry_id', '=', 'journal_entries.id')
            ->where('accounts.company_id', $companyId)
            ->where(function ($q) use ($asOfDate) {
                $q->whereNull('journal_entries.id')
                  ->orWhere(function ($sub) use ($asOfDate) {
                      $sub->where('journal_entries.status', 'POSTED')
                          ->where('journal_entries.posting_date', '<=', $asOfDate);
                  });
            })
            ->select(
                'accounts.account_type',
                'accounts.normal_balance',
                DB::raw('COALESCE(SUM(journal_lines.txn_debit), 0) as total_debit'),
                DB::raw('COALESCE(SUM(journal_lines.txn_credit), 0) as total_credit')
            )
            ->groupBy('accounts.account_type', 'accounts.normal_balance')
            ->get();

        $reportData = [
            'ASSET' => 0,
            'LIABILITY' => 0,
            'EQUITY' => 0,
            'REVENUE' => 0,
            'EXPENSE' => 0,
        ];

        foreach ($balances as $b) {
            $net = ($b->normal_balance === 'DEBIT')
                ? ($b->total_debit - $b->total_credit)
                : ($b->total_credit - $b->total_debit);

            $reportData[$b->account_type] = $net;
        }

        // Net Income calculation (Revenue - Expense)
        $netIncome = $reportData['REVENUE'] - $reportData['EXPENSE'];
        $totalEquityAndLiabilities = $reportData['LIABILITY'] + $reportData['EQUITY'] + $netIncome;

        return view('reports.balance_sheet', compact('reportData', 'netIncome', 'totalEquityAndLiabilities', 'asOfDate'));
    }
    /**
     * Export Trial Balance as CSV stream.
     */
    public function exportTrialBalanceCsv(Request $request)
    {
        $companyId = session('active_company_id');
        $asOfDate = $request->input('as_of_date', date('Y-m-d'));

        $accounts = $this->getTrialBalanceData($companyId, $asOfDate);

        $filename = "Trial_Balance_{$asOfDate}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($accounts) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Account Code', 'Account Name', 'Type', 'Normal Balance', 'Debit ($)', 'Credit ($)']);

            foreach ($accounts as $acc) {
                fputcsv($file, [
                    $acc->code,
                    $acc->name,
                    $acc->account_type,
                    $acc->normal_balance,
                    number_format($acc->total_debit, 2, '.', ''),
                    number_format($acc->total_credit, 2, '.', ''),
                ]);
            }

            fputcsv($file, ['TOTALS', '', '', '', number_format($accounts->sum('total_debit'), 2, '.', ''), number_format($accounts->sum('total_credit'), 2, '.', '')]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Trial Balance as PDF document.
     */
    public function exportTrialBalancePdf(Request $request)
    {
        $companyId = session('active_company_id');
        $asOfDate = $request->input('as_of_date', date('Y-m-d'));

        $company = DB::table('companies')->where('id', $companyId)->first();
        $accounts = $this->getTrialBalanceData($companyId, $asOfDate);

        $grandTotalDebit = $accounts->sum('total_debit');
        $grandTotalCredit = $accounts->sum('total_credit');

        $pdf = Pdf::loadView('reports.pdf.trial_balance', compact('company', 'accounts', 'asOfDate', 'grandTotalDebit', 'grandTotalCredit'));
        return $pdf->download("Trial_Balance_{$asOfDate}.pdf");
    }

    /**
     * Export General Ledger Detail as CSV stream.
     */
    public function exportGeneralLedgerCsv(Request $request)
    {
        $companyId = session('active_company_id');
        $accountId = $request->input('account_id');
        $fromDate = $request->input('from_date', date('Y-01-01'));
        $toDate = $request->input('to_date', date('Y-m-d'));

        if (!$accountId) {
            return back()->withErrors(['error' => 'Select an account to export general ledger data.']);
        }

        $account = DB::table('accounts')->where('id', $accountId)->where('company_id', $companyId)->first();
        $data = $this->getGeneralLedgerData($companyId, $account, $fromDate, $toDate);

        $filename = "GL_{$account->code}_{$fromDate}_to_{$toDate}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($account, $data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ["General Ledger: {$account->code} - {$account->name}"]);
            fputcsv($file, ['Posting Date', 'Entry #', 'Description', 'Counterparty', 'Debit ($)', 'Credit ($)', 'Running Balance ($)']);

            $running = $data['openingBalance'];
            fputcsv($file, ['', '', 'Opening Balance', '', '', '', number_format($running, 2, '.', '')]);

            foreach ($data['lines'] as $line) {
                $movement = ($account->normal_balance === 'DEBIT')
                    ? ($line->debit - $line->credit)
                    : ($line->credit - $line->debit);
                $running += $movement;

                fputcsv($file, [
                    $line->posting_date,
                    $line->entry_number,
                    $line->line_description ?? $line->header_description,
                    $line->party_name ?? 'N/A',
                    number_format($line->debit, 2, '.', ''),
                    number_format($line->credit, 2, '.', ''),
                    number_format($running, 2, '.', ''),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Helper to compute Trial Balance records.
     */
    private function getTrialBalanceData($companyId, $asOfDate)
    {
        return DB::table('accounts')
            ->leftJoin('journal_lines', 'accounts.id', '=', 'journal_lines.account_id')
            ->leftJoin('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('accounts.company_id', $companyId)
            ->where(function ($q) use ($asOfDate) {
                $q->whereNull('journal_entries.id')
                  ->orWhere(function ($sub) use ($asOfDate) {
                      $sub->where('journal_entries.status', 'POSTED')
                          ->where('journal_entries.posting_date', '<=', $asOfDate);
                  });
            })
            ->select(
                'accounts.id',
                'accounts.code',
                'accounts.name',
                'accounts.account_type',
                'accounts.normal_balance',
                DB::raw('COALESCE(SUM(journal_lines.debit), 0) as total_debit'),
                DB::raw('COALESCE(SUM(journal_lines.credit), 0) as total_credit')
            )
            ->groupBy('accounts.id', 'accounts.code', 'accounts.name', 'accounts.account_type', 'accounts.normal_balance')
            ->orderBy('accounts.code', 'asc')
            ->get();
    }

    /**
     * Helper to compute General Ledger opening balances and line items.
     */
    private function getGeneralLedgerData($companyId, $account, $fromDate, $toDate)
    {
        $openingSums = DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_lines.account_id', $account->id)
            ->where('journal_entries.company_id', $companyId)
            ->where('journal_entries.status', 'POSTED')
            ->where('journal_entries.posting_date', '<', $fromDate)
            ->select(
                DB::raw('COALESCE(SUM(journal_lines.debit), 0) as dr'),
                DB::raw('COALESCE(SUM(journal_lines.credit), 0) as cr')
            )
            ->first();

        $openingBalance = ($account->normal_balance === 'DEBIT')
            ? ($openingSums->dr - $openingSums->cr)
            : ($openingSums->cr - $openingSums->dr);

        $lines = DB::table('journal_lines')
            ->join('journal_entries', 'journal_lines.journal_entry_id', '=', 'journal_entries.id')
            ->leftJoin('parties', 'journal_lines.party_id', '=', 'parties.id')
            ->where('journal_lines.account_id', $account->id)
            ->where('journal_entries.company_id', $companyId)
            ->where('journal_entries.status', 'POSTED')
            ->whereBetween('journal_entries.posting_date', [$fromDate, $toDate])
            ->select(
                'journal_entries.entry_number',
                'journal_entries.posting_date',
                'journal_entries.description as header_description',
                'journal_lines.description as line_description',
                'journal_lines.debit',
                'journal_lines.credit',
                'parties.legal_name as party_name'
            )
            ->orderBy('journal_entries.posting_date', 'asc')
            ->get();

        return compact('openingBalance', 'lines');
    }
}