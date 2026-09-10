<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\LogsActivity;

class Company extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'company_name', 'company_code', 'activity', 'juridique_form', 'social_capital',
        'adresse', 'code_postal', 'city', 'commune', 'quartier', 'country', 'phone_number',
        'email_adresse', 'identification_TVA', 'is_active', 'user_id', 'parent_company_id',
        'is_blocked', 'block_reason', 'blocked_at', 'blocked_by',
        'account_digits', 'tier_digits', 'tier_id_type', 'accounting_system',
        'journal_code_digits', 'journal_code_type',
        // Champs DGI de base
        'ncc', 'idu', 'rccm', 'cnps', 'siege_social', 'rattachement_dgi',
        'proprietaire_local', 'reference_cadastrale', 'logo_path', 'sticker_solde_alerte',
        'expert_comptable_nom', 'expert_comptable_ncc',
        'compte_contribuable', 'regime',
        'selflow_company_id', 'selflow_sync_key', 'selflow_sync_status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_blocked' => 'boolean',
        'blocked_at' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'company_id');
    }

    public function associatedUsers()
    {
        return $this->belongsToMany(User::class, 'company_user', 'company_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }

     public function admin()
    {
        return $this->belongsTo(User::class, 'user_id');
        // return $this->hasMany(User::class, 'company_id')->where('role', 'admin');
    }

    /**
     * Toutes les personnes rattachées à l'entreprise, quel que soit le mode de rattachement :
     *  - users.company_id : l'entreprise principale de la personne ;
     *  - table pivot company_user : les personnes affectées à plusieurs comptabilités ;
     *  - companies.user_id : le créateur, premier responsable de l'entreprise.
     *
     * Compter uniquement users.company_id fait apparaître à tort des entreprises
     * « sans utilisateur », alors qu'une entreprise a toujours au moins son créateur.
     *
     * Penser à charger with(['users', 'associatedUsers', 'admin']) pour éviter le N+1.
     */
    /**
     * Cette personne est-elle responsable de l'entreprise ?
     * C'est le cas de son créateur, de l'admin dont c'est l'entreprise principale
     * et de toute personne affectée avec le rôle admin.
     * Un responsable gère la comptabilité de bout en bout : exercices compris.
     */
    public function estResponsable($user): bool
    {
        if (!$user) {
            return false;
        }

        if ((int) $this->user_id === (int) $user->id) {
            return true;
        }

        if ((int) $user->company_id === (int) $this->id && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return true;
        }

        return $this->associatedUsers()
            ->where('users.id', $user->id)
            ->wherePivot('role', 'admin')
            ->exists();
    }

    public function membres()
    {
        return collect()
            ->merge($this->users)
            ->merge($this->associatedUsers)
            ->push($this->admin)
            ->filter()
            ->unique('id')
            ->sortBy(function ($user) {
                // Les administrateurs en premier, puis par nom
                return ($user->role === 'admin' ? '0' : '1') . ' ' . mb_strtolower($user->name ?? '');
            })
            ->values();
    }
      public function children()
    {
        return $this->hasMany(Company::class, 'parent_company_id');
    }

    // Ajout d'une relation pour le parent (compagnie mère)
    public function parent()
    {
        return $this->belongsTo(Company::class, 'parent_company_id');
    }

    public function childCompanies()
    {
        return $this->hasMany(Company::class, 'parent_company_id');
    }

    /**
     * Get all of the plans comptables for the company.
     */
    public function plansComptables()
    {
        return $this->hasMany(PlanComptable::class, 'company_id');
    }
    public function ecritures()
    {
        return $this->hasMany(EcritureComptable::class, 'company_id');
    }

    public function appSubscriptions()
    {
        return $this->hasMany(AppSubscription::class, 'company_id');
    }

    public function serviceHonoraires()
    {
        return $this->hasMany(ServiceHonoraire::class, 'company_id');
    }
}
