<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegleVentilation extends Model
{
    use HasFactory;

    protected $table = 'regles_ventilation';

    protected $fillable = [
        'plan_comptable_id',
        'section_id',
        'pourcentage_defaut',
        'company_id'
    ];

    public function planComptable()
    {
        return $this->belongsTo(PlanComptable::class, 'plan_comptable_id');
    }

    public function section()
    {
        return $this->belongsTo(SectionAnalytique::class, 'section_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
