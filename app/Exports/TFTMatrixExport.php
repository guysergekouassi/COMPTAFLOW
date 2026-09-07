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
    /** Données de l'exercice précédent (null si le comparatif N-1 n'est pas demandé). */
    protected $dataN1;
    protected $exerciceN1;
    /** Bornes de la plage de mois affichée (1 = premier mois de l'exercice). */
    protected $monthFrom;
    protected $monthTo;

    public function __construct($data, $exercice, $detailed = false, $dataN1 = null, $exerciceN1 = null, $monthFrom = null, $monthTo = null)
    {
        $this->data = $data;
        $this->exercice = $exercice;
        $this->detailed = $detailed;
        $this->dataN1 = $dataN1;
        $this->exerciceN1 = $exerciceN1;
        $this->monthFrom = $monthFrom;
        $this->monthTo = $monthTo;
    }

    public function view(): View
    {
        return view('reporting.excel.tft', [
            'data'       => $this->data,
            'exercice'   => $this->exercice,
            'detailed'   => $this->detailed,
            'dataN1'     => $this->dataN1,
            'exerciceN1' => $this->exerciceN1,
            'monthFrom'  => $this->monthFrom,
            'monthTo'    => $this->monthTo,
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
        ];
    }
}
