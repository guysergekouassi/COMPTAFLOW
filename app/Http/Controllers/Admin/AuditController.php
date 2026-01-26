<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use App\Exports\AuditExport;
use Maatwebsite\Excel\Facades\Excel;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')
            ->where('company_id', auth()->user()->company_id)
            ->orderBy('created_at', 'desc');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('event')) {
            $query->where('action', $request->event);
        }

        $logs = $query->paginate(50);

        $users = \App\Models\User::orderBy('name')->get();

        return view('admin.audit.index', compact('logs', 'users'));
    }

    public function export(Request $request)
    {
        $query = AuditLog::with('user')
            ->where('company_id', auth()->user()->company_id)
            ->orderBy('created_at', 'desc');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('event')) {
            $query->where('action', $request->event);
        }

        return Excel::download(new AuditExport($query), 'audit_logs_' . date('d_m_Y') . '.xlsx');
    }
}
