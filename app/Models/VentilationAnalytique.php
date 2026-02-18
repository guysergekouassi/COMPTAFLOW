<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentilationAnalytique extends Model
{
    use HasFactory;

    protected $table = 'ventilations_analytiques';

    protected $fillable = [
        'ecriture_id',
        'section_id',
        'montant',
        'pourcentage'
    ];

    public function ecriture()
    {
        return $this->belongsTo(EcritureComptable::class, 'ecriture_id');
    }

    public function section()
    {
        return $this->belongsTo(SectionAnalytique::class, 'section_id');
    }
}
