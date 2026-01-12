<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;

class PlanComptable extends Model
{
    use HasFactory, BelongsToTenant;
    protected $table = 'plan_comptables';
    protected $fillable = [
        'numero_de_compte',
        'intitule',
        'type_de_compte',
        'adding_strategy',
        'classe',
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
