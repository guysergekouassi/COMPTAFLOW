<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GrandLivreTiersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $groupedEcritures;
    protected $totauxGeneraux;
    protected $ligneCouranteSolde = [];

    protected $soldesInitiaux;

    public function __construct(Collection $ecritures, $soldesInitiaux = [])
    {
        $this->soldesInitiaux = $soldesInitiaux;
        // Trier par tiers, puis date, puis n_saisie
        $sorted = $ecritures->sort(function ($a, $b) {
            // 1. Tiers
            $tA = $a->planTiers->numero_de_tiers ?? '';
            $tB = $b->planTiers->numero_de_tiers ?? '';
            $cmp = strcmp($tA, $tB);
            if ($cmp !== 0) return $cmp;

            // 2. Date
            $dA = $a->date ?? '';
            $dB = $b->date ?? '';
            $cmp = strcmp($dA, $dB);
            if ($cmp !== 0) return $cmp;

            // 3. N° Saisie
            return strcmp($a->n_saisie ?? '', $b->n_saisie ?? '');
        });

        // Grouper par tiers
        $this->groupedEcritures = $sorted->groupBy('plan_tiers_id');

        $this->totauxGeneraux = [
            'debit' => 0,
            'credit' => 0,
        ];
    }

    public function collection()
    {
        $rows = collect();

        foreach ($this->groupedEcritures as $tiersId => $operations) {
            $tiersNumero = optional($operations->first()->planTiers)->numero_de_tiers ?? '-';
            $tiersIntitule = optional($operations->first()->planTiers)->intitule ?? '-';

            // Initialisation avec le solde initial
            $si = $this->soldesInitiaux[$tiersId] ?? ['debit' => 0, 'credit' => 0, 'solde' => 0];
            $this->ligneCouranteSolde[$tiersId] = (float)$si['solde'];

            $totalDebit = (float)$si['debit'];
            $totalCredit = (float)$si['credit'];

            // Ligne OUVERTURE
            $rows->push((object)[
                'planTiers' => (object)[
                    'numero_de_tiers' => $tiersNumero,
                    'intitule' => $tiersIntitule,
                ],
                'date' => '',
                'codeJournal' => (object)['code_journal' => ''],
                'n_saisie' => '',
                'description_operation' => 'SOLDE INITIAL (OUVERTURE)',
                'lettrage' => '',
                'debit' => $si['debit'],
                'credit' => $si['credit'],
                'solde_progressif' => $this->ligneCouranteSolde[$tiersId],
            ]);

            foreach ($operations as $ecriture) {
                $debit = $ecriture->debit ?? 0;
                $credit = $ecriture->credit ?? 0;

                // calcul du solde progressif
                $this->ligneCouranteSolde[$tiersId] += ($debit - $credit);
                $ecriture->solde_progressif = $this->ligneCouranteSolde[$tiersId];

                $totalDebit += $debit;
                $totalCredit += $credit;

                $rows->push($ecriture);
            }

            // Ajouter une ligne "Total"
            $rows->push((object)[
                'planTiers' => (object)[
                    'numero_de_tiers' => '',
                    'intitule' => 'TOTAL CUMULÉ ' . $tiersNumero,
                ],
                'date' => '',
                'codeJournal' => (object)['code_journal' => ''],
                'n_saisie' => '',
                'description_operation' => '',
                'lettrage' => '',
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'solde_progressif' => $this->ligneCouranteSolde[$tiersId],
            ]);

            $this->totauxGeneraux['debit'] += $totalDebit;
            $this->totauxGeneraux['credit'] += $totalCredit;
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'N° Tiers',
            'Intitulé Tiers',
            'Date',
            'Journal',
            'N° Pièce',
            'Libellé',
            'Lettrage',
            'Débit',
            'Crédit',
            'Solde Progressif'
        ];
    }

    public function map($ecriture): array
    {
        return [
            $ecriture->planTiers->numero_de_tiers ?? '',
            $ecriture->planTiers->intitule ?? '',
            $ecriture->date ?: '',
            $ecriture->codeJournal->code_journal ?? '',
            $ecriture->n_saisie ?? '',
            $ecriture->description_operation,
            $ecriture->lettrage ?? '',
            number_format($ecriture->debit ?? 0, 2, '.', ''),
            number_format($ecriture->credit ?? 0, 2, '.', ''),
            number_format($ecriture->solde_progressif ?? 0, 2, '.', ''),
        ];
    }
}
