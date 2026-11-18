<?php

namespace App\Imports;

use App\Models\CodeJournal;
use App\Models\PlanComptable;
use App\Services\UniformisationImport;
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
        // Le référentiel Selflow transmet le numéro du compte de trésorerie
        // (et non son identifiant SQL). Le plan est importé avant les journaux.
        $compteNumero = trim((string) ($row[3] ?? ''));
        $compteTresorerieId = null;

        if ($compteNumero !== '' && in_array($type, ['Trésorerie', 'Banque', 'Caisse'], true)) {
            $company = Auth::user()->company;
            $compte = $company ? UniformisationImport::compte($company, $compteNumero) : null;

            // Défense supplémentaire : si un journal désigne un compte absent
            // du plan transmis, on le crée avec la nature SYSCOHADA correcte.
            if (!$compte && $company) {
                $numero = UniformisationImport::numeroDeCompte(
                    $compteNumero,
                    UniformisationImport::chiffresDeCompte($company)
                );
                $classe = substr($numero, 0, 1);
                $nature = match ($classe) {
                    '1' => 'passif',
                    '2', '3', '5' => 'actif',
                    '4' => 'actif',
                    '6', '8' => 'charge',
                    '7' => 'produit',
                    '9' => 'analytique',
                    default => 'actif',
                };
                $compte = PlanComptable::create([
                    'numero_de_compte' => $numero,
                    'numero_original' => $compteNumero,
                    'intitule' => 'Compte de trésorerie ' . $compteNumero,
                    'type_de_compte' => $nature,
                    'classe' => ctype_digit($classe) ? $classe : null,
                    'user_id' => $this->userId,
                    'company_id' => $this->companyId,
                ]);
            }

            $compteTresorerieId = $compte?->id;
        }

        if (empty($code) || empty($label)) {
            return null;
        }

        // Ignorer si la ligne est une en-tête textuelle
        if (str_contains(strtolower($code), 'code') || str_contains(strtolower($label), 'intitule')) {
            return null;
        }

        $codeRaw      = $code;
        $codeFormatted = UniformisationImport::codeJournal($code, $this->journalDigits);

        $existant = CodeJournal::where('company_id', $this->companyId)
            ->where(function($q) use ($codeFormatted, $codeRaw) {
                $q->where('code_journal', $codeFormatted)
                  ->orWhere('numero_original', $codeRaw);
            })
            ->first();

        if ($existant) {
            // Ne pas remplacer la configuration du comptable. En revanche,
            // compléter un lien de trésorerie resté vide est sans danger.
            $aCompleter = [];
            if (blank($existant->numero_original)) {
                $aCompleter['numero_original'] = $codeRaw;
            }
            if ($compteTresorerieId && blank($existant->compte_de_tresorerie)) {
                $aCompleter['compte_de_tresorerie'] = $compteTresorerieId;
            }
            if ($aCompleter) {
                $existant->update($aCompleter);
            }

            return null;
        }

        return new CodeJournal([
            'code_journal'          => $codeFormatted,
            'numero_original'       => $codeRaw,
            'intitule'              => strtoupper($label),
            'type'                  => $type,
            'compte_de_tresorerie'  => $compteTresorerieId,
            'user_id'               => $this->userId,
            'company_id'            => $this->companyId,
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
