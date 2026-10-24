<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PermissionService
{
    /**
     * Check if the logged-in user has a specific permission in the active company.
     */
    public static function hasPermission(string $permissionCode): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        // 1. Super Admins bypass all checks
        if ($user->is_super_admin) {
            return true;
        }

        $activeCompanyId = session('active_company_id');

        if (!$activeCompanyId) {
            return false;
        }

        // 2. Query company-scoped roles and mapped permissions
        return DB::table('user_company_access')
            ->join('role_permissions', 'user_company_access.role_id', '=', 'role_permissions.role_id')
            ->join('permissions', 'role_permissions.permission_id', '=', 'permissions.id')
            ->where('user_company_access.user_id', $user->id)
            ->where('user_company_access.company_id', $activeCompanyId)
            ->where('permissions.code', $permissionCode)
            ->exists();
    }
}