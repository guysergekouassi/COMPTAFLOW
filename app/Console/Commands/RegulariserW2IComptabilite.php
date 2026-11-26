<?php

namespace App\Console\Commands;

use App\Models\CodeJournal;
use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;
use App\Models\JournalSaisi;
use App\Models\PlanComptable;
use App\Services\AccountingReportingService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Régularisation exceptionnelle, non rejouable sans nouveau diagnostic, et verrouillée pour W2I.
 *
 * Mode par défaut : diagnostic sans écriture.
 * Application : --apply --confirm=W2I-210-331-332
 */
class RegulariserW2IComptabilite extends Command
{
    protected $signature = 'comptaflow:w2i-regulariser
        {--apply : Appliquer la régularisation après les contrôles}
        {--confirm= : Confirmation exacte requise en mode application}';

    protected $description = 'Contrôle puis régularise la pièce RES et le REPORT À NOUVEAU de W2I (exercices 331 vers 332).';

    public function handle(AccountingReportingService $reporting): int
    {
        $companyId = 210;
        $sourceId = 331;
        $targetId = 332;
        $resultatAttendu = -4183800.00;
        $ranReference = 'RAN-331';
        $resReference = 'RES-331';

        if ($this->option('apply') && $this->option('confirm') !== 'W2I-210-331-332') {
            $this->error('Confirmation refusée. Utilisez exactement --confirm=W2I-210-331-332.');
            return self::FAILURE;
        }

        try {
            $diagnostic = DB::transaction(function () use (
                $companyId, $sourceId, $targetId, $resultatAttendu,
                $ranReference, $resReference, $reporting
            ) {
                $source = ExerciceComptable::where('company_id', $companyId)
                    ->whereKey($sourceId)->lockForUpdate()->firstOrFail();
                $target = ExerciceComptable::where('company_id', $companyId)
                    ->whereKey($targetId)->lockForUpdate()->firstOrFail();

                if (Carbon::parse($source->date_fin)->toDateString() !== '2024-12-31'
                    || Carbon::parse($target->date_debut)->toDateString() !== '2025-01-01') {
                    throw new RuntimeException('Les dates des exercices W2I ne correspondent plus au périmètre validé.');
                }
                if ((bool) $target->cloturer) {
                    throw new RuntimeException('L’exercice cible 332 est clôturé : intervention interrompue.');
                }

                $sourcePending = EcritureComptable::where('company_id', $companyId)
                    ->where('exercices_comptables_id', $sourceId)
                    ->where('statut', 'pending')->count();
                if ($sourcePending) {
                    throw new RuntimeException("L’exercice source contient {$sourcePending} écriture(s) en attente.");
                }

                $resRows = EcritureComptable::where('company_id', $companyId)
                    ->where('exercices_comptables_id', $sourceId)
                    ->where('reference_piece', $resReference)->get();
                if ($resRows->isNotEmpty()) {
                    // Une application déjà réussie doit être reconnue sans jamais rejouer les écritures.
                    $resDebit = round((float) $resRows->sum('debit'), 2);
                    $resCredit = round((float) $resRows->sum('credit'), 2);
                    $existingRan = EcritureComptable::where('company_id', $companyId)
                        ->where('exercices_comptables_id', $targetId)
                        ->where('reference_piece', $ranReference)->get();
                    $ordinaryRows = EcritureComptable::where('company_id', $companyId)
                        ->where('exercices_comptables_id', $targetId)
                        ->where(function ($q) use ($ranReference) {
                            $q->whereNull('reference_piece')->orWhere('reference_piece', '!=', $ranReference);
                        })->get();
                    $ranDebit = round((float) $existingRan->sum('debit'), 2);
                    $ranCredit = round((float) $existingRan->sum('credit'), 2);
                    $ordinaryDebit = round((float) $ordinaryRows->sum('debit'), 2);
                    $ordinaryCredit = round((float) $ordinaryRows->sum('credit'), 2);

                    if (abs($resDebit - abs($resultatAttendu)) >= 0.01
                        || abs($resCredit - abs($resultatAttendu)) >= 0.01
                        || !$source->resultat_passe
                        || $existingRan->count() !== 13
                        || $existingRan->contains(fn ($row) => !(bool) $row->is_ran)
                        || abs($ranDebit - 8301800.00) >= 0.01
                        || abs($ranCredit - 8301800.00) >= 0.01
                        || abs($ranDebit - $ranCredit) >= 0.01
                        || $ordinaryRows->count() !== 51
                        || abs($ordinaryDebit - 8247735.00) >= 0.01
                        || abs($ordinaryCredit - 8247735.00) >= 0.01
                        || abs($ordinaryDebit - $ordinaryCredit) >= 0.01) {
                        throw new RuntimeException('Une pièce RES-331 existe, mais les contrôles de cohérence de la régularisation déjà appliquée échouent. Aucune donnée modifiée.');
                    }

                    return [
                        'already_applied' => true,
                        'source' => $source,
                        'target' => $target,
                        'other_count' => $ordinaryRows->count(),
                        'other_debit' => $ordinaryDebit,
                        'other_credit' => $ordinaryCredit,
                        'ran_count' => $existingRan->count(),
                        'ran_debit' => $ranDebit,
                        'ran_credit' => $ranCredit,
                        'resultat' => $resultatAttendu,
                        'source_flag' => true,
                    ];
                }

                $manualResult = EcritureComptable::where('company_id', $companyId)
                    ->where('exercices_comptables_id', $sourceId)
                    ->where(function ($q) {
                        $q->whereNull('is_resultat_systeme')->orWhere('is_resultat_systeme', false);
                    })
                    ->whereHas('planComptable', function ($q) {
                        $q->where('numero_de_compte', 'like', '1301%')
                            ->orWhere('numero_de_compte', 'like', '1309%');
                    })->count();
                if ($manualResult) {
                    throw new RuntimeException("{$manualResult} mouvement(s) manuel(s) en 1301/1309 : vérification comptable requise, aucun changement effectué.");
                }

                $ranRows = EcritureComptable::where('company_id', $companyId)
                    ->where('exercices_comptables_id', $targetId)
                    ->where('reference_piece', $ranReference)->get();
                if ($ranRows->count() !== 8 || $ranRows->contains(fn ($row) => !(bool) $row->is_ran)) {
                    throw new RuntimeException('La pièce RAN-331 n’est pas exactement constituée des 8 lignes système attendues. Suppression refusée.');
                }

                $otherRows = EcritureComptable::where('company_id', $companyId)
                    ->where('exercices_comptables_id', $targetId)
                    ->where(function ($q) use ($ranReference) {
                        $q->whereNull('reference_piece')->orWhere('reference_piece', '!=', $ranReference);
                    })->get();
                if ($otherRows->count() !== 51) {
                    throw new RuntimeException('Le nombre de mouvements ordinaires de l’exercice cible a changé (attendu : 51). Aucun mouvement n’est touché.');
                }
                $otherDebit = round((float) $otherRows->sum('debit'), 2);
                $otherCredit = round((float) $otherRows->sum('credit'), 2);
                if (abs($otherDebit - 8247735.00) >= 0.01
                    || abs($otherCredit - 8247735.00) >= 0.01
                    || abs($otherDebit - $otherCredit) >= 0.01) {
                    throw new RuntimeException('Les 51 écritures ordinaires ne correspondent plus au contrôle de référence (8 247 735,00 équilibrés).');
                }

                $sig = $reporting->getSIGData($sourceId, $companyId);
                $sigResult = round((float) ($sig['resultat_net'] ?? 0), 2);

                // Contrôle indépendant par soldes algébriques (crédit - débit)
                // des comptes 6, 7 et 8, incluant les comptes 89.
                $sourceRows = EcritureComptable::where('company_id', $companyId)
                    ->where('exercices_comptables_id', $sourceId)
                    ->where(function ($q) {
                        $q->whereNull('statut')->orWhereIn('statut', ['approved', 'valid']);
                    })
                    ->with('planComptable')->get();

                $ledgerResult = 0.0;
                $balancesGestion = [];
                $balancesBilan = [];
                foreach ($sourceRows as $line) {
                    $account = $line->planComptable;
                    if (!$account) continue;
                    $number = (string) $account->numero_de_compte;
                    $prefix = substr($number, 0, 1);
                    $balance = round((float) $line->credit - (float) $line->debit, 2);

                    if (in_array($prefix, ['6', '7', '8'], true)) {
                        $ledgerResult += $balance;
                        $balancesGestion[$account->id] = ($balancesGestion[$account->id] ?? 0.0) + $balance;
                    }
                    if (in_array($prefix, ['1', '2', '3', '4', '5'], true)) {
                        $key = implode('|', [
                            (string) $line->plan_comptable_id,
                            (string) $line->plan_tiers_id,
                            (string) $line->compte_tresorerie_id,
                            (string) $line->poste_tresorerie_id,
                            (string) $line->type_flux,
                        ]);
                        $balancesBilan[$key] ??= [
                            'plan_comptable_id' => $line->plan_comptable_id,
                            'plan_tiers_id' => $line->plan_tiers_id,
                            'compte_tresorerie_id' => $line->compte_tresorerie_id,
                            'poste_tresorerie_id' => $line->poste_tresorerie_id,
                            'type_flux' => $line->type_flux,
                            'balance' => 0.0,
                        ];
                        $balancesBilan[$key]['balance'] -= $balance; // debit - credit
                    }
                }
                $ledgerResult = round($ledgerResult, 2);
                if (abs($sigResult - $ledgerResult) >= 0.01) {
                    throw new RuntimeException(sprintf('Le SIG (%.2f) diffère du grand livre classes 6–8/89 (%.2f). Diagnostic requis.', $sigResult, $ledgerResult));
                }
                if (abs($sigResult - $resultatAttendu) >= 0.01) {
                    throw new RuntimeException(sprintf('Le résultat actuel est %.2f et non %.2f : la base a évolué depuis le contrôle. Rien n’a été modifié.', $sigResult, $resultatAttendu));
                }

                $comptePerte = PlanComptable::where('company_id', $companyId)
                    ->where('numero_de_compte', 'like', '1309%')->orderBy('numero_de_compte')->first();
                if (!$comptePerte) {
                    throw new RuntimeException('Le compte 1309 de W2I est absent du plan comptable. Création automatique interdite pendant cette réparation.');
                }
                $journalOd = CodeJournal::where('company_id', $companyId)
                    ->whereIn('code_journal', ['OD', 'OD01'])
                    ->orderByRaw("CASE WHEN code_journal = 'OD01' THEN 0 ELSE 1 END")
                    ->first();
                $journalRan = CodeJournal::where('company_id', $companyId)->where('code_journal', 'RAN')->first()
                    ?: CodeJournal::where('company_id', $companyId)->where('code_journal', 'AN')->first();
                if (!$journalOd || !$journalRan) {
                    throw new RuntimeException('Le journal OD ou RAN/AN n’est pas configuré pour W2I.');
                }

                $ranDebit = round((float) $ranRows->sum('debit'), 2);
                $ranCredit = round((float) $ranRows->sum('credit'), 2);
                if (abs(($ranDebit - $ranCredit) - $resultatAttendu) >= 0.01) {
                    throw new RuntimeException('L’écart du RAN existant ne correspond pas exactement à la perte identifiée. Aucune suppression.');
                }

                // La perte en 1309 (débit) fait partie du bilan à reporter.
                // Le calcul du bilan ci-dessus précède la création de RES :
                // on ajoute donc explicitement la ligne 1309 de la pièce RES au futur RAN.
                $key1309 = implode('|', [(string) $comptePerte->id, '', '', '', '']);
                $balancesBilan[$key1309] ??= [
                    'plan_comptable_id' => $comptePerte->id,
                    'plan_tiers_id' => null,
                    'compte_tresorerie_id' => null,
                    'poste_tresorerie_id' => null,
                    'type_flux' => null,
                    'balance' => 0.0,
                ];
                $balancesBilan[$key1309]['balance'] += abs($resultatAttendu);

                return [
                    'source' => $source,
                    'target' => $target,
                    'other_count' => $otherRows->count(),
                    'other_debit' => $otherDebit,
                    'other_credit' => $otherCredit,
                    'ran_count' => $ranRows->count(),
                    'ran_debit' => $ranDebit,
                    'ran_credit' => $ranCredit,
                    'resultat' => $sigResult,
                    'source_rows' => $sourceRows,
                    'source_fingerprint' => $this->fingerprint($sourceRows),
                    'target_fingerprint' => $this->fingerprint($otherRows),
                    'ran_fingerprint' => $this->fingerprint($ranRows),
                    'gestion_balances' => $balancesGestion,
                    'bilan_balances' => $balancesBilan,
                    'account_1309' => $comptePerte,
                    'journal_od' => $journalOd,
                    'journal_ran' => $journalRan,
                    'source_flag' => (bool) $source->resultat_passe,
                ];
            }, 3);

            $this->table(
                ['Contrôle W2I', 'Valeur'],
                [
                    ['Entreprise / exercices', '210 / 331 → 332'],
                    ['Résultat SIG et grand livre', number_format($diagnostic['resultat'], 2, ',', ' ') . ' (perte)'],
                    ['RAN actuel', $diagnostic['ran_count'] . ' lignes : D ' . number_format($diagnostic['ran_debit'], 2, ',', ' ') . ' / C ' . number_format($diagnostic['ran_credit'], 2, ',', ' ')],
                    ['Écritures ordinaires cible', $diagnostic['other_count'] . ' lignes : D=C=' . number_format($diagnostic['other_debit'], 2, ',', ' ')],
                    ['Indicateur résultat source', $diagnostic['source_flag'] ? 'actif' : 'inactif'],
                    ['Action', !empty($diagnostic['already_applied']) ? 'DÉJÀ RÉGULARISÉE — AUCUNE ÉCRITURE' : ($this->option('apply') ? 'APPLICATION' : 'DIAGNOSTIC SEUL')],
                ]
            );

            if (!empty($diagnostic['already_applied'])) {
                $this->info('Régularisation W2I déjà appliquée et contrôlée : RES-331 et RAN-331 sont équilibrés, les 51 écritures ordinaires sont intactes. Aucune donnée modifiée.');
                return self::SUCCESS;
            }

            if (!$this->option('apply')) {
                $this->warn('Aucune donnée modifiée. Pour appliquer après sauvegarde, relancez avec --apply --confirm=W2I-210-331-332.');
                return self::SUCCESS;
            }

            DB::transaction(function () use ($diagnostic, $companyId, $sourceId, $targetId, $ranReference, $resReference, $reporting) {
                $source = ExerciceComptable::where('company_id', $companyId)->whereKey($sourceId)->lockForUpdate()->firstOrFail();
                $target = ExerciceComptable::where('company_id', $companyId)->whereKey($targetId)->lockForUpdate()->firstOrFail();

                // Recontrôle dans la transaction d'application : aucun ajout sur un état qui aurait changé.
                if ($target->cloturer || EcritureComptable::where('company_id', $companyId)
                    ->where('exercices_comptables_id', $sourceId)->where('reference_piece', $resReference)->exists()) {
                    throw new RuntimeException('État comptable modifié depuis le diagnostic. Application interrompue.');
                }
                $ran = EcritureComptable::where('company_id', $companyId)
                    ->where('exercices_comptables_id', $targetId)->where('reference_piece', $ranReference)->get();
                if ($ran->count() !== 8 || $ran->contains(fn ($row) => !(bool) $row->is_ran)) {
                    throw new RuntimeException('La pièce RAN a changé depuis le diagnostic. Application interrompue.');
                }
                $normalRows = EcritureComptable::where('company_id', $companyId)
                    ->where('exercices_comptables_id', $targetId)
                    ->where(function ($q) use ($ranReference) {
                        $q->whereNull('reference_piece')->orWhere('reference_piece', '!=', $ranReference);
                    })->get();
                if ($normalRows->count() !== 51
                    || $this->fingerprint($normalRows) !== $diagnostic['target_fingerprint']) {
                    throw new RuntimeException('Les 51 mouvements ordinaires ont changé depuis le diagnostic. Application interrompue.');
                }
                $currentRan = EcritureComptable::where('company_id', $companyId)
                    ->where('exercices_comptables_id', $targetId)
                    ->where('reference_piece', $ranReference)->get();
                if ($this->fingerprint($currentRan) !== $diagnostic['ran_fingerprint']) {
                    throw new RuntimeException('Le RAN a changé depuis le diagnostic. Application interrompue.');
                }
                $currentSource = EcritureComptable::where('company_id', $companyId)
                    ->where('exercices_comptables_id', $sourceId)
                    ->where(function ($q) {
                        $q->whereNull('statut')->orWhereIn('statut', ['approved', 'valid']);
                    })
                    ->with('planComptable')->get();
                if ($this->fingerprint($currentSource) !== $diagnostic['source_fingerprint']) {
                    throw new RuntimeException('Les écritures sources ont changé depuis le diagnostic. Application interrompue.');
                }
                $currentSig = round((float) ($reporting->getSIGData($sourceId, $companyId)['resultat_net'] ?? 0), 2);
                if (abs($currentSig - $diagnostic['resultat']) >= 0.01) {
                    throw new RuntimeException('Le SIG a changé depuis le diagnostic. Application interrompue.');
                }

                $sourceDate = Carbon::parse($source->date_fin)->toDateString();
                $journalSaisi = JournalSaisi::firstOrCreate([
                    'annee' => 2024,
                    'mois' => 12,
                    'exercices_comptables_id' => $sourceId,
                    'code_journals_id' => $diagnostic['journal_od']->id,
                    'company_id' => $companyId,
                ], ['user_id' => $source->user_id]);

                // Réparer le faux indicateur avant de matérialiser la pièce RES.
                $source->update(['resultat_passe' => false, 'resultat_passe_le' => null]);

                $main = EcritureComptable::create([
                    'date' => $sourceDate,
                    'n_saisie' => 'RES-2024',
                    'n_saisie_user' => 'RES-2024',
                    'description_operation' => 'AFFECTATION DU RESULTAT EXERCICE 2023 - RÉGULARISATION W2I',
                    'reference_piece' => $resReference,
                    'plan_comptable_id' => $diagnostic['account_1309']->id,
                    'code_journal_id' => $diagnostic['journal_od']->id,
                    'exercices_comptables_id' => $sourceId,
                    'journaux_saisis_id' => $journalSaisi->id,
                    'debit' => abs($diagnostic['resultat']),
                    'credit' => 0,
                    'company_id' => $companyId,
                    'user_id' => $source->user_id,
                    'is_ran' => false,
                    'is_resultat_systeme' => true,
                    'plan_analytique' => 0,
                    'statut' => 'approved',
                ]);

                // Contreparties : fermeture détaillée des comptes de gestion 6, 7, 8 (89 inclus).
                foreach ($diagnostic['gestion_balances'] as $planId => $balanceCreditDebit) {
                    $amount = round($balanceCreditDebit, 2); // solde crédit-débit
                    if (abs($amount) < 0.01) continue;
                    EcritureComptable::create([
                        'date' => $sourceDate,
                        'n_saisie' => 'RES-2024',
                        'n_saisie_user' => 'RES-2024',
                        'description_operation' => 'CLÔTURE RESULTAT: compte de gestion ' . $planId . ' - W2I',
                        'reference_piece' => $resReference,
                        'plan_comptable_id' => $planId,
                        'code_journal_id' => $diagnostic['journal_od']->id,
                        'exercices_comptables_id' => $sourceId,
                        'journaux_saisis_id' => $journalSaisi->id,
                        // Solde source = crédit - débit ; l’écriture de clôture
                        // porte exactement le solde opposé dans le grand livre.
                        'debit' => $amount > 0 ? $amount : 0,
                        'credit' => $amount < 0 ? abs($amount) : 0,
                        'company_id' => $companyId,
                        'user_id' => $source->user_id,
                        'is_ran' => false,
                        'is_resultat_systeme' => true,
                        'plan_analytique' => 0,
                        'statut' => 'approved',
                    ]);
                }

                $resRows = EcritureComptable::where('company_id', $companyId)
                    ->where('exercices_comptables_id', $sourceId)
                    ->where('reference_piece', $resReference)->get();
                $resDebit = round((float) $resRows->sum('debit'), 2);
                $resCredit = round((float) $resRows->sum('credit'), 2);
                if (abs($resDebit - $resCredit) >= 0.01) {
                    throw new RuntimeException(sprintf('Pièce RES non équilibrée : D %.2f / C %.2f. Transaction annulée.', $resDebit, $resCredit));
                }

                // Le RAN existant est supprimé uniquement après toutes les assertions ;
                // les 51 écritures ordinaires ne sont jamais ciblées.
                EcritureComptable::where('company_id', $companyId)
                    ->where('exercices_comptables_id', $targetId)
                    ->where('reference_piece', $ranReference)
                    ->where('is_ran', true)->delete();

                $ranDebit = 0.0;
                $ranCredit = 0.0;
                foreach ($diagnostic['bilan_balances'] as $balance) {
                    $amount = round($balance['balance'], 2); // debit-credit
                    if (abs($amount) < 0.01) continue;
                    $debit = $amount > 0 ? $amount : 0.0;
                    $credit = $amount < 0 ? abs($amount) : 0.0;
                    $ranDebit += $debit;
                    $ranCredit += $credit;
                    EcritureComptable::create([
                        'date' => Carbon::parse($target->date_debut)->toDateString(),
                        'n_saisie' => 'RAN-2024',
                        'n_saisie_user' => 'RAN-2024',
                        'description_operation' => 'REPORT À NOUVEAU exercice 2023 - régularisation contrôlée W2I',
                        'reference_piece' => $ranReference,
                        'plan_comptable_id' => $balance['plan_comptable_id'],
                        'plan_tiers_id' => $balance['plan_tiers_id'],
                        'compte_tresorerie_id' => $balance['compte_tresorerie_id'],
                        'poste_tresorerie_id' => $balance['poste_tresorerie_id'],
                        'type_flux' => $balance['type_flux'],
                        'code_journal_id' => $diagnostic['journal_ran']->id,
                        'exercices_comptables_id' => $targetId,
                        'debit' => $debit,
                        'credit' => $credit,
                        'company_id' => $companyId,
                        'user_id' => $source->user_id,
                        'is_ran' => true,
                        'is_resultat_systeme' => false,
                        'plan_analytique' => 0,
                        'statut' => 'approved',
                    ]);
                }

                if (abs(round($ranDebit - $ranCredit, 2)) >= 0.01) {
                    throw new RuntimeException(sprintf('RAN régénéré non équilibré : D %.2f / C %.2f. Transaction annulée.', $ranDebit, $ranCredit));
                }

                $source->update(['resultat_passe' => true, 'resultat_passe_le' => now()]);
            }, 3);

            $this->info('Régularisation W2I appliquée : pièce RES-331 recréée, RAN-331 recalculé et équilibré. Les 51 écritures ordinaires ont été préservées.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Régularisation refusée/annulée : ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /** Empreinte des lignes : protège contre toute modification entre diagnostic et application. */
    private function fingerprint($rows): string
    {
        $snapshot = $rows->map(function ($row) {
            return [
                'id' => (int) $row->id,
                'plan' => $row->plan_comptable_id,
                'tiers' => $row->plan_tiers_id,
                'treso' => $row->compte_tresorerie_id,
                'poste' => $row->poste_tresorerie_id,
                'flux' => $row->type_flux,
                'debit' => number_format((float) $row->debit, 2, '.', ''),
                'credit' => number_format((float) $row->credit, 2, '.', ''),
                'status' => $row->statut,
                'ran' => (bool) $row->is_ran,
                'reference' => $row->reference_piece,
            ];
        })->sortBy('id')->values()->all();

        return hash('sha256', json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
