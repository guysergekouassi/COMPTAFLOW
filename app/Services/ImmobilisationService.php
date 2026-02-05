<?php

namespace App\Services;

use App\Models\Immobilisation;
use App\Models\Amortissement;
use App\Models\EcritureComptable;
use App\Models\ExerciceComptable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImmobilisationService
{
    /**
     * Créer une nouvelle immobilisation
     */
    public function creerImmobilisation(array $data)
    {
        DB::beginTransaction();
        try {
            // Générer un code unique si non fourni
            if (empty($data['code'])) {
                $data['code'] = $this->genererCodeUnique($data['company_id'], $data['categorie']);
            }

            // Calculer le taux d'amortissement si non fourni
            if (empty($data['taux_amortissement'])) {
                $data['taux_amortissement'] = $this->calculerTauxAmortissement(
                    $data['duree_amortissement'],
                    $data['methode_amortissement']
                );
            }

            $immobilisation = Immobilisation::create($data);

            // Générer le tableau d'amortissement prévisionnel
            $this->genererTableauAmortissement($immobilisation);

            DB::commit();
            return $immobilisation;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création immobilisation: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Générer le tableau d'amortissement prévisionnel
     */
    public function genererTableauAmortissement(Immobilisation $immobilisation)
    {
        $anneeDebut = $immobilisation->date_mise_en_service->year;
        $anneeFin = $anneeDebut + $immobilisation->duree_amortissement;
        
        $cumulAmortissement = 0;
        $vnc = $immobilisation->valeur_acquisition;

        for ($annee = $anneeDebut; $annee < $anneeFin; $annee++) {
            // Calculer la dotation pour cette année
            $dotation = $this->calculerDotationAnnuelle($immobilisation, $annee, $cumulAmortissement);
            
            $cumulAmortissement += $dotation;
            $vnc = $immobilisation->valeur_acquisition - $cumulAmortissement;

            // Créer ou mettre à jour l'amortissement
            Amortissement::updateOrCreate(
                [
                    'immobilisation_id' => $immobilisation->id,
                    'annee' => $annee,
                ],
                [
                    'exercice_id' => $this->getExerciceParAnnee($immobilisation->company_id, $annee),
                    'base_amortissable' => $immobilisation->valeur_acquisition - $immobilisation->valeur_residuelle,
                    'dotation_annuelle' => $dotation,
                    'cumul_amortissement' => $cumulAmortissement,
                    'valeur_nette_comptable' => max($vnc, $immobilisation->valeur_residuelle),
                    'statut' => 'previsionnel',
                ]
            );

            // Arrêter si VNC atteint la valeur résiduelle
            if ($vnc <= $immobilisation->valeur_residuelle) {
                break;
            }
        }

        return $immobilisation->amortissements;
    }

    /**
     * Calculer la dotation annuelle
     */
    private function calculerDotationAnnuelle(Immobilisation $immobilisation, int $annee, float $cumulAmortissement)
    {
        $vnc = $immobilisation->valeur_acquisition - $cumulAmortissement;
        $baseAmortissable = $immobilisation->valeur_acquisition - $immobilisation->valeur_residuelle;

        if ($immobilisation->methode_amortissement === 'lineaire') {
            return $this->calculerDotationLineaire($immobilisation, $annee, $baseAmortissable);
        }

        return $this->calculerDotationDegressive($immobilisation, $annee, $vnc, $cumulAmortissement);
    }

    /**
     * Calcul linéaire
     */
    private function calculerDotationLineaire(Immobilisation $immobilisation, int $annee, float $baseAmortissable)
    {
        $dotationAnnuelle = $baseAmortissable / $immobilisation->duree_amortissement;

        // Prorata temporis pour la première année
        if ($annee == $immobilisation->date_mise_en_service->year) {
            $moisRestants = 12 - $immobilisation->date_mise_en_service->month + 1;
            return round(($dotationAnnuelle * $moisRestants) / 12, 2);
        }

        return round($dotationAnnuelle, 2);
    }

    /**
     * Calcul dégressif
     */
    private function calculerDotationDegressive(Immobilisation $immobilisation, int $annee, float $vnc, float $cumulAmortissement)
    {
        // Coefficient dégressif selon la durée
        $coefficient = $this->getCoefficientDegressif($immobilisation->duree_amortissement);
        $tauxDegressif = (100 / $immobilisation->duree_amortissement) * $coefficient;
        
        $dotationDegressive = round($vnc * ($tauxDegressif / 100), 2);

        // Années restantes
        $anneesEcoulees = $annee - $immobilisation->date_mise_en_service->year;
        $anneesRestantes = $immobilisation->duree_amortissement - $anneesEcoulees;

        // Basculement en linéaire si plus avantageux
        if ($anneesRestantes > 0) {
            $dotationLineaire = round($vnc / $anneesRestantes, 2);
            if ($dotationLineaire > $dotationDegressive) {
                return $dotationLineaire;
            }
        }

        // Prorata temporis pour la première année
        if ($annee == $immobilisation->date_mise_en_service->year) {
            $moisRestants = 12 - $immobilisation->date_mise_en_service->month + 1;
            return round(($dotationDegressive * $moisRestants) / 12, 2);
        }

        return $dotationDegressive;
    }

    /**
     * Générer les dotations annuelles pour un exercice
     */
    public function genererDotationsAnnuelles(int $exerciceId, int $companyId)
    {
        $exercice = ExerciceComptable::findOrFail($exerciceId);
        $annee = Carbon::parse($exercice->date_debut)->year;

        // Récupérer toutes les immobilisations en cours
        $immobilisations = Immobilisation::where('company_id', $companyId)
            ->where('statut', 'en_cours')
            ->whereYear('date_mise_en_service', '<=', $annee)
            ->get();

        $compteur = 0;
        DB::beginTransaction();
        try {
            foreach ($immobilisations as $immobilisation) {
                // Vérifier si l'amortissement existe pour cette année
                $amortissement = Amortissement::where('immobilisation_id', $immobilisation->id)
                    ->where('annee', $annee)
                    ->first();

                if ($amortissement && $amortissement->statut === 'previsionnel') {
                    // Générer l'écriture comptable
                    $this->genererEcritureDotation($amortissement, $exercice);
                    $compteur++;
                }
            }

            DB::commit();
            return $compteur;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur génération dotations: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Générer l'écriture comptable de dotation
     */
    public function genererEcritureDotation(Amortissement $amortissement, ExerciceComptable $exercice)
    {
        $immobilisation = $amortissement->immobilisation;
        
        // Date de l'écriture : dernier jour de l'exercice
        $dateEcriture = Carbon::parse($exercice->date_fin);
        $nSaisie = $this->getProchainNumeroSaisie();

        // Créer l'écriture de dotation
        $ecriture = EcritureComptable::create([
            'company_id' => $immobilisation->company_id,
            'exercices_comptables_id' => $exercice->id,
            'code_journal_id' => $this->getJournalOperationsDiverses($immobilisation->company_id),
            'date' => $dateEcriture,
            'n_saisie' => $nSaisie,
            'reference_piece' => 'DOT-' . $immobilisation->code . '-' . $amortissement->annee,
            'description_operation' => 'Dotation aux amortissements - ' . $immobilisation->libelle,
            'plan_comptable_id' => $immobilisation->compte_dotation_id,
            'plan_tiers_id' => 0, // Pas de tiers
            'plan_analytique' => 0,
            'debit' => $amortissement->dotation_annuelle,
            'credit' => 0,
            'user_id' => auth()->id() ?? 1,
            'statut' => 'approved',
        ]);

        // Contrepartie : Amortissement cumulé
        EcritureComptable::create([
            'company_id' => $immobilisation->company_id,
            'exercices_comptables_id' => $exercice->id,
            'code_journal_id' => $this->getJournalOperationsDiverses($immobilisation->company_id),
            'date' => $dateEcriture,
            'n_saisie' => $nSaisie,
            'reference_piece' => 'DOT-' . $immobilisation->code . '-' . $amortissement->annee,
            'description_operation' => 'Dotation aux amortissements - ' . $immobilisation->libelle,
            'plan_comptable_id' => $immobilisation->compte_amortissement_id,
            'plan_tiers_id' => 0,
            'plan_analytique' => 0,
            'debit' => 0,
            'credit' => $amortissement->dotation_annuelle,
            'user_id' => auth()->id() ?? 1,
            'statut' => 'approved',
        ]);

        // Marquer l'amortissement comme comptabilisé
        $amortissement->marquerCommeComptabilise($ecriture->id);

        return $ecriture;
    }

    /**
     * Gérer la cession d'une immobilisation
     */
    public function cederImmobilisation(Immobilisation $immobilisation, array $data)
    {
        DB::beginTransaction();
        try {
            // Mettre à jour l'immobilisation
            $immobilisation->update([
                'statut' => 'cede',
                'date_cession' => $data['date_cession'],
                'montant_cession' => $data['montant_cession'],
                'compte_cession_id' => $data['compte_cession_id'],
                'motif_cession' => $data['motif_cession'] ?? null,
            ]);

            // Générer les écritures de cession
            $this->genererEcrituresCession($immobilisation, $data);

            DB::commit();
            return $immobilisation;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur cession immobilisation: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Générer les écritures de cession
     */
    private function genererEcrituresCession(Immobilisation $immobilisation, array $data)
    {
        $vnc = $immobilisation->getValeurNetteComptable();
        $prixCession = $data['montant_cession'];
        $dateCession = Carbon::parse($data['date_cession']);
        $exercice = $this->getExerciceParAnnee($immobilisation->company_id, $dateCession->year);
        
        if (!$exercice) {
            throw new \Exception("Aucun exercice ouvert trouvé pour l'année de cession (" . $dateCession->year . ").");
        }

        $journalId = $this->getJournalOperationsDiverses($immobilisation->company_id);
        $reference = 'CES-' . $immobilisation->code;
        $nSaisie = $this->getProchainNumeroSaisie();

        // 1. Constatation du prix de cession (775)
        // Débit : Compte de tiers ou Trésorerie (fourni dans $data['compte_cession_id'])
        // Crédit : Produits des cessions (775)
        $compte775 = \App\Models\PlanComptable::where('company_id', $immobilisation->company_id)
            ->where('numero_de_compte', 'like', '775%')
            ->first();

        if ($compte775 && $prixCession > 0) {
            EcritureComptable::create([
                'company_id' => $immobilisation->company_id,
                'exercices_comptables_id' => $exercice,
                'code_journal_id' => $journalId,
                'date' => $dateCession,
                'n_saisie' => $nSaisie,
                'reference_piece' => $reference,
                'description_operation' => 'Cession immobilisation ' . $immobilisation->libelle,
                'plan_comptable_id' => $data['compte_cession_id'], // Compte de règlement fourni
                'plan_tiers_id' => 0,
                'plan_analytique' => 0,
                'debit' => $prixCession,
                'credit' => 0,
                'user_id' => auth()->id() ?? 1,
                'statut' => 'approved'
            ]);

            EcritureComptable::create([
                'company_id' => $immobilisation->company_id,
                'exercices_comptables_id' => $exercice,
                'code_journal_id' => $journalId,
                'date' => $dateCession,
                'n_saisie' => $nSaisie,
                'reference_piece' => $reference,
                'description_operation' => 'Produit cession ' . $immobilisation->libelle,
                'plan_comptable_id' => $compte775->id,
                'plan_tiers_id' => 0,
                'plan_analytique' => 0,
                'debit' => 0,
                'credit' => $prixCession,
                'user_id' => auth()->id() ?? 1,
                'statut' => 'approved'
            ]);
        }

        // 2. Sortie de l'immobilisation du bilan
        // Débit : Cumul des amortissements (28x)
        // Débit : VNC des immo cédées (81x)
        // Crédit : Compte d'immobilisation (2x)
        $amortissementsCumules = $immobilisation->valeur_acquisition - $vnc;
        $compte81 = \App\Models\PlanComptable::where('company_id', $immobilisation->company_id)
            ->where('numero_de_compte', 'like', '81%')
            ->first();

        if (!$compte81) {
            // Créer le compte 81 s'il n'existe pas (Standard SYSCOHADA)
            $compte81 = \App\Models\PlanComptable::create([
                'company_id' => $immobilisation->company_id,
                'numero_de_compte' => '811',
                'intitule' => 'VNC des immobilisations corporelles cédées',
                'description' => 'Généré automatiquement lors d\'une cession',
                'active' => true
            ]);
        }

        // Crédit du compte d'immobilisation pour sa valeur d'origine
        EcritureComptable::create([
            'company_id' => $immobilisation->company_id,
            'exercices_comptables_id' => $exercice,
            'code_journal_id' => $journalId,
            'date' => $dateCession,
            'n_saisie' => $nSaisie,
            'reference_piece' => $reference,
            'description_operation' => 'Sortie actif ' . $immobilisation->libelle,
            'plan_comptable_id' => $immobilisation->compte_immobilisation_id,
            'plan_tiers_id' => 0,
            'plan_analytique' => 0,
            'debit' => 0,
            'credit' => $immobilisation->valeur_acquisition,
            'user_id' => auth()->id() ?? 1,
            'statut' => 'approved'
        ]);

        // Débit des amortissements cumulés
        if ($amortissementsCumules > 0) {
            EcritureComptable::create([
                'company_id' => $immobilisation->company_id,
                'exercices_comptables_id' => $exercice,
                'code_journal_id' => $journalId,
                'date' => $dateCession,
                'n_saisie' => $nSaisie,
                'reference_piece' => $reference,
                'description_operation' => 'Reprise amortissements ' . $immobilisation->libelle,
                'plan_comptable_id' => $immobilisation->compte_amortissement_id,
                'plan_tiers_id' => 0,
                'plan_analytique' => 0,
                'debit' => $amortissementsCumules,
                'credit' => 0,
                'user_id' => auth()->id() ?? 1,
                'statut' => 'approved'
            ]);
        }

        // Débit de la VNC (Compte 81)
        if ($vnc > 0) {
            EcritureComptable::create([
                'company_id' => $immobilisation->company_id,
                'exercices_comptables_id' => $exercice,
                'code_journal_id' => $journalId,
                'date' => $dateCession,
                'n_saisie' => $nSaisie,
                'reference_piece' => $reference,
                'description_operation' => 'VNC immo cédée ' . $immobilisation->libelle,
                'plan_comptable_id' => $compte81->id,
                'plan_tiers_id' => 0,
                'plan_analytique' => 0,
                'debit' => $vnc,
                'credit' => 0,
                'user_id' => auth()->id() ?? 1,
                'statut' => 'approved'
            ]);
        }

        return true;
    }

    // Méthodes utilitaires

    private function genererCodeUnique($companyId, $categorie)
    {
        $prefix = strtoupper(substr($categorie, 0, 3));
        $dernier = Immobilisation::where('company_id', $companyId)
            ->where('code', 'like', $prefix . '%')
            ->orderBy('code', 'desc')
            ->first();

        if ($dernier) {
            $numero = intval(substr($dernier->code, 3)) + 1;
        } else {
            $numero = 1;
        }

        return $prefix . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }

    private function calculerTauxAmortissement($duree, $methode)
    {
        if ($methode === 'lineaire') {
            return round(100 / $duree, 2);
        }

        $coefficient = $this->getCoefficientDegressif($duree);
        return round((100 / $duree) * $coefficient, 2);
    }

    private function getCoefficientDegressif($duree)
    {
        if ($duree <= 4) return 1.5;
        if ($duree <= 6) return 2.0;
        return 2.5;
    }

    private function getExerciceParAnnee($companyId, $annee)
    {
        $exercice = ExerciceComptable::where('company_id', $companyId)
            ->whereYear('date_debut', $annee)
            ->first();

        return $exercice ? $exercice->id : null;
    }

    private function getJournalOperationsDiverses($companyId)
    {
        // Récupérer le journal des opérations diverses (OD)
        $journal = \App\Models\CodeJournal::where('company_id', $companyId)
            ->where('code_journal', 'OD')
            ->first();

        return $journal ? $journal->id : null;
    }

    private function getProchainNumeroSaisie()
    {
        $lastSaisie = EcritureComptable::max('id');
        return str_pad(($lastSaisie ? $lastSaisie + 1 : 1), 12, '0', STR_PAD_LEFT);
    }
}
