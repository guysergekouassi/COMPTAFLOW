<?php

namespace App\Http\Controllers\Super;

use App\Http\Controllers\Controller;
use App\Models\AppSubscription;
use App\Models\CatalogPrice;
use App\Models\Company;
use App\Models\ServiceHonoraire;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HonorairesController extends Controller
{
    // ═══════════════════════════════════════════════════════════════
    // WEB ROUTES
    // ═══════════════════════════════════════════════════════════════

    /**
     * Page principale : Vue globale des honoraires
     */
    public function index()
    {
        // ── 1. Abonnements Apps ───────────────────────────────────────────────
        $allSubscriptions = AppSubscription::with('company')->get();
        $allServices      = ServiceHonoraire::with('company')->get();

        // Totaux par app
        $statsParApp = [];
        foreach (['RHFLOW', 'COMPTAFLOW', 'TASKFLOW', 'SELFLOW', 'LEGALFLOW'] as $app) {
            $subs = $allSubscriptions->where('app_name', $app);
            $statsParApp[$app] = [
                'total_clients'   => $subs->count(),
                'total_mensuel'   => $subs->sum('prix_mensuel'),
                'total_du'        => $subs->sum(fn($s) => $s->totalDu()),
                'en_attente'      => $subs->where('statut_paiement', 'pending')->count(),
                'en_retard'       => $subs->where('statut_paiement', 'overdue')->count(),
            ];
        }

        // ── 2. Totaux Globaux Apps ────────────────────────────────────────────
        $totalAppsMensuel  = $allSubscriptions->sum('prix_mensuel');
        $totalAppsDu       = $allSubscriptions->sum(fn($s) => $s->totalDu());
        $totalAppsClients  = $allSubscriptions->count();

        // ── 3. Totaux Services ────────────────────────────────────────────────
        $totalServicesMensuel = $allServices->sum('prix_mensuel');
        $totalServicesDu      = $allServices->sum(fn($s) => $s->totalDu());
        $totalServicesClients = $allServices->count();

        // ── 4. Grand Total ────────────────────────────────────────────────────
        $grandTotalDu      = $totalAppsDu + $totalServicesDu;
        $grandTotalMensuel = $totalAppsMensuel + $totalServicesMensuel;

        // ── 5. Liste des entreprises avec leurs abonnements ───────────────────
        $companies = Company::with(['appSubscriptions', 'serviceHonoraires'])
            ->whereHas('appSubscriptions')
            ->orWhereHas('serviceHonoraires')
            ->get()
            ->map(function ($company) {
                $totalDu = $company->appSubscriptions->sum(fn($s) => $s->totalDu())
                         + $company->serviceHonoraires->sum(fn($s) => $s->totalDu());
                $company->total_du = $totalDu;
                return $company;
            })
            ->sortByDesc('total_du');

        // ── 6. Catalogue services ─────────────────────────────────────────────
        $catalogueServices = ServiceHonoraire::catalogue();

        // ── 7. Stats services par type ────────────────────────────────────────
        $statsParService = [];
        foreach ($catalogueServices as $key => $cat) {
            $srvs = $allServices->where('service_name', $key);
            $statsParService[$key] = [
                'catalogue'    => $cat,
                'total_clients'=> $srvs->count(),
                'total_du'     => $srvs->sum(fn($s) => $s->totalDu()),
                'prix_defini'  => $srvs->where('prix_mensuel', '>', 0)->count() > 0,
            ];
        }

        // ── 8. Données pour le JavaScript (mapping fait en PHP, pas dans Blade) ──
        $subsForJs = $allSubscriptions->map(function ($s) {
            return [
                'id'           => $s->id,
                'company_id'   => $s->company_id,
                'company_name' => $s->company->company_name ?? '—',
                'app_name'     => $s->app_name,
                'pack_name'    => $s->pack_name,
                'prix_mensuel' => $s->prix_mensuel,
                'statut'       => $s->statut_paiement,
                'mois'         => $s->moisEcoules(),
                'total_du'     => $s->totalDu(),
            ];
        })->values()->toArray();

        return view('superadmin.honoraires', compact(
            'statsParApp', 'statsParService', 'catalogueServices',
            'totalAppsMensuel', 'totalAppsDu', 'totalAppsClients',
            'totalServicesMensuel', 'totalServicesDu', 'totalServicesClients',
            'grandTotalDu', 'grandTotalMensuel',
            'companies',
            'allSubscriptions', 'allServices',
            'subsForJs'
        ));
    }

    /**
     * Détail JSON d'une entreprise (pour la modal AJAX)
     */
    public function show(int $companyId)
    {
        $company = Company::with(['appSubscriptions', 'serviceHonoraires'])->findOrFail($companyId);

        $subscriptions = $company->appSubscriptions->map(function ($s) {
            $mois = $s->moisEcoules();
            return [
                'app'           => $s->app_name,
                'pack'          => $s->pack_name,
                'prix_mensuel'  => $s->prix_mensuel,
                'date_debut'    => $s->date_debut->format('d/m/Y'),
                'date_fin'      => $s->date_fin?->format('d/m/Y') ?? 'En cours',
                'statut'        => $s->statut_paiement,
                'mois_ecoules'  => $mois,
                'semaines_ecoulees' => $mois * 4,
                'total_du'      => $s->totalDu(),
                'par_annee'     => $s->prix_mensuel * 12,
            ];
        });

        $services = $company->serviceHonoraires->map(function ($s) {
            $mois = $s->moisEcoules();
            return [
                'service'       => $s->service_name,
                'description'   => $s->description,
                'prix_mensuel'  => $s->prix_mensuel,
                'declarations'  => $s->declarations ?? [],
                'date_debut'    => $s->date_debut->format('d/m/Y'),
                'date_fin'      => $s->date_fin?->format('d/m/Y') ?? 'En cours',
                'statut'        => $s->statut_paiement,
                'mois_ecoules'  => $mois,
                'total_du'      => $s->totalDu(),
                'par_annee'     => $s->prix_mensuel ? $s->prix_mensuel * 12 : null,
            ];
        });

        $totalDu = $company->appSubscriptions->sum(fn($s) => $s->totalDu())
                 + $company->serviceHonoraires->sum(fn($s) => $s->totalDu());

        return response()->json([
            'company'       => $company->company_name,
            'subscriptions' => $subscriptions,
            'services'      => $services,
            'total_du'      => $totalDu,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // API MOBILE (Sanctum)
    // ═══════════════════════════════════════════════════════════════

    /**
     * API : Vue globale de tous les honoraires
     */
    public function apiIndex()
    {
        $all = AppSubscription::with('company')->get();
        $services = ServiceHonoraire::with('company')->get();

        $apps = [];
        foreach (['RHFLOW', 'COMPTAFLOW', 'TASKFLOW', 'SELFLOW', 'LEGALFLOW'] as $app) {
            $subs = $all->where('app_name', $app);
            $apps[] = [
                'name'           => $app,
                'total_clients'  => $subs->count(),
                'total_mensuel'  => $subs->sum('prix_mensuel'),
                'total_du'       => round($subs->sum(fn($s) => $s->totalDu()), 2),
            ];
        }

        return response()->json([
            'currency'             => 'FCFA',
            'total_apps_du'        => round($all->sum(fn($s) => $s->totalDu()), 2),
            'total_services_du'    => round($services->sum(fn($s) => $s->totalDu()), 2),
            'grand_total_du'       => round(
                $all->sum(fn($s) => $s->totalDu()) + $services->sum(fn($s) => $s->totalDu()), 2
            ),
            'apps'     => $apps,
            'updated'  => now()->toIso8601String(),
        ]);
    }

    /**
     * API : Détail d'une entreprise
     */
    public function apiShow(int $companyId)
    {
        $company = Company::with(['appSubscriptions', 'serviceHonoraires'])->findOrFail($companyId);

        $subscriptions = $company->appSubscriptions->map(fn($s) => [
            'app'           => $s->app_name,
            'pack'          => $s->pack_name,
            'prix_mensuel'  => $s->prix_mensuel,
            'date_debut'    => $s->date_debut?->toDateString(),
            'date_fin'      => $s->date_fin?->toDateString(),
            'statut'        => $s->statut_paiement,
            'mois_ecoules'  => $s->moisEcoules(),
            'total_du'      => round($s->totalDu(), 2),
            'par_annee'     => round($s->prix_mensuel * 12, 2),
        ]);

        $services = $company->serviceHonoraires->map(fn($s) => [
            'service'       => $s->service_name,
            'prix_mensuel'  => $s->prix_mensuel,
            'declarations'  => $s->declarations ?? [],
            'statut'        => $s->statut_paiement,
            'total_du'      => round($s->totalDu(), 2),
        ]);

        return response()->json([
            'company_id'    => $company->id,
            'company'       => $company->company_name,
            'currency'      => 'FCFA',
            'subscriptions' => $subscriptions,
            'services'      => $services,
            'total_du'      => round(
                $company->appSubscriptions->sum(fn($s) => $s->totalDu())
              + $company->serviceHonoraires->sum(fn($s) => $s->totalDu()), 2
            ),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // PARAMÉTRAGE : Catalogue de prix
    // ═══════════════════════════════════════════════════════════════

    /**
     * GET /superadmin/honoraires/parametrage
     * Retourne le catalogue de prix JSON pour le panneau de paramétrage
     */
    public function getPrixCatalogue()
    {
        $apps  = CatalogPrice::catalogueApps();
        $services = CatalogPrice::catalogueServices();

        // Charger les prix depuis la DB
        $dbPrix = CatalogPrice::all()->keyBy(fn($r) => $r->type . '|' . $r->app_name . '|' . $r->pack_name);

        $catalogue = [];

        // --- Apps ---
        foreach ($apps as $appName => $packs) {
            $packData = [];
            // Récupérer les prix par défaut (fallback)
            $defaults = CatalogPrice::prixParApp($appName);
            foreach ($packs as $pack) {
                $key = 'app|' . $appName . '|' . $pack;
                $row = $dbPrix[$key] ?? null;
                $packData[] = [
                    'pack'        => $pack,
                    'prix'        => $row ? $row->prix_mensuel : ($defaults[$pack] ?? null),
                    'sur_mesure'  => $row ? $row->sur_mesure : is_null($defaults[$pack] ?? 0),
                    'actif'       => $row ? $row->actif : true,
                    'in_db'       => !is_null($row),
                ];
            }
            $catalogue['apps'][$appName] = $packData;
        }

        // --- Services ---
        foreach ($services as $service) {
            $key = 'service||' . $service;
            $row = $dbPrix[$key] ?? null;
            $catalogue['services'][] = [
                'service'    => $service,
                'prix'       => $row ? $row->prix_mensuel : null,
                'sur_mesure' => $row ? $row->sur_mesure : false,
                'actif'      => $row ? $row->actif : true,
                'in_db'      => !is_null($row),
            ];
        }

        return response()->json($catalogue);
    }

    /**
     * POST /superadmin/honoraires/parametrage
     * Sauvegarde un prix dans le catalogue
     */
    public function updatePrix(Request $request)
    {
        $validated = $request->validate([
            'type'         => 'required|in:app,service',
            'app_name'     => 'nullable|string',
            'pack_name'    => 'required|string',
            'prix_mensuel' => 'nullable|numeric|min:0',
            'sur_mesure'   => 'boolean',
            'actif'        => 'boolean',
            'notes'        => 'nullable|string',
        ]);

        CatalogPrice::updateOrCreate(
            [
                'type'      => $validated['type'],
                'app_name'  => $validated['app_name'] ?? null,
                'pack_name' => $validated['pack_name'],
            ],
            [
                'prix_mensuel' => $validated['sur_mesure'] ? null : ($validated['prix_mensuel'] ?? null),
                'sur_mesure'   => $validated['sur_mesure'] ?? false,
                'actif'        => $validated['actif'] ?? true,
                'notes'        => $validated['notes'] ?? null,
            ]
        );

        return response()->json(['success' => true, 'message' => 'Prix mis à jour avec succès.']);
    }
}
