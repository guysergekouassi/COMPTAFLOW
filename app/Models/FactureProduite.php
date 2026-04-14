<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FactureProduite extends Model
{
    use SoftDeletes;

    protected $table = 'factures_produites';

    protected $fillable = [
        'company_id', 'user_id', 'exercice_id',
        'reference', 'n_saisie',
        'client_nom', 'client_tiers_code',
        'montant', 'devise',
        'date_facture', 'mois', 'annee',
        'nom_fichier_original', 'chemin_fichier', 'type_fichier', 'taille_fichier',
        'statut', 'notes', 'injectee_comptaflow',
    ];

    protected $casts = [
        'date_facture'        => 'date',
        'montant'             => 'decimal:2',
        'injectee_comptaflow' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function getIconAttribute(): string
    {
        return match($this->type_fichier) {
            'pdf'  => 'fa-file-pdf',
            'jpg', 'jpeg' => 'fa-file-image',
            'png'  => 'fa-file-image',
            default => 'fa-file',
        };
    }
}
