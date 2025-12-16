<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BalanceExport implements FromCollection, WithHeadings, WithMapping
{
    protected $groupedEcritures;
    protected $totauxGeneraux;

    public function __construct(Collection $ecritures)
    {
        // Trier par numéro de compte
        $sorted = $ecritures->sortBy(fn($item) => $item->planComptable->numero_de_compte ?? 0);

        // Grouper par compte
        $this->groupedEcritures = $sorted->groupBy('plan_comptable_id');

        $this->totauxGeneraux = [
            'mouvement_debit' => 0,
            'mouvement_credit' => 0,
            'solde_debit' => 0,
            'solde_credit' => 0,
        ];
    }

    public function collection()
    {
        $rows = collect();

        foreach ($this->groupedEcritures as $operations) {
            $compteNumero = optional($operations->first()->planComptable)->numero_de_compte ?? '-';
            $compteIntitule = optional($operations->first()->planComptable)->intitule ?? 'Intitulé inconnu';

            // Totaux du compte
            $totalDebit = $operations->sum('debit');
            $totalCredit = $operations->sum('credit');

            // Solde calculé
            $solde = $totalDebit - $totalCredit;
            $soldeDebit = $solde > 0 ? $solde : 0;
            $soldeCredit = $solde < 0 ? abs($solde) : 0;

            // Ligne du compte
            $rows->push((object)[
                'compte' => $compteNumero,
                'intitule' => $compteIntitule,
                'mouvement_debit' => $totalDebit,
                'mouvement_credit' => $totalCredit,
                'solde_debit' => $soldeDebit,
                'solde_credit' => $soldeCredit,
            ]);

            // Cumuler dans les totaux généraux
            $this->totauxGeneraux['mouvement_debit'] += $totalDebit;
            $this->totauxGeneraux['mouvement_credit'] += $totalCredit;
            $this->totauxGeneraux['solde_debit'] += $soldeDebit;
            $this->totauxGeneraux['solde_credit'] += $soldeCredit;
        }

        // Ligne "Totaux généraux"
        $rows->push((object)[
            'compte' => '',
            'intitule' => 'Totaux généraux',
            'mouvement_debit' => $this->totauxGeneraux['mouvement_debit'],
            'mouvement_credit' => $this->totauxGeneraux['mouvement_credit'],
            'solde_debit' => $this->totauxGeneraux['solde_debit'],
            'solde_credit' => $this->totauxGeneraux['solde_credit'],
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Compte général',
            'Intitulé',
            'Mouvement Débit',
            'Mouvement Crédit',
            'Solde Débit',
            'Solde Crédit',
        ];
    }

    public function map($row): array
    {
        return [
            $row->compte,
            $row->intitule,
            $row->mouvement_debit,
            $row->mouvement_credit,
            $row->solde_debit,
            $row->solde_credit,
        ];
    }
}
