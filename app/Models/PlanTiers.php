<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanTiers extends Model
{
    protected $table = 'plan_tiers';

    protected $fillable = [
        'numero_de_tiers',
        'compte_general',
        'intitule',
        'type_de_tiers',
        'user_id',
        'company_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function compte()
{
    return $this->belongsTo(PlanComptable::class, 'compte_general');
}

}
