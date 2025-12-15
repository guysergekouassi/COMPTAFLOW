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

    public function __construct(Collection $ecritures)
    {
        // Trier par numéro de compte
        $sorted = $ecritures->sortBy(fn($item) => $item->planComptable->numero_de_compte ?? 0);

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
            $compteNumero = optional($operations->first()->planComptable)->numero_de_compte ?? '-';
            $compteIntitule = optional($operations->first()->planComptable)->intitule ?? '-';

            // Initialiser solde pour ce compte
            $this->ligneCouranteSolde[$compteId] = 0;

            $totalDebit = 0;
            $totalCredit = 0;

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
                    'numero_de_compte' => $compteNumero,
                    'intitule' => $compteIntitule,
                ],
                'date' => '',
                'planTiers' => (object)['numero_de_tiers' => ''],
                'codeJournal' => (object)['code_journal' => ''],
                'n_saisie' => '',
                'description_operation' => 'Total compte ' . $compteNumero,
                'lettrage' => '',
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'solde_progressif' => '', // pas de solde sur ligne total
            ]);

            // cumuler dans les totaux généraux
            $this->totauxGeneraux['debit'] += $totalDebit;
            $this->totauxGeneraux['credit'] += $totalCredit;
        }

        // Ligne totaux généraux
        $rows->push((object)[
            'planComptable' => (object)[
                'numero_de_compte' => '',
                'intitule' => 'Totaux généraux',
            ],
            'date' => '',
            'planTiers' => (object)['numero_de_tiers' => ''],
            'codeJournal' => (object)['code_journal' => ''],
            'n_saisie' => '',
            'description_operation' => '',
            'lettrage' => '',
            'debit' => $this->totauxGeneraux['debit'],
            'credit' => $this->totauxGeneraux['credit'],
            'solde_progressif' => $this->totauxGeneraux['debit'] - $this->totauxGeneraux['credit'],
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Compte',
            'Intitulé',
            'Date',
            'Compte général',
            'Tiers',
            'C.J',
            'N° Saisie',
            'Libellé écriture',
            'Let',
            'Débit',
            'Crédit',
            'Solde progressif',
        ];
    }

    public function map($ecriture): array
    {
        return [
            $ecriture->planComptable->numero_de_compte ?? '',
            $ecriture->planComptable->intitule ?? '',
            $ecriture->date ? \Carbon\Carbon::parse($ecriture->date)->format('d/m/Y') : '',
            $ecriture->planComptable->numero_de_compte ?? '',
            $ecriture->planTiers->numero_de_tiers ?? '',
            $ecriture->codeJournal->code_journal ?? '',
            $ecriture->n_saisie ?? '',
            $ecriture->description_operation ?? '',
            $ecriture->lettrage ?? '',
            $ecriture->debit ?? 0,
            $ecriture->credit ?? 0,
            $ecriture->solde_progressif ?? '',
        ];
    }
}
