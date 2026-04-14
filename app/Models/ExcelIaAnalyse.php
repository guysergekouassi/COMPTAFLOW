<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExcelIaAnalyse extends Model
{
    protected $table = 'excel_ia_analyses';

    protected $fillable = [
        'company_id', 'projet_id', 'user_id', 'exercice_id',
        'fichiers_noms', 'mois_cible',
        'ecritures_json', 'rapport_transparence', 'notes_utilisateur',
        'statut', 'erreur_message',
        'injecte_bdd', 'txt_telecharge', 'injecte_le',
        'nb_ecritures', 'total_debit', 'total_credit',
    ];

    protected $casts = [
        'injecte_bdd'    => 'boolean',
        'txt_telecharge' => 'boolean',
        'injecte_le'     => 'datetime',
        'total_debit'    => 'decimal:2',
        'total_credit'   => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getFichiersNomsArrayAttribute(): array
    {
        return json_decode($this->fichiers_noms ?? '[]', true) ?? [];
    }

    public function getEquilibreAttribute(): bool
    {
        return abs($this->total_debit - $this->total_credit) < 0.01;
    }

    public function projet(): BelongsTo
    {
        return $this->belongsTo(ExcelIaProjet::class, 'projet_id');
    }
}
