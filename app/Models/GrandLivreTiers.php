<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;
use App\Traits\BelongsToUser;

class GrandLivreTiers extends Model
{
    use BelongsToTenant, BelongsToUser;
    protected $table = 'grand_livres_tiers';

    protected $fillable = [
        'date_debut',
        'date_fin',
        'plan_tiers_id_1',
        'plan_tiers_id_2',
        'format',
        'grand_livre_tiers',
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

