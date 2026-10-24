<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserCompanyAccessController extends Controller
{
    /**
     * Show access management interface for a specific user.
     */
    public function edit(string $userId)
    {
        $user = DB::table('users')->where('id', $userId)->first();
        if (!$user) {
            abort(404, 'User not found.');
        }

        $accessRecords = DB::table('user_company_access')
            ->join('companies', 'user_company_access.company_id', '=', 'companies.id')
            ->join('roles', 'user_company_access.role_id', '=', 'roles.id')
            ->where('user_company_access.user_id', $userId)
            ->select(
                'user_company_access.*',
                'companies.legal_name as company_name',
                'companies.base_currency_code',
                'roles.name as role_name'
            )
            ->get();

        $companies = DB::table('companies')->orderBy('legal_name')->get();
        $roles = DB::table('roles')->orderBy('name')->get();

        return view('users.access', compact('user', 'accessRecords', 'companies', 'roles'));
    }

    /**
     * Grant company access to a user.
     */
    public function store(Request $request, string $userId)
    {
        $validated = $request->validate([
            'company_id' => 'required|uuid|exists:companies,id',
            'role_id' => 'required|uuid|exists:roles,id',
            'max_approval_limit' => 'required|numeric|min:0',
            'can_post_journals' => 'boolean',
        ]);

        // Check if unique constraint exists
        $exists = DB::table('user_company_access')
            ->where('user_id', $userId)
            ->where('company_id', $validated['company_id'])
            ->where('role_id', $validated['role_id'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['error' => 'This user already holds this specific role for the selected company.']);
        }

        $accessId = Str::uuid();

        DB::transaction(function () use ($validated, $userId, $accessId, $request) {
            DB::table('user_company_access')->insert([
                'id' => $accessId,
                'user_id' => $userId,
                'company_id' => $validated['company_id'],
                'role_id' => $validated['role_id'],
                'max_approval_limit' => $validated['max_approval_limit'],
                'can_post_journals' => $request->has('can_post_journals'),
                'granted_at' => now(),
                'granted_by_user_id' => auth()->id(),
            ]);

            // SOX Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => $validated['company_id'],
                'user_id' => auth()->id(),
                'action_type' => 'GRANT_COMPANY_ACCESS',
                'table_name' => 'user_company_access',
                'record_id' => $accessId,
                'new_state' => json_encode([
                    'target_user_id' => $userId,
                    'company_id' => $validated['company_id'],
                    'role_id' => $validated['role_id'],
                    'max_approval_limit' => $validated['max_approval_limit'],
                ]),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        return back()->with('success', 'Company access granted successfully.');
    }

    /**
     * Revoke company access from a user.
     */
    public function destroy(Request $request, string $accessId)
    {
        $access = DB::table('user_company_access')->where('id', $accessId)->first();
        if (!$access) {
            abort(404);
        }

        DB::transaction(function () use ($accessId, $access, $request) {
            DB::table('user_company_access')->where('id', $accessId)->delete();

            // SOX Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => $access->company_id,
                'user_id' => auth()->id(),
                'action_type' => 'REVOKE_COMPANY_ACCESS',
                'table_name' => 'user_company_access',
                'record_id' => $accessId,
                'previous_state' => json_encode([
                    'target_user_id' => $access->user_id,
                    'company_id' => $access->company_id,
                    'role_id' => $access->role_id,
                ]),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        return back()->with('success', 'Company access revoked successfully.');
    }
}