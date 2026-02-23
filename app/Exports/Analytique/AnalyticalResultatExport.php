<?php

namespace App\Exports\Analytique;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnalyticalResultatExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $data;
    protected $axeLibelle;

    public function __construct($data, $axeLibelle)
    {
        $this->data = $data;
        $this->axeLibelle = $axeLibelle;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            ['RAPPORT : RÉSULTAT ANALYTIQUE'],
            ['AXE : ' . $this->axeLibelle],
            [''],
            ['SECTION', 'CHARGES (CL. 6)', 'PRODUITS (CL. 7)', 'RÉSULTAT NET', 'MARGE %']
        ];
    }

    public function map($item): array
    {
        $resultat = $item->total_produits - $item->total_charges;
        $marge = $item->total_produits > 0 ? ($resultat / $item->total_produits) * 100 : 0;

        return [
            $item->code . ' - ' . $item->libelle,
            $item->total_charges,
            $item->total_produits,
            $resultat,
            number_format($marge, 2, ',', ' ') . '%'
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
