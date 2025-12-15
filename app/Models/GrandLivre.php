<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GrandLivre extends Model
{
    protected $table = 'grand_livres';

    protected $fillable = [
        'date_debut',
        'date_fin',
        'plan_comptable_id_1',
        'plan_comptable_id_2',
        'format',
        'grand_livre',
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

