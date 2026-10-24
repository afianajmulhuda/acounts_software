<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CompanySwitchController extends Controller
{
    public function switch(Request $request)
    {
        $request->validate(['company_id' => 'required|uuid']);

        // Check if current user has access to requested company
        $hasAccess = auth()->user()->companies()->where('companies.id', $request->company_id)->exists();

        if ($hasAccess) {
            session(['active_company_id' => $request->company_id]);
            return back()->with('success', 'Active Legal Entity switched successfully.');
        }

        return back()->withErrors(['company_id' => 'Unauthorized entity access attempt.']);
    }
}