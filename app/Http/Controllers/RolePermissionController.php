<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RolePermissionController extends Controller
{
    /**
     * Display matrix for managing role permissions.
     */
    public function edit(string $roleId)
    {
        $role = DB::table('roles')->where('id', $roleId)->first();

        if (!$role) {
            abort(404, 'Role not found.');
        }

        // Fetch all permissions grouped by module
        $permissionsByModule = DB::table('permissions')
            ->orderBy('module')
            ->orderBy('code')
            ->get()
            ->groupBy('module');

        // Fetch IDs of permissions currently assigned to this role
        $assignedPermissionIds = DB::table('role_permissions')
            ->where('role_id', $roleId)
            ->pluck('permission_id')
            ->toArray();

        return view('roles.permissions', compact('role', 'permissionsByModule', 'assignedPermissionIds'));
    }

    /**
     * Sync assigned permissions for a role.
     */
    public function update(Request $request, string $roleId)
    {
        $role = DB::table('roles')->where('id', $roleId)->first();

        if (!$role) {
            abort(404, 'Role not found.');
        }

        if ($role->is_system_role && auth()->user()->is_super_admin !== true) {
            return back()->withErrors(['error' => 'Only Super Admins can alter permissions for system roles.']);
        }

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'uuid|exists:permissions,id',
        ]);

        $selectedPermissions = $request->input('permissions', []);

        DB::transaction(function () use ($roleId, $selectedPermissions, $role, $request) {
            // Get old state for SOX audit logging
            $oldPermissions = DB::table('role_permissions')
                ->where('role_id', $roleId)
                ->pluck('permission_id')
                ->toArray();

            // Clear existing allocations
            DB::table('role_permissions')->where('role_id', $roleId)->delete();

            // Bulk insert newly selected permissions
            if (!empty($selectedPermissions)) {
                $insertData = array_map(function ($permissionId) use ($roleId) {
                    return [
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                    ];
                }, $selectedPermissions);

                DB::table('role_permissions')->insert($insertData);
            }

            // SOX Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => session('active_company_id'),
                'user_id' => auth()->id(),
                'action_type' => 'SYNC_ROLE_PERMISSIONS',
                'table_name' => 'role_permissions',
                'record_id' => $roleId,
                'previous_state' => json_encode(['permission_ids' => $oldPermissions]),
                'new_state' => json_encode(['permission_ids' => $selectedPermissions]),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        return redirect()->route('roles.index')->with('success', "Permissions updated for role '{$role->name}'.");
    }
}
