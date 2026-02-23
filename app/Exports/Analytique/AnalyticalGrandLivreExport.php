<?php

namespace App\Exports\Analytique;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnalyticalGrandLivreExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $data;
    protected $sectionName;

    public function __construct($data, $sectionName)
    {
        $this->data = $data;
        $this->sectionName = $sectionName;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            ['RAPPORT : GRAND LIVRE ANALYTIQUE'],
            ['SECTION : ' . $this->sectionName],
            [''],
            ['DATE', 'N° SAISIE', 'COMPTE', 'LIBELLÉ OPÉRATION', 'VENT. %', 'MONTANT', 'SENS']
        ];
    }

    public function map($item): array
    {
        return [
            \Carbon\Carbon::parse($item->date)->format('d/m/Y'),
            $item->n_saisie,
            $item->numero_de_compte . ' - ' . $item->compte_libelle,
            $item->description_operation,
            $item->pourcentage . '%',
            $item->montant,
            $item->sens
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['bold' => true]],
            4 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1E40AF']]],
        ];
    }
}
