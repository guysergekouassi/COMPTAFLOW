<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;

class FluxType extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'flux_types';

    protected $fillable = [
        'categorie',
        'nature',
       
        'plan_comptable_id_1',
        'plan_comptable_id_2',

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
