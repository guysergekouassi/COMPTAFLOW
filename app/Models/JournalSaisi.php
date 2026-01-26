<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
 use App\Models\tresoreries\Tresoreries;
 use Illuminate\Support\Facades\Auth;
use App\Traits\BelongsToTenant;
use App\Traits\BelongsToUser;

class JournalSaisi extends Model
{
    use HasFactory, BelongsToTenant, BelongsToUser;

    protected $table = 'journaux_saisis';

    protected $fillable = [
        'annee',
        'mois',
        'exercices_comptables_id',
        'code_journals_id',
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
    public function exercice()
    {
        return $this->belongsTo(ExerciceComptable::class, 'exercices_comptables_id');
    }
    public function codeJournal()
    {
        return $this->belongsTo(CodeJournal::class, 'code_journals_id');
    }
 public function tresoreries()
    {
        return $this->hasMany(Tresoreries::class, 'code_journals_id');
    }
}
