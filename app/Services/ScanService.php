<?php

namespace App\Services;

use App\Models\PlanComptable;
use App\Models\PlanTiers;
use App\Models\IaMapping;
use App\Models\Company;
use Illuminate\Support\Facades\Log;

class ScanService
{
    /**
     * Construit le prompt enrichi pour l'IA.
     */
    public function buildPrompt(int $companyId, string $journalCode = 'AC'): string
    {
        $planComptableContext = $this->buildPlanComptableContext($companyId);
        $tiersContext = $this->buildTiersContext($companyId);
        $mappingsContext = $this->buildMappingsContext($companyId);
        $companyName = Company::find($companyId)->raison_sociale ?? 'L\'entreprise';

        return "Tu es un expert-comptable SYSCOHADA pour \"$companyName\".
Analyse ce document (PDF/Image) avec une précision chirurgicale.

CONSIGNES RELATIVES AUX DONNÉES (CRITIQUE) :
1. RÉFÉRENCE : Le champ \"reference\" DOIT être au format \"FACT N°\" suivi du numéro (ex: FACT N° 12345). Si aucun numéro n'est trouvé, mets \"FACT N° - \".
2. LIBELLÉS (intitule) - FORMAT STRICT : 
   - Pour les Factures/Charges : Préfixe \"Fact N°/\" suivi du libellé OCR.
   - Pour les Règlements/Paiements : Préfixe \"Fact N°/RGLT/\" suivi du libellé OCR.
   - Pour les Opérations Diverses (OD) : Préfixe \"Fact N°/OD/\" suivi du libellé OCR.
   - Ne mets PAS de code journal dans l'intitulé.
   - Exemple Charge : \"Fact N°/Dépôt Advans\"
   - Exemple Règlement : \"Fact N°/RGLT/Remboursement Coopec\"
   - Exemple OD : \"Fact N°/OD/Salaire Axel\"
3. OCR LITTÉRAL : Le contenu du libellé après le préfixe doit être EXACTEMENT celui écrit sur la facture.
4. PAS D'INVENTION : Ne reformule pas, ne résume pas.
5. RÉPONDS UNIQUEMENT EN JSON PUR (PAS DE MARKDOWN).

PLAN COMPTABLE DE L'ENTREPRISE :
$planComptableContext

$tiersContext

$mappingsContext

Schema JSON attendu :
{
  \"est_facture\": true,
  \"statut_lecture\": \"lisible\",
  \"tiers\": \"Nom Exact\",
  \"date\": \"AAAA-MM-JJ\",
  \"reference\": \"FACT N° 000\",
  \"montant_ht\": 0,
  \"montant_tva\": 0,
  \"montant_ttc\": 0,
  \"devise\": \"XOF\",
  \"ecriture\": [
    {\"compte\": \"601100\", \"intitule\": \"Fact N°/libellé exact\", \"debit\": 100, \"credit\": 0},
    {\"compte\": \"401100\", \"intitule\": \"Fact N°/nom du tiers\", \"debit\": 0, \"credit\": 100}
  ],
  \"confiance\": 0.95,
  \"analyse\": \"...\"
}";
    }

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
            ->limit(300)
            ->get(['numero_de_compte', 'intitule']);

        if ($comptes->isEmpty()) {
            return "Plan comptable non disponible.";
        }

        return $comptes->map(fn($c) => "COMPTE : {$c->numero_de_compte} - {$c->intitule}")->join("\n");
    }

    private function buildTiersContext(int $companyId): string
    {
        $tiers = PlanTiers::where('company_id', $companyId)
            ->limit(100)
            ->get(['intitule', 'type_de_tiers', 'numero_de_tiers']);

        if ($tiers->isEmpty()) {
            return "Aucun tiers enregistré.";
        }

        $lines = $tiers->map(fn($t) => "- {$t->intitule} [{$t->type_de_tiers}] (Compte: {$t->numero_de_tiers})")->join("\n");
        return "TIERS EXISTANTS :\n{$lines}";
    }

    private function buildMappingsContext(int $companyId): string
    {
        $mappings = IaMapping::where('company_id', $companyId)
            ->orderByDesc('utilisations')
            ->limit(50)
            ->get();

        if ($mappings->isEmpty()) return "";

        $lines = $mappings->map(fn($m) => "- {$m->tiers_nom} → Compte {$m->compte_numero}")->join("\n");
        return "ASSOCIATIONS APPRISES :\n{$lines}";
    }

    public function compressImage(string $path, int $maxWidthPx = 800, int $quality = 65): string
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
