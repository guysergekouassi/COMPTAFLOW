<?php

namespace App\Imports;

use App\Models\PlanTiers;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

/**
 * Import direct des Tiers (page Configuration).
 *
 * - PAS de WithHeadingRow : un fichier avec UNE seule ligne de données
 *   (ex: "401FRS001;NOM FOURNISSEUR;Fournisseur") fonctionnera correctement.
 * - WithCustomCsvSettings : détection automatique du délimiteur (;  ,  \t  |).
 */
class MasterTiersImport implements ToModel, WithCustomCsvSettings
{
    protected $userId;
    protected $companyId;
    protected $filePath;

    public function __construct(?string $filePath = null)
    {
        $user = Auth::user();
        $this->userId    = $user->id;
        $this->companyId = $user->company_id;
        $this->filePath  = $filePath;
    }

    // ─── WithCustomCsvSettings ───────────────────────────────────────────────

    public function getCsvSettings(): array
    {
        return [
            'delimiter'      => $this->detectDelimiter(),
            'enclosure'      => '"',
            'input_encoding' => 'UTF-8',
        ];
    }

    private function detectDelimiter(): string
    {
        if (!$this->filePath || !file_exists($this->filePath)) {
            return ';';
        }
        $sample = array_slice(
            file($this->filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES),
            0, 5
        );
        $counts = [';' => 0, ',' => 0, "\t" => 0, '|' => 0];
        foreach ($sample as $line) {
            foreach ($counts as $d => $c) {
                $counts[$d] += substr_count($line, $d);
            }
        }
        arsort($counts);
        $best = array_key_first($counts);
        return ($counts[$best] > 0) ? $best : ';';
    }

    // ─── ToModel ─────────────────────────────────────────────────────────────

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Accès par index numérique (pas d'en-tête obligatoire)
        $num   = trim($row[0] ?? '');
        $label = trim($row[1] ?? '');
        $type  = trim($row[2] ?? 'Autre');

        if (empty($num) || empty($label)) {
            return null;
        }

        // Ignorer si la ligne est une en-tête textuelle
        if (str_contains(strtolower($num), 'tiers') || str_contains(strtolower($label), 'intitule')) {
            return null;
        }

        $numero = strtoupper($num);

        $exists = PlanTiers::where('company_id', $this->companyId)
            ->where('numero_de_tiers', $numero)
            ->exists();

        if ($exists) {
            return null;
        }

        return new PlanTiers([
            'numero_de_tiers' => $numero,
            'intitule'        => strtoupper($label),
            'type_de_tiers'   => $type,
            'user_id'         => $this->userId,
            'company_id'      => $this->companyId,
        ]);
    }
}
