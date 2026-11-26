<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;
use App\Services\AccountingReportingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Expose les 8 rapports comptables ComptaFlow au format unifié attendu par FlowHub (hub-rapports.js).
 * Sécurisé par le middleware VerifyHubToken (X-Hub-Token / X-Company-Id).
 */
class HubRapportsController extends Controller
{
    public function __construct(protected AccountingReportingService $reportingService)
    {
    }

    /**
     * Résout l'entreprise, l'exercice et le mois à partir de la requête Hub.
     */
    private function resolveContext(Request $request): array
    {
        $companyId = (int) ($request->query('company_id') ?: $request->header('X-Company-Id') ?: 1);
        $company = Company::withoutGlobalScopes()->find($companyId);

        $exerciceId = $request->query('exercice_id') ?: $request->header('X-Exercice-Id');
        $exercice = $exerciceId
            ? ExerciceComptable::withoutGlobalScopes()->where('company_id', $companyId)->find($exerciceId)
            : ExerciceComptable::withoutGlobalScopes()->where('company_id', $companyId)->where('is_active', 1)->first();

        if (!$exercice) {
            $exercice = ExerciceComptable::withoutGlobalScopes()->where('company_id', $companyId)->orderByDesc('date_debut')->first();
        }

        $month = $request->query('month');
        if ($month && str_contains((string) $month, '-')) {
            $parts = explode('-', (string) $month);
            $month = (int) end($parts);
        } elseif ($month !== null && $month !== '') {
            $month = (int) $month;
        } else {
            $month = null;
        }

        return [
            'companyId'  => $companyId,
            'entreprise' => $company->company_name ?? $company->name ?? "Compte n°{$companyId}",
            'exercice'   => $exercice,
            'exerciceId' => $exercice?->id,
            'month'      => $month,
        ];
    }

    private function noExerciceResponse(array $ctx, string $titre)
    {
        return response()->json([
            'status'  => 'error',
            'message' => "Aucun exercice comptable trouvé pour {$ctx['entreprise']} ({$titre}).",
        ], 422);
    }

    /**
     * 1. Balance générale
     */
    public function balance(Request $request)
    {
        $ctx = $this->resolveContext($request);
        if (!$ctx['exerciceId']) {
            return $this->noExerciceResponse($ctx, 'Balance générale');
        }

        $classe = $request->query('classe');
        $rows = $this->reportingService->getBalanceData($ctx['exerciceId'], $ctx['companyId'], $ctx['month']);

        if ($classe !== null && $classe !== '') {
            $rows = array_values(array_filter($rows, fn ($r) => str_starts_with((string) ($r['numero'] ?? ''), (string) $classe)));
        }

        $totDebit = 0;
        $totCredit = 0;
        $totSoldeD = 0;
        $totSoldeC = 0;
        $lignes = [];

        foreach ($rows as $r) {
            $d = (float) ($r['debit'] ?? 0);
            $c = (float) ($r['credit'] ?? 0);
            $sd = (float) ($r['solde_debiteur'] ?? 0);
            $sc = (float) ($r['solde_crediteur'] ?? 0);
            $totDebit += $d;
            $totCredit += $c;
            $totSoldeD += $sd;
            $totSoldeC += $sc;

            $lignes[] = [
                'type'    => 'ligne',
                'valeurs' => [
                    'numero'          => $r['numero'],
                    'intitule'        => $r['intitule'],
                    'debit'           => $d,
                    'credit'          => $c,
                    'solde_debiteur'  => $sd,
                    'solde_crediteur' => $sc,
                ],
            ];
        }

        if (count($lignes)) {
            $lignes[] = [
                'type'    => 'total',
                'valeurs' => [
                    'numero'          => 'TOTAL',
                    'intitule'        => 'Totaux généraux',
                    'debit'           => $totDebit,
                    'credit'          => $totCredit,
                    'solde_debiteur'  => $totSoldeD,
                    'solde_crediteur' => $totSoldeC,
                ],
            ];
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'rapport'     => 'balance',
                'titre'       => 'Balance générale',
                'entreprise'  => $ctx['entreprise'],
                'sous_titre'  => $ctx['exercice']->intitule ?? 'Exercice courant',
                'unite'       => 'FCFA',
                'indicateurs' => [
                    ['libelle' => 'Total des mouvements au débit', 'valeur' => $totDebit, 'format' => 'montant'],
                    ['libelle' => 'Total des mouvements au crédit', 'valeur' => $totCredit, 'format' => 'montant'],
                    ['libelle' => 'Comptes mouvementés', 'valeur' => count($rows), 'format' => 'nombre'],
                    ['libelle' => 'Écart débit / crédit', 'valeur' => $totDebit - $totCredit, 'format' => 'montant'],
                ],
                'graphiques'  => [],
                'tableaux'    => [[
                    'titre'    => 'Balance générale',
                    'colonnes' => [
                        ['cle' => 'numero', 'libelle' => 'Compte', 'format' => 'texte'],
                        ['cle' => 'intitule', 'libelle' => 'Intitulé', 'format' => 'texte'],
                        ['cle' => 'debit', 'libelle' => 'Débit', 'format' => 'montant'],
                        ['cle' => 'credit', 'libelle' => 'Crédit', 'format' => 'montant'],
                        ['cle' => 'solde_debiteur', 'libelle' => 'Solde débiteur', 'format' => 'montant'],
                        ['cle' => 'solde_crediteur', 'libelle' => 'Solde créditeur', 'format' => 'montant'],
                    ],
                    'lignes'   => $lignes,
                ]],
            ],
        ]);
    }

    /**
     * 2. Balance des tiers (clients 41x / fournisseurs 40x)
     */
    public function balanceTiers(Request $request)
    {
        $ctx = $this->resolveContext($request);
        if (!$ctx['exerciceId']) {
            return $this->noExerciceResponse($ctx, 'Balance des tiers');
        }

        $typeTiers = $request->query('type_tiers');

        $query = DB::table('ecriture_comptables')
            ->join('plan_tiers', 'ecriture_comptables.plan_tiers_id', '=', 'plan_tiers.id')
            ->where('ecriture_comptables.company_id', $ctx['companyId'])
            ->where('ecriture_comptables.exercices_comptables_id', $ctx['exerciceId'])
            ->whereNotNull('ecriture_comptables.plan_tiers_id');

        if ($ctx['month']) {
            $query->whereMonth('ecriture_comptables.date', $ctx['month']);
        }
        if ($typeTiers === 'clients') {
            $query->where(function ($q) {
                $q->where('plan_tiers.numero_de_tiers', 'like', '41%')
                  ->orWhere('plan_tiers.type_de_tiers', 'client');
            });
        } elseif ($typeTiers === 'fournisseurs') {
            $query->where(function ($q) {
                $q->where('plan_tiers.numero_de_tiers', 'like', '40%')
                  ->orWhere('plan_tiers.type_de_tiers', 'fournisseur');
            });
        }

        $rows = $query->select(
                'plan_tiers.id',
                'plan_tiers.numero_de_tiers as numero',
                'plan_tiers.intitule as intitule',
                DB::raw('SUM(ecriture_comptables.debit) as debit'),
                DB::raw('SUM(ecriture_comptables.credit) as credit')
            )
            ->groupBy('plan_tiers.id', 'plan_tiers.numero_de_tiers', 'plan_tiers.intitule')
            ->orderBy('plan_tiers.numero_de_tiers')
            ->get();

        $soldeClients = 0;
        $soldeFournisseurs = 0;
        $totD = 0;
        $totC = 0;
        $lignes = [];

        foreach ($rows as $r) {
            $d = (float) $r->debit;
            $c = (float) $r->credit;
            $solde = $d - $c;
            $totD += $d;
            $totC += $c;

            if (str_starts_with((string) $r->numero, '41')) {
                $soldeClients += $solde;
            } elseif (str_starts_with((string) $r->numero, '40')) {
                $soldeFournisseurs += -$solde;
            }

            $lignes[] = [
                'type'    => 'ligne',
                'valeurs' => [
                    'numero'          => $r->numero,
                    'intitule'        => $r->intitule,
                    'debit'           => $d,
                    'credit'          => $c,
                    'solde_debiteur'  => $solde > 0 ? $solde : 0,
                    'solde_crediteur' => $solde < 0 ? abs($solde) : 0,
                ],
            ];
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'rapport'     => 'balance-tiers',
                'titre'       => 'Balance des tiers',
                'entreprise'  => $ctx['entreprise'],
                'sous_titre'  => $ctx['exercice']->intitule ?? 'Exercice courant',
                'unite'       => 'FCFA',
                'indicateurs' => [
                    ['libelle' => 'Solde des clients', 'valeur' => $soldeClients, 'format' => 'montant'],
                    ['libelle' => 'Solde des fournisseurs', 'valeur' => $soldeFournisseurs, 'format' => 'montant'],
                    ['libelle' => 'Tiers mouvementés', 'valeur' => count($rows), 'format' => 'nombre'],
                    ['libelle' => 'Total mouvements débit', 'valeur' => $totD, 'format' => 'montant'],
                ],
                'graphiques'  => [],
                'tableaux'    => [[
                    'titre'    => 'Balance des tiers',
                    'colonnes' => [
                        ['cle' => 'numero', 'libelle' => 'N° Tiers', 'format' => 'texte'],
                        ['cle' => 'intitule', 'libelle' => 'Nom / Raison sociale', 'format' => 'texte'],
                        ['cle' => 'debit', 'libelle' => 'Débit', 'format' => 'montant'],
                        ['cle' => 'credit', 'libelle' => 'Crédit', 'format' => 'montant'],
                        ['cle' => 'solde_debiteur', 'libelle' => 'Solde débiteur', 'format' => 'montant'],
                        ['cle' => 'solde_crediteur', 'libelle' => 'Solde créditeur', 'format' => 'montant'],
                    ],
                    'lignes'   => $lignes,
                ]],
            ],
        ]);
    }

    /**
     * 3. Grand livre général (paginé)
     */
    public function grandLivre(Request $request)
    {
        $ctx = $this->resolveContext($request);
        if (!$ctx['exerciceId']) {
            return $this->noExerciceResponse($ctx, 'Grand livre');
        }

        $compte = $request->query('compte');
        $perPage = 100;
        $page = max(1, (int) $request->query('page', 1));

        $query = EcritureComptable::withoutGlobalScopes()
            ->with([
                'planComptable' => fn ($q) => $q->withoutGlobalScopes()->select('id', 'numero_de_compte', 'intitule'),
                'codeJournal'   => fn ($q) => $q->withoutGlobalScopes()->select('id', 'code_journal'),
            ])
            ->where('company_id', $ctx['companyId'])
            ->where('exercices_comptables_id', $ctx['exerciceId']);

        if ($ctx['month']) {
            $query->whereMonth('date', $ctx['month']);
        }
        if ($compte) {
            $query->whereHas('planComptable', fn ($q) => $q->withoutGlobalScopes()->where('numero_de_compte', 'like', $compte . '%'));
        }

        $paginator = $query->orderBy('date')->orderBy('id')->paginate($perPage, ['*'], 'page', $page);

        $lignes = [];
        foreach ($paginator->items() as $ec) {
            $lignes[] = [
                'type'    => 'ligne',
                'valeurs' => [
                    'date'    => $ec->date,
                    'journal' => $ec->codeJournal->code_journal ?? '',
                    'piece'   => $ec->reference_piece ?: ($ec->n_saisie ?? ''),
                    'compte'  => $ec->planComptable->numero_de_compte ?? '',
                    'libelle' => $ec->description_operation ?: ($ec->planComptable->intitule ?? ''),
                    'debit'   => (float) $ec->debit,
                    'credit'  => (float) $ec->credit,
                ],
            ];
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'rapport'     => 'grand-livre',
                'titre'       => 'Grand livre',
                'entreprise'  => $ctx['entreprise'],
                'sous_titre'  => $ctx['exercice']->intitule ?? 'Exercice courant',
                'unite'       => 'FCFA',
                'indicateurs' => [],
                'graphiques'  => [],
                'tableaux'    => [[
                    'titre'    => 'Écritures comptables',
                    'colonnes' => [
                        ['cle' => 'date', 'libelle' => 'Date', 'format' => 'date'],
                        ['cle' => 'journal', 'libelle' => 'Journal', 'format' => 'texte'],
                        ['cle' => 'piece', 'libelle' => 'Pièce', 'format' => 'texte'],
                        ['cle' => 'compte', 'libelle' => 'Compte', 'format' => 'texte'],
                        ['cle' => 'libelle', 'libelle' => 'Libellé', 'format' => 'texte'],
                        ['cle' => 'debit', 'libelle' => 'Débit', 'format' => 'montant'],
                        ['cle' => 'credit', 'libelle' => 'Crédit', 'format' => 'montant'],
                    ],
                    'lignes'   => $lignes,
                ]],
                'pagination'  => [
                    'page'         => $paginator->currentPage(),
                    'pages'        => $paginator->lastPage(),
                    'total_lignes' => $paginator->total(),
                ],
            ],
        ]);
    }

    /**
     * 4. Grand livre des tiers (paginé)
     */
    public function grandLivreTiers(Request $request)
    {
        $ctx = $this->resolveContext($request);
        if (!$ctx['exerciceId']) {
            return $this->noExerciceResponse($ctx, 'Grand livre des tiers');
        }

        $typeTiers = $request->query('type_tiers');
        $tiers = $request->query('tiers');
        $perPage = 100;
        $page = max(1, (int) $request->query('page', 1));

        $query = EcritureComptable::withoutGlobalScopes()
            ->with([
                'planTiers'   => fn ($q) => $q->withoutGlobalScopes()->select('id', 'numero_de_tiers', 'intitule', 'type_de_tiers'),
                'codeJournal' => fn ($q) => $q->withoutGlobalScopes()->select('id', 'code_journal'),
            ])
            ->where('company_id', $ctx['companyId'])
            ->where('exercices_comptables_id', $ctx['exerciceId'])
            ->whereNotNull('plan_tiers_id');

        if ($ctx['month']) {
            $query->whereMonth('date', $ctx['month']);
        }
        if ($typeTiers === 'clients') {
            $query->whereHas('planTiers', fn ($q) => $q->withoutGlobalScopes()->where('numero_de_tiers', 'like', '41%')->orWhere('type_de_tiers', 'client'));
        } elseif ($typeTiers === 'fournisseurs') {
            $query->whereHas('planTiers', fn ($q) => $q->withoutGlobalScopes()->where('numero_de_tiers', 'like', '40%')->orWhere('type_de_tiers', 'fournisseur'));
        }
        if ($tiers) {
            $query->whereHas('planTiers', fn ($q) => $q->withoutGlobalScopes()->where('numero_de_tiers', 'like', $tiers . '%'));
        }

        $paginator = $query->orderBy('date')->orderBy('id')->paginate($perPage, ['*'], 'page', $page);

        $lignes = [];
        foreach ($paginator->items() as $ec) {
            $lignes[] = [
                'type'    => 'ligne',
                'valeurs' => [
                    'date'    => $ec->date,
                    'journal' => $ec->codeJournal->code_journal ?? '',
                    'tiers'   => trim(($ec->planTiers->numero_de_tiers ?? '') . ' ' . ($ec->planTiers->intitule ?? '')),
                    'libelle' => $ec->description_operation ?? '',
                    'debit'   => (float) $ec->debit,
                    'credit'  => (float) $ec->credit,
                ],
            ];
        }

        return response()->json([
            'status' => 'success',
            'data'   => [
                'rapport'     => 'grand-livre-tiers',
                'titre'       => 'Grand livre des tiers',
                'entreprise'  => $ctx['entreprise'],
                'sous_titre'  => $ctx['exercice']->intitule ?? 'Exercice courant',
                'unite'       => 'FCFA',
                'indicateurs' => [],
                'graphiques'  => [],
                'tableaux'    => [[
                    'titre'    => 'Écritures des tiers',
                    'colonnes' => [
                        ['cle' => 'date', 'libelle' => 'Date', 'format' => 'date'],
                        ['cle' => 'journal', 'libelle' => 'Journal', 'format' => 'texte'],
                        ['cle' => 'tiers', 'libelle' => 'Tiers', 'format' => 'texte'],
                        ['cle' => 'libelle', 'libelle' => 'Libellé', 'format' => 'texte'],
                        ['cle' => 'debit', 'libelle' => 'Débit', 'format' => 'montant'],
                        ['cle' => 'credit', 'libelle' => 'Crédit', 'format' => 'montant'],
                    ],
                    'lignes'   => $lignes,
                ]],
                'pagination'  => [
                    'page'         => $paginator->currentPage(),
                    'pages'        => $paginator->lastPage(),
                    'total_lignes' => $paginator->total(),
                ],
            ],
        ]);
    }

    /**
     * 5. Bilan (Actif & Passif SYSCOHADA)
     */
    public function bilan(Request $request)
    {
        $ctx = $this->resolveContext($request);
        if (!$ctx['exerciceId']) {
            return $this->noExerciceResponse($ctx, 'Bilan');
        }

        $bilan = $this->reportingService->getBilanData($ctx['exerciceId'], $ctx['companyId'], $ctx['month'], false);
        $actif = $bilan['actif'] ?? [];
        $passif = $bilan['passif'] ?? [];

        $totalBilan = (float) ($actif['total_net'] ?? 0);
        $capitauxPropres = (float) ($passif['capitaux']['total'] ?? 0);
        $tresoNette = (float) ($actif['tresorerie']['total_net'] ?? 0) - (float) ($passif['tresorerie']['total'] ?? 0);

        $lignesActif = [];
        foreach (['immobilise' => 'Actif immobilisé', 'circulant' => 'Actif circulant', 'tresorerie' => 'Trésorerie-Actif'] as $k => $lbl) {
            if (!isset($actif[$k])) continue;
            $lignesActif[] = ['type' => 'titre', 'valeurs' => ['libelle' => $lbl, 'brut' => $actif[$k]['total_brut'] ?? 0, 'amort' => $actif[$k]['total_amort'] ?? 0, 'net' => $actif[$k]['total_net'] ?? 0]];
            foreach (($actif[$k]['subcategories'] ?? []) as $subKey => $sub) {
                $lignesActif[] = [
                    'type'    => 'ligne',
                    'niveau'  => 1,
                    'valeurs' => [
                        'libelle' => $sub['label'] ?? ucfirst(str_replace('_', ' ', (string) $subKey)),
                        'brut'    => (float) ($sub['brut'] ?? 0),
                        'amort'   => (float) ($sub['amort'] ?? 0),
                        'net'     => (float) ($sub['net'] ?? 0),
                    ],
                ];
            }
        }
        $lignesActif[] = ['type' => 'total', 'valeurs' => ['libelle' => 'TOTAL ACTIF', 'brut' => $actif['total_brut'] ?? 0, 'amort' => $actif['total_amort'] ?? 0, 'net' => $totalBilan]];

        $lignesPassif = [];
        foreach (['capitaux' => 'Capitaux propres', 'dettes_fin' => 'Dettes financières', 'passif_circ' => 'Passif circulant', 'tresorerie' => 'Trésorerie-Passif'] as $k => $lbl) {
            if (!isset($passif[$k])) continue;
            $lignesPassif[] = ['type' => 'titre', 'valeurs' => ['libelle' => $lbl, 'montant' => $passif[$k]['total'] ?? 0]];
            foreach (($passif[$k]['subcategories'] ?? []) as $subKey => $sub) {
                $lignesPassif[] = [
                    'type'    => 'ligne',
                    'niveau'  => 1,
                    'valeurs' => [
                        'libelle' => $sub['label'] ?? ucfirst(str_replace('_', ' ', (string) $subKey)),
                        'montant' => (float) ($sub['total'] ?? 0),
                    ],
                ];
            }
        }
        $lignesPassif[] = ['type' => 'total', 'valeurs' => ['libelle' => 'TOTAL PASSIF', 'montant' => (float) ($passif['total'] ?? 0)]];

        return response()->json([
            'status' => 'success',
            'data'   => [
                'rapport'     => 'bilan',
                'titre'       => 'Bilan (SYSCOHADA)',
                'entreprise'  => $ctx['entreprise'],
                'sous_titre'  => $ctx['exercice']->intitule ?? 'Exercice courant',
                'unite'       => 'FCFA',
                'indicateurs' => [
                    ['libelle' => 'Total du bilan', 'valeur' => $totalBilan, 'format' => 'montant'],
                    ['libelle' => 'Capitaux propres', 'valeur' => $capitauxPropres, 'format' => 'montant'],
                    ['libelle' => 'Trésorerie nette', 'valeur' => $tresoNette, 'format' => 'montant'],
                    ['libelle' => 'Équilibre', 'valeur' => !empty($bilan['equilibre']) ? 'Équilibré' : 'Écart détecté', 'format' => 'texte'],
                ],
                'graphiques'  => [],
                'tableaux'    => [
                    [
                        'titre'    => 'Bilan — Actif',
                        'colonnes' => [
                            ['cle' => 'libelle', 'libelle' => 'Poste', 'format' => 'texte'],
                            ['cle' => 'brut', 'libelle' => 'Brut', 'format' => 'montant'],
                            ['cle' => 'amort', 'libelle' => 'Amort. / Prov.', 'format' => 'montant'],
                            ['cle' => 'net', 'libelle' => 'Net', 'format' => 'montant'],
                        ],
                        'lignes'   => $lignesActif,
                    ],
                    [
                        'titre'    => 'Bilan — Passif',
                        'colonnes' => [
                            ['cle' => 'libelle', 'libelle' => 'Poste', 'format' => 'texte'],
                            ['cle' => 'montant', 'libelle' => 'Montant', 'format' => 'montant'],
                        ],
                        'lignes'   => $lignesPassif,
                    ],
                ],
            ],
        ]);
    }

    /**
     * 6. Compte de résultat (SIG SYSCOHADA)
     */
    public function compteResultat(Request $request)
    {
        $ctx = $this->resolveContext($request);
        if (!$ctx['exerciceId']) {
            return $this->noExerciceResponse($ctx, 'Compte de résultat');
        }

        $sig = $this->reportingService->getSIGData($ctx['exerciceId'], $ctx['companyId'], $ctx['month'], false);
        $ca = (float) (($sig['ventes_marchandises'] ?? 0) + ($sig['prod_vendue'] ?? 0));

        $lignes = [
            ['type' => 'ligne', 'valeurs' => ['libelle' => 'Ventes de marchandises', 'montant' => (float) ($sig['ventes_marchandises'] ?? 0)]],
            ['type' => 'ligne', 'valeurs' => ['libelle' => 'Production vendue', 'montant' => (float) ($sig['prod_vendue'] ?? 0)]],
            ['type' => 'sous_total', 'valeurs' => ['libelle' => 'Marge commerciale', 'montant' => (float) ($sig['marge_commerciale'] ?? 0)]],
            ['type' => 'sous_total', 'valeurs' => ['libelle' => 'Valeur ajoutée', 'montant' => (float) ($sig['valeur_ajoutee'] ?? 0)]],
            ['type' => 'ligne', 'valeurs' => ['libelle' => 'Charges de personnel', 'montant' => -(float) ($sig['charges_personnel'] ?? 0)]],
            ['type' => 'sous_total', 'valeurs' => ['libelle' => "Excédent brut d'exploitation (EBE)", 'montant' => (float) ($sig['ebe'] ?? 0)]],
            ['type' => 'sous_total', 'valeurs' => ['libelle' => "Résultat d'exploitation", 'montant' => (float) ($sig['resultat_exploitation'] ?? 0)]],
            ['type' => 'ligne', 'valeurs' => ['libelle' => 'Résultat financier', 'montant' => (float) ($sig['resultat_financier'] ?? 0)]],
            ['type' => 'ligne', 'valeurs' => ['libelle' => 'Résultat HAO', 'montant' => (float) ($sig['resultat_hao'] ?? 0)]],
            ['type' => 'ligne', 'valeurs' => ['libelle' => 'Impôts sur le résultat', 'montant' => -(float) ($sig['impots_resultat'] ?? 0)]],
            ['type' => 'total', 'valeurs' => ['libelle' => 'Résultat net', 'montant' => (float) ($sig['resultat_net'] ?? 0)]],
        ];

        return response()->json([
            'status' => 'success',
            'data'   => [
                'rapport'     => 'compte-resultat',
                'titre'       => 'Compte de résultat',
                'entreprise'  => $ctx['entreprise'],
                'sous_titre'  => $ctx['exercice']->intitule ?? 'Exercice courant',
                'unite'       => 'FCFA',
                'indicateurs' => [
                    ['libelle' => "Chiffre d'affaires", 'valeur' => $ca, 'format' => 'montant'],
                    ['libelle' => 'Valeur ajoutée', 'valeur' => (float) ($sig['valeur_ajoutee'] ?? 0), 'format' => 'montant'],
                    ['libelle' => "Excédent brut d'exploitation", 'valeur' => (float) ($sig['ebe'] ?? 0), 'format' => 'montant'],
                    ['libelle' => 'Résultat net', 'valeur' => (float) ($sig['resultat_net'] ?? 0), 'format' => 'montant'],
                ],
                'graphiques'  => [],
                'tableaux'    => [[
                    'titre'    => 'Soldes intermédiaires de gestion',
                    'colonnes' => [
                        ['cle' => 'libelle', 'libelle' => 'Poste', 'format' => 'texte'],
                        ['cle' => 'montant', 'libelle' => 'Montant', 'format' => 'montant'],
                    ],
                    'lignes'   => $lignes,
                ]],
            ],
        ]);
    }

    /**
     * 7. Compte d'exploitation (mensuel)
     */
    public function compteExploitation(Request $request)
    {
        $ctx = $this->resolveContext($request);
        if (!$ctx['exerciceId']) {
            return $this->noExerciceResponse($ctx, "Compte d'exploitation");
        }

        $monthly = $this->reportingService->getMonthlyResultatData($ctx['exerciceId'], $ctx['companyId'], false);
        $sig = $this->reportingService->getSIGData($ctx['exerciceId'], $ctx['companyId'], null, false);
        $ca = (float) (($sig['ventes_marchandises'] ?? 0) + ($sig['prod_vendue'] ?? 0));

        $months = $monthly['months'] ?? [];
        $data = $monthly['data'] ?? [];

        $colonnes = [['cle' => 'libelle', 'libelle' => 'Poste', 'format' => 'texte']];
        $monthKeys = [];
        foreach ($months as $idx => $m) {
            $key = 'm' . str_replace('-', '_', (string) ($m['key'] ?? ($m['ym'] ?? sprintf('2026_%02d', $idx + 1))));
            if (!preg_match('/^m\d{4}_\d{2}$/', $key)) {
                $key = sprintf('m%s_%02d', date('Y'), $idx + 1);
            }
            $monthKeys[$idx] = $key;
            $colonnes[] = ['cle' => $key, 'libelle' => $m['label'] ?? $m['name'] ?? $key, 'format' => 'montant'];
        }

        $buildRow = function (string $type, string $libelle, array $series) use ($monthKeys) {
            $vals = ['libelle' => $libelle];
            foreach ($monthKeys as $idx => $key) {
                $vals[$key] = (float) ($series[$idx] ?? 0);
            }
            return ['type' => $type, 'valeurs' => $vals];
        };

        $lignes = [
            $buildRow('sous_total', 'Total Produits', $data['produits']['total'] ?? []),
            $buildRow('sous_total', 'Total Charges', $data['charges']['total'] ?? []),
            $buildRow('total', 'Résultat net', $data['resultat'] ?? []),
        ];

        return response()->json([
            'status' => 'success',
            'data'   => [
                'rapport'     => 'compte-exploitation',
                'titre'       => "Compte d'exploitation",
                'entreprise'  => $ctx['entreprise'],
                'sous_titre'  => $ctx['exercice']->intitule ?? 'Exercice courant',
                'unite'       => 'FCFA',
                'indicateurs' => [
                    ['libelle' => "Chiffre d'affaires", 'valeur' => $ca, 'format' => 'montant'],
                    ['libelle' => "Excédent brut d'exploitation", 'valeur' => (float) ($sig['ebe'] ?? 0), 'format' => 'montant'],
                    ['libelle' => 'Résultat net', 'valeur' => (float) ($sig['resultat_net'] ?? 0), 'format' => 'montant'],
                ],
                'graphiques'  => [],
                'tableaux'    => [[
                    'titre'    => "Compte d'exploitation",
                    'colonnes' => $colonnes,
                    'lignes'   => $lignes,
                ]],
            ],
        ]);
    }

    /**
     * 8. Flux de trésorerie (TFT)
     */
    public function fluxTresorerie(Request $request)
    {
        $ctx = $this->resolveContext($request);
        if (!$ctx['exerciceId']) {
            return $this->noExerciceResponse($ctx, 'Flux de trésorerie');
        }

        $tft = $this->reportingService->getTFTData($ctx['exerciceId'], $ctx['companyId'], $ctx['month'], false);

        $debut = (float) ($tft['tresorerie']['initiale'] ?? 0);
        $fin = (float) ($tft['tresorerie']['finale'] ?? 0);
        $caf = (float) ($tft['operationnel']['caf'] ?? 0);
        $bfr = (float) ($tft['operationnel']['variation_bfr'] ?? 0);
        $fop = (float) ($tft['operationnel']['total'] ?? 0);
        $finv = (float) ($tft['investissement']['total'] ?? 0);
        $ffin = (float) ($tft['financement']['total'] ?? 0);

        $lignes = [
            ['type' => 'ligne', 'valeurs' => ['libelle' => "Capacité d'autofinancement (CAF)", 'montant' => $caf]],
            ['type' => 'ligne', 'valeurs' => ['libelle' => 'Variation du BFR', 'montant' => $bfr]],
            ['type' => 'sous_total', 'valeurs' => ['libelle' => 'Flux des activités opérationnelles', 'montant' => $fop]],
            ['type' => 'sous_total', 'valeurs' => ['libelle' => "Flux des activités d'investissement", 'montant' => $finv]],
            ['type' => 'sous_total', 'valeurs' => ['libelle' => 'Flux des activités de financement', 'montant' => $ffin]],
            ['type' => 'ligne', 'valeurs' => ['libelle' => 'Trésorerie au début', 'montant' => $debut]],
            ['type' => 'total', 'valeurs' => ['libelle' => 'Trésorerie à la fin', 'montant' => $fin]],
        ];

        return response()->json([
            'status' => 'success',
            'data'   => [
                'rapport'     => 'flux-tresorerie',
                'titre'       => 'Tableau des flux de trésorerie',
                'entreprise'  => $ctx['entreprise'],
                'sous_titre'  => $ctx['exercice']->intitule ?? 'Exercice courant',
                'unite'       => 'FCFA',
                'indicateurs' => [
                    ['libelle' => 'Trésorerie au début', 'valeur' => $debut, 'format' => 'montant'],
                    ['libelle' => "Capacité d'autofinancement", 'valeur' => $caf, 'format' => 'montant'],
                    ['libelle' => 'Flux des activités opérationnelles', 'valeur' => $fop, 'format' => 'montant'],
                    ['libelle' => 'Trésorerie à la fin', 'valeur' => $fin, 'format' => 'montant'],
                ],
                'graphiques'  => [],
                'tableaux'    => [[
                    'titre'    => 'Synthèse des flux de trésorerie',
                    'colonnes' => [
                        ['cle' => 'libelle', 'libelle' => 'Rubrique', 'format' => 'texte'],
                        ['cle' => 'montant', 'libelle' => 'Montant', 'format' => 'montant'],
                    ],
                    'lignes'   => $lignes,
                ]],
            ],
        ]);
    }
}
