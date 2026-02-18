<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SectionAnalytique extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sections_analytiques';

    protected $fillable = [
        'axe_id',
        'code',
        'libelle',
        'company_id'
    ];

    public function axe()
    {
        return $this->belongsTo(AxeAnalytique::class, 'axe_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function ventilations()
    {
        return $this->hasMany(VentilationAnalytique::class, 'section_id');
    }

    public function getJsonDataAttribute()
    {
        return json_encode($this->only(['id', 'axe_id', 'code', 'libelle']));
    }
}
