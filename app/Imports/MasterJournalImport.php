<?php

namespace App\Imports;

use App\Models\CodeJournal;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MasterJournalImport implements ToModel, WithHeadingRow
{
    protected $userId;
    protected $companyId;

    public function __construct()
    {
        $user = Auth::user();
        $this->userId = $user->id;
        $this->companyId = $user->company_id;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Tentative de récupération intelligente (Slugified headers ou Index)
        $code = $row['code_journal'] ?? $row['codejournal'] ?? $row['code'] ?? $row[0] ?? null;
        $label = $row['intitule'] ?? $row['libelle'] ?? $row['nom'] ?? $row[1] ?? null;
        $type = $row['type'] ?? $row[2] ?? 'Opérations Diverses';

        if (empty($code) || empty($label)) {
            return null;
        }

        // Nettoyage si entête passée par erreur
        if (str_contains(strtolower($code), 'code') || str_contains(strtolower($label), 'intitule')) {
            return null;
        }

        $codeFormatted = strtoupper($code);

        $exists = CodeJournal::where('company_id', $this->companyId)
            ->where('code_journal', $codeFormatted)
            ->exists();

        if ($exists) {
            return null;
        }

        return new CodeJournal([
            'code_journal' => $codeFormatted,
            'intitule'     => strtoupper($label),
            'type'         => $type,
            'user_id'      => $this->userId,
            'company_id'   => $this->companyId,
        ]);
    }
}
