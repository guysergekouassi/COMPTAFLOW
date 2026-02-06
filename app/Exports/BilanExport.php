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
    protected $month;
    protected $detailed;

    public function __construct($data, $exercice, $month = null, $detailed = false)
    {
        $this->data = $data;
        $this->exercice = $exercice;
        $this->month = $month;
        $this->detailed = $detailed;
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

        // --- ACTIF ---
        $rows->push((object)['section' => 'ACTIF', 'libelle' => '', 'montant' => '']);
        
        $actifParams = [
            'immobilise' => 'Actif Immobilisé', 
            'circulant' => 'Actif Circulant', 
            'tresorerie' => 'Trésorerie Actif'
        ];

        foreach ($actifParams as $key => $title) {
            // Section Total
            $rows->push((object)[
                'section' => '',
                'libelle' => $title,
                'montant' => $this->data['actif'][$key]['total']
            ]);

            // Subcategories
            foreach ($this->data['actif'][$key]['subcategories'] as $subData) {
                if ($subData['total'] != 0 || !empty($subData['details'])) {
                     $rows->push((object)[
                        'section' => '',
                        'libelle' => '   ' . $subData['label'],
                        'montant' => $subData['total']
                    ]);
                    
                    // Details
                    if ($this->detailed && !empty($subData['details'])) {
                        foreach ($subData['details'] as $item) {
                            $rows->push((object)[
                                'section' => '',
                                'libelle' => '      ' . $item['numero'] . ' - ' . $item['intitule'],
                                'montant' => $item['solde']
                            ]);
                        }
                    }
                }
            }
        }
        
        $rows->push((object)[
            'section' => 'TOTAL ACTIF',
            'libelle' => '',
            'montant' => $this->data['actif']['total']
        ]);
        $rows->push((object)['section' => '', 'libelle' => '', 'montant' => '']);

        // --- PASSIF ---
        $rows->push((object)['section' => 'PASSIF & CAPITAUX', 'libelle' => '', 'montant' => '']);

        $passifParams = [
            'capitaux' => 'Capitaux Propres', 
            'dettes_fin' => 'Dettes Financières', 
            'passif_circ' => 'Passif Circulant', 
            'tresorerie' => 'Trésorerie Passif'
        ];

        foreach ($passifParams as $key => $title) {
             // Section Total
            $rows->push((object)[
                'section' => '',
                'libelle' => $title,
                'montant' => $this->data['passif'][$key]['total']
            ]);

            // Subcategories
            foreach ($this->data['passif'][$key]['subcategories'] as $subData) {
                if ($subData['total'] != 0 || !empty($subData['details'])) {
                     $rows->push((object)[
                        'section' => '',
                        'libelle' => '   ' . $subData['label'],
                        'montant' => $subData['total']
                    ]);
                    
                    // Details
                    if ($this->detailed && !empty($subData['details'])) {
                        foreach ($subData['details'] as $item) {
                            $rows->push((object)[
                                'section' => '',
                                'libelle' => '      ' . $item['numero'] . ' - ' . $item['intitule'],
                                'montant' => $item['solde']
                            ]);
                        }
                    }
                }
            }
        }

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
            3 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 12], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E293B']]], // ACTIF header
            // Dynamic styling is hard with collection based simplified export, but we can style specific known rows if we tracked index.
            // For now, let's keep it simple.
        ];
    }
}
