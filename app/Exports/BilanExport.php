<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BilanExport implements FromCollection, WithHeadings, WithMapping, WithStyles
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
            'section' => 'BILAN ACTIF/PASSIF',
            'libelle' => 'Exercice: ' . $this->exercice->intitule,
            'montant' => ''
        ]);
        $rows->push((object)['section' => '', 'libelle' => '', 'montant' => '']);

        // ACTIF
        $rows->push((object)['section' => 'ACTIF', 'libelle' => '', 'montant' => '']);
        $rows->push((object)[
            'section' => '',
            'libelle' => 'Actif Immobilisé (Classe 2)',
            'montant' => $this->data['actif']['immobilise']['total']
        ]);
        $rows->push((object)[
            'section' => '',
            'libelle' => 'Actif Circulant (Stocks & Créances)',
            'montant' => $this->data['actif']['circulant']['total']
        ]);
        $rows->push((object)[
            'section' => '',
            'libelle' => 'Trésorerie Actif',
            'montant' => $this->data['actif']['tresorerie']['total']
        ]);
        $rows->push((object)[
            'section' => 'TOTAL ACTIF',
            'libelle' => '',
            'montant' => $this->data['actif']['total']
        ]);
        $rows->push((object)['section' => '', 'libelle' => '', 'montant' => '']);

        // PASSIF
        $rows->push((object)['section' => 'PASSIF & CAPITAUX', 'libelle' => '', 'montant' => '']);
        $rows->push((object)[
            'section' => '',
            'libelle' => 'Capitaux Propres',
            'montant' => $this->data['passif']['capitaux']['total']
        ]);
        $rows->push((object)[
            'section' => '',
            'libelle' => 'Dettes à court/long terme',
            'montant' => $this->data['passif']['dettes']['total']
        ]);
        $rows->push((object)[
            'section' => '',
            'libelle' => 'Trésorerie Passif',
            'montant' => $this->data['passif']['tresorerie']['total']
        ]);
        $rows->push((object)[
            'section' => 'TOTAL PASSIF',
            'libelle' => '',
            'montant' => $this->data['passif']['total']
        ]);

        return $rows;
    }

    public function headings(): array
    {
        return ['Section', 'Libellé', 'Montant (FCFA)'];
    }

    public function map($row): array
    {
        return [
            $row->section,
            $row->libelle,
            $row->montant
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            3 => ['font' => ['bold' => true]],
            8 => ['font' => ['bold' => true]],
            10 => ['font' => ['bold' => true]],
            16 => ['font' => ['bold' => true]],
        ];
    }
}
