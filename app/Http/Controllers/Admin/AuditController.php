<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Exports\AuditExport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class AuditController extends Controller
{
    /**
     * Entreprise réellement consultée (contexte de session, pas l'entreprise
     * du profil : les deux diffèrent en mode switch).
     */
    private function currentCompany(): ?Company
    {
        return Company::with(['users', 'associatedUsers', 'admin'])
            ->find(session('current_company_id', Auth::user()->company_id));
    }

    private function baseQuery(Request $request, ?Company $company)
    {
        $query = AuditLog::with('user')
            ->where('company_id', $company?->id)
            ->orderBy('created_at', 'desc');

        // Le filtre utilisateur ne peut viser qu'un membre de l'entreprise.
        if ($request->filled('user_id') && $company) {
            $membreIds = $company->membres()->pluck('id')->all();
            if (in_array((int) $request->user_id, $membreIds, true)) {
                $query->where('user_id', $request->user_id);
            }
        }

        if ($request->filled('event')) {
            $query->where('action', $request->event);
        }

        if ($request->filled('module')) {
            $query->where('model_type', 'App\\Models\\' . $request->module);
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('created_at', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('created_at', '<=', $request->date_fin);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $company = $this->currentCompany();

        $perPage = (int) $request->input('per_page', 25);
        if (!in_array($perPage, [25, 50, 100, 200], true)) {
            $perPage = 25;
        }

        $logs = $this->baseQuery($request, $company)->paginate($perPage)->withQueryString();

        // Cloisonnement : on ne propose que les membres de l'entreprise.
        $users = $company
            ? $company->membres()->sortBy(fn ($u) => mb_strtolower($u->name ?? ''))->values()
            : collect();

        // Modules réellement présents dans le journal de cette entreprise
        $modules = AuditLog::where('company_id', $company?->id)
            ->distinct()
            ->pluck('model_type')
            ->filter()
            ->map(fn ($type) => class_basename($type))
            ->unique()
            ->sort()
            ->values();

        return view('admin.audit.index', compact('logs', 'users', 'modules', 'company', 'perPage'));
    }

    public function export(Request $request)
    {
        $company = $this->currentCompany();
        $query = $this->baseQuery($request, $company);

        return Excel::download(new AuditExport($query), 'audit_logs_' . date('d_m_Y') . '.xlsx');
    }
}
