<?php

namespace App\Models\tresoreries;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tresoreries extends Model
{
use HasFactory;

    // Définir le nom de la table (facultatif, si le nom de la table ne suit pas la convention Laravel)
    protected $table = 'tresorerie';

    // Les champs que vous pouvez remplir via l'attribution de masse
    protected $fillable = [
        'code_journal',
        'intitule',
        'traitement_analytique',
        'compte_de_contrepartie',
        'rapprochement_sur',
        'poste_tresorerie',
        'categorie',
        'type_flux',
        'user_id',
        'company_id',
    ];

    // Définir les relations avec d'autres modèles

    // Relation avec PlanComptable (contrepartie)
    public function planComptable()
    {
        return $this->belongsTo('App\Models\PlanComptable', 'compte_de_contrepartie');
    }

    // Relation avec User
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    // Relation avec Company
    public function company()
    {
        return $this->belongsTo('App\Models\Company');
    }
}
