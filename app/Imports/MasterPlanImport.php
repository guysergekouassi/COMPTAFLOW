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
        // Tentative de récupération intelligente des données (Slugified headers ou Index)
        $num = $row['numero_de_compte'] ?? $row['numerodecompte'] ?? $row['compte'] ?? $row[0] ?? null;
        $label = $row['intitule'] ?? $row['libelle'] ?? $row['nom'] ?? $row[1] ?? null;

        if (empty($num) || empty($label)) {
            return null;
        }

        // Nettoyage : Si le numéro ne contient aucun chiffre, ce n'est probablement pas un compte
        if (!preg_match('/[0-9]/', $num)) {
            return null;
        }

        // Nettoyage sommaire si c'est l'entête qui est passée par erreur
        if (str_contains(strtolower($num), 'compte') || str_contains(strtolower($label), 'intitule')) {
            return null;
        }

        $numero = str_pad($num, $this->digits, '0', STR_PAD_RIGHT);

        $exists = PlanComptable::where('company_id', $this->companyId)
            ->where('numero_de_compte', $numero)
            ->exists();

        if ($exists) {
            return null;
        }

        // Calcul dynamique de la classe et du type
        $prefix = substr($num, 0, 1);
        $classe = is_numeric($prefix) ? (int)$prefix : 0;
        $type = in_array($classe, [1, 2, 3, 4, 5, 9]) ? 'Bilan' : 'Compte de résultat';

        return new PlanComptable([
            'numero_de_compte' => $numero,
            'intitule'         => mb_strtoupper($label),
            'type_de_compte'   => $type,
            'classe'           => $classe,
            'adding_strategy'  => 'imported',
            'user_id'          => $this->userId,
            'company_id'       => $this->companyId,
        ]);
    }
}
