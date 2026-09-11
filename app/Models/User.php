<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Config; 
use App\Traits\LogsActivity;

use Laravel\Sanctum\HasApiTokens;

use App\Models\Cabinet;
use App\Models\Company;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use \Laravel\Sanctum\HasApiTokens, HasFactory, Notifiable, LogsActivity;

    protected $fillable = [
        'name',
        'last_name',
        'email_adresse',
        'password',
        'role',
        'pack',
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
        'google_id',
        'avatar',
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

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'company_user', 'user_id', 'company_id')
            ->withPivot('role')
            ->withTimestamps();
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
        return $this->role === 'super_admin' && ($this->super_admin_type === 'primary' || $this->super_admin_type === null);
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


    /**
     * Une comptabilité ne disparaît pas avec la personne qui l'a ouverte.
     *
     * Le dossier reste, et revient au gérant du cabinet qui le porte : il
     * continue de le voir, de l'ouvrir et d'en répondre. Quand c'est le gérant
     * lui-même qui s'en va, le cabinet passe au plus ancien de ses membres.
     */
    protected static function booted(): void
    {
        static::deleting(function (User $partant) {
            foreach (Cabinet::where('user_id', $partant->id)->get() as $cabinet) {
                $successeur = DB::table('cabinet_user')
                    ->where('cabinet_id', $cabinet->id)
                    ->where('user_id', '!=', $partant->id)
                    ->orderBy('id')
                    ->value('user_id');

                if ($successeur) {
                    $cabinet->user_id = $successeur;
                    $cabinet->save();
                    DB::table('cabinet_user')
                        ->where('cabinet_id', $cabinet->id)
                        ->where('user_id', $successeur)
                        ->update(['role' => 'gerant', 'updated_at' => now()]);
                }
            }

            // Ses dossiers reviennent au gérant du cabinet qui les porte
            foreach (Company::where('user_id', $partant->id)->get() as $dossier) {
                $repreneur = $dossier->cabinet_id
                    ? Cabinet::where('id', $dossier->cabinet_id)->value('user_id')
                    : null;

                if (!$repreneur || (int) $repreneur === (int) $partant->id) {
                    continue;
                }

                $dossier->user_id = $repreneur;
                $dossier->save();

                // Sans ligne d'affectation, le repreneur verrait le dossier
                // sans pouvoir y ouvrir d'exercice.
                DB::table('company_user')->updateOrInsert(
                    ['company_id' => $dossier->id, 'user_id' => $repreneur],
                    ['role' => 'admin', 'updated_at' => now(), 'created_at' => now()]
                );
            }

            DB::table('cabinet_user')->where('user_id', $partant->id)->delete();
        });
    }

    public function ecritures()
    {
        return $this->hasMany(EcritureComptable::class, 'user_id');
    }

    public function getHabilitations(): array
    {
        // SA Primaire : Accès à tout
        if ($this->isPrimarySuperAdmin()) {
            return Config::get('accounting_permissions.permissions', []);
        }

        // Pour les autres (SA Secondaire, Admin, Comptable), on retourne les habilitations stockées
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
     * Offre « Pack Entreprise » : une seule comptabilité, celle créée à
     * l'inscription. Pas d'espace cabinet, pas de nouvelle société, pas de
     * fusion, jusqu'à un passage sur une offre supérieure.
     */
    public function estPackEntreprise(): bool
    {
        return ($this->pack ?? 'cabinet') === 'entreprise';
    }

    /** Libellé de l'offre souscrite. */
    public function libellePack(): string
    {
        return $this->estPackEntreprise() ? 'Pack Entreprise' : 'Pack Cabinet';
    }

    /**
     * Accorder toutes les habilitations métier, hors section Super Admin.
     *
     * Utilisé pour le responsable d'une comptabilité : sans cela, des pages
     * entières (Hub des Tiers, états, configuration) lui restent invisibles.
     */
    public function accorderToutesLesHabilitationsMetier(): void
    {
        $this->habilitations = array_merge($this->habilitations ?? [], self::catalogueMetier());
        $this->save();
    }

    /**
     * Mon Espace est ouvert à tout le monde, Pack Entreprise compris : chacun y
     * retrouve ses comptabilités et ses collaborateurs. Ce que l'offre décide,
     * c'est le droit d'ouvrir de nouvelles sociétés et de fusionner.
     */
    public function aAccesMonEspace(): bool
    {
        return !$this->isSuperAdmin();
    }

    /** Ouvrir d'autres comptabilités : réservé aux offres multi-dossiers. */
    public function peutCreerDesSocietes(): bool
    {
        return !$this->isSuperAdmin() && !$this->estPackEntreprise();
    }

    /** La fusion suppose plusieurs dossiers : même règle que la création. */
    public function peutFusionner(): bool
    {
        return $this->peutCreerDesSocietes();
    }

    /** Les cabinets auxquels cette personne appartient. */
    public function cabinets()
    {
        return $this->belongsToMany(Cabinet::class, 'cabinet_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /** Le cabinet dont cette personne est la gérante, s'il existe. */
    public function cabinetGere()
    {
        return $this->hasOne(Cabinet::class, 'user_id');
    }

    public function estGerantDeCabinet(): bool
    {
        return Cabinet::where('user_id', $this->id)->exists();
    }

    /**
     * Catalogue complet des habilitations métier, hors section Super Admin.
     * Sert d'accès total pour un responsable de dossier.
     */
    public static function catalogueMetier(): array
    {
        $habilitations = [];

        foreach (Config::get('accounting_permissions.permissions', []) as $section => $permissions) {
            if (!is_array($permissions) || str_contains($section, 'Super Admin')) {
                continue;
            }
            foreach (array_keys($permissions) as $cle) {
                $habilitations[$cle] = "1";
            }
        }

        return $habilitations;
    }

    /**
     * Droits accordés sur la comptabilité ouverte, et sur elle seule.
     *
     * Une affectation porte ses propres habilitations : elles ne valent que
     * pour ce dossier et ne touchent pas à celles reçues ailleurs. Renvoie
     * null quand l'affectation ne dit rien de particulier, auquel cas les
     * habilitations du compte s'appliquent.
     */
    public function habilitationsSurDossierCourant(): ?array
    {
        $companyId = session('current_company_id', $this->company_id);
        if (!$companyId || !$this->id) {
            return null;
        }

        $pivot = DB::table('company_user')
            ->where('company_id', $companyId)
            ->where('user_id', $this->id)
            ->first();

        if (!$pivot) {
            return null;
        }

        // Accès total sur ce dossier
        if (($pivot->role ?? null) === 'admin') {
            return self::catalogueMetier();
        }

        $choisies = !empty($pivot->habilitations) ? json_decode($pivot->habilitations, true) : null;

        return is_array($choisies) && $choisies !== [] ? $choisies : null;
    }

    /**
     * Cette personne gère-t-elle la comptabilité ouverte ?
     *
     * Vrai pour un administrateur, un super administrateur, et pour le
     * responsable de l'entreprise courante (créateur, admin de l'entreprise ou
     * personne affectée avec le rôle admin) — y compris un comptable qui gère
     * ses propres comptabilités depuis Mon Espace. Sans cela, il ne pourrait
     * pas ouvrir d'exercice et sa comptabilité resterait inutilisable.
     */
    public function gereLaComptabiliteCourante(): bool
    {
        if ($this->isAdmin() || $this->isSuperAdmin()) {
            return true;
        }

        $companyId = session('current_company_id', $this->company_id);
        if (!$companyId) {
            return false;
        }

        $company = Company::find($companyId);

        return $company ? $company->estResponsable($this) : false;
    }

    /**
     * Check if user has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        // 1. SA Primaire : Toujours accès à TOUT
        if ($this->isPrimarySuperAdmin()) {
            return true;
        }

        // 2. Logique Fusion : Uniquement pour les sous-entreprises (Sauf Super Admin)
        if ($permission === 'admin.fusion.index' && !$this->isSuperAdmin()) {
            if (!$this->company || !$this->company->parent_company_id) {
                return false;
            }
        }

        // 3. Protection SuperAdmin : Seuls les SuperAdmins (Primaire ou Secondaire) peuvent accéder aux routes superadmin.*
        // Si c'est une permission superadmin et que l'utilisateur n'est pas SA
        if (str_starts_with($permission, 'superadmin.') && !$this->isSuperAdmin()) {
            return false;
        }

        // 3 bis. Droits accordés sur la comptabilité ouverte : ils priment, et
        // ne concernent qu'elle. Sans affectation particulière, on retombe sur
        // les habilitations du compte.
        $surDossier = $this->habilitationsSurDossierCourant();
        if ($surDossier !== null && !$this->isSuperAdmin()) {
            return isset($surDossier[$permission])
                && ($surDossier[$permission] === "1" || $surDossier[$permission] === true || $surDossier[$permission] === 1);
        }

        $habilitations = $this->habilitations ?? [];

        // 4. Super Admin Secondaire : On vérifie s'il a le droit explicitement (s'il n'est pas primaire)
        // Note: Si on veut qu'il ait TOUT par défaut s'il n'a rien de configuré, on peut ajuster.
        // Mais ici on suit la logique granulaire demandée.
        if ($this->isSecondarySuperAdmin()) {
            return isset($habilitations[$permission]) && ($habilitations[$permission] === "1" || $habilitations[$permission] === true || $habilitations[$permission] === 1);
        }

        // 5. Admin Principal : On vérifie s'il a le droit par défaut (s'il n'a pas d'habilitations personnalisées).
        if ($this->isPrincipalAdmin() && empty($habilitations)) {
            return true;
        }

        // 6. Règle NB4: Les accès de configurations sont réservés au SEUL administrateur principal
        if (str_contains($permission, '.config.') && !$this->isPrincipalAdmin() && !$this->isSuperAdmin()) {
            // Laisse la vérification finale au tableau d'habilitations
        }

        // 7. Vérification finale par clé d'habilitation
        return isset($habilitations[$permission]) && ($habilitations[$permission] === "1" || $habilitations[$permission] === true || $habilitations[$permission] === 1);
    }

}
