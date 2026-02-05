<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\JournalSaisi;
use App\Models\CodeJournal;
use App\Models\Company;

use App\Traits\BelongsToTenant;
use App\Traits\BelongsToUser;


class ExerciceComptable extends Model
{
    use HasFactory, BelongsToTenant, BelongsToUser;
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
        'parent_company_id',
        'is_active',
    ];
    
    protected $dates = [
        'date_debut',
        'date_fin',
        'created_at',
        'updated_at',
    ];
    
    protected $casts = [
        'date_debut' => 'date:Y-m-d',
        'date_fin' => 'date:Y-m-d',
        'cloturer' => 'boolean',
        'is_active' => 'boolean',
        'nombre_journaux_saisis' => 'integer',
    ];
    
    /**
     * Scope pour filtrer par société (inclut les sociétés filles)
     */
    public function scopeForCompany($query, $companyId = null)
    {
        $companyId = $companyId ?? (auth()->check() ? auth()->user()->company_id : null);
        
        if (!$companyId) {
            return $query;
        }
        
        return $query->where(function($q) use ($companyId) {
            $q->where('company_id', $companyId)
              ->orWhere('parent_company_id', $companyId);
        });
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
        $realEnd = \Carbon\Carbon::parse($this->date_fin);
        $codeJournals = CodeJournal::where('company_id', $companyId)->get();
        
        // Optimisation : Récupérer tous les journaux saisis existants pour cet exercice une seule fois
        $existingRecords = JournalSaisi::where('exercices_comptables_id', $this->id)
            ->select('annee', 'mois', 'code_journals_id')
            ->get()
            ->groupBy(['annee', 'mois', 'code_journals_id']);

        $newRecords = [];

        while ($start->lte($realEnd->copy()->startOfMonth())) {
            if ($start->isSameMonth($realEnd) && $realEnd->day == 1 && !$start->isSameMonth(\Carbon\Carbon::parse($this->date_debut))) {
                break;
            }
            
            foreach ($codeJournals as $codeJournal) {
                // Vérification en mémoire via le groupBy
                $exists = isset($existingRecords[$start->year][$start->month][$codeJournal->id]);

                if (!$exists) {
                    $newRecords[] = [
                        'annee' => $start->year,
                        'mois' => $start->month,
                        'exercices_comptables_id' => $this->id,
                        'code_journals_id' => $codeJournal->id,
                        'user_id' => $userId,
                        'company_id' => $companyId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            $start->addMonth();
        }

        if (!empty($newRecords)) {
            // Insertion groupée pour les performances
            JournalSaisi::insert($newRecords);
        }

        // Mise à jour du nombre total de journaux
        $this->update([
            'nombre_journaux_saisis' => JournalSaisi::where('exercices_comptables_id', $this->id)->count()
        ]);
    }


    /**
     * Get the user that owns the exercice comptable.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the company that owns the exercice comptable.
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
    
    /**
     * Get the parent company that owns the exercice comptable.
     */
    public function parentCompany()
    {
        return $this->belongsTo(Company::class, 'parent_company_id');
    }

    /**
     * Vérifie si l'exercice appartient à une société spécifique
     */
    public function belongsToCompany($companyId)
    {
        return $this->company_id == $companyId || $this->parent_company_id == $companyId;
    }




}
