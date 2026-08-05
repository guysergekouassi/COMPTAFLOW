<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcritureModele extends Model
{
    use HasFactory;

    protected $table = 'ecriture_modeles';

    protected $fillable = [
        'nom',
        'code_journal_id',
        'lignes',
        'company_id',
        'user_id',
    ];

    protected $casts = [
        'lignes' => 'array',
    ];

    public function codeJournal()
    {
        return $this->belongsTo(CodeJournal::class, 'code_journal_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
