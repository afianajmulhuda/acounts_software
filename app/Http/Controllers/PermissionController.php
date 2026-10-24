<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     */
    public function index(Request $request)
    {
        $query = DB::table('permissions');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'ILIKE', "%{$search}%")
                  ->orWhere('module', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        $permissions = $query->orderBy('module')->orderBy('code')->paginate(15);
        $modules = DB::table('permissions')->distinct()->pluck('module');

        return view('permissions.index', compact('permissions', 'modules'));
    }

    /**
     * Show form to create a new permission.
     */
    public function create()
    {
        $existingModules = DB::table('permissions')->distinct()->pluck('module');
        return view('permissions.create', compact('existingModules'));
    }

    /**
     * Store a newly created permission.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:permissions,code',
            'module' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
        ]);

        $permissionId = Str::uuid();

        DB::transaction(function () use ($validated, $permissionId, $request) {
            DB::table('permissions')->insert([
                'id' => $permissionId,
                'code' => Str::slug($validated['code'], '_'),
                'module' => strtoupper($validated['module']),
                'description' => $validated['description'] ?? null,
            ]);

            // SOX Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => session('active_company_id'),
                'user_id' => auth()->id(),
                'action_type' => 'CREATE_PERMISSION',
                'table_name' => 'permissions',
                'record_id' => $permissionId,
                'new_state' => json_encode(['code' => $validated['code'], 'module' => $validated['module']]),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        return redirect()->route('permissions.index')->with('success', 'Permission created successfully.');
    }

    /**
     * Show form to edit a permission.
     */
    public function edit(string $id)
    {
        $permission = DB::table('permissions')->where('id', $id)->first();

        if (!$permission) {
            abort(404, 'Permission not found.');
        }

        $existingModules = DB::table('permissions')->distinct()->pluck('module');

        return view('permissions.edit', compact('permission', 'existingModules'));
    }

    /**
     * Update an existing permission.
     */
    public function update(Request $request, string $id)
    {
        $permission = DB::table('permissions')->where('id', $id)->first();

        if (!$permission) {
            abort(404, 'Permission not found.');
        }

        $validated = $request->validate([
            'code' => 'required|string|max:100|unique:permissions,code,' . $id . ',id',
            'module' => 'required|string|max:50',
            'description' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated, $permission, $id, $request) {
            $formattedCode = Str::slug($validated['code'], '_');
            $formattedModule = strtoupper($validated['module']);

            DB::table('permissions')->where('id', $id)->update([
                'code' => $formattedCode,
                'module' => $formattedModule,
                'description' => $validated['description'] ?? null,
            ]);

            // SOX Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => session('active_company_id'),
                'user_id' => auth()->id(),
                'action_type' => 'UPDATE_PERMISSION',
                'table_name' => 'permissions',
                'record_id' => $id,
                'previous_state' => json_encode(['code' => $permission->code, 'module' => $permission->module]),
                'new_state' => json_encode(['code' => $formattedCode, 'module' => $formattedModule]),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove a permission.
     */
    public function destroy(Request $request, string $id)
    {
        $permission = DB::table('permissions')->where('id', $id)->first();

        if (!$permission) {
            abort(404);
        }

        DB::transaction(function () use ($id, $permission, $request) {
            // Delete pivot allocations first
            DB::table('role_permissions')->where('permission_id', $id)->delete();
            DB::table('permissions')->where('id', $id)->delete();

            // SOX Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => session('active_company_id'),
                'user_id' => auth()->id(),
                'action_type' => 'DELETE_PERMISSION',
                'table_name' => 'permissions',
                'record_id' => $id,
                'previous_state' => json_encode(['code' => $permission->code, 'module' => $permission->module]),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        return redirect()->route('permissions.index')->with('success', 'Permission deleted successfully.');
    }
}