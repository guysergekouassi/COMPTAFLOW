<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TFTMatrixExport implements FromView, ShouldAutoSize, WithTitle, WithStyles
{
    protected $data;
    protected $exercice;
    protected $detailed;

    public function __construct($data, $exercice, $detailed = false)
    {
        $this->data = $data;
        $this->exercice = $exercice;
        $this->detailed = $detailed;
    }

    public function view(): View
    {
        // We reuse the PDF view because it will include the full matrix logic
        // We might want to create a specific 'reporting.excel.tft' if specific excel formatting is needed, 
        // but starting with the pdf one (which we will rewrite next) is a good base.
        return view('reporting.pdf.tft', [
            'data' => $this->data,
            'exercice' => $this->exercice,
            'detailed' => $this->detailed
        ]);
    }

    public function title(): string
    {
        return 'TFT ' . $this->exercice->intitule;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            // Add more styles as needed after seeing the result
        ];
    }
}
