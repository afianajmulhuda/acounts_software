<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    /**
     * Display a hierarchical listing of accounts for the active company.
     */
    public function index(Request $request)
    {
        $companyId = session('active_company_id');

        $query = DB::table('accounts')
            ->leftJoin('accounts as parent', 'accounts.parent_account_id', '=', 'parent.id')
            ->where('accounts.company_id', $companyId)
            ->select(
                'accounts.*',
                'parent.code as parent_code',
                'parent.name as parent_name'
            );

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('accounts.code', 'ILIKE', "%{$search}%")
                  ->orWhere('accounts.name', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('account_type')) {
            $query->where('accounts.account_type', $request->account_type);
        }

        $accounts = $query->orderBy('accounts.code', 'asc')->paginate(20);

        return view('accounts.index', compact('accounts'));
    }

    /**
     * Show form for creating a new account.
     */
    public function create()
    {
        $companyId = session('active_company_id');
        // dd($companyId);
        $parentAccounts = DB::table('accounts')
            ->where('company_id', $companyId)
            ->orderBy('code', 'asc')
            ->get();

        return view('accounts.create', compact('parentAccounts'));
    }

    /**
     * Store a newly created account.
     */
    public function store(Request $request)
    {
        $companyId = session('active_company_id');

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('accounts')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'name' => 'required|string|max:255',
            'parent_account_id' => [
                'nullable',
                'uuid',
                Rule::exists('accounts', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'account_type' => 'required|string|in:ASSET,LIABILITY,EQUITY,REVENUE,EXPENSE',
            'normal_balance' => 'required|string|in:DEBIT,CREDIT',
            'is_active' => 'boolean',
        ]);

        $accountId = Str::uuid();

        DB::transaction(function () use ($validated, $accountId, $companyId, $request) {
            DB::table('accounts')->insert([
                'id' => $accountId,
                'company_id' => $companyId,
                'code' => $validated['code'],
                'name' => $validated['name'],
                'parent_account_id' => $validated['parent_account_id'] ?? null,
                'account_type' => $validated['account_type'],
                'normal_balance' => $validated['normal_balance'],
                'is_active' => $request->has('is_active'),
            ]);

            // SOX Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => $companyId,
                'user_id' => auth()->id(),
                'action_type' => 'CREATE_ACCOUNT',
                'table_name' => 'accounts',
                'record_id' => $accountId,
                'new_state' => json_encode([
                    'code' => $validated['code'],
                    'name' => $validated['name'],
                    'account_type' => $validated['account_type'],
                    'normal_balance' => $validated['normal_balance'],
                ]),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        return redirect()->route('accounts.index')->with('success', 'GL Account created successfully.');
    }

    /**
     * Show form for editing an account.
     */
    public function edit(string $id)
    {
        $companyId = session('active_company_id');

        $account = DB::table('accounts')
            ->where('id', $id)
            ->where('company_id', $companyId)
            ->first();

        if (!$account) {
            abort(404, 'Account not found.');
        }

        // Parent candidates excluding current account to prevent self-referencing loops
        $parentAccounts = DB::table('accounts')
            ->where('company_id', $companyId)
            ->where('id', '!=', $id)
            ->where('is_active', true)
            ->orderBy('code', 'asc')
            ->get();

        return view('accounts.edit', compact('account', 'parentAccounts'));
    }

    /**
     * Update an existing account.
     */
    public function update(Request $request, string $id)
    {
        $companyId = session('active_company_id');

        $account = DB::table('accounts')
            ->where('id', $id)
            ->where('company_id', $companyId)
            ->first();

        if (!$account) {
            abort(404);
        }

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('accounts')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                })->ignore($id),
            ],
            'name' => 'required|string|max:255',
            'parent_account_id' => [
                'nullable',
                'uuid',
                'different:id',
                Rule::exists('accounts', 'id')->where(function ($query) use ($companyId) {
                    return $query->where('company_id', $companyId);
                }),
            ],
            'account_type' => 'required|string|in:ASSET,LIABILITY,EQUITY,REVENUE,EXPENSE',
            'normal_balance' => 'required|string|in:DEBIT,CREDIT',
            'is_active' => 'boolean',
        ]);

        DB::transaction(function () use ($validated, $account, $id, $companyId, $request) {
            DB::table('accounts')->where('id', $id)->update([
                'code' => $validated['code'],
                'name' => $validated['name'],
                'parent_account_id' => $validated['parent_account_id'] ?? null,
                'account_type' => $validated['account_type'],
                'normal_balance' => $validated['normal_balance'],
                'is_active' => $request->has('is_active'),
            ]);

            // SOX Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => $companyId,
                'user_id' => auth()->id(),
                'action_type' => 'UPDATE_ACCOUNT',
                'table_name' => 'accounts',
                'record_id' => $id,
                'previous_state' => json_encode(['code' => $account->code, 'name' => $account->name, 'type' => $account->account_type]),
                'new_state' => json_encode(['code' => $validated['code'], 'name' => $validated['name'], 'type' => $validated['account_type']]),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        return redirect()->route('accounts.index')->with('success', 'GL Account updated successfully.');
    }

    /**
     * Remove an account if unreferenced.
     */
    public function destroy(Request $request, string $id)
    {
        $companyId = session('active_company_id');

        $account = DB::table('accounts')
            ->where('id', $id)
            ->where('company_id', $companyId)
            ->first();

        if (!$account) {
            abort(404);
        }

        // Block deletion if account holds child accounts
        $hasChildren = DB::table('accounts')->where('parent_account_id', $id)->exists();
        if ($hasChildren) {
            return back()->withErrors(['error' => 'Cannot delete an account that has sub-accounts attached. Reassign child accounts first.']);
        }

        // Block deletion if journal entries are already posted to this account
        $hasLines = DB::table('journal_lines')->where('account_id', $id)->exists();
        if ($hasLines) {
            return back()->withErrors(['error' => 'Cannot delete an account that has posted journal entries. Deactivate it instead.']);
        }

        DB::transaction(function () use ($id, $account, $companyId, $request) {
            DB::table('accounts')->where('id', $id)->delete();

            // SOX Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => $companyId,
                'user_id' => auth()->id(),
                'action_type' => 'DELETE_ACCOUNT',
                'table_name' => 'accounts',
                'record_id' => $id,
                'previous_state' => json_encode(['code' => $account->code, 'name' => $account->name]),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        return redirect()->route('accounts.index')->with('success', 'GL Account deleted successfully.');
    }
}