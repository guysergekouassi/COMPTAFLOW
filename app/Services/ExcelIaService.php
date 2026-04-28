<?php

namespace App\Services;

use App\Models\PlanComptable;
use App\Models\PlanTiers;
use App\Models\CodeJournal;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * Service IA spécialisé pour l'analyse et le chat comptable.
 * Supporte : chat texte seul, analyse de fichiers, combiné.
 */
class ExcelIaService
{
    private PythonAiService $pythonAi;

    public function __construct(PythonAiService $pythonAi)
    {
        $this->pythonAi = $pythonAi;
    }

    /**
     * MODE CHAT : répond à une question comptable (avec ou sans fichiers).
     */
    public function chat(string $message, array $visionFiles, int $companyId, array $historique = [], ?int $projetId = null): array
    {
        try {
            $planComptable = $this->getPlanComptableContext($companyId);
            $planTiers     = $this->getPlanTiersContext($companyId);
            $journaux      = $this->getJournauxContext($companyId);

            // 1. INSTRUCTIONS SYSTÈME (L'identité et les règles du projet)
            $identityPrompt = $this->buildSystemPrompt($planComptable, $planTiers, $journaux);
            $instructionsProjet = "";
            $knowledgeBase = "";

            if ($projetId) {
                $projetData = $this->getProjectContextData($projetId, $companyId);
                $instructionsProjet = $projetData['instructions'];
                $knowledgeBase = $projetData['knowledgeBase'];
                $visionFiles = array_merge($visionFiles, $projetData['visionFiles']);
            }

            $systemInstruction = $identityPrompt . $instructionsProjet;

            // 2. CONTEXTE UTILISATEUR (La connaissance et l'historique)
            $userPrompt = $knowledgeBase;
            
            if (!empty($historique)) {
                $userPrompt .= "\n\n## HISTORIQUE DES ÉCHANGES\n";
                foreach ($historique as $h) {
                    $role = $h['role'] === 'user' ? 'Client' : 'Assistant';
                    $userPrompt .= "{$role}: {$h['content']}\n";
                }
            }

            $userPrompt .= "\n\n---\n## REQUÊTE CALCULÉE\n" . $message;
            $userPrompt .= "\n(Rappel : Si je parle du 'fichier' ou des 'données', je parle de la BASE DE CONNAISSANCE ci-dessus).";

            $result = $this->pythonAi->chat($userPrompt, $companyId, 'superviseur');
            
            if (!$result['success']) {
                return $result;
            }

            return ['success' => true, 'reponse' => $result['data']['reponse'] ?? '', 'type' => 'chat'];

        } catch (\Throwable $e) {
            Log::error("[ExcelIA Chat] Erreur: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * MODE ANALYSE : analyse des fichiers pour générer des écritures Sage.
     */
    public function analyser(string $contexteData, array $visionFiles, int $companyId, string $moisCible = 'TOUS', array $historique = [], ?int $projetId = null): array
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(600);

        try {
            $planComptable = $this->getPlanComptableContext($companyId);
            $planTiers     = $this->getPlanTiersContext($companyId);
            $journaux      = $this->getJournauxContext($companyId);

            // Instructions Système (Identity + Project Rules)
            $identityPrompt = $this->buildSystemPrompt($planComptable, $planTiers, $journaux);
            $instructionsProjet = "";
            $knowledgeBase = "";

            if ($projetId) {
                $projetData = $this->getProjectContextData($projetId, $companyId);
                $instructionsProjet = $projetData['instructions'];
                $knowledgeBase = $projetData['knowledgeBase'];
                $visionFiles = array_merge($visionFiles, $projetData['visionFiles']);
            }

            // Contexte d'Analyse (Files + History)
            // On concatène le contexte "immédiat" (fichiers uploadés à l'instant) avec le "Centre de Dépôt"
            $fullContexte = $knowledgeBase . "\n\n## FICHIERS RÉCENTS À ANALYSER :\n" . $contexteData;
            
            $analysisPrompt = $this->buildAnalysePrompt($fullContexte, $planComptable, $planTiers, $journaux, $moisCible, $historique);
            
            $systemInstruction = $identityPrompt . $instructionsProjet;
            $userPrompt = $analysisPrompt;

            $result = $this->pythonAi->chat($userPrompt, $companyId, 'comptabilite');

            if (!$result['success']) {
                return $result;
            }

            return $this->parseReponseIA($result['data']['reponse'] ?? '');

        } catch (\Throwable $e) {
            Log::error("[ExcelIA] Erreur critique: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Récupère dynamiquement les instructions et les fichiers d'un projet.
     */
    private function getProjectContextData(int $projetId, int $companyId): array
    {
        $instructions = "";
        $knowledgeBase = "";
        $visionFiles = [];

        $projet = \App\Models\ExcelIaProjet::with('fichiers')
            ->where('company_id', $companyId)
            ->find($projetId);
        if ($projet) {
            if (!empty($projet->instructions)) {
                $instructions = "\n\n## CONSIGNES PARTICULIÈRES DU PROJET (Règles Métier) :\n" . $projet->instructions;
            }

            if ($projet->fichiers->isNotEmpty()) {
                $knowledgeBase = "\n\n## BASE DE CONNAISSANCE DU PROJET (CENTRE DE DÉPÔT)\n";
                $knowledgeBase .= "Voici les données extraites des fichiers de référence déposés dans le projet. Utilise-les pour guider ton analyse :\n\n";

                $parser = new \App\Services\ExcelParserService();
                foreach ($projet->fichiers as $f) {
                    // Si on a déjà le texte extrait en BDD, on l'utilise directement (Gain de temps massif)
                    if (!empty($f->contenu_extrait)) {
                        $knowledgeBase .= "### FICHIER RÉFÉRENCE : {$f->nom}\n";
                        $knowledgeBase .= $f->contenu_extrait . "\n---\n";
                        continue;
                    }

                    // Fallback : Si pas de cache, on parse et on sauvegarde pour la prochaine fois (Lazy Loading)
                    $absolutePath = \Illuminate\Support\Facades\Storage::disk('local')->path($f->chemin);

                    if (file_exists($absolutePath)) {
                        Log::info("[ExcelIA] Génération cache pour : {$f->nom}");
                        $fileObj = new \Illuminate\Http\UploadedFile($absolutePath, $f->nom, $f->mime, null, true);
                        $parsed = $parser->parse($fileObj);

                        if (!empty($parsed['ia_vision'])) {
                            $visionFiles[] = ['base64' => $parsed['base64'], 'mime' => $parsed['mime'], 'nom' => $parsed['nom']];
                            $knowledgeBase .= "### FICHIER RÉFÉRENCE : {$f->nom} (Mode Vision activé)\n";
                        } else {
                            $extractedText = $parsed['contenu'] ?? 'Fichier vide.';
                            
                            // Sécurité : Tronquer si le contenu d'un seul fichier est gigantesque (> 500k chars)
                            if (strlen($extractedText) > 500000) {
                                $extractedText = substr($extractedText, 0, 500000) . "\n[... Contenu tronqué pour des raisons de limite technique ...]";
                            }

                            $knowledgeBase .= "### FICHIER RÉFÉRENCE : {$f->nom}\n";
                            $knowledgeBase .= $extractedText . "\n---\n";
                            
                            // Sauvegarde en BDD pour les prochains appels
                            try {
                                $f->update(['contenu_extrait' => $extractedText]);
                            } catch (\Exception $e) {
                                Log::error("Échec sauvegarde cache IA: " . $e->getMessage());
                            }
                        }
                    } else {
                        Log::warning("[ExcelIA] Fichier absent sur disque : {$absolutePath}");
                    }
                }
            }
        }

        return [
            'instructions' => $instructions,
            'knowledgeBase' => $knowledgeBase,
            'visionFiles' => $visionFiles
        ];
    }

    // ─── APPELS API ───────────────────────────────────────────────────────────

    private function callGeminiChat(string $prompt, array $visionFiles, string $systemInstruction = ''): array
    {
        $fullPrompt = $systemInstruction . "\n\n" . $prompt;
        $contents = [['parts' => [['text' => $fullPrompt]]]];

        foreach ($visionFiles as $vf) {
            $contents[0]['parts'][] = [
                'inlineData' => [
                    'mimeType' => $vf['mime'],
                    'data' => $vf['base64']
                ]
            ];
        }

        $result = $this->vertexAi->generateContent($contents, ['temperature' => 0.4, 'maxOutputTokens' => 12000]);

        if ($result['has_error']) {
            return ['success' => false, 'error' => $result['error_message']];
        }

        return ['success' => true, 'reponse' => $result['raw_text'] ?? '', 'type' => 'chat'];
    }

    private function callGemini(string $prompt, array $visionFiles, string $systemInstruction = ''): array
    {
        $fullPrompt = $systemInstruction . "\n\n" . $prompt;
        $contents = [['parts' => [['text' => $fullPrompt]]]];

        foreach ($visionFiles as $vf) {
            $contents[0]['parts'][] = [
                'inlineData' => [
                    'mimeType' => $vf['mime'],
                    'data' => $vf['base64']
                ]
            ];
        }

        $result = $this->vertexAi->generateContent($contents, ['temperature' => 0.1, 'maxOutputTokens' => 16000]);

        if ($result['has_error']) {
            return ['success' => false, 'error' => $result['error_message']];
        }

        $texte = $result['raw_text'] ?? '';
        
        // Si VertexAiService a déjà essayé de json_decode le texte (ce qu'il fait dans callVertexApi),
        // on doit peut-être reconstruire le texte ou changer VertexAiService pour qu'il soit plus souple.
        // En fait, VertexAiService->callVertexApi fait déjà regex + json_decode.
        // Si c'est un chat ou une analyse avec rapport, c'est pas forcément du JSON pur.
        
        return $this->parseReponseIA($texte);
    }

    // ─── PROMPTS ──────────────────────────────────────────────────────────────

    /**
     * Prompt système générique — expert comptable SYSCOHADA.
     */
    private function buildSystemPrompt(string $planComptable, string $planTiers, string $journaux): string
    {
        return <<<PROMPT
Tu es un expert-comptable certifié SYSCOHADA révisé (Acte Uniforme OHADA 2017), spécialisé dans la comptabilité des entreprises d'Afrique de l'Ouest (Côte d'Ivoire, Sénégal, Cameroun...).

Tu maîtrises :
- Le référentiel SYSCOHADA révisé 2017 (plan comptable, états financiers)
- L'import Sage 100 Comptabilité (format TXT pipeline)
- Les journaux comptables : achats, ventes, banque, caisse, opérations diverses
- La TVA locale, les charges sociales CNPS, la fiscalité OHADA
- La comptabilité des PME/PMI, associations, cabinets de conseils

## PLAN COMPTABLE DE L'ENTREPRISE ACTIVE
{$planComptable}

## PLAN DES TIERS
{$planTiers}

## CODES JOURNAUX DISPONIBLES
{$journaux}

## RÈGLES DE RÉPONSE
- Réponds en français, de façon professionnelle et pédagogique
- Pour les écritures comptables, utilise le format Sage : JJMMAA;NumFact;Journal;RefPiece;Compte;Libellé;Débit;Crédit;Tiers;
- Si tu génères des écritures, vérifie toujours que Débit = Crédit
- Explique ton raisonnement comptable quand c'est utile
- Si des informations manquent, indique-le clairement plutôt que d'inventer des données
PROMPT;
    }

    /**
     * Prompt pour l'analyse fichiers avec génération d'écritures Sage.
     */
    private function buildAnalysePrompt(string $donnees, string $planComptable, string $planTiers, string $journaux, string $moisCible, array $historique = []): string
    {
        $moisInstruction = $moisCible === 'TOUS'
            ? "Traite TOUS les mois présents dans les données, mois par mois."
            : "Traite UNIQUEMENT le mois de : {$moisCible}.";

        $histoContext = "";
        if (!empty($historique)) {
            $histoContext = "\n\n## CONTEXTE DE LA CONVERSATION PRÉCÉDENTE (Règles à respecter)\n";
            foreach ($historique as $h) {
                $role = $h['role'] === 'user' ? 'Utilisateur' : 'IA';
                $histoContext .= "{$role}: {$h['content']}\n";
            }
        }

        return <<<PROMPT
Tu es un expert-comptable certifié SYSCOHADA révisé. {$moisInstruction}

**IMPORTANT :** Tu dois analyser les données fournies dans le Centre de Dépôt ET respecter scrupuleusement les consignes discutées dans l'historique de la conversation ci-dessous.

{$histoContext}

## FORMAT DE SORTIE OBLIGATOIRE (Sage 100)
Séparateur : point-virgule (;)
Format : JJMMAA;NumFact;Journal;RefPiece;Compte;Libellé;Débit;Crédit;Tiers;
- Date : JJMMAA (6 chiffres, ex: 060125 = 06 janvier 2025)
- Débit/Crédit : entiers sans décimale si montant rond
- Tiers : code 400xxx ou 410xxx, vide si non applicable

## RÈGLES D'ANALYSE (Étape par étape)
1. Analyse le contenu de chaque fichier du Centre de Dépôt.
2. Identifie les flux financiers, les dates, les tiers et les montants.
3. Applique les règles de compte OHADA et les instructions de la conversation.
4. Génère les écritures équilibrées.
5. Produis un rapport détaillé de ton action (comme Claude) : explique ce que tu as fait pour chaque partie, les comptes créés et les choix effectués.

## PLAN COMPTABLE
{$planComptable}

## PLAN DES TIERS
{$planTiers}

## CODES JOURNAUX
{$journaux}

## DONNÉES DU CENTRE DE DÉPÔT
{$donnees}

## INSTRUCTIONS FINALES
- Séparer les mois par : "=== MOIS : [NOM] ==="
- Après chaque mois : Total Débits / Total Crédits / Équilibre
- Terminer par un Rapport de Transparence PÉDAGOGIQUE (expliquant étape par étape ton travail sur les fichiers).
PROMPT;
    }

    // ─── PARSING ──────────────────────────────────────────────────────────────

    private function parseReponseIA(string $texteIA): array
    {
        $ecritures   = [];
        $lignesTxt   = [];
        $rapport     = '';
        $inRapport   = false;
        $moisCourant = '';

        foreach (explode("\n", $texteIA) as $ligne) {
            $ligne = trim($ligne);
            if (empty($ligne)) continue;

            if (preg_match('/^={3,}/', $ligne) || preg_match('/^MOIS\s*:\s*(.+)/i', $ligne, $m)) {
                if (isset($m[1])) $moisCourant = trim($m[1]);
                $lignesTxt[] = $ligne;
                continue;
            }

            if (preg_match('/RAPPORT DE TRANSPARENCE|PHASE 5|COMPTES UTILIS/i', $ligne)) {
                $inRapport = true;
            }

            if ($inRapport) {
                $rapport .= $ligne . "\n";
                continue;
            }

            $parts = explode(';', $ligne);
            if (count($parts) >= 8 && preg_match('/^\d{6}$/', $parts[0])) {
                $ecritures[] = [
                    'date'        => $parts[0],
                    'num_facture' => $parts[1] ?? '',
                    'journal'     => $parts[2] ?? '',
                    'ref_piece'   => $parts[3] ?? '',
                    'compte'      => $parts[4] ?? '',
                    'libelle'     => $parts[5] ?? '',
                    'debit'       => (float) ($parts[6] ?? 0),
                    'credit'      => (float) ($parts[7] ?? 0),
                    'tiers'       => $parts[8] ?? '',
                    'mois'        => $moisCourant,
                ];
                $lignesTxt[] = $ligne;
            } else {
                $lignesTxt[] = $ligne;
            }
        }

        $totalDebit  = array_sum(array_column($ecritures, 'debit'));
        $totalCredit = array_sum(array_column($ecritures, 'credit'));

        return [
            'success'      => true,
            'ecritures'    => $ecritures,
            'rapport'      => $rapport,
            'txt_sage'     => implode("\n", $lignesTxt),
            'nb_ecritures' => count($ecritures),
            'total_debit'  => $totalDebit,
            'total_credit' => $totalCredit,
            'equilibre'    => abs($totalDebit - $totalCredit) < 0.01,
        ];
    }

    // ─── CONTEXTE BDD ─────────────────────────────────────────────────────────

    private function getPlanComptableContext(int $companyId): string
    {
        $comptes = PlanComptable::where('company_id', $companyId)
            ->where(function ($q) {
                $q->where('numero_de_compte', 'LIKE', '2%')
                  ->orWhere('numero_de_compte', 'LIKE', '4%')
                  ->orWhere('numero_de_compte', 'LIKE', '5%')
                  ->orWhere('numero_de_compte', 'LIKE', '6%')
                  ->orWhere('numero_de_compte', 'LIKE', '7%');
            })
            ->orderBy('numero_de_compte')
            ->limit(400)
            ->get(['numero_de_compte', 'intitule']);

        if ($comptes->isEmpty()) return "Plan comptable non disponible.";
        return $comptes->map(fn($c) => "{$c->numero_de_compte} | {$c->intitule}")->join("\n");
    }

    private function getPlanTiersContext(int $companyId): string
    {
        $tiers = PlanTiers::where('company_id', $companyId)
            ->orderBy('numero_de_tiers')
            ->limit(300)
            ->get(['numero_de_tiers', 'intitule']);

        if ($tiers->isEmpty()) return "Plan des tiers non disponible.";
        return $tiers->map(fn($t) => "{$t->numero_de_tiers} | {$t->intitule}")->join("\n");
    }

    private function getJournauxContext(int $companyId): string
    {
        try {
            $journaux = CodeJournal::where('company_id', $companyId)
                ->orderBy('code')
                ->get(['code', 'intitule']);

            if ($journaux->isEmpty()) return "ACH1|Achat\nBQ01|Banque\nCAI1|Caisse\nVEN1|Vente\nOD01|Opérations Diverses";
            return $journaux->map(fn($j) => "{$j->code} | {$j->intitule}")->join("\n");
        } catch (\Throwable $e) {
            return "ACH1|Achat\nBQ01|Banque\nCAI1|Caisse\nVEN1|Vente\nOD01|Opérations Diverses";
        }
    }
}


