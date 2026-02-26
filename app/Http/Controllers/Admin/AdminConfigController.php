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
        $company = Company::findOrFail($user->company_id);
        
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
        $mainCompany = Company::findOrFail($user->company_id);
        $plansComptables = PlanComptable::where('company_id', $user->company_id)
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
        $planTiers = PlanTiers::where('company_id', $user->company_id)
            ->orderBy('numero_de_tiers')
            ->get();
        
        $plansComptables = PlanComptable::where('company_id', $user->company_id)
            ->orderBy('numero_de_compte')
            ->get();

        $mainCompany = Company::findOrFail($user->company_id);
        
        return view('admin.config.plan_tiers', compact('planTiers', 'plansComptables', 'mainCompany'));
    }

    /**
     * Gestion de la Structure des Journaux
     */
    public function journals()
    {
        $user = Auth::user();
        $mainCompany = Company::findOrFail($user->company_id);
        $journals = CodeJournal::with('account')
            ->where('company_id', $user->company_id)
            ->orderBy('code_journal')
            ->get();
        
        // On récupère aussi le plan comptable pour les comptes de trésorerie
        $plansComptables = PlanComptable::where('company_id', $user->company_id)
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
        
        $importedCount = EcritureComptable::where('company_id', $user->company_id)
            ->where('statut', 'imported')
            ->count();

        if ($importedCount === 0) {
            return redirect()->route('admin.config.external_import')
                ->with('info', 'Aucune écriture importée en attente. Veuillez d\'abord importer un fichier.');
        }

        // Passage au statut approved pour toutes les écritures importées de la compagnie
        EcritureComptable::where('company_id', $user->company_id)
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

                $exists = PlanComptable::where('company_id', $user->company_id)
                    ->where('numero_de_compte', $numero)
                    ->exists();

                if (!$exists) {
                    PlanComptable::create([
                        'numero_de_compte' => $numero,
                        'intitule' => mb_strtoupper($intitule),
                        'type_de_compte' => $type,
                        'classe' => $classe,
                        'user_id' => $user->id,
                        'company_id' => $user->company_id,
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
                
                if (!PlanComptable::where('company_id', $user->company_id)->where('numero_de_compte', $numeroBase)->exists()) {
                    PlanComptable::create([
                        'numero_de_compte' => $numeroBase,
                        'intitule' => "CLASSE $classPrefix - $intitule",
                        'user_id' => $user->id,
                        'company_id' => $user->company_id,
                        'adding_strategy' => 'auto'
                    ]);
                    $count++;
                }

                // Si séquentiel, on génère une série
                if ($request->sequential === 'oui') {
                    $prefix = $classPrefix . ($request->seq_prefix ?? '');
                    for ($i = $request->seq_start; $i <= $request->seq_end; $i++) {
                        $numero = str_pad($prefix . $i, $digits, '0', STR_PAD_RIGHT);
                        
                        if (strlen($numero) <= $digits && !PlanComptable::where('company_id', $user->company_id)->where('numero_de_compte', $numero)->exists()) {
                            PlanComptable::create([
                                'numero_de_compte' => $numero,
                                'intitule' => "Compte $numero (Généré)",
                                'user_id' => $user->id,
                                'company_id' => $user->company_id,
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
            PlanComptable::where('company_id', $user->company_id)->delete();
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
            PlanTiers::where('company_id', $user->company_id)->delete();
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
            CodeJournal::where('company_id', $user->company_id)->delete();
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
        $company = Company::findOrFail($user->company_id);
        $digits = $company->account_digits ?? 8;

        // Validation stricte de la longueur
        if (strlen($request->numero_de_compte) != $digits) {
            return redirect()->back()->with('error', "Le numéro de compte doit comporter exactement $digits chiffres.");
        }

        $numero = $request->numero_de_compte;

        $exists = PlanComptable::where('company_id', $user->company_id)
            ->where('numero_de_compte', $numero)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', "Le compte $numero existe déjà dans votre modèle.");
        }

        PlanComptable::create([
            'numero_de_compte' => $numero,
            'intitule' => $request->intitule,
            'user_id' => $user->id,
            'company_id' => $user->company_id,
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
            Excel::import(new \App\Imports\MasterTiersImport, $request->file('file'));
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
            Excel::import(new \App\Imports\MasterJournalImport, $request->file('file'));
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
                $exists = CodeJournal::where('company_id', $user->company_id)
                    ->where('code_journal', $tpl['code'])
                    ->exists();

                if (!$exists) {
                    CodeJournal::create([
                        'code_journal' => $tpl['code'],
                        'intitule' => $tpl['intitule'],
                        'type' => $tpl['type'],
                        'user_id' => $user->id,
                        'company_id' => $user->company_id,
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
            $company = Company::findOrFail($user->company_id);
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

        $existingTiers = PlanTiers::where('company_id', $user->company_id)
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
        $company = Company::findOrFail($user->company_id);
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

        $exists = PlanTiers::where('company_id', $user->company_id)
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
            'company_id' => $user->company_id,
        ]);

        return redirect()->route('admin.config.plan_tiers')->with('success', 'Tiers ajouté au modèle avec succès.');
    }

    /**
     * Enregistrer un nouveau Journal Master
     */
    public function storeJournal(Request $request)
    {
        $user = Auth::user();
        $company = Company::findOrFail($user->company_id);
            $digits = $company->journal_code_digits ?? 4;
        $codeType = $company->journal_code_type ?? 'alphabetical';

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

        // Validation du type de code
        if ($codeType == 'numeric' && !ctype_digit($code)) {
            return redirect()->back()->with('error', "Le code journal doit être uniquement numérique.");
        } elseif ($codeType == 'alphabetical' && !ctype_alpha($code)) {
            return redirect()->back()->with('error', "Le code journal doit être uniquement alphabétique.");
        }

        $exists = CodeJournal::where('company_id', $user->company_id)
            ->where('code_journal', $code)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', "Le code journal {$code} existe déjà.");
        }

        $compteId = null;
        if ($request->compte_de_tresorerie) {
            $compteId = PlanComptable::where('company_id', $user->company_id)
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
            'company_id' => $user->company_id,
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
            $account = PlanComptable::where('company_id', $user->company_id)->findOrFail($id);
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
            $tier = PlanTiers::where('company_id', $user->company_id)->findOrFail($id);
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
            $journal = CodeJournal::where('company_id', $user->company_id)->findOrFail($id);
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
            $company = Company::findOrFail($user->company_id);
            $digits = $company->account_digits ?? 8;

            if (strlen($request->numero_de_compte) != $digits) {
                return redirect()->back()->with('error', "Le numéro de compte doit comporter exactement $digits chiffres.");
            }

            $account = PlanComptable::where('company_id', $user->company_id)->findOrFail($id);
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
            $tier = PlanTiers::where('company_id', $user->company_id)->findOrFail($id);
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
        $company = Company::findOrFail($user->company_id);
        $digits = $company->journal_code_digits ?? 4;
        $codeType = $company->journal_code_type ?? 'alphabetical';

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

        // Validation du type de code
        if ($codeType == 'numeric' && !ctype_digit($code)) {
            return redirect()->back()->with('error', "Le code journal doit être uniquement numérique.");
        } elseif ($codeType == 'alphabetical' && !ctype_alpha($code)) {
            return redirect()->back()->with('error', "Le code journal doit être uniquement alphabétique.");
        }

        // Unicité
        $exists = CodeJournal::where('company_id', $user->company_id)
            ->where('code_journal', $code)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', "Le code journal {$code} existe déjà.");
        }

        try {
            $journal = CodeJournal::where('company_id', $user->company_id)->findOrFail($id);
            
            $posteTresorerie = $request->poste_tresorerie_autre ?: $request->poste_tresorerie;

            $compteId = null;
            if ($request->compte_de_tresorerie) {
                $compteId = PlanComptable::where('company_id', $user->company_id)
                    ->where('numero_de_compte', $request->compte_de_tresorerie)
                    ->value('id');
            }

            $journal->update([
                'code_journal' => $code,
                'intitule' => mb_strtoupper($request->intitule),
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
        $imports = ImportStaging::where('company_id', $user->company_id)
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
            
            file_put_contents(base_path('debug_import.txt'), "[" . date('H:i:s') . "] FILE: " . $file->getClientOriginalName() . " EXT: $extension TYPE: $type\n", FILE_APPEND);
            
            ImportStaging::where('user_id', $user->id)
                ->where('company_id', $user->company_id)
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
                'company_id' => $user->company_id,
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

        // LOGIQUE D'INTELLIGENCE : Détection de la meilleure ligne d'en-tête
        $headers = [];
        $headerIndex = -1; // -1 signifie PAS d'en-tête (on commence à la ligne 0)
        $maxMatches = 0;
        $scanLimit = min(count($import->raw_data), 20);

        for ($i = 0; $i < $scanLimit; $i++) {
            $row = $import->raw_data[$i];
            if (empty($row)) continue;
            
            $currentMatches = 0;
            $matchedFields = [];

            foreach ($row as $cell) {
                if (empty($cell) || is_numeric($cell)) continue;
                
                try {
                     $cleanCell = strtolower(preg_replace('/[^a-z]/', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $cell)));
                } catch (\Exception $e) {
                     $cleanCell = '';
                }
                if (empty($cleanCell) || strlen($cleanCell) < 3) continue;

                foreach ($fields as $fieldKey => $field) {
                    if (in_array($fieldKey, $matchedFields)) continue;
                    
                    foreach ($field['match'] as $m) {
                        try {
                             $cleanM = strtolower(preg_replace('/[^a-z]/', '', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $m)));
                        } catch (\Exception $e) {
                             $cleanM = ''; // Should not happen for hardcoded matches but safe
                        }
                        if ($cleanCell === $cleanM || str_contains($cleanCell, $cleanM)) {
                            $currentMatches++;
                            $matchedFields[] = $fieldKey;
                            break 2;
                        }
                    }
                }
            }

            // Seuil de détection : on a besoin d'au moins 1 match solide ou 2 partiels
            if ($currentMatches >= 1 && $currentMatches > $maxMatches) {
                $maxMatches = $currentMatches;
                $headerIndex = $i;
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
        $dataSamples = array_slice($import->raw_data, $headerIndex + 1, 20);
        
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
                }

                if ($score > $bestScore && $score > 35) {
                    $bestScore = $score;
                    $field['suggested_col'] = $colIdx;
                }
            }
        }

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
        return view($viewName, compact('import', 'headers', 'fields', 'importTitle', 'user'));
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
    public function importStaging($id)
    {
        $import = ImportStaging::findOrFail($id);
        $user = Auth::user();
        $accountDigits = $user->company->account_digits ?? 8;
        $tierDigits = $user->company->tier_digits ?? 8;
        $journalDigits = $user->company->journal_code_digits ?? 4;
        
        $mapping = $import->mapping;
        
        $headerIndex = $mapping['_header_index'] ?? 0;
        
        // Nettoyage des données : Ignorer les lignes avant les titres et les lignes totalement vides basées sur les colonnes mappées
        $data = array_filter(array_slice($import->raw_data, $headerIndex + 1, null, true), function($row) use ($mapping) {
            $hasData = false;
            foreach ($mapping as $field => $index) {
                if ($field !== '_header_index' && $index !== null && $index !== "" && !empty(trim($row[$index] ?? ''))) {
                    $hasData = true;
                    break;
                }
            }
            return $hasData;
        });

        $existingAccounts = PlanComptable::where('company_id', $user->company_id)
            ->pluck('numero_de_compte')
            ->toArray();
        
        $existingJournals = CodeJournal::where('company_id', $user->company_id)
            ->pluck('code_journal')
            ->toArray();

        // --- DICTIONNAIRES DE CORRESPONDANCE (AUTO-LOOKUP) ---
        $accountMapping = [];
        PlanComptable::where('company_id', $user->company_id)
            ->whereNotNull('numero_original')
            ->where('numero_original', '!=', '')
            ->select('numero_de_compte', 'numero_original')
            ->chunk(100, function($accounts) use (&$accountMapping) {
                foreach($accounts as $acc) {
                    $accountMapping[strtoupper(trim($acc->numero_original))] = trim($acc->numero_de_compte);
                }
            });
            
        $journalMapping = [];
        CodeJournal::where('company_id', $user->company_id)
            ->whereNotNull('numero_original')
            ->where('numero_original', '!=', '')
            ->select('code_journal', 'numero_original')
            ->chunk(100, function($journals) use (&$journalMapping) {
                foreach($journals as $jnl) {
                    $journalMapping[strtoupper(trim($jnl->numero_original))] = trim($jnl->code_journal);
                }
            });

        $tierMapping = [];
        PlanTiers::where('company_id', $user->company_id)
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

        $maxMappingIndex = 0;
        foreach ($mapping as $mIdx) {
            if (is_numeric($mIdx)) {
                $maxMappingIndex = max($maxMappingIndex, (int)$mIdx);
            }
        }

        $exercice = ExerciceComptable::find($import->exercice_id);

        $rowsWithStatus = [];
        $batchAccounts = []; // [ 'numero_standard' => 'numero_original' ]
        $errorCount = 0;
        $validCount = 0;

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
                if (is_string($cId) && str_starts_with($cId, 'FIXED:')) {
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

                // GESTION DES DOUBLONS ET COLLISIONS PAR NUMÉROTATION SÉQUENTIELLE
                if (!empty($rowCompte) && empty($errors)) {
                    if (in_array($rowCompte, $existingAccounts) || isset($batchAccounts[$rowCompte])) {
                        $racine = substr($rowCompte, 0, 3);
                        
                        if (!isset($localMaxAccounts[$racine])) {
                            // Chercher le plus grand numéro existant pour cette racine
                            $maxInDb = \App\Models\PlanComptable::where('company_id', $user->company_id)
                                ->where('numero_de_compte', 'LIKE', $racine . '%')
                                ->whereRaw('LENGTH(numero_de_compte) = ?', [$accountDigits])
                                ->max('numero_de_compte');
                            
                            $localMaxAccounts[$racine] = $maxInDb ?: ($racine . str_pad('0', ($accountDigits - 3), '0', STR_PAD_LEFT));
                        }
                        
                        // Incrémenter la séquence
                        $lastId = $localMaxAccounts[$racine];
                        $sequencePart = substr($lastId, 3);
                        $nextSeq = (int)$sequencePart + 1;
                        $newId = $racine . str_pad($nextSeq, ($accountDigits - 3), '0', STR_PAD_LEFT);
                        
                        $row['numero_de_compte'] = $newId;
                        $row['suggested_account'] = $newId;
                        $rowCompte = $newId;
                        $localMaxAccounts[$racine] = $newId;
                    }
                    
                    $batchAccounts[$rowCompte] = $originalRawValue;
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
                
                // Si une valeur manuelle existe (suite à une édition utilisateur), elle gagne TOUJOURS
                if (!empty($manualType)) {
                    $row['type'] = $manualType;
                } else {
                    // Sinon, on lance la détection automatique
                    // PRIORITÉ 1 : Présence d'un compte de trésorerie (Classe 5)
                    $rowCompteTreso = $this->standardizeAccountNumber(trim($row['compte_de_tresorerie'] ?? ''), $accountDigits);
                    $row['compte_de_tresorerie'] = $rowCompteTreso;

                    if (!empty($rowCompteTreso)) {
                         // Si un compte est spécifié, on vérifie s'il ressemble à un compte de trésorerie
                         if (str_starts_with($rowCompteTreso, '5')) {
                             $detectedType = 'Trésorerie';
                         }
                    }

                    // PRIORITÉ 2 : Analyse sémantique si pas de compte
                    if (!$detectedType) {
                        $searchStr = strtoupper($rowCode . ' ' . ($row['intitule'] ?? ''));
                        if (Str::contains($searchStr, ['ACH', 'FOURN', 'FRN'])) $detectedType = 'Achats';
                        elseif (Str::contains($searchStr, ['VEN', 'CLT', 'CLI'])) $detectedType = 'Ventes';
                        elseif (Str::contains($searchStr, ['BQ', 'BNQ', 'BANK', 'SG', 'ECO', 'BOA', 'UBA', 'TRES', 'TRZ', 'BANKING', 'CAI', 'CASH', 'BANQUE', 'CAISSE'])) {
                            $detectedType = 'Trésorerie';
                            // Détection automatique du poste de trésorerie
                            if (Str::contains($searchStr, ['CAI', 'CASH', 'CAISSE'])) {
                                $row['poste_tresorerie'] = 'Caisse';
                            } else {
                                $row['poste_tresorerie'] = 'Banque';
                            }
                        }
                        elseif (Str::contains($searchStr, ['OD', 'DIV', 'VAR'])) $detectedType = 'Standard';
                    }

                    // Assignation du type détecté ou par défaut
                    if (empty($row['type']) || $mapping['type'] === 'AUTO') {
                        $row['type'] = $detectedType ?? 'Standard';
                    }
                }

                // Surcharges de trésorerie
                if (in_array($row['type'], ['Trésorerie', 'Banque', 'Caisse'])) {
                    if (!empty($manualPoste)) $row['poste_tresorerie'] = $manualPoste;
                    if (!empty($manualCompte)) $row['compte_de_tresorerie'] = $manualCompte;
                    if (!empty($manualAnalytique)) $row['traitement_analytique'] = $manualAnalytique;
                    if (!empty($manualRapprochement)) $row['rapprochement_sur'] = $manualRapprochement;
                }
                
                // Stockage du numéro original (si pas surchargé manuellement, c'est la détection ou le mappé)
                $row['numero_original'] = $rowCode;
                
                // On injecte tous les index dans data pour que le JS puisse les utiliser
                $row['type_override_index'] = $typeOverrideIndex;
                $row['poste_override_index'] = $posteTresoOverrideIndex;
                $row['compte_override_index'] = $compteTresoOverrideIndex;
                $row['analytique_override_index'] = $analytiqueOverrideIndex;
                $row['rapprochement_override_index'] = $rapprochementOverrideIndex;
                $row['code_journal_override_index'] = $codeJournalOverrideIndex;

                // --- GÉNÉRATION SÉQUENTIELLE DU CODE JOURNAL ---
                if (!empty($manualCodeOrig)) {
                     $row['code_journal'] = $manualCodeOrig;
                } elseif (empty($rowCode) || (isset($mapping['code_journal']) && $mapping['code_journal'] === 'AUTO')) {
                    $prefix = 'JRN';
                    $typeLower = mb_strtolower($row['type'] ?? '');
                    if (str_contains($typeLower, 'achat')) $prefix = 'ACH';
                    elseif (str_contains($typeLower, 'vente')) $prefix = 'VEN';
                    elseif (str_contains($typeLower, 'trésorerie') || str_contains($typeLower, 'banque') || str_contains($typeLower, 'caisse')) {
                        $prefix = (isset($row['poste_tresorerie']) && $row['poste_tresorerie'] === 'Caisse') ? 'CAI' : 'BQ';
                    }
                    elseif (str_contains($typeLower, 'opération') || str_contains($typeLower, 'diverse')) $prefix = 'OD';
                    elseif (str_contains($typeLower, 'standard')) $prefix = 'STD';

                    if (!isset($localMaxJournals[$prefix])) {
                        $resp = app(\App\Http\Controllers\CodeJournalController::class)->getNextSequentialCode(new Request(['prefix' => $prefix]));
                        $genData = $resp->getData();
                        if ($genData->success) {
                            $row['code_journal'] = $genData->code;
                            $localMaxJournals[$prefix] = $genData->code;
                        } else {
                            $row['code_journal'] = $prefix . '1';
                        }
                    } else {
                        $lastCode = $localMaxJournals[$prefix];
                        $suffix = substr($lastCode, strlen($prefix));
                        if (is_numeric($suffix)) {
                            $nextNum = (int)$suffix + 1;
                            $availableSpace = max(1, $journalDigits - strlen($prefix));
                            $newCode = $prefix . str_pad((string)$nextNum, $availableSpace, '0', STR_PAD_LEFT);
                            if (strlen($newCode) > $journalDigits && strlen($prefix) < $journalDigits) {
                                $newCode = substr($newCode, 0, $journalDigits);
                            }
                            $row['code_journal'] = $newCode;
                            $localMaxJournals[$prefix] = $newCode;
                        } else {
                            $row['code_journal'] = $prefix . '1';
                        }
                    }
                    $rowCode = $row['code_journal'];
                }


                if (empty($rowCode) && !($mapping['code_journal'] === 'AUTO')) {
                    $errors[] = "Code journal manquant";
                } elseif (strlen($rowCode) > 10) {
                    $errors[] = "Code '$rowCode' invalide : Max 10 caractères.";
                } elseif (in_array(strtoupper($rowCode), array_map('strtoupper', $existingJournals))) {
                    $errors[] = "Doublon : Le code journal '$rowCode' existe déjà.";
                }

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
                    '47' => 'Divers Tiers',
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
                    // Fournisseurs : F, F-, FOU, FOUR, FRN, FRS, FR, FR-
                    if (preg_match('/^(FOU|FOUR|FRN|FRS|FR-|F-|F\d|FR\d)/', $upperNum)) {
                        $generationPrefix = '40';
                        $prefix = '40';
                    } 
                    // Clients : C, C-, CLI, CLT, CL, CL-
                    elseif (preg_match('/^(CLI|CLT|CL-|C-|C\d|CL\d)/', $upperNum)) {
                        $generationPrefix = '41';
                        $prefix = '41';
                    }
                    // Personnel : P, P-, PER, SAL
                    elseif (preg_match('/^(PERS|PER|SAL|P-|P\d)/', $upperNum)) {
                        $generationPrefix = '42';
                        $prefix = '42';
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
                            $match = PlanComptable::where('company_id', $user->company_id)
                                ->where('numero_de_compte', 'LIKE', $searchVal . '%')
                                ->orderBy('numero_de_compte')
                                ->first();
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
                    $row['numero_de_tiers'] = null; // Reset pour éviter de garder l'ancien si la génération échoue

                    if (!empty($rowCompte)) {
                        // Correspondance automatique pour le compte collectif
                        if (!in_array($rowCompte, $existingAccounts) && isset($accountMapping[$rowCompte])) {
                            $row['numero_original_compte'] = $rowCompte;
                            $row['compte_general'] = $accountMapping[$rowCompte];
                            $rowCompte = $row['compte_general'];
                        }

                        $prefix = substr($rowCompte, 0, 2);
                        
                        // On cherche le compte spécifique
                        $planAcc = PlanComptable::where('company_id', $user->company_id)
                            ->where('numero_de_compte', $rowCompte)
                            ->first();

                        if ($planAcc) {
                            // Logique de génération avec mémoire locale pour éviter les doublons dans le staging
                            // PRIORITÉ : Variable $generationPrefix (Importé) > $prefix (Compte)
                            $finalPrefix = $generationPrefix ?? ($prefix ?? ($planAcc ? substr($planAcc->numero_de_compte, 0, 2) : '40'));

                            if (!isset($localMaxTiers[$finalPrefix])) {
                                $resp = $this->getNextTierNumber(new Request([
                                    'plan_comptable_id' => $planAcc->id,
                                    'prefix' => $finalPrefix,
                                    'intitule' => $row['intitule'] ?? ''
                                ]));
                                $genData = $resp->getData();
                                if ($genData->success) {
                                    $row['numero_de_tiers'] = $genData->next_id;
                                    // On stocke le dernier numéro généré pour ce préfixe
                                    $localMaxTiers[$finalPrefix] = $genData->next_id;
                                } else {
                                    $errors[] = "Erreur de génération : " . ($genData->message ?? 'Inconnue');
                                }
                            } else {
                                // On repart du dernier numéro généré localement pour ce préfixe
                                $lastId = $localMaxTiers[$finalPrefix];
                                $prefixLen = strlen($finalPrefix);
                                $sequencePart = substr($lastId, $prefixLen);
                                
                                if (is_numeric($sequencePart)) {
                                    $nextSeq = (int)$sequencePart + 1;
                                    $availableSpace = $tierDigits - $prefixLen;
                                    $newId = $finalPrefix . str_pad($nextSeq, $availableSpace, '0', STR_PAD_LEFT);
                                    
                                    $row['numero_de_tiers'] = $newId;
                                    $localMaxTiers[$finalPrefix] = $newId;
                                } else {
                                    // Cas alphanumérique ou erreur de format
                                    $resp = $this->getNextTierNumber(new Request([
                                        'plan_comptable_id' => $planAcc->id,
                                        'prefix' => $finalPrefix,
                                        'intitule' => $row['intitule'] ?? ''
                                    ]));
                                    $genData = $resp->getData();
                                    if ($genData->success) {
                                        $row['numero_de_tiers'] = $genData->next_id;
                                        $localMaxTiers[$finalPrefix] = $genData->next_id;
                                    }
                                }
                            }
                        } else {
                            $errors[] = "Le compte collectif $rowCompte n'existe pas. Veuillez le créer au préalable.";
                            $row['is_virtual'] = true;
                        }
                    } else {
                        $errors[] = "Compte collectif absent ou impossible à déterminer.";
                        $row['is_virtual'] = true;
                    }
                }

                // GÉNÉRATION DE SECOURS (Si le compte est absent ou inexistant)
                if (empty($row['numero_de_tiers'])) {
                    $finalPrefix = $generationPrefix ?? '40';
                    $row['is_virtual'] = true;
                    
                    if (!isset($localMaxTiers[$finalPrefix])) {
                        $resp = $this->getNextTierNumber(new Request([
                            'plan_comptable_id' => null,
                            'prefix' => $finalPrefix,
                            'intitule' => $row['intitule'] ?? ''
                        ]));
                        $genData = $resp->getData();
                        if ($genData->success) {
                            $row['numero_de_tiers'] = $genData->next_id;
                            $localMaxTiers[$finalPrefix] = $genData->next_id;
                        }
                    } else {
                        $lastId = $localMaxTiers[$finalPrefix];
                        $prefixLen = strlen($finalPrefix);
                        $sequencePart = substr($lastId, $prefixLen);
                        
                        if (is_numeric($sequencePart)) {
                            $nextSeq = (int)$sequencePart + 1;
                            $availableSpace = max(1, $tierDigits - $prefixLen);
                            $newId = $finalPrefix . str_pad($nextSeq, $availableSpace, '0', STR_PAD_LEFT);
                            $row['numero_de_tiers'] = $newId;
                            $localMaxTiers[$finalPrefix] = $newId;
                        } else {
                             // Cas alphanumérique fallback
                             $resp = $this->getNextTierNumber(new Request([
                                 'plan_comptable_id' => null,
                                 'prefix' => $finalPrefix,
                                 'intitule' => $row['intitule'] ?? ''
                             ]));
                             $genData = $resp->getData();
                             if ($genData->success) {
                                 $row['numero_de_tiers'] = $genData->next_id;
                                 $localMaxTiers[$finalPrefix] = $genData->next_id;
                             }
                        }
                    }
                    
                    if (empty($row['numero_de_tiers'])) {
                        $errors[] = "Numéro de tiers impossible à générer avec le préfixe $finalPrefix.";
                    }
                }
                
                // Normalisation finale du type de tiers pour l'affichage
                if (!empty($rowType)) {
                    $row['type_de_tiers'] = $rowType;
                }
            } else {
                // Validation pour Écritures
                // Validation pour Écritures
                $rowCompte = trim($row['compte'] ?? '');
                $rowJournal = trim($row['journal'] ?? '');
                $rowTiers = trim($row['tiers'] ?? '');

                // 0. Filtrage Analytiques (Type A) : ignorées et exclues des contrôles
                $typeEcriture = strtoupper(trim((string)($row['type_ecriture'] ?? '')));
                if ($typeEcriture === 'A' || $typeEcriture === 'ANALYTIQUE') {
                    $rowDebit = (float)str_replace(',', '.', preg_replace('/[^0-9,.]/', '', $row['debit'] ?? 0));
                    $rowCredit = (float)str_replace(',', '.', preg_replace('/[^0-9,.]/', '', $row['credit'] ?? 0));
                    $row['debit_val'] = $rowDebit;
                    $row['credit_val'] = $rowCredit;
                    $errors = ["Ignorée (analytique - type A)"];
                    $errors = array_values(array_filter($errors, fn($e) => is_string($e) ? trim($e) !== '' : !empty($e)));

                    $rowsWithStatus[] = [
                        'index' => $index,
                        'data' => $row,
                        'status' => 'ignored',
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
                    }
                }

                // 2. Compte Général
                if (!empty($rowCompte)) {
                    $rowCompteNormalized = strtoupper(trim((string)$rowCompte));
                    if (isset($accountMapping[$rowCompteNormalized])) {
                        $row['numero_original_compte'] = $rowCompte;
                        $row['compte'] = $accountMapping[$rowCompteNormalized];
                        $rowCompte = $row['compte'];
                    }
                }

                // 3. Tiers
                // 3. Tiers
                if (!empty($rowTiers)) {
                    $rowTiersUpper = strtoupper(trim($rowTiers));
                    if (isset($tierMapping[$rowTiersUpper])) {
                        $row['numero_original_tiers'] = $rowTiers;
                        $row['tiers'] = $tierMapping[$rowTiersUpper];
                        $rowTiers = $row['tiers'];
                    }
                }

                $rowDebit = (float)str_replace(',', '.', preg_replace('/[^0-9,.]/', '', $row['debit'] ?? 0));
                $rowCredit = (float)str_replace(',', '.', preg_replace('/[^0-9,.]/', '', $row['credit'] ?? 0));
                $rowDateStr = trim($row['jour'] ?? '');

                // 1. Validation de l'existence minimale
                if (empty($rowCompte)) {
                    $errors[] = "Compte manquant";
                } elseif (!in_array($rowCompte, $existingAccounts)) {
                    $errors[] = "Compte inconnu : $rowCompte";
                }

                if (empty($rowJournal)) {
                    $errors[] = "Journal manquant";
                } elseif (!in_array(strtoupper($rowJournal), array_map('strtoupper', $existingJournals))) {
                    $errors[] = "Journal inconnu : $rowJournal";
                }

                if (array_key_exists('n_saisie', $mapping) && $mapping['n_saisie'] !== null && $mapping['n_saisie'] !== '' && $mapping['n_saisie'] !== 'AUTO') {
                    $ns = trim((string)($row['n_saisie'] ?? ''));
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
                            $formats = ['d/m/Y', 'j/n/Y', 'd/n/Y', 'j/m/Y', 'Y-m-d', 'd-m-Y', 'Y/m/d'];
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

                // Validation Tiers obligatoire pour 401/411
                $rowTiers = trim($row['tiers'] ?? '');
                if (empty($rowTiers) && Str::startsWith($rowCompte, ['401', '402', '411', '412'])) {
                    $errors[] = "Un numéro de tiers est obligatoire pour le compte '$rowCompte'.";
                }
                
                if (!empty($rowTiers)) {
                    $tierExists = PlanTiers::where('company_id', $user->company_id)
                        ->where('numero_de_tiers', $rowTiers)
                        ->exists();
                    if (!$tierExists) {
                        $originalPart = !empty($row['numero_original_tiers']) ? " (L'original '{$row['numero_original_tiers']}' n'a pas pu être rattaché)" : "";
                        $errors[] = "Tiers inconnu : $rowTiers$originalPart";
                    }
                }
                
                $row['debit_val'] = $rowDebit;
                $row['credit_val'] = $rowCredit;
            }

            $errors = array_values(array_filter($errors, function ($e) {
                return is_string($e) ? trim($e) !== '' : !empty($e);
            }));

            $status = (count($errors) > 0) ? 'error' : 'valid';
            if ($status === 'error' && count($errors) === 0) {
                $errors = ["Erreur de validation inconnue"]; 
            }
            if ($status == 'error') $errorCount++; else $validCount++;

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
            $nSaisieMapped = isset($mapping['n_saisie']) && $mapping['n_saisie'] !== null && $mapping['n_saisie'] !== '' && $mapping['n_saisie'] !== 'AUTO';
            $balances = [];
            foreach ($rowsWithStatus as &$r) {
                if ($r['status'] === 'ignored') {
                    continue;
                }

                // Groupement: si n_saisie est mappé, on groupe STRICTEMENT par n_saisie (clé d'écriture)
                // et on ignore les lignes sans n_saisie (elles sont déjà en erreur explicite).
                if ($nSaisieMapped) {
                    $ref = trim((string)($r['data']['n_saisie'] ?? ''));
                    if ($ref === '') {
                        continue;
                    }
                } else {
                    $ref = trim((string)($r['data']['jour'] ?? ''))
                        . '|' . trim((string)($r['data']['journal'] ?? ''))
                        . '|' . trim((string)($r['data']['reference'] ?? ''));
                }

                if (true) { // On track quand même pour info
                    if (!isset($balances[$ref])) $balances[$ref] = ['d' => 0, 'c' => 0, 'rows' => []];
                    $balances[$ref]['d'] += round((float)$r['debit'], 2);
                    $balances[$ref]['c'] += round((float)$r['credit'], 2);
                    $balances[$ref]['rows'][] = &$r;
                }
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
                if ($r['status'] === 'ignored') {
                    continue;
                }

                if ($nSaisieMapped) {
                    $ref = trim((string)($r['data']['n_saisie'] ?? ''));
                    if ($ref === '') {
                        continue;
                    }
                } else {
                    $ref = trim((string)($r['data']['jour'] ?? ''))
                        . '|' . trim((string)($r['data']['journal'] ?? ''))
                        . '|' . trim((string)($r['data']['reference'] ?? ''));
                }

                $r['group_key'] = $ref;
                $r['group_debit'] = $groupSummary[$ref]['debit'] ?? null;
                $r['group_credit'] = $groupSummary[$ref]['credit'] ?? null;
                $r['group_diff'] = $groupSummary[$ref]['diff'] ?? null;
            }

            $totalDebit = array_sum(array_map(fn($r) => ($r['status'] ?? null) === 'ignored' ? 0 : (float)($r['debit'] ?? 0), $rowsWithStatus));
            $totalCredit = array_sum(array_map(fn($r) => ($r['status'] ?? null) === 'ignored' ? 0 : (float)($r['credit'] ?? 0), $rowsWithStatus));
            
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
            if (($r['status'] ?? null) === 'error') {
                $errorCount++;
            } elseif (($r['status'] ?? null) === 'valid') {
                $validCount++;
            }
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

        return view($viewName, compact('import', 'rowsWithStatus', 'errorCount', 'validCount', 'importTitle', 'user', 'plansComptables', 'accountDigits'));
    }

    /**
     * Tunnel d'Importation - Injection Finale
     */
    public function commitImport(Request $request, $id)
    {
        $import = ImportStaging::findOrFail($id);
        $user = Auth::user();
        
        $mapping = $import->mapping;
        
        $headerIndex = $mapping['_header_index'] ?? 0;

        // Nettoyage des données : Ignorer les lignes avant les titres et les lignes totalement vides basées sur les colonnes mappées
        $data = array_filter(array_slice($import->raw_data, $headerIndex + 1, null, true), function($row) use ($mapping) {
            $hasData = false;
            foreach ($mapping as $field => $index) {
                if ($field !== '_header_index' && $index !== null && $index !== "" && !empty(trim($row[$index] ?? ''))) {
                    $hasData = true;
                    break;
                }
            }
            return $hasData;
        });

        $exercice = ExerciceComptable::find($import->exercice_id);
        $accountDigits = $user->company->account_digits ?? 8;
        $journalLimit = 10;
        
        $groupBalances = []; // Pour l'équilibre des écritures
        
        // REPORT STATISTICS
        $report = [
            'status' => 'success',
            'processed_g' => 0,
            'filtered_a' => 0,
            'deduplicated' => 0,
            'total_debit' => 0,
            'total_credit' => 0,
            'new_accounts' => 0,
            'new_tiers' => 0,
            'errors' => []
        ];
        
        $importedCount = 0;
        $duplicateCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            $planComptableIds = PlanComptable::where('company_id', $user->company_id)->pluck('id', 'numero_de_compte')->toArray();
            $planComptableIds = array_change_key_case($planComptableIds, CASE_UPPER);
            $existingAccounts = array_keys($planComptableIds);

            $planComptableOriginalIds = PlanComptable::where('company_id', $user->company_id)
                ->whereNotNull('numero_original')
                ->where('numero_original', '!=', '')
                ->pluck('id', 'numero_original')->toArray();
            $planComptableOriginalIds = array_change_key_case($planComptableOriginalIds, CASE_UPPER);

            $planTiersIds = PlanTiers::where('company_id', $user->company_id)->pluck('id', 'numero_de_tiers')->toArray();
            $planTiersIds = array_change_key_case($planTiersIds, CASE_UPPER);

            $planTiersOriginalIds = PlanTiers::where('company_id', $user->company_id)
                ->whereNotNull('numero_original')
                ->where('numero_original', '!=', '')
                ->pluck('id', 'numero_original')->toArray();
            $planTiersOriginalIds = array_change_key_case($planTiersOriginalIds, CASE_UPPER);

            $existingJournalsCount = CodeJournal::where('company_id', $user->company_id)->count();
            $existingJournals = CodeJournal::where('company_id', $user->company_id)->pluck('id', 'code_journal')->toArray();
            $existingJournals = array_change_key_case($existingJournals, CASE_UPPER);

            $existingJournalsOriginal = CodeJournal::where('company_id', $user->company_id)
                ->whereNotNull('numero_original')
                ->where('numero_original', '!=', '')
                ->pluck('id', 'numero_original')->toArray();
            $existingJournalsOriginal = array_change_key_case($existingJournalsOriginal, CASE_UPPER);

            // Pour les écritures, on va grouper par référence pour donner le même numéro ECR_ au besoin
            // Ou plus simplement, un ECR_ par ligne si elles sont indépendantes. 
            // Mais généralement, l'import regroupe par "n_saisie" d'origine.
            $ecrMapping = []; 
            $localMaxTiers = [];
            $localMaxJournals = [];
            $journalDigits = $user->company->journal_code_digits ?? 4;
            $localMaxAccounts = [];
            $batchAccounts = [];

            // DEDUPLICATION_BUFFER: Pour le CAS 2 (Sans colonne Type)
            $deduplicationBuffer = [];
            $isTypeMapped = !empty($mapping['type_ecriture']);

            foreach ($data as $index => $rowOrig) {
                // ANALYSE DATA SELON MAPPING (support FIXED values)
                $rowMapped = [];
                foreach($mapping as $field => $colIndex) {
                    if ($field === '_header_index') continue;
                    if (is_string($colIndex) && str_starts_with($colIndex, 'FIXED:')) {
                        $rowMapped[$field] = substr($colIndex, 6);
                    } else {
                        $rowMapped[$field] = $rowOrig[$colIndex] ?? null;
                    }
                }

                // CAPTURE DU NUMÉRO ORIGINAL AVANT TRANSFORMATION
                // Pour Plan Comptable
                $numeroOriginalPlan = trim($rowMapped['numero_de_compte'] ?? '');
                // Pour Tiers
                $numeroOriginalTiers = trim($rowMapped['numero_de_tiers'] ?? '');
                
                // Fallback : Si le numéro original est vide ou AUTO, on essaie de le trouver dans les colonnes brutes (souvent col 0 ou 1)
                if ((empty($numeroOriginalTiers) || $numeroOriginalTiers === 'AUTO') && $import->type == 'tiers') {
                     // Recherche simple : première colonne qui ressemble à un numéro de tiers (commence par 4)
                     foreach ($rowOrig as $cell) {
                         $cell = trim($cell ?? '');
                         if (preg_match('/^[4]\d{2,19}$/', $cell)) {
                             $numeroOriginalTiers = $cell;
                             break;
                         }
                     }
                }

                if ($import->type == 'initial') {
                    // --- IMPORT PLAN COMPTABLE ---
                    $msg_type = "comptes";
                    $rowCompteDeduced = trim($rowMapped['numero_de_compte'] ?? '');

                    if (empty($rowCompteDeduced) || $rowCompteDeduced === 'AUTO') {
                        // Déduction si AUTO ou vide
                        foreach ($rowOrig as $val) {
                            $val = trim($val ?? '');
                            if (preg_match('/^\d{3,12}$/', $val)) {
                                $rowCompteDeduced = $val;
                                break;
                            }
                        }
                    }
                    if (empty($rowCompteDeduced)) {
                        $errors[] = "Ligne " . ($index + 1) . " : Numéro de compte manquant.";
                        continue;
                    }
                    
                    $numeroOriginalPlan = $rowCompteDeduced; // Capture la valeur brute
                    $rowCompte = $this->standardizeAccountNumber($rowCompteDeduced, $accountDigits);
                    if (empty($rowCompte)) {
                        $errors[] = "Ligne " . ($index + 1) . " : Numéro de compte invalide après standardisation.";
                        continue;
                    }
                    if (empty(trim($rowMapped['intitule'] ?? ''))) {
                        $errors[] = "Ligne " . ($index + 1) . " : L'intitulé du compte '$rowCompte' est obligatoire.";
                        continue;
                    }

                    // GESTION DES DOUBLONS ET COLLISIONS PAR NUMÉROTATION SÉQUENTIELLE (COMMIT)
                    if (in_array($rowCompte, $existingAccounts) || isset($batchAccounts[$rowCompte])) {
                        $racine = substr($rowCompte, 0, 3);
                        if (!isset($localMaxAccounts[$racine])) {
                            $maxInDb = PlanComptable::where('company_id', $user->company_id)
                                ->where('numero_de_compte', 'LIKE', $racine . '%')
                                ->whereRaw('LENGTH(numero_de_compte) = ?', [$accountDigits])
                                ->max('numero_de_compte');
                            $localMaxAccounts[$racine] = $maxInDb ?: ($racine . str_pad('0', ($accountDigits - 3), '0', STR_PAD_LEFT));
                        }
                        $lastId = $localMaxAccounts[$racine];
                        $sequencePart = substr($lastId, 3);
                        $nextSeq = (int)$sequencePart + 1;
                        $newId = $racine . str_pad($nextSeq, ($accountDigits - 3), '0', STR_PAD_LEFT);
                        
                        $rowCompte = $newId;
                        $localMaxAccounts[$racine] = $newId;
                    }
                    $batchAccounts[$rowCompte] = $numeroOriginalPlan;

                    // On vérifie si par hasard le NOUVEAU numéro existe déjà (sécurité ultime)
                    $existingMatchId = $planComptableIds[$rowCompte] ?? null;

                    if ($existingMatchId) {
                        // Si le numéro existe toujours (cas rare de collision après séquence), on met à jour
                        PlanComptable::where('id', $existingMatchId)->update([
                            'intitule' => strtoupper($rowMapped['intitule'] ?? 'COMPTE SANS NOM'),
                            'numero_original' => $numeroOriginalPlan,
                            'user_id' => $user->id
                        ]);
                        $duplicateCount++;
                    } else {
                        // CREATE nouveau
                        $classe = substr($rowCompte, 0, 1);
                        $type = in_array((int)$classe, [1, 2, 3, 4, 5, 9]) ? 'Bilan' : 'Compte de résultat';
                        
                        $newAcc = PlanComptable::create([
                            'numero_de_compte' => $rowCompte,
                            'intitule' => strtoupper($rowMapped['intitule'] ?? 'COMPTE SANS NOM'),
                            'numero_original' => $numeroOriginalPlan,
                            'type_de_compte' => $type,
                            'classe' => $classe,
                            'user_id' => $user->id,
                            'company_id' => $user->company_id,
                            'adding_strategy' => 'imported'
                        ]);
                        $planComptableIds[$rowCompte] = $newAcc->id;
                        $importedCount++;
                    }

                } elseif ($import->type == 'tiers') {
                    // --- IMPORT TIERS ---
                    $msg_type = "tiers";
                    $rowCompteNum = $this->standardizeAccountNumber(trim($rowMapped['compte_general'] ?? ''), $accountDigits);
                    $rowType = trim($rowMapped['type_de_tiers'] ?? '');
                    $rowNum = trim($rowMapped['numero_de_tiers'] ?? '');
                    $rowIntitule = trim($rowMapped['intitule'] ?? '');

                    if (empty($rowIntitule)) {
                        $errors[] = "Ligne " . ($index + 1) . " : L'intitulé du tiers est obligatoire.";
                        continue;
                    }

                    // DÉDUCTION INTELLIGENTE DU COMPTE COLLECTIF SI AUTO OU VIDE
                    if (empty($rowCompteNum) || $rowCompteNum === 'AUTO' || $mapping['compte_general'] === 'AUTO') {
                        // 1. Essayer de trouver une colonne brute qui ressemble à un compte 401/411
                        foreach ($rowOrig as $cell) {
                            $cell = trim($cell ?? '');
                            if (preg_match('/^(401|411)\d*/', $cell)) {
                                $rowCompteNum = $this->standardizeAccountNumber($cell, $accountDigits);
                                break;
                            }
                        }

                        // 2. Sinon, déduire par rapport au type de tiers si présent
                        if ((empty($rowCompteNum) || $rowCompteNum === 'AUTO') && !empty($rowType)) {
                            $typeLower = strtolower($rowType);
                            if (in_array($typeLower, ['client', 'cli', 'clt'])) {
                                $rowCompteNum = '411' . str_pad('0', ($accountDigits - 3), '0', STR_PAD_RIGHT);
                            } elseif (in_array($typeLower, ['fournisseur', 'four', 'frs', 'fourn'])) {
                                $rowCompteNum = '401' . str_pad('0', ($accountDigits - 3), '0', STR_PAD_RIGHT);
                            }
                        }

                        // 3. Fallback ultime : déduction via le préfixe du numéro de tiers importé
                        if ((empty($rowCompteNum) || $rowCompteNum === 'AUTO') && !empty($numeroOriginalTiers)) {
                             if (str_starts_with($numeroOriginalTiers, '40')) $rowCompteNum = '401' . str_pad('0', ($accountDigits - 3), '0', STR_PAD_RIGHT);
                             elseif (str_starts_with($numeroOriginalTiers, '41')) $rowCompteNum = '411' . str_pad('0', ($accountDigits - 3), '0', STR_PAD_RIGHT);
                             elseif (str_starts_with($numeroOriginalTiers, '42')) $rowCompteNum = '421' . str_pad('0', ($accountDigits - 3), '0', STR_PAD_RIGHT);
                             elseif (str_starts_with($numeroOriginalTiers, '43')) $rowCompteNum = '431' . str_pad('0', ($accountDigits - 3), '0', STR_PAD_RIGHT);
                             elseif (str_starts_with($numeroOriginalTiers, '44')) $rowCompteNum = '441' . str_pad('0', ($accountDigits - 3), '0', STR_PAD_RIGHT);
                        }

                        // 4. Fallback de sécurité : Si toujours rien, on utilise le compte Fournisseur par défaut pour éviter le blocage
                        if (empty($rowCompteNum) || $rowCompteNum === 'AUTO') {
                            $rowCompteNum = '401' . str_pad('0', ($accountDigits - 3), '0', STR_PAD_RIGHT);
                        }
                    }

                    // Récupérer l'ID du compte collectif
                    $compteCollectifId = $planComptableIds[$rowCompteNum] ?? null;

                    // Si pas trouvé par numéro exact, chercher par racine (ex: 411)
                    if (!$compteCollectifId && !empty($rowCompteNum) && $rowCompteNum !== 'AUTO') {
                         $prefix = substr($rowCompteNum, 0, 3); // 401, 411...
                         $compteCollectifId = PlanComptable::where('company_id', $user->company_id)
                            ->where('numero_de_compte', 'LIKE', $prefix . '%')
                            ->orderBy('numero_de_compte', 'asc')
                            ->value('id');
                    }

                    if (!$compteCollectifId) {
                        $errors[] = "Ligne " . ($index + 1) . " : Impossible de lier le tiers '$rowIntitule' à un compte collectif (Classe 4).";
                        continue;
                    }
                    
                    // LOGIQUE DE GÉNÉRATION BASÉE SUR LE PRÉFIXE IMPORTÉ (si AUTO ou vide)
                    if (($mapping['numero_de_tiers'] === 'AUTO' || empty($rowNum) || $rowNum === 'AUTO' || str_starts_with($rowNum, '-')) && $compteCollectifId) {
                        $importedNum = trim($rowMapped['numero_de_tiers'] ?? '');
                        $generationPrefix = null;
                        if (!empty($importedNum) && strlen($importedNum) >= 2) {
                            $tempPrefix = substr($importedNum, 0, 2);
                            if (in_array($tempPrefix, ['40', '41', '42', '43', '44', '45', '46', '47', '48', '49'])) {
                                $generationPrefix = $tempPrefix;
                            }
                        }

                        $prefix = $generationPrefix ?? substr($rowCompteNum, 0, 2);

                        if (!isset($localMaxTiers[$prefix])) {
                            $resp = $this->getNextTierNumber(new Request([
                                'plan_comptable_id' => $compteCollectifId,
                                'prefix' => $prefix,
                                'intitule' => $rowMapped['intitule'] ?? ''
                            ]));
                            $genData = $resp->getData();
                            if ($genData->success) {
                                $rowNum = $genData->next_id;
                                $localMaxTiers[$prefix] = $genData->next_id;
                            }
                        } else {
                            // Génération locale pour le batch
                            $lastId = $localMaxTiers[$prefix];
                            $prefixLen = strlen($prefix);
                            $sequencePart = substr($lastId, $prefixLen);
                            if (is_numeric($sequencePart)) {
                                $nextSeq = (int)$sequencePart + 1;
                                $availableSpace = (int)($user->company->tier_digits ?? 8) - $prefixLen;
                                $newId = $prefix . str_pad($nextSeq, $availableSpace, '0', STR_PAD_LEFT);
                                $rowNum = $newId;
                                $localMaxTiers[$prefix] = $newId;
                            }
                        }
                    }

                    // Fallback ultime si toujours vide ou AUTO (ne devrait pas arriver avec les corrections)
                    if (empty($rowNum) || $rowNum === 'AUTO') {
                        continue;
                    }

                    $existingTier = PlanTiers::where('company_id', $user->company_id)
                        ->where('numero_de_tiers', $rowNum)
                        ->first();

                    if ($existingTier) {
                        // UPDATE existant
                        $existingTier->update([
                            'intitule' => strtoupper($rowMapped['intitule'] ?? 'TIERS SANS NOM'),
                            'type_de_tiers' => ucfirst(strtolower($rowMapped['type_de_tiers'] ?? 'Autre')),
                            'compte_general' => $compteCollectifId,
                            'numero_original' => $numeroOriginalTiers,
                            'user_id' => $user->id
                        ]);
                        $duplicateCount++;
                    } else {
                        // CREATE nouveau
                        PlanTiers::create([
                            'numero_de_tiers' => strtoupper($rowNum),
                            'intitule' => strtoupper($rowMapped['intitule'] ?? 'TIERS SANS NOM'),
                            'type_de_tiers' => ucfirst(strtolower($rowMapped['type_de_tiers'] ?? 'Autre')),
                            'compte_general' => $compteCollectifId,
                            'numero_original' => $numeroOriginalTiers,
                            'user_id' => $user->id,
                            'company_id' => $user->company_id
                        ]);
                        $importedCount++;
                    }

                } elseif ($import->type == 'journals') {
                    // --- IMPORT JOURNAUX ---
                    $msg_type = "journaux";
                    $rowCodeRaw = trim($rowMapped['code_journal'] ?? '');
                    $rowCode = $this->standardizeJournalCode($rowCodeRaw, $journalDigits);
                    $numeroOriginalJournal = (!empty($rowCodeRaw) && $rowCodeRaw !== 'AUTO') ? $rowCodeRaw : null;
                    if (empty($rowCode) && !($mapping['code_journal'] === 'AUTO')) {
                        $errors[] = "Ligne " . ($index + 1) . " : Code journal manquant.";
                        continue;
                    }

                    if (strlen($rowCode) > $journalLimit) {
                        $errors[] = "Ligne " . ($index + 1) . " : Le code journal '$rowCode' est trop long (Max $journalLimit caractères).";
                        continue;
                    }

                    $type = $rowMapped['type'] ?? null;
                    if (empty($type)) {
                        $searchStr = strtoupper($rowCode . ' ' . ($rowMapped['intitule'] ?? ''));
                        $type = 'Opérations Diverses';
                        if (Str::contains($searchStr, ['ACH', 'FOURN', 'FRN'])) $type = 'Achats';
                        elseif (Str::contains($searchStr, ['VEN', 'CLT', 'CLI'])) $type = 'Ventes';
                        elseif (Str::contains($searchStr, ['BQ', 'BNQ', 'BANK', 'SG', 'ECO', 'BOA', 'UBA', 'TRES', 'TRZ', 'BANKING'])) $type = 'Banque';
                        elseif (Str::contains($searchStr, ['CAI', 'CASH', 'CAS'])) $type = 'Caisse';
                    }

                    // GÉNÉRATION SÉQUENTIELLE SI AUTO OU VIDE
                    if (empty($rowCode) || $rowCode === 'AUTO' || $mapping['code_journal'] === 'AUTO') {
                        $prefix = 'OD';
                        if ($type === 'Achats') $prefix = 'ACH';
                        elseif ($type === 'Ventes') $prefix = 'VEN';
                        elseif ($type === 'Banque') $prefix = 'BQ';
                        elseif ($type === 'Caisse') $prefix = 'CAI';

                        if (!isset($localMaxJournals[$prefix])) {
                            // Chercher en base pour le premier du lot
                            $lastCode = CodeJournal::where('company_id', $user->company_id)
                                ->where('code_journal', 'LIKE', $prefix . '%')
                                ->orderBy('code_journal', 'desc')
                                ->value('code_journal');

                            if ($lastCode) {
                                $num = filter_var($lastCode, FILTER_SANITIZE_NUMBER_INT);
                                $nextNum = (int)$num + 1;
                            } else {
                                $nextNum = 1;
                            }
                            $rowCode = $prefix . $nextNum;
                            $localMaxJournals[$prefix] = $rowCode;
                        } else {
                            // Incrémenter localement
                            $lastCode = $localMaxJournals[$prefix];
                            $num = filter_var($lastCode, FILTER_SANITIZE_NUMBER_INT);
                            $nextNum = (int)$num + 1;
                            $rowCode = $prefix . $nextNum;
                            $localMaxJournals[$prefix] = $rowCode;
                        }
                    }

                    $compteNum = $this->standardizeAccountNumber(trim($rowMapped['compte_de_tresorerie'] ?? ''), $accountDigits);
                    $compteId = $planComptableIds[$compteNum] ?? null;

                    // VALIDATION COMPTE TRESORERIE POUR BANQUE/CAISSE
                    if (in_array($type, ['Banque', 'Caisse']) && !$compteId) {
                        $errors[] = "Ligne " . ($index + 1) . " : Un compte de trésorerie (Classe 5) est obligatoire pour un journal de type '$type'.";
                        continue;
                    }

                    $existingJournal = CodeJournal::where('company_id', $user->company_id)
                        ->where(function($q) use ($rowCode) {
                            $q->where('code_journal', strtoupper($rowCode))
                              ->orWhere('numero_original', $rowCode)
                              ->orWhere('numero_original', strtoupper($rowCode));
                        })
                        ->first();

                    if ($existingJournal) {
                        // UPDATE existant
                        $existingJournal->update([
                            'intitule' => strtoupper($rowMapped['intitule'] ?? 'JOURNAL SANS NOM'),
                            'type' => $type,
                            'compte_de_tresorerie' => $compteId,
                            'numero_original' => $numeroOriginalJournal ?? $existingJournal->numero_original,
                            'poste_tresorerie' => $rowMapped['poste_tresorerie'] ?? null,
                            'traitement_analytique' => (strtolower($rowMapped['traitement_analytique'] ?? '') === 'oui'),
                            'rapprochement_sur' => $rowMapped['rapprochement_sur'] ?? null,
                            'user_id' => $user->id
                        ]);
                        $duplicateCount++;
                    } else {
                        // CREATE nouveau
                        $newJournal = CodeJournal::create([
                            'code_journal' => strtoupper($rowCode),
                            'intitule' => strtoupper($rowMapped['intitule'] ?? 'JOURNAL SANS NOM'),
                            'type' => $type,
                            'compte_de_tresorerie' => $compteId,
                            'numero_original' => $numeroOriginalJournal,
                            'poste_tresorerie' => $rowMapped['poste_tresorerie'] ?? null,
                            'traitement_analytique' => (strtolower($rowMapped['traitement_analytique'] ?? '') === 'oui'),
                            'rapprochement_sur' => $rowMapped['rapprochement_sur'] ?? null,
                            'user_id' => $user->id,
                            'company_id' => $user->company_id
                        ]);
                        $existingJournals[strtoupper($rowCode)] = $newJournal->id; 
                        $importedCount++;
                    }

                } elseif ($import->type == 'courant') {
                    // --- IMPORT ÉCRITURES ---
                    $msg_type = "écritures";

                    // CAS 1 : FILTRAGE PAR TYPE (Si colonne mappée)
                    if (isset($rowMapped['type_ecriture'])) {
                        $typeVal = strtoupper(trim($rowMapped['type_ecriture']));
                        if ($typeVal === 'A') {
                            // On ignore les lignes Analytiques
                            $report['filtered_a']++;
                            continue;
                        }
                    }

                    $rowCompte = $this->standardizeAccountNumber(trim($rowMapped['compte'] ?? ''), $accountDigits);
                    $rowJournalRaw = trim($rowMapped['journal'] ?? '');
                    $rowJournal = $this->standardizeJournalCode($rowJournalRaw, $journalDigits);

                    // CAS 2 : DÉDUPLICATION INTELLIGENTE & DÉTECTION AUTO DU TYPE
                    // Si colonne Type NON mappée, on cherche "A" partout et on déduplique
                    if (!$isTypeMapped) {
                         // 2.1 : Détection "Aveugle" du Type A
                         // On regarde si une des colonnes brutes (non mappée ou même mappée si on veut être agressif) contient juste "A"
                         // On exclut les champs déjà identifiés comme Account/Debit/Credit pour éviter les faux positifs (peu probable pour "A")
                         $isHiddenA = false;
                         foreach ($rowOrig as $cellVal) {
                             $v = strtoupper(trim($cellVal ?? ''));
                             if ($v === 'A' || $v === 'ANALYTIQUE') {
                                 $isHiddenA = true;
                                 break;
                             }
                         }
                         if ($isHiddenA) {
                             $report['filtered_a']++;
                             continue;
                         }

                         // 2.2 : Signature de déduplication (Fallback)
                         $normRef = trim($rowMapped['reference'] ?? '');
                         $normLib = trim($rowMapped['libelle'] ?? '');
                         $normDebit = str_replace([',', ' '], ['.', ''], $rowMapped['debit'] ?? '0');
                         $normCredit = str_replace([',', ' '], ['.', ''], $rowMapped['credit'] ?? '0');
                         
                         $sigParts = [
                             trim($rowMapped['jour'] ?? ''),
                             trim($rowJournal),
                             trim($rowCompte),
                             (string)(float)$normDebit, 
                             (string)(float)$normCredit
                         ];

                         // Logique stricte : Si Ref existe, on l'utilise. Sinon Libellé.
                         // MAIS pour résoudre le problème "Surcharge", on force l'exclusion du libellé si on a une Ref.
                         if (!empty($normRef)) {
                             $sigParts[] = $normRef;
                         }

                         $signature = md5(implode('|', $sigParts));

                         if (isset($deduplicationBuffer[$signature])) {
                             $duplicateCount++;
                             $report['deduplicated']++;
                             continue;
                         }
                         $deduplicationBuffer[$signature] = true;
                    }

                    if (empty($rowCompte)) {
                        $errors[] = "Ligne " . ($index + 1) . " : Compte manquant.";
                        continue;
                    }
                    if (empty($rowJournal)) {
                        $errors[] = "Ligne " . ($index + 1) . " : Journal manquant.";
                        continue;
                    }

                    $compteId = $planComptableIds[$rowCompte] ?? $planComptableOriginalIds[$rowCompte] ?? null;
                    $journalId = $existingJournals[strtoupper($rowJournal)] ?? $existingJournalsOriginal[strtoupper($rowJournal)] ?? null;

                    if (!$compteId) {
                        $errors[] = "Ligne " . ($index + 1) . " : Le compte '$rowCompte' n'existe pas dans le plan.";
                        continue;
                    }
                    if (!$journalId) {
                        $errors[] = "Ligne " . ($index + 1) . " : Le journal '$rowJournal' n'existe pas.";
                        continue;
                    }

                    $tiersNum = trim($rowMapped['tiers'] ?? '');
                    $tiersId = !empty($tiersNum) ? ($planTiersIds[strtoupper($tiersNum)] ?? $planTiersOriginalIds[strtoupper($tiersNum)] ?? null) : null;

                    $debit = (float)str_replace(',', '.', preg_replace('/[^0-9,.]/', '', $rowMapped['debit'] ?? 0));
                    $credit = (float)str_replace(',', '.', preg_replace('/[^0-9,.]/', '', $rowMapped['credit'] ?? 0));

                    // VALIDATION MONTANT NUL
                    if (abs($debit) < 0.01 && abs($credit) < 0.01) {
                        $errors[] = "Ligne " . ($index + 1) . " : Le montant (Débit ou Crédit) ne peut pas être nul.";
                        continue;
                    }

                    $dateStr = trim($rowMapped['jour'] ?? '');
                    if (empty($dateStr)) {
                        $errors[] = "Ligne " . ($index + 1) . " : Date manquante.";
                        continue;
                    }

                    // Cas 1: Excel stocke parfois une date sous forme de numéro de série (ex: 45672)
                    // On convertit via PhpSpreadsheet
                    if (is_numeric($dateStr) && (float)$dateStr > 59) {
                        try {
                            $dt = ExcelDate::excelToDateTimeObject((float)$dateStr);
                            $date = Carbon::instance($dt);
                        } catch (\Exception $e) {
                            $errors[] = "Ligne " . ($index + 1) . " : Date Excel invalide '$dateStr'.";
                            continue;
                        }

                    // Cas 2: L'import fournit juste un quantième (jour du mois)
                    } elseif (is_numeric($dateStr) && strlen($dateStr) <= 2) {
                        $year = $exercice ? $exercice->date_debut->year : now()->year;
                        $month = $exercice ? $exercice->date_debut->month : now()->month;
                        try {
                            $date = Carbon::create($year, $month, (int)$dateStr);
                        } catch(\Exception $e) {
                            $errors[] = "Ligne " . ($index + 1) . " : Jour invalide '$dateStr'.";
                            continue;
                        }
                    } else {
                        // Cas 3: Date texte. On priorise d/m/Y pour éviter les inversions (m/d/Y)
                        $dateStrNormalized = str_replace(['\\', '.'], ['/', '/'], $dateStr);
                        try {
                            $formats = ['d/m/Y', 'j/n/Y', 'd/n/Y', 'j/m/Y', 'Y-m-d', 'd-m-Y', 'Y/m/d'];
                            $date = null;
                            foreach ($formats as $fmt) {
                                try {
                                    $d = Carbon::createFromFormat($fmt, $dateStrNormalized);
                                    if ($d && $d->format($fmt) == $dateStrNormalized) {
                                        $date = $d;
                                        break;
                                    }
                                } catch (\Exception $e) {}
                            }
                            if (!$date) {
                                $date = Carbon::parse($dateStrNormalized);
                            }
                        } catch(\Exception $e) {
                            $errors[] = "Ligne " . ($index + 1) . " : Format de date invalide '$dateStr'.";
                            continue;
                        }
                    }

                    // VALIDATION DATE HORS EXERCICE
                    if ($exercice && !$date->between($exercice->date_debut->startOfDay(), $exercice->date_fin->endOfDay())) {
                        $errors[] = "Ligne " . ($index + 1) . " : Date hors exercice (" . $date->format('d/m/Y') . ").";
                        continue;
                    }

                    // LOGIQUE MASTER NUMBERING : Groupement par n_saisie d'origine ou référence
                    $origNSaisie = $rowMapped['n_saisie'] ?? $rowMapped['reference'] ?? 'IMPORT';
                    if (!isset($ecrMapping[$origNSaisie])) {
                        $ecrMapping[$origNSaisie] = $this->generateGlobalSaisieNumber($user->company_id);
                    }
                    $globalNSaisie = $ecrMapping[$origNSaisie];

                    // TRACKING EQUILIBRE
                    if (!isset($groupBalances[$globalNSaisie])) {
                        $groupBalances[$globalNSaisie] = ['debit' => 0, 'credit' => 0, 'ref' => $origNSaisie];
                    }
                    $groupBalances[$globalNSaisie]['debit'] += round($debit, 2);
                    $groupBalances[$globalNSaisie]['credit'] += round($credit, 2);

                    // VALIDATION TIERS OBLIGATOIRE (401/411)
                    if (Str::startsWith($rowCompte, ['401', '402', '411', '412']) && !$tiersId) {
                        $errors[] = "Ligne " . ($index + 1) . " : Un tiers est obligatoire pour le compte '$rowCompte'.";
                        continue;
                    }

                    // LOGIQUE JOURNAL SAISI : On s'assure que le lien existe pour les rapports
                    $journalSaisiId = null;
                    if ($journalId) {
                        $js = \App\Models\JournalSaisi::firstOrCreate([
                            'annee' => $date->year,
                            'mois' => $date->month,
                            'exercices_comptables_id' => $import->exercice_id ?? session('current_exercice_id'),
                            'code_journals_id' => $journalId,
                            'company_id' => $user->company_id,
                        ], [
                            'user_id' => $user->id
                        ]);
                        $journalSaisiId = $js->id;
                    }

                    EcritureComptable::create([
                        'date' => $date,
                        'n_saisie' => $globalNSaisie, // MASTER
                        'n_saisie_user' => $origNSaisie, // ORIGIN
                        'reference_piece' => strtoupper($rowMapped['reference'] ?? 'IMPORT'),
                        'plan_comptable_id' => $compteId,
                        'plan_tiers_id' => $tiersId,
                        'plan_analytique' => 0,
                        'code_journal_id' => $journalId,
                        'journaux_saisis_id' => $journalSaisiId, // LIEN VERS JOURNAL SAISI
                        'description_operation' => strtoupper($rowMapped['libelle'] ?? 'IMPORTATION EXTERNE'),
                        'debit' => $debit,
                        'credit' => $credit,
                        'exercices_comptables_id' => $import->exercice_id ?? session('current_exercice_id'),
                        'company_id' => $user->company_id,
                        'user_id' => $user->id,
                        'statut' => 'approved'
                    ]);
                    $importedCount++;
                }
            }

            // VÉRIFICATION FINALE DE L'ÉQUILIBRE (Écritures uniquement)
            if ($import->type == 'courant') {
                foreach ($groupBalances as $ns => $bal) {
                    if (abs($bal['debit'] - $bal['credit']) > 0.01) {
                        $diff = round(abs($bal['debit'] - $bal['credit']), 2);
                        $errors[] = "DÉSÉQUILIBRE : Le groupe d'écritures '{$bal['ref']}' est déséquilibré de $diff (Débit: {$bal['debit']}, Crédit: {$bal['credit']}).";
                    }
                }
            }

            // Si on a des erreurs, on annule tout
            if (!empty($errors)) {
                DB::rollBack();
                $report['status'] = 'error';
                $report['errors'] = $errors;
                return view('admin.import.report', ['report' => $report, 'batch_id' => $id]);
            }

            $import->update([
                'status' => 'committed',
                'error_log' => "Importation réussie : $importedCount $msg_type créés."
            ]);
            
            // Finalize Stats
            $report['processed_g'] = $importedCount;
            // Total debit/credit already tracked via groupBalances if needed, but easier to sum here if we tracked it row by row
            // Let's compute totals from groupBalances for simplicity
            $report['total_debit'] = array_sum(array_column($groupBalances, 'debit'));
            $report['total_credit'] = array_sum(array_column($groupBalances, 'credit'));

            DB::commit();

            return view('admin.import.report', ['report' => $report, 'batch_id' => $id]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("IMPORT ERROR [{$import->type}]: " . $e->getMessage(), [
                'exception' => $e,
                'import_id' => $id,
                'user_id' => $user->id ?? 'N/A'
            ]);
            
            $report['status'] = 'error';
            $report['errors'][] = "Erreur Système : " . $e->getMessage();
            return view('admin.import.report', ['report' => $report, 'batch_id' => $id]);
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
            
            $exists = PlanComptable::where('company_id', $user->company_id)
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
                'company_id' => $user->company_id,
                'adding_strategy' => 'manuel'
            ]);

            return response()->json(['success' => true, 'message' => 'Compte créé avec succès.']);

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
            
            if (!isset($data[(int)$index]) && !isset($data[(string)$index])) {
                Log::warning("STAGING UPDATE: Index $index not found in raw_data", ['data_keys' => array_keys($data)]);
                return response()->json(['success' => false, 'message' => "Ligne non trouvée (Index: $index)."], 404);
            }

            $targetIndex = isset($data[(int)$index]) ? (int)$index : (string)$index;
            $newValues = $request->input('values');
            
            Log::info("STAGING UPDATE: Data received", [
                'target_index' => $targetIndex,
                'provided_values' => $newValues,
                'current_row' => $data[$targetIndex] ?? 'MISSING'
            ]);

            foreach ($newValues as $colIndex => $value) {
                $data[$targetIndex][(string)$colIndex] = $value;
                // Si c'est un tableau numérique, PHP gère la conversion
            }

            // Forcer l'assignation et la sauvegarde
            $import->raw_data = $data;
            $import->save();

            Log::info("STAGING UPDATE: SUCCESS", [
                'saved_row' => $import->raw_data[$targetIndex] ?? 'ERROR'
            ]);

            return response()->json(['success' => true, 'message' => 'Ligne mise à jour avec succès.']);
        } catch (\Exception $e) {
            Log::error("STAGING UPDATE FAILED: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
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

            // Supprimer la ligne et réindexer
            array_splice($data, $index, 1);
            
            $import->update(['raw_data' => $data]);

            return response()->json(['success' => true, 'message' => 'Ligne supprimée de l\'import.']);
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
                $data = PlanComptable::where('company_id', $user->company_id)->orderBy('numero_de_compte')->get();
                $headers = ['Compte', 'Intitule', 'Type', 'Classe'];
                $callback = function($file) use ($data) {
                    foreach ($data as $row) {
                        fputcsv($file, [$row->numero_de_compte, $row->intitule, $row->type_de_compte, $row->classe], ';');
                    }
                };
                break;

            case 'plan_tiers':
                $data = PlanTiers::with('compte')->where('company_id', $user->company_id)->orderBy('numero_de_tiers')->get();
                $headers = ['Numero Tiers', 'Intitule', 'Type', 'Compte Collectif'];
                $callback = function($file) use ($data) {
                    foreach ($data as $row) {
                        fputcsv($file, [$row->numero_de_tiers, $row->intitule, $row->type_de_tiers, $row->compte->numero_de_compte ?? ''], ';');
                    }
                };
                break;

            case 'journals':
                $data = CodeJournal::where('company_id', $user->company_id)->orderBy('code_journal')->get();
                $headers = ['Code', 'Intitule', 'Type'];
                $callback = function($file) use ($data) {
                    foreach ($data as $row) {
                        fputcsv($file, [$row->code_journal, $row->intitule, $row->type], ';');
                    }
                };
                break;

            case 'ecritures':
                $query = EcritureComptable::with(['planComptable', 'planTiers', 'codeJournal'])
                    ->where('company_id', $user->company_id);
                
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

        $existingAccounts = PlanComptable::where('company_id', $user->company_id)
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
                        $match = PlanComptable::where('company_id', $user->company_id)
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
        if (empty($number) || !is_numeric($number)) {
            return $number;
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

        if (strlen($code) < $digits) {
            return str_pad($code, $digits, '0', STR_PAD_RIGHT);
        } elseif (strlen($code) > $digits) {
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
}


