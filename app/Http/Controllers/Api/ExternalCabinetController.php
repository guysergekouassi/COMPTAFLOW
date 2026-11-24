<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cabinet;
use App\Models\Company;
use App\Services\AffectationCabinetSelflow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Les cabinets, vus depuis Selflow (24/11/2026).
 *
 * Entre serveurs, au secret partagé, comme `list-companies` : Selflow liste
 * les cabinets et leurs membres pour sa liste déroulante, puis affecte le
 * dossier lié au cabinet ou à un membre.
 */
class ExternalCabinetController extends Controller
{
    private function secretRefuse(Request $request): bool
    {
        $attendu = config('external_sync.external_sync_secret');
        $fourni = $request->input('secret') ?? $request->header('X-Sync-Secret');

        return empty($attendu) || empty($fourni) || !hash_equals($attendu, $fourni);
    }

    /** POST /api/external/cabinets */
    public function lister(Request $request)
    {
        if ($this->secretRefuse($request)) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 401);
        }

        $cabinets = Cabinet::where('is_active', true)->with('gerant')->orderBy('nom')->get();
        $membres = DB::table('cabinet_user')
            ->join('users', 'cabinet_user.user_id', '=', 'users.id')
            ->whereIn('cabinet_user.cabinet_id', $cabinets->pluck('id'))
            ->get(['cabinet_user.cabinet_id', 'cabinet_user.role', 'users.id', 'users.name', 'users.last_name', 'users.email_adresse'])
            ->groupBy('cabinet_id');

        return response()->json([
            'success' => true,
            'cabinets' => $cabinets->map(function (Cabinet $c) use ($membres) {
                $liste = collect($membres[$c->id] ?? [])->map(fn ($m) => [
                    'id' => (int) $m->id,
                    'nom' => trim(($m->name ?? '') . ' ' . ($m->last_name ?? '')),
                    'email' => $m->email_adresse,
                    'role' => $m->role,
                ]);
                if ($c->gerant && !$liste->contains('id', (int) $c->gerant->id)) {
                    $liste->prepend([
                        'id' => (int) $c->gerant->id,
                        'nom' => trim(($c->gerant->name ?? '') . ' ' . ($c->gerant->last_name ?? '')),
                        'email' => $c->gerant->email_adresse,
                        'role' => 'gerant',
                    ]);
                }

                return [
                    'id' => $c->id,
                    'nom' => $c->nom,
                    'code' => $c->code,
                    'membres' => $liste->values(),
                ];
            })->values(),
        ]);
    }

    /** POST /api/external/companies/affecter — dossier déjà lié. */
    public function affecter(Request $request)
    {
        if ($this->secretRefuse($request)) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 401);
        }

        $v = Validator::make($request->all(), [
            'selflow_company_id' => 'required|integer|min:1',
            'cabinet_id' => 'required|integer|min:1',
            'user_id' => 'nullable|integer|min:1',
        ]);
        if ($v->fails()) {
            return response()->json(['success' => false, 'message' => 'Données invalides.', 'errors' => $v->errors()], 422);
        }

        $company = Company::where('selflow_company_id', $request->integer('selflow_company_id'))->first();
        if (!$company) {
            return response()->json(['success' => false, 'message' => 'Aucun dossier Comptaflow lié à cette entreprise Selflow.'], 404);
        }

        $resultat = AffectationCabinetSelflow::appliquer($company, $request->integer('cabinet_id'), $request->integer('user_id') ?: null);

        return response()->json($resultat, $resultat['success'] ? 200 : 422);
    }
}
