<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ExerciceComptable;


use App\Traits\BelongsToTenant;


class CodeJournal extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        // 'annee',
        // 'mois',
        'code_journal',
        'intitule',
        'traitement_analytique',
        'type',
        'compte_de_contrepartie',
        'compte_de_tresorerie',
        'rapprochement_sur',
        'user_id',
        'company_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    protected static function booted()
    {
        static::created(function ($codeJournal) {
            $exercices = ExerciceComptable::where('company_id', $codeJournal->company_id)->get();

            foreach ($exercices as $exercice) {
                $exercice->syncJournaux();
            }
        });
    }


}
