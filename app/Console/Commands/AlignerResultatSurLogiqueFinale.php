<?php

namespace App\Console\Commands;

use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;
use App\Services\ComptesDeResultat;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Aligne des exercices DÉJÀ clôturés avec l'ancienne logique sur la logique finale :
 *  - aucune pièce de résultat (RES-n) dans l'exercice source : elle est supprimée ;
 *  - dans l'exercice suivant, le report à nouveau (RAN-n, journal RAN) reprend les soldes
 *    de bilan, et UNE seule ligne de résultat (1301 ou 1309) est portée dans le journal OD
 *    (référence RES-n), marquée système. L'ensemble est équilibré.
 *
 * Mode par défaut : SIMULATION (rien n'est écrit).
 * Application : --apply --confirm=ALIGNER-<ids dans le même ordre que l'appel>
 *
 * Chaque exercice est traité dans sa propre transaction : au moindre doute,
 * cet exercice est laissé intact et la commande passe au suivant.
 */
class AlignerResultatSurLogiqueFinale extends Command
{
    protected $signature = 'comptaflow:aligner-resultat
        {exercices* : ID(s) des exercices clôturés à aligner}
        {--apply : Écrire les corrections (sinon simulation)}
        {--confirm= : Confirmation exacte : ALIGNER-<id>-<id>… (mêmes ids, même ordre)}';

    protected $description = 'Aligne les exercices clôturés avec l\'ancienne logique : supprime la pièce RES-n et normalise la ligne de résultat unique du RAN-n.';

    public function handle(): int
    {
        $ids = array_map('intval', (array) $this->argument('exercices'));
        $apply = (bool) $this->option('apply');
        $attendu = 'ALIGNER-' . implode('-', $ids);

        if ($apply && $this->option('confirm') !== $attendu) {
            $this->error("Confirmation refusée. Utilisez exactement --confirm={$attendu}");
            return self::FAILURE;
        }
        if (!$apply) {
            $this->warn('MODE SIMULATION : aucune donnée ne sera modifiée.');
        }

        $echecs = 0;
        foreach ($ids as $id) {
            $this->line('');
            $this->info("=== Exercice {$id} ===");
            try {
                DB::transaction(function () use ($id, $apply) {
                    $this->traiter($id, $apply);
                });
            } catch (\Throwable $e) {
                $echecs++;
                $this->error("Exercice {$id} laissé INTACT : " . $e->getMessage());
            }
        }

        $this->line('');
        if (!$apply) {
            $this->warn("Simulation terminée. Pour appliquer : relancer avec --apply --confirm={$attendu}");
        }
        return $echecs ? self::FAILURE : self::SUCCESS;
    }

    private function lignes(int $companyId, int $exerciceId)
    {
        return EcritureComptable::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('exercices_comptables_id', $exerciceId);
    }

    private function traiter(int $id, bool $apply): void
    {
        $source = ExerciceComptable::withoutGlobalScopes()->find($id);
        if (!$source) throw new RuntimeException('Exercice introuvable.');
        if (!$source->cloturer) {
            throw new RuntimeException('Exercice ouvert : rien à aligner (le bouton Clôturer applique déjà la logique finale).');
        }
        $companyId = (int) $source->company_id;

        $debutSuivant = Carbon::parse($source->date_fin)->addDay()->toDateString();
        $next = ExerciceComptable::withoutGlobalScopes()->where('company_id', $companyId)
            ->whereDate('date_debut', $debutSuivant)->first();
        if (!$next) throw new RuntimeException("Exercice suivant (début {$debutSuivant}) introuvable.");
        if ($next->cloturer) throw new RuntimeException("Exercice suivant {$next->id} clôturé : intervention refusée.");

        $refRes = 'RES-' . $id;
        $refRan = 'RAN-' . $id;

        // 1. Pièce RES à supprimer : uniquement comptes de gestion (6/7/8) et 1301/1309.
        $resRows = $this->lignes($companyId, $id)->where('reference_piece', $refRes)->with('planComptable')->get();
        foreach ($resRows as $l) {
            $num = (string) ($l->planComptable?->numero_de_compte ?? '');
            $ok = in_array(substr($num, 0, 1), ['6', '7', '8'], true)
                || str_starts_with($num, '1301') || str_starts_with($num, '1309');
            if (!$ok) throw new RuntimeException("La pièce {$refRes} contient le compte {$num} : suppression refusée.");
        }

        // 2. Résultat réel = lignes de gestion hors pièce RES.
        $gestion = $this->lignes($companyId, $id)
            ->where(fn ($q) => $q->whereNull('reference_piece')->orWhere('reference_piece', '!=', $refRes))
            ->where(fn ($q) => $q->whereNull('statut')->orWhereIn('statut', ['approved', 'valid']))
            ->whereHas('planComptable', fn ($q) => $q->where(
                fn ($w) => $w->where('numero_de_compte', 'like', '6%')
                    ->orWhere('numero_de_compte', 'like', '7%')
                    ->orWhere('numero_de_compte', 'like', '8%')
            ))->get();
        $resultat = round((float) $gestion->sum('credit') - (float) $gestion->sum('debit'), 2);

        // 3. Report à nouveau existant.
        $ranRows = $this->lignes($companyId, $next->id)->where('reference_piece', $refRan)->with('planComptable')->get();
        if ($ranRows->isEmpty()) throw new RuntimeException("Aucune pièce {$refRan} dans l'exercice {$next->id} : utilisez le bouton Clôturer après réouverture.");
        if ($ranRows->contains(fn ($l) => !(bool) $l->is_ran)) throw new RuntimeException("{$refRan} contient des lignes non identifiées comme report à nouveau.");
        foreach ($ranRows as $l) {
            if (in_array(substr((string) ($l->planComptable?->numero_de_compte ?? ''), 0, 1), ['6', '7', '8'], true)) {
                throw new RuntimeException("{$refRan} contient un compte de classe 6, 7 ou 8.");
            }
        }
        // Report à nouveau (journal RAN) + ligne de résultat (journal OD) forment un tout équilibré.
        $resSuivant = $this->lignes($companyId, $next->id)->where('reference_piece', 'RES-' . $id)->with('planComptable')->get();
        $ensemble = $ranRows->concat($resSuivant);
        $debit = round((float) $ensemble->sum('debit'), 2);
        $credit = round((float) $ensemble->sum('credit'), 2);
        $ecart = round($debit - $credit, 2);

        $this->line(sprintf('Société %d | exercice suivant %d | résultat réel : %s',
            $companyId, $next->id, number_format($resultat, 2, ',', ' ')));
        $this->line(sprintf('Pièce %s : %d ligne(s), débit %s / crédit %s', $refRes, $resRows->count(),
            number_format((float) $resRows->sum('debit'), 2, ',', ' '), number_format((float) $resRows->sum('credit'), 2, ',', ' ')));
        $this->line(sprintf('Report %s + résultat RES-%d : %d ligne(s), débit %s / crédit %s, écart %s', $refRan, $id, $ensemble->count(),
            number_format($debit, 2, ',', ' '), number_format($credit, 2, ',', ' '), number_format($ecart, 2, ',', ' ')));

        // 4. Identifier la ligne de résultat unique du RAN.
        $ligneAAjouter = null;
        $ligneAMarquer = null;
        if (abs($resultat) < 0.01) {
            if (abs($ecart) >= 0.01) throw new RuntimeException('Résultat nul mais RAN déséquilibré : analyse manuelle requise.');
            $this->line('Résultat nul : aucune ligne de résultat attendue.');
        } else {
            $prefixe = $resultat > 0 ? '1301' : '1309';
            $sens = ComptesDeResultat::sens($resultat);
            $candidats = $ensemble->filter(fn ($l) => str_starts_with((string) $l->planComptable->numero_de_compte, $prefixe)
                && abs(round((float) $l->debit - $sens['debit'], 2)) < 0.01
                && abs(round((float) $l->credit - $sens['credit'], 2)) < 0.01);

            if ($candidats->count() === 1 && abs($ecart) < 0.01) {
                $ligneAMarquer = $candidats->first();
                $this->line("Ligne de résultat identifiée : {$prefixe} (à marquer système).");
            } elseif ($candidats->isEmpty() && abs($ecart - $resultat) < 0.01) {
                $compte = ComptesDeResultat::pour($companyId, $resultat);
                if (!$compte) throw new RuntimeException("Le compte {$prefixe} est absent du plan comptable : création automatique interdite.");
                $ligneAAjouter = ['compte' => $compte, 'sens' => $sens];
                $this->warn("Ligne de résultat MANQUANTE : le RAN est déséquilibré exactement du résultat. Ajout d'une ligne {$prefixe}.");
            } else {
                throw new RuntimeException('Situation non reconnue (ligne de résultat introuvable, multiple ou fusionnée avec un autre solde) : analyse manuelle requise.');
            }
        }

        if (!$apply) {
            $this->line($resRows->isEmpty() ? 'Aucune pièce RES à supprimer.' : "Action prévue : supprimer {$resRows->count()} ligne(s) de {$refRes}.");
            return;
        }

        // 5. Application (dans la transaction de l'exercice).
        if ($resRows->isNotEmpty()) {
            $this->lignes($companyId, $id)->where('reference_piece', $refRes)->delete();
        }
        if ($ligneAMarquer || $ligneAAjouter) {
            $journalOd = \App\Services\JournauxSysteme::operationsDiverses($companyId, (int) $source->user_id);
            $annee = Carbon::parse($source->date_fin)->format('Y');
            $debutS = Carbon::parse($source->date_debut);
            $finS = Carbon::parse($source->date_fin);
            $libelle = 'Résultat de l\'exercice ' . ($debutS->year === $finS->year ? $debutS->year : $debutS->year . '-' . $finS->year);
            $champs = [
                'n_saisie' => 'RES-' . $annee, 'n_saisie_user' => 'RES-' . $annee,
                'description_operation' => $libelle, 'reference_piece' => $refRes,
                'code_journal_id' => $journalOd->id,
                'is_ran' => false, 'is_resultat_systeme' => true,
            ];
            if ($ligneAMarquer) {
                $ligneAMarquer->update($champs);
            } else {
                $modele = $ranRows->first();
                EcritureComptable::withoutGlobalScopes()->create($champs + [
                    'date' => $modele->date,
                    'plan_comptable_id' => $ligneAAjouter['compte']->id,
                    'exercices_comptables_id' => $next->id,
                    'debit' => $ligneAAjouter['sens']['debit'],
                    'credit' => $ligneAAjouter['sens']['credit'],
                    'company_id' => $companyId, 'user_id' => $modele->user_id,
                    'plan_analytique' => 0, 'statut' => 'approved',
                ]);
            }
        }

        // 6. Vérification finale : sinon la transaction est annulée.
        $report = $this->lignes($companyId, $next->id)->where('reference_piece', $refRan)->get();
        $resultatFinal = $this->lignes($companyId, $next->id)->where('reference_piece', $refRes)->get();
        $tout = $report->concat($resultatFinal);
        $d = round((float) $tout->sum('debit'), 2);
        $c = round((float) $tout->sum('credit'), 2);
        if (abs($d - $c) >= 0.01) throw new RuntimeException(sprintf('Report + résultat non équilibrés après correction (D %.2f / C %.2f) : annulé.', $d, $c));
        if ($report->contains(fn ($l) => (bool) $l->is_resultat_systeme)) {
            throw new RuntimeException('Le report à nouveau contient encore une ligne de résultat : annulé.');
        }
        $attendu = abs($resultat) < 0.01 ? 0 : 1;
        if ($resultatFinal->count() !== $attendu
            || $resultatFinal->contains(fn ($l) => (bool) $l->is_ran || !(bool) $l->is_resultat_systeme)) {
            throw new RuntimeException('Ligne de résultat incorrecte après correction : annulé.');
        }
        if ($this->lignes($companyId, $id)->where('reference_piece', $refRes)->exists()) {
            throw new RuntimeException("La pièce {$refRes} existe encore dans l'exercice source : annulé.");
        }
        $this->info('Corrigé et vérifié : report (RAN) équilibré avec la ligne de résultat unique (OD), source sans pièce RES.');
    }
}
