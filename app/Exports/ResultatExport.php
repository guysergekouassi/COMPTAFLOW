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

    /** Lignes à mettre en gras (numéros de lignes de la feuille). */
    protected $boldRows = [];
    /** Lignes de solde intermédiaire (fond gris). */
    protected $soldeRows = [];
    /** Ligne du résultat net. */
    protected $resultatRow = null;

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

        $periode = app(\App\Services\AccountingReportingService::class)
            ->resolvePeriode($this->exercice, $this->month);

        // --- En-tête du document ---
        $this->push($rows, 'COMPTE DE RÉSULTAT (SIG)', '', 'title');
        $this->push($rows, 'Entreprise : ' . ($this->exercice->company->company_name ?? ''), '');
        $this->push($rows, 'Exercice : ' . $this->exercice->intitule, '');
        $this->push($rows, 'Période : ' . $periode['label'], '');
        $this->push($rows, 'Du ' . $periode['debut']->format('d/m/Y') . ' au ' . $periode['fin']->format('d/m/Y'), '');
        $this->push($rows, '', '');

        // --- 1. MARGE COMMERCIALE ---
        $this->push($rows, 'Ventes de marchandises', $this->v('ventes_marchandises'));
        $this->push($rows, 'Achats de marchandises (y compris variations stocks)', -($this->v('achats_marchandises') + $this->v('var_stock_march')));
        $this->push($rows, 'MARGE COMMERCIALE', $this->v('marge_commerciale'), 'solde');

        // --- 2. VALEUR AJOUTÉE ---
        $this->push($rows, "Production de l'exercice", $this->v('production_exercice'));
        $this->push($rows, "Consommation de l'exercice", -$this->v('consommation_exercice'));
        $this->push($rows, 'VALEUR AJOUTÉE', $this->v('valeur_ajoutee'), 'solde');

        // --- 3. EXCÉDENT BRUT D'EXPLOITATION ---
        $this->push($rows, "Subventions d'exploitation", $this->v('subventions_expl'));
        $this->push($rows, 'Charges de personnel', -$this->v('charges_personnel'));
        $this->push($rows, 'Impôts et Taxes', -$this->v('impots_taxes'));
        $this->push($rows, "EXCÉDENT BRUT D'EXPLOITATION (EBE)", $this->v('ebe'), 'solde');

        // --- 4. RÉSULTAT D'EXPLOITATION ---
        $this->push($rows, "Reprises d'amortissements et provisions", $this->v('reprises_amort_prov') + $this->v('transfert_charges'));
        $this->push($rows, 'Dotations aux amortissements et provisions', -$this->v('dotations_amort_prov'));
        $this->push($rows, "RÉSULTAT D'EXPLOITATION", $this->v('resultat_exploitation'), 'solde');

        // --- 5. RÉSULTAT FINANCIER ---
        $this->push($rows, 'Revenus financiers', $this->v('revenus_financiers') + $this->v('reprises_fin') + $this->v('transfert_fin'));
        $this->push($rows, 'Frais financiers', -($this->v('frais_financiers') + $this->v('dotations_fin')));
        $this->push($rows, 'RÉSULTAT FINANCIER', $this->v('resultat_financier'), 'solde');

        $this->push($rows, 'RÉSULTAT DES ACTIVITÉS ORDINAIRES', $this->v('resultat_activites_ordinaires'), 'solde');

        // --- 6. RÉSULTAT H.A.O ---
        $this->push($rows, 'Produits H.A.O', $this->v('produits_hao'));
        $this->push($rows, 'Charges H.A.O', -$this->v('charges_hao'));
        $this->push($rows, 'RÉSULTAT H.A.O', $this->v('resultat_hao'), 'solde');

        // --- 7. RÉSULTAT NET ---
        $this->push($rows, 'Impôts sur le Résultat', -$this->v('impots_resultat'));
        $this->push($rows, 'RÉSULTAT NET', $this->v('resultat_net'), 'resultat');

        // --- DÉTAIL DES COMPTES ---
        if ($this->detailed && !empty($this->data['details'])) {
            $this->push($rows, '', '');
            $this->push($rows, 'DÉTAIL DES COMPTES', '', 'title');

            foreach ($this->data['details'] as $categorie => $items) {
                $this->push($rows, '', '');
                $this->push($rows, mb_strtoupper($categorie), '', 'solde');
                foreach ($items as $item) {
                    $this->push(
                        $rows,
                        '   ' . ($item['numero'] ?? '') . ' - ' . ($item['intitule'] ?? ''),
                        $item['solde'] ?? 0
                    );
                }
            }
        }

        return $rows;
    }

    /**
     * Ajoute une ligne et mémorise son style (la ligne 1 est l'en-tête de colonnes).
     */
    private function push(Collection $rows, $libelle, $montant, $style = null)
    {
        $rows->push((object)['libelle' => $libelle, 'montant' => $montant]);
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
     * Valeur numérique sécurisée d'un poste SIG.
     */
    private function v($key)
    {
        return isset($this->data[$key]) && is_numeric($this->data[$key]) ? (float) $this->data[$key] : 0;
    }

    public function headings(): array
    {
        return ['Libellé', 'Montant (' . ($this->exercice->company->currency ?? 'FCFA') . ')'];
    }

    public function map($row): array
    {
        return [
            $row->libelle,
            $row->montant,
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 55, 'B' => 22];
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

        $sheet->getStyle('B2:B' . max(2, $sheet->getHighestRow()))
            ->getNumberFormat()->setFormatCode('#,##0');

        return $styles;
    }
}
