<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Un cabinet regroupe un gérant, ses collaborateurs et leurs comptabilités.
 *
 * Toute société créée par un membre du cabinet lui revient : le collaborateur
 * n'a pas à demander la permission pour ouvrir un dossier, mais le gérant le
 * voit dans son espace.
 */
class Cabinet extends Model
{
    protected $fillable = ['nom', 'code', 'user_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /** Le gérant : celui qui a ouvert le cabinet. */
    public function gerant()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Tous les membres, gérant compris. */
    public function membres()
    {
        return $this->belongsToMany(User::class, 'cabinet_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /** Les collaborateurs, le gérant mis à part. */
    public function collaborateurs()
    {
        return $this->membres()->wherePivot('role', '!=', 'gerant');
    }

    /** Les comptabilités du cabinet, quel que soit le membre qui les a créées. */
    public function companies()
    {
        return $this->hasMany(Company::class, 'cabinet_id');
    }

    public function estGerant(?User $user): bool
    {
        return $user && (int) $this->user_id === (int) $user->id;
    }

    /**
     * Code lisible et unique, dérivé du nom : CAB-XXX-ABC123.
     * Il identifie le cabinet comme le code entreprise identifie un dossier.
     */
    public static function genererCode(string $nom): string
    {
        $lettres = preg_replace('/[^A-Za-z]/', '', $nom);
        $prefixe = strtoupper(substr($lettres, 0, 3));
        if (strlen($prefixe) < 3) {
            $prefixe = str_pad($prefixe, 3, 'X');
        }

        do {
            $code = 'CAB-' . $prefixe . '-' . strtoupper(Str::random(6));
        } while (self::where('code', $code)->exists());

        return $code;
    }
}
