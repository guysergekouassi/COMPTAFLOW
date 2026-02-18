<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AxeAnalytique extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'axes_analytiques';

    protected $fillable = [
        'code',
        'libelle',
        'type',
        'company_id'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function sections()
    {
        return $this->hasMany(SectionAnalytique::class, 'axe_id');
    }

    public function getJsonDataAttribute()
    {
        return json_encode($this->only(['id', 'code', 'libelle', 'type']));
    }
}
