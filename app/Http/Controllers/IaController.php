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
    public function __construct()
    {
        $this->middleware('auth');
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
     * Traite une facture scannée via l'API Gemini.
     * Injecte le Plan Comptable et les Tiers de l'entreprise dans le prompt.
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

            /* On commente ce bloc pour forcer un nouveau scan IA à chaque fois
            if (Cache::has($cache_key)) {
                $cachedData = Cache::get($cache_key);
                if (is_array($cachedData)) {
                    $cachedData['message'] = 'Résultat récupéré du cache';
                    $cachedData['from_cache'] = true;
                    return response()->json($cachedData);
                }
                return response()->json([
                    'message' => 'Résultat récupéré du cache',
                    'data' => $cachedData,
                    'from_cache' => true,
                ]);
            }
            */

            // --- CONFIGURATION ---
            $api_key = env('GEMINI_API_KEY');
            if (!$api_key) {
                return response()->json(['error' => 'Clé API Gemini manquante dans le fichier .env'], 500);
            }
            $model = "gemini-2.5-flash";
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";

            // 1. Préparation de l'image (Compression automatique pour stabilité)
            $raw_image_data = file_get_contents($image->getPathname());
            $compressed_image = $this->compressImage($image->getPathname());
            $image_data = base64_encode($compressed_image);
            $mime_type = "image/jpeg"; // On force le JPEG après compression pour Gemini

            // 2. Récupération du contexte métier
            $planComptableContext = $this->buildPlanComptableContext($companyId);
            $tiersContext = $this->buildTiersContext($companyId);
            $mappingsContext = $this->buildMappingsContext($companyId);
            $companyName = Company::find($companyId)->raison_sociale ?? 'Mon Entreprise';

            // 3. Construction du prompt enrichi
            $prompt = $this->buildPrompt($planComptableContext, $tiersContext, $mappingsContext, $companyName);

            // 4. Payload pour Gemini
            $payload = [
                "contents" => [
                    [
                        "parts" => [
                            ["text" => $prompt],
                            [
                                "inlineData" => [
                                    "mimeType" => $mime_type,
                                    "data" => $image_data
                                ]
                            ]
                        ]
                    ]
                ],
                "generationConfig" => [
                    "temperature" => 0.1
                ]
            ];

            \Illuminate\Support\Facades\Log::debug("Gemini Request URL: " . $url);
            \Illuminate\Support\Facades\Log::debug("Gemini Request Payload: " . json_encode($payload));

            // 5. Appel API avec retry exponentiel anti-429
            $result = $this->callGeminiApi($url, $payload, $api_key);

            if (isset($result['error'])) {
                // Log de l'erreur avec JSON brut si disponible
                IaLog::create([
                    'company_id' => $companyId,
                    'user_id' => $user->id,
                    'image_hash' => $image_hash,
                    'image_nom' => $image->getClientOriginalName(),
                    'status' => 'error',
                    'erreur_message' => $result['error'],
                    'json_brut' => $result['raw'] ?? json_encode($result['details'] ?? null),
                ]);
                return response()->json([
                    'error' => $result['error'],
                    'details' => $result['details'] ?? null,
                    'debug_url' => str_replace($api_key, '***', $url)
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

            // Mise en cache pour 1 heure
            Cache::put($cache_key, $data, 3600);

            return response()->json($data);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Fichier invalide : ' . implode(', ', $e->errors()['facture'] ?? [])], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
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
            ->limit(1000) // On peut se permettre d'en envoyer plus
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

    /**
     * Construit le prompt complet pour Gemini.
     */
    private function buildPrompt(string $planComptable, string $tiers, string $mappings, string $companyName): string
    {
        $mappingsSection = $mappings ? "\n\n{$mappings}" : '';

        return <<<PROMPT
Tu es un expert-comptable SYSCOHADA Côte d'Ivoire. Analyse cette pièce comptable.

{$planComptable}

{$tiers}{$mappingsSection}

RÈGLES D'EXTRACTION STRICTE (LITÉRALE) :
1. RÈGLE 1 LIGNE = 1 OBJET JSON : Tu dois créer un objet séparé dans le tableau "ecriture" pour CHAQUE ligne visible dans le tableau de la facture. INTERDICTION FORMELLE de regrouper plusieurs prestations sur une même ligne.
2. FIDÉLITÉ DES LIBELLÉS (COPIER-COLLER) : Recopie le texte EXACT et COMPLET de la colonne "Désignation" pour chaque ligne. 
   - INTERDICTION de résumer ou de changer un mot.
   - INTERDICTION d'ajouter la date, le numéro de pièce ou le nom du client dans le libellé de la ligne.
3. LECTURE LITÉRALE DES MONTANTS : Recopie les montants EXACTEMENT comme ils sont écrits dans les colonnes. NE FAIS AUCUN CALCUL.
4. INTERDICTIONS FORMELLES :
   - INTERDIT de calculer la TVA ou le Hors-Taxe par déduction (ne divise jamais par 1.18). Si la TVA n'est pas écrite explicitement sur sa ligne, le montant TVA est 0.
   - INTERDIT d'utiliser des décimales (virgules) si elles ne sont pas imprimées. Utilise des nombres entiers ronds.
   - INTERDIT d'inventer des lignes de type "Remise" ou "Ajustement".
5. COMPTES : 
   - DÉBIT (Charges) : Utilise obligatoirement un compte de Classe 6.
   - CRÉDIT (Tiers) : Identifie le rôle :
     * Si l'entreprise "{$companyName}" est le DESTINATAIRE (reçoit la facture), utilise un compte de type FOURNISSEUR (commençant par '401').
     * Si l'entreprise "{$companyName}" est l'ÉMETTEUR (fournit le service), utilise un compte de type CLIENT (commençant par '411').
6. TIERS (IDENTIFICATION) : 
   - Recherche le nom du tiers (fournisseur ou client) sur la facture. 
   - Si tu le trouves dans la liste fournie ci-dessus, utilise son compte exact.
   - S'IL N'EST PAS DANS LA LISTE : Utilise son NOM RÉEL lu sur la facture comme "intitule" dans l'objet JSON de la ligne de compte tiers (ex: "AMA VAISSELLE ET DÉCORATION").
7. TOTAL : Le montant au CRÉDIT du fournisseur (ou débit du client) dans le JSON doit être EXACTEMENT le total TTC écrit sur la facture (ex: 90000).

FORMAT EXIGÉ : Réponds avec UNIQUEMENT le bloc JSON.
{
  "type_document": "Facture|Reçu|Note de frais|Autre",
  "tiers": "Nom exact du fournisseur ou client",
  "date": "AAAA-MM-JJ",
  "reference": "Numéro de pièce",
  "montant_ht": 0,
  "montant_tva": 0,
  "montant_ttc": 0,
  "devise": "XOF",
  "ecriture": [
    {"compte": "601000", "intitule": "Libellé comptable", "debit": 10000, "credit": 0},
    {"compte": "445100", "intitule": "TVA déductible 18%", "debit": 1800, "credit": 0},
    {"compte": "401000", "intitule": "Fournisseurs", "debit": 0, "credit": 11800}
  ],
  "analyse": "Explication brève du choix des comptes"
}
PROMPT;
    }

    /**
     * Appelle l'API Gemini avec retry exponentiel.
     */
    private function callGeminiApi(string $url, array $payload, string $api_key): array
    {
        $max_retries = 5;
        $retry_count = 0;
        $base_delay = 2;
        $http_code = 0;
        $response = '';

        while ($retry_count < $max_retries) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
            if ($http_code !== 200) {
                \Illuminate\Support\Facades\Log::error("Gemini API Error ($http_code): " . $response);
            } else {
                // Log de succès partiel pour debug
                \Illuminate\Support\Facades\Log::info("Gemini API Success. Response length: " . strlen($response));
            }
        
            curl_close($ch);

            if ($http_code == 429 || $http_code == 503) {
                $retry_count++;
                if ($retry_count >= $max_retries) {
                    if ($http_code == 429) {
                        // Vérifier si c'est un problème de Free Tier vs billing
                        $resp = json_decode($response, true);
                        $isFreeT = str_contains($response, 'free_tier');
                        $msg = $isFreeT
                            ? 'Clé API Gemini sur Free Tier (quota 0). Activez la facturation sur console.cloud.google.com pour votre projet Google Cloud.'
                            : 'Quota Gemini dépassé. Réessayez dans quelques minutes.';
                    } else {
                        $msg = 'Le service IA est temporairement surchargé (503). Veuillez réessayer dans quelques instants.';
                    }
                    return ['error' => $msg, 'http_code' => $http_code];
                }
                $delay = $base_delay * pow(2, $retry_count - 1) + rand(1, 3);
                sleep($delay);
                continue;
            }

            if ($http_code === 200) {
                $result = json_decode($response, true);
                if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                    $candidate = $result['candidates'][0];
                    $finishReason = $candidate['finishReason'] ?? 'UNKNOWN';
                    $json_text = $candidate['content']['parts'][0]['text'];
                    
                    \Illuminate\Support\Facades\Log::info("Gemini Model Output: Reason={$finishReason}, Length=" . strlen($json_text));

                    // Nettoyage Markdown (si présent)
                    $json_text = preg_replace('/```(?:json)?\s*(.*?)\s*```/s', '$1', $json_text);
                    
                    // Extraction du premier bloc { ... }
                    $start = strpos($json_text, '{');
                    $end = strrpos($json_text, '}');
                    
                    if ($start !== false && $end !== false) {
                        $json_text = substr($json_text, $start, $end - $start + 1);
                    }

                    // Supprimer d'éventuels caractères invisibles/parasites
                    $json_text = trim($json_text);
                    $json_text = preg_replace('/[\x00-\x1F\x7F]/', '', $json_text);

                    $data = json_decode($json_text, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return ['data' => $data];
                    }
                    
                    // Si la conversion JSON échoue (ex: texte tronqué), on log l'erreur et on réessaie si possible
                    \Illuminate\Support\Facades\Log::warning("Gemini JSON Parse Error (Tentative " . ($retry_count + 1) . "): " . json_last_error_msg() . " | Content: " . substr($json_text, 0, 100) . "...");
                    
                    $retry_count++;
                    if ($retry_count >= $max_retries) {
                        \Illuminate\Support\Facades\Log::error("Gemini JSON Parse Error définitif: " . json_last_error_msg() . " | Content: " . substr($json_text, 0, 100) . "...");
                        return ['error' => 'JSON invalide généré par l\'IA après plusieurs tentatives', 'raw' => $json_text];
                    }
                    
                    $delay = $base_delay * pow(2, $retry_count - 1) + rand(1, 3);
                    sleep($delay);
                    continue;
                }
            }

            break;
        }

        return ['error' => "Erreur API ({$http_code})", 'details' => json_decode($response, true) ?? ['raw' => $response]];
    }

    /**
     * Compresse l'image si nécessaire pour éviter les timeouts API.
     */
    private function compressImage(string $path, int $maxWidthPx = 1000, int $quality = 70): string
    {
        $info = @getimagesize($path);
        if (!$info) return file_get_contents($path);

        [$w, $h, $type] = $info;
        
        // Si le mime type n'est pas supporté par imagecreatefrom..., on renvoie l'original
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
            $nw = $maxWidthPx; $nh = (int)($h * $ratio);
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
