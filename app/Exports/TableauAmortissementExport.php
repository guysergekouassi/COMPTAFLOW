<?php

namespace App\Exports;

use App\Models\Immobilisation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TableauAmortissementExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $immobilisation;

    public function __construct(Immobilisation $immobilisation)
    {
        $this->immobilisation = $immobilisation;
    }

    public function collection()
    {
        return $this->immobilisation->amortissements;
    }

    public function headings(): array
    {
        return [
            ['TABLEAU D\'AMORTISSEMENT'],
            ['Immobilisation: ' . $this->immobilisation->libelle],
            ['Code: ' . $this->immobilisation->code],
            [''],
            [
                'Année',
                'Base Amortissable',
                'Dotation Annuelle',
                'Cumul Amortissement',
                'VNC Fin Exercice',
                'Statut'
            ]
        ];
    }

    public function map($amortissement): array
    {
        return [
            $amortissement->annee,
            $amortissement->base_amortissable,
            $amortissement->dotation_annuelle,
            $amortissement->cumul_amortissement,
            $amortissement->valeur_nette_comptable,
            ucfirst($amortissement->statut),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
            5 => ['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEFEFEF']]],
        ];
    }
}
