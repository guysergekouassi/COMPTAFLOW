<?php

namespace App\Imports;

use App\Models\PlanTiers;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MasterTiersImport implements ToModel, WithHeadingRow
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
        $num = $row['numero_de_tiers'] ?? $row['numerodetiers'] ?? $row['code_tiers'] ?? $row[0] ?? null;
        $label = $row['intitule'] ?? $row['libelle'] ?? $row['nom'] ?? $row[1] ?? null;
        $type = $row['type_de_tiers'] ?? $row['typedetiers'] ?? $row['type'] ?? $row[2] ?? 'Autre';

        if (empty($num) || empty($label)) {
            return null;
        }

        // Nettoyage si entête passée par erreur
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
