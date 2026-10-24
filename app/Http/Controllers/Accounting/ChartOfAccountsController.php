<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChartOfAccountsController extends Controller
{
    public function index()
    {
        $companyId = session('active_company_id');
        // dd($companyId);

        $accounts = DB::table('accounts')
            ->leftJoin('accounts as parent', 'accounts.parent_account_id', '=', 'parent.id')
            ->where('accounts.company_id', $companyId)
            ->select(
                'accounts.*',
                'parent.code as parent_code',
                'parent.name as parent_name'
            )
            ->orderBy('accounts.code', 'asc')
            ->get();

        return view('accounting.coa.index', compact('accounts'));
    }

    public function store(Request $request)
    {
        $companyId = session('active_company_id');

        $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'account_type' => 'required|in:ASSET,LIABILITY,EQUITY,REVENUE,EXPENSE',
            'normal_balance' => 'required|in:DEBIT,CREDIT',
            'parent_account_id' => 'nullable|uuid'
        ]);

        DB::table('accounts')->insert([
            'id' => Str::uuid(),
            'company_id' => $companyId,
            'code' => $request->code,
            'name' => $request->name,
            'parent_account_id' => $request->parent_account_id,
            'account_type' => $request->account_type,
            'normal_balance' => $request->normal_balance,
            'is_active' => true,
        ]);

        return back()->with('success', 'Account added to Chart of Accounts successfully.');
    }
}
