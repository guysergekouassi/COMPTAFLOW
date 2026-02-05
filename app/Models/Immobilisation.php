<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Immobilisation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'exercice_id',
        'code',
        'libelle',
        'categorie',
        'description',
        'compte_immobilisation_id',
        'compte_amortissement_id',
        'compte_dotation_id',
        'date_acquisition',
        'valeur_acquisition',
        'fournisseur',
        'numero_facture',
        'date_mise_en_service',
        'duree_amortissement',
        'methode_amortissement',
        'taux_amortissement',
        'valeur_residuelle',
        'statut',
        'date_cession',
        'montant_cession',
        'compte_cession_id',
        'motif_cession',
        'ecriture_id',
    ];

    protected $casts = [
        'date_acquisition' => 'date',
        'date_mise_en_service' => 'date',
        'date_cession' => 'date',
        'valeur_acquisition' => 'decimal:2',
        'taux_amortissement' => 'decimal:2',
        'valeur_residuelle' => 'decimal:2',
        'montant_cession' => 'decimal:2',
    ];

    // Relations
    public function ecriture()
    {
        return $this->belongsTo(EcritureComptable::class, 'ecriture_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function exercice()
    {
        return $this->belongsTo(ExerciceComptable::class, 'exercice_id');
    }

    public function compteImmobilisation()
    {
        return $this->belongsTo(PlanComptable::class, 'compte_immobilisation_id');
    }

    public function compteAmortissement()
    {
        return $this->belongsTo(PlanComptable::class, 'compte_amortissement_id');
    }

    public function compteDotation()
    {
        return $this->belongsTo(PlanComptable::class, 'compte_dotation_id');
    }

    public function compteCession()
    {
        return $this->belongsTo(PlanComptable::class, 'compte_cession_id');
    }

    public function amortissements()
    {
        return $this->hasMany(Amortissement::class);
    }

    // Scopes
    public function scopeEnCours($query)
    {
        return $query->where('statut', 'en_cours');
    }

    public function scopeParCategorie($query, $categorie)
    {
        return $query->where('categorie', $categorie);
    }

    // Méthodes métier
    public function getValeurNetteComptable()
    {
        $cumulAmortissement = $this->amortissements()
            ->where('statut', 'comptabilise')
            ->sum('dotation_annuelle');
        
        return $this->valeur_acquisition - $cumulAmortissement;
    }

    public function getTauxAmortissement()
    {
        if ($this->methode_amortissement === 'lineaire') {
            return 100 / $this->duree_amortissement;
        }
        
        // Dégressif
        $coefficient = $this->getCoefficientDegressif();
        return (100 / $this->duree_amortissement) * $coefficient;
    }

    public function getCoefficientDegressif()
    {
        if ($this->duree_amortissement <= 4) {
            return 1.5;
        } elseif ($this->duree_amortissement <= 6) {
            return 2.0;
        } else {
            return 2.5;
        }
    }

    public function estTotalementAmorti()
    {
        return $this->getValeurNetteComptable() <= $this->valeur_residuelle;
    }

    public function calculerDotationAnnuelle($annee)
    {
        $dateDebut = max(
            $this->date_mise_en_service,
            now()->startOfYear()
        );
        
        if ($this->methode_amortissement === 'lineaire') {
            return $this->calculerDotationLineaire($annee);
        }
        
        return $this->calculerDotationDegressive($annee);
    }

    private function calculerDotationLineaire($annee)
    {
        $baseAmortissable = $this->valeur_acquisition - $this->valeur_residuelle;
        $dotationAnnuelle = $baseAmortissable / $this->duree_amortissement;
        
        // Prorata temporis pour la première année
        if ($annee == $this->date_mise_en_service->year) {
            $moisRestants = 12 - $this->date_mise_en_service->month + 1;
            return ($dotationAnnuelle * $moisRestants) / 12;
        }
        
        return $dotationAnnuelle;
    }

    private function calculerDotationDegressive($annee)
    {
        $vnc = $this->getValeurNetteComptable();
        $taux = $this->getTauxAmortissement() / 100;
        $dotation = $vnc * $taux;
        
        // Basculement en linéaire si plus avantageux
        $anneesRestantes = $this->duree_amortissement - 
            ($annee - $this->date_mise_en_service->year);
        
        if ($anneesRestantes > 0) {
            $dotationLineaire = $vnc / $anneesRestantes;
            if ($dotationLineaire > $dotation) {
                return $dotationLineaire;
            }
        }
        
        return $dotation;
    }
}
