<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanComptable extends Model
{
    use HasFactory;
protected $table = 'plan_comptables';
    protected $fillable = [
        'numero_de_compte',
        'intitule',
        'adding_strategy',

     

        'user_id',
        'company_id'
    ];

    public function scopeClasse5($query)
    {
        return $query->where('numero_de_compte', 'like', '5%');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
