<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TaxRateController extends Controller
{
    /**
     * Display a listing of tax rates.
     */
    public function index(Request $request)
    {
        $companyId = session('active_company_id');

        $query = DB::table('tax_rates')
            ->leftJoin('accounts', 'tax_rates.tax_account_id', '=', 'accounts.id')
            ->where('tax_rates.company_id', $companyId)
            ->select(
                'tax_rates.*',
                'accounts.code as account_code',
                'accounts.name as account_name'
            );

        if ($request->filled('search')) {
            $search =$request->search;
            $query->where('tax_rates.code', 'ILIKE', "\%{$search}%");
        }

        $taxRates =$query->orderBy('tax_rates.code', 'asc')->paginate(15);

        return view('tax_rates.index', compact('taxRates'));
    }

    /**
     * Show form to create a new tax rate.
     */
    public function create()
    {
        $companyId = session('active_company_id');

        // Fetch active GL accounts for tax linking (typically Liability/Asset types)
        $taxAccounts = DB::table('accounts')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->whereIn('account_type', ['LIABILITY', 'ASSET'])
            ->orderBy('code', 'asc')
            ->get();

        return view('tax_rates.create', compact('taxAccounts'));
    }

    /**
     * Store a newly created tax rate.
     */
    public function store(Request $request)
    {
        $companyId = session('active_company_id');

        $validated =$request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tax_rates')->where(function ($query) use ($companyId) {
                    return $query->where('company_id',$companyId);
                }),
            ],
            'percentage_rate' => 'required|numeric|min:0|max:100',
            'tax_account_id' => [
                'nullable',
                'uuid',
                Rule::exists('accounts', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('company_id',$companyId);
                }),
            ],
            'is_active' => 'boolean',
        ]);

        $taxRateId = Str::uuid();
        $decimalRate =$validated['percentage_rate'] / 100; // Convert 20% to 0.2000

        DB::transaction(function () use ($validated, $taxRateId,$decimalRate, $companyId,$request) {
            DB::table('tax_rates')->insert([
                'id' => $taxRateId,
                'company_id' => $companyId,
                'code' => strtoupper($validated['code']),
                'rate' => $decimalRate,
                'tax_account_id' => $validated['tax_account_id'] ?? null,
                'is_active' => $request->has('is_active'),
            ]);

            // SOX Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => $companyId,
                'user_id' => auth()->id(),
                'action_type' => 'CREATE_TAX_RATE',
                'table_name' => 'tax_rates',
                'record_id' => $taxRateId,
                'new_state' => json_encode([
                    'code' => strtoupper($validated['code']),
                    'rate' => $decimalRate,
                    'tax_account_id' => $validated['tax_account_id'] ?? null,
                ]),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        return redirect()->route('tax-rates.index')->with('success', 'Tax rate configured successfully.');
    }

    /**
     * Show form to edit a tax rate.
     */
    public function edit(string $id)
    {
        $companyId = session('active_company_id');

        $taxRate = DB::table('tax_rates')
            ->where('id', $id)
            ->where('company_id', $companyId)
            ->first();

        if (!$taxRate) {
            abort(404, 'Tax Rate not found.');
        }

        $taxAccounts = DB::table('accounts')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->whereIn('account_type', ['LIABILITY', 'ASSET'])
            ->orderBy('code', 'asc')
            ->get();

        return view('tax_rates.edit', compact('taxRate', 'taxAccounts'));
    }

    /**
     * Update an existing tax rate.
     */
    public function update(Request $request, string$id)
    {
        $companyId = session('active_company_id');

        $taxRate = DB::table('tax_rates')
            ->where('id', $id)
            ->where('company_id', $companyId)
            ->first();

        if (!$taxRate) {
            abort(404);
        }

        $validated =$request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tax_rates')->where(function ($query) use ($companyId) {
                    return $query->where('company_id',$companyId);
                })->ignore($id),
            ],
            'percentage_rate' => 'required|numeric|min:0|max:100',
            'tax_account_id' => [
                'nullable',
                'uuid',
                Rule::exists('accounts', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('company_id',$companyId);
                }),
            ],
            'is_active' => 'boolean',
        ]);

        $decimalRate =$validated['percentage_rate'] / 100;

        DB::transaction(function () use ($validated,$taxRate, $decimalRate,$id, $companyId,$request) {
            DB::table('tax_rates')->where('id', $id)->update([
                'code' => strtoupper($validated['code']),
                'rate' => $decimalRate,
                'tax_account_id' => $validated['tax_account_id'] ?? null,
                'is_active' => $request->has('is_active'),
            ]);

            // SOX Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => $companyId,
                'user_id' => auth()->id(),
                'action_type' => 'UPDATE_TAX_RATE',
                'table_name' => 'tax_rates',
                'record_id' => $id,
                'previous_state' => json_encode(['code' => $taxRate->code, 'rate' =>$taxRate->rate]),
                'new_state' => json_encode(['code' => strtoupper($validated['code']), 'rate' =>$decimalRate]),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        return redirect()->route('tax-rates.index')->with('success', 'Tax rate updated successfully.');
    }

    /**
     * Delete a tax rate.
     */
    public function destroy(Request $request, string$id)
    {
        $companyId = session('active_company_id');

        $taxRate = DB::table('tax_rates')
            ->where('id', $id)
            ->where('company_id', $companyId)
            ->first();

        if (!$taxRate) {
            abort(404);
        }

        // Verify if linked to journal lines
        $hasTransactions = DB::table('journal_lines')->where('tax_rate_id',$id)->exists();
        if ($hasTransactions) {
            return back()->withErrors(['error' => 'Cannot delete a tax rate linked to posted journal line items.']);
        }

        DB::transaction(function () use ($id,$taxRate, $companyId,$request) {
            DB::table('tax_rates')->where('id', $id)->delete();

            // SOX Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => $companyId,
                'user_id' => auth()->id(),
                'action_type' => 'DELETE_TAX_RATE',
                'table_name' => 'tax_rates',
                'record_id' => $id,
                'previous_state' => json_encode(['code' => $taxRate->code, 'rate' =>$taxRate->rate]),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        return redirect()->route('tax-rates.index')->with('success', 'Tax rate deleted successfully.');
    }
}