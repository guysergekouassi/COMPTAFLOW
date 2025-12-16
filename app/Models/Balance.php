<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    //

    protected $table = 'balances';
    protected $fillable = [
        'date_debut',
        'date_fin',
        'plan_comptable_id_1',
        'plan_comptable_id_2',
        'format',
        'balance',
        'type',
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

    public function planComptable1()
    {
        return $this->belongsTo(PlanComptable::class, 'plan_comptable_id_1');
    }

    // Relation pour le second compte général
    public function planComptable2()
    {
        return $this->belongsTo(PlanComptable::class, 'plan_comptable_id_2');
    }

}
