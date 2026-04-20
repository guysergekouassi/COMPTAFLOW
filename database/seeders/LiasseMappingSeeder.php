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
                    'AE' => '201,202', 
                    'AF' => '21',      
                    'AG' => '22',      
                    'AH' => '20,23,24', 
                    'AJ' => '211',     
                    'AK' => '231,232', 
                    'AL' => '233,243', 
                    'AM' => '241,242', 
                    'AN' => '245',     
                    'AP' => '238,248,258', 
                    'AR' => '26',      
                    'AS' => '27',      
                    'BB' => '3',       
                    'BG' => '409,41,42,43,44,45,46,47,48', 
                    'BS' => '50,52,53,57,58', 
                    default => null
                };
            } elseif ($data[1] === 'PASSIF' || $data[1] === 'BILAN_PASSIF') {
                $codeTableauDb = 'BILAN_PASSIF';
                $accountRange = match($shortCode) {
                    'CA' => '101,102,103,104,105,106,107,108,109',      
                    'CF' => '111,112,113,114,115,116,117,118,119',      
                    'CG' => '121,122,129',      
                    'CH' => '121,122,129',      
                    'CJ' => '13',      
                    'DA' => '161,162,163,164,165,166,168',      
                    'DK' => '42,43,44', 
                    'DM' => '40,419',  
                    'FB' => '401,402,403,404,405,408,409,421,422,423,424,425,426,427,428,431,432,433,434,435,436,437,438,441,442,443,444,445,446,447,448,462',      
                    'HA' => '521,522,523,524,525,526,527,528,529,561,562,563,564,565,566,567,568,569',   
                    default => null
                };
            } elseif ($data[1] === 'RESULTAT') {
                $accountRange = match($shortCode) {
                    'XA' => '701,707',
                    'XB' => '601,607',
                    'XD' => '702,703,704,705,706,708,709,71,72,73,74,75',
                    'XE' => '602,603,604,605,606,608,609,61,62',
                    'XG' => '74',
                    'XH' => '64',
                    'XJ' => '78',
                    'XK' => '68',
                    'XM' => '76',
                    'XN' => '66',
                    'XQ' => '77',
                    'XR' => '67',
                    'XT' => '691,692',
                    'XU' => '695,699',
                    'TC' => '70',      // Fallback old DGI fields
                    'RE' => '60,61,62', // Fallback old DGI fields
                    'RI' => '63,64',   // Fallback old DGI fields
                    default => null
                };
            } elseif ($data[1] === 'TFT') {
                $accountRange = match($shortCode) {
                    'ZA' => '5',       
                    'ZB' => '4',       
                    'ZD' => '2',       
                    'ZG' => '10',      
                    'ZH' => '16',      
                    'ZL' => '5',       
                    'ZM' => '5',       
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
