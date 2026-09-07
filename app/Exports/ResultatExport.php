<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ResultatExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithStrictNullComparison
{
    protected $data;
    protected $exercice;
    protected $month;
    protected $detailed;
    /** Données de l'exercice précédent (null si le comparatif N-1 n'est pas demandé). */
    protected $dataN1;
    protected $exerciceN1;

    /** Lignes à mettre en gras (numéros de lignes de la feuille). */
    protected $boldRows = [];
    /** Lignes de solde intermédiaire (fond gris). */
    protected $soldeRows = [];
    /** Ligne du résultat net. */
    protected $resultatRow = null;

    public function __construct($data, $exercice, $month = null, $detailed = false, $dataN1 = null, $exerciceN1 = null)
    {
        $this->data = $data;
        $this->exercice = $exercice;
        $this->month = $month;
        $this->detailed = $detailed;
        $this->dataN1 = $dataN1;
        $this->exerciceN1 = $exerciceN1;
    }

    /** Le comparatif est-il actif ? */
    protected function cmp(): bool
    {
        return !is_null($this->dataN1);
    }

    public function collection()
    {
        $rows = collect();

        $periode = app(\App\Services\AccountingReportingService::class)
            ->resolvePeriode($this->exercice, $this->month);

        // --- En-tête du document ---
        $this->push($rows, 'COMPTE DE RÉSULTAT (SIG)', null, 'title');
        $this->push($rows, 'Entreprise : ' . ($this->exercice->company->company_name ?? ''), null);
        $this->push($rows, 'Exercice : ' . $this->exercice->intitule, null);
        $this->push($rows, 'Période : ' . $periode['label'], null);
        $this->push($rows, 'Du ' . $periode['debut']->format('d/m/Y') . ' au ' . $periode['fin']->format('d/m/Y'), null);
        if ($this->cmp()) {
            $this->push($rows, 'Comparatif : ' . ($this->exerciceN1->intitule ?? 'N-1'), null);
        }
        $this->push($rows, '', null);

        // Trame du SIG : une définition unique, évaluée sur N puis sur N-1.
        $lignes = [
            ['Ventes de marchandises',                               fn($d) => $this->v($d, 'ventes_marchandises')],
            ['Achats de marchandises (y compris variations stocks)', fn($d) => -($this->v($d, 'achats_marchandises') + $this->v($d, 'var_stock_march'))],
            ['MARGE COMMERCIALE',                                    fn($d) => $this->v($d, 'marge_commerciale'), 'solde'],

            ["Production de l'exercice",                             fn($d) => $this->v($d, 'production_exercice')],
            ["Consommation de l'exercice",                           fn($d) => -$this->v($d, 'consommation_exercice')],
            ['VALEUR AJOUTÉE',                                       fn($d) => $this->v($d, 'valeur_ajoutee'), 'solde'],

            ["Subventions d'exploitation",                           fn($d) => $this->v($d, 'subventions_expl')],
            ['Charges de personnel',                                 fn($d) => -$this->v($d, 'charges_personnel')],
            ['Impôts et Taxes',                                      fn($d) => -$this->v($d, 'impots_taxes')],
            ["EXCÉDENT BRUT D'EXPLOITATION (EBE)",                   fn($d) => $this->v($d, 'ebe'), 'solde'],

            ["Reprises d'amortissements et provisions",              fn($d) => $this->v($d, 'reprises_amort_prov') + $this->v($d, 'transfert_charges')],
            ['Dotations aux amortissements et provisions',           fn($d) => -$this->v($d, 'dotations_amort_prov')],
            ["RÉSULTAT D'EXPLOITATION",                              fn($d) => $this->v($d, 'resultat_exploitation'), 'solde'],

            ['Revenus financiers',                                   fn($d) => $this->v($d, 'revenus_financiers') + $this->v($d, 'reprises_fin') + $this->v($d, 'transfert_fin')],
            ['Frais financiers',                                     fn($d) => -($this->v($d, 'frais_financiers') + $this->v($d, 'dotations_fin'))],
            ['RÉSULTAT FINANCIER',                                   fn($d) => $this->v($d, 'resultat_financier'), 'solde'],

            ['RÉSULTAT DES ACTIVITÉS ORDINAIRES',                    fn($d) => $this->v($d, 'resultat_activites_ordinaires'), 'solde'],

            ['Produits H.A.O',                                       fn($d) => $this->v($d, 'produits_hao')],
            ['Charges H.A.O',                                        fn($d) => -$this->v($d, 'charges_hao')],
            ['RÉSULTAT H.A.O',                                       fn($d) => $this->v($d, 'resultat_hao'), 'solde'],

            ['Impôts sur le Résultat',                               fn($d) => -$this->v($d, 'impots_resultat')],
            ['RÉSULTAT NET',                                         fn($d) => $this->v($d, 'resultat_net'), 'resultat'],
        ];

        foreach ($lignes as $ligne) {
            [$libelle, $calc] = $ligne;
            $style = $ligne[2] ?? null;
            $this->push($rows, $libelle, $calc($this->data), $style, $this->cmp() ? $calc($this->dataN1) : null);
        }

        // --- DÉTAIL DES COMPTES ---
        if ($this->detailed && !empty($this->data['details'])) {
            $this->push($rows, '', null);
            $this->push($rows, 'DÉTAIL DES COMPTES', null, 'title');

            foreach ($this->data['details'] as $categorie => $items) {
                $this->push($rows, '', null);
                $this->push($rows, mb_strtoupper($categorie), null, 'solde');

                // Index des mêmes comptes en N-1, pour aligner le détail ligne à ligne.
                $itemsN1 = [];
                if ($this->cmp()) {
                    foreach ($this->dataN1['details'][$categorie] ?? [] as $itemN1) {
                        $itemsN1[$itemN1['numero'] ?? ''] = $itemN1['solde'] ?? 0;
                    }
                }

                foreach ($items as $item) {
                    $numero = $item['numero'] ?? '';
                    $this->push(
                        $rows,
                        '   ' . $numero . ' - ' . ($item['intitule'] ?? ''),
                        $item['solde'] ?? 0,
                        null,
                        $this->cmp() ? ($itemsN1[$numero] ?? 0) : null
                    );
                }
            }
        }

        return $rows;
    }

    /**
     * Ajoute une ligne et mémorise son style (la ligne 1 est l'en-tête de colonnes).
     */
    private function push(Collection $rows, $libelle, $montant, $style = null, $montantN1 = null)
    {
        $rows->push((object) [
            'libelle'    => $libelle,
            'montant'    => $montant,
            'montant_n1' => $montantN1,
            'variation'  => (is_numeric($montant) && is_numeric($montantN1)) ? $montant - $montantN1 : null,
        ]);
        $sheetRow = $rows->count() + 1;

        if ($style === 'title') {
            $this->boldRows[] = $sheetRow;
        } elseif ($style === 'solde') {
            $this->soldeRows[] = $sheetRow;
        } elseif ($style === 'resultat') {
            $this->resultatRow = $sheetRow;
        }
    }

    /**
     * Valeur numérique sécurisée d'un poste SIG dans un jeu de données donné.
     */
    private function v($data, $key)
    {
        return isset($data[$key]) && is_numeric($data[$key]) ? (float) $data[$key] : 0;
    }

    public function headings(): array
    {
        $devise = $this->exercice->company->currency ?? 'FCFA';

        if (!$this->cmp()) {
            return ['Libellé', 'Montant (' . $devise . ')'];
        }

        return [
            'Libellé',
            ($this->exercice->intitule ?? 'N') . ' (' . $devise . ')',
            ($this->exerciceN1->intitule ?? 'N-1') . ' (' . $devise . ')',
            'Variation (' . $devise . ')',
        ];
    }

    public function map($row): array
    {
        if (!$this->cmp()) {
            return [$row->libelle, $row->montant];
        }

        return [$row->libelle, $row->montant, $row->montant_n1, $row->variation];
    }

    public function columnWidths(): array
    {
        return $this->cmp()
            ? ['A' => 55, 'B' => 22, 'C' => 22, 'D' => 22]
            : ['A' => 55, 'B' => 22];
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [
            1 => ['font' => ['bold' => true]],
        ];

        foreach ($this->boldRows as $row) {
            $styles[$row] = ['font' => ['bold' => true, 'size' => 13]];
        }

        foreach ($this->soldeRows as $row) {
            $styles[$row] = [
                'font' => ['bold' => true],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'EEEEEE']],
            ];
        }

        if ($this->resultatRow) {
            $styles[$this->resultatRow] = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '000000']],
            ];
        }

        $derniereColonne = $this->cmp() ? 'D' : 'B';
        $sheet->getStyle('B2:' . $derniereColonne . max(2, $sheet->getHighestRow()))
            ->getNumberFormat()->setFormatCode('#,##0');

        return $styles;
    }
}
