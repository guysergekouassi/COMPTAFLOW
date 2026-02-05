<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TreasuryCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'company_id',
    ];

    /**
     * Relation avec l'entreprise.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relation avec les postes de trésorerie.
     */
    public function postes(): HasMany
    {
        return $this->hasMany(CompteTresorerie::class, 'category_id');
    }
}
