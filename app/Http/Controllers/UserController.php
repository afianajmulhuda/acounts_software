<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = DB::table('users');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'ILIKE', "%{$search}%")
                  ->orWhere('first_name', 'ILIKE', "%{$search}%")
                  ->orWhere('last_name', 'ILIKE', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->orderBy('first_name')->paginate(15);

        return view('users.index', compact('users'));
    }

    /**
     * Show form for creating a new user.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:191|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'job_title' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'status' => 'required|in:ACTIVE,SUSPENDED,DEACTIVATED',
            'is_super_admin' => 'boolean',
            'timezone' => 'required|string|max:50',
            'preferred_language' => 'required|string|max:10',
        ]);

        $userId = Str::uuid();

        DB::transaction(function () use ($validated, $userId, $request) {
            DB::table('users')->insert([
                'id' => $userId,
                'email' => $validated['email'],
                'password_hash' => Hash::make($validated['password']),
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'job_title' => $validated['job_title'] ?? null,
                'department' => $validated['department'] ?? null,
                'status' => $validated['status'],
                'is_super_admin' => $request->has('is_super_admin'),
                'timezone' => $validated['timezone'],
                'preferred_language' => $validated['preferred_language'],
                'password_changed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // SOX Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => session('active_company_id'),
                'user_id' => auth()->id(),
                'action_type' => 'CREATE_USER',
                'table_name' => 'users',
                'record_id' => $userId,
                'new_state' => json_encode([
                    'email' => $validated['email'],
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'status' => $validated['status'],
                ]),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });
        return redirect()
            ->route('users.access.edit', $userId)
            ->with('success', 'User created successfully! Set up company permissions below.');
        // return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Show form for editing user.
     */
    public function edit(string $id)
    {
        $user = DB::table('users')->where('id', $id)->first();

        if (!$user) {
            abort(404, 'User not found.');
        }

        return view('users.edit', compact('user'));
    }

    /**
     * Update user details.
     */
    public function update(Request $request, string $id)
    {
        $user = DB::table('users')->where('id', $id)->first();

        if (!$user) {
            abort(404, 'User not found.');
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:191', Rule::unique('users')->ignore($id)],
            'password' => 'nullable|string|min:8|confirmed',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'job_title' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'status' => 'required|in:ACTIVE,SUSPENDED,DEACTIVATED',
            'is_super_admin' => 'boolean',
            'timezone' => 'required|string|max:50',
            'preferred_language' => 'required|string|max:10',
        ]);

        DB::transaction(function () use ($validated, $user, $request, $id) {
            $updateData = [
                'email' => $validated['email'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'job_title' => $validated['job_title'] ?? null,
                'department' => $validated['department'] ?? null,
                'status' => $validated['status'],
                'is_super_admin' => $request->has('is_super_admin'),
                'timezone' => $validated['timezone'],
                'preferred_language' => $validated['preferred_language'],
                'updated_at' => now(),
            ];

            if ($request->filled('password')) {
                $updateData['password_hash'] = Hash::make($validated['password']);
                $updateData['password_changed_at'] = now();
            }

            // Reset lock state if account status updated to ACTIVE
            if ($validated['status'] === 'ACTIVE' && $user->status !== 'ACTIVE') {
                $updateData['failed_login_attempts'] = 0;
                $updateData['locked_until'] = null;
            }

            DB::table('users')->where('id', $id)->update($updateData);

            // Audit Logging
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => session('active_company_id'),
                'user_id' => auth()->id(),
                'action_type' => 'UPDATE_USER',
                'table_name' => 'users',
                'record_id' => $id,
                'previous_state' => json_encode(['email' => $user->email, 'status' => $user->status]),
                'new_state' => json_encode(['email' => $validated['email'], 'status' => $validated['status']]),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Delete user from the database.
     */
    public function destroy(Request $request, string $id)
    {
        if ($id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        $user = DB::table('users')->where('id', $id)->first();

        if (!$user) {
            abort(404);
        }

        DB::transaction(function () use ($id, $user, $request) {
            // Delete pivot dependencies first
            DB::table('user_company_access')->where('user_id', $id)->delete();
            DB::table('users')->where('id', $id)->delete();

            // Record Deletion in Audit Log
            DB::table('audit_logs')->insert([
                'id' => Str::uuid(),
                'company_id' => session('active_company_id'),
                'user_id' => auth()->id(),
                'action_type' => 'DELETE_USER',
                'table_name' => 'users',
                'record_id' => $id,
                'previous_state' => json_encode(['email' => $user->email, 'first_name' => $user->first_name]),
                'ip_address' => $request->ip(),
                'timestamp' => now(),
            ]);
        });

        return redirect()->route('users.index')->with('success', 'User account permanently removed.');
    }
}