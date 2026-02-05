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
        'code_journal',
        'numero_original',
        'intitule',
        'type',
        'compte_de_tresorerie',
        'traitement_analytique',
        'rapprochement_sur',
        'poste_tresorerie',
        'compte_de_contrepartie',
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

    public function account()
    {
        return $this->belongsTo(PlanComptable::class, 'compte_de_tresorerie');
    }

    public function getCodeTresorerieDisplayAttribute()
    {
        if ($this->account) {
            return $this->account->numero_de_compte;
        }
        return $this->compte_de_contrepartie ?? '-';
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
