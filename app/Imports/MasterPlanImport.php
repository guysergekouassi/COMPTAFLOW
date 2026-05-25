<?php

namespace App\Imports;

use App\Models\PlanComptable;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

/**
 * Import direct du Plan Comptable (page Configuration).
 *
 * - PAS de WithHeadingRow : un fichier avec UNE seule ligne de données
 *   (ex: "601000;ACHATS DE MARCHANDISES") fonctionnera correctement.
 * - WithCustomCsvSettings : détection automatique du délimiteur (;  ,  \t  |).
 */
class MasterPlanImport implements ToModel, WithCustomCsvSettings
{
    protected $digits;
    protected $userId;
    protected $companyId;
    protected $filePath;

    public function __construct(?string $filePath = null)
    {
        $user = Auth::user();
        $this->userId = $user->id;
        $this->companyId = $user->company_id;
        $company = Company::find($this->companyId);
        $this->digits = $company->account_digits ?? 8;
        $this->filePath = $filePath;
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

        if (empty($num) || empty($label)) {
            return null;
        }

        // Si le numéro ne contient aucun chiffre, ce n'est probablement pas un compte
        if (!preg_match('/[0-9]/', $num)) {
            return null;
        }

        // Ignorer si la ligne est une en-tête textuelle
        if (str_contains(strtolower($num), 'compte') || str_contains(strtolower($label), 'intitule')) {
            return null;
        }

        $numero = str_pad($num, $this->digits, '0', STR_PAD_RIGHT);

        $exists = PlanComptable::where('company_id', $this->companyId)
            ->where(function($q) use ($numero, $num) {
                $q->where('numero_de_compte', $numero)
                  ->orWhere('numero_original', $num);
            })
            ->exists();

        if ($exists) {
            return null;
        }

        // Calcul dynamique de la classe et du type
        $prefix = substr($num, 0, 1);
        $classe = is_numeric($prefix) ? (int)$prefix : 0;
        $type   = in_array($classe, [1, 2, 3, 4, 5, 9]) ? 'Bilan' : 'Compte de résultat';

        return new PlanComptable([
            'numero_de_compte' => $numero,
            'numero_original'  => $num,
            'intitule'         => mb_strtoupper($label),
            'type_de_compte'   => $type,
            'classe'           => $classe,
            'adding_strategy'  => 'imported',
            'user_id'          => $this->userId,
            'company_id'       => $this->companyId,
        ]);
    }
}
