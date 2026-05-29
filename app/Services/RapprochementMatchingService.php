<?php

namespace App\Services;

use App\Models\RapprochementBancaire;
use App\Models\LigneReleveBancaire;
use App\Models\PointageRapprochement;
use App\Models\EcritureComptable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * RapprochementMatchingService
 *
 * Algorithme de pré-pointage automatique et calcul des soldes théoriques.
 *
 * Logique du sens bancaire/comptable :
 *   Banque CRÉDIT (argent entrant pour nous)  ↔ Comptabilité DÉBIT  (encaissement)
 *   Banque DÉBIT  (argent sortant pour nous)  ↔ Comptabilité CRÉDIT (paiement)
 */
class RapprochementMatchingService
{
    // Tolérance de date pour le matching (en jours)
    const TOLERANCE_JOURS = 7;

    // ─────────────────────────────────────────────────────────────────────────
    //  AUTO-MATCHING
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Pré-pointage automatique d'une session de rapprochement.
     *
     * @return array ['pointes' => int, 'ambigus' => int, 'non_trouves' => int, 'suggestions' => [...]]
     */
    public function autoMatch(int $rapprochementId): array
    {
        $rapprochement = RapprochementBancaire::with(['lignesReleve', 'compteTresorerie'])->findOrFail($rapprochementId);

        // Charger toutes les écritures non encore pointées pour ce compte/période
        $ecrituresNonPointees = $this->getEcrituresNonPointees($rapprochement);

        $stats = ['pointes' => 0, 'ambigus' => 0, 'non_trouves' => 0, 'suggestions' => []];

        // Traiter chaque ligne de relevé non encore pointée
        $lignesNonPointees = $rapprochement->lignesReleve()
            ->where('statut', 'non_pointe')
            ->orderBy('date_operation')
            ->get();

        foreach ($lignesNonPointees as $ligne) {
            $result = $this->matchLigne($ligne, $ecrituresNonPointees);

            if ($result['statut'] === 'exact') {
                // Un seul candidat → pointage automatique
                $this->creerPointage($rapprochement->id, $ligne, $result['ecriture'], 'auto');
                $ligne->update(['statut' => 'pointe']);
                // Retirer l'écriture du pool des disponibles
                $ecrituresNonPointees = $ecrituresNonPointees->reject(fn($e) => $e->id === $result['ecriture']->id);
                $stats['pointes']++;
            } elseif ($result['statut'] === 'ambigu') {
                // Plusieurs candidats → suggestions pour l'utilisateur
                $stats['ambigus']++;
                $stats['suggestions'][] = [
                    'ligne_id'   => $ligne->id,
                    'libelle'    => $ligne->libelle,
                    'montant'    => $ligne->credit > 0 ? $ligne->credit : $ligne->debit,
                    'date'       => $ligne->date_operation->format('d/m/Y'),
                    'candidats'  => $result['candidats']->map(fn($e) => [
                        'id'        => $e->id,
                        'date'      => $e->date,
                        'libelle'   => $e->description_operation,
                        'montant'   => $e->debit > 0 ? $e->debit : $e->credit,
                        'journal'   => $e->codeJournal?->code_journal ?? '-',
                    ])->values()->toArray(),
                ];
            } else {
                $stats['non_trouves']++;
            }
        }

        return $stats;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  MATCHING D'UNE LIGNE
    // ─────────────────────────────────────────────────────────────────────────

    private function matchLigne(LigneReleveBancaire $ligne, Collection $ecritures): array
    {
        // Montant de la ligne relevé
        $montant    = $ligne->credit > 0 ? (float) $ligne->credit : (float) $ligne->debit;
        $sensCompta = $ligne->credit > 0 ? 'debit' : 'credit'; // sens en comptabilité

        // Fenêtre de date
        $dateMin = $ligne->date_operation->copy()->subDays(self::TOLERANCE_JOURS);
        $dateMax = $ligne->date_operation->copy()->addDays(self::TOLERANCE_JOURS);

        // Filtrer par montant exact + fenêtre de date + bon sens
        $candidats = $ecritures->filter(function ($e) use ($montant, $sensCompta, $dateMin, $dateMax) {
            $dateEcriture = \Carbon\Carbon::parse($e->date);
            if ($dateEcriture->lt($dateMin) || $dateEcriture->gt($dateMax)) return false;

            if ($sensCompta === 'debit') {
                return abs((float) $e->debit - $montant) < 0.01;
            } else {
                return abs((float) $e->credit - $montant) < 0.01;
            }
        })->values();

        if ($candidats->count() === 0) {
            return ['statut' => 'non_trouve'];
        }

        if ($candidats->count() === 1) {
            return ['statut' => 'exact', 'ecriture' => $candidats->first()];
        }

        // Plusieurs candidats : trier par proximité de date puis similarité de libellé
        $candidats = $candidats->sortBy(function ($e) use ($ligne) {
            $jours = abs(\Carbon\Carbon::parse($e->date)->diffInDays($ligne->date_operation));
            $sim   = similar_text(
                mb_strtolower($ligne->libelle ?? ''),
                mb_strtolower($e->description_operation ?? ''),
                $pct
            );
            return $jours - ($pct / 10); // Score : moins de jours d'écart + libellé similaire
        });

        // Si le meilleur candidat est à 0 jour d'écart ET libellé très similaire → auto
        $best = $candidats->first();
        $joursBest = abs(\Carbon\Carbon::parse($best->date)->diffInDays($ligne->date_operation));
        similar_text(
            mb_strtolower($ligne->libelle ?? ''),
            mb_strtolower($best->description_operation ?? ''),
            $pctBest
        );

        if ($joursBest === 0 && $pctBest > 70) {
            return ['statut' => 'exact', 'ecriture' => $best];
        }

        return ['statut' => 'ambigu', 'candidats' => $candidats->take(5)];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  SAUVEGARDE D'UN POINTAGE
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Crée ou met à jour un pointage (manuel ou auto).
     */
    public function savePointage(int $rapprochementId, int $ligneId, int $ecritureId, string $type = 'manuel', ?string $note = null): PointageRapprochement
    {
        $ligne    = LigneReleveBancaire::findOrFail($ligneId);
        $ecriture = EcritureComptable::findOrFail($ecritureId);

        // Calcul de l'écart
        $montantBanque   = (float) $ligne->credit > 0 ? (float) $ligne->credit : (float) $ligne->debit;
        $montantCompta   = (float) $ecriture->debit  > 0 ? (float) $ecriture->debit : (float) $ecriture->credit;
        $ecart           = round(abs($montantBanque - $montantCompta), 2);

        DB::transaction(function () use ($rapprochementId, $ligne, $ecriture, $type, $note, $ecart) {
            PointageRapprochement::updateOrCreate(
                ['rapprochement_id' => $rapprochementId, 'ecriture_comptable_id' => $ecriture->id],
                [
                    'ligne_releve_id'      => $ligne->id,
                    'type_pointage'        => $type,
                    'ecart'                => $ecart,
                    'note'                 => $note,
                    'created_by'           => auth()->id(),
                ]
            );
            $ligne->update(['statut' => 'pointe']);
        });

        return PointageRapprochement::where('rapprochement_id', $rapprochementId)
            ->where('ecriture_comptable_id', $ecriture->id)
            ->first();
    }

    /**
     * Supprime un pointage (annule le rapprochement).
     */
    public function deletePointage(int $pointageId): void
    {
        $pointage = PointageRapprochement::findOrFail($pointageId);
        $ligneId  = $pointage->ligne_releve_id;
        $pointage->delete();
        // Remettre la ligne en non_pointe si plus aucun pointage
        LigneReleveBancaire::where('id', $ligneId)->update(['statut' => 'non_pointe']);
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  CALCUL DES SOLDES THÉORIQUES
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Calcule les statistiques de la session et les soldes théoriques.
     *
     * Formule :
     *   Solde Compta Réel   = Solde Compta Actuel   + Σ opérations banque non saisies en compta
     *   Solde Bancaire Réel = Solde Bancaire Actuel + Σ opérations compta non traitées par la banque
     *
     * Si Solde Compta Réel == Solde Bancaire Réel → ✅ Équilibré
     */
    public function getStats(int $rapprochementId): array
    {
        $r = RapprochementBancaire::with(['lignesReleve', 'pointages'])->findOrFail($rapprochementId);

        // IDs des écritures déjà pointées
        $ecrituresPointeesIds = $r->pointages->pluck('ecriture_comptable_id')->toArray();

        // Écritures compta non pointées
        $ecrituresNonPointees = $this->getEcrituresNonPointees($r)
            ->whereNotIn('id', $ecrituresPointeesIds);

        $totalEcrituresNonPointeesDebit  = $ecrituresNonPointees->sum('debit');
        $totalEcrituresNonPointeesCredit = $ecrituresNonPointees->sum('credit');

        // Lignes relevé non pointées
        $lignesNonPointees = $r->lignesReleve->where('statut', 'non_pointe');
        $totalRelevéNonPointeDebit  = $lignesNonPointees->sum('debit');
        $totalRelevéNonPointeCredit = $lignesNonPointees->sum('credit');

        $soldeBancaireActuel = (float) $r->solde_final_banque;
        $soldeComptaActuel   = (float) $r->solde_initial_compta;

        // Opérations en compta mais pas encore traitées par la banque
        $opComptaNonBanque = $totalEcrituresNonPointeesDebit - $totalEcrituresNonPointeesCredit;

        // Opérations en banque mais pas encore saisies en compta
        $opBanqueNonCompta = $totalRelevéNonPointeCredit - $totalRelevéNonPointeDebit;

        $soldeBancaireReel = $soldeBancaireActuel + $opComptaNonBanque;
        $soldeComptaReel   = $soldeComptaActuel   + $opBanqueNonCompta;

        return [
            'nb_lignes_releve'           => $r->lignesReleve->count(),
            'nb_pointees'                => $r->lignesReleve->where('statut', 'pointe')->count(),
            'nb_non_pointees'            => $lignesNonPointees->count(),
            'nb_ecritures_non_pointees'  => $ecrituresNonPointees->count(),
            'solde_bancaire_actuel'      => $soldeBancaireActuel,
            'solde_compta_actuel'        => $soldeComptaActuel,
            'solde_bancaire_reel'        => round($soldeBancaireReel, 2),
            'solde_compta_reel'          => round($soldeComptaReel,   2),
            'ecart_residuel'             => round($soldeBancaireReel - $soldeComptaReel, 2),
            'equilibre'                  => abs($soldeBancaireReel - $soldeComptaReel) < 0.01,
            // Détail des écarts
            'ecritures_non_pointees'     => $ecrituresNonPointees->values(),
            'lignes_releve_non_pointees' => $lignesNonPointees->values(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  HELPER PRIVÉ
    // ─────────────────────────────────────────────────────────────────────────

    private function getEcrituresNonPointees(RapprochementBancaire $r): Collection
    {
        $pointeesIds = $r->pointages()->pluck('ecriture_comptable_id')->toArray();

        return EcritureComptable::with(['codeJournal', 'planComptable'])
            ->where('company_id', $r->company_id)
            ->where('compte_tresorerie_id', $r->compte_tresorerie_id)
            ->whereBetween('date', [$r->date_debut->format('Y-m-d'), $r->date_fin->format('Y-m-d')])
            ->whereNotIn('id', $pointeesIds)
            ->orderBy('date')
            ->get();
    }

    private function creerPointage(int $rapprochementId, LigneReleveBancaire $ligne, EcritureComptable $ecriture, string $type): void
    {
        $montantBanque = $ligne->credit > 0 ? (float) $ligne->credit : (float) $ligne->debit;
        $montantCompta = $ecriture->debit > 0  ? (float) $ecriture->debit  : (float) $ecriture->credit;

        PointageRapprochement::create([
            'rapprochement_id'      => $rapprochementId,
            'ligne_releve_id'       => $ligne->id,
            'ecriture_comptable_id' => $ecriture->id,
            'type_pointage'         => $type,
            'ecart'                 => round(abs($montantBanque - $montantCompta), 2),
            'created_by'            => auth()->id(),
        ]);
    }
}
