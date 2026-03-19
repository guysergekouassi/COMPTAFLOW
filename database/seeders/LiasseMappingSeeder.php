<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LiasseMappingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filePath = base_path('La logique des liasses/mapping_complet.csv');
        if (!file_exists($filePath)) {
            return;
        }

        $handle = fopen($filePath, 'r');
        $header = fgetcsv($handle, 0, ';');

        $rows = [];
        $count = 0;

        while (($data = fgetcsv($handle, 0, ';')) !== false) {
            if (count($data) < 12) continue;

            $codeChamp = $data[8];
            $parts = explode('_', $codeChamp);
            $shortCode = $parts[count($parts)-2] ?? null;
            $accountRange = null;

            // Mapping standard SYSCOHADA (Préfixes de comptes)
            $codeTableauDb = $data[1]; // Par défaut

            if ($data[1] === 'ACTIF' || $data[1] === 'BILAN_ACTIF') {
                $codeTableauDb = 'BILAN_ACTIF';
                $accountRange = match($shortCode) {
                    'AE' => '201,202', // Frais de dév
                    'AF' => '21',      // Brevets
                    'AG' => '22',      // Fonds comm
                    'AH' => '20,23,24', // Autres incorp
                    'AJ' => '211',     // Terrains
                    'AK' => '231,232', // Bâtiments
                    'AL' => '233,243', // Aménagements
                    'AM' => '241,242', // Matériel
                    'AN' => '245',     // Transport
                    'AP' => '238,248,258', // Avances immo
                    'AR' => '26',      // Titres part
                    'AS' => '27',      // Autres immo fin
                    'BB' => '3',       // Stocks
                    'BG' => '409,41',  // Créances
                    'BS' => '52,53,57', // Trésorerie
                    default => null
                };
            } elseif ($data[1] === 'PASSIF' || $data[1] === 'BILAN_PASSIF') {
                $codeTableauDb = 'BILAN_PASSIF';
                $accountRange = match($shortCode) {
                    'CA' => '10',      // Capital
                    'CF' => '11',      // Réserves
                    'CG' => '12',      // Report à nouveau
                    'CH' => '12',      // Report à nouveau (selon DGI)
                    'CJ' => '13',      // Résultat net (avant calcul)
                    'DA' => '16',      // Emprunts
                    'DK' => '42,43,44', // Dettes soc/fisc
                    'DM' => '40,419',  // Fournisseurs / Dettes divers
                    'FB' => '40',      // Fournisseurs exploitation
                    'HA' => '52,56',   // Banques crédits passif
                    default => null
                };
            } elseif ($data[1] === 'RESULTAT') {
                $accountRange = match($shortCode) {
                    'TC' => '70',      // CA
                    'RE' => '60,61,62', // Achats
                    'RI' => '63,64',   // Impôts / Salaires
                    default => null
                };
            } elseif ($data[1] === 'TFT') {
                $accountRange = match($shortCode) {
                    'ZA' => '5',       // Capacité d'autofinancement (Sera affiné)
                    'ZB' => '4',       // Variation BFR
                    'ZD' => '2',       // Investissements
                    'ZG' => '10',      // Capital
                    'ZH' => '16',      // Emprunts
                    'ZL' => '5',       // Trésorerie initiale
                    'ZM' => '5',       // Trésorerie finale
                    default => null
                };
            }

            $rows[] = [
                'type' => $data[0],
                'code_tableau' => $codeTableauDb,
                'titre_tableau' => $data[2],
                'type_tableau' => $data[3],
                'onglet_excel' => $data[4],
                'code_ligne_dgi' => $data[5],
                'libelle_ligne' => $data[6],
                'libelle_colonne' => $data[7],
                'code_champ_dgi' => $codeChamp,
                'cellule_excel' => $data[9],
                'account_range' => $accountRange,
                'pos_ligne' => is_numeric($data[10]) ? (int)$data[10] : null,
                'pos_col' => is_numeric($data[11]) ? (int)$data[11] : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $count++;

            if (count($rows) >= 500) {
                \App\Models\LiasseMapping::insert($rows);
                $rows = [];
            }
        }

        if (count($rows) > 0) {
            \App\Models\LiasseMapping::insert($rows);
        }

        fclose($handle);
        $this->command->info("Importation terminée : $count lignes insérées.");
    }
}
