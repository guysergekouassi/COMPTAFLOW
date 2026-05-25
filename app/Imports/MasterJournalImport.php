<?php

namespace App\Imports;

use App\Models\CodeJournal;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

/**
 * Import direct des journaux (page Configuration).
 *
 * - PAS de WithHeadingRow : un fichier avec UNE seule ligne de données
 *   (ex: "RAN2;Report a nouveau") fonctionnera correctement.
 * - WithCustomCsvSettings : détection automatique du délimiteur (;  ,  \t  |).
 */
class MasterJournalImport implements ToModel, WithCustomCsvSettings
{
    protected $userId;
    protected $companyId;
    protected $journalDigits;
    protected $filePath;

    public function __construct(?string $filePath = null)
    {
        $user = Auth::user();
        $this->userId = $user->id;
        $this->companyId = $user->company_id;
        $this->journalDigits = $user->company->journal_code_digits ?? 4;
        $this->filePath = $filePath;
    }

    // ─── WithCustomCsvSettings ───────────────────────────────────────────────

    public function getCsvSettings(): array
    {
        $delimiter = $this->detectDelimiter();
        return [
            'delimiter'        => $delimiter,
            'enclosure'        => '"',
            'input_encoding'   => 'UTF-8',
        ];
    }

    /**
     * Détecte automatiquement le délimiteur en analysant les premières lignes du fichier.
     */
    private function detectDelimiter(): string
    {
        if (!$this->filePath || !file_exists($this->filePath)) {
            return ';'; // Défaut Syscohada
        }
        $sample = file($this->filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $sample = array_slice($sample, 0, 5);
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
        $code  = trim($row[0] ?? '');
        $label = trim($row[1] ?? '');
        $type  = trim($row[2] ?? 'Opérations Diverses');

        if (empty($code) || empty($label)) {
            return null;
        }

        // Ignorer si la ligne est une en-tête textuelle
        if (str_contains(strtolower($code), 'code') || str_contains(strtolower($label), 'intitule')) {
            return null;
        }

        $codeRaw      = $code;
        $codeFormatted = $this->standardizeJournalCode($code, $this->journalDigits);

        $exists = CodeJournal::where('company_id', $this->companyId)
            ->where(function($q) use ($codeFormatted, $codeRaw) {
                $q->where('code_journal', $codeFormatted)
                  ->orWhere('numero_original', $codeRaw);
            })
            ->exists();

        if ($exists) {
            return null;
        }

        return new CodeJournal([
            'code_journal'    => $codeFormatted,
            'numero_original' => $codeRaw,
            'intitule'        => strtoupper($label),
            'type'            => $type,
            'user_id'         => $this->userId,
            'company_id'      => $this->companyId,
        ]);
    }

    /**
     * Standardise un code journal sur la longueur configurée.
     */
    private function standardizeJournalCode(string $code, int $digits): string
    {
        $code = strtoupper(trim($code));
        if (empty($code)) return $code;

        if (strlen($code) < $digits) {
            return str_pad($code, $digits, '0', STR_PAD_RIGHT);
        } elseif (strlen($code) > $digits) {
            return substr($code, 0, $digits);
        }
        return $code;
    }
}
