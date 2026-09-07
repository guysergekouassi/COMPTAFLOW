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
    /** Données de l'exercice précédent (null si le comparatif N-1 n'est pas demandé). */
    protected $dataN1;
    protected $exerciceN1;

    public function __construct($data, $exercice, $month = null, $detailed = false, $dataN1 = null, $exerciceN1 = null)
    {
        $this->data = $data;
        $this->exercice = $exercice;
        $this->month = $month;
        $this->detailed = $detailed;
        $this->dataN1 = $dataN1;
        $this->exerciceN1 = $exerciceN1;
    }

    /** Le comparatif N-1 est-il actif ? */
    protected function cmp(): bool
    {
        return !is_null($this->dataN1);
    }

    /** Construit une ligne, avec sa contrepartie N-1 et l'écart. */
    private function ligne($section, $libelle, $montant, $montantN1 = null)
    {
        return (object) [
            'section'    => $section,
            'libelle'    => $libelle,
            'montant'    => $montant,
            'montant_n1' => $this->cmp() ? $montantN1 : null,
            'variation'  => ($this->cmp() && is_numeric($montant) && is_numeric($montantN1)) ? $montant - $montantN1 : null,
        ];
    }

    public function collection()
    {
        $rows = collect();

        // En-tête du document
        $rows->push($this->ligne('BILAN ACTIF/PASSIF', 'Exercice: ' . $this->exercice->intitule, ''));
        if ($this->cmp()) {
            $rows->push($this->ligne('', 'Comparatif : ' . ($this->exerciceN1->intitule ?? 'N-1'), ''));
        }
        $rows->push($this->ligne('', '', ''));

        // --- ACTIF ---
        $rows->push($this->ligne('ACTIF', '', ''));

        $actifParams = [
            'immobilise'  => 'Actif Immobilisé',
            'circulant'   => 'Actif Circulant',
            'tresorerie'  => 'Trésorerie Actif',
        ];

        foreach ($actifParams as $key => $title) {
            $rows->push($this->ligne(
                '',
                $title,
                $this->data['actif'][$key]['total_net'],
                $this->dataN1['actif'][$key]['total_net'] ?? 0
            ));

            foreach ($this->data['actif'][$key]['subcategories'] as $subKey => $subData) {
                if ($subData['net'] == 0 && empty($subData['details'])) {
                    continue;
                }

                $rows->push($this->ligne(
                    '',
                    '   ' . $subData['label'],
                    $subData['net'],
                    $this->dataN1['actif'][$key]['subcategories'][$subKey]['net'] ?? 0
                ));

                if ($this->detailed && !empty($subData['details'])) {
                    $detailsN1 = $this->indexerDetails($this->dataN1['actif'][$key]['subcategories'][$subKey]['details'] ?? []);
                    foreach ($subData['details'] as $item) {
                        $rows->push($this->ligne(
                            '',
                            '      ' . $item['numero'] . ' - ' . $item['intitule'],
                            $item['solde'],
                            $detailsN1[$item['numero'] ?? ''] ?? 0
                        ));
                    }
                }
            }
        }

        $rows->push($this->ligne('TOTAL ACTIF', '', $this->data['actif']['total_net'], $this->dataN1['actif']['total_net'] ?? 0));
        $rows->push($this->ligne('', '', ''));

        // --- PASSIF ---
        $rows->push($this->ligne('PASSIF & CAPITAUX', '', ''));

        $passifParams = [
            'capitaux'    => 'Capitaux Propres',
            'dettes_fin'  => 'Dettes Financières',
            'passif_circ' => 'Passif Circulant',
            'tresorerie'  => 'Trésorerie Passif',
        ];

        foreach ($passifParams as $key => $title) {
            $rows->push($this->ligne(
                '',
                $title,
                $this->data['passif'][$key]['total'],
                $this->dataN1['passif'][$key]['total'] ?? 0
            ));

            foreach ($this->data['passif'][$key]['subcategories'] as $subKey => $subData) {
                if ($subData['total'] == 0 && empty($subData['details'])) {
                    continue;
                }

                $rows->push($this->ligne(
                    '',
                    '   ' . $subData['label'],
                    $subData['total'],
                    $this->dataN1['passif'][$key]['subcategories'][$subKey]['total'] ?? 0
                ));

                if ($this->detailed && !empty($subData['details'])) {
                    $detailsN1 = $this->indexerDetails($this->dataN1['passif'][$key]['subcategories'][$subKey]['details'] ?? []);
                    foreach ($subData['details'] as $item) {
                        $rows->push($this->ligne(
                            '',
                            '      ' . $item['numero'] . ' - ' . $item['intitule'],
                            $item['solde'],
                            $detailsN1[$item['numero'] ?? ''] ?? 0
                        ));
                    }
                }
            }
        }

        $rows->push($this->ligne('TOTAL PASSIF', '', $this->data['passif']['total'], $this->dataN1['passif']['total'] ?? 0));

        return $rows;
    }

    /** Indexe les lignes de détail N-1 par numéro de compte, pour l'alignement. */
    private function indexerDetails($details): array
    {
        $index = [];
        foreach ($details as $d) {
            $index[$d['numero'] ?? ''] = $d['solde'] ?? 0;
        }
        return $index;
    }

    public function headings(): array
    {
        $devise = $this->exercice->company->currency ?? 'FCFA';

        if (!$this->cmp()) {
            return ['Section', 'Libellé', 'Montant (' . $devise . ')'];
        }

        return [
            'Section',
            'Libellé',
            ($this->exercice->intitule ?? 'N') . ' (' . $devise . ')',
            ($this->exerciceN1->intitule ?? 'N-1') . ' (' . $devise . ')',
            'Variation (' . $devise . ')',
        ];
    }

    public function map($row): array
    {
        if (!$this->cmp()) {
            return [$row->section, $row->libelle, $row->montant];
        }

        return [$row->section, $row->libelle, $row->montant, $row->montant_n1, $row->variation];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            3 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 12],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E293B']],
            ],
        ];
    }
}
