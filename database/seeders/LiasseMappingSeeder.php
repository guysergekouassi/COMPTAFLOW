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

            $rows[] = [
                'type' => $data[0],
                'code_tableau' => $data[1],
                'titre_tableau' => $data[2],
                'type_tableau' => $data[3],
                'onglet_excel' => $data[4],
                'code_ligne_dgi' => $data[5],
                'libelle_ligne' => $data[6],
                'libelle_colonne' => $data[7],
                'code_champ_dgi' => $data[8],
                'cellule_excel' => $data[9],
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
