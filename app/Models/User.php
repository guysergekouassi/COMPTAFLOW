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
        'super_admin_type',
        'supervised_companies',
        'is_online',
        'company_id',
        'habilitations',
        'is_blocked',
        'block_reason',
        'blocked_at',
        'blocked_by',
        'is_active',
        'pack_id',
        'created_by_id',
    ];

    protected $casts = [
        'habilitations' => 'array',
        'supervised_companies' => 'array',
        'is_blocked' => 'boolean',
        'is_active' => 'boolean',
        'blocked_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by_id');
    }


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

    // verifier si l'utilisateur est super admin primaire (non supprimable)
    public function isPrimarySuperAdmin(): bool{
        return $this->role === 'super_admin' && $this->super_admin_type === 'primary';
    }

    // verifier si l'utilisateur est super admin secondaire (périmètre limité)
    public function isSecondarySuperAdmin(): bool{
        return $this->role === 'super_admin' && $this->super_admin_type === 'secondary';
    }

    // vérifier si le SA peut gérer une entreprise donnée
    public function canManageCompany(int $companyId): bool{
        // SA primaire peut tout gérer
        if ($this->isPrimarySuperAdmin()) return true;
        
        // SA secondaire : vérifier le périmètre
        if (!$this->isSecondarySuperAdmin()) return false;
        
        $supervised = $this->supervised_companies ?? [];
        return in_array($companyId, $supervised);
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
        // Si l'utilisateur est Super Admin, il a accès à toutes les sections de configuration.
        if ($this->isSuperAdmin()) {
            return Config::get('accounting_permissions.permissions', []);
        }

        return $this->habilitations ?? [];
    }

    public function isPrincipalAdmin(): bool
    {
        if ($this->role !== 'admin') return false;
        
        // Un admin est principal s'il a été créé par un super admin 
        // ou s'il est désigné comme l'admin de la compagnie.
        $creator = $this->creator;
        if ($creator && $creator->role === 'super_admin') return true;
        
        if ($this->company && $this->company->user_id === $this->id) return true;

        // Par défaut, si pas de créateur (seeder/migration), on considère principal
        if (!$this->created_by_id) return true;

        return false;
    }

    public function isSecondaryAdmin(): bool
    {
        return $this->role === 'admin' && !$this->isPrincipalAdmin();
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        // Les Super Admins ont tous les droits.
        if ($this->isSuperAdmin()) {
            return true;
        }
        
        // 1. Protection contre les permissions de Super Admin
        // Aucun autre rôle ne peut accéder aux routes superadmin.*
        if (str_starts_with($permission, 'superadmin.')) {
            return false;
        }

        // 2. Logique Fusion : Uniquement pour les sous-entreprises
        if ($permission === 'admin.fusion.index') {
            if (!$this->company || !$this->company->parent_company_id) {
                return false;
            }
        }

        // 3. Règle NB4: Les accès de configurations sont réservés au SEUL administrateur principal
        // Si c'est une permission de config et que l'utilisateur n'est pas admin principal (et pas SA)
        if (str_contains($permission, '.config.') && !$this->isPrincipalAdmin()) {
            // Exception : Si l'admin principal lui a explicitement donné (Règle NB4 suite)
            // On laisse la vérification finale au tableau d'habilitations
        }

        $habilitations = $this->habilitations ?? [];
        
        // 4. Admin Principal : On vérifie s'il a le droit par défaut.
        if ($this->isPrincipalAdmin() && empty($habilitations)) {
            return true;
        }

        // 5. Règle NB3: Un comptable ne peut que voir son profil, notifications et dashboard par défaut
        // Si on veut être restrictif, on pourrait filtrer ici, mais Rule NB4 permet l'octroi de droits.
        // Donc on se fie au tableau d'habilitations qui sera géré par l'admin principal.

        // Si des habilitations sont présentes, on vérifie la clé spécifiquement
        return isset($habilitations[$permission]) && ($habilitations[$permission] === "1" || $habilitations[$permission] === true || $habilitations[$permission] === 1);
    }

}
