<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name', 'activity', 'juridique_form', 'social_capital',
        'adresse', 'code_postal', 'city', 'country', 'phone_number',
        'email_adresse', 'identification_TVA','is_active','user_id','parent_company_id',
        'is_blocked', 'block_reason', 'blocked_at', 'blocked_by',
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
}
