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
use App\Services\VertexAiService;

class IaController extends Controller
{
    private VertexAiService $vertexAiService;

    public function __construct()
    {
        $this->middleware('auth');
        $this->vertexAiService = new VertexAiService();
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
     * Injecte le Plan Comptable et les Tiers de l'entreprise dans le prompt.
     *
     * CHANGEMENTS vs version Gemini API :
     * - Utilise VertexAiService à la place de callGeminiApi direct
     * - ADC authentication au lieu de clé API
     * - Pas besoin de retry sur les modèles (Vertex AI gère ça)
     * - Logging amélioré via Vertex AI
     */
    public function traiterFacture(Request $request)
    {
        try {
            $user = Auth::user();
            $companyId = session('current_company_id', $user->company_id);

            // Validation stricte du fichier
            $request->validate([
                'facture' => 'required|file|mimes:jpeg,jpg,png,pdf|max:10240',
            ]);

            $image = $request->file('facture');
            $image_hash = md5_file($image->getPathname()) . '_' . $image->getSize();
            $cache_key = "ia_analysis_{$companyId}_{$image_hash}";

            // Cache optionnel (décommenter si souhaité)
            /*
            if (Cache::has($cache_key)) {
                $cachedData = Cache::get($cache_key);
                if (is_array($cachedData)) {
                    $cachedData['message'] = 'Résultat récupéré du cache';
                    $cachedData['from_cache'] = true;
                    return response()->json($cachedData);
                }
            }
            */

            // 1. Préparation de l'image / PDF
            $raw_image_data = file_get_contents($image->getPathname());
            $extension = strtolower($image->getClientOriginalExtension());
            
            if ($extension === 'pdf') {
                $image_data = base64_encode($raw_image_data);
                $mime_type = "application/pdf";
            } else {
                $compressed_image = $this->compressImage($image->getPathname());
                $image_data = base64_encode($compressed_image);
                $mime_type = "image/jpeg";
            }

            // 2. Récupération du contexte métier
            $planComptableContext = $this->buildPlanComptableContext($companyId);
            $tiersContext = $this->buildTiersContext($companyId);
            $mappingsContext = $this->buildMappingsContext($companyId);
            $companyName = Company::find($companyId)->raison_sociale ?? 'Mon Entreprise';

            // 3. Construction du master prompt enrichi
            $prompt = $this->buildPrompt($planComptableContext, $tiersContext, $mappingsContext, $companyName);

            // 4. CHANGEMENT MAJEUR : Utiliser VertexAiService au lieu de Gemini API direct
            $result = $this->vertexAiService->analyzeInvoice($image_data, $mime_type, $prompt);

            // Gestion des erreurs
            if (isset($result['error'])) {
                IaLog::create([
                    'company_id' => $companyId,
                    'user_id' => $user->id,
                    'image_hash' => $image_hash,
                    'image_nom' => $image->getClientOriginalName(),
                    'status' => 'error',
                    'erreur_message' => $result['error'],
                    'json_brut' => json_encode($result['details'] ?? []),
                ]);

                return response()->json([
                    'error' => $result['error'],
                    'details' => $result['details'] ?? null,
                    'http_code' => $result['http_code'] ?? null
                ], 500);
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

            // Mise en cache
            Cache::put($cache_key, $data, 3600);

            return response()->json($data);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Fichier invalide : ' . implode(', ', $e->errors()['facture'] ?? [])], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
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
        $test = VertexAiService::testConnection();
        $config = VertexAiService::getConfig();

        return response()->json([
            'test' => $test,
            'config' => $config
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

        return response()->json(['success' => true, 'message' => 'Correction enregistrée.']);
    }

    /**
     * Construit le contexte du Plan Comptable pour le prompt.
     */
    private function buildPlanComptableContext(int $companyId): string
    {
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
            ->limit(1000)
            ->get(['numero_de_compte', 'intitule']);

        if ($comptes->isEmpty()) {
            return "Plan comptable non disponible. Utiliser les comptes SYSCOHADA standard.";
        }

        $lines = $comptes->map(fn($c) => "COMPTE : {$c->numero_de_compte} - CATEGORIE : {$c->intitule}")->join("\n");
        return "PLAN COMPTABLE DE L'ENTREPRISE :\n{$lines}";
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

        return "TIERS EXISTANTS :\n{$lines}";
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

        $lines = $mappings->map(fn($m) => "- Fournisseur '{$m->tiers_nom}' → Compte {$m->compte_numero}")->join("\n");
        return "ASSOCIATIONS APPRISES :\n{$lines}";
    }

    private function buildPrompt(string $planComptable, string $tiers, string $mappings, string $companyName): string
    {
        $mappingsSection = $mappings ? "\n\n{$mappings}" : '';

        return <<<PROMPT
Tu es un Expert-Comptable SYSCOHADA senior, spécialisé dans la lecture et la comptabilisation de documents financiers en Côte d'Ivoire (zone OHADA).

{$planComptable}

{$tiers}{$mappingsSection}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
⚠️ PRINCIPE FONDAMENTAL :
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Les utilisateurs n'uploadent QUE des documents comptables.
Par conséquent, TON BIAIS PAR DÉFAUT EST : "est_facture": true.

Rejette uniquement si :
1. Document totalement non comptable (photo personnelle, paysage, animal)
2. Fichier corrompu ou 100% noir/vierge

TOUT LE RESTE EST UNE FACTURE (même partiellement lisible à 30%).

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
RÈGLES D'EXTRACTION :
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

- Si montant TTC sans HT → Calcule HT = TTC / 1.18 (TVA 18% Côte d'Ivoire)
- Si montant HT → Calcule TVA = HT × 0.18
- Si date illisible → Aujourd'hui (format AAAA-MM-JJ)
- Si tiers illisible → "Fournisseur inconnu"
- 1 LIGNE FACTURE = 1 OBJET dans "ecriture" (jamais regroupe)
- Débit = Charges (Classe 6) ou Clients (411xxx)
- Crédit = Fournisseurs (401xxx) ou Produits (Classe 7)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
FORMAT DE RÉPONSE (JSON STRICT, AUCUN TEXTE) :
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

{\n  \"est_facture\": true,\n  \"statut_lecture\": \"lisible|partiel|illisible\",\n  \"type_rejet\": \"none\",\n  \"explication_rejet\": \"Facture acceptée\",\n  \"type_document\": \"Facture|Reçu|Note de frais|Bon|Relevé\",\n  \"tiers\": \"Nom du fournisseur\",\n  \"date\": \"AAAA-MM-JJ\",\n  \"reference\": \"Numéro de pièce ou vide\",\n  \"montant_ht\": 0,\n  \"montant_tva\": 0,\n  \"montant_ttc\": 0,\n  \"devise\": \"XOF\",\n  \"ecriture\": [\n    {\"compte\": \"601000\", \"intitule\": \"Libellé\", \"debit\": 0, \"credit\": 0},\n    {\"compte\": \"401000\", \"intitule\": \"Fournisseur\", \"debit\": 0, \"credit\": 0}\n  ],\n  \"analyse\": \"Brève description de l'analyse\"\n}

ANALYSE LA FACTURE ET RETOURNE LE JSON UNIQUEMENT.
PROMPT;
    }

    /**
     * Compresse l'image si nécessaire pour éviter les timeouts API.
     */
    private function compressImage(string $path, int $maxWidthPx = 1000, int $quality = 70): string
    {
        $info = @getimagesize($path);
        if (!$info) return file_get_contents($path);

        [$w, $h, $type] = $info;
        
        try {
            $src = match ($type) {
                IMAGETYPE_JPEG => imagecreatefromjpeg($path),
                IMAGETYPE_PNG  => imagecreatefrompng($path),
                default        => null,
            };
        } catch (\Throwable $e) {
            return file_get_contents($path);
        }
        
        if (!$src) return file_get_contents($path);

        // Redimensionner si trop large
        if ($w > $maxWidthPx) {
            $ratio = $maxWidthPx / $w;
            $nw = $maxWidthPx; 
            $nh = (int)($h * $ratio);
            $dst = imagecreatetruecolor($nw, $nh);
            imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagedestroy($src);
            $src = $dst;
        }

        ob_start();
        imagejpeg($src, null, $quality);
        $data = ob_get_clean();
        imagedestroy($src);
        
        return $data;
    }
}
