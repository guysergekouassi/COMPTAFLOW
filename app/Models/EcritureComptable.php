<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Traits\BelongsToTenant;
use App\Traits\BelongsToUser;
use App\Traits\LogsActivity;

class EcritureComptable extends Model
{
    use HasFactory, BelongsToTenant, BelongsToUser, LogsActivity;

    // Nom de la table (optionnel si la table s'appelle "ecriture_comptables" par convention)
    protected $table = 'ecriture_comptables';

    // Les champs qu'on peut remplir en masse (mass assignement)
    protected $fillable = [
        'date',
        'n_saisie',
        'n_saisie_user',
        'description_operation',
        'reference_piece',
        'plan_comptable_id',  // compte_general
        'plan_tiers_id',      // compte_tiers
        'plan_analytique',    // booléen ou tinyint (0/1)
        'code_journal_id',    // imputation
        'exercices_comptables_id',    // imputation
        'journaux_saisis_id',    // imputation
        'piece_justificatif', // chemin du fichier uploadé (nullable)
        'debit',
        'credit',
        'compte_tresorerie_id',
        'type_flux',
        'user_id',
        'company_id',
        'statut',
    ];

    // Si tu utilises des dates dans ce format et veux les cast automatiquement
    // protected $casts = [
    //     'date' => 'date',
    //     'plan_analytique' => 'boolean',
    //     'debit' => 'float',
    //     'credit' => 'float',
    // ];

    // Relations (exemples, à adapter selon tes modèles)
    public function planComptable()
    {
        return $this->belongsTo(PlanComptable::class, 'plan_comptable_id');
    }

    public function planTiers()
    {
        return $this->belongsTo(PlanTiers::class, 'plan_tiers_id');
    }

    public function codeJournal()
    {
        return $this->belongsTo(CodeJournal::class, 'code_journal_id');
    }

    public function JournauxSaisis()
    {
        return $this->belongsTo(JournalSaisi::class, 'journaux_saisis_id');
    }
    public function ExerciceComptable()
    {
        return $this->belongsTo(ExerciceComptable::class, 'exercices_comptables_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function compteTresorerie()
    {
        // Assurez-vous d'importer le modèle App\Models\CompteTresorerie si ce n'est pas déjà fait
        return $this->belongsTo(CompteTresorerie::class);
    }

    public function immobilisation()
    {
        return $this->hasOne(Immobilisation::class, 'ecriture_id');
    }
}
