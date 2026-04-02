<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\IaMapping;
use App\Models\IaLog;
use App\Models\PlanComptable;
use App\Models\PlanTiers;
use App\Models\Company;

class IaController extends Controller
{
    private \App\Services\VertexAiService $vertexAiService;

    public function __construct()
    {
        $this->middleware('auth');
        $this->vertexAiService = new \App\Services\VertexAiService();
    }

    public function dashboard(Request $request)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $stats = [
            'total' => IaLog::where('company_id', $companyId)->count(),
            'success' => IaLog::where('company_id', $companyId)->where('status', 'success')->count(),
            'error' => IaLog::where('company_id', $companyId)->where('status', 'error')->count(),
            'corrected' => IaLog::where('company_id', $companyId)->where('status', 'corrected')->count(),
            'avg_tokens' => IaLog::where('company_id', $companyId)
                ->where('status', 'success')
                ->selectRaw('AVG(prompt_tokens + response_tokens) as avg')
                ->first()->avg ?? 0,
        ];

        $recentLogs = IaLog::where('company_id', $companyId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return view('admin.ia.dashboard', compact('stats', 'recentLogs'));
    }

    /**
     * Traite une facture scannée via Vertex AI Gemini Vision.
     */
    public function traiterFacture(Request $request)
    {
        // 0. Augmentation des ressources pour PHP
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        try {
            $user = Auth::user();
            $companyId = session('current_company_id', $user->company_id);

            \Illuminate\Support\Facades\Log::info("Début ia-traitement", [
                'user' => $user->email,
                'company_id' => $companyId
            ]);


            // Validation stricte du fichier
            $request->validate([
                'facture' => 'required|file|mimes:jpeg,jpg,png,pdf|max:10240',
            ]);

            $image = $request->file('facture');
            $image_hash = md5_file($image->getPathname()) . '_' . $image->getSize();
            $cache_key = "ia_analysis_{$companyId}_{$image_hash}";

            \Illuminate\Support\Facades\Log::info("Fichier reçu et validé", [
                'name' => $image->getClientOriginalName(),
                'size' => $image->getSize(),
                'hash' => $image_hash
            ]);


            // 1. Préparation de l'image / PDF
            $raw_image_data = file_get_contents($image->getPathname());
            $extension = strtolower($image->getClientOriginalExtension());
            
            if ($extension === 'pdf') {
                $image_data = base64_encode($raw_image_data);
                $mime_type = "application/pdf";
            } else {
                \Illuminate\Support\Facades\Log::info("Compression d'image...");
                $compressed_image = $this->compressImage($image->getPathname());
                $image_data = base64_encode($compressed_image);
                $mime_type = "image/jpeg";
            }

            \Illuminate\Support\Facades\Log::info("Encodage Base64 OK", [
                'mime' => $mime_type,
                'data_length' => strlen($image_data)
            ]);


            // 2. Récupération du contexte métier
            $planComptableContext = $this->buildPlanComptableContext($companyId);
            $tiersContext = $this->buildTiersContext($companyId);
            $mappingsContext = $this->buildMappingsContext($companyId);
            $companyName = Company::find($companyId)->raison_sociale ?? 'Mon Entreprise';

            \Illuminate\Support\Facades\Log::info("Contexte métier récupéré");


            // 3. Construction du prompt enrichi (Master Prompt preservé)
            $journalCode = $request->input('journal_code', 'AC'); // Défaut à AC si non fourni
            $prompt = $this->buildPrompt($planComptableContext, $tiersContext, $mappingsContext, $companyName, $journalCode);

            // 4. Appel Vertex AI via le Service
            \Illuminate\Support\Facades\Log::info("Appel Vertex AI Service...");
            $result = $this->vertexAiService->analyzeInvoice($image_data, $mime_type, $prompt);
            \Illuminate\Support\Facades\Log::info("Réponse Vertex AI reçue", ['has_error' => isset($result['error'])]);


            if (isset($result['error'])) {
                $rawResponse = $result['raw_response'] ?? null;
                
                IaLog::create([
                    'company_id' => $companyId,
                    'user_id' => $user->id,
                    'image_hash' => $image_hash,
                    'image_nom' => $image->getClientOriginalName(),
                    'status' => 'error',
                    'erreur_message' => substr($result['error'], 0, 250),
                    'json_brut' => $rawResponse ? json_encode(['raw_response' => substr($rawResponse, 0, 2000)]) : json_encode($result['details'] ?? []),
                ]);

                $statusCode = ($result['http_code'] == 429) ? 429 : 500;
                return response()->json([
                    'error' => $result['error'],
                    'details' => $result['details'] ?? null,
                    'http_code' => $result['http_code'] ?? null,
                    'raw_response' => $rawResponse
                ], $statusCode);
            }

            $data = $result['data'];
            $json_brut = json_encode($data);

            // Log du succès
            IaLog::create([
                'company_id' => $companyId,
                'user_id' => $user->id,
                'image_hash' => $image_hash,
                'image_nom' => $image->getClientOriginalName(),
                'json_brut' => $json_brut,
                'status' => 'success',
            ]);

            Cache::put($cache_key, $data, 3600);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Fichier invalide : ' . implode(', ', $e->errors()['facture'] ?? [])], 422);
        } catch (\Throwable $e) {
            // Log de l'erreur fatale pour le diagnostic serveur
            \Illuminate\Support\Facades\Log::error("IA Controller Error 500: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => "Erreur Serveur (500) : " . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }

    }

    /**
     * Test de connectivité Vertex AI
     */
    public function testVertexAiConnection()
    {
        $test = \App\Services\VertexAiService::testConnection();
        $config = \App\Services\VertexAiService::getConfig();
        $credsPath = env('GOOGLE_APPLICATION_CREDENTIALS');
        $fallbackExists = file_exists(base_path('credentials.json'));
        
        $env = [
            'php_version' => PHP_VERSION,
            'extensions' => [
                'gd' => extension_loaded('gd'),
                'curl' => extension_loaded('curl'),
                'fileinfo' => extension_loaded('fileinfo'),
            ],
            'google_creds_env' => $credsPath,
            'google_creds_env_exists' => $credsPath ? file_exists(base_path($credsPath)) : false,
            'google_creds_fallback_exists' => $fallbackExists,
        ];



        return response()->json([
            'status' => $test['status'],
            'vertex_test' => $test,
            'config' => $config,
            'env' => $env
        ]);
    }


    /**
     * Enregistre les corrections apportées par l'utilisateur (apprentissage).
     */
    public function enregistrerCorrection(Request $request)
    {
        $user = Auth::user();
        $companyId = session('current_company_id', $user->company_id);

        $request->validate([
            'tiers_nom' => 'required|string|max:255',
            'compte_numero' => 'required|string|max:20',
            'compte_libelle' => 'nullable|string|max:255',
            'ia_log_id' => 'nullable|integer|exists:ia_logs,id',
        ]);

        // Enregistrement du mapping pour apprentissage futur
        IaMapping::findOrCreateForTiers(
            $companyId,
            $request->tiers_nom,
            $request->compte_numero,
            $request->compte_libelle
        );

        // Mise à jour du log si fourni
        if ($request->ia_log_id) {
            IaLog::where('id', $request->ia_log_id)
                ->where('company_id', $companyId)
                ->update(['status' => 'corrected']);
        }

        return response()->json(['success' => true, 'message' => 'Correction enregistrée. L\'IA apprendra de cette correction.']);
    }

    /**
     * Construit le contexte du Plan Comptable pour le prompt.
     * Limite aux comptes de niveau 4+ pour éviter la surcharge.
     */
    private function buildPlanComptableContext(int $companyId): string
    {
        // On récupère en priorité les comptes de charges (6), tiers (4) et immo (2)
        $comptes = PlanComptable::where('company_id', $companyId)
            ->where(function($query) {
                $query->where('numero_de_compte', 'LIKE', '6%')
                      ->orWhere('numero_de_compte', 'LIKE', '4%')
                      ->orWhere('numero_de_compte', 'LIKE', '2%')
                      ->orWhere('numero_de_compte', 'LIKE', '5%')
                      ->orWhere('numero_de_compte', 'LIKE', '7%');
            })
            ->whereRaw('LENGTH(numero_de_compte) >= 4')
            ->orderBy('numero_de_compte')
            ->limit(300) // Réduit de 1000 à 300 pour la vitesse et éviter la troncature
            ->get(['numero_de_compte', 'intitule']);

        if ($comptes->isEmpty()) {
            return "Plan comptable non disponible. Utiliser les comptes SYSCOHADA standard.";
        }

        $lines = $comptes->map(fn($c) => "COMPTE : {$c->numero_de_compte} - CATEGORIE : {$c->intitule}")->join("\n");
        return "PLAN COMPTABLE DE L'ENTREPRISE (Note: 'FR.' dans un libellé de compte général type '200000' ne signifie PAS forcément un compte fournisseur, vérifiez toujours le premier chiffre: 6=Charge, 4=Tiers) :\n{$lines}";
    }

    /**
     * Construit le contexte des Tiers pour le prompt.
     */
    private function buildTiersContext(int $companyId): string
    {
        $tiers = PlanTiers::where('company_id', $companyId)
            ->limit(100)
            ->get(['intitule', 'type_de_tiers', 'numero_de_tiers']);

        if ($tiers->isEmpty()) {
            return "Aucun tiers enregistré.";
        }

        $lines = $tiers->map(function ($t) {
            return "- {$t->intitule} [{$t->type_de_tiers}] (Compte: {$t->numero_de_tiers})";
        })->join("\n");

        return "TIERS EXISTANTS DANS LE SYSTÈME :\n{$lines}\n\nSi le tiers de la facture correspond à l'un de ces noms, utilise le nom exact.";
    }

    /**
     * Construit les hints de mapping appris pour le prompt.
     */
    private function buildMappingsContext(int $companyId): string
    {
        $mappings = IaMapping::where('company_id', $companyId)
            ->orderByDesc('utilisations')
            ->limit(50)
            ->get();

        if ($mappings->isEmpty()) {
            return '';
        }

        $lines = $mappings->map(fn($m) => "- Fournisseur '{$m->tiers_nom}' → Compte {$m->compte_numero} ({$m->compte_libelle})")->join("\n");
        return "ASSOCIATIONS APPRISES (priorité haute) :\n{$lines}";
    }

    private function buildPrompt(string $planComptable, string $tiers, string $mappings, string $companyName, string $journalCode = 'AC'): string
    {
        return <<<PROMPT
Tu es un expert-comptable SYSCOHADA pour "$companyName".
Analyse ce document (PDF/Image) avec une précision chirurgicale.

CONSIGNES RELATIVES AUX DONNÉES (CRITIQUE) :
1. RÉFÉRENCE : Le champ "reference" DOIT être au format "FACT N°" suivi du numéro (ex: FACT N° 12345). Si aucun numéro n'est trouvé, mets "FACT N° - ".
2. LIBELLÉS (intitule) - FORMAT STRICT : 
   - Pour les Factures/Charges : Préfixe "FACT N° {numéro}/" suivi du libellé OCR LITTÉRAL.
   - Pour les Règlements/Paiements : Préfixe "FACT N° {numéro}/RGLT/" suivi du libellé OCR LITTÉRAL.
   - Pour les Opérations Diverses (OD) : Préfixe "FACT N° {numéro}/OD/" suivi du libellé OCR LITTÉRAL.
   - Remplace "{numéro}" par le numéro réel de la facture trouvé dans le document.
   - LITTÉRALITÉ ABSOLUE : Le texte doit être EXACTEMENT celui écrit sur le document. NE PAS RÉSUMER. NE PAS INVENTER.
   - CASSE DU LIBELLÉ : Le libellé après le "/" doit être en **Sentence case** (Première lettre en majuscule, le reste en minuscule).
   - Ne mets PAS de code journal dans l'intitulé.
   - Exemple Charge : "FACT N° 1334/Achat de viande"
   - Exemple Règlement : "FACT N° 456/RGLT/Paiement facture"
   - Exemple OD : "FACT N° 789/OD/Salaire Axel"
3. PLUSIEURS LIGNES D'ÉCRITURE : Si le document comporte plusieurs lignes de dépenses/opérations distinctes, tu DOIS créer une ligne d'écriture pour chaque opération réelle avec son montant et son libellé LITTÉRAL respectif.
4. AUCUNE REFORMULATION : Si le document dit "Achat de consommables divers", utilise ce texte exact, ne résume pas en "Divers achats".
5. RÉPONDS UNIQUEMENT EN JSON PUR (PAS DE MARKDOWN).

Schema JSON attendu :
{
  "est_facture": true,
  "statut_lecture": "lisible",
  "tiers": "Nom Exact",
  "date": "AAAA-MM-JJ",
  "reference": "FACT N° 000",
  "montant_ht": 0,
  "montant_tva": 0,
  "montant_ttc": 0,
  "devise": "XOF",
  "ecriture": [
    {"compte": "601100", "intitule": "Fact N°/libellé exact", "debit": 100, "credit": 0},
    {"compte": "401100", "intitule": "Fact N°/nom du tiers", "debit": 0, "credit": 100}
  ],
  "confiance": 0.95,
  "analyse": "..."
}
PROMPT;
    }



    /**
     * Compresse l'image si nécessaire pour éviter les timeouts API.
     */
    private function compressImage(string $path, int $maxWidthPx = 800, int $quality = 65): string
    {
        if (!function_exists('imagecreatefromjpeg') || !function_exists('imagejpeg') || !function_exists('getimagesize')) {
            return \file_get_contents($path);
        }

        $info = @\getimagesize($path);
        if (!$info) return \file_get_contents($path);

        [$w, $h, $type] = $info;
        
        try {
            $src = match ($type) {
                IMAGETYPE_JPEG => \imagecreatefromjpeg($path),
                IMAGETYPE_PNG  => \imagecreatefrompng($path),
                default        => null,
            };
        } catch (\Throwable $e) {
            return \file_get_contents($path);
        }
        
        if (!$src) return \file_get_contents($path);

        if ($w > $maxWidthPx) {
            $ratio = $maxWidthPx / $w;
            $nw = $maxWidthPx; $nh = (int)($h * $ratio);
            $dst = \imagecreatetruecolor($nw, $nh);
            \imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
            \imagedestroy($src);
            $src = $dst;
        }

        \ob_start();
        \imagejpeg($src, null, $quality);
        $data = \ob_get_clean();
        \imagedestroy($src);
        
        return $data;
    }
}
