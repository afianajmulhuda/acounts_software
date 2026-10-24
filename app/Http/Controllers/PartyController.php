<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PartyController extends Controller
{
    /**
     * Display a listing of counterparties (Customers/Vendors).
     */
    public function index(Request $request)
    {
        $companyId = session('active_company_id');

        $query = DB::table('parties')
            ->where('company_id', $companyId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('legal_name', 'ILIKE', "%{$search}%")
                  ->orWhere('tax_number', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('party_type')) {
            $query->where('party_type', $request->party_type);
        }

        $parties = $query->orderBy('legal_name', 'asc')->paginate(15);

        return view('parties.index', compact('parties'));
    }

    /**
     * Show form to create a new party.
     */
    public function create()
    {
        return view('parties.create');
    }

    /**
     * Store a newly created party.
     */
    public function store(Request $request)
    {
        $companyId = session('active_company_id');

        $validated = $request->validate([
            'party_type' => 'required|string|in:CUSTOMER,VENDOR,BOTH',
            'legal_name' => 'required|string|max:255',
            'tax_number' => 'nullable|string|max:100',
            'country_code' => 'required|string|size:2',
            'currency_code' => 'required|string|size:3',
        ]);

        $partyId = Str::uuid();

        DB::transaction(function () use ($validated, $partyId, $companyId, $request) {
            DB::table('parties')->insert([
                'id' => $partyId,
                'company_id' => $companyId,
                'party_type' => $validated['party_type'],
                'legal_name' => $validated['legal_name'],
                'tax_number' => $validated['tax_number'] ?? null,
                'country_code' => strtoupper($validated['country_code']),
                'currency_code' => strtoupper($validated['currency_code']),
            ]);

            // SOX Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => $companyId,
                'user_id' => auth()->id(),
                'action_type' => 'CREATE_PARTY',
                'table_name' => 'parties',
                'record_id' => $partyId,
                'new_state' => json_encode([
                    'legal_name' => $validated['legal_name'],
                    'party_type' => $validated['party_type'],
                    'tax_number' => $validated['tax_number'] ?? null,
                ]),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        return redirect()->route('parties.index')->with('success', 'Counterparty created successfully.');
    }

    /**
     * Show form to edit a party.
     */
    public function edit(string $id)
    {
        $companyId = session('active_company_id');

        $party = DB::table('parties')
            ->where('id', $id)
            ->where('company_id', $companyId)
            ->first();

        if (!$party) {
            abort(404, 'Counterparty not found.');
        }

        return view('parties.edit', compact('party'));
    }

    /**
     * Update an existing party.
     */
    public function update(Request $request, string $id)
    {
        $companyId = session('active_company_id');

        $party = DB::table('parties')
            ->where('id', $id)
            ->where('company_id', $companyId)
            ->first();

        if (!$party) {
            abort(404);
        }

        $validated = $request->validate([
            'party_type' => 'required|string|in:CUSTOMER,VENDOR,BOTH',
            'legal_name' => 'required|string|max:255',
            'tax_number' => 'nullable|string|max:100',
            'country_code' => 'required|string|size:2',
            'currency_code' => 'required|string|size:3',
        ]);

        DB::transaction(function () use ($validated, $party, $id, $companyId, $request) {
            DB::table('parties')->where('id', $id)->update([
                'party_type' => $validated['party_type'],
                'legal_name' => $validated['legal_name'],
                'tax_number' => $validated['tax_number'] ?? null,
                'country_code' => strtoupper($validated['country_code']),
                'currency_code' => strtoupper($validated['currency_code']),
            ]);

            // SOX Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => $companyId,
                'user_id' => auth()->id(),
                'action_type' => 'UPDATE_PARTY',
                'table_name' => 'parties',
                'record_id' => $id,
                'previous_state' => json_encode(['legal_name' => $party->legal_name, 'party_type' => $party->party_type]),
                'new_state' => json_encode(['legal_name' => $validated['legal_name'], 'party_type' => $validated['party_type']]),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        return redirect()->route('parties.index')->with('success', 'Counterparty details updated successfully.');
    }

    /**
     * Delete a party if no sub-ledger journal entries exist.
     */
    public function destroy(Request $request, string $id)
    {
        $companyId = session('active_company_id');

        $party = DB::table('parties')
            ->where('id', $id)
            ->where('company_id', $companyId)
            ->first();

        if (!$party) {
            abort(404);
        }

        // Check if party is linked to journal lines
        $hasTransactions = DB::table('journal_lines')->where('party_id', $id)->exists();
        if ($hasTransactions) {
            return back()->withErrors(['error' => 'Cannot delete a counterparty with existing ledger transactions.']);
        }

        DB::transaction(function () use ($id, $party, $companyId, $request) {
            DB::table('parties')->where('id', $id)->delete();

            // SOX Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => $companyId,
                'user_id' => auth()->id(),
                'action_type' => 'DELETE_PARTY',
                'table_name' => 'parties',
                'record_id' => $id,
                'previous_state' => json_encode(['legal_name' => $party->legal_name]),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        return redirect()->route('parties.index')->with('success', 'Counterparty deleted successfully.');
    }
}