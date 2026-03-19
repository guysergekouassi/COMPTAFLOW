<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiasseMapping extends Model
{
    protected $fillable = [
        'type',
        'code_tableau',
        'titre_tableau',
        'type_tableau',
        'onglet_excel',
        'code_ligne_dgi',
        'libelle_ligne',
        'libelle_colonne',
        'code_champ_dgi',
        'cellule_excel',
        'pos_ligne',
        'pos_col',
    ];
}
