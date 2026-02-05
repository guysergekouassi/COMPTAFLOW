<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Amortissement extends Model
{
    use HasFactory;

    protected $fillable = [
        'immobilisation_id',
        'exercice_id',
        'annee',
        'base_amortissable',
        'dotation_annuelle',
        'cumul_amortissement',
        'valeur_nette_comptable',
        'ecriture_comptable_id',
        'statut',
        'date_comptabilisation',
    ];

    protected $casts = [
        'annee' => 'integer',
        'base_amortissable' => 'decimal:2',
        'dotation_annuelle' => 'decimal:2',
        'cumul_amortissement' => 'decimal:2',
        'valeur_nette_comptable' => 'decimal:2',
        'date_comptabilisation' => 'date',
    ];

    // Relations
    public function immobilisation()
    {
        return $this->belongsTo(Immobilisation::class);
    }

    public function exercice()
    {
        return $this->belongsTo(ExerciceComptable::class, 'exercice_id');
    }

    public function ecritureComptable()
    {
        return $this->belongsTo(EcritureComptable::class, 'ecriture_comptable_id');
    }

    // Scopes
    public function scopeComptabilise($query)
    {
        return $query->where('statut', 'comptabilise');
    }

    public function scopePrevisionnel($query)
    {
        return $query->where('statut', 'previsionnel');
    }

    public function scopeParAnnee($query, $annee)
    {
        return $query->where('annee', $annee);
    }

    // Méthodes métier
    public function marquerCommeComptabilise($ecritureId)
    {
        $this->update([
            'statut' => 'comptabilise',
            'ecriture_comptable_id' => $ecritureId,
            'date_comptabilisation' => now(),
        ]);
    }

    public function estComptabilise()
    {
        return $this->statut === 'comptabilise';
    }
}
