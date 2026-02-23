<?php

namespace App\Exports\Analytique;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AnalyticalBalanceExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
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
            ['RAPPORT : BALANCE ANALYTIQUE'],
            ['AXE : ' . $this->axeLibelle],
            [''],
            ['CODE SECTION', 'LIBELLÉ SECTION', 'TOTAL DÉBIT', 'TOTAL CRÉDIT', 'SOLDE']
        ];
    }

    public function map($item): array
    {
        $solde = $item->total_debit - $item->total_credit;
        $soldeStr = number_format(abs($solde), 2, ',', ' ') . ($solde >= 0 ? ' D' : ' C');

        return [
            $item->code,
            $item->libelle,
            $item->total_debit,
            $item->total_credit,
            $soldeStr
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
