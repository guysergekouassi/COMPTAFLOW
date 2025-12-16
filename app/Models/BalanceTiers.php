<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BalanceTiers extends Model
{
    protected $table = 'balances_tiers';

    protected $fillable = [
        'date_debut',
        'date_fin',
        'plan_tiers_id_1',
        'plan_tiers_id_2',
        'format',
        'balance_tiers',
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

    // Relation pour le premier compte général
    public function planTiers1()
    {
        return $this->belongsTo(PlanTiers::class, 'plan_tiers_id_1');
    }

    // Relation pour le second compte général
    public function planTiers2()
    {
        return $this->belongsTo(PlanTiers::class, 'plan_tiers_id_2');
    }
}

