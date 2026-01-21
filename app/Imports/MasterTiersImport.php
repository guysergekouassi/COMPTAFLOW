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
        if (empty($row['numero_de_tiers']) || empty($row['intitule']) || empty($row['type_de_tiers'])) {
            return null;
        }

        $numero = strtoupper($row['numero_de_tiers']);

        $exists = PlanTiers::where('company_id', $this->companyId)
            ->where('numero_de_tiers', $numero)
            ->exists();

        if ($exists) {
            return null;
        }

        return new PlanTiers([
            'numero_de_tiers' => $numero,
            'intitule'        => strtoupper($row['intitule']),
            'type_de_tiers'   => $row['type_de_tiers'],
            'user_id'         => $this->userId,
            'company_id'      => $this->companyId,
        ]);
    }
}
