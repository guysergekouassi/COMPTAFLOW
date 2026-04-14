<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Services\ExcelParserService;
use App\Services\ExcelIaService;
use App\Models\ExcelIaAnalyse;
use App\Models\EcritureComptable;
use App\Models\PlanComptable;
use App\Models\PlanTiers;
use App\Models\CodeJournal;
use App\Models\ExerciceComptable;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelIaController extends Controller
{
    private ExcelParserService $parser;
    private ExcelIaService $iaService;

    public function __construct(ExcelParserService $parser, ExcelIaService $iaService)
    {
        $this->middleware('auth');
        $this->parser    = $parser;
        $this->iaService = $iaService;
    }

    public function index()
    {
        $user      = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $historique = ExcelIaAnalyse::where('company_id', $companyId)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Stats rapides
        $stats = [
            'total'    => ExcelIaAnalyse::where('company_id', $companyId)->count(),
            'injectes' => ExcelIaAnalyse::where('company_id', $companyId)->where('injecte_bdd', true)->count(),
        ];

        // Récupération du Plan Comptable et Tiers pour l'édition (lightweight lists)
        $comptes = PlanComptable::where('company_id', $companyId)
            ->orderBy('numero_de_compte')
            ->get(['numero_de_compte', 'intitule']);

        $tiers = PlanTiers::where('company_id', $companyId)
            ->orderBy('numero_de_tiers')
            ->get(['numero_de_tiers', 'intitule']);

        return view('excel_ia', compact('historique', 'stats', 'comptes', 'tiers'));
    }

    /**
     * Lance l'analyse IA des fichiers uploadés.
     * Retourne JSON avec les écritures générées.
     */
    public function analyser(Request $request)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(600);

        $request->validate([
            'projet_id'  => 'nullable|exists:excel_ia_projets,id',
            'fichiers'   => 'nullable|array|max:20',
            'fichiers.*' => 'nullable|file|mimes:xlsx,xls,csv,pdf,jpg,jpeg,png|max:20480',
            'mois_cible' => 'nullable|string|max:20',
            'message'    => 'nullable|string|max:5000',
            'historique' => 'nullable|array',
        ]);

        // Mode chat (texte seul) — redirige vers chat()
        if (empty($request->file('fichiers')) && $request->filled('message')) {
            return $this->chatHandler($request);
        }

        $user      = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $mois      = strtoupper($request->input('mois_cible', 'TOUS'));

        try {
            // 1. Parser tous les fichiers
            $fichiersParsed = [];
            $visionFiles    = [];
            $nomsFC         = [];

            // Si des fichiers sont uploadés directement dans le formulaire classique
            if ($request->hasFile('fichiers')) {
                foreach ($request->file('fichiers') as $fichier) {
                    $parsed = $this->parser->parse($fichier);
                    $fichiersParsed[] = $parsed;
                    $nomsFC[] = $parsed['nom'];

                    if ($parsed['ia_vision'] ?? false) {
                        $visionFiles[] = ['base64' => $parsed['base64'], 'mime' => $parsed['mime'], 'nom' => $parsed['nom']];
                    }
                }
            }

            // Si la requête provient d'un projet, on ajoute les fichiers du Centre de Dépôt
            $projetInstructions = "";
            $projetId = $request->input('projet_id');
            if ($projetId) {
                // Import dynamique du modèle
                $projet = \App\Models\ExcelIaProjet::with('fichiers')->find($projetId);
                if ($projet) {
                    $projetInstructions = $projet->instructions;
                    foreach ($projet->fichiers as $f) {
                        $absolutePath = storage_path('app/' . $f->chemin);
                        if (file_exists($absolutePath)) {
                            $fileObj = new \Illuminate\Http\UploadedFile($absolutePath, $f->nom, $f->mime, null, true);
                            $parsed = $this->parser->parse($fileObj);
                            $fichiersParsed[] = $parsed;
                            $nomsFC[] = $f->nom;

                            if (!empty($parsed['ia_vision'])) {
                                $visionFiles[] = ['base64' => $parsed['base64'], 'mime' => $parsed['mime'], 'nom' => $parsed['nom']];
                            }
                        }
                    }
                }
            }

            // 2. Construire le contexte global texte
            $contexte = $this->parser->construireContexteGlobal($fichiersParsed);
            if (!empty($projetInstructions)) {
                $contexte .= "\n\n**INSTRUCTIONS DU PROJET :**\n" . $projetInstructions;
            }

            // 3. Appel IA (incluant l'historique du chat et les instructions système du projet)
            $historique = $request->input('historique', []);
            $resultat = $this->iaService->analyser($contexte, $visionFiles, $companyId, $mois, $historique, $projetId);

            if (!$resultat['success']) {
                return response()->json([
                    'success' => false,
                    'error'   => $resultat['error'] ?? "L'analyse IA a échoué.",
                ], 500);
            }

            // 4. Sauvegarder l'analyse en BDD (historique)
            $analyse = ExcelIaAnalyse::create([
                'company_id'          => $companyId,
                'user_id'             => $user->id,
                'projet_id'           => $projetId, // Liaison dynamique au projet
                'exercice_id'         => session('exercice_id'),
                'fichiers_noms'       => json_encode($nomsFC),
                'mois_cible'          => $mois,
                'ecritures_json'      => json_encode($resultat['ecritures']),
                'rapport_transparence'=> $resultat['rapport'],
                'statut'              => 'analyse',
                'nb_ecritures'        => $resultat['nb_ecritures'],
                'total_debit'         => $resultat['total_debit'],
                'total_credit'        => $resultat['total_credit'],
            ]);

            return response()->json([
                'success'      => true,
                'analyse_id'   => $analyse->id,
                'ecritures'    => $resultat['ecritures'],
                'txt_sage'     => $resultat['txt_sage'],
                'rapport'      => $resultat['rapport'],
                'nb_ecritures' => $resultat['nb_ecritures'],
                'total_debit'  => $resultat['total_debit'],
                'total_credit' => $resultat['total_credit'],
                'equilibre'    => $resultat['equilibre'],
            ]);

        } catch (\Throwable $e) {
            Log::error("[ExcelIaController] Erreur: " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Répond à un message de chat (sans fichiers obligatoires).
     */
    public function chat(Request $request)
    {
        $request->validate([
            'message'    => 'required|string|max:5000',
            'fichiers'   => 'nullable|array|max:20',
            'fichiers.*' => 'nullable|file|mimes:xlsx,xls,csv,pdf,jpg,jpeg,png|max:20480',
            'historique' => 'nullable|array',
        ]);

        $user      = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        try {
            // Préparer les fichiers vision si présents
            $visionFiles = [];
            foreach ($request->file('fichiers', []) as $fichier) {
                $parsed = $this->parser->parse($fichier);
                if ($parsed['ia_vision'] ?? false) {
                    $visionFiles[] = ['base64' => $parsed['base64'], 'mime' => $parsed['mime'], 'nom' => $parsed['nom']];
                }
            }

            $historique = $request->input('historique', []);
            $projetId   = $request->input('projet_id') ? (int) $request->input('projet_id') : null;
            $resultat   = $this->iaService->chat($request->input('message'), $visionFiles, $companyId, $historique, $projetId);

            if (!$resultat['success']) {
                return response()->json(['success' => false, 'error' => $resultat['error']], 500);
            }

            return response()->json([
                'success' => true,
                'type'    => 'chat',
                'reponse' => $resultat['reponse'],
            ]);

        } catch (\Throwable $e) {
            Log::error("[ExcelIaController Chat] " . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Gestion interne du chat quand appelé depuis analyser() sans fichiers.
     */
    private function chatHandler(Request $request)
    {
        $user      = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $resultat = $this->iaService->chat(
            $request->input('message', ''),
            [],
            $companyId,
            $request->input('historique', []),
            $request->input('projet_id')
        );

        if (!$resultat['success']) {
            return response()->json(['success' => false, 'error' => $resultat['error']], 500);
        }

        return response()->json(['success' => true, 'type' => 'chat', 'reponse' => $resultat['reponse']]);
    }

    /**
     * Exporte les écritures au format TXT Sage (téléchargement direct).
     */
    public function exportTxt(Request $request)
    {
        $request->validate([
            'analyse_id'     => 'nullable|integer',
            'ecritures_json' => 'nullable|string',
        ]);

        $user      = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        $ecritures = [];
        $mois      = 'IA';

        if ($request->ecritures_json) {
            $ecritures = json_decode($request->ecritures_json, true) ?? [];
        } elseif ($request->analyse_id) {
            $analyse = ExcelIaAnalyse::where('id', $request->analyse_id)
                ->where('company_id', $companyId)
                ->firstOrFail();
            $ecritures = json_decode($analyse->ecritures_json, true) ?? [];
            $mois = $analyse->mois_cible;
            $analyse->update(['txt_telecharge' => true]);
        }

        if (empty($ecritures)) {
            return response()->json(['success' => false, 'error' => 'Aucune donnée à exporter.'], 400);
        }

        $contenu   = $this->genererTxtSage($ecritures, $mois);
        $filename  = "ecritures_sage_{$mois}_" . now()->format('Ymd_His') . ".txt";

        return response()->streamDownload(function () use ($contenu) {
            echo $contenu;
        }, $filename, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    /**
     * Injecte les écritures dans la BDD ComptaFlow.
     */
    public function injecterBdd(Request $request)
    {
        $request->validate([
            'ecritures_json' => 'required|string',
        ]);

        $user      = Auth::user();
        $companyId = session('current_company_id', $user->company_id);
        
        $ecritures  = json_decode($request->ecritures_json, true) ?? [];
        if (empty($ecritures)) {
            return back()->with('error', 'Aucune écriture à injecter.');
        }

        $exerciceId = session('exercice_id');
        $nSaisieUser = $this->generateUserSaisieNumber($companyId, $user);
        $nSaisieGlobal = $this->generateGlobalSaisieNumber($companyId);
        $injected   = 0;

        // Si on a un analyse_id, on mettra à jour l'historique
        $analyseId = $request->input('analyse_id');
        if ($analyseId) {
            $analyse = ExcelIaAnalyse::where('id', $analyseId)->where('company_id', $companyId)->first();
            if ($analyse) {
                $analyse->update([
                    'ecritures_json' => $request->ecritures_json, // On sauve les modifs faites dans la grille
                    'injecte_bdd'   => true,
                    'injecte_le'    => now()
                ]);
            }
        }

        foreach ($ecritures as $e) {
            try {
                $dateRaw = $e['date'] ?? '010125';
                $date = $this->convertirDate($dateRaw);
                
                // Recherche des IDs réels en BDD
                $journalId = null;
                if (!empty($e['journal'])) {
                    $journal = CodeJournal::where('company_id', $companyId)->where('code', $e['journal'])->first();
                    $journalId = $journal ? $journal->id : null;
                }

                $compteId = null;
                if (!empty($e['compte'])) {
                    $compte = PlanComptable::where('company_id', $companyId)->where('numero_de_compte', $e['compte'])->first();
                    $compteId = $compte ? $compte->id : null;
                }

                $tiersId = null;
                if (!empty($e['tiers'])) {
                    $tiers = PlanTiers::where('company_id', $companyId)->where('numero_de_tiers', $e['tiers'])->first();
                    $tiersId = $tiers ? $tiers->id : null;
                }

                if (!$compteId || !$journalId) {
                    Log::warning("[ExcelIA] Ignoré car compte ou journal introuvable : " . json_encode($e));
                    continue;
                }

                EcritureComptable::create([
                    'company_id'    => $companyId,
                    'exercice_id'   => $exerciceId,
                    'user_id'       => $user->id,
                    'n_saisie'      => $nSaisieGlobal,
                    'n_saisie_user' => $nSaisieUser,
                    'date_ecriture' => $date,
                    'journal_code'  => $e['journal'] ?? 'OD',
                    'code_journal_id' => $journalId,
                    'numero_piece'  => $e['num_facture'] ?? '',
                    'reference'     => $e['ref_piece'] ?? '',
                    'numero_compte' => $e['compte'] ?? '',
                    'plan_comptable_id' => $compteId,
                    'plan_tiers_id' => $tiersId,
                    'libelle'       => substr($e['libelle'] ?? '', 0, 60),
                    'debit'         => $e['debit'] ?? 0,
                    'credit'        => $e['credit'] ?? 0,
                    'source'        => 'excel_ia',
                    'status'        => 'valide', // Par défaut pour injection IA autorisée
                ]);
                $injected++;
            } catch (\Throwable $e2) {
                Log::warning("[ExcelIaController] Ligne non injectée: " . $e2->getMessage());
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'injected' => $injected,
                'message'  => "{$injected} écritures enregistrées dans ComptaFlow.",
            ]);
        }

        return back()->with('success', "{$injected} écritures enregistrées avec succès dans ComptaFlow.");
    }

    /**
     * Injection BDD + téléchargement TXT en une seule action.
     */
    public function injecterEtTelecharger(Request $request)
    {
        $request->validate(['ecritures_json' => 'required|string']);

        $user      = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        // Injection BDD
        $requestData = new Request(['ecritures_json' => $request->ecritures_json]);
        $requestData->setMethod('POST');
        $this->injecterBdd($requestData);

        // Préparer le TXT
        $ecritures = json_decode($request->ecritures_json, true) ?? [];
        $contenu   = $this->genererTxtSage($ecritures, 'IA');
        $filename  = "ecritures_sage_ia_" . now()->format('Ymd_His') . ".txt";

        return response()->json([
            'success'    => true,
            'download'   => true,
            'filename'   => $filename,
            'content'    => base64_encode($contenu),
            'message'    => "Écritures injectées et fichier TXT prêt.",
        ]);
    }

    /**
     * Historique des analyses.
     */
    public function historique()
    {
        $user      = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $analyses = ExcelIaAnalyse::where('company_id', $companyId)
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('excel_ia_historique', compact('analyses'));
    }

    /**
     * Affiche les détails d'une analyse.
     */
    public function historiqueShow(int $id)
    {
        $user      = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $analyse = ExcelIaAnalyse::where('id', $id)
            ->where('company_id', $companyId)
            ->firstOrFail();

        $ecritures = json_decode($analyse->ecritures_json, true) ?? [];

        return view('excel_ia_detail', compact('analyse', 'ecritures'));
    }

    // ─────────────────────────── HELPERS ───────────────────────────────

    /**
     * Génère le contenu TXT au format Sage.
     */
    private function genererTxtSage(array $ecritures, string $mois): string
    {
        $lines = [];
        $lines[] = "================================================================";
        $lines[] = "ÉCRITURES COMPTABLES — {$mois}";
        $lines[] = "Généré par ComptaFlow — " . now()->format('d/m/Y H:i');
        $lines[] = "Format : Date;NumFact;Journal;RefPiece;Compte;Libellé;Débit;Crédit;Tiers;";
        $lines[] = "================================================================";
        $lines[] = "";

        $moisCourant = '';
        foreach ($ecritures as $e) {
            if (!empty($e['mois']) && $e['mois'] !== $moisCourant) {
                $moisCourant = $e['mois'];
                $lines[] = "";
                $lines[] = "=== MOIS : {$moisCourant} ===";
            }
            $lines[] = implode(';', [
                $e['date']        ?? '',
                $e['num_facture'] ?? 'Fact N°',
                $e['journal']     ?? '',
                $e['ref_piece']   ?? '',
                $e['compte']      ?? '',
                $e['libelle']     ?? '',
                $e['debit']       ?? 0,
                $e['credit']      ?? 0,
                $e['tiers']       ?? '',
            ]) . ';';
        }

        return implode("\r\n", $lines);
    }

    /**
     * Numérotation user-friendly (CPT-XX_000...)
     */
    private function generateUserSaisieNumber(int $companyId, $user): string
    {
        $initials = $user->initiales ?? 'IA';
        $prefix = "CPT-" . $initials . "_";

        $lastUserSaisie = EcritureComptable::where('company_id', $companyId)
            ->where('n_saisie_user', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        $nextSequence = 1;
        if ($lastUserSaisie && preg_match('/_(\d+)$/', $lastUserSaisie->n_saisie_user, $matches)) {
            $nextSequence = intval($matches[1]) + 1;
        }

        do {
            $nSaisieUser = $prefix . str_pad($nextSequence, 12, '0', STR_PAD_LEFT);
            $existe = EcritureComptable::where('company_id', $companyId)
                ->where('n_saisie_user', $nSaisieUser)
                ->exists();
            if ($existe) $nextSequence++;
        } while ($existe);

        return $nSaisieUser;
    }

    /**
     * Numérotation interne globale (ECR_000...)
     */
    private function generateGlobalSaisieNumber(int $companyId): string
    {
        $prefix = "ECR_";

        $lastGlobalSaisie = EcritureComptable::where('company_id', $companyId)
            ->whereNotNull('n_saisie')
            ->orderBy('id', 'desc')
            ->first();

        $nextSequence = 1;
        if ($lastGlobalSaisie && preg_match('/_(\d+)$/', $lastGlobalSaisie->n_saisie, $matches)) {
            $nextSequence = intval($matches[1]) + 1;
        }

        do {
            $nSaisie = $prefix . str_pad($nextSequence, 12, '0', STR_PAD_LEFT);
            $existe = EcritureComptable::where('company_id', $companyId)
                ->where('n_saisie', $nSaisie)
                ->exists();
            if ($existe) $nextSequence++;
        } while ($existe);

        return $nSaisie;
    }

    /**
     * Convertit une date JJMMAA en AAAA-MM-JJ.
     */
    private function convertirDate(string $date): string
    {
        try {
            if (preg_match('/^(\d{2})(\d{2})(\d{2})$/', $date, $m)) {
                $annee = (int)$m[3] < 50 ? '20' . $m[3] : '19' . $m[3];
                return "{$annee}-{$m[2]}-{$m[1]}";
            }
            return now()->format('Y-m-d');
        } catch (\Throwable $e) {
            return now()->format('Y-m-d');
        }
    }
}
