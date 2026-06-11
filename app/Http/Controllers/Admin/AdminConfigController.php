<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlanComptable;
use App\Models\PlanTiers;
use App\Models\CodeJournal;
use App\Models\Company;
use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;
use App\Models\ImportStaging;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class AdminConfigController extends Controller
{
    /**
     * Dashboard de configuration pour l'Administrateur
     */
    public function hub()
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $mainCompany = Company::where('id', $companyId)->first();

        // Statistiques du modèle de référence
        $stats = [
            'accounts' => PlanComptable::where('company_id', $companyId)->count(),
            'tiers' => PlanTiers::where('company_id', $companyId)->count(),
            'journals' => CodeJournal::where('company_id', $companyId)->count(),
            'imported' => EcritureComptable::where('company_id', $companyId)
                ->where('statut', 'imported')
                ->count(),
            'treasury_categories' => \App\Models\TreasuryCategory::where('company_id', $companyId)->count(),
        ];

        // Récupération de l'exercice actif
        $exerciceActif = \App\Models\ExerciceComptable::where('company_id', $companyId)
            ->where('is_active', 1)
            ->first();

        $user = auth()->user();
        return view('admin.config.hub', compact('mainCompany', 'stats', 'exerciceActif', 'user'));
    }

    /**
     * Mise à jour des paramètres globaux de l'entreprise
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'accounting_system' => 'required|string|in:SYSCOHADA,PCG,CUSTOM',
            'account_digits' => 'required|integer|min:4|max:12',
            'journal_code_digits' => 'nullable|integer|min:4|max:10',
            'journal_code_type' => 'nullable|string|in:alphanumeric',
            'tier_digits' => 'nullable|integer|min:4|max:15',
            'tier_id_type' => 'nullable|string|in:numeric,alphanumeric',
        ]);

        $user = Auth::user();
        $company = Company::findOrFail(session('current_company_id', $user->company_id));

        $company->update([
            'accounting_system' => $request->accounting_system,
            'account_digits' => $request->account_digits,
            'journal_code_digits' => $request->journal_code_digits ?? $company->journal_code_digits,
            'journal_code_type' => $request->journal_code_type ?? $company->journal_code_type,
            'tier_digits' => $request->tier_digits ?? $company->tier_digits,
            'tier_id_type' => $request->tier_id_type ?? $company->tier_id_type,
        ]);

        return redirect()->back()->with('success', 'Paramètres mis à jour avec succès.');
    }

    /**
     * Gestion du Modèle de Plan Comptable
     */
    public function planComptable()
    {
        $user = Auth::user();
        $mainCompany = Company::findOrFail(session('current_company_id', $user->company_id));
        $plansComptables = PlanComptable::where('company_id', session('current_company_id', $user->company_id))
            ->orderBy('numero_de_compte')
            ->get();

        $hasAccounts = $plansComptables->count() > 0;

        return view('admin.config.plan_comptable', compact('plansComptables', 'hasAccounts', 'mainCompany'));
    }

    /**
     * Gestion du Modèle de Plan Tiers
     */
    public function planTiers()
    {
        $user = Auth::user();
        $planTiers = PlanTiers::where('company_id', session('current_company_id', $user->company_id))
            ->orderBy('numero_de_tiers')
            ->get();

        $plansComptables = PlanComptable::where('company_id', session('current_company_id', $user->company_id))
            ->orderBy('numero_de_compte')
            ->get();

        $mainCompany = Company::findOrFail(session('current_company_id', $user->company_id));

        return view('admin.config.plan_tiers', compact('planTiers', 'plansComptables', 'mainCompany'));
    }

    /**
     * Gestion de la Structure des Journaux
     */
    public function journals()
    {
        $user = Auth::user();
        $mainCompany = Company::findOrFail(session('current_company_id', $user->company_id));
        $journals = CodeJournal::with('account')
            ->where('company_id', session('current_company_id', $user->company_id))
            ->orderBy('code_journal')
            ->get();

        // On récupère aussi le plan comptable pour les comptes de trésorerie
        $plansComptables = PlanComptable::where('company_id', session('current_company_id', $user->company_id))
            ->orderBy('numero_de_compte')
            ->get();

        return view('admin.config.journals', compact('journals', 'mainCompany', 'plansComptables'));
    }

    /**
     * Importation externe, etc.)
     */
    public function externalImport()
    {
        return view('admin.config.external_import');
    }

    /**
     * Charger les écritures importées (Passage de 'imported' à 'approved')
     */
    public function chargeImports()
    {
        $user = Auth::user();

        $importedCount = EcritureComptable::where('company_id', session('current_company_id', $user->company_id))
            ->where('statut', 'imported')
            ->count();

        if ($importedCount === 0) {
            return redirect()->route('admin.config.external_import')
                ->with('info', 'Aucune écriture importée en attente. Veuillez d\'abord importer un fichier.');
        }

        // Passage au statut approved pour toutes les écritures importées de la compagnie
        EcritureComptable::where('company_id', session('current_company_id', $user->company_id))
            ->where('statut', 'imported')
            ->update(['statut' => 'approved']);

        return redirect()->route('accounting_entry_list')
            ->with('success', "$importedCount écritures ont été chargées avec succès dans la liste principale.");
    }

    /**
     * Charger le Plan Comptable SYSCOHADA (4 chiffres)
     */
    /**
     * Charger le Plan Comptable SYSCOHADA (2-4 chiffres)
     */
    public function loadSyscohada4()
    {
        return $this->loadSyscohadaPlan('syscohada_24');
    }

    /**
     * Charger le Plan Comptable SAGE (6 chiffres)
     */
    public function loadSyscohada6()
    {
        return $this->loadSyscohadaPlan('sage_6');
    }

    /**
     * Charger le Plan Comptable DC-KNOWING (8 chiffres)
     */
    public function loadSyscohada8()
    {
        return $this->loadSyscohadaPlan('dc_knowing_8');
    }

    /**
     * Charger le Plan Comptable SYSCOHADA Standard (Interne)
     */
    private function loadSyscohadaPlan($mode = 'syscohada_24')
    {
        try {
            $user = Auth::user();
            $digits = 8; // Default for other logic if needed

            // CHARGEMENT DU PLAN COMPTABLE SYSCOHADA COMPLET
            // Source: config/syscohada_complet.php (870 comptes extraits de syscohada.txt)
            $syscohadaComplet = require config_path('syscohada_complet.php');

            if (empty($syscohadaComplet)) {
                return redirect()->back()->with('error', 'Erreur: Le fichier SYSCOHADA complet est introuvable ou vide.');
            }

            // Utiliser directement le référentiel complet
            $templates = $syscohadaComplet;

            // Tri par ordre croissant des clés (numéros de comptes)
            ksort($templates);

            DB::beginTransaction();
            $count = 0;
            foreach ($templates as $prefix => $intitule) {
                $numero = $prefix;

                if ($mode === 'sage_6') {
                    $numero = str_pad($prefix, 6, '0', STR_PAD_RIGHT);
                } elseif ($mode === 'dc_knowing_8') {
                    // Règle DC-KNOWING : [Préfixe SYSCOHADA] + '1' + '0' (padding à 8)
                    $numero = str_pad($prefix . '1', 8, '0', STR_PAD_RIGHT);
                } else {
                    // Mode SYSCOHADA (2-4) : on garde le numéro tel quel (longueur 2, 3 ou 4)
                    $numero = $prefix;
                }

                // Détection intelligente de la classe et du type
                $classe = (int)substr($prefix, 0, 1);
                $type = in_array($classe, [1, 2, 3, 4, 5, 9]) ? 'Bilan' : 'Compte de résultat';

                $exists = PlanComptable::where('company_id', session('current_company_id', $user->company_id))
                    ->where('numero_de_compte', $numero)
                    ->exists();

                if (!$exists) {
                    PlanComptable::create([
                        'numero_de_compte' => $numero,
                        'intitule' => mb_strtoupper($intitule),
                        'type_de_compte' => $type,
                        'classe' => $classe,
                        'user_id' => $user->id,
                        'company_id' => session('current_company_id', $user->company_id),
                        'adding_strategy' => 'auto'
                    ]);
                    $count++;
                }
            }
            DB::commit();

            $modeNames = [
                'syscohada_24' => 'SYSCOHADA (2-4)',
                'sage_6' => 'COMPTES SAGE (6)',
                'dc_knowing_8' => 'COMPTES DC-KNOWING (8)'
            ];
            $modeName = $modeNames[$mode] ?? 'SYSCOHADA';

            return redirect()->back()->with('success', "$count comptes chargés avec succès dans le format $modeName.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erreur lors du chargement : ' . $e->getMessage());
        }
    }

    /**
     * Génération de Plan Comptable Personnalisé
     */
    public function generateCustomPlan(Request $request)
    {
        $request->validate([
            'digits' => 'required|integer|min:4|max:12',
            'system' => 'required|string|in:SYSCOHADA,PCG,CUSTOM',
            'sequential' => 'required|string|in:oui,non',
            'seq_prefix' => 'nullable|string',
            'seq_start' => 'nullable|integer|min:0|max:9',
            'seq_end' => 'nullable|integer|min:0|max:9',
        ]);

        try {
            $user = Auth::user();
            $digits = $request->digits;

            // On utilise les libellés SYSCOHADA par défaut (même pour CUSTOM comme demandé)
            $templates = [
                '1' => 'Comptes de capitaux propres',
                '2' => 'Comptes d\'immobilisations',
                '3' => 'Comptes de stocks',
                '4' => 'Comptes de tiers',
                '5' => 'Comptes de trésorerie',
                '6' => 'Comptes de charges des activités ordinaires',
                '7' => 'Comptes de produits des activités ordinaires',
                '8' => 'Comptes des autres charges et produits',
                '9' => 'Comptes des engagements hors bilan'
            ];

            DB::beginTransaction();
            $count = 0;

            foreach ($templates as $classPrefix => $intitule) {
                // Génération de base pour la classe
                $numeroBase = str_pad($classPrefix, $digits, '0', STR_PAD_RIGHT);

                if (!PlanComptable::where('company_id', session('current_company_id', $user->company_id))->where('numero_de_compte', $numeroBase)->exists()) {
                    PlanComptable::create([
                        'numero_de_compte' => $numeroBase,
                        'intitule' => "CLASSE $classPrefix - $intitule",
                        'user_id' => $user->id,
                        'company_id' => session('current_company_id', $user->company_id),
                        'adding_strategy' => 'auto'
                    ]);
                    $count++;
                }

                // Si séquentiel, on génère une série
                if ($request->sequential === 'oui') {
                    $prefix = $classPrefix . ($request->seq_prefix ?? '');
                    for ($i = $request->seq_start; $i <= $request->seq_end; $i++) {
                        $numero = str_pad($prefix . $i, $digits, '0', STR_PAD_RIGHT);

                        if (strlen($numero) <= $digits && !PlanComptable::where('company_id', session('current_company_id', $user->company_id))->where('numero_de_compte', $numero)->exists()) {
                            PlanComptable::create([
                                'numero_de_compte' => $numero,
                                'intitule' => "Compte $numero (Généré)",
                                'user_id' => $user->id,
                                'company_id' => session('current_company_id', $user->company_id),
                                'adding_strategy' => 'auto'
                            ]);
                            $count++;
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->back()->with('success', "$count comptes générés avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erreur de génération : ' . $e->getMessage());
        }
    }

    /**
     * Réinitialiser / Annuler le Plan Comptable
     */
    public function resetPlanComptable()
    {
        try {
            $user = Auth::user();
            $companyId = session('current_company_id', $user->company_id);

            // Récupérer les identifiants uniques des comptes liés à des écritures
            $linkedIds = \App\Models\EcritureComptable::where('company_id', $companyId)
                ->whereNotNull('plan_comptable_id')
                ->pluck('plan_comptable_id')
                ->unique()
                ->toArray();

            // Supprimer uniquement les comptes non liés
            $deletedCount = PlanComptable::where('company_id', $companyId)
                ->whereNotIn('id', $linkedIds)
                ->delete();

            $remainingCount = PlanComptable::where('company_id', $companyId)->count();

            if ($remainingCount > 0) {
                return redirect()->back()->with('warning', "$deletedCount comptes ont été supprimés. Cependant, $remainingCount comptes n'ont pas pu être supprimés car ils sont liés à des écritures.");
            }

            return redirect()->back()->with('success', 'Plan comptable réinitialisé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la réinitialisation : ' . $e->getMessage());
        }
    }

    /**
     * Réinitialiser / Annuler le Plan Tiers
     */
    public function resetPlanTiers()
    {
        try {
            $user = Auth::user();
            $companyId = session('current_company_id', $user->company_id);

            // Récupérer les identifiants uniques des tiers liés à des écritures
            $linkedIds = \App\Models\EcritureComptable::where('company_id', $companyId)
                ->whereNotNull('plan_tiers_id')
                ->pluck('plan_tiers_id')
                ->unique()
                ->toArray();

            // Supprimer uniquement les tiers non liés
            $deletedCount = PlanTiers::where('company_id', $companyId)
                ->whereNotIn('id', $linkedIds)
                ->delete();

            $remainingCount = PlanTiers::where('company_id', $companyId)->count();

            if ($remainingCount > 0) {
                return redirect()->back()->with('warning', "$deletedCount tiers ont été supprimés. Cependant, $remainingCount tiers n'ont pas pu être supprimés car ils sont liés à des écritures.");
            }

            return redirect()->back()->with('success', 'Plan tiers réinitialisé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la réinitialisation : ' . $e->getMessage());
        }
    }

    /**
     * Réinitialiser / Annuler les Journaux
     */
    public function resetJournals()
    {
        try {
            $user = Auth::user();
            $companyId = session('current_company_id', $user->company_id);

            // Récupérer les identifiants uniques des journaux liés à des écritures
            $linkedIds = \App\Models\EcritureComptable::where('company_id', $companyId)
                ->whereNotNull('code_journal_id')
                ->pluck('code_journal_id')
                ->unique()
                ->toArray();

            // Supprimer uniquement les journaux non liés
            $deletedCount = CodeJournal::where('company_id', $companyId)
                ->whereNotIn('id', $linkedIds)
                ->delete();

            $remainingCount = CodeJournal::where('company_id', $companyId)->count();

            if ($remainingCount > 0) {
                return redirect()->back()->with('warning', "$deletedCount journaux ont été supprimés. Cependant, $remainingCount journaux n'ont pas pu être supprimés car ils sont liés à des écritures.");
            }

            return redirect()->back()->with('success', 'Modèle de journaux réinitialisé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la réinitialisation : ' . $e->getMessage());
        }
    }

    /**
     * Enregistrer un nouveau compte Master
     */
    public function storeAccount(Request $request)
    {
        $request->validate([
            'numero_de_compte' => 'required|string',
            'intitule' => 'required|string|max:255',
        ]);

        $user = Auth::user();
        $company = Company::findOrFail(session('current_company_id', $user->company_id));
        $digits = $company->account_digits ?? 8;

        // Validation stricte de la longueur
        if (strlen($request->numero_de_compte) != $digits) {
            return redirect()->back()->with('error', "Le numéro de compte doit comporter exactement $digits chiffres.");
        }

        $numero = $request->numero_de_compte;

        $exists = PlanComptable::where('company_id', session('current_company_id', $user->company_id))
            ->where('numero_de_compte', $numero)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', "Le compte $numero existe déjà dans votre modèle.");
        }

        PlanComptable::create([
            'numero_de_compte' => $numero,
            'intitule' => $request->intitule,
            'user_id' => $user->id,
            'company_id' => session('current_company_id', $user->company_id),
            'adding_strategy' => 'manuel'
        ]);

        return redirect()->back()->with('success', 'Compte ajouté au modèle avec succès.');
    }

    /**
     * Importation des comptes via Excel/CSV
     */
    public function importAccounts(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv,txt',
        ]);

        try {
            Excel::import(new \App\Imports\MasterPlanImport, $request->file('file'));
            return redirect()->back()->with('success', 'Importation terminée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de l\'importation : ' . $e->getMessage());
        }
    }

    /**
     * Importation des tiers via Excel/CSV
     */
    public function importTiers(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv,txt',
        ]);

        try {
            $file = $request->file('file');
            Excel::import(new \App\Imports\MasterTiersImport($file->getRealPath()), $file);
            return redirect()->back()->with('success', 'Importation des tiers terminée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de l\'importation des tiers : ' . $e->getMessage());
        }
    }

    /**
     * Importation des journaux via Excel/CSV
     */
    public function importJournals(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv,txt',
        ]);

        try {
            $file = $request->file('file');
            Excel::import(new \App\Imports\MasterJournalImport($file->getRealPath()), $file);
            return redirect()->back()->with('success', 'Importation des journaux terminée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de l\'importation des journaux : ' . $e->getMessage());
        }
    }

    /**
     * Charger les Journaux Standards
     */
    public function loadStandardJournals()
    {
        try {
            $user = Auth::user();

            $templates = [
                ['code' => 'ACH', 'intitule' => 'JOURNAL DES ACHATS', 'type' => 'Achats'],
                ['code' => 'VEN', 'intitule' => 'JOURNAL DES VENTES', 'type' => 'Ventes'],
                ['code' => 'BQ',  'intitule' => 'JOURNAL DE BANQUE', 'type' => 'Banque'],
                ['code' => 'CSH', 'intitule' => 'JOURNAL DE CAISSE', 'type' => 'Caisse'],
                ['code' => 'OD',  'intitule' => 'OPERATIONS DIVERSES', 'type' => 'Opérations Diverses'],
            ];

            DB::beginTransaction();
            $count = 0;
            foreach ($templates as $tpl) {
                $exists = CodeJournal::where('company_id', session('current_company_id', $user->company_id))
                    ->where('code_journal', $tpl['code'])
                    ->exists();

                if (!$exists) {
                    CodeJournal::create([
                        'code_journal' => $tpl['code'],
                        'intitule' => $tpl['intitule'],
                        'type' => $tpl['type'],
                        'user_id' => $user->id,
                        'company_id' => session('current_company_id', $user->company_id),
                    ]);
                    $count++;
                }
            }
            DB::commit();

            return redirect()->back()->with('success', "$count journaux standards chargés avec succès.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erreur lors du chargement : ' . $e->getMessage());
        }
    }
    /**
     * Obtenir le prochain numéro de tiers disponible
     */
    public function getNextTierNumber(Request $request)
    {
        try {
            $user = Auth::user();
            $company = Company::findOrFail(session('current_company_id', $user->company_id));
            $digits = (int)($company->tier_digits ?? 8);

            $prefix = $request->input('prefix'); // Préfixe de catégorie (ex: 40, 41)
            $planComptableId = $request->input('plan_comptable_id');
            $intitule = $request->input('intitule', '');

            // Si pas de préfixe fourni, on essaie de l'extraire du compte général
            if (!$prefix && $planComptableId) {
                $planAccount = PlanComptable::find($planComptableId);
                if ($planAccount) {
                    $prefix = substr($planAccount->numero_de_compte, 0, 2);
                }
            }

            if (!$prefix) {
                return response()->json(['success' => false, 'message' => 'Préfixe ou compte collectif requis.'], 400);
            }

        // CORRECTION ANTIGRAVITY : Logique Hybride (Numérique ou Alphanumérique)
        $tierIdType = $company->tier_id_type ?? 'numeric';
        $base = $prefix;

        if ($tierIdType === 'alphanumeric' && !empty($intitule)) {
            // Logique: Racine (Prefix) + Nom (3 car.) + Séquence
            // Nettoyage du nom : majuscules, seulement lettres, max 3
            $cleanName = strtoupper(preg_replace('/[^a-zA-Z]/', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $intitule)));
            $namePart = substr($cleanName, 0, 3);

            // Si le nom est trop court, on complete avec des X ou on prend ce qu'il y a
            if (strlen($namePart) < 1) $namePart = 'XXX';

            $base = $prefix . $namePart;
        }

        // --- Logique commune de recherche de séquence ---
        // On cherche le max séquentiel pour la base déterminée (soit Prefix simple, soit Prefix+Nom)
        // La base peut être "411" (numeric) ou "411DUP" (alphanumeric)

        $availableSpace = max(0, $digits - strlen($base));

        // Si plus de place pour la séquence, on retourne la base tronquée (cas extrême)
        if ($availableSpace === 0) {
            return response()->json([
                'success' => true,
                'next_id' => substr($base, 0, $digits)
            ]);
        }

        $existingTiers = PlanTiers::where('company_id', session('current_company_id', $user->company_id))
            ->where('numero_de_tiers', 'like', $base . '%')
            ->get();

        $maxSeq = 0;
        foreach ($existingTiers as $tier) {
            // On extrait la fin de la chaîne après la base
            $suffix = substr($tier->numero_de_tiers, strlen($base));
            // On vérifie si le suffixe est numérique (la séquence)
            if (is_numeric($suffix)) {
                $maxSeq = max($maxSeq, (int)$suffix);
            }
        }

        $seq = $maxSeq + 1;

        // Construction finale avec padding strict sur l'espace disponible
        $nextId = $base . str_pad($seq, $availableSpace, '0', STR_PAD_LEFT);

        if (strlen($nextId) > $digits) {
            $nextId = substr($nextId, 0, $digits);
        }

            return response()->json([
                'success' => true,
                'next_id' => $nextId,
                'debug' => [
                    'digits' => $digits,
                    'prefix' => $prefix
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Erreur getNextTierNumber: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    /**
     * Enregistrer un nouveau Tiers Master
     */
    public function storeTier(Request $request)
    {
        $request->validate([
            'numero_de_tiers' => 'required|string|max:20',
            'intitule' => 'required|string|max:255',
            'type_de_tiers' => 'required|string', // On laisse plus souple pour les nouvelles catégories
            'compte_general' => 'nullable|exists:plan_comptables,id',
        ]);

        $user = Auth::user();
        $company = Company::findOrFail(session('current_company_id', $user->company_id));
        $digits = $company->tier_digits ?? 8;

        $num = strtoupper($request->numero_de_tiers);

        // Sécurité de longueur
        if (strlen($num) != $digits) {
             $prefix = substr($num, 0, 2); // On essaie de garder le préfixe si possible
             $resp = $this->getNextTierNumber(new Request([
                'prefix' => $prefix,
                'intitule' => $request->intitule
             ]));
             $data = $resp->getData();
             if ($data->success) {
                 $num = $data->next_id;
             }
        }

        $exists = PlanTiers::where('company_id', session('current_company_id', $user->company_id))
            ->where('numero_de_tiers', $num)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', "Le numéro de tiers {$num} existe déjà.");
        }

        PlanTiers::create([
            'numero_de_tiers' => $num,
            'intitule' => strtoupper($request->intitule),
            'type_de_tiers' => $request->type_de_tiers,
            'compte_general' => $request->compte_general,
            'user_id' => $user->id,
            'company_id' => session('current_company_id', $user->company_id),
        ]);

        return redirect()->route('admin.config.plan_tiers')->with('success', 'Tiers ajouté au modèle avec succès.');
    }

    /**
     * Enregistrer un nouveau Journal Master
     */
    public function storeJournal(Request $request)
    {
        $user = Auth::user();
        $company = Company::findOrFail(session('current_company_id', $user->company_id));
            $digits = $company->journal_code_digits ?? 4;
        $codeType = $company->journal_code_type ?? 'alphanumeric';

        $request->validate([
            'code_journal' => 'required|string',
            'intitule' => 'required|string|max:255',
            'type' => 'required|string',
            'compte_de_tresorerie' => 'nullable|string',
            'poste_tresorerie' => 'nullable|string',
            'traitement_analytique' => 'nullable|string|in:oui,non',
            'rapprochement_sur' => 'nullable|string|in:Manuel,Automatique',
        ]);

        $code = strtoupper($request->code_journal);

        // Validation de la longueur
        if (strlen($code) != $digits) {
            return redirect()->back()->with('error', "Le code journal doit comporter exactement $digits caractères.");
        }

        // Validation du type de code (Tous les codes journaux sont toujours de type alphanumérique)
        if (!preg_match('/^[A-Z0-9]+$/', $code)) {
            return redirect()->back()->with('error', "Le code journal doit être uniquement alphanumérique (lettres et chiffres en majuscules).");
        }

        $exists = CodeJournal::where('company_id', session('current_company_id', $user->company_id))
            ->where('code_journal', $code)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', "Le code journal {$code} existe déjà.");
        }

        $compteId = null;
        if ($request->compte_de_tresorerie) {
            $compteId = PlanComptable::where('company_id', session('current_company_id', $user->company_id))
                ->where('numero_de_compte', $request->compte_de_tresorerie)
                ->value('id');
        }

        $posteTresorerie = $request->poste_tresorerie_autre ?: $request->poste_tresorerie;

        CodeJournal::create([
            'code_journal' => $code,
            'intitule' => strtoupper($request->intitule),
            'type' => $request->type,
            'compte_de_tresorerie' => $compteId,
            'compte_de_contrepartie' => $request->compte_de_contrepartie,
            'poste_tresorerie' => $posteTresorerie,
            'traitement_analytique' => ($request->traitement_analytique === 'oui'),
            'rapprochement_sur' => $request->rapprochement_sur,
            'user_id' => $user->id,
            'company_id' => session('current_company_id', $user->company_id),
        ]);

        return redirect()->back()->with('success', 'Journal ajouté au modèle avec succès.');
    }

    /**
     * Synchronisation du Plan Comptable (Charger depuis l'Admin)
     */
    public function syncPlanComptable(Request $request)
    {
        try {
            $user = Auth::user();
            $currentCompanyId = session('current_company_id');

            if (!$currentCompanyId) {
                return response()->json(['success' => false, 'message' => 'Aucune entité active sélectionnée.'], 400);
            }

            $currentCompany = Company::find($currentCompanyId);
            $parentCompanyId = $currentCompany->parent_company_id ?? $user->company_id;

            if ($currentCompanyId == $parentCompanyId) {
                return response()->json(['success' => false, 'message' => 'Vous êtes déjà sur l\'entité parente.'], 400);
            }

            // Récupérer le plan de l'admin
            $adminPlans = PlanComptable::where('company_id', $parentCompanyId)->get();

            DB::beginTransaction();
            $count = 0;
            foreach ($adminPlans as $plan) {
                $exists = PlanComptable::where('company_id', $currentCompanyId)
                    ->where('numero_de_compte', $plan->numero_de_compte)
                    ->exists();

                if (!$exists) {
                    $newPlan = $plan->replicate();
                    $newPlan->company_id = $currentCompanyId;
                    $newPlan->user_id = $user->id;
                    $newPlan->save();
                    $count++;
                }
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "$count comptes ont été synchronisés avec succès."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Synchronisation des Tiers
     */
    public function syncPlanTiers(Request $request)
    {
        try {
            $user = Auth::user();
            $currentCompanyId = session('current_company_id');

            $currentCompany = Company::find($currentCompanyId);
            $parentCompanyId = $currentCompany->parent_company_id ?? $user->company_id;

            $adminTiers = PlanTiers::where('company_id', $parentCompanyId)->get();

            DB::beginTransaction();
            $count = 0;
            foreach ($adminTiers as $tier) {
                $exists = PlanTiers::where('company_id', $currentCompanyId)
                    ->where('numero_de_tiers', $tier->numero_de_tiers)
                    ->exists();

                if (!$exists) {
                    $newTier = $tier->replicate();
                    $newTier->company_id = $currentCompanyId;
                    $newTier->save();
                    $count++;
                }
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "$count fiches tiers synchronisées."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Synchronisation des Journaux
     */
    public function syncJournals(Request $request)
    {
        try {
            $user = Auth::user();
            $currentCompanyId = session('current_company_id');

            $currentCompany = Company::find($currentCompanyId);
            $parentCompanyId = $currentCompany->parent_company_id ?? $user->company_id;

            $adminJournals = CodeJournal::where('company_id', $parentCompanyId)->get();

            DB::beginTransaction();
            $count = 0;
            foreach ($adminJournals as $journal) {
                $exists = CodeJournal::where('company_id', $currentCompanyId)
                    ->where('code_journal', $journal->code_journal)
                    ->exists();

                if (!$exists) {
                    $newJournal = $journal->replicate();
                    $newJournal->company_id = $currentCompanyId;
                    $newJournal->save();
                    $count++;
                }
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "$count journaux synchronisés."
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Suppression d'un compte du modèle
     */
    public function deleteAccount($id)
    {
        try {
            $user = Auth::user();
            $companyId = session('current_company_id', $user->company_id);
            $account = PlanComptable::where('company_id', $companyId)->findOrFail($id);
            
            $utilise = \App\Models\EcritureComptable::where('company_id', $companyId)
                ->where('plan_comptable_id', $id)
                ->exists();
                
            if ($utilise) {
                return redirect()->back()->with('error', 'Impossible de supprimer ce compte car il est lié à une écriture.');
            }
            
            $account->delete();
            return redirect()->back()->with('success', 'Compte supprimé du modèle avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Suppression d'un tiers du modèle
     */
    public function deleteTier($id)
    {
        try {
            $user = Auth::user();
            $companyId = session('current_company_id', $user->company_id);
            $tier = PlanTiers::where('company_id', $companyId)->findOrFail($id);
            
            $utilise = \App\Models\EcritureComptable::where('company_id', $companyId)
                ->where('plan_tiers_id', $id)
                ->exists();
                
            if ($utilise) {
                return redirect()->back()->with('error', 'Impossible de supprimer ce plan tiers car il est lié à une écriture.');
            }
            
            $tier->delete();
            return redirect()->back()->with('success', 'Tiers supprimé du modèle avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Suppression d'un journal du modèle
     */
    public function deleteJournal($id)
    {
        try {
            $user = Auth::user();
            $companyId = session('current_company_id', $user->company_id);
            $journal = CodeJournal::where('company_id', $companyId)->findOrFail($id);
            
            $utilise = \App\Models\EcritureComptable::where('company_id', $companyId)
                ->where('code_journal_id', $id)
                ->exists();
                
            if ($utilise) {
                return redirect()->back()->with('error', 'Impossible de supprimer ce code journal car il est lié à une écriture.');
            }
            
            $journal->delete();
            return redirect()->back()->with('success', 'Journal supprimé du modèle avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Mise à jour d'un compte du modèle
     */
    public function updateAccount(Request $request, $id)
    {
        $request->validate([
            'numero_de_compte' => 'required|string',
            'intitule' => 'required|string',
        ]);

        try {
            $user = Auth::user();
            $company = Company::findOrFail(session('current_company_id', $user->company_id));
            $digits = $company->account_digits ?? 8;

            if (strlen($request->numero_de_compte) != $digits) {
                return redirect()->back()->with('error', "Le numéro de compte doit comporter exactement $digits chiffres.");
            }

            $account = PlanComptable::where('company_id', session('current_company_id', $user->company_id))->findOrFail($id);
            $account->update([
                'numero_de_compte' => $request->numero_de_compte,
                'intitule' => mb_strtoupper($request->intitule),
            ]);
            return redirect()->back()->with('success', 'Compte mis à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    /**
     * Mise à jour d'un tiers du modèle
     */
    public function updateTier(Request $request, $id)
    {
        $request->validate([
            'numero_de_tiers' => 'required|string',
            'intitule' => 'required|string',
            'type_de_tiers' => 'required|string',
        ]);

        try {
            $user = Auth::user();
            $tier = PlanTiers::where('company_id', session('current_company_id', $user->company_id))->findOrFail($id);
            $tier->update([
                'numero_de_tiers' => $request->numero_de_tiers,
                'intitule' => mb_strtoupper($request->intitule),
                'type_de_tiers' => $request->type_de_tiers,
                'compte_general' => $request->compte_general,
            ]);
            return redirect()->back()->with('success', 'Tiers mis à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    /**
     * Mise à jour d'un journal du modèle
     */
    public function updateJournal(Request $request, $id)
    {
        $user = Auth::user();
        $company = Company::findOrFail(session('current_company_id', $user->company_id));
        $digits = $company->journal_code_digits ?? 4;
        $codeType = $company->journal_code_type ?? 'alphanumeric';

        $request->validate([
            'code_journal' => 'required|string',
            'intitule' => 'required|string',
            'type' => 'required|string',
            'compte_de_tresorerie' => 'nullable|string',
            'compte_de_contrepartie' => 'nullable|string',
            'poste_tresorerie' => 'nullable|string',
            'traitement_analytique' => 'nullable|string|in:oui,non',
            'rapprochement_sur' => 'nullable|string|in:Manuel,Automatique',
        ]);

        $code = strtoupper($request->code_journal);

        // Validation de la longueur
        if (strlen($code) != $digits) {
            return redirect()->back()->with('error', "Le code journal doit comporter exactement $digits caractères.");
        }

        // Validation du type de code (Tous les codes journaux sont toujours de type alphanumérique)
        if (!preg_match('/^[A-Z0-9]+$/', $code)) {
            return redirect()->back()->with('error', "Le code journal doit être uniquement alphanumérique (lettres et chiffres en majuscules).");
        }

        // Unicité
        $exists = CodeJournal::where('company_id', session('current_company_id', $user->company_id))
            ->where('code_journal', $code)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', "Le code journal {$code} existe déjà.");
        }

        try {
            $journal = CodeJournal::where('company_id', session('current_company_id', $user->company_id))->findOrFail($id);

            $posteTresorerie = $request->poste_tresorerie_autre ?: $request->poste_tresorerie;

            $compteId = null;
            if ($request->compte_de_tresorerie) {
                $compteId = PlanComptable::where('company_id', session('current_company_id', $user->company_id))
                    ->where('numero_de_compte', $request->compte_de_tresorerie)
                    ->value('id');
            }

            $journal->update([
                'code_journal' => $code,
                'intitule' => trim($request->intitule),
                'type' => $request->type,
                'compte_de_tresorerie' => $compteId,
                'compte_de_contrepartie' => $request->compte_de_contrepartie,
                'poste_tresorerie' => $posteTresorerie,
                'traitement_analytique' => ($request->traitement_analytique === 'oui'),
                'rapprochement_sur' => $request->rapprochement_sur,
            ]);
            return redirect()->back()->with('success', 'Journal mis à jour avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

    /**
     * Tunnel d'Importation - Hub
     */
    public function importHub()
    {
        $user = Auth::user();
        $imports = ImportStaging::where('company_id', session('current_company_id', $user->company_id))
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.config.import_hub', compact('imports', 'user'));
    }

    /**
     * Tunnel d'Importation - Upload & Analyse Initiale
     */
    public function importUpload(Request $request)
    {
        file_put_contents(base_path('debug_import.txt'), "[" . date('H:i:s') . "] START UPLOAD\n", FILE_APPEND);

        try {
            $user = Auth::user();
            $file = $request->file('file');

            if (!$file) {
                file_put_contents(base_path('debug_import.txt'), "[" . date('H:i:s') . "] NO FILE RECEIVED\n", FILE_APPEND);
                return redirect()->back()->with('error', "Aucun fichier n'a été reçu.");
            }

            $extension = strtolower($file->getClientOriginalExtension());
            $type = $request->type;

            $targetCompanyId = session('current_company_id', $user->company_id);

            file_put_contents(base_path('debug_import.txt'), "[" . date('H:i:s') . "] FILE: " . $file->getClientOriginalName() . " EXT: $extension TYPE: $type TARGET_CO: $targetCompanyId\n", FILE_APPEND);

            ImportStaging::where('user_id', $user->id)
                ->where('company_id', $targetCompanyId)
                ->where('type', $request->type)
                ->whereIn('status', ['upload', 'staging'])
                ->delete();

            $sheetData = [];

            file_put_contents(base_path('debug_import.txt'), "[" . date('H:i:s') . "] PRE-PARSING\n", FILE_APPEND);

            if ($extension === 'xml') {
                // --- PARSEUR XML INTELLIGENT & RECURSIF ---
                $xmlString = @file_get_contents($file->getRealPath());
                $xml = @simplexml_load_string($xmlString);
                if (!$xml) return redirect()->back()->with('error', "Format XML illisible.");

                // Fonction de détection du bloc de données répétitif (Recherche de la collection la plus dense)
                $findDataNode = function($node) use (&$findDataNode) {
                    $counts = [];
                    foreach($node->children() as $child) {
                        $n = $child->getName();
                        $counts[$n] = ($counts[$n] ?? 0) + 1;
                    }
                    foreach($counts as $n => $c) if ($c >= 2) return $node->{$n};
                    foreach($node->children() as $child) { $res = $findDataNode($child); if ($res) return $res; }
                    return null;
                };

                $records = $findDataNode($xml);
                if ($records) {
                    $headers = [];
                    $rows = [];
                    foreach($records as $rec) {
                        $rowMap = [];
                        foreach($rec->children() as $f) {
                            $h = strtoupper($f->getName());
                            if (!in_array($h, $headers)) $headers[] = $h;
                            $rowMap[$h] = (string)$f;
                        }
                        // Support des attributs si les enfants sont vides
                        if (empty($rowMap) && count($rec->attributes()) > 0) {
                            foreach($rec->attributes() as $aName => $aVal) {
                                $h = strtoupper($aName);
                                if (!in_array($h, $headers)) $headers[] = $h;
                                $rowMap[$h] = (string)$aVal;
                            }
                        }
                        if (!empty($rowMap)) $rows[] = $rowMap;
                    }
                    $sheetData[] = $headers;
                    foreach($rows as $rm) {
                        $line = [];
                        foreach($headers as $h) $line[] = $rm[$h] ?? '';
                        $sheetData[] = $line;
                    }
                }
            } elseif ($extension === 'html' || $extension === 'htm') {
                // --- EXTRACTEUR HTML (TABLES) ---
                $html = file_get_contents($file->getRealPath());
                $dom = new \DOMDocument();
                @$dom->loadHTML($html);
                $tables = $dom->getElementsByTagName('table');

                if ($tables->length > 0) {
                    // On prend la table qui a le plus de lignes (souvent la table de données)
                    $bestTable = null;
                    $maxRows = 0;
                    foreach ($tables as $t) {
                        if ($t->getElementsByTagName('tr')->length > $maxRows) {
                            $maxRows = $t->getElementsByTagName('tr')->length;
                            $bestTable = $t;
                        }
                    }

                    if ($bestTable) {
                        foreach ($bestTable->getElementsByTagName('tr') as $tr) {
                            $rowData = [];
                            foreach ($tr->getElementsByTagName('td') as $td) $rowData[] = trim($td->nodeValue);
                            if (empty($rowData)) foreach ($tr->getElementsByTagName('th') as $th) $rowData[] = trim($th->nodeValue);
                            if (!empty(array_filter($rowData))) $sheetData[] = $rowData;
                        }
                    }
                }
            } elseif ($extension === 'txt' || $extension === 'csv') {
                // --- ANALYSEUR TXT ULTRA-ROBUSTE (ENCODING + FIXED/DELIM) ---
                $rawContent = file_get_contents($file->getRealPath());

                // 1. DÉTECTION & CONVERSION ENCODING (Export souvent ANSI/ISO)
                $enc = mb_detect_encoding($rawContent, ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII']);
                if ($enc && $enc !== 'UTF-8') {
                    $rawContent = mb_convert_encoding($rawContent, 'UTF-8', $enc);
                }

                $content = explode("\n", str_replace("\r", "", $rawContent));
                $content = array_filter($content, fn($l) => trim($l) !== '');
                $sample = array_slice($content, 0, 50); // Scan plus large

                // 2. ESSAI DÉLIMITÉ
                $delimiters = [';', ',', "\t", '|'];
                $bestDelim = null;
                $maxCols = 0;
                foreach($delimiters as $d) {
                    $colsCount = array_map(fn($l) => count(explode($d, $l)), $sample);
                    $avgCols = count($colsCount) > 0 ? array_sum($colsCount) / count($colsCount) : 0;
                    if ($avgCols > $maxCols && $avgCols > 1.5) {
                        $maxCols = $avgCols;
                        $bestDelim = $d;
                    }
                }

                if ($bestDelim) {
                    foreach($content as $l) $sheetData[] = str_getcsv($l, $bestDelim);
                } else {
                    // 3. ESSAI LARGEUR FIXE (MAINFRAME / EXPORT ANCIEN)
                    // On cherche les "caniveaux" (colonnes de vides verticaux)
                    $maxL = max(array_map('mb_strlen', $sample) ?: [0]);
                    $density = array_fill(0, $maxL, 0);
                    foreach($sample as $l) {
                        for($i=0; $i<mb_strlen($l); $i++) if (mb_substr($l, $i, 1) !== ' ') $density[$i]++;
                    }

                    $offsets = [0];
                    $inSilence = false;
                    for($i=1; $i<$maxL - 1; $i++) {
                        // Un silence est une zone où la densité est nulle sur TOUT l'échantillon
                        $isVoid = ($density[$i] == 0);
                        if ($isVoid && !$inSilence) {
                            $inSilence = true;
                        } elseif (!$isVoid && $inSilence) {
                            // Fin du silence = nouveau début de colonne
                            $offsets[] = $i;
                            $inSilence = false;
                        }
                    }

                    foreach($content as $l) {
                        $row = [];
                        for($i=0; $i<count($offsets); $i++) {
                            $start = $offsets[$i];
                            $len = isset($offsets[$i+1]) ? ($offsets[$i+1] - $start) : null;
                            $val = $len ? mb_substr($l, $start, $len) : mb_substr($l, $start);
                            $row[] = trim($val);
                        }
                        // Garder seulement les lignes qui ont au moins une donnée utile
                        if (count(array_filter($row)) > 0) $sheetData[] = $row;
                    }
                }
            } else {
                // --- EXCEL STANDARD ---
                // On force l'extraction formatée (formatData=true) pour obtenir les dates telles qu'affichées dans Excel
                // (ex: 21/10/2025 au lieu de 45672)
                $inputFileType = IOFactory::identify($file->getRealPath());
                $reader = IOFactory::createReader($inputFileType);
                $spreadsheet = $reader->load($file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                // On désactive formatData (3ème param) pour obtenir les valeurs brutes (N° série Excel)
                $sheetData = $sheet->toArray(null, true, false, false);
            }

            if (empty($sheetData) || count($sheetData) < 1) {
                file_put_contents(base_path('debug_import.txt'), "[" . date('H:i:s') . "] NO DATA EXTRACTED\n", FILE_APPEND);
                return redirect()->back()->with('error', "Aucune donnée n'a pu être extraite.");
            }

            file_put_contents(base_path('debug_import.txt'), "[" . date('H:i:s') . "] DATA EXTRACTED: " . count($sheetData) . " lines\n", FILE_APPEND);

            Log::info("IMPORT UPLOAD: Data extracted", [
                'rows_count' => count($sheetData),
                'first_row_cells' => count($sheetData[0] ?? [])
            ]);

            // AUTO-ADAPTATION : On autorise TOUT, c'est le Mapping qui fera le tri.
            // On ne bloque plus par validation rigide.
            $import = ImportStaging::create([
                'company_id' => session('current_company_id', $user->company_id),
                'user_id' => $user->id,
                'exercice_id' => ($request->type === 'courant') ? $request->exercice : null,
                'source' => $request->source,
                'type' => $request->type,
                'file_name' => $file->getClientOriginalName(),
                'raw_data' => $sheetData,
                'status' => 'upload'
            ]);

            Log::info("IMPORT UPLOAD: Redirecting to mapping", ['import_id' => $import->id]);
            file_put_contents(base_path('debug_import.txt'), "[" . date('H:i:s') . "] REDIRECTING TO MAPPING: ID=" . $import->id . "\n", FILE_APPEND);
            return redirect()->route('admin.import.mapping', $import->id);

        } catch (\Exception $e) {
            file_put_contents(base_path('debug_import.txt'), "[" . date('H:i:s') . "] CRITICAL ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
            return redirect()->back()->with('error', 'Erreur critique : ' . $e->getMessage());
        }
    }

    /**
     * Tunnel d'Importation - Interface de Mapping
     */
    public function importMapping($id)
    {
        Log::info("IMPORT MAPPING START", ['import_id' => $id]);
        $import = ImportStaging::findOrFail($id);
        // Définition exhaustive des champs et de leurs synonymes pour le mappage intelligent
        $fieldsDictionary = [
            'initial' => [
                'numero_de_compte' => [
                    'label' => 'Numéro de compte',
                    'required' => true,
                    'icon' => 'fa-hashtag',
                    'auto_generate' => true,
                    'match' => ['compte', 'num', 'code', 'acc', 'no', 'numero', 'ncompte', 'noaccount', 'comptenumber', 'comptegeneral', 'cptgen', 'cpt', 'compte_numero', 'compte_num'],
                    'pattern' => '/^\d{3,12}$/',
                    'info' => 'Ce champ sera automatiquement standardisé (longueur fixe) lors de l\'importation.'
                ],
                'intitule' => [
                    'label' => 'Intitulé du compte',
                    'required' => true,
                    'icon' => 'fa-font',
                    'match' => ['intitule', 'libelle', 'nom', 'desc', 'label', 'designation', 'intitule_compte', 'label_compte', 'accountname', 'libelledelasaisie', 'description', 'nomcompte', 'compte_intitule'],
                    'pattern' => '/^[a-zA-Z]/i'
                ]
            ],
            'tiers' => [
                'numero_de_tiers' => [
                    'label' => 'Numéro de Tiers',
                    'required' => false,
                    'icon' => 'fa-hashtag',
                    'auto_generate' => true,
                    'match' => ['identifiant', 'id', 'numtiers', 'notiers', 'numerotiers', 'codetiers', 'idtiers', 'comptetiers', 'auxiliaire', 'tiers', 'code_tiers', 'num_tiers', 'tier_num', 'tiers_numero', 'ctiers_numero'],
                    'pattern' => '/^[A-Z0-9]{2,15}$/i',
                    'info' => 'Ce champ sera automatiquement généré. Tout numéro importé sera remplacé par un nouveau numéro conforme à votre configuration.'
                ],
                'intitule' => [
                    'label' => 'Nom / Intitulé',
                    'required' => true,
                    'icon' => 'fa-font',
                    'match' => ['intituledutiers', 'nomdutiers', 'raisondutiers', 'societe', 'intitule', 'nomtiers', 'client', 'fournisseur', 'nom', 'raison', 'raisonsociale', 'denomination', 'libelle', 'name', 'tiers_nom', 'nom_client', 'nom_fournisseur', 'tiers_intitule', 'ctiers_intitule'],
                    'pattern' => '/^[a-zA-Z]/i'
                ],
                'type_de_tiers' => [
                    'label' => 'Catégorie / Type',
                    'required' => false,
                    'icon' => 'fa-tags',
                    'auto_generate' => true,
                    'match' => ['type', 'categorie', 'cat', 'nature', 'naturetiers', 'typetiers', 'qualite', 'classification', 'cat_tiers', 'role', 'statut'],
                    'pattern' => '/^(cl|fo|cli|fou|0|1|2|3)$/i',
                    'info' => 'La catégorie sera automatiquement déterminée à partir du préfixe du numéro de tiers importé.'
                ],
                'compte_general' => [
                    'label' => 'Compte général',
                    'required' => true,
                    'icon' => 'fa-link',
                    'auto_generate' => true,
                    'match' => ['rattachement', 'collectif', 'comptegeneral', 'cptgeneral', 'nocptcollectif', 'compte_collectif', 'compte_general', 'general', 'compte', 'comptecollectif', 'cptcollectif', 'numerocompte', 'numcompte', 'comptetiers', 'collectif_num', 'cpt_collectif', 'compte_numero'],
                    'pattern' => '/^(401|411|4)\d*/'
                ]
            ],
            'journals' => [
                'code_journal' => [
                    'label' => 'Code Journal',
                    'required' => true,
                    'icon' => 'fa-tag',
                    'auto_generate' => true,
                    'match' => ['code', 'jnl', 'abr', 'codejournal', 'journalcode', 'jrn', 'abreviation', 'code_jnl', 'journal_code'], // Removed 'journal' to avoid confusion with Label
                    'pattern' => '/^[A-Z0-9]{2,5}$/i',
                    'info' => 'Le code journal importé sera standardisé.'
                ],
                'intitule' => [
                    'label' => 'Intitulé du Journal',
                    'required' => true,
                    'icon' => 'fa-font',
                    'match' => ['intitule', 'libelle', 'nom', 'label', 'designation', 'intitulejnl', 'nomdujournal', 'jnlname', 'libelle_journal', 'nom_journal', 'journal_intitule', 'journal', 'description'], // 'Journal' is often the name in headers
                    'pattern' => '/^[a-zA-Z]/i'
                ],
                'type' => [
                    'label' => 'Type (Achats, Ventes, etc.)',
                    'required' => true,
                    'icon' => 'fa-layer-group',
                    'auto_generate' => true,
                    'match' => ['type', 'nature', 'cat', 'flux', 'categorie', 'journaltype', 'type_journal', 'classement'],
                    'pattern' => '/^(ach|ven|bq|ca|od|0|1|2|3)$/i'
                ],
                'compte_de_tresorerie' => [
                    'label' => 'Compte Trésorerie',
                    'required' => false,
                    'icon' => 'fa-university',
                    'match' => ['compte_tresorerie', 'treso', 'banque', 'caisse', 'comptetresorerie', 'ncomptetresorerie', 'ctres', 'cpte_treso', 'nocountetresorerie', 'compte_banque', 'num_compte_treso', 'compte_caisse', 'compte_numero'],
                    'pattern' => '/^5\d+/'
                ],
                'traitement_analytique' => ['label' => 'Traitement Analytique', 'required' => false, 'icon' => 'fa-chart-pie', 'match' => ['analytique', 'traitementanalytique', 'sectionana', 'analyt', 'ana', 'analytique_actif', 'trtana', 'gestion_analytique']],
                'rapprochement_sur' => ['label' => 'État Rapprochement', 'required' => false, 'icon' => 'fa-check-double', 'match' => ['rapprochement', 'etatrap', 'rap', 'rappro', 'rapprochement_bancaire', 'rapprochement_manuel', 'bnqrap', 'gestion_rapprochement']]
            ],
            'courant' => [
                'jour' => [
                    'label' => 'Date / Jour',
                    'required' => true,
                    'icon' => 'fa-calendar',
                    'match' => ['date', 'jour', 'period', 'quantieme', 'mvt', 'dateecrit', 'ecrituredate', 'date_operation', 'date_ecriture', 'quantieme', 'ecriture_date', 'dateecr'],
                    'pattern' => '/^\d{1,4}/'
                ],
                'n_saisie' => [
                    'label' => 'N° Saisie / Écriture',
                    'required' => false,
                    'icon' => 'fa-hashtag',
                    'match' => ['n_saisie', 'nsaisie', 'no_saisie', 'num_saisie', 'numero_saisie', 'numero_de_saisie', 'ecriture', 'ecriture_num', 'num_ecriture', 'piece', 'num_piece', 'numero_piece'],
                    'pattern' => '/^.{1,50}$/'
                ],
                'journal' => [
                    'label' => 'Code Journal',
                    'required' => true,
                    'icon' => 'fa-book',
                    'match' => ['journal', 'code', 'jnl', 'jrn', 'codejnl', 'idjnl', 'code_journal', 'jnl_code', 'identifiant_journal', 'ecriture_journal'],
                    'pattern' => '/^[A-Z0-9]{2,5}$/i'
                ],
                'reference' => ['label' => 'Référence Pièce', 'required' => false, 'icon' => 'fa-receipt', 'match' => ['ref', 'piece', 'facture', 'num', 'doc', 'numpiece', 'refpiece', 'ndoc', 'n_piece', 'reference_piece', 'n_facture', 'ecriture_piece', 'ecriture_reference']],
                'compte' => [
                    'label' => 'Numéro Compte',
                    'required' => true,
                    'icon' => 'fa-hashtag',
                    'match' => ['compte', 'num', 'general', 'acc', 'ncompte', 'numaccount', 'compte_general', 'cpt_gen', 'numero_compte', 'ecriture_compte', 'compte_numero'],
                    'pattern' => '/^(?!401|411)\d{3,12}$/' // Ignore les comptes commençant par 401/411 pour favoriser le mapping Tiers
                ],
                'libelle' => ['label' => 'Libellé Opération', 'required' => true, 'icon' => 'fa-font', 'match' => ['libelle', 'desc', 'nom', 'intitule', 'comm', 'objet', 'libelleecrit', 'description', 'intitule_operation', 'commentaire', 'designation_operation', 'ecriture_libelle', 'libelle_operation']],
                'debit' => [
                    'label' => 'Montant Débit',
                    'required' => true,
                    'icon' => 'fa-plus-circle',
                    'match' => ['debit', 'montant', 'flux_d', 'entree', 'amount_d', 'midebit', 'debits', 'montant_debit', 'somme_debit', 'ecriture_debit', 'montant_d', 'ecrc_montant', 'debit_montant'],
                    'is_numeric' => true
                ],
                'credit' => [
                    'label' => 'Montant Crédit',
                    'required' => true,
                    'icon' => 'fa-minus-circle',
                    'match' => ['credit', 'montant_c', 'flux_c', 'sortie', 'amount_c', 'micredit', 'credits', 'montant_credit', 'somme_credit', 'ecriture_credit', 'montant_c', 'ecrc_montant', 'credit_montant'],
                    'is_numeric' => true
                ],
                'tiers' => [
                    'label' => 'Compte Tiers',
                    'required' => false,
                    'icon' => 'fa-user',
                    'match' => ['tier', 'auxiliaire', 'client', 'fourn', 'compte_t', 'aux', 'tiersaux', 'compte_auxiliaire', 'auxiliaire_num', 'tiers_id', 'ecriture_tiers', 'tiers_numero'],
                    'pattern' => '/^(401|411)\d*/' // Priorité absolue aux comptes commençant par 401/411
                ],
                // CAS 1 : Filtrage par Type
                'type_ecriture' => [
                    'label' => 'Type Écriture (A/G)',
                    'required' => false,
                    'icon' => 'fa-filter',
                    'match' => ['type', 'type_ecriture', 'lettrage', 'statut', 'analytique', 'g_a', 'type_journal'],
                    'pattern' => '/^[AGag]$/'
                ]
            ]
        ];

        $typeKey = $import->type == 'initial' ? 'initial' : ($import->type == 'journals' ? 'journals' : ($import->type == 'tiers' ? 'tiers' : 'courant'));
        $fields = $fieldsDictionary[$typeKey];

        // LOGIQUE D'INTELLIGENCE AVANCÉE : Détection En-tête vs Données vs Titre (Junk)
        $headers = [];
        $headerIndex = -1;
        $dataStartIndex = 0;
        $maxHeaderMatches = 0;
        $scanLimit = min(count($import->raw_data), 20);

        for ($i = 0; $i < $scanLimit; $i++) {
            $row = $import->raw_data[$i];
            if (empty(array_filter($row))) continue;

            $headerMatches = 0;
            $dataMatches = 0;

            foreach ($row as $cell) {
                $cell = trim((string)$cell);
                if ($cell === '') continue;

                // 1. Détection de patterns de DONNÉES (Dates, Comptes, Montants)
                if (preg_match('/^\d{1,2}[\/\-\.]\d{1,2}[\/\-\.]\d{2,4}$/', $cell) ||
                    preg_match('/^\d{4}[\/\-\.]\d{1,2}[\/\-\.]\d{1,2}$/', $cell)) {
                    $dataMatches += 2;
                }
                // Compte ou Numéro : numérique 4+ chiffres
                if (is_numeric($cell) && strlen($cell) >= 4 && strlen($cell) <= 12) {
                    $dataMatches++;
                }

                // 2. Détection de MOTS-CLÉS d'en-tête
                try {
                    $cleanCell = strtolower(preg_replace('/[^a-z]/', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $cell)));
                } catch (\Exception $e) { $cleanCell = ''; }

                if (empty($cleanCell) || strlen($cleanCell) < 3) continue;

                foreach ($fields as $field) {
                    foreach ($field['match'] as $m) {
                        try {
                            $cleanM = strtolower(preg_replace('/[^a-z]/', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $m)));
                        } catch (\Exception $e) { $cleanM = ''; }

                        if ($cleanCell === $cleanM || (strlen($cleanCell) > 3 && str_contains($cleanCell, $cleanM))) {
                            $headerMatches++;
                            break 2;
                        }
                    }
                }
            }

            // DÉCISION : Si la ligne contient des patterns de données, c'est le début des informations utiles.
            if ($dataMatches >= 2) {
                // Si on a déjà trouvé un en-tête avant, on s'arrête là (données après en-tête).
                // Si on n'en a pas trouvé, c'est un fichier SANS en-tête.
                $dataStartIndex = ($headerIndex === -1) ? $i : ($headerIndex + 1);
                break;
            }

            // Si c'est un en-tête probable (3+ matches) sans patterns de données
            if ($headerMatches >= 3 && $dataMatches == 0) {
                if ($headerMatches > $maxHeaderMatches) {
                    $maxHeaderMatches = $headerMatches;
                    $headerIndex = $i;
                }
            }
        }

        // Si on a trouvé un en-tête, on le définit
        if ($headerIndex !== -1) {
            $headers = $import->raw_data[$headerIndex];
        } else {
            // Sinon, on génère des en-têtes fictifs "Colonne X"
            $maxCols = 0;
            foreach(array_slice($import->raw_data, 0, 10) as $r) $maxCols = max($maxCols, count($r));
            for($i=0; $i<$maxCols; $i++) $headers[] = "Colonne " . chr(65 + ($i % 26)) . ($i > 25 ? floor($i/26) : '');
        }

        // MOTEUR DE CORRESPONDANCE "INFAILLIBLE" : Multicritère
        // 1. Dictionnaire étendu, 2. Regex Pattern, 3. Analyse statistique
        $dataSamples = array_slice($import->raw_data, $dataStartIndex, 20);

        foreach ($fields as $fieldKey => &$field) {
            $field['suggested_col'] = null;
            $bestScore = 0;

            foreach ($headers as $colIdx => $header) {
                $score = 0;

                // --- CRITÈRE 1 : CORRESPONDANCE SÉMANTIQUE (NOM DE COLONNE) ---
                if ($header) {
                    $cleanHeader = strtolower(preg_replace('/[^a-z0-9]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $header)));
                    foreach ($field['match'] as $m) {
                        $cleanM = strtolower(preg_replace('/[^a-z0-9]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $m)));
                        if ($cleanHeader === $cleanM) { $score += 120; break; }
                        elseif (str_contains($cleanHeader, $cleanM)) { $score += 60; }
                    }
                }

                // --- CRITÈRE 2 : ANALYSE DU CONTENU (DATA-FIRST) ---
                $formatMatches = 0;
                $totalPopulated = 0;
                $sampleValues = [];

                foreach ($dataSamples as $sampleRow) {
                    $cell = trim((string)($sampleRow[$colIdx] ?? ''));
                    if ($cell === '') continue;
                    $totalPopulated++;
                    $sampleValues[] = $cell;

                    // Test Regex Pattern
                    if (isset($field['pattern']) && preg_match($field['pattern'], $cell)) {
                        $formatMatches += 3; // Poids encore plus fort pour le pattern de contenu
                    }
                    // Test Numérique Spécifique
                    if (isset($field['is_numeric']) && is_numeric(str_replace([' ', ',', '€'], ['', '.', ''], $cell))) {
                        $formatMatches++;
                    }
                }

                if ($totalPopulated > 0) {
                    $dataScore = ($formatMatches / ($totalPopulated * 2)) * 100;
                    $score += $dataScore;
                }

                // --- CRITÈRE 3 : RÈGLES MÉTIERS (NATURE DES DONNÉES) ---
                if (count($sampleValues) > 0) {
                    // Si la colonne ressemble à un compte général (ex: 601)
                    if (($fieldKey == 'compte' || $fieldKey == 'numero_de_compte') && collect($sampleValues)->every(fn($v) => is_numeric($v) && strlen($v) >= 3)) $score += 50;

                    // Si la colonne ressemble à un tiers (commence par 401 ou 411)
                    if ($fieldKey == 'compte_general' && collect($sampleValues)->every(fn($v) => str_starts_with($v, '401') || str_starts_with($v, '411'))) $score += 80;

                    // Si la colonne contient des montants avec décimales
                    if (($fieldKey == 'debit' || $fieldKey == 'credit') && collect($sampleValues)->contains(fn($v) => str_contains($v, ',') || str_contains($v, '.'))) $score += 40;

                    // --- RÈGLES ANTI-CONFUSION TIERS / MONTANTS ---
                    if ($fieldKey === 'tiers') {
                        // Pénalité forte si la colonne est entièrement numérique avec des valeurs > 100
                        // (= montants financiers, pas des codes tiers)
                        $allNumericLarge = collect($sampleValues)->filter(fn($v) => $v !== '')->every(
                            fn($v) => is_numeric(str_replace([' ', ','], ['', '.'], $v)) && (float)str_replace([' ', ','], ['', '.'], $v) > 100
                        );
                        if ($allNumericLarge) {
                            $score -= 200; // Disqualifie cette colonne pour le champ tiers
                        }

                        // Bonus si la colonne contient des codes alphanumériques courts (2-15 car, avec au moins une lettre)
                        // Ex: "CGE", "401ANGHOURA", "CLIDURAND"
                        $hasAlphanumericCodes = collect($sampleValues)->filter(fn($v) => $v !== '')->contains(
                            fn($v) => preg_match('/^[A-Z0-9]{2,15}$/i', $v) && preg_match('/[A-Za-z]/', $v)
                        );
                        if ($hasAlphanumericCodes) {
                            $score += 60;
                        }
                    }

                    // --- RÈGLES ANTI-CONFUSION DÉBIT/CRÉDIT / CODES ---
                    if ($fieldKey === 'debit' || $fieldKey === 'credit') {
                        // Pénalité si la colonne contient principalement des codes non-numériques
                        $hasNonNumeric = collect($sampleValues)->filter(fn($v) => $v !== '')->contains(
                            fn($v) => !is_numeric(str_replace([' ', ',', '.'], ['', '.', ''], $v))
                        );
                        if ($hasNonNumeric) {
                            $score -= 150;
                        }
                    }
                } // end if (count($sampleValues) > 0)

                if ($score > $bestScore && $score > 35) {
                    $bestScore = $score;
                    $field['suggested_col'] = $colIdx;
                }
            } // end foreach headers
        } // end foreach fields


        // Enregistrer l'index de l'en-tête SANS écraser le mapping existant
        $currentMapping = $import->mapping ?? [];
        $currentMapping['_header_index'] = $headerIndex;
        $import->update([
            'mapping' => $currentMapping
        ]);

        $typeLabels = [
            'initial' => 'Plan Comptable Master',
            'tiers' => 'Modèle de Tiers',
            'journals' => 'Modèle des Journaux',
            'courant' => 'Écritures Comptables'
        ];
        $importTitle = $typeLabels[$import->type] ?? 'Importation';

        $viewName = 'admin.config.import_mapper';
        if ($import->type == 'initial') $viewName = 'admin.config.import_mapper_plan';
        elseif ($import->type == 'tiers') $viewName = 'admin.config.import_mapper_tiers';
        elseif ($import->type == 'journals') $viewName = 'admin.config.import_mapper_journals';

        $user = Auth::user();

        // --- CALCUL DES DOUBLONS POTENTIELS (POUR AFFICHAGE MAPPAGE) ---
        $potentialDuplicates = 0;
        $targetCompanyId = $import->company_id ?: session('current_company_id', $user->company_id);

        if ($import->type == 'tiers') {
            $tiersField = $fields['numero_de_tiers'] ?? null;
            if ($tiersField && $tiersField['suggested_col'] !== null) {
                $colIdx = $tiersField['suggested_col'];
                $existingTiers = PlanTiers::where('company_id', $targetCompanyId)->pluck('numero_de_tiers')->toArray();
                $existingOriginals = PlanTiers::where('company_id', $targetCompanyId)->whereNotNull('numero_original')->pluck('numero_original')->toArray();
                $allExisting = array_flip(array_unique(array_merge(
                    array_map('strtoupper', $existingTiers),
                    array_map('strtoupper', $existingOriginals)
                )));

                foreach ($import->raw_data as $i => $row) {
                    if ($i <= $headerIndex) continue;
                    $val = strtoupper(trim($row[$colIdx] ?? ''));
                    if (!empty($val) && isset($allExisting[$val])) $potentialDuplicates++;
                }
            }
        } elseif ($import->type == 'journals') {
            // Fix: clé correcte 'code_journal' (et non 'code') dans le dictionnaire
            $codeField = $fields['code_journal'] ?? null;
            if ($codeField && $codeField['suggested_col'] !== null) {
                $colIdx = $codeField['suggested_col'];
                $existingCodes = CodeJournal::where('company_id', $targetCompanyId)->pluck('code_journal')->toArray();
                $existingOriginals = CodeJournal::where('company_id', $targetCompanyId)->whereNotNull('numero_original')->pluck('numero_original')->toArray();

                $allExisting = array_flip(array_unique(array_merge(
                    array_map('strtoupper', $existingCodes),
                    array_map('strtoupper', $existingOriginals)
                )));

                foreach ($import->raw_data as $i => $row) {
                    if ($i <= $headerIndex) continue;
                    $val = strtoupper(trim($row[$colIdx] ?? ''));
                    if (!empty($val) && isset($allExisting[$val])) $potentialDuplicates++;
                }
            }
        }

        return view($viewName, compact('import', 'headers', 'fields', 'importTitle', 'user', 'potentialDuplicates'));
    }

    /**
     * Tunnel d'Importation - Traitement du Mapping & Staging
     */
    public function processMapping(Request $request, $id)
    {
        $import = ImportStaging::findOrFail($id);
        $mapping = $request->input('mapping'); // Array: ['date' => 0, 'compte' => 2, ...]
        $fixedValues = $request->input('fixed_value', []);

        // Nettoyer le mapping des valeurs FIXED pour stocker les vraies colonnes ou les valeurs fixes
        $cleanMapping = [];
        foreach($mapping as $field => $col) {
            if ($col === 'FIXED') {
                $cleanMapping[$field] = 'FIXED:' . ($fixedValues[$field] ?? '');
            } else {
                $cleanMapping[$field] = $col;
            }
        }

        // Préserver le _header_index s'il existe
        $existingMapping = $import->mapping ?? [];
        $headerIndex = $existingMapping['_header_index'] ?? 0;
        $cleanMapping['_header_index'] = $headerIndex;

        $import->update([
            'mapping' => $cleanMapping,
            'status' => 'staging'
        ]);

        return redirect()->route('admin.import.staging', $import->id);
    }

    /**
     * Tunnel d'Importation - Revue & Correction (Staging)
     */
    public function importStaging($id, $returnData = false)
    {
        set_time_limit(0);
        ini_set('memory_limit', '2G');

        $import = ImportStaging::findOrFail($id);
        $user = Auth::user();
        $targetCompanyId = $import->company_id ?: session('current_company_id', $user->company_id);

        $targetCompany = Company::find($targetCompanyId);
        $accountDigits = $targetCompany->account_digits ?? 8;
        $tierDigits = $targetCompany->tier_digits ?? 8;
        $journalDigits = $targetCompany->journal_code_digits ?? 4;

        $mapping = $import->mapping;

        $headerIndex = $mapping['_header_index'] ?? 0;

        // Nettoyage des données : Ignorer les lignes avant les titres et les lignes totalement vides basées sur les colonnes mappées
        $rawRows = array_slice($import->raw_data, $headerIndex + 1, null, true);
        $data = array_filter($rawRows, function($row) use ($mapping) {
            $hasData = false;
            foreach ($mapping as $field => $index) {
                if ($field !== '_header_index' && $index !== null && $index !== "" && !empty(trim($row[$index] ?? ''))) {
                    $hasData = true;
                    break;
                }
            }
            return $hasData;
        });

        $ignoredEmptyLines = count($rawRows) - count($data);

        $existingAccounts = PlanComptable::where('company_id', $targetCompanyId)
            ->pluck('numero_de_compte')
            ->toArray();

        $accountDetails = PlanComptable::where('company_id', $targetCompanyId)
            ->select('id', 'numero_de_compte', 'intitule')
            ->get()
            ->keyBy('numero_de_compte');

        $journalDetails = CodeJournal::where('company_id', $targetCompanyId)
            ->select('id', 'code_journal', 'intitule', 'numero_original')
            ->get()
            ->keyBy(fn($item) => strtoupper($item->code_journal));

        $existingJournalsArr = $journalDetails->keys()->toArray();
        $journalMapping = [];
        foreach($journalDetails as $j) {
            if ($j->numero_original) {
                $journalMapping[strtoupper(trim($j->numero_original))] = trim($j->code_journal);
            }
        }

        $tierDetails = PlanTiers::where('company_id', $targetCompanyId)
            ->select('id', 'numero_de_tiers', 'intitule')
            ->get()
            ->keyBy(fn($item) => strtoupper($item->numero_de_tiers));

        $existingTiers = $tierDetails->keys()->toArray();

        // --- DICTIONNAIRES DE CORRESPONDANCE (AUTO-LOOKUP) ---
        $accountMapping = [];
        PlanComptable::where('company_id', $targetCompanyId)
            ->whereNotNull('numero_original')
            ->where('numero_original', '!=', '')
            ->select('numero_de_compte', 'numero_original')
            ->chunk(100, function($accounts) use (&$accountMapping) {
                foreach($accounts as $acc) {
                    $accountMapping[strtoupper(trim($acc->numero_original))] = trim($acc->numero_de_compte);
                }
            });

        $journalMapping = [];
        CodeJournal::where('company_id', $targetCompanyId)
            ->whereNotNull('numero_original')
            ->where('numero_original', '!=', '')
            ->select('code_journal', 'numero_original')
            ->chunk(100, function($journals) use (&$journalMapping) {
                foreach($journals as $jnl) {
                    $journalMapping[strtoupper(trim($jnl->numero_original))] = trim($jnl->code_journal);
                }
            });

        $tierMapping = [];
        PlanTiers::where('company_id', $targetCompanyId)
            ->whereNotNull('numero_original')
            ->where('numero_original', '!=', '')
            ->select('numero_de_tiers', 'numero_original')
            ->chunk(100, function($tiers) use (&$tierMapping) {
                foreach($tiers as $t) {
                    $tierMapping[strtoupper(trim($t->numero_original))] = trim($t->numero_de_tiers);
                }
            });

        // Pour la génération des tiers et journaux, on garde une trace des max par préfixe pour éviter les collisions en mémoire
        $localMaxTiers = [];
        $localMaxJournals = [];
        $localMaxAccounts = [];

        // Mappage de lot pour assurer la cohérence (Ex: CAI -> CAIS001 pour toutes les lignes identiques)
        $batchJournalMap = [];
        $batchTierMap = [];

        $maxMappingIndex = 0;
        foreach ($mapping as $mIdx) {
            if (is_numeric($mIdx)) {
                $maxMappingIndex = max($maxMappingIndex, (int)$mIdx);
            }
        }

        $exercice = ExerciceComptable::find($import->exercice_id);

        $cacheKey = 'import_staging_v4_' . $import->id . '_' . $import->updated_at->timestamp;
        $cachedData = \Illuminate\Support\Facades\Cache::get($cacheKey);

        if ($cachedData) {
            $rowsWithStatus = $cachedData['rowsWithStatus'];
            $errorCount = $cachedData['errorCount'];
            $validCount = $cachedData['validCount'];
            $duplicateCount = $cachedData['duplicateCount'];
            $missingAccounts = $cachedData['missingAccounts'];
            $missingJournals = $cachedData['missingJournals'];
            $missingTiers = $cachedData['missingTiers'];
        } else {
            $existingEcrituresDbHashes = [];
            if ($import->type === 'courant') {
                \App\Models\EcritureComptable::where('company_id', $targetCompanyId)
                    ->where('exercices_comptables_id', $import->exercice_id ?? session('current_exercice_id'))
                    ->select('date', 'code_journal_id', 'plan_comptable_id', 'plan_tiers_id', 'debit', 'credit', 'reference_piece', 'description_operation')
                    ->chunk(5000, function ($ecritures) use (&$existingEcrituresDbHashes) {
                        foreach ($ecritures as $e) {
                            $hash = md5($e->date . '|' . $e->code_journal_id . '|' . $e->plan_comptable_id . '|' . $e->plan_tiers_id . '|' . (float)$e->debit . '|' . (float)$e->credit . '|' . strtoupper($e->reference_piece) . '|' . strtoupper($e->description_operation));
                            $existingEcrituresDbHashes[$hash] = true;
                        }
                    });
            }

            $rowsWithStatus = [];
            $batchAccounts = []; // [ 'numero_standard' => 'numero_original' ]
            $errorCount = 0;
            $validCount = 0;
            $duplicateCount = 0;

            $missingAccounts = [];
            $missingJournals = [];
            $missingTiers = [];

            // --- DÉTERMINATION GLOBALE DYNAMIQUE DE LA STRATÉGIE DE GROUPAGE ---
            $groupingKeyStrategy = 'n_saisie'; // default
            
            if ($import->type === 'courant') {
                $parseAmountTemp = function($val) {
                    if (empty($val)) return 0.0;
                    $val = trim((string)$val);
                    $val = str_replace([' ', ' ', "\xC2\xA0"], '', $val);
                    $isNegative = str_starts_with($val, '-');
                    $val = ltrim($val, '-');
                    if (strpos($val, ',') !== false && strpos($val, '.') !== false) {
                        $val = (strrpos($val, ',') > strrpos($val, '.')) ? str_replace(['.', ','], ['', '.'], $val) : str_replace(',', '', $val);
                    } else {
                        $val = str_replace(',', '.', $val);
                    }
                    $val = preg_replace('/[^0-9.]/', '', $val);
                    return $isNegative ? -(float)$val : (float)$val;
                };

                $nSaisieCol = $mapping['n_saisie'] ?? null;
                $referenceCol = $mapping['reference'] ?? null;
                $jourCol = $mapping['jour'] ?? null;
                $journalCol = $mapping['journal'] ?? null;
                $debitCol = $mapping['debit'] ?? null;
                $creditCol = $mapping['credit'] ?? null;
                
                $hasNSaisie = ($nSaisieCol !== null && $nSaisieCol !== '' && $nSaisieCol !== 'AUTO');
                $hasReference = ($referenceCol !== null && $referenceCol !== '' && $referenceCol !== 'AUTO');
                
                if ($hasNSaisie && $hasReference) {
                    $nSaisieValues = [];
                    $referenceValues = [];
                    $groupsNSaisie = [];
                    $groupsReference = [];
                    
                    $lastJourTemp = null;
                    $lastJournalTemp = null;
                    $lastNSaisieTemp = null;
                    $lastReferenceTemp = null;
                    
                    foreach ($data as $rowRawTemp) {
                        $nsVal = trim((string)($rowRawTemp[$nSaisieCol] ?? ''));
                        $refVal = trim((string)($rowRawTemp[$referenceCol] ?? ''));
                        $jour = trim((string)($rowRawTemp[$jourCol] ?? ''));
                        $journal = trim((string)($rowRawTemp[$journalCol] ?? ''));

                        if (empty($jour) && $lastJourTemp !== null) $jour = $lastJourTemp;
                        if (empty($journal) && $lastJournalTemp !== null) $journal = $lastJournalTemp;
                        if (empty($nsVal) && $lastNSaisieTemp !== null) $nsVal = $lastNSaisieTemp;
                        if (empty($refVal) && $lastReferenceTemp !== null) $refVal = $lastReferenceTemp;

                        if (!empty($jour)) $lastJourTemp = $jour;
                        if (!empty($journal)) $lastJournalTemp = $journal;
                        if (!empty($nsVal)) $lastNSaisieTemp = $nsVal;
                        if (!empty($refVal)) $lastReferenceTemp = $refVal;
                        
                        $debit = 0.0;
                        if ($debitCol !== null && $debitCol !== '') {
                            $debit = $parseAmountTemp($rowRawTemp[$debitCol] ?? 0);
                        }
                        $credit = 0.0;
                        if ($creditCol !== null && $creditCol !== '') {
                            $credit = $parseAmountTemp($rowRawTemp[$creditCol] ?? 0);
                        }
                        
                        if ($nsVal !== '') {
                            $nSaisieValues[] = $nsVal;
                            $keyNS = $jour . '|' . $journal . '|' . strtoupper($nsVal);
                            if (!isset($groupsNSaisie[$keyNS])) {
                                $groupsNSaisie[$keyNS] = ['d' => 0.0, 'c' => 0.0];
                            }
                            $groupsNSaisie[$keyNS]['d'] += $debit;
                            $groupsNSaisie[$keyNS]['c'] += $credit;
                        }
                        
                        if ($refVal !== '') {
                            $referenceValues[] = $refVal;
                            $keyRef = $jour . '|' . $journal . '|' . strtoupper($refVal);
                            if (!isset($groupsReference[$keyRef])) {
                                $groupsReference[$keyRef] = ['d' => 0.0, 'c' => 0.0];
                            }
                            $groupsReference[$keyRef]['d'] += $debit;
                            $groupsReference[$keyRef]['c'] += $credit;
                        }
                    }
                    
                    $uniqueNSaisie = array_unique($nSaisieValues);
                    $uniqueReference = array_unique($referenceValues);
                    
                    $totalRowsCount = count($data);
                    if ($totalRowsCount > 0) {
                        $nsRatio = count($uniqueNSaisie) / $totalRowsCount;
                        $refRatio = count($uniqueReference) / $totalRowsCount;
                        
                        if (count($uniqueNSaisie) < 10 && count($uniqueReference) >= 10 && $nsRatio < 0.05) {
                            $groupingKeyStrategy = 'reference';
                        } elseif (count($uniqueReference) < 10 && count($uniqueNSaisie) >= 10 && $refRatio < 0.05) {
                            $groupingKeyStrategy = 'n_saisie';
                        } else {
                            $unbalancedNS = 0;
                            foreach ($groupsNSaisie as $g) {
                                if (abs($g['d'] - $g['c']) > 0.01) {
                                    $unbalancedNS++;
                                }
                            }
                            
                            $unbalancedRef = 0;
                            foreach ($groupsReference as $g) {
                                if (abs($g['d'] - $g['c']) > 0.01) {
                                    $unbalancedRef++;
                                }
                            }
                            
                            if ($unbalancedRef < $unbalancedNS) {
                                $groupingKeyStrategy = 'reference';
                            } else {
                                $groupingKeyStrategy = 'n_saisie';
                            }
                        }
                    }
                } elseif ($hasReference) {
                    $groupingKeyStrategy = 'reference';
                } else {
                    $groupingKeyStrategy = 'n_saisie';
                }
                
                Log::info("DYNAMIC GROUPING PRE-DECISION: $groupingKeyStrategy selected for import $id");
            }

        $lastJour = null;
        $lastJournal = null;
        $lastNSaisie = null;
        $lastReference = null;

        foreach($data as $index => $rowRaw) {
            // PADDING : Forcer la ligne à avoir au moins assez de colonnes pour couvrir le mapping
            $row = array_pad($rowRaw, $maxMappingIndex + 1, null);

            // INITIALISATION AVEC TOUTES LES CLÉS POSSIBLES (Évite le null / Sans nom)
            $processedRow = [
                'intitule' => null,
                'libelle' => null,
                'numero_de_compte' => null,
                'compte' => null,
                'numero_de_tiers' => null,
                'compte_general' => null,
                'type_de_tiers' => null,
                'code_journal' => null,
                'journal' => null,
                'type' => null,
                'jour' => null,
                'reference' => null,
                'debit' => 0,
                'credit' => 0,
                'tiers' => null,
                'n_saisie' => null,
                'type_ecriture' => null,
            ];

            foreach($mapping as $fKey => $cId) {
                if ($fKey === '_header_index' || $cId === null || $cId === "" || $cId === "AUTO") continue;

                $val = null;
                if (array_key_exists($fKey, $rowRaw)) {
                    // Priorité 1: Si une valeur a été surchargée manuellement via Edit Staging
                    $val = $rowRaw[$fKey];
                } elseif (is_string($cId) && str_starts_with($cId, 'FIXED:')) {
                    $val = substr($cId, 6);
                } else {
                    $idx = (int)$cId;
                    $val = $row[$idx] ?? null;
                }

                if ($val !== null) {
                    $processedRow[$fKey] = is_string($val) ? trim($val) : $val;
                }
            }

            // SYNCHRONISATION DES NOMS : 'intitule' est le standard pour Plan & Tiers, 'libelle' pour Écritures
            if (empty($processedRow['intitule']) && !empty($processedRow['libelle'])) {
                $processedRow['intitule'] = $processedRow['libelle'];
            } elseif (empty($processedRow['libelle']) && !empty($processedRow['intitule'])) {
                $processedRow['libelle'] = $processedRow['intitule'];
            }

            // On bascule sur le tableau nommé
            $row = $processedRow;

            // Carry-over logic for entries (courant) import
            if ($import->type === 'courant') {
                if (empty($row['jour']) && $lastJour !== null) {
                    $row['jour'] = $lastJour;
                }
                if (empty($row['journal']) && $lastJournal !== null) {
                    $row['journal'] = $lastJournal;
                }
                if (empty($row['n_saisie']) && $lastNSaisie !== null) {
                    $row['n_saisie'] = $lastNSaisie;
                }
                if (empty($row['reference']) && $lastReference !== null) {
                    $row['reference'] = $lastReference;
                }

                if (!empty($row['jour'])) $lastJour = $row['jour'];
                if (!empty($row['journal'])) $lastJournal = $row['journal'];
                if (!empty($row['n_saisie'])) $lastNSaisie = $row['n_saisie'];
                if (!empty($row['reference'])) $lastReference = $row['reference'];
            }

            $errors = [];

            if ($import->type == 'initial') {
                // Validation pour Plan Comptable
                $rowCompte = trim($row['numero_de_compte'] ?? '');
                // Stockage du numéro original RÉEL (avant toute standardisation ou séquence)
                $originalRawValue = $rowCompte;
                $row['numero_original'] = $originalRawValue;

                if (empty($rowCompte) || (isset($mapping['numero_de_compte']) && $mapping['numero_de_compte'] === 'AUTO')) {
                    // Si AUTO ou vide, on essaie de trouver la première colonne qui ressemble à un compte (3 à 12 chiffres)
                    foreach ($rowRaw as $val) {
                        $val = trim($val ?? '');
                        if (preg_match('/^\d{3,12}$/', $val)) {
                            $rowCompte = $val;
                            $row['numero_de_compte'] = $val;
                            $row['numero_original'] = $val; // Capturer ici aussi pour le mode AUTO
                            break;
                        }
                    }
                }

                if (empty($rowCompte)) {
                    $errors[] = "Numéro de compte manquant";
                } else {
                    $newCompte = $this->standardizeAccountNumber($rowCompte, $accountDigits);
                    if ($newCompte !== $rowCompte) {
                        $row['numero_de_compte'] = $newCompte;
                        $row['suggested_account'] = $newCompte;
                        $rowCompte = $newCompte;
                    }

                    if (strlen($rowCompte) != $accountDigits) {
                        $errors[] = "Longueur incorrecte : attendu $accountDigits chiffres (reçu " . strlen($rowCompte) . ")";
                    }
                }

                // GESTION DES DOUBLONS ET COLLISIONS (SMART NUMBERING)
                if (!empty($rowCompte) && empty($errors)) {
                    // Prioritize lookup by original account number mapping (in case it was previously renumbered)
                    $existing = null;
                    if (isset($accountMapping[strtoupper($originalRawValue)])) {
                        $mappedAcc = $accountMapping[strtoupper($originalRawValue)];
                        $existing = $accountDetails->get($mappedAcc);
                    }
                    if (!$existing) {
                        $existing = $accountDetails->get($rowCompte);
                    }

                    // Strict code-based duplicate check (DB or Batch)
                    $isDuplicateInDb = ($existing !== null);
                    $isDuplicateInBatch = false;
                    $existingLabel = $existing ? $existing->intitule : null;

                    foreach ($rowsWithStatus as $prevRow) {
                        if (($prevRow['status'] ?? '') === 'valid') {
                            $prevOrig = $prevRow['data']['numero_original'] ?? '';
                            $prevCompte = $prevRow['data']['numero_de_compte'] ?? '';
                            if ((!empty($originalRawValue) && strtoupper($prevOrig) === strtoupper($originalRawValue)) || 
                                (!empty($rowCompte) && strtoupper($prevCompte) === strtoupper($rowCompte))) {
                                $isDuplicateInBatch = true;
                                $existingLabel = $prevRow['data']['intitule'] ?? null;
                                break;
                            }
                        }
                    }

                    if ($isDuplicateInDb || $isDuplicateInBatch) {
                        $currentLabelUpper = trim(strtoupper($row['intitule'] ?? ''));
                        $existingLabelUpper = trim(strtoupper($existingLabel ?? ''));

                        if ($currentLabelUpper === $existingLabelUpper) {
                            $row['is_duplicate'] = true;
                            $row['existing_label'] = $existingLabel;
                            $row['info'] = "Doublon (Déjà présent). Cette ligne sera ignorée.";
                            unset($row['suggested_account']);
                            unset($row['info_renum']);
                            $duplicateCount++;
                        } else {
                            // The numbers match but labels differ. Auto-increment to find next available.
                            $newSeq = 1;
                            $newCompteCandidate = '';
                            
                            while ($newSeq <= 999) {
                                $seqStr = (string)$newSeq;
                                $candidate = substr($rowCompte, 0, strlen($rowCompte) - strlen($seqStr)) . $seqStr;
                                
                                // Check if candidate exists in DB and if it has the SAME label
                                $candDb = $accountDetails->get($candidate);
                                if ($candDb && trim(strtoupper($candDb->intitule)) === $currentLabelUpper) {
                                    $row['is_duplicate'] = true;
                                    $row['existing_label'] = $candDb->intitule;
                                    $row['info'] = "Doublon (Déjà présent). Cette ligne sera ignorée.";
                                    unset($row['suggested_account']);
                                    unset($row['info_renum']);
                                    $duplicateCount++;
                                    $newCompteCandidate = '';
                                    break;
                                }
                                
                                // Check if candidate exists in batch and if it has the SAME label
                                $candBatchRow = null;
                                foreach ($rowsWithStatus as $prev) {
                                    if (($prev['status'] ?? '') === 'valid') {
                                        $pCompte = $prev['data']['numero_de_compte'] ?? '';
                                        $pSug = $prev['data']['suggested_account'] ?? '';
                                        if ($pCompte === $candidate || $pSug === $candidate) {
                                            $candBatchRow = $prev;
                                            break;
                                        }
                                    }
                                }
                                if ($candBatchRow && trim(strtoupper($candBatchRow['data']['intitule'] ?? '')) === $currentLabelUpper) {
                                    $row['is_duplicate'] = true;
                                    $row['existing_label'] = $candBatchRow['data']['intitule'];
                                    $row['info'] = "Doublon (Déjà présent). Cette ligne sera ignorée.";
                                    unset($row['suggested_account']);
                                    unset($row['info_renum']);
                                    $duplicateCount++;
                                    $newCompteCandidate = '';
                                    break;
                                }

                                $candExistsDb = ($candDb !== null);
                                $candExistsBatch = ($candBatchRow !== null);
                                
                                if (!$candExistsDb && !$candExistsBatch) {
                                    $newCompteCandidate = $candidate;
                                    break;
                                }
                                $newSeq++;
                            }
                            
                            if ($newCompteCandidate !== '') {
                                $row['numero_de_compte'] = $newCompteCandidate;
                                $row['suggested_account'] = $newCompteCandidate;
                                $rowCompte = $newCompteCandidate;
                                $row['info'] = "Nouveau numéro suggéré (" . $newCompteCandidate . ") car le libellé diffère de l'existant.";
                                $row['is_duplicate'] = false;
                            } elseif (!isset($row['is_duplicate'])) {
                                $row['is_duplicate'] = true;
                                $row['existing_label'] = $existingLabel;
                                $row['info'] = "Impossible de trouver un numéro libre. Doublon.";
                                unset($row['suggested_account']);
                                unset($row['info_renum']);
                                $duplicateCount++;
                            }
                        }
                    }

                    if (!($row['is_duplicate'] ?? false)) {
                        $batchAccounts[$rowCompte] = $originalRawValue;
                    }
                }

                if (empty(trim($row['intitule'] ?? ''))) {
                    $errors[] = "L'intitulé du compte est obligatoire.";
                }
            } elseif ($import->type == 'journals') {
                // Validation pour Journaux
                $rowCode = trim($row['code_journal'] ?? '');

                // --- LOGIQUE TYPE : DETECTION + OVERRIDE ---
                if (empty($rowCode) || (isset($mapping['code_journal']) && $mapping['code_journal'] === 'AUTO')) {
                    // Fallback pour numero_original : on cherche la première colonne qui ressemble à un code journal (2-5 caractères, majuscules/chiffres)
                    foreach ($rowRaw as $val) {
                        $val = trim($val ?? '');
                        if (preg_match('/^[A-Z0-9]{2,5}$/i', $val)) {
                            $rowCode = $val;
                            break;
                        }
                    }
                }

                // On détermine les index virtuels pour stocker les surcharges manuelles
                // Ces colonnes n'existent pas dans le fichier source mais sont injectées via le modal d'édition
                $typeOverrideIndex = $maxMappingIndex + 1;
                $posteTresoOverrideIndex = $maxMappingIndex + 2;
                $compteTresoOverrideIndex = $maxMappingIndex + 3;
                $analytiqueOverrideIndex = $maxMappingIndex + 4;
                $rapprochementOverrideIndex = $maxMappingIndex + 5;
                $codeJournalOverrideIndex = $maxMappingIndex + 6; // Pour modifier le code d'origine

                // Application des surcharges si présentes
                $manualType = $rowRaw[$typeOverrideIndex] ?? null;
                $manualPoste = $rowRaw[$posteTresoOverrideIndex] ?? null;
                $manualCompte = $rowRaw[$compteTresoOverrideIndex] ?? null;
                $manualAnalytique = $rowRaw[$analytiqueOverrideIndex] ?? null;
                $manualRapprochement = $rowRaw[$rapprochementOverrideIndex] ?? null;
                $manualCodeOrig = $rowRaw[$codeJournalOverrideIndex] ?? null;

                if (!empty($manualCodeOrig)) {
                    $rowCode = trim($manualCodeOrig);
                }

                $detectedType = null;

                // --- STANDARTISATION DU CODE JOURNAL ---
                // On garde la valeur originale non modifiée pour affichage de la source "Original: WVE1"
                $rawOriginalCode = $rowCode;

                // On standardise le code SEULEMENT s'il n'est pas issu d'une saisie manuelle
                // ou si on veut forcer la cohérence à 4-5 chars par défaut.
                // MAIS l'utilisateur a dit : "seul les codes générer doivent respecter la configuration"
                if (!empty($rowCode)) {
                    if (empty($manualCodeOrig)) {
                        $rowCode = $this->standardizeJournalCode($rowCode, $journalDigits);
                    } else {
                        $rowCode = strtoupper(trim($rowCode));
                        if (strlen($rowCode) > 10) $rowCode = substr($rowCode, 0, 10);
                    }
                    $row['code_journal'] = $rowCode;
                }

                // Si une valeur manuelle existe (suite à une édition utilisateur), elle gagne TOUJOURS
                if (!empty($manualType)) {
                    $row['type'] = $manualType;
                } else {
                    // Sinon, on lance la détection automatique

                    // PRIORITÉ 1 : Présence d'un compte de trésorerie (Classe 5)
                    // Si la colonne 'compte_de_tresorerie' est mappée ou contient une valeur
                    $rowCompteTreso = trim($row['compte_de_tresorerie'] ?? '');
                    if (!empty($rowCompteTreso)) {
                        $rowCompteTreso = $this->standardizeAccountNumber($rowCompteTreso, $accountDigits);
                        $row['compte_de_tresorerie'] = $rowCompteTreso;

                        if (str_starts_with($rowCompteTreso, '5')) {
                            $detectedType = 'Trésorerie';
                            // Détection automatique du poste de trésorerie
                            $searchStrPoste = strtoupper(($manualCodeOrig ?? '') . ' ' . ($row['intitule'] ?? ''));
                            if (Str::contains($searchStrPoste, ['CAI', 'CASH', 'CAISSE'])) {
                                $row['poste_tresorerie'] = 'Caisse';
                            } elseif (Str::contains($searchStrPoste, ['BQ', 'BNQ', 'BANK', 'SG', 'ECO', 'BOA', 'UBA', 'TRES', 'TRZ', 'BANKING', 'BANQUE'])) {
                                $row['poste_tresorerie'] = 'Banque';
                            } else {
                                // Par défaut pour les mobiles money ou autres si pas de marqueur banque/caisse
                                $row['poste_tresorerie'] = 'Autre';
                            }
                        }
                    }



                    // PRIORITÉ 2 : Analyse sémantique si pas de compte ou type non encore détecté
                    if (!$detectedType) {
                        $searchStr = strtoupper($rowCode . ' ' . ($row['intitule'] ?? ''));
                        
                        // Séparation en mots pour éviter les faux positifs (ex: "AUTRES" contient "TRES")
                        $words = preg_split('/[\s\-_]+/', $searchStr);
                        
                        if (Str::contains($searchStr, ['RAN', 'REPORT', 'NOUVEAU'])) {
                            $detectedType = 'REPORT A NOUVEAU';
                        } elseif (Str::contains($searchStr, ['ACH', 'FOURN', 'FRN'])) {
                            $detectedType = 'Achats';
                        } elseif (Str::contains($searchStr, ['VEN', 'CLT', 'CLI'])) {
                            $detectedType = 'Ventes';
                        } elseif (Str::contains($searchStr, ['OD', 'DIV', 'VAR', 'OPÉRATION'])) {
                            $detectedType = 'Opérations Diverses';
                        } else {
                            $tresoKeywords = ['BQ', 'BNQ', 'BANK', 'SG', 'ECO', 'BOA', 'UBA', 'TRZ', 'BANKING', 'CAI', 'CASH', 'BANQUE', 'CAISSE', 'WAVE', 'DJAMO', 'MTN', 'MOOV', 'ORANGE', 'OM', 'MOMO', 'TRESORERIE', 'TRESOR'];
                            $isTreso = false;
                            
                            // On vérifie d'abord les mots complets pour les acronymes courts
                            foreach ($words as $word) {
                                if (in_array($word, $tresoKeywords)) {
                                    $isTreso = true;
                                    break;
                                }
                            }
                            
                            // On vérifie aussi les sous-chaînes pour les mots longs et indubitables
                            if (!$isTreso) {
                                if (Str::contains($searchStr, ['BANK', 'BANQUE', 'CAISSE', 'TRESORERIE', 'CASH'])) {
                                    $isTreso = true;
                                }
                            }
                            
                            if ($isTreso) {
                                $detectedType = 'Trésorerie';
                                if (Str::contains($searchStr, ['CAI', 'CASH', 'CAISSE'])) {
                                    $row['poste_tresorerie'] = 'Caisse';
                                } elseif (Str::contains($searchStr, ['BQ', 'BNQ', 'BANK', 'SG', 'ECO', 'BOA', 'UBA', 'BANKING', 'BANQUE'])) {
                                    $row['poste_tresorerie'] = 'Banque';
                                } else {
                                    $row['poste_tresorerie'] = 'Autre';
                                }
                            }
                        }
                    }

                    // Assignation du type détecté ou par défaut
                    if (empty($row['type']) || ($mapping['type'] ?? 'AUTO') === 'AUTO') {
                        $row['type'] = $detectedType ?? 'Standard';
                    }
                }

                // Surcharges de trésorerie (Analytique, Rapprochement, etc.)
                $isTreasuryType = in_array($row['type'], ['Trésorerie', 'Tresorerie', 'Banque', 'Caisse']);
                if ($isTreasuryType) {
                    if (!empty($manualPoste)) $row['poste_tresorerie'] = $manualPoste;
                    if (!empty($manualCompte)) $row['compte_de_tresorerie'] = $manualCompte;
                    if (!empty($manualAnalytique)) $row['traitement_analytique'] = $manualAnalytique;
                    if (!empty($manualRapprochement)) $row['rapprochement_sur'] = $manualRapprochement;
                }

                // Stockage du numéro original (si pas surchargé manuellement, c'est la détection ou le mappé)
                // On utilise la valeur brute AVANT standardisation
                $row['numero_original'] = $rawOriginalCode;

                // On injecte tous les index dans data pour que le JS puisse les utiliser
                $row['type_override_index'] = $typeOverrideIndex;
                $row['poste_override_index'] = $posteTresoOverrideIndex;
                $row['compte_override_index'] = $compteTresoOverrideIndex;
                $row['analytique_override_index'] = $analytiqueOverrideIndex;
                $row['rapprochement_override_index'] = $rapprochementOverrideIndex;
                $row['code_journal_override_index'] = $codeJournalOverrideIndex;

                // --- GÉNÉRATION SÉQUENTIELLE DU CODE JOURNAL ---
                // Si le code d'origine importé est déjà valide, on le conserve au lieu d'en générer un nouveau.
                $isValidAlready = !empty($rowCode) && strlen($rowCode) <= $journalDigits && preg_match('/^[A-Z0-9]+$/i', $rowCode);

                if (!empty($manualCodeOrig)) {
                     $row['code_journal'] = $rowCode;
                } elseif ($isValidAlready) {
                     $row['code_journal'] = $rowCode;
                } else {
                    $prefix = 'JRN';
                    $typeLower = mb_strtolower($row['type'] ?? '');
                    $origAlpha = preg_replace('/[^A-Z]/', '', strtoupper($row['numero_original'] ?? ''));

                    if (str_contains($typeLower, 'achat')) $prefix = 'ACH';
                    elseif (str_contains($typeLower, 'vente')) $prefix = 'VEN';
                    elseif (str_contains($typeLower, 'trésorerie') || str_contains($typeLower, 'banque') || str_contains($typeLower, 'caisse')) {
                        if (isset($row['poste_tresorerie'])) {
                            if ($row['poste_tresorerie'] === 'Caisse') {
                                $prefix = 'CAI';
                            } elseif ($row['poste_tresorerie'] === 'Banque') {
                                $prefix = 'BQ';
                            } else {
                                // "Autre" : On déduit du libellé (Intitulé) pour créer un code propre (ex: WAVE -> WAV)
                                $intituleNorm = preg_replace('/[^A-Z]/', '', strtoupper($row['intitule'] ?? 'TRZ'));

                                if (!empty($intituleNorm) && !Str::contains($intituleNorm, 'TRZ')) {
                                    $prefix = substr($intituleNorm, 0, 3);
                                } elseif (!empty($origAlpha)) {
                                    $prefix = substr($origAlpha, 0, $journalDigits - 1);
                                } else {
                                    $prefix = substr('TRZ', 0, max(1, $journalDigits - 2));
                                }
                            }
                        } else {
                            $prefix = 'BQ';
                        }
                    }
                    elseif (str_contains($typeLower, 'nouveau') || str_contains($typeLower, 'ran') || strtoupper($origAlpha) === 'RAN') {
                        $prefix = 'RAN';
                    }
                    elseif (str_contains($typeLower, 'opération') || str_contains($typeLower, 'diverse') || str_contains($typeLower, 'standard')) {
                        if (in_array(strtoupper($origAlpha), ['OD'])) {
                            $prefix = strtoupper($origAlpha);
                        } else {
                            // S'assurer qu'un journal reconnu comme Opération Diverse prenne "OD" en préfixe.
                            $prefix = str_contains($typeLower, 'opération') ? 'OD' : 'ST';
                        }
                    }

                    if (!isset($localMaxJournals[$prefix])) {
                        $resp = app(\App\Http\Controllers\CodeJournalController::class)->getNextSequentialCode(new Request(['prefix' => $prefix]));
                        $genData = $resp->getData();
                        if ($genData->success) {
                            $row['code_journal'] = $genData->code;
                            $localMaxJournals[$prefix] = $genData->code;
                        } else {
                            // Repli en cas d'erreur API interne
                            $numStr = "1";
                            $numLen = strlen($numStr);
                            if ($numLen >= $journalDigits) {
                                $newCode = substr($numStr, -$journalDigits);
                            } else {
                                $maxPrefixLen = $journalDigits - $numLen;
                                $actualPrefix = substr($prefix, 0, $maxPrefixLen);
                                $newCode = $actualPrefix . str_pad($numStr, $journalDigits - strlen($actualPrefix), '0', STR_PAD_LEFT);
                            }
                            $row['code_journal'] = $newCode;
                            $localMaxJournals[$prefix] = $newCode;
                        }
                    } else {
                        $lastCode = $localMaxJournals[$prefix];
                        // S'il n'y a pas de chiffres dans le dernier code, on part de zéro
                        if (!preg_match('/(\d+)$/', $lastCode, $matches)) {
                            $suffix = "0";
                        } else {
                            $suffix = $matches[1];
                        }

                        $nextNum = (int)$suffix + 1;
                        $numStr = (string)$nextNum;
                        $numLen = strlen($numStr);

                        // Logique intelligente qui réduit le préfixe si le numéro prend trop de place pour respecter la configuration
                        if ($numLen >= $journalDigits) {
                            $newCode = substr($numStr, -$journalDigits);
                        } else {
                            $maxPrefixLen = $journalDigits - $numLen;
                            $actualPrefix = substr($prefix, 0, $maxPrefixLen);
                            $newCode = $actualPrefix . str_pad($numStr, $journalDigits - strlen($actualPrefix), '0', STR_PAD_LEFT);
                        }

                        $row['code_journal'] = $newCode;
                        $localMaxJournals[$prefix] = $newCode;
                    }
                    $rowCode = $row['code_journal'];
                }

                // --- DÉDUPLICATION ET UNICITÉ DES CODES JOURNAUX (BATCH CONSISTENCY) ---
                if (!empty($rowCode)) {
                    $upperOrig = strtoupper(trim($row['numero_original'] ?? $rowCode));

                    // On vérifie d'abord si le code existe déjà en base pour CETTE société (par code OU numéro original)
                    $isDuplicateInDb = in_array(strtoupper($rowCode), $existingJournalsArr) || isset($journalMapping[$upperOrig]);
                    $isDuplicateInBatch = false;
                    foreach ($rowsWithStatus as $prevRow) {
                        if (($prevRow['status'] ?? '') === 'valid') {
                            $prevOrig = $prevRow['data']['numero_original'] ?? '';
                            $prevJournal = $prevRow['data']['code_journal'] ?? '';
                            if ((!empty($upperOrig) && strtoupper($prevOrig) === strtoupper($upperOrig)) || 
                                (!empty($rowCode) && strtoupper($prevJournal) === strtoupper($rowCode))) {
                                $isDuplicateInBatch = true;
                                break;
                            }
                        }
                    }

                    $existing = $journalDetails->get(strtoupper($rowCode)) ?? $journalDetails->first(fn($j) => strtoupper($j->numero_original) === $upperOrig);
                    $existingLabel = $existing ? $existing->intitule : null;
                    if (!$existingLabel) {
                        foreach ($rowsWithStatus as $prevRow) {
                            if (($prevRow['status'] ?? '') === 'valid') {
                                $prevOrig = $prevRow['data']['numero_original'] ?? '';
                                $prevJournal = $prevRow['data']['code_journal'] ?? '';
                                if ((!empty($upperOrig) && strtoupper($prevOrig) === strtoupper($upperOrig)) || 
                                    (!empty($rowCode) && strtoupper($prevJournal) === strtoupper($rowCode))) {
                                    $existingLabel = $prevRow['data']['intitule'] ?? null;
                                    break;
                                }
                            }
                        }
                    }

                    if ($isDuplicateInDb || $isDuplicateInBatch) {
                        $currentLabelUpper = trim(strtoupper($row['intitule'] ?? ''));
                        $existingLabelUpper = trim(strtoupper($existingLabel ?? ''));

                        if ($currentLabelUpper === $existingLabelUpper) {
                            $row['is_duplicate'] = true;
                            $row['existing_label'] = $existingLabel;
                            $row['info'] = "Doublon (Journal déjà présent). Il sera ignoré.";
                            $duplicateCount++;
                            $batchJournalMap[$upperOrig] = $rowCode;
                        } else {
                            $tempCode = $rowCode;
                            $counter = 1;

                            while (in_array(strtoupper($tempCode), $existingJournalsArr) || isset($localMaxJournals[strtoupper($tempCode)]) || strtoupper($tempCode) === strtoupper($rowCode)) {
                                $counter++;
                                $numStr = (string)$counter;
                                $numLen = strlen($numStr);
                                if (preg_match('/^([A-Z]+)/i', $rowCode, $matches)) {
                                    $prefix = strtoupper($matches[1]);
                                } else {
                                    $prefix = 'JRN';
                                }

                                if ($numLen >= $journalDigits) {
                                    $tempCode = substr($numStr, -$journalDigits);
                                } else {
                                    $maxPrefixLen = $journalDigits - $numLen;
                                    $actualPrefix = substr($prefix, 0, $maxPrefixLen);
                                    $tempCode = $actualPrefix . str_pad($numStr, $journalDigits - strlen($actualPrefix), '0', STR_PAD_LEFT);
                                }
                                if ($counter > 500) break;
                            }

                            $rowCode = $tempCode;
                            $row['code_journal'] = $rowCode;
                            $batchJournalMap[$upperOrig] = $rowCode;
                            $localMaxJournals[strtoupper($rowCode)] = $rowCode;
                            $row['info'] = "Nouveau journal suggéré (" . $rowCode . ") car le libellé diffère de l'existant.";
                            $row['is_duplicate'] = false;
                        }
                    } else {
                        // Si pas doublon en DB, on vérifie si collision avec un autre du lot (localMaxJournals)
                        $tempCode = $rowCode;
                        $counter = 1;

                        while (isset($localMaxJournals[strtoupper($tempCode)])) {
                            $counter++;
                            $numStr = (string)$counter;
                            $numLen = strlen($numStr);
                            if (preg_match('/^([A-Z]+)/i', $rowCode, $matches)) {
                                    $prefix = strtoupper($matches[1]);
                            } else {
                                    $prefix = 'JRN';
                            }

                            if ($numLen >= $journalDigits) {
                                $tempCode = substr($numStr, -$journalDigits);
                            } else {
                                $maxPrefixLen = $journalDigits - $numLen;
                                $actualPrefix = substr($prefix, 0, $maxPrefixLen);
                                $tempCode = $actualPrefix . str_pad($numStr, $journalDigits - strlen($actualPrefix), '0', STR_PAD_LEFT);
                            }
                            if ($counter > 500) break;
                        }

                        $rowCode = $tempCode;
                        $row['code_journal'] = $rowCode;
                        $batchJournalMap[$upperOrig] = $rowCode;
                        $localMaxJournals[strtoupper($rowCode)] = $rowCode;
                    }
                }


                if (empty($row['code_journal'])) {
                    $errors[] = "Erreur système : Impossible de générer un code journal.";
                }
                // Plus besoin de l'erreur de doublon car géré par la séquence

                // Validation Compte Trésorerie
                $typeNorm = mb_strtolower($row['type'] ?? '');
                if (str_contains($typeNorm, 'trésorerie') || str_contains($typeNorm, 'tresorerie') || str_contains($typeNorm, 'banque') || str_contains($typeNorm, 'caisse')) {
                    if (empty($trimTreso = trim($row['compte_de_tresorerie'] ?? ''))) {
                        $errors[] = "Compte de trésorerie manquant";
                    } elseif (!in_array($trimTreso, $existingAccounts)) {
                         // Correspondance automatique pour le compte de trésorerie
                         if (isset($accountMapping[$trimTreso])) {
                             $row['numero_original_compte'] = $trimTreso;
                             $row['compte_de_tresorerie'] = $accountMapping[$trimTreso];
                             $trimTreso = $row['compte_de_tresorerie'];
                         } else {
                             $errors[] = "Compte Inconnu : Le compte '$trimTreso' n'existe pas.";
                         }
                    }
                }
            } elseif ($import->type == 'tiers') {
                // Validation pour Tiers
                if (empty(trim($row['intitule'] ?? ''))) {
                    $errors[] = "L'intitulé du tiers est obligatoire.";
                }

                // S'assurer que les clés sont bien lues
                $importedNum = trim($row['numero_de_tiers'] ?? '');
                $rowCompte = trim($row['compte_general'] ?? '');
                $rowType = trim($row['type_de_tiers'] ?? '');

                // NETTOYAGE : Si le compte général contient du texte (ex: "Associés 1401000"),
                // on essaie d'extraire uniquement la partie numérique.
                if (!empty($rowCompte) && !is_numeric($rowCompte)) {
                    if (preg_match('/\d+/', $rowCompte, $matches)) {
                        $rowCompte = $matches[0];
                    }
                }

                if (empty($importedNum) || (isset($mapping['numero_de_tiers']) && $mapping['numero_de_tiers'] === 'AUTO')) {
                    // Si AUTO ou vide, on essaie de trouver la première colonne qui ressemble à un tiers (alphanumérique, classe 4)
                    foreach ($rowRaw as $val) {
                        $val = trim($val ?? '');
                        if (preg_match('/^[4]\d{2,19}$/', $val)) {
                            $importedNum = $val;
                            $row['numero_de_tiers'] = $val;
                            break;
                        }
                    }
                }


                // LOGIQUE DE DÉTECTION DE CATÉGORIE PAR PRÉFIXE (Stratégies Multiples)

                $generationPrefix = null;
                $prefix = null;
                $categoryMap = [
                    '40' => 'Fournisseur',
                    '41' => 'Client',
                    '42' => 'Personnel',
                    '43' => 'Organisme sociaux / CNPS',
                    '44' => 'Impôt',
                    '45' => 'Organisme international',
                    '46' => 'Associé',
                    '47' => 'Divers Tiers',
                    '48' => 'Dettes sur acquisition d\'immobilisations',
                    '49' => 'Dépréciation',
                ];

                // =========================================================================
                // LOGIQUE DE DÉTECTION DU TYPE DE TIERS (3 STRATÉGIES)
                // =========================================================================

                $generationPrefix = null;
                $prefix = null;
                $categoryMap = [
                    '40' => 'Fournisseur',
                    '41' => 'Client',
                    '42' => 'Personnel',
                    '43' => 'Organisme sociaux / CNPS',
                    '44' => 'État / Impôts',
                    '45' => 'Organisme international',
                    '46' => 'Associé / Groupe',
                    '47' => 'Divers Tiers',
                    '48' => 'Dettes sur acquisition (Immos)',
                    '49' => 'Dépréciation',
                ];

                // --- STRATÉGIE 1 : Détection Directe (Code commence par 4) ---
                if (!empty($importedNum) && str_starts_with($importedNum, '4')) {
                    $prefix = substr($importedNum, 0, 2);
                    if (isset($categoryMap[$prefix])) {
                        $generationPrefix = $prefix;
                    }
                }

                // --- STRATÉGIE 2 : Sémantique & Alias (Dernière volonté de l'utilisateur) ---
                // Priorité aux préfixes (lettres) puis aux mots-clés dans l'intitulé
                if (!$generationPrefix) {
                    $upperNum = strtoupper($importedNum);

                    // 2a. Détection par préfixe de code (Alias Sage, Quadratus, etc.)
                    if (preg_match('/^(FOU|FOUR|FRN|FRS|FR-|F-|F\d|FR\d)/', $upperNum)) {
                        $generationPrefix = '40';
                        $prefix = '40';
                    } elseif (preg_match('/^(CLI|CLT|CL-|C-|C\d|CL\d)/', $upperNum)) {
                        $generationPrefix = '41';
                        $prefix = '41';
                    } elseif (preg_match('/^(PERS|PER|SAL|P-|P\d)/', $upperNum)) {
                        $generationPrefix = '42';
                        $prefix = '42';
                    } elseif (preg_match('/^(ETAT|IMP|TAX|E-|E\d)/', $upperNum)) {
                        $generationPrefix = '44';
                        $prefix = '44';
                    } else {
                        // 2b. Recherche sémantique dans l'intitulé
                        $searchStr = strtoupper($row['intitule'] ?? '');
                        if (Str::contains($searchStr, ['FOURNISSEUR', 'FOURN', 'FRN', 'ACHAT'])) {
                            $generationPrefix = '40';
                            $prefix = '40';
                        } elseif (Str::contains($searchStr, ['CLIENT', 'CLT', 'VENTE'])) {
                            $generationPrefix = '41';
                            $prefix = '41';
                        } elseif (Str::contains($searchStr, ['PERSONNEL', 'SALAIRE', 'EMPLOYE'])) {
                            $generationPrefix = '42';
                            $prefix = '42';
                        } elseif (Str::contains($searchStr, ['ETAT', 'IMPOT', 'TVA', 'CNPS'])) {
                            $generationPrefix = Str::contains($searchStr, 'CNPS') ? '43' : '44';
                            $prefix = $generationPrefix;
                        }
                    }
                }

                // --- STRATÉGIE 3 : Secours par le Compte Collectif (Compte Général) ---
                // Uniquement si le compte commence par 4
                if (!$generationPrefix && !empty($rowCompte)) {
                    if (str_starts_with($rowCompte, '4')) {
                        $comptePrefix = substr($rowCompte, 0, 2);
                        if (isset($categoryMap[$comptePrefix])) {
                            $generationPrefix = $comptePrefix;
                            $prefix = $comptePrefix;
                        }
                    }
                }

                // --- FINALISATION ---
                if ($generationPrefix && isset($categoryMap[$generationPrefix])) {
                    $rowType = $categoryMap[$generationPrefix];
                    $row['type_de_tiers'] = $rowType;
                }

                // Stockage du numéro original
                $row['numero_original'] = $importedNum;

                // Si le compte général est vide, on peut aussi le déduire
                if (empty($rowCompte) && !empty($generationPrefix)) {
                    $rowCompte = $prefix . str_pad('0', ($accountDigits - 2), '0', STR_PAD_RIGHT);
                    $row['compte_general'] = $rowCompte;
                }

                // TENTATIVE DE DÉDUCTION DU COMPTE SI MANQUANT (Fallback)
                if (empty($rowCompte) && !empty($rowType)) {
                    $typeLower = strtolower($rowType);
                    if (in_array($typeLower, ['client', 'cli', 'clt'])) {
                        $rowCompte = '411' . str_pad('0', ($accountDigits - 3), '0', STR_PAD_RIGHT);
                        $row['compte_general'] = $rowCompte;
                    } elseif (in_array($typeLower, ['fournisseur', 'four', 'frs', 'fourn'])) {
                        $rowCompte = '401' . str_pad('0', ($accountDigits - 3), '0', STR_PAD_RIGHT);
                        $row['compte_general'] = $rowCompte;
                    }

                    // TENTATIVE DE RÉCUPÉRATION D'UN COMPTE SUGGÉRÉ SI INCONNU
                    if (!in_array($rowCompte, $existingAccounts)) {
                         for ($len = strlen($rowCompte); $len >= 2; $len--) {
                            $searchVal = substr($rowCompte, 0, $len);
                            // Recherche dans le dictionnaire pré-chargé au lieu d'une requête N+1
                            $match = $accountDetails->filter(function($item, $key) use ($searchVal) {
                                return str_starts_with($key, $searchVal);
                            })->sortBy('numero_de_compte')->first();

                            if ($match) {
                                $row['suggested_account'] = $match->numero_de_compte;
                                $row['suggested_account_label'] = $match->intitule;
                                 break;
                            }
                         }
                    }
                }

                // Validation longueur avec standardisation auto (Padding ou Troncature)
                if (!empty($rowCompte)) {
                    $newCompte = $this->standardizeAccountNumber($rowCompte, $accountDigits);
                    if ($newCompte !== $rowCompte) {
                        $row['numero_original_compte'] = $rowCompte;
                        $row['compte_general'] = $newCompte;
                        $rowCompte = $newCompte;
                    }

                    if (strlen($rowCompte) != $accountDigits) {
                        // On garde l'erreur seulement si ce n'est pas numérique (donc pas standardisable automatiquement)
                        if (!is_numeric($rowCompte)) {
                            $errors[] = "Le compte général '$rowCompte' ne respecte pas la configuration ($accountDigits chiffres).";
                        }
                    }
                }

                // DÉTECTION DE COLLISION POUR TIERS (Compte général)
                if (!empty($rowCompte) && empty($errors)) {
                    // On ne lève pas forcément d'erreur si plusieurs tiers partagent le même compte collectif (c'est normal),
                    // mais on peut lever une alerte si c'est inattendu.
                    // Pour l'instant, on se concentre sur les collisions du Plan Comptable.
                }

                // GÉNÉRATION FORCÉE POUR L'IMPORTATION
                // Tout numéro importé est gardé en référence mais remplacé
                $row['numero_original'] = $importedNum;

                // VALIDATION CLASSE 4 OBLIGATOIRE POUR LES TIERS
                if (!$generationPrefix) {
                    $row['numero_de_tiers'] = 'NON GÉNÉRÉ';
                    $errors[] = "Impossible de déduire le type de tiers (Fournisseur/Client) pour la génération. Veuillez vérifier le compte collectif ou l'intitulé.";
                } else {
                    // --- GÉNÉRATION AUTOMATIQUE DU TIERS ---
                    $upperOrigNum = strtoupper($importedNum);
                    $tierIdType = $targetCompany->tier_id_type ?? 'numeric';
                    $base = $generationPrefix;
                    
                    if ($tierIdType === 'alphanumeric' && !empty($row['intitule'])) {
                        $cleanName = strtoupper(preg_replace('/[^a-zA-Z]/', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $row['intitule'])));
                        $namePart = substr($cleanName, 0, 3);
                        if (strlen($namePart) < 1) $namePart = 'XXX';
                        $base = $generationPrefix . $namePart;
                    }

                    // CRITICAL IMPROVEMENT: If the imported number is already valid, keep it!
                    // Valid means: 
                    // 1. Length matches $tierDigits
                    // 2. Starts with the correct class prefix
                    // 3. For numeric type, it must be numeric
                    $isValidAlready = false;
                    $cleanedNum = $upperOrigNum;
                    if (is_numeric($cleanedNum)) {
                        $cleanedNum = str_pad($cleanedNum, $tierDigits, '0', STR_PAD_RIGHT);
                    }
                    
                    if (strlen($cleanedNum) == $tierDigits && str_starts_with($cleanedNum, $generationPrefix)) {
                        if ($tierIdType === 'numeric') {
                            $isValidAlready = is_numeric($cleanedNum);
                        } else {
                            $isValidAlready = true;
                        }
                    }

                    $numeroGenere = null;
                    if ($isValidAlready) {
                        $numeroGenere = $cleanedNum;
                    } else {
                        if (!isset($localMaxTiers[$base])) {
                            // Pour le staging, on peut estimer la séquence avec findNextAvailable
                            $numeroGenere = \App\Services\NumberingService::findNextAvailable(
                                'tier',
                                $targetCompanyId,
                                $base . str_pad('1', max(0, $tierDigits - strlen($base)), '0', STR_PAD_LEFT),
                                $tierDigits,
                                array_values($batchTierMap)
                            );
                            $localMaxTiers[$base] = $numeroGenere;
                        } else {
                            $lastId = $localMaxTiers[$base];
                            $baseLen = strlen($base);
                            $sequencePart = substr($lastId, $baseLen);
                            if (is_numeric($sequencePart)) {
                                $nextSeq = (int)$sequencePart + 1;
                                $availableSpace = max(0, $tierDigits - $baseLen);
                                $numeroGenere = $base . str_pad($nextSeq, $availableSpace, '0', STR_PAD_LEFT);
                                $localMaxTiers[$base] = $numeroGenere;
                            } else {
                                $numeroGenere = $base . str_pad('1', max(0, $tierDigits - strlen($base)), '0', STR_PAD_LEFT);
                                $localMaxTiers[$base] = $numeroGenere;
                            }
                        }
                    }

                    $finalNum = $numeroGenere;

                    // --- LOGIQUE DE DOUBLONS SMART POUR TIERS ---
                    $existingMatch = $tierDetails->get(strtoupper($importedNum)) ?? (isset($tierMapping[$upperOrigNum]) ? $tierDetails->get(strtoupper($tierMapping[$upperOrigNum])) : null);

                    $isDuplicateInDb = ($existingMatch !== null);
                    $isDuplicateInBatch = false;
                    foreach ($rowsWithStatus as $prevRow) {
                        if (($prevRow['status'] ?? '') === 'valid') {
                            $prevOrig = $prevRow['data']['numero_original'] ?? '';
                            $prevTiers = $prevRow['data']['numero_de_tiers'] ?? '';
                            if ((!empty($upperOrigNum) && strtoupper($prevOrig) === strtoupper($upperOrigNum)) || 
                                (!empty($importedNum) && strtoupper($prevTiers) === strtoupper($importedNum))) {
                                $isDuplicateInBatch = true;
                                break;
                            }
                        }
                    }

                    $existingLabel = $existingMatch ? $existingMatch->intitule : null;
                    if (!$existingLabel) {
                        foreach ($rowsWithStatus as $prevRow) {
                            if (($prevRow['status'] ?? '') === 'valid') {
                                $prevOrig = $prevRow['data']['numero_original'] ?? '';
                                $prevTiers = $prevRow['data']['numero_de_tiers'] ?? '';
                                if ((!empty($upperOrigNum) && strtoupper($prevOrig) === strtoupper($upperOrigNum)) || 
                                    (!empty($importedNum) && strtoupper($prevTiers) === strtoupper($importedNum))) {
                                    $existingLabel = $prevRow['data']['intitule'] ?? null;
                                    break;
                                }
                            }
                        }
                    }

                    if ($isDuplicateInDb || $isDuplicateInBatch) {
                        $currentLabelUpper = trim(strtoupper($row['intitule'] ?? ''));
                        $existingLabelUpper = trim(strtoupper($existingLabel ?? ''));

                        if ($currentLabelUpper === $existingLabelUpper) {
                            $row['is_duplicate'] = true;
                            $row['existing_label'] = $existingLabel;
                            $row['info'] = "Doublon (Tiers déjà présent). Cette ligne sera ignorée.";
                            $duplicateCount++;
                            $row['numero_de_tiers'] = $existingMatch ? $existingMatch->numero_de_tiers : ($batchTierMap[$upperOrigNum] ?? $importedNum);
                            unset($row['info_renum']);
                        } else {
                            $isNumberTakenInDb = $tierDetails->has($finalNum);
                            $isNumberTakenInBatch = in_array($finalNum, array_values($batchTierMap));

                            if ($isNumberTakenInDb || $isNumberTakenInBatch || $finalNum === $importedNum) {
                                $nextId = \App\Services\NumberingService::findNextAvailable(
                                    'tier',
                                    $targetCompanyId,
                                    $finalNum,
                                    $tierDigits,
                                    array_values($batchTierMap)
                                );
                                $finalNum = $nextId;
                            }
                            $row['numero_de_tiers'] = $finalNum;
                            $batchTierMap[$upperOrigNum] = $finalNum;
                            $row['info'] = "Nouveau tiers suggéré (" . $finalNum . ") car le libellé diffère de l'existant.";
                            $row['info_renum'] = "Sera généré en : " . $finalNum;
                            $row['is_duplicate'] = false;
                        }
                    } else {
                        // VARIATION OU NOUVEAU TIERS - On vérifie si la séquence générée est libre
                        $isNumberTakenInDb = $tierDetails->has($finalNum);
                        $isNumberTakenInBatch = in_array($finalNum, array_values($batchTierMap));

                        if ($isNumberTakenInDb || $isNumberTakenInBatch) {
                            $nextId = \App\Services\NumberingService::findNextAvailable(
                                'tier',
                                $targetCompanyId,
                                $finalNum,
                                $tierDigits,
                                array_values($batchTierMap)
                            );
                            $finalNum = $nextId;
                        }
                        $row['numero_de_tiers'] = $finalNum;
                        $batchTierMap[$upperOrigNum] = $finalNum;
                        $row['info_renum'] = "Sera généré en : " . $finalNum;
                    }

                    if (empty($row['numero_de_tiers'])) {
                        $errors[] = "Numéro de tiers impossible à générer.";
                    }
                }

                // Normalisation finale du type de tiers pour l'affichage
                if (!empty($rowType)) {
                    $row['type_de_tiers'] = $rowType;
                }
            } else {
                // Validation pour Écritures
                // Fonction robuste d'analyse de montants (SAGE met parfois des points, espaces, ou virgules)
                $parseAmount = function($val) {
                    if (empty($val)) return 0.0;
                    $val = trim((string)$val);
                    $val = str_replace([' ', ' ', "\xC2\xA0"], '', $val); // Enlève les espaces insécables SAGE
                    $isNegative = str_starts_with($val, '-');
                    $val = ltrim($val, '-');
                    if (strpos($val, ',') !== false && strpos($val, '.') !== false) {
                        $val = (strrpos($val, ',') > strrpos($val, '.')) ? str_replace(['.', ','], ['', '.'], $val) : str_replace(',', '', $val);
                    } else {
                        $val = str_replace(',', '.', $val);
                    }
                    $val = preg_replace('/[^0-9.]/', '', $val);
                    return $isNegative ? -(float)$val : (float)$val;
                };

                $rowCompte = trim($row['compte'] ?? '');
                $rowJournal = trim($row['journal'] ?? '');
                $rowTiers = trim($row['tiers'] ?? '');

                // 0. Filtrage Analytiques (Type A) : ignorées et exclues des contrôles
                $typeEcriture = strtoupper(trim((string)($row['type_ecriture'] ?? '')));
                if ($typeEcriture === 'A' || $typeEcriture === 'ANALYTIQUE') {
                    $rowDebit = $parseAmount($row['debit'] ?? 0);
                    $rowCredit = $parseAmount($row['credit'] ?? 0);
                    $row['debit_val'] = $rowDebit;
                    $row['credit_val'] = $rowCredit;
                    $errors = ["Ignorée (analytique - type A)"];
                    $errors = array_values(array_filter($errors, fn($e) => is_string($e) ? trim($e) !== '' : !empty($e)));

                    $rowsWithStatus[] = [
                        'index' => $index,
                        'data' => $row,
                        'status' => 'duplicate',
                        'errors' => $errors,
                        'debit' => $row['debit_val'] ?? 0,
                        'credit' => $row['credit_val'] ?? 0
                    ];
                    continue;
                }

                // 1. Journal
                if (!empty($rowJournal)) {
                    $rowJournalUpper = strtoupper(trim($rowJournal));
                    if (isset($journalMapping[$rowJournalUpper])) {
                        $row['code_original_journal'] = $rowJournal;
                        $row['journal'] = $journalMapping[$rowJournalUpper];
                        $rowJournal = $row['journal'];
                    } elseif (in_array($rowJournalUpper, array_map('strtoupper', $existingJournalsArr))) {
                        // Déjà un code journal valide
                        $row['journal'] = $rowJournalUpper;
                        $rowJournal = $rowJournalUpper;
                    }
                }

                // 2. Compte Général
                if (!empty($rowCompte)) {
                    $rowCompteNormalized = strtoupper(trim((string)$rowCompte));

                    // Standardisation automatique (ex: 6011 -> 60110000)
                    $rowCompteNormalized = $this->standardizeAccountNumber($rowCompteNormalized, $accountDigits);

                    if (isset($accountMapping[$rowCompteNormalized])) {
                        $row['numero_original_compte'] = $rowCompte;
                        $row['compte'] = $accountMapping[$rowCompteNormalized];
                        $rowCompte = $row['compte'];
                    } else {
                        $rowCompte = $rowCompteNormalized;
                    }
                    $row['compte'] = $rowCompte;
                }

                // 3. Tiers
                if (!empty($rowTiers)) {
                    $rowTiersUpper = strtoupper(trim($rowTiers));
                    
                    // Standardisation/padding automatique si le tiers est numérique et plus court que tier_digits
                    $standardizedTier = $rowTiersUpper;
                    if (is_numeric($rowTiersUpper) && strlen($rowTiersUpper) < $tierDigits) {
                        $standardizedTier = str_pad($rowTiersUpper, $tierDigits, '0', STR_PAD_RIGHT);
                    }
                    
                    if (isset($tierMapping[$rowTiersUpper])) {
                        $row['numero_original_tiers'] = $rowTiers;
                        $row['tiers'] = $tierMapping[$rowTiersUpper];
                        $rowTiers = $row['tiers'];
                    } elseif (isset($tierMapping[$standardizedTier])) {
                        $row['numero_original_tiers'] = $rowTiers;
                        $row['tiers'] = $tierMapping[$standardizedTier];
                        $rowTiers = $row['tiers'];
                    } elseif (in_array($rowTiersUpper, array_map('strtoupper', $existingTiers))) {
                        // Déjà un code standardisé correct
                        $row['tiers'] = $rowTiersUpper;
                        $rowTiers = $rowTiersUpper;
                    } elseif (in_array($standardizedTier, array_map('strtoupper', $existingTiers))) {
                        // Déjà un code standardisé correct (version paddée)
                        $row['tiers'] = $standardizedTier;
                        $rowTiers = $standardizedTier;
                    } else {
                        $errors[] = "Tiers inconnu : $rowTiers";
                        $missingTiers[$rowTiersUpper] = $row['intitule'] ?? 'Tiers ' . $rowTiersUpper;
                    }
                }

                $rowDebit = $parseAmount($row['debit'] ?? 0);
                $rowCredit = $parseAmount($row['credit'] ?? 0);
                $rowDateStr = trim($row['jour'] ?? '');

                // 1. Validation de l'existence minimale
                if (empty($rowCompte)) {
                    $errors[] = "Compte manquant";
                } elseif (!in_array($rowCompte, $existingAccounts)) {
                    $errors[] = "Compte inconnu : $rowCompte";
                    $missingAccounts[$rowCompte] = $row['intitule'] ?? 'Compte ' . $rowCompte;
                }

                if (empty($rowJournal)) {
                    $errors[] = "Journal manquant";
                } elseif (!in_array(strtoupper($rowJournal), array_map('strtoupper', $existingJournalsArr))) {
                    $errors[] = "Journal inconnu : $rowJournal";
                    $missingJournals[$rowJournal] = $row['intitule'] ?? 'Journal ' . $rowJournal;
                }

                $ns = trim((string)($row['n_saisie'] ?? ''));
                $ref = trim((string)($row['reference'] ?? ''));

                if ($groupingKeyStrategy === 'reference') {
                    if ($ref === '') {
                        $errors[] = "Numéro de facture / référence manquant";
                    }
                } else {
                    if ($ns === '') {
                        $errors[] = "Numéro de saisie manquant";
                    }
                }

                // 2. Validation des montants (Non nul)
                if (abs($rowDebit) < 0.01 && abs($rowCredit) < 0.01) {
                    $errors[] = "Ligne vide : Débit et Crédit sont nuls";
                }

                // 3. Validation de la Date (Stricte)
                if (empty($rowDateStr)) {
                    $errors[] = "Date manquante";
                } else {
                    $isValidDate = false;
                    $parsedDate = null;

                    // Cas 1: Excel stocke parfois une date sous forme de numéro de série (ex: 45672)
                    if (is_numeric($rowDateStr) && (float)$rowDateStr > 59) {
                        try {
                            $dt = ExcelDate::excelToDateTimeObject((float)$rowDateStr);
                            $parsedDate = \Carbon\Carbon::instance($dt);
                            $isValidDate = true;
                        } catch (\Exception $e) {
                             $errors[] = "Date Excel invalide : $rowDateStr";
                        }
                    }
                    // Essayer le format Jour uniquement (1-31)
                    elseif (is_numeric($rowDateStr) && strlen($rowDateStr) <= 2) {
                        $day = (int)$rowDateStr;
                        if ($day >= 1 && $day <= 31) {
                            // AVERTISSEMENT : Jour seul => on suppose mois/année de l'exercice
                             $errors[] = "Format incomplet : Jour '$day' détecté. Veuillez utiliser JJ/MM/AAAA pour éviter toute erreur de période.";
                        } else {
                            $errors[] = "Jour invalide : $rowDateStr";
                        }
                    }
                    // Essayer les formats standards (d/m/Y, Y-m-d, d-m-Y)
                    else {
                        try {
                            // On ajoute j/n/Y etc pour être plus souple sur les zéros non significatifs
                            $formats = ['d/m/Y', 'j/n/Y', 'd/n/Y', 'j/m/Y', 'Y-m-d', 'd-m-Y', 'Y/m/d', 'dmy', 'dmY'];
                            foreach($formats as $fmt) {
                                try {
                                    $d = \Carbon\Carbon::createFromFormat($fmt, $rowDateStr);
                                    if ($d && $d->format($fmt) == $rowDateStr) {
                                        $parsedDate = $d;
                                        $isValidDate = true;
                                        break;
                                    }
                                } catch (\Exception $e) { continue; }
                            }
                        } catch (\Exception $e) {}

                        if (!$isValidDate) {
                             // Tentative de parsing souple
                             try {
                                 $parsedDate = \Carbon\Carbon::parse($rowDateStr);
                                 $isValidDate = true;
                             } catch (\Exception $e) {
                                 $errors[] = "Format de date invalide : $rowDateStr (Attendu : JJ/MM/AAAA)";
                             }
                        }

                        // 4. Vérification Période Exercice
                        if ($isValidDate && $parsedDate) {
                            if ($exercice) {
                                // On utilise startOfDay() pour comparer proprement les dates sans heures
                                if (!$parsedDate->between($exercice->date_debut->startOfDay(), $exercice->date_fin->endOfDay())) {
                                    $errors[] = "Date hors exercice : " . $parsedDate->format('d/m/Y') .
                                                " (Exercice : " . $exercice->date_debut->format('d/m/Y') . " - " . $exercice->date_fin->format('d/m/Y') . ")";
                                }
                            }
                        }
                    }
                }

                // Validation Tiers obligatoire pour 401/411 (désactivée à la demande de l'utilisateur)
                $rowTiers = trim($row['tiers'] ?? '');
                // if (empty($rowTiers) && Str::startsWith($rowCompte, ['401', '402', '411', '412'])) {
                //     $errors[] = "Un numéro de tiers est obligatoire pour le compte '$rowCompte'.";
                // }

                if (!empty($rowTiers)) {
                    // Use case-insensitive check since $existingTiers keys are uppercased
                    // but $rowTiers after tierMapping resolution may have original DB casing
                    $tierExists = in_array(strtoupper($rowTiers), $existingTiers);
                    if (!$tierExists) {
                        $originalPart = !empty($row['numero_original_tiers']) ? " (L'original '{$row['numero_original_tiers']}' n'a pas pu être rattaché)" : "";
                        $errors[] = "Tiers inconnu : $rowTiers$originalPart";
                    }
                }

                $row['debit_val'] = $rowDebit;
                $row['credit_val'] = $rowCredit;

                // --- DÉTECTION DE DOUBLON EN BASE DE DONNÉES (ÉCRITURES) ---
                if (empty($errors) && $isValidDate && $parsedDate) {
                    $compteObj = $accountDetails->get($rowCompte);
                    $journalObj = $journalDetails->get(strtoupper($rowJournal));
                    $tiersObj = !empty($rowTiers) ? $tierDetails->get(strtoupper($rowTiers)) : null;

                    $compteId = $compteObj ? $compteObj->id : null;
                    $journalId = $journalObj ? $journalObj->id : null;
                    $tiersId = $tiersObj ? $tiersObj->id : null;

                    if ($compteId && $journalId) {
                         $hash = md5($parsedDate->format('Y-m-d') . '|' . $journalId . '|' . $compteId . '|' . $tiersId . '|' . (float)$rowDebit . '|' . (float)$rowCredit . '|' . strtoupper($row['reference'] ?? 'IMPORT') . '|' . strtoupper($row['libelle'] ?? 'IMPORTATION EXTERNE'));
                         if (isset($existingEcrituresDbHashes[$hash])) {
                             $row['is_duplicate'] = true;
                             $row['info'] = "Existe déjà en base de données.";
                             $duplicateCount++;
                         }
                    }
                }
            }

            $errors = array_values(array_filter($errors, function ($e) {
                return is_string($e) ? trim($e) !== '' : !empty($e);
            }));

            $status = (count($errors) > 0) ? 'error' : (($row['is_duplicate'] ?? false) ? 'duplicate' : 'valid');
            if ($status === 'error' && count($errors) === 0) {
                $errors = ["Erreur de validation inconnue"];
            }
            if ($status == 'error') $errorCount++;
            elseif ($status == 'valid') $validCount++;

            $rowsWithStatus[] = [
                'index' => $index,
                'data' => $row,
                'status' => $status,
                'errors' => $errors,
                'debit' => $row['debit_val'] ?? 0,
                'credit' => $row['credit_val'] ?? 0
            ];
        }

        // --- VALIDATION GLOBALE & PAR GROUPE (Équilibre) ---
        if ($import->type == 'courant') {
            $balances = [];
            foreach ($rowsWithStatus as &$r) {
                if ($r['status'] === 'duplicate') {
                    continue;
                }

                $nSaisie = trim((string)($r['data']['n_saisie'] ?? ''));
                $reference = trim((string)($r['data']['reference'] ?? ''));
                $jour = trim((string)($r['data']['jour'] ?? ''));
                $journal = trim((string)($r['data']['journal'] ?? ''));
                $originalJournal = trim((string)($r['data']['code_original_journal'] ?? ''));
                
                if (strtoupper($journal) === 'RAN' || strtoupper($originalJournal) === 'RAN') {
                    if (!isset($cachedRanNumberForStaging)) {
                        $cachedRanNumberForStaging = $this->generateRanSaisieNumber($targetCompanyId);
                    }
                    $nSaisie = $cachedRanNumberForStaging;
                    $reference = $cachedRanNumberForStaging;
                    $r['data']['n_saisie'] = $cachedRanNumberForStaging;
                    $r['data']['reference'] = $cachedRanNumberForStaging;
                }
                
                if ($groupingKeyStrategy === 'reference') {
                    $keyPart = $reference;
                } else {
                    $keyPart = $nSaisie;
                }
                
                if ($keyPart === '') {
                    $keyPart = 'row_' . $r['index']; // fallback
                }
                
                // Group by date + keyPart only (NOT journal) to ensure all lines of the same
                // saisie/reference are in the same group even if journal values differ slightly
                $ref = $jour . '|' . $keyPart;

                if (!isset($balances[$ref])) $balances[$ref] = ['d' => 0, 'c' => 0, 'rows' => []];
                $balances[$ref]['d'] += round((float)$r['debit'], 2);
                $balances[$ref]['c'] += round((float)$r['credit'], 2);
                $balances[$ref]['rows'][] = &$r;
            }


            // Résumé par groupe pour affichage (débit/crédit/diff par n_saisie)
            $groupSummary = [];
            foreach ($balances as $ref => $b) {
                $groupSummary[$ref] = [
                    'debit' => round((float)$b['d'], 2),
                    'credit' => round((float)$b['c'], 2),
                    'diff' => round((float)($b['d'] - $b['c']), 2),
                ];
            }

            foreach ($balances as $ref => $b) {
                if (abs($b['d'] - $b['c']) > 0.01) {
                    $diff = round(abs($b['d'] - $b['c']), 2);
                    foreach ($b['rows'] as &$rowError) {
                        $rowError['status'] = 'error';
                        $rowError['errors'][] = "DÉSÉQUILIBRE Groupe '$ref' : Différence $diff";
                    }
                }
            }

            // Attacher le résumé (y compris après marquage des erreurs)
            foreach ($rowsWithStatus as &$r) {
                if ($r['status'] === 'duplicate') {
                    continue;
                }

                $nSaisie = trim((string)($r['data']['n_saisie'] ?? ''));
                $reference = trim((string)($r['data']['reference'] ?? ''));
                $jour = trim((string)($r['data']['jour'] ?? ''));
                $journal = trim((string)($r['data']['journal'] ?? ''));
                $originalJournal = trim((string)($r['data']['code_original_journal'] ?? ''));
                
                if (strtoupper($journal) === 'RAN' || strtoupper($originalJournal) === 'RAN') {
                    if (!isset($cachedRanNumberForStaging)) {
                        $cachedRanNumberForStaging = $this->generateRanSaisieNumber($targetCompanyId);
                    }
                    $nSaisie = $cachedRanNumberForStaging;
                    $reference = $cachedRanNumberForStaging;
                }
                
                if ($groupingKeyStrategy === 'reference') {
                    $keyPart = $reference;
                } else {
                    $keyPart = $nSaisie;
                }
                
                if ($keyPart === '') {
                    $keyPart = 'row_' . $r['index']; // fallback
                }
                
                $ref = $jour . '|' . $keyPart;

                $r['group_key'] = $ref;
                $r['group_debit'] = $groupSummary[$ref]['debit'] ?? null;
                $r['group_credit'] = $groupSummary[$ref]['credit'] ?? null;
                $r['group_diff'] = $groupSummary[$ref]['diff'] ?? null;
            }

            $totalDebit = array_sum(array_map(fn($r) => ($r['status'] ?? null) === 'duplicate' ? 0 : (float)($r['debit'] ?? 0), $rowsWithStatus));
            $totalCredit = array_sum(array_map(fn($r) => ($r['status'] ?? null) === 'duplicate' ? 0 : (float)($r['credit'] ?? 0), $rowsWithStatus));

            if (abs($totalDebit - $totalCredit) > 0.01) {
                $diff = round($totalDebit - $totalCredit, 2);
                $import->update(['description' => "ATTENTION : Lot total déséquilibré de $diff"]);
            } else {
                $import->update(['description' => null]);
            }
        }

        // IMPORTANT: les contrôles d'équilibre peuvent requalifier des lignes (valid -> error).
        // On recalcule donc les compteurs sur les statuts finaux pour que l'UI (cartes + filtres) soit cohérente.
        $errorCount = 0;
        $validCount = 0;
        foreach ($rowsWithStatus as $r) {
            if (($r['status'] ?? null) === 'error' || ($r['status'] ?? null) === 'duplicate') {
                $errorCount++;
            } elseif (($r['status'] ?? null) === 'valid') {
                $validCount++;
            }
        }

        \Illuminate\Support\Facades\Cache::put($cacheKey, [
            'rowsWithStatus' => $rowsWithStatus,
            'errorCount' => $errorCount,
            'validCount' => $validCount,
            'duplicateCount' => $duplicateCount,
            'missingAccounts' => $missingAccounts,
            'missingJournals' => $missingJournals,
            'missingTiers' => $missingTiers,
        ], 3600);
    }

        $typeLabels = [
            'initial' => 'Plan Comptable Master',
            'tiers' => 'Modèle de Tiers',
            'journals' => 'Modèle des Journaux',
            'courant' => 'Écritures Comptables'
        ];
        $importTitle = $typeLabels[$import->type] ?? 'Importation';

        $viewName = 'admin.config.import_staging';
        if ($import->type == 'initial') $viewName = 'admin.config.import_staging_plan';
        elseif ($import->type == 'tiers') $viewName = 'admin.config.import_staging_tiers';
        elseif ($import->type == 'journals') $viewName = 'admin.config.import_staging_journals';

        // On marque l'import comme "Bloqué" si des erreurs subsistent
        $import->update([
            'status' => ($errorCount > 0) ? 'error' : 'processing'
        ]);

        // Récupération des comptes de classe 5 pour les journaux de trésorerie dans le modal d'édition
        $plansComptables = PlanComptable::whereRaw('SUBSTRING(numero_de_compte, 1, 1) = "5"')
            ->orderBy('numero_de_compte')
            ->get();

        // --- FILTRAGE côté serveur ---
        $statusFilter = request('status', 'all');
        $searchFilter = request('search');

        $rowsWithStatusFiltered = $rowsWithStatus;

        if ($statusFilter === 'error') {
            $rowsWithStatusFiltered = array_filter($rowsWithStatusFiltered, function($r) {
                return in_array($r['status'] ?? null, ['error', 'duplicate']);
            });
        } elseif ($statusFilter !== 'all') {
            $rowsWithStatusFiltered = array_filter($rowsWithStatusFiltered, function($r) use ($statusFilter) {
                return ($r['status'] ?? null) === $statusFilter;
            });
        }

        if (!empty($searchFilter)) {
            $searchFilter = strtolower($searchFilter);
            $rowsWithStatusFiltered = array_filter($rowsWithStatusFiltered, function($r) use ($searchFilter) {
                $dataStr = implode(' ', array_map(fn($v) => (string)$v, $r['data'] ?? []));
                return str_contains(strtolower($dataStr), $searchFilter);
            });
        }

        // --- PAGINATION côté serveur (pour éviter les pages trop lourdes sur 33k lignes) ---
        $perPage = 150;
        $currentPage = max(1, (int) request('page', 1));
        $totalRows = count($rowsWithStatusFiltered);
        $totalPages = (int) ceil($totalRows / $perPage);
        $currentPage = min($currentPage, max(1, $totalPages));
        $offset = ($currentPage - 1) * $perPage;
        $rowsWithStatusPaged = array_slice(array_values($rowsWithStatusFiltered), $offset, $perPage);

        if ($returnData) {
            return [
                'rowsWithStatus' => $rowsWithStatus,
                'errorCount' => $errorCount,
                'validCount' => $validCount,
                'duplicateCount' => $duplicateCount,
                'user' => $user,
                'accountDigits' => $accountDigits
            ];
        }

        if (request()->ajax()) {
            return view('admin.config.import_staging_content', compact(
                'import', 'rowsWithStatus', 'rowsWithStatusPaged',
                'errorCount', 'validCount', 'duplicateCount', 'importTitle',
                'user', 'plansComptables', 'accountDigits',
                'currentPage', 'totalPages', 'totalRows', 'perPage',
                'statusFilter', 'searchFilter', 'mapping',
                'missingAccounts', 'missingJournals', 'missingTiers'
            ));
        }

        return view($viewName, compact(
            'import', 'rowsWithStatus', 'rowsWithStatusPaged',
            'errorCount', 'validCount', 'duplicateCount', 'importTitle',
            'user', 'plansComptables', 'accountDigits',
            'currentPage', 'totalPages', 'totalRows', 'perPage',
            'statusFilter', 'searchFilter', 'mapping',
            'missingAccounts', 'missingJournals', 'missingTiers'
        ));
    }

    /**
     * Tunnel d'Importation - Injection Finale (ASYNC via Queue Job)
     */
    public function commitImport(Request $request, $id)
    {
        $import = ImportStaging::findOrFail($id);
        $user   = Auth::user();

        // Si déjà committed, afficher le rapport existant
        if ($import->status === 'committed') {
            $report = ($import->metadata ?? [])['commit_report']
                   ?? ['status' => 'success', 'processed_g' => 0, 'errors' => []];
            return view('admin.import.report', ['report' => $report, 'batch_id' => $id]);
        }

        // Marquer l'import comme en cours
        $import->update([
            'status'    => 'processing',
            'error_log' => null,
            'metadata'  => array_merge($import->metadata ?? [], [
                'commit_status'   => 'processing',
                'commit_progress' => 0,
                'commit_message'  => 'Démarrage du traitement…',
            ]),
        ]);

        // Dispatcher le Job de traitement
        // (async si QUEUE_CONNECTION != sync ; sinon exécution immédiate et on redirige après)
        \App\Jobs\ImportCommitJob::dispatch($import->id, $user->id);

        return view('admin.import.processing', [
            'import'    => $import,
            'statusUrl' => route('admin.import.job.status', $id),
            'reportUrl' => route('admin.import.report.view', $id),
        ]);
    }

    /**
     * Tunnel d'Importation - Statut du Job (polling AJAX)
     */
    public function importJobStatus(Request $request, $id)
    {
        $import = ImportStaging::findOrFail($id);
        $meta   = $import->metadata ?? [];

        return response()->json([
            'status'   => $meta['commit_status']   ?? $import->status,
            'progress' => (int)($meta['commit_progress'] ?? 0),
            'message'  => $meta['commit_message']  ?? '',
            'error'    => in_array($import->status, ['error']) ? ($import->error_log ?? '') : null,
        ]);
    }

    /**
     * Tunnel d'Importation - Affichage du rapport final
     */
    public function importReportView(Request $request, $id)
    {
        $import = ImportStaging::findOrFail($id);
        $report = ($import->metadata ?? [])['commit_report']
               ?? ['status' => $import->status, 'processed_g' => 0, 'errors' => [], 'error_log' => $import->error_log];

        // Ensure all keys required by resources/views/admin/import/report.blade.php exist
        $defaults = [
            'status'       => 'success',
            'processed_g'  => 0,
            'filtered_a'   => 0,
            'deduplicated' => 0,
            'total_debit'  => 0.0,
            'total_credit' => 0.0,
            'new_accounts' => 0,
            'new_tiers'    => 0,
            'errors'       => [],
            'warnings'     => [],
        ];
        $report = array_merge($defaults, $report);

        return view('admin.import.report', ['report' => $report, 'batch_id' => $id]);
    }

    /**
     * Tunnel d'Importation - Création rapide de compte à la volée
     */
    public function suggestNextNumber(Request $request) {
        $type = $request->get('type');
        $original = $request->get('original');
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $suggestion = $original;

        try {
            if ($type === 'account') {
                // Essayer d'extraire la racine (ex: 411)
                $prefix = substr(preg_replace('/[^0-9]/', '', $original), 0, 3);
                if (empty($prefix)) $prefix = '411'; // Par défaut client

                $maxAccount = PlanComptable::where('company_id', $companyId)
                    ->where('numero_de_compte', 'LIKE', $prefix . '%')
                    ->max('numero_de_compte');

                if ($maxAccount) {
                    $suggestion = str_pad((int)$maxAccount + 1, strlen($maxAccount), '0', STR_PAD_LEFT);
                } else {
                    $targetLength = $user->company->account_digits ?? 8;
                    $suggestion = str_pad($prefix . '1', $targetLength, '0', STR_PAD_RIGHT);
                }
            } elseif ($type === 'tier') {
                $maxTier = PlanTiers::where('company_id', $companyId)->max('numero_de_tiers');
                if ($maxTier && preg_match('/^([^\d]*)(\d+)$/', $maxTier, $matches)) {
                    $suggestion = $matches[1] . str_pad((int)$matches[2] + 1, strlen($matches[2]), '0', STR_PAD_LEFT);
                } elseif ($maxTier) {
                    // S'il n'y a pas de pad reconnu
                    $suggestion = $maxTier . '1';
                } else {
                    $suggestion = 'T001';
                }
            } elseif ($type === 'journal') {
                $maxJournal = CodeJournal::where('company_id', $companyId)->max('code_journal');
                if ($maxJournal && preg_match('/^([^\d]*)(\d+)$/', $maxJournal, $matches)) {
                    $suggestion = $matches[1] . str_pad((int)$matches[2] + 1, strlen($matches[2]), '0', STR_PAD_LEFT);
                } else {
                    $suggestion = 'J01';
                }
            }
            return response()->json(['success' => true, 'suggestion' => $suggestion]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'suggestion' => $original]);
        }
    }

    /**
     * Tunnel d'Importation - Création rapide de compte à la volée
     */
    public function quickAccountCreate(Request $request)
    {
        $request->validate([
            'numero_compte' => 'required|string',
            'intitule' => 'required|string',
            'type_de_compte' => 'nullable|string'
        ]);

        try {
            $user = Auth::user();

            $exists = PlanComptable::where('company_id', session('current_company_id', $user->company_id))
                ->where('numero_de_compte', $request->numero_compte)
                ->exists();

            if ($exists) {
                return response()->json(['success' => false, 'message' => 'Ce compte existe déjà.']);
            }

            PlanComptable::create([
                'numero_de_compte' => $request->numero_compte,
                'intitule' => strtoupper($request->intitule),
                'type_de_compte' => $request->type_de_compte ?? 'Bilan',
                'classe' => substr($request->numero_compte, 0, 1),
                'user_id' => $user->id,
                'company_id' => session('current_company_id', $user->company_id),
                'adding_strategy' => 'manuel'
            ]);

            // Mettre à jour les lignes de l'import si un import_id et un original sont fournis
            if ($request->filled('import_id') && $request->filled('original_numero')) {
                $importId = $request->import_id;
                $original = $request->original_numero;
                $nouveau = $request->numero_compte;

                if ($original !== $nouveau) {
                    $import = ImportStaging::find($importId);
                    if ($import) {
                        $data = $import->raw_data;
                        $mapping = $import->mapping;
                        // On trouve la colonne "compte"
                        $colCompte = $mapping['compte'] ?? null;
                        if ($colCompte !== null) {
                            $updated = false;
                            foreach ($data as $index => &$row) {
                                if (isset($row[$colCompte]) && trim($row[$colCompte]) === $original) {
                                    $row[$colCompte] = $nouveau;
                                    $updated = true;
                                }
                            }
                            if ($updated) {
                                $import->raw_data = $data;
                                $import->save();
                            }
                        }
                    }
                }
            }

            return response()->json(['success' => true, 'message' => 'Compte créé avec succès.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Tunnel d'Importation - Création rapide de Tiers
     */
    public function quickTierCreate(Request $request)
    {
        $request->validate([
            'numero_tiers' => 'required|string',
            'intitule' => 'required|string',
            'type_de_tiers' => 'nullable|string'
        ]);

        try {
            $user = Auth::user();

            $exists = PlanTiers::where('company_id', session('current_company_id', $user->company_id))
                ->where('numero_de_tiers', $request->numero_tiers)
                ->exists();

            if ($exists) {
                return response()->json(['success' => false, 'message' => 'Ce tiers existe déjà.']);
            }

            $typeTiers = $request->type_de_tiers ?? 'Client';
            // Utilisation des racines standards si non spécifié
            $compteGeneral = ($typeTiers === 'Fournisseur') ? '401100' : '411100';

            PlanTiers::create([
                'numero_de_tiers' => $request->numero_tiers,
                'intitule' => strtoupper($request->intitule),
                'type_de_tiers' => $typeTiers,
                'compte_general' => $compteGeneral,
                'numero_original' => $request->original_numero ?? null,
                'user_id' => $user->id,
                'company_id' => session('current_company_id', $user->company_id),
            ]);

            // Mettre à jour les lignes de l'import si un import_id et un original sont fournis
            if ($request->filled('import_id') && $request->filled('original_numero')) {
                $importId = $request->import_id;
                $original = $request->original_numero;
                $nouveau = $request->numero_tiers;

                if ($original !== $nouveau) {
                    $import = ImportStaging::find($importId);
                    if ($import) {
                        $data = $import->raw_data;
                        if (is_array($data)) {
                            $updated = false;
                            foreach ($data as &$row) {
                                // On cherche dans toute la ligne si une cellule correspond au numéro original
                                // car le mapping peut varier. C'est plus sûr.
                                foreach ($row as $k => $v) {
                                    if (trim((string)$v) === $original) {
                                        $row[$k] = $nouveau;
                                        $updated = true;
                                    }
                                }
                            }
                            if ($updated) {
                                $import->raw_data = $data;
                                $import->save();
                            }
                        }
                    }
                }
            }

            return response()->json(['success' => true, 'message' => 'Tiers créé avec succès.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Tunnel d'Importation - Création rapide de Journal
     */
    public function quickJournalCreate(Request $request)
    {
        $request->validate([
            'code_journal' => 'required|string',
            'intitule' => 'required|string',
            'type_journal' => 'nullable|string'
        ]);

        try {
            $user = Auth::user();

            $exists = CodeJournal::where('company_id', session('current_company_id', $user->company_id))
                ->where('code_journal', $request->code_journal)
                ->exists();

            if ($exists) {
                return response()->json(['success' => false, 'message' => 'Ce journal existe déjà.']);
            }

            CodeJournal::create([
                'code_journal' => strtoupper($request->code_journal),
                'intitule' => strtoupper($request->intitule),
                'type' => $request->type_journal ?? 'Opérations diverses',
                'traitement_analytique' => 0,
                'poste_tresorerie' => 'Autre',
                'rapprochement_sur' => 'Manuel',
                'user_id' => $user->id,
                'company_id' => session('current_company_id', $user->company_id),
            ]);

            // Mettre à jour les lignes de l'import si un import_id et un original sont fournis
            if ($request->filled('import_id') && $request->filled('original_numero')) {
                $importId = $request->import_id;
                $original = $request->original_numero;
                $nouveau = strtoupper($request->code_journal);

                if ($original !== $nouveau) {
                    $import = ImportStaging::find($importId);
                    if ($import) {
                        $data = $import->raw_data;
                        $mapping = $import->mapping;
                        // On trouve la colonne "journal"
                        $colJournal = $mapping['journal'] ?? null;
                        if ($colJournal !== null) {
                            $updated = false;
                            foreach ($data as $index => &$row) {
                                if (isset($row[$colJournal]) && trim($row[$colJournal]) === $original) {
                                    $row[$colJournal] = $nouveau;
                                    $updated = true;
                                }
                            }
                            if ($updated) {
                                $import->raw_data = $data;
                                $import->save();
                            }
                        }
                    }
                }
            }

            return response()->json(['success' => true, 'message' => 'Journal créé avec succès.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Tunnel d'Importation - Annulation de l'import (Suppression du Staging)
     */
    public function cancelImport($id)
    {
        try {
            $import = ImportStaging::findOrFail($id);
            $import->delete();
            return redirect()->route('admin.import.hub')->with('success', 'Importation annulée avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de l\'annulation : ' . $e->getMessage());
        }
    }

    /**
     * Modification d'une ligne spécifique du staging
     */
    public function updateRow(Request $request, $id, $index)
    {
        Log::info("STAGING UPDATE ATTEMPT: ID=$id, Index=$index");
        try {
            $import = ImportStaging::findOrFail($id);
            $data = $import->raw_data;

            // L'index passé ici est l'index original dans le tableau $data
            if (!isset($data[$index])) {
                Log::warning("STAGING UPDATE: Index $index not found in raw_data");
                return response()->json(['success' => false, 'message' => "Ligne non trouvée (Index: $index)."], 404);
            }

            $newValues = $request->input('values');

            Log::info("STAGING UPDATE: Data received", [
                'target_index' => $index,
                'provided_values' => $newValues,
                'current_row' => $data[$index] ?? 'MISSING'
            ]);

            foreach ($newValues as $colIndex => $value) {
                // On met à jour la colonne si l'index est numérique (données brutes Excel)
                if (is_numeric($colIndex)) {
                    $data[$index][(int)$colIndex] = $value;
                } else {
                    // On garde la valeur sous sa clé textuelle (ex: "intitule", "numero_de_compte")
                    // pour forcer la surcharge (override) lors du re-processing du Staging
                    $data[$index][$colIndex] = $value;
                }
            }

            // Sauvegarde des données mises à jour
            $import->raw_data = $data;
            $import->save();

            Log::info("STAGING UPDATE: SUCCESS", [
                'saved_row' => $import->raw_data[$index] ?? 'ERROR'
            ]);

            return response()->json(['success' => true, 'message' => 'Ligne mise à jour avec succès.']);
        } catch (\Exception $e) {
            Log::error("STAGING UPDATE FAILED: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Suppression d'une ligne spécifique du staging
     */
    public function deleteRow($id, $index)
    {
        try {
            $import = ImportStaging::findOrFail($id);
            $data = $import->raw_data;

            if (!isset($data[$index])) {
                return response()->json(['success' => false, 'message' => 'Ligne non trouvée.'], 404);
            }

            // Supprimer la ligne par sa clé exacte
            unset($data[$index]);

            // Réindexer pour éviter les trous dans le JSON (optionnel mais propre pour des tableaux 0-indexed)
            $data = array_values($data);

            $import->update(['raw_data' => $data]);

            return response()->json(['success' => true, 'message' => 'Ligne supprimée de l\'import.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Ajout d'une nouvelle ligne au staging
     */
    public function addRow(Request $request, $id)
    {
        try {
            $import = ImportStaging::findOrFail($id);
            $data = $import->raw_data;

            $newValues = $request->input('values', []);

            // On s'assure que les clés sont numériques (correspondance colonnes brutes)
            $row = [];

            // On calcule l'index max du mapping pour initialiser une ligne vide cohérente
            $maxIdx = 0;
            foreach($import->mapping as $idx) {
                if(is_numeric($idx)) $maxIdx = max($maxIdx, (int)$idx);
            }

            // Initialiser la ligne avec des nulls
            $row = array_fill(0, $maxIdx + 1, null);

            // Remplir avec les valeurs reçues
            foreach($newValues as $colIdx => $val) {
                if (is_numeric($colIdx)) {
                    $row[(int)$colIdx] = $val;
                }
            }

            $data[] = $row;

            $import->update(['raw_data' => $data]);

            return response()->json(['success' => true, 'message' => 'Ligne ajoutée avec succès.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Suppression multiple de lignes de staging
     */
    public function deleteMultipleRows($id, Request $request)
    {
        try {
            $import = ImportStaging::findOrFail($id);
            $indices = $request->input('indices', []);

            if (empty($indices)) {
                return response()->json(['success' => false, 'message' => 'Aucune ligne sélectionnée.'], 400);
            }

            $data = $import->raw_data;

            // Trier les indices par ordre décroissant pour éviter les décalages lors de la suppression
            sort($indices);
            $indices = array_reverse($indices);

            foreach ($indices as $index) {
                if (isset($data[$index])) {
                    array_splice($data, $index, 1);
                }
            }

            $import->update(['raw_data' => $data]);

            return response()->json(['success' => true, 'message' => count($indices) . ' lignes supprimées de l\'import.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Module d'Exportation - Hub principal
     */
    public function exportHub()
    {
        $user = Auth::user();
        return view('admin.config.export', compact('user'));
    }

    /**
     * Module d'Exportation - Traitement de la génération
     */
    public function exportProcess(Request $request)
    {
        $type = $request->input('type'); // plan_comptable, plan_tiers, journals, ecritures
        $format = $request->input('format'); // excel, csv, sage, fec
        $user = Auth::user();

        // Récupération des données selon le type
        $data = [];
        $filename = "export_" . $type . "_" . date('Ymd_His');

        switch ($type) {
            case 'plan_comptable':
                $data = PlanComptable::where('company_id', session('current_company_id', $user->company_id))->orderBy('numero_de_compte')->get();
                $headers = ['Compte', 'Intitule', 'Type', 'Classe'];
                $callback = function($file) use ($data) {
                    foreach ($data as $row) {
                        fputcsv($file, [$row->numero_de_compte, $row->intitule, $row->type_de_compte, $row->classe], ';');
                    }
                };
                break;

            case 'plan_tiers':
                $data = PlanTiers::with('compte')->where('company_id', session('current_company_id', $user->company_id))->orderBy('numero_de_tiers')->get();
                $headers = ['Numero Tiers', 'Intitule', 'Type', 'Compte Collectif'];
                $callback = function($file) use ($data) {
                    foreach ($data as $row) {
                        fputcsv($file, [$row->numero_de_tiers, $row->intitule, $row->type_de_tiers, $row->compte->numero_de_compte ?? ''], ';');
                    }
                };
                break;

            case 'journals':
                $data = CodeJournal::where('company_id', session('current_company_id', $user->company_id))->orderBy('code_journal')->get();
                $headers = ['Code', 'Intitule', 'Type'];
                $callback = function($file) use ($data) {
                    foreach ($data as $row) {
                        fputcsv($file, [$row->code_journal, $row->intitule, $row->type], ';');
                    }
                };
                break;

            case 'ecritures':
                $query = EcritureComptable::with(['planComptable', 'planTiers', 'codeJournal'])
                    ->where('company_id', session('current_company_id', $user->company_id));

                if ($request->input('status') == 'validated') {
                    $query->where('statut', 'approved');
                }

                if ($request->input('journal') && $request->input('journal') != 'all') {
                    $query->where('code_journal_id', $request->input('journal'));
                }

                $data = $query->orderBy('date')->orderBy('created_at')->get();

                if ($format == 'fec') {
                    $headers = ['JournalCode', 'JournalLib', 'EcritureNum', 'EcritureDate', 'CompteNum', 'CompteLib', 'CompAuxNum', 'CompAuxLib', 'PieceRef', 'PieceDate', 'EcritureLib', 'Debit', 'Credit', 'EcritureLet', 'DateLet', 'ValidDate', 'Montantdevise', 'Idevise'];
                    $callback = function($file) use ($data) {
                        foreach ($data as $row) {
                            fputcsv($file, [
                                $row->codeJournal->code_journal ?? '',
                                $row->codeJournal->intitule ?? '',
                                $row->id,
                                Carbon::parse($row->date)->format('Ymd'),
                                $row->planComptable->numero_de_compte ?? '',
                                $row->planComptable->intitule ?? '',
                                $row->planTiers->numero_de_tiers ?? '',
                                $row->planTiers->intitule ?? '',
                                $row->reference_piece ?? '',
                                Carbon::parse($row->date)->format('Ymd'),
                                $row->description_operation,
                                number_format($row->debit, 2, ',', ''),
                                number_format($row->credit, 2, ',', ''),
                                '', '',
                                $row->updated_at->format('Ymd'),
                                '', ''
                            ], "\t");
                        }
                    };
                    $filename .= ".txt";
                    $contentType = 'text/plain';
                } elseif ($format == 'sage') {
                    $headers = [];
                    $callback = function($file) use ($data) {
                        foreach ($data as $row) {
                            $line = implode("\t", [
                                Carbon::parse($row->date)->format('dmy'),
                                $row->codeJournal->code_journal ?? '',
                                $row->planComptable->numero_de_compte ?? '',
                                '',
                                $row->reference_piece ?? '',
                                $row->description_operation,
                                $row->debit > 0 ? 'D' : 'C',
                                number_format(max($row->debit, $row->credit), 2, '.', '')
                            ]);
                            fwrite($file, $line . "\r\n");
                        }
                    };
                    $filename .= ".txt";
                    $contentType = 'text/plain';
                } else {
                    $headers = ['Journal', 'Date', 'Compte', 'Tier', 'Piece', 'Libelle', 'Debit', 'Credit'];
                    $callback = function($file) use ($data) {
                        foreach ($data as $row) {
                            fputcsv($file, [
                                $row->codeJournal->code_journal ?? '',
                                $row->date,
                                $row->planComptable->numero_de_compte ?? '',
                                $row->planTiers->numero_de_tiers ?? '',
                                $row->reference_piece ?? '',
                                $row->description_operation,
                                $row->debit,
                                $row->credit
                            ], ';');
                        }
                    };
                }
                break;
        }

        $extension = ($format == 'excel' || $format == 'csv') ? '.csv' : '';
        if (!empty($extension)) {
            $filename .= $extension;
        }

        $headers_res = [
            "Content-type"        => $contentType ?? 'text/csv',
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        return new StreamedResponse(function() use ($headers, $callback, $format) {
            $file = fopen('php://output', 'w');

            if ($format != 'sage' && $format != 'fec') {
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            }

            if (!empty($headers)) {
                $separator = ($format == 'fec') ? "\t" : ';';
                fputcsv($file, $headers, $separator);
            }

            $callback($file);
            fclose($file);
        }, 200, $headers_res);
    }
    /**
     * Génère un numéro de saisie global séquentiel au format ECR_000000000001
     * (Copié de EcritureComptableController pour éviter les dépendances circulaires)
     */
    /**
     * Uniformisation automatique des comptes (Standardisation)
     */
    public function standardizeImportAccounts($id)
    {
        $import = ImportStaging::findOrFail($id);
        $user = Auth::user();
        $accountDigits = $user->company->account_digits ?? 8;

        $mapping = $import->mapping;
        $rawData = $import->raw_data;
        $modified = false;

        // On identifie les colonnes de comptes selon le type
        $accountCols = [];
        if ($import->type == 'initial') {
            $accountCols = [$mapping['numero_de_compte'] ?? null];
        } elseif ($import->type == 'tiers') {
            $accountCols = [$mapping['compte_general'] ?? null];
        } elseif ($import->type == 'journals') {
            $accountCols = [$mapping['compte_de_tresorerie'] ?? null];
        } elseif ($import->type == 'courant') {
            $accountCols = [$mapping['compte'] ?? null];
        }

        $accountCols = array_filter($accountCols, fn($c) => $c !== null && $c !== "" && $c !== "AUTO");

        if (empty($accountCols)) {
            return response()->json(['success' => false, 'message' => 'Aucune colonne de compte à uniformiser trouvée.']);
        }

        $existingAccounts = PlanComptable::where('company_id', session('current_company_id', $user->company_id))
            ->pluck('numero_de_compte')
            ->toArray();

        foreach ($rawData as $idx => &$row) {
            if ($idx <= ($mapping['_header_index'] ?? 0)) continue;

            foreach ($accountCols as $col) {
                $val = trim($row[$col] ?? '');
                if (empty($val)) continue;

                // 1. Padding avec zéros
                if (is_numeric($val) && strlen($val) < $accountDigits) {
                    $newVal = str_pad($val, $accountDigits, '0', STR_PAD_RIGHT);
                    $row[$col] = $newVal;
                    $modified = true;
                    $val = $newVal;
                }

                // 2. Matching intelligent si le compte n'existe pas
                if (!in_array($val, $existingAccounts)) {
                    for ($len = strlen($val) - 1; $len >= 2; $len--) {
                        $search = substr($val, 0, $len);
                        $match = PlanComptable::where('company_id', session('current_company_id', $user->company_id))
                            ->where('numero_de_compte', 'LIKE', $search . '%')
                            ->orderBy('numero_de_compte')
                            ->first();

                        if ($match) {
                            $row[$col] = $match->numero_de_compte;
                            $modified = true;
                            break;
                        }
                    }
                }
            }
        }

        if ($modified) {
            $import->update(['raw_data' => $rawData]);
            return response()->json(['success' => true, 'message' => 'Uniformisation terminée avec succès.']);
        }

        return response()->json(['success' => true, 'message' => 'Aucun compte n\'a eu besoin d\'être uniformisé.']);
    }

    private function standardizeAccountNumber($number, $digits)
    {
        $number = trim($number ?? '');
        if (empty($number)) {
            return $number;
        }

        // Si le numéro contient du texte (ex: "Associés 1401000"), on extrait uniquement les chiffres
        if (!is_numeric($number)) {
            if (preg_match('/\d+/', $number, $matches)) {
                $number = $matches[0];
            } else {
                return $number; // On laisse tel quel si vraiment pas de chiffres
            }
        }

        if (strlen($number) < $digits) {
            return str_pad($number, $digits, '0', STR_PAD_RIGHT);
        } elseif (strlen($number) > $digits) {
            return substr($number, 0, $digits);
        }

        return $number;
    }

    /**
     * Standardise un code journal sur la longueur configurée
     */
    private function standardizeJournalCode($code, $digits)
    {
        $code = strtoupper(trim($code ?? ''));
        if (empty($code)) {
            return $code;
        }

        // Cas Alphanumérique : "BQ1" -> "BQ01"
        // On isole la partie alphabétique et la partie numérique
        if (preg_match('/^([A-Z]+)(\d+)$/i', $code, $matches)) {
            $prefix = $matches[1];
            $number = $matches[2];
            $totalLen = strlen($prefix) + strlen($number);
            if ($totalLen >= $digits) {
                // Le code est déjà à la bonne longueur ou trop long : on tronque si nécessaire
                if ($totalLen > $digits) {
                    $maxPrefixLen = max(1, $digits - strlen($number));
                    $prefix = substr($prefix, 0, $maxPrefixLen);
                }
                return $prefix . $number;
            }
            $availableSpace = max(1, $digits - strlen($prefix));
            // On complète avec des zéros à GAUCHE du numéro
            return $prefix . str_pad($number, $availableSpace, '0', STR_PAD_LEFT);
        }

        // Cas Purement Alphabétique (ex: BQ, CAIS, VEN)
        // Si le code est plus court que $digits, on complète avec "01" ou "001" etc.
        if (strlen($code) < $digits) {
            $numPart = str_pad('1', $digits - strlen($code), '0', STR_PAD_LEFT);
            return $code . $numPart;
        }

        // Code trop long : on tronque
        if (strlen($code) > $digits) {
            return substr($code, 0, $digits);
        }

        return $code;
    }

    private function generateGlobalSaisieNumber($companyId)
    {
        // On cherche le dernier numéro dans la table réelle
        $lastRealSaisie = \App\Models\EcritureComptable::where('company_id', $companyId)
            ->where('n_saisie', 'like', 'ECR_%')
            ->max(DB::raw('CAST(SUBSTRING(n_saisie, 5) AS UNSIGNED)')) ?? 0;

        // On cherche aussi dans les approbations en attente qui pourraient avoir un numéro ECR_
        $lastApprovalSaisie = \App\Models\Approval::whereHasMorph('approvable', [\App\Models\EcritureComptable::class], function($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })
            ->where('data->n_saisie', 'like', 'ECR_%')
            ->get()
            ->map(function($a) {
                return (int) substr($a->data['n_saisie'], 4);
            })
            ->max() ?? 0;

        $maxNumeric = max($lastRealSaisie, $lastApprovalSaisie);

        return 'ECR_' . str_pad($maxNumeric + 1, 12, '0', STR_PAD_LEFT);
    }

    private function generateRanSaisieNumber($companyId)
    {
        $company = \App\Models\Company::find($companyId);
        $targetLength = $company->journal_code_digits ?? 4; 
        
        $lastRealSaisie = \App\Models\EcritureComptable::where('company_id', $companyId)
            ->where('n_saisie', 'like', 'RAN%')
            ->get(['n_saisie'])
            ->map(function($e) {
                return (int) substr($e->n_saisie, 3);
            })
            ->max() ?? 0;
            
        $next = $lastRealSaisie + 1;
        $numLength = max(1, $targetLength - 3);
        
        return 'RAN' . str_pad($next, $numLength, '0', STR_PAD_LEFT);
    }

    /**
     * Gestion des Postes de Trésorerie (Admin)
     */
    public function tresoreriePosts()
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $mainCompany = Company::findOrFail($companyId);

        $postesTresorerie = \App\Models\CompteTresorerie::where('company_id', $companyId)
            ->with('category')
            ->orderBy('name')
            ->get();

        $tftRequired = [
            'I. Flux de trésorerie des activités opérationnelles',
            'II. Flux de trésorerie des activités d\'investissement',
            'III. Flux de trésorerie des activités de financement',
        ];

        $categories = \App\Models\TreasuryCategory::where('company_id', $companyId)
            ->whereIn('name', $tftRequired)
            ->orderBy('name')
            ->get();

        // Auto-réparation pour les entreprises existantes
        if ($categories->count() < 3) {
            foreach ($tftRequired as $catName) {
                \App\Models\TreasuryCategory::firstOrCreate([
                    'company_id' => $companyId,
                    'name' => $catName
                ]);
            }
            // Re-fetch après création
            $categories = \App\Models\TreasuryCategory::where('company_id', $companyId)
                ->whereIn('name', $tftRequired)
                ->orderBy('name')
                ->get();
        }

        return view('admin.config.tresorerie_posts', compact('postesTresorerie', 'mainCompany', 'categories'));
    }

    /**
     * Enregistrer un nouveau poste de trésorerie
     */
    public function storeTresoreriePost(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:treasury_categories,id',
            'syscohada_line_id' => 'nullable|string|max:50',
        ]);

        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $exists = \App\Models\CompteTresorerie::where('company_id', $companyId)
            ->where('name', $request->name)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', "Le poste de trésorerie '{$request->name}' existe déjà.");
        }

        \App\Models\CompteTresorerie::create([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'syscohada_line_id' => $request->syscohada_line_id ?? null,
            'company_id' => $companyId,
            'solde_initial' => 0,
            'solde_actuel' => 0,
        ]);

        return redirect()->route('admin.config.tresorerie_posts')->with('success', 'Poste de trésorerie ajouté avec succès.');
    }

    /**
     * Mettre à jour un poste de trésorerie
     */
    public function updateTresoreriePost(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:treasury_categories,id',
            'syscohada_line_id' => 'nullable|string|max:50',
        ]);

        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $poste = \App\Models\CompteTresorerie::where('company_id', $companyId)->findOrFail($id);

        $exists = \App\Models\CompteTresorerie::where('company_id', $companyId)
            ->where('name', $request->name)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', "Un autre poste de trésorerie porte déjà le nom '{$request->name}'.");
        }

        $poste->update([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'syscohada_line_id' => $request->syscohada_line_id ?? null,
        ]);

        return redirect()->route('admin.config.tresorerie_posts')->with('success', 'Poste de trésorerie mis à jour avec succès.');
    }

    /**
     * Supprimer un poste de trésorerie
     */
    public function deleteTresoreriePost($id)
    {
        try {
            $user = Auth::user();
            $companyId = session('current_company_id', $user->company_id);
            $poste = \App\Models\CompteTresorerie::where('company_id', $companyId)->findOrFail($id);

            // Vérifier s'il y a des mouvements ou des écritures liés
            if ($poste->mouvements()->count() > 0 || $poste->ecritures()->count() > 0) {
                return redirect()->back()->with('error', 'Impossible de supprimer ce poste car il contient des mouvements ou des écritures comptables.');
            }

            $poste->delete();
            return redirect()->route('admin.config.tresorerie_posts')->with('success', 'Poste de trésorerie supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }

    /**
     * Helper pour obtenir les lignes validées sans charger la vue complète (pour export et suppression en masse)
     */
    private function getValidatedRows($import)
    {
        // On récupère temporairement le contrôleur pour appeler la logique (ou on la duplique/isole)
        // Étant donné la taille du fichier, je vais extraire la logique de importStaging() plus tard.
        // Pour l'instant on va simuler l'appel à importStaging mais capturer les données.

        // ATTENTION: importStaging() retourne une vue. On va injecter un flag pour qu'il retourne les données.
        // Ou plus simplement, on va copier la logique vitale ici.

        // Je vais implémenter une version qui ne dépend pas de la vue.
        return $this->importStaging($import->id, true); // On ajoute un paramètre 'return_data'
    }

    /**
     * Exportation des erreurs en Excel
     */
    public function exportErrors($id)
    {
        $import = ImportStaging::findOrFail($id);
        $data = $this->importStaging($import->id, true);
        $rowsWithStatus = $data['rowsWithStatus'] ?? [];

        $errorRows = array_filter($rowsWithStatus, fn($r) => ($r['status'] ?? '') === 'error');

        if (empty($errorRows)) {
            return back()->with('error', "Aucune ligne en erreur à exporter.");
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['Index', 'N° Saisie', 'Date', 'Journal', 'Référence', 'Compte', 'Tiers', 'Libellé', 'Débit', 'Crédit', 'Erreurs'];
        $sheet->fromArray($headers, NULL, 'A1');

        $rowNum = 2;
        foreach ($errorRows as $r) {
            $rowData = $r['data'];
            $sheet->setCellValue('A'.$rowNum, $r['index']);
            $sheet->setCellValue('B'.$rowNum, $rowData['n_saisie'] ?? '');
            $sheet->setCellValue('C'.$rowNum, $rowData['jour'] ?? '');
            $sheet->setCellValue('D'.$rowNum, $rowData['journal'] ?? '');
            $sheet->setCellValue('E'.$rowNum, $rowData['reference'] ?? '');
            $sheet->setCellValue('F'.$rowNum, $rowData['compte'] ?? '');
            $sheet->setCellValue('G'.$rowNum, $rowData['tiers'] ?? '');
            $sheet->setCellValue('H'.$rowNum, $rowData['libelle'] ?? '');
            $sheet->setCellValue('I'.$rowNum, $r['debit'] ?? 0);
            $sheet->setCellValue('J'.$rowNum, $r['credit'] ?? 0);
            $sheet->setCellValue('K'.$rowNum, implode(' | ', $r['errors'] ?? []));
            $rowNum++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'erreurs_import_' . $id . '.xlsx';
        $tempPath = storage_path('app/' . $fileName);
        $writer->save($tempPath);

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }


    /**
     * Suppression de TOUTES les erreurs du staging
     */
    public function bulkDeleteErrors($id)
    {
        try {
            $import = ImportStaging::findOrFail($id);
            $data = $this->importStaging($import->id, true);
            $rowsWithStatus = $data['rowsWithStatus'] ?? [];

            $indicesToDelete = [];
            foreach ($rowsWithStatus as $r) {
                if (($r['status'] ?? '') === 'error') {
                    $indicesToDelete[] = $r['index'];
                }
            }

            if (empty($indicesToDelete)) {
                return response()->json(['success' => false, 'message' => "Aucune erreur à supprimer."]);
            }

            $rawData = $import->raw_data;
            rsort($indicesToDelete);

            foreach ($indicesToDelete as $index) {
                array_splice($rawData, (int)$index, 1);
            }

            $import->update(['raw_data' => $rawData]);

            return response()->json(['success' => true, 'message' => count($indicesToDelete) . " erreur(s) supprimée(s)."]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
