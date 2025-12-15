<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class journalsTresorerie extends Model
{
    //
    use HasFactory;
    protected $table = "journals_tresorerie";
    protected $fillable = [
         'code_journal',
        'intitule',
        'traitement_analytique',
        'compte_de_contrepartie',
        'rapprochement_sur',
        'user_id',
        'company_id',
    ] ;

    // Relations
    public function compte()
    {
        return $this->belongsTo(PlanComptable::class, 'compte_de_contrepartie');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    // Casts pour les enums (optionnel, pour faciliter l'usage)
    protected $casts = [
        'traitement_analytique' => 'string',
        'rapprochement_sur' => 'string',
    ];
}

