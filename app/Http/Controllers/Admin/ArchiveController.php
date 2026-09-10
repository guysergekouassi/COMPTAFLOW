<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArchivedRecord;
use App\Models\AuditLog;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Archive des suppressions : tout ce qui est supprimé dans l'entreprise y reste
 * consultable pendant 30 jours (ArchivedRecord::RETENTION_JOURS), suppressions
 * en lot comprises, avant purge définitive.
 */
class ArchiveController extends Controller
{
    private function currentCompany(): ?Company
    {
        return Company::with(['users', 'associatedUsers', 'admin'])
            ->find(session('current_company_id', Auth::user()->company_id));
    }

    public function index(Request $request)
    {
        $company = $this->currentCompany();

        // Purge des archives dépassant la durée de conservation
        ArchivedRecord::where('company_id', $company?->id)
            ->where('expires_at', '<=', now())
            ->delete();

        $query = ArchivedRecord::with('user')
            ->where('company_id', $company?->id)
            ->enConservation()
            ->orderByDesc('deleted_at');

        if ($request->filled('module')) {
            $query->where('model_type', 'App\\Models\\' . $request->module);
        }

        if ($request->filled('user_id') && $company) {
            $membreIds = $company->membres()->pluck('id')->all();
            if (in_array((int) $request->user_id, $membreIds, true)) {
                $query->where('user_id', $request->user_id);
            }
        }

        if ($request->filled('search')) {
            $query->where('label', 'like', '%' . $request->search . '%');
        }

        $perPage = (int) $request->input('per_page', 25);
        if (!in_array($perPage, [25, 50, 100], true)) {
            $perPage = 25;
        }

        $archives = $query->paginate($perPage)->withQueryString();

        $users = $company
            ? $company->membres()->sortBy(fn ($u) => mb_strtolower($u->name ?? ''))->values()
            : collect();

        $modules = ArchivedRecord::where('company_id', $company?->id)
            ->enConservation()
            ->distinct()
            ->pluck('model_type')
            ->filter()
            ->map(fn ($type) => class_basename($type))
            ->unique()
            ->sort()
            ->values();

        $stats = [
            'total'      => ArchivedRecord::where('company_id', $company?->id)->enConservation()->count(),
            'lots'       => ArchivedRecord::where('company_id', $company?->id)->enConservation()->whereNotNull('batch_id')->distinct()->count('batch_id'),
            'expire_7j'  => ArchivedRecord::where('company_id', $company?->id)->enConservation()->where('expires_at', '<=', now()->addDays(7))->count(),
            'retention'  => ArchivedRecord::RETENTION_JOURS,
        ];

        return view('admin.archives.index', compact('archives', 'users', 'modules', 'company', 'stats', 'perPage'));
    }

    /**
     * Contenu complet d'une donnée supprimée (JSON lisible).
     */
    public function show($id)
    {
        $company = $this->currentCompany();

        $archive = ArchivedRecord::with('user')
            ->where('company_id', $company?->id)
            ->findOrFail($id);

        $champs = collect($archive->data ?? [])
            ->reject(fn ($v, $k) => in_array($k, ['id', 'created_at', 'updated_at'], true))
            ->map(function ($valeur, $champ) {
                return [
                    'champ'  => (new AuditLog())->libelleChamp($champ),
                    'valeur' => is_array($valeur) ? json_encode($valeur, JSON_UNESCAPED_UNICODE) : (string) ($valeur ?? '(vide)'),
                ];
            })
            ->values();

        return response()->json([
            'label'      => $archive->label,
            'module'     => $archive->libelleModule(),
            'auteur'     => trim(($archive->user->name ?? 'Système') . ' ' . ($archive->user->last_name ?? '')),
            'supprime_le' => optional($archive->deleted_at)->format('d/m/Y à H:i:s'),
            'expire_le'  => optional($archive->expires_at)->format('d/m/Y'),
            'jours_restants' => $archive->joursRestants(),
            'lot'        => $archive->batch_id,
            'batch_size' => $archive->batch_size,
            'champs'     => $champs,
        ]);
    }
}
