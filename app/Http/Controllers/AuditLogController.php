<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditLogController extends Controller
{
    /**
     * Display SOX audit log stream.
     */
    public function index(Request $request)
    {
        $companyId = session('active_company_id');

        $query = DB::table('audit_logs')
            ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
            ->where('audit_logs.company_id', $companyId)
            ->select(
                'audit_logs.*',
                'users.first_name',
                'users.last_name'
            );

        if ($request->filled('action_type')) {
            $query->where('audit_logs.action_type', 'ILIKE', "%{$request->action_type}%");
        }

        if ($request->filled('table_name')) {
            $query->where('audit_logs.table_name', 'ILIKE', "%{$request->table_name}%");
        }

        if ($request->filled('date_from')) {
            $query->where('audit_logs.timestamp', '>=', $request->date_from);
        }

        $logs = $query->orderBy('audit_logs.timestamp', 'desc')->paginate(20);

        return view('audit_logs.index', compact('logs'));
    }

    /**
     * Inspect state diff (Old vs New state).
     */
    public function show(string $id)
    {
        $companyId = session('active_company_id');

        $log = DB::table('audit_logs')
            ->leftJoin('users', 'audit_logs.user_id', '=', 'users.id')
            ->where('audit_logs.id', $id)
            ->where('audit_logs.company_id', $companyId)
            ->select('audit_logs.*', 'users.first_name', 'users.last_name')
            ->first();

        if (!$log) {
            abort(404, 'Audit log entry not found.');
        }

        return view('audit_logs.show', compact('log'));
    }
}