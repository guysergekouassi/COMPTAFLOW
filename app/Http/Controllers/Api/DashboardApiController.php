<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * DashboardApiController
 *
 * Expose les KPIs financiers de ComptaFlow au FlowHub via l'API.
 * Sécurisé par le middleware VerifyHubToken.
 *
 * Endpoint : GET /api/dashboard/kpis?company_id={id}
 *
 * Format de réponse attendu par le Hub :
 * {
 *   "status": "success",
 *   "data": {
 *     "company_name":        string,
 *     "solde_banque":        float,   // Comptes Classe 52x
 *     "solde_caisse":        float,   // Comptes Classe 57x
 *     "chiffre_affaires":    float,   // Comptes Classe 70x (Crédits)
 *     "depenses":            float,   // Comptes Classe 60x–65x (Débits)
 *     "creances_clients":    float,   // Compte 411xxx (Débit net)
 *     "dettes_fournisseurs": float,   // Compte 401xxx (Crédit net)
 *     "tva_a_declarer":      float,   // Compte 443xxx (Crédit net)
 *     "provisions_sociales": float,   // Compte 431xxx (Crédit net)
 *     "chart_flux":          object,  // Encaissements/Décaissements 6 mois
 *     "chart_charges":       object,  // Répartition charges OPEX
 *   }
 * }
 */
class DashboardApiController extends Controller
{
    /**
     * Retourne la liste simplifiée des entreprises pour le Hub
     */
    public function companies(Request $request)
    {
        try {
            $companies = DB::table('companies')
                ->select('id', 'company_name as name')
                ->orderBy('company_name')
                ->get();

            return response()->json([
                'status' => 'success',
                'data'   => $companies,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Erreur lors de la récupération des entreprises : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Point d'entrée principal - retourne tous les KPIs pour le Hub
     */
    public function kpis(Request $request)
    {
        try {
            $companyId = $request->input('company_id', 1);

            // Vérification que la société existe
            $company = DB::table('companies')
                ->where('id', $companyId)
                ->first();

            if (!$company) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Société introuvable (ID: {$companyId})",
                ], 404);
            }

            // Exercice comptable actif (non clôturé)
            $exercice = DB::table('exercices_comptables')
                ->where('company_id', $companyId)
                ->where('cloturer', 0)
                ->orderBy('date_debut', 'desc')
                ->first();

            // Période de l'exercice actif (ou année courante par défaut)
            $dateDebut = $exercice ? $exercice->date_debut : Carbon::now()->startOfYear()->toDateString();
            $dateFin   = $exercice ? $exercice->date_fin   : Carbon::now()->toDateString();

            // =========================================================
            // 1. TRÉSORERIE (Classe 5 SYSCOHADA)
            //    Banque = 52xxxxxx | Caisse = 57xxxxxx
            // =========================================================
            $soldeBanque = $this->calculerSoldeCompte($companyId, '52', $dateDebut, $dateFin);
            $soldeCaisse = $this->calculerSoldeCompte($companyId, '57', $dateDebut, $dateFin);

            // =========================================================
            // 2. CHIFFRE D'AFFAIRES (Classe 70 SYSCOHADA — Ventes)
            //    Les produits s'accumulent en CRÉDIT
            // =========================================================
            $chiffreAffaires = $this->calculerMontant($companyId, '70', 'credit', $dateDebut, $dateFin);

            // =========================================================
            // 3. DÉPENSES / CHARGES (Classes 60–65 SYSCOHADA)
            //    Les charges s'accumulent en DÉBIT
            // =========================================================
            $depenses = $this->calculerCharges($companyId, $dateDebut, $dateFin);

            // =========================================================
            // 4. CRÉANCES CLIENTS (Compte 411xxx — Débit net)
            //    Solde débiteur = montant dû par les clients
            // =========================================================
            $creancesClients = $this->calculerSoldeCompte($companyId, '411', $dateDebut, $dateFin);
            $creancesClients = max(0, $creancesClients); // Ne peut être négatif

            // =========================================================
            // 5. DETTES FOURNISSEURS (Compte 401xxx — Crédit net)
            //    Solde créditeur = montant dû aux fournisseurs
            // =========================================================
            $rawDettes = $this->calculerSoldeCompte($companyId, '401', $dateDebut, $dateFin);
            $dettesFournisseurs = max(0, -$rawDettes); // Inversé car créditeur

            // =========================================================
            // 6. TVA À DÉCLARER (Compte 443xxx — Crédit net)
            //    TVA collectée sur les ventes
            // =========================================================
            $rawTva = $this->calculerSoldeCompte($companyId, '443', $dateDebut, $dateFin);
            $tvaDeclarer = max(0, -$rawTva);

            // =========================================================
            // 7. PROVISIONS SOCIALES (Compte 431xxx — Crédit net)
            //    CNPS et charges sociales
            // =========================================================
            $rawProvisions = $this->calculerSoldeCompte($companyId, '431', $dateDebut, $dateFin);
            $provisionsSociales = max(0, -$rawProvisions);

            // =========================================================
            // 8. GRAPHIQUE FLUX (Encaissements vs Décaissements)
            //    6 derniers mois glissants
            // =========================================================
            $chartFlux = $this->calculerFluxMensuels($companyId, 6);

            // =========================================================
            // 9. GRAPHIQUE RÉPARTITION CHARGES (OPEX)
            // =========================================================
            $chartCharges = $this->calculerRepartitionCharges($companyId, $dateDebut, $dateFin);

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'company_name'        => $company->company_name,
                    'exercice'            => $exercice ? $exercice->intitule : 'Exercice courant',
                    'periode'             => [
                        'debut' => $dateDebut,
                        'fin'   => $dateFin,
                    ],
                    // KPIs principaux
                    'solde_banque'        => round($soldeBanque, 2),
                    'solde_caisse'        => round($soldeCaisse, 2),
                    'chiffre_affaires'    => round($chiffreAffaires, 2),
                    'depenses'            => round($depenses, 2),
                    'creances_clients'    => round($creancesClients, 2),
                    'dettes_fournisseurs' => round($dettesFournisseurs, 2),
                    'tva_a_declarer'      => round($tvaDeclarer, 2),
                    'provisions_sociales' => round($provisionsSociales, 2),
                    // Données graphiques
                    'chart_flux'          => $chartFlux,
                    'chart_charges'       => $chartCharges,
                ],
                'meta' => [
                    'generated_at' => Carbon::now()->toIso8601String(),
                    'company_id'   => $companyId,
                ],
            ]);

        } catch (\Throwable $e) {
            \Log::error('[ComptaFlow Hub KPI Error] ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Erreur lors du calcul des KPIs : ' . $e->getMessage(),
            ], 500);
        }
    }

    // =========================================================
    // MÉTHODES PRIVÉES DE CALCUL
    // =========================================================

    /**
     * Calcule le solde net (Débit - Crédit) d'un groupe de comptes
     * identifié par son préfixe SYSCOHADA.
     *
     * @param int    $companyId  ID de la société
     * @param string $prefix     Préfixe du numéro de compte (ex: '52', '411')
     * @param string $dateDebut  Date de début (Y-m-d)
     * @param string $dateFin    Date de fin (Y-m-d)
     * @return float Solde net positif = débiteur, négatif = créditeur
     */
    private function calculerSoldeCompte(int $companyId, string $prefix, string $dateDebut, string $dateFin): float
    {
        $result = DB::table('ecriture_comptables as e')
            ->join('plan_comptables as p', 'e.plan_comptable_id', '=', 'p.id')
            ->where('e.company_id', $companyId)
            ->where('p.numero_de_compte', 'like', $prefix . '%')
            ->whereBetween('e.date', [$dateDebut, $dateFin])
            ->selectRaw('
                COALESCE(SUM(e.debit), 0)  as total_debit,
                COALESCE(SUM(e.credit), 0) as total_credit
            ')
            ->first();

        if (!$result) {
            return 0.0;
        }

        return (float) $result->total_debit - (float) $result->total_credit;
    }

    /**
     * Calcule le total d'un type de mouvement (débit ou crédit)
     * pour un groupe de comptes.
     *
     * @param string $type 'debit' ou 'credit'
     */
    private function calculerMontant(int $companyId, string $prefix, string $type, string $dateDebut, string $dateFin): float
    {
        $result = DB::table('ecriture_comptables as e')
            ->join('plan_comptables as p', 'e.plan_comptable_id', '=', 'p.id')
            ->where('e.company_id', $companyId)
            ->where('p.numero_de_compte', 'like', $prefix . '%')
            ->whereBetween('e.date', [$dateDebut, $dateFin])
            ->selectRaw("COALESCE(SUM(e.{$type}), 0) as total")
            ->first();

        return $result ? (float) $result->total : 0.0;
    }

    /**
     * Calcule le total des charges d'exploitation.
     * Classes 60 (Achats), 61 (Transport), 62 (Services ext.),
     * 63 (Impôts/taxes), 64 (Personnel), 65 (Autres charges).
     */
    private function calculerCharges(int $companyId, string $dateDebut, string $dateFin): float
    {
        $result = DB::table('ecriture_comptables as e')
            ->join('plan_comptables as p', 'e.plan_comptable_id', '=', 'p.id')
            ->where('e.company_id', $companyId)
            ->where(function ($query) {
                $query->where('p.numero_de_compte', 'like', '60%')
                      ->orWhere('p.numero_de_compte', 'like', '61%')
                      ->orWhere('p.numero_de_compte', 'like', '62%')
                      ->orWhere('p.numero_de_compte', 'like', '63%')
                      ->orWhere('p.numero_de_compte', 'like', '64%')
                      ->orWhere('p.numero_de_compte', 'like', '65%');
            })
            ->whereBetween('e.date', [$dateDebut, $dateFin])
            ->selectRaw('COALESCE(SUM(e.debit), 0) as total')
            ->first();

        return $result ? (float) $result->total : 0.0;
    }

    /**
     * Calcule les flux mensuels (encaissements/décaissements)
     * sur les N derniers mois glissants, basés sur la Classe 5.
     */
    private function calculerFluxMensuels(int $companyId, int $nombreMois = 6): array
    {
        $labels        = [];
        $encaissements = [];
        $decaissements = [];

        $moisFr = [
            1  => 'Jan', 2  => 'Fév', 3  => 'Mar',
            4  => 'Avr', 5  => 'Mai', 6  => 'Juin',
            7  => 'Juil', 8 => 'Aoû', 9  => 'Sep',
            10 => 'Oct', 11 => 'Nov', 12 => 'Déc',
        ];

        for ($i = $nombreMois - 1; $i >= 0; $i--) {
            $moisDate  = Carbon::now()->subMonths($i);
            $annee     = $moisDate->year;
            $mois      = $moisDate->month;
            $dateDebut = $moisDate->startOfMonth()->toDateString();
            $dateFin   = $moisDate->copy()->endOfMonth()->toDateString();

            $labels[] = ($moisFr[$mois] ?? $mois) . ' ' . substr($annee, 2);

            // Encaissements = Crédits sur les comptes de trésorerie (Classe 5)
            // = de l'argent qui ENTRE dans la tréso
            $encaissement = DB::table('ecriture_comptables as e')
                ->join('plan_comptables as p', 'e.plan_comptable_id', '=', 'p.id')
                ->where('e.company_id', $companyId)
                ->where(function ($q) {
                    $q->where('p.numero_de_compte', 'like', '52%')
                      ->orWhere('p.numero_de_compte', 'like', '57%');
                })
                ->whereBetween('e.date', [$dateDebut, $dateFin])
                ->selectRaw('COALESCE(SUM(e.debit), 0) as total')
                ->first();

            // Décaissements = Débits sur les comptes de trésorerie
            // = de l'argent qui SORT de la tréso
            $decaissement = DB::table('ecriture_comptables as e')
                ->join('plan_comptables as p', 'e.plan_comptable_id', '=', 'p.id')
                ->where('e.company_id', $companyId)
                ->where(function ($q) {
                    $q->where('p.numero_de_compte', 'like', '52%')
                      ->orWhere('p.numero_de_compte', 'like', '57%');
                })
                ->whereBetween('e.date', [$dateDebut, $dateFin])
                ->selectRaw('COALESCE(SUM(e.credit), 0) as total')
                ->first();

            $encaissements[] = round((float) ($encaissement->total ?? 0) / 1000, 2); // En milliers FCFA
            $decaissements[] = round((float) ($decaissement->total ?? 0) / 1000, 2);
        }

        return [
            'labels'        => $labels,
            'encaissements' => $encaissements,
            'decaissements' => $decaissements,
            'unite'         => 'K FCFA',
        ];
    }

    /**
     * Calcule la répartition des charges par grande famille OPEX (SYSCOHADA).
     * Pour le graphique doughnut du Hub.
     */
    private function calculerRepartitionCharges(int $companyId, string $dateDebut, string $dateFin): array
    {
        $categories = [
            ['label' => 'Achats (60)',      'prefix' => '60'],
            ['label' => 'Services ext. (61-62)', 'prefix' => null, 'prefixes' => ['61', '62']],
            ['label' => 'Personnel (64)',   'prefix' => '64'],
            ['label' => 'Impôts (63)',      'prefix' => '63'],
            ['label' => 'Autres (65)',      'prefix' => '65'],
        ];

        $labels = [];
        $data   = [];

        foreach ($categories as $cat) {
            $query = DB::table('ecriture_comptables as e')
                ->join('plan_comptables as p', 'e.plan_comptable_id', '=', 'p.id')
                ->where('e.company_id', $companyId)
                ->whereBetween('e.date', [$dateDebut, $dateFin]);

            if (isset($cat['prefixes'])) {
                $query->where(function ($q) use ($cat) {
                    foreach ($cat['prefixes'] as $pref) {
                        $q->orWhere('p.numero_de_compte', 'like', $pref . '%');
                    }
                });
            } else {
                $query->where('p.numero_de_compte', 'like', $cat['prefix'] . '%');
            }

            $total = $query->selectRaw('COALESCE(SUM(e.debit), 0) as total')->first();
            $montant = round((float) ($total->total ?? 0));

            if ($montant > 0) {
                $labels[] = $cat['label'];
                $data[]   = $montant;
            }
        }

        // Si aucune donnée réelle, retourner des données placeholder pour le graphique
        if (empty($data)) {
            return [
                'labels' => ['Achats (60)', 'Personnel (64)', 'Impôts (63)', 'Autres'],
                'data'   => [0, 0, 0, 0],
            ];
        }

        return [
            'labels' => $labels,
            'data'   => $data,
        ];
    }
}
