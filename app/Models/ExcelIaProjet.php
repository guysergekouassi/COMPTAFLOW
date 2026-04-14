<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExcelIaProjet extends Model
{
    use HasFactory;

    protected $table = 'excel_ia_projets';

    protected $fillable = [
        'company_id',
        'user_id',
        'titre',
        'instructions',
        'couleur'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function fichiers()
    {
        return $this->hasMany(ExcelIaProjetFichier::class, 'projet_id');
    }

    public function analyses()
    {
        return $this->hasMany(ExcelIaAnalyse::class, 'projet_id');
    }
}
