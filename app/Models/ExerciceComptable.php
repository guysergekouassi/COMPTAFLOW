<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\JournalSaisi;
use App\Models\CodeJournal;

use App\Traits\BelongsToTenant;


class ExerciceComptable extends Model
{
    use HasFactory, BelongsToTenant;
    protected $table = 'exercices_comptables';
    
    public static $rules = [
        'date_debut' => 'required|date',
        'date_fin' => 'required|date|after_or_equal:date_debut',
        'intitule' => 'required|string|max:255',
    ];
    
    protected $fillable = [
        'date_debut',
        'date_fin',
        'intitule',
        'nombre_journaux_saisis',
        'cloturer',
        'user_id',
        'company_id',
    ];
    
    public function scopeForCompany($query, $companyId = null)
    {
        return $query->where('company_id', $companyId ?? auth()->user()->company_id);
    }

    /**
     * Relation avec les journaux saisis
     */
    public function journauxSaisis()
    {
        return $this->hasMany(JournalSaisi::class, 'exercices_comptables_id');
    }

    /**
     * Synchronise les journaux pour cet exercice
     */
    public function syncJournaux()
    {
        $companyId = $this->company_id;
        $userId = $this->user_id;

        $start = \Carbon\Carbon::parse($this->date_debut)->startOfMonth();
        $end = \Carbon\Carbon::parse($this->date_fin)->startOfMonth();

        $codeJournals = CodeJournal::where('company_id', $companyId)->get();

        while ($start->lte($end)) {
            foreach ($codeJournals as $codeJournal) {
                // Vérifie si le journal existe déjà pour éviter doublons
                $exists = JournalSaisi::where('exercices_comptables_id', $this->id)
                    ->where('annee', $start->year)
                    ->where('mois', $start->month)
                    ->where('code_journals_id', $codeJournal->id)
                    ->exists();

                if (!$exists) {
                    JournalSaisi::create([
                        'annee' => $start->year,
                        'mois' => $start->month,
                        'exercices_comptables_id' => $this->id,
                        'code_journals_id' => $codeJournal->id,
                        'user_id' => $userId,
                        'company_id' => $companyId,
                    ]);
                }
            }
            $start->addMonth();
        }

        // Mise à jour du nombre total de journaux
        $this->update([
            'nombre_journaux_saisis' => JournalSaisi::where('exercices_comptables_id', $this->id)->count()
        ]);
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }



}
