<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brouillon extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'source',
        'date',
        'n_saisie',
        'description_operation',
        'reference_piece',
        'plan_comptable_id',
        'plan_tiers_id',
        'compte_tresorerie_id',
        'type_flux',
        'plan_analytique',
        'code_journal_id',
        'exercices_comptables_id',
        'journaux_saisis_id',
        'debit',
        'credit',
        'piece_justificatif',
        'user_id',
        'company_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function planComptable()
    {
        return $this->belongsTo(PlanComptable::class);
    }

    public function planTiers()
    {
        return $this->belongsTo(PlanTiers::class);
    }

    public function codeJournal()
    {
        return $this->belongsTo(CodeJournal::class);
    }

    public function exerciceComptable()
    {
        return $this->belongsTo(ExerciceComptable::class);
    }
}
