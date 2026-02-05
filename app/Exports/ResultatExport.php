<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResultatExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $data;
    protected $exercice;

    public function __construct($data, $exercice)
    {
        $this->data = $data;
        $this->exercice = $exercice;
    }

    public function collection()
    {
        $rows = collect();

        // En-tête du document
        $rows->push((object)[
            'nature' => 'COMPTE DE RÉSULTAT',
            'montant' => 'Exercice: ' . $this->exercice->intitule
        ]);
        $rows->push((object)['nature' => '', 'montant' => '']);

        // PRODUITS
        $rows->push((object)['nature' => 'PRODUITS (CLASSE 7)', 'montant' => '']);
        $rows->push((object)[
            'nature' => 'Ventes et prestations de services',
            'montant' => $this->data['produits']['total']
        ]);
        $rows->push((object)['nature' => '', 'montant' => '']);

        // CHARGES
        $rows->push((object)['nature' => 'CHARGES (CLASSE 6)', 'montant' => '']);
        $rows->push((object)[
            'nature' => 'Achats et charges externes',
            'montant' => $this->data['charges']['total']
        ]);
        $rows->push((object)['nature' => '', 'montant' => '']);

        // RÉSULTAT
        $rows->push((object)[
            'nature' => 'RÉSULTAT NET (' . $this->data['type'] . ')',
            'montant' => $this->data['resultat']
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return ['Nature', 'Montant (FCFA)'];
    }

    public function map($row): array
    {
        return [
            $row->nature,
            $row->montant
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            3 => ['font' => ['bold' => true]],
            6 => ['font' => ['bold' => true]],
            9 => ['font' => ['bold' => true, 'color' => ['rgb' => $this->data['resultat'] >= 0 ? '008000' : 'FF0000']]],
        ];
    }
}
