<?php

namespace App\Services;

use App\Models\EcritureComptable;
use Illuminate\Support\Facades\DB;

class TaxValidationService
{
    /**
     * Vérifie si HT + TVA = TTC pour un ensemble d'écritures.
     * 
     * @param array $ecritures
     * @return array [success: bool, errors: array]
     */
    public function validateVatConsistency(array $ecritures)
    {
        $errors = [];
        $totalHt = 0;
        $totalTva = 0;

        foreach ($ecritures as $ecriture) {
            $isVat = isset($ecriture['is_vat']) && $ecriture['is_vat'] === true;
            $montant = ($ecriture['debit'] ?? 0) - ($ecriture['credit'] ?? 0);
            
            if ($isVat) {
                $totalTva += abs($montant);
            } else {
                $totalHt += abs($montant);
            }
        }

        // Si pas de TVA, on ne valide pas cette partie
        if ($totalTva === 0) {
            return ['success' => true, 'errors' => []];
        }

        // Calcul théorique de la TVA (18% standard en zone OHADA/UEMOA)
        // Note: On pourrait rendre ce taux dynamique plus tard
        $expectedTva = round($totalHt * 0.18, 2);
        $diff = abs($totalTva - $expectedTva);

        // Seuil de tolérance pour les arrondis (ex: 1 FCFA)
        if ($diff > 2) {
            $errors[] = "Écart de TVA détecté : Théo. {$expectedTva} vs Réel {$totalTva} (Diff: {$diff})";
        }

        return [
            'success' => count($errors) === 0,
            'errors' => $errors
        ];
    }

    /**
     * Vérifie si une référence de pièce existe déjà pour la même entreprise et le même exercice.
     * 
     * @param string $reference
     * @param int $companyId
     * @param int $exerciceId
     * @param int|null $excludeSaisieId
     * @return bool
     */
    public function isReferenceDuplicate(string $reference, int $companyId, int $exerciceId, $excludeSaisieId = null)
    {
        if (empty($reference) || $reference === '-') {
            return false;
        }

        $query = EcritureComptable::where('company_id', $companyId)
            ->where('exercices_comptables_id', $exerciceId)
            ->where('reference_piece', $reference);

        if ($excludeSaisieId) {
            $query->where('id', '!=', $excludeSaisieId);
        }

        return $query->exists();
    }

    /**
     * Vérifie l'équilibre débit/crédit d'un lot d'écritures.
     * 
     * @param array $ecritures
     * @return bool
     */
    public function checkBalance(array $ecritures)
    {
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($ecritures as $e) {
            $totalDebit += floatval($e['debit'] ?? 0);
            $totalCredit += floatval($e['credit'] ?? 0);
        }

        return abs($totalDebit - $totalCredit) < 0.01;
    }
}
