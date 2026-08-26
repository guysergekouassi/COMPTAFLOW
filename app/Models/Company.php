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
        'adresse', 'code_postal', 'city', 'country', 'phone_number',
        'email_adresse', 'identification_TVA', 'is_active', 'user_id', 'parent_company_id',
        'is_blocked', 'block_reason', 'blocked_at', 'blocked_by',
        'account_digits', 'tier_digits', 'tier_id_type', 'accounting_system',
        'journal_code_digits', 'journal_code_type',
        // Champs DGI de base
        'ncc', 'rccm', 'cnps', 'siege_social', 'rattachement_dgi',
        'expert_comptable_nom', 'expert_comptable_ncc',
        'compte_contribuable', 'regime',
        'selflow_company_id', 'selflow_sync_key', 'selflow_sync_status',
        'selflow_sync_key_hash', 'selflow_sync_key_chiffree', 'selflow_sync_key_revoked_at',
        'selflow_linked_at', 'selflow_last_deposit_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_blocked' => 'boolean',
        'blocked_at' => 'datetime',
        // Sans ces trois-là, `selflow_sync_key_revoked_at` revient en chaîne et
        // le message « clé révoquée le … » ne peut pas la formater.
        'selflow_sync_key_revoked_at' => 'datetime',
        'selflow_linked_at'           => 'datetime',
        'selflow_last_deposit_at'     => 'datetime',
    ];

    /**
     * `selflow_sync_key_chiffree` ne sort jamais d'ici par accident.
     *
     * La colonne porte de quoi écrire dans les livres de l'entreprise. Elle est
     * cachée de toute sérialisation — un `toArray()` distrait dans une réponse
     * d'API la publierait. `companies/provision` la déchiffre explicitement, et
     * c'est le seul endroit qui en a besoin.
     */
    protected $hidden = ['selflow_sync_key', 'selflow_sync_key_hash', 'selflow_sync_key_chiffree'];

    /**
     * Le préfixe des clés de liaison.
     *
     * Il n'a pas d'usage technique : il permet de reconnaître un secret quand il
     * traîne — dans un journal, dans un presse-papiers, dans une capture d'écran
     * de support — et donc de savoir qu'il faut le révoquer.
     */
    public const PREFIXE_CLE_LIAISON = 'cptf_live_';

    /**
     * Génère la clé de liaison du dossier, la range, et rend sa valeur en clair.
     *
     * **Un seul endroit produit ces clés**, parce qu'il y a trois chemins qui en
     * ont besoin — `companies/provision`, et les deux écrans du
     * superadministrateur qui créent une entreprise Selflow depuis ici. Ces deux
     * derniers tiraient chacun leur propre `Str::random()` et l'écrivaient **en
     * clair** dans `selflow_sync_key` : depuis que la reconnaissance se fait par
     * haché, une clé posée ainsi n'aurait plus été reconnue par personne.
     *
     * C'est le seul instant où la clé existe en clair côté Comptaflow — le temps
     * de la remettre à l'appelant.
     */
    public function poserUneCleDeLiaison(): string
    {
        $cle = self::PREFIXE_CLE_LIAISON . \Illuminate\Support\Str::random(40);

        $this->forceFill([
            // La colonne en clair reste vide : c'est tout l'objet du changement.
            'selflow_sync_key'            => null,
            'selflow_sync_key_hash'       => hash('sha256', $cle),
            // La copie chiffrée n'existe que pour l'idempotence de `provision` :
            // rappelé pour une entreprise déjà provisionnée, il doit rendre *la
            // même* clé, et un haché ne se retourne pas.
            'selflow_sync_key_chiffree'   => \Illuminate\Support\Facades\Crypt::encryptString($cle),
            'selflow_sync_key_revoked_at' => null,
            'selflow_sync_status'         => 'active',
            'selflow_linked_at'           => now(),
        ])->save();

        return $cle;
    }

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
