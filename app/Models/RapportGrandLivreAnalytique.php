<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RapportGrandLivreAnalytique extends Model
{
    protected $table = 'rapports_grand_livre_analytiques';

    protected $fillable = [
        'company_id',
        'user_id',
        'axe_analytique_id',
        'tous_axes',
        'section_de_id',
        'section_a_id',
        'axe_libelle',
        'section_de_libelle',
        'section_a_libelle',
        'toutes_sections',
        'date_debut',
        'date_fin',
        'toute_periode',
        'format',
        'fichier',
        'nb_mouvements',
    ];

    protected $casts = [
        'tous_axes'       => 'boolean',
        'toutes_sections' => 'boolean',
        'toute_periode'   => 'boolean',
        'date_debut'      => 'date',
        'date_fin'        => 'date',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function axe()
    {
        return $this->belongsTo(AxeAnalytique::class, 'axe_analytique_id');
    }

    /** URL publique du fichier généré */
    public function getFileUrlAttribute(): ?string
    {
        return $this->fichier ? asset('rapports_analytiques/' . $this->fichier) : null;
    }
}

