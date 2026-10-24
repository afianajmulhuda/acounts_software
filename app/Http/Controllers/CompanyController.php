<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    /**
     * Display a listing of all companies.
     */
    public function index()
    {
        $companies = DB::table('companies')
            ->orderBy('legal_name', 'asc')
            ->paginate(10);

        return view('companies.index', compact('companies'));
    }

    /**
     * Store a newly created company in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'legal_name'         => 'required|string|max:255',
            'tax_identifier'     => 'required|string|max:100',
            'base_currency_code' => 'required|string|size:3',
            'country_code'        => 'required|string|size:2',
        ]);

        $companyId = Str::uuid();

        DB::transaction(function () use ($request, $companyId) {
            // Insert Company
            DB::table('companies')->insert([
                'id'                 => $companyId,
                'legal_name'         => $request->legal_name,
                'tax_identifier'     => $request->tax_identifier,
                'base_currency_code' => strtoupper($request->base_currency_code),
                'country_code'        => strtoupper($request->country_code),
                'created_at'         => now(),
            ]);

            // Grant active user access to newly created company
            DB::table('user_company_access')->insert([
                'id'                  => Str::uuid(),
                'user_id'             => auth()->id(),
                'company_id'          => $companyId,
                'role_id'             => DB::table('roles')->where('name', 'Admin')->value('id') ?? DB::table('roles')->first()->id,
                'can_post_journals'   => true,
                'granted_at'          => now(),
                'granted_by_user_id'  => auth()->id(),
            ]);
        });

        return back()->with('success', 'Company legal entity created successfully.');
    }

    /**
     * Update the specified company in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'legal_name'         => 'required|string|max:255',
            'tax_identifier'     => 'required|string|max:100',
            'base_currency_code' => 'required|string|size:3',
            'country_code'        => 'required|string|size:2',
        ]);

        DB::table('companies')
            ->where('id', $id)
            ->update([
                'legal_name'         => $request->legal_name,
                'tax_identifier'     => $request->tax_identifier,
                'base_currency_code' => strtoupper($request->base_currency_code),
                'country_code'        => strtoupper($request->country_code),
            ]);

        return back()->with('success', 'Company details updated successfully.');
    }

    /**
     * Remove the specified company from storage.
     */
    public function destroy($id)
    {
        // Prevent deletion if transactions exist
        $hasJournalEntries = DB::table('journal_entries')->where('company_id', $id)->exists();

        if ($hasJournalEntries) {
            return back()->withErrors(['error' => 'Cannot delete company with posted journal transactions.']);
        }

        DB::table('companies')->where('id', $id)->delete();

        // Clear active company session if deleted
        if (session('active_company_id') === $id) {
            session()->forget('active_company_id');
        }

        return back()->with('success', 'Company deleted successfully.');
    }
}