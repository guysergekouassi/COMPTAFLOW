<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GrandLivreExport implements FromCollection, WithHeadings, WithMapping
{
    protected $groupedEcritures;
    protected $totauxGeneraux;
    protected $ligneCouranteSolde = []; // mémoriser solde progressif par compte

    protected $soldesInitiaux;

    public function __construct(Collection $ecritures, $soldesInitiaux = [])
    {
        $this->soldesInitiaux = $soldesInitiaux;
        // Trier par numéro de compte, puis date, puis n_saisie
        $sorted = $ecritures->sort(function ($a, $b) {
            // 1. Compte
            $cA = $a->planComptable->numero_de_compte ?? '';
            $cB = $b->planComptable->numero_de_compte ?? '';
            $cmp = strcmp($cA, $cB);
            if ($cmp !== 0) return $cmp;

            // 2. Date
            $dA = $a->date ?? '';
            $dB = $b->date ?? '';
            $cmp = strcmp($dA, $dB);
            if ($cmp !== 0) return $cmp;

            // 3. N° Saisie
            return strcmp($a->n_saisie ?? '', $b->n_saisie ?? '');
        });

        // Grouper par compte
        $this->groupedEcritures = $sorted->groupBy('plan_comptable_id');

        $this->totauxGeneraux = [
            'debit' => 0,
            'credit' => 0,
        ];
    }

    public function collection()
    {
        $rows = collect();

        foreach ($this->groupedEcritures as $compteId => $operations) {
            $compteNum = optional($operations->first()->planComptable)->numero_de_compte ?? '-';
            $compteIntitule = optional($operations->first()->planComptable)->intitule ?? '-';

            // Initialisation avec le solde initial (si présent)
            $si = $this->soldesInitiaux[$compteId] ?? ['debit' => 0, 'credit' => 0, 'solde' => 0];
            $this->ligneCouranteSolde[$compteId] = (float)$si['solde'];

            $totalDebit = (float)$si['debit'];
            $totalCredit = (float)$si['credit'];

            // Ligne OUVERTURE
            $rows->push((object)[
                'planComptable' => (object)[
                    'numero_de_compte' => $compteNum,
                    'intitule' => $compteIntitule,
                ],
                'date' => '',
                'codeJournal' => (object)['code_journal' => ''],
                'n_saisie' => '',
                'description_operation' => 'SOLDE INITIAL (OUVERTURE)',
                'lettrage' => '',
                'debit' => $si['debit'],
                'credit' => $si['credit'],
                'solde_progressif' => $this->ligneCouranteSolde[$compteId],
            ]);

            foreach ($operations as $ecriture) {
                $debit = $ecriture->debit ?? 0;
                $credit = $ecriture->credit ?? 0;

                // calcul du solde progressif
                $this->ligneCouranteSolde[$compteId] += ($debit - $credit);

                // stocker le solde progressif dans l'objet pour map()
                $ecriture->solde_progressif = $this->ligneCouranteSolde[$compteId];

                $totalDebit += $debit;
                $totalCredit += $credit;

                $rows->push($ecriture);
            }

            // Ajouter une ligne "Total compte"
            $rows->push((object)[
                'planComptable' => (object)[
                    'numero_de_compte' => '',
                    'intitule' => 'TOTAL CUMULÉ COMPTE ' . $compteNum,
                ],
                'date' => '',
                'codeJournal' => (object)['code_journal' => ''],
                'n_saisie' => '',
                'description_operation' => '',
                'lettrage' => '',
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'solde_progressif' => $this->ligneCouranteSolde[$compteId],
            ]);

            $this->totauxGeneraux['debit'] += $totalDebit;
            $this->totauxGeneraux['credit'] += $totalCredit;
        }

        return $rows; // Retourne toutes les lignes accumulées
    }

    public function headings(): array
    {
        return [
            'N° compte',
            'Intitulé compte',
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
            $ecriture->planComptable->numero_de_compte ?? '',
            $ecriture->planComptable->intitule ?? '',
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
