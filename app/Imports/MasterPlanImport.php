<?php

namespace App\Imports;

use App\Models\PlanComptable;
use App\Models\Company;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MasterPlanImport implements ToModel, WithHeadingRow
{
    protected $digits;
    protected $userId;
    protected $companyId;

    public function __construct()
    {
        $user = Auth::user();
        $this->userId = $user->id;
        $this->companyId = $user->company_id;
        $company = Company::find($this->companyId);
        $this->digits = $company->account_digits ?? 8;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row) 
    {
        if (empty($row['numero_de_compte']) || empty($row['intitule'])) {
            return null;
        }

        $numero = str_pad($row['numero_de_compte'], $this->digits, '0', STR_PAD_RIGHT);

        $exists = PlanComptable::where('company_id', $this->companyId)
            ->where('numero_de_compte', $numero)
            ->exists();

        if ($exists) {
            return null;
        }

        return new PlanComptable([
            'numero_de_compte' => $numero,
            'intitule'         => $row['intitule'],
            'user_id'          => $this->userId,
            'company_id'       => $this->companyId,
            'is_active'        => true
        ]);
    }
}
