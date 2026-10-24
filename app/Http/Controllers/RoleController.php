<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(Request $request)
    {
        $query = DB::table('roles');

        if ($request->filled('search')) {
            $query->where('name', 'ILIKE', "%{$request->search}%");
        }

        $roles = $query->orderBy('name')->paginate(15);

        return view('roles.index', compact('roles'));
    }

    /**
     * Show form to create a new role.
     */
    public function create()
    {
        return view('roles.create');
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:roles,name',
            'description' => 'nullable|string|max:255',
        ]);

        $roleId = Str::uuid();

        DB::transaction(function () use ($validated, $roleId, $request) {
            DB::table('roles')->insert([
                'id' => $roleId,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_system_role' => false,
                'created_at' => now(),
            ]);

            // SOX Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => session('active_company_id'),
                'user_id' => auth()->id(),
                'action_type' => 'CREATE_ROLE',
                'table_name' => 'roles',
                'record_id' => $roleId,
                'new_state' => json_encode(['name' => $validated['name']]),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        return redirect()->route('roles.index')->with('success', 'Role created successfully.');
    }

    /**
     * Show form to edit a role.
     */
    public function edit(string $id)
    {
        $role = DB::table('roles')->where('id', $id)->first();

        if (!$role) {
            abort(404, 'Role not found.');
        }

        return view('roles.edit', compact('role'));
    }

    /**
     * Update an existing role.
     */
    public function update(Request $request, string $id)
    {
        $role = DB::table('roles')->where('id', $id)->first();

        if (!$role) {
            abort(404, 'Role not found.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:roles,name,' . $id . ',id',
            'description' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated, $role, $id, $request) {
            DB::table('roles')->where('id', $id)->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            // SOX Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => session('active_company_id'),
                'user_id' => auth()->id(),
                'action_type' => 'UPDATE_ROLE',
                'table_name' => 'roles',
                'record_id' => $id,
                'previous_state' => json_encode(['name' => $role->name]),
                'new_state' => json_encode(['name' => $validated['name']]),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        return redirect()->route('roles.index')->with('success', 'Role updated successfully.');
    }

    /**
     * Remove a role.
     */
    public function destroy(Request $request, string $id)
    {
        $role = DB::table('roles')->where('id', $id)->first();

        if (!$role) {
            abort(404);
        }

        if ($role->is_system_role) {
            return back()->withErrors(['error' => 'System roles cannot be deleted.']);
        }

        // Check if role is assigned in user_company_access
        $isAssigned = DB::table('user_company_access')->where('role_id', $id)->exists();
        if ($isAssigned) {
            return back()->withErrors(['error' => 'Cannot delete role assigned to active user company permissions.']);
        }

        DB::transaction(function () use ($id, $role, $request) {
            DB::table('role_permissions')->where('role_id', $id)->delete();
            DB::table('roles')->where('id', $id)->delete();

            // SOX Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => session('active_company_id'),
                'user_id' => auth()->id(),
                'action_type' => 'DELETE_ROLE',
                'table_name' => 'roles',
                'record_id' => $id,
                'previous_state' => json_encode(['name' => $role->name]),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
}