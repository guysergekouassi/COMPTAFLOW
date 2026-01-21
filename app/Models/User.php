<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Config; 
use App\Traits\LogsActivity;

class User extends Authenticatable
{
    use HasFactory, Notifiable, LogsActivity;

    protected $fillable = [
        'name',
        'last_name',
        'email_adresse',
        'password',
        'role',
        'is_online',
        'company_id',
        'habilitations',
        'is_blocked',
        'block_reason',
        'blocked_at',
        'blocked_by',
        'is_active',
        'pack_id',
    ];

    protected $casts = [
        'habilitations' => 'array',
        'is_blocked' => 'boolean',
        'is_active' => 'boolean',
        'blocked_at' => 'datetime',
    ];


    protected $hidden = ['password'];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }


    public function getInitialesAttribute()
    {
        return strtoupper(
            mb_substr(trim($this->last_name ?? ''), 0, 1) .
            mb_substr(trim($this->name ?? ''), 0, 1)
        ) ?: 'U';
    }

    // verifier si l'utilisateur est admin ou super admin
    public function isAdmin(): bool{
        return $this->role ==="admin"||  $this->role ==="super_admin";
    }
     // verifier si l'utilisateur est strictement super admin
    public function isSuperAdmin(): bool{
       return $this->role ==="super_admin";
    }

    // verifier si l'utilisateur est comptable
    public function isComptable(): bool{
        return $this->role ==="comptable";
    }


    public function ecritures()
    {
        return $this->hasMany(EcritureComptable::class, 'user_id');
    }

    public function getHabilitations(): array
    {
        // Si l'utilisateur est Super Admin (au niveau plateforme), il a toutes les permissions.
        if ($this->isSuperAdmin()) {
            return Config::get('accounting_permissions.permissions', []);
        }

        // Pour les utilisateurs des comptes comptabilité ('admin' de compagnie, 'comptable', etc.),
        // on retourne les habilitations spécifiques stockées dans la colonne 'habilitations'.
        return $this->habilitations ?? [];
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        // Super admins and admins have all permissions
        if ($this->isAdmin()) {
            return true;
        }
        
        // For comptables, check their specific habilitations
        $habilitations = $this->habilitations ?? [];
        return isset($habilitations[$permission]) && $habilitations[$permission] === "1";
    }

}
