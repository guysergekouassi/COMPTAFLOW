<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\CompteTresorerie;
use App\Models\EcritureComptable;
use App\Models\PlanComptable;
use App\Models\ExerciceComptable;
use App\Services\AccountingReportingService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Carbon\Carbon;

class SyscohadaTftDemoTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');
    }

    public function test_tft_respects_syscohada_explicit_mapping()
    {
        // 1. Setup : Création Environnement Manuelle (Sans Factory)
        $company = Company::create(['name' => 'Demo SAS', 'email' => 'demo@sas.com']);
        $user = User::factory()->create(['company_id' => $company->id]);
        
        $exercice = ExerciceComptable::create([
            'company_id' => $company->id,
            'date_debut' => Carbon::now()->startOfYear()->toDateString(),
            'date_fin' => Carbon::now()->endOfYear()->toDateString(),
            'is_active' => true,
            'libelle' => 'Exercice ' . Carbon::now()->year
        ]);
        
        $this->actingAs($user);

        // 2. Création des Postes de Trésorerie avec Mapping SYSCOHADA
        
        // Cas A : Emprunt (Financement - Entrée d'argent)
        $posteEmprunt = CompteTresorerie::create([
            'company_id' => $company->id,
            'name' => 'Ligne de Crédit BOA',
            'type' => 'Banque', // Peu importe
            'category_id' => 1, // Dummy
            'syscohada_line_id' => 'FIN_EMP', // <--- LE POINT CLÉ
            'solde_initial' => 0,
            'solde_actuel' => 0
        ]);

        // Cas B : Achat Matériel (Investissement - Sortie d'argent)
        $posteInvest = CompteTresorerie::create([
            'company_id' => $company->id,
            'name' => 'Investissement Matériel',
            'type' => 'Banque',
            'category_id' => 1, // Dummy
            'syscohada_line_id' => 'INV_ACQ', // <--- LE POINT CLÉ
            'solde_initial' => 0,
            'solde_actuel' => 0
        ]);

        // Correction : Il faut que create EcritureComptable pointe vers un vrai PlanComptable avec un numéro commencant par 5
        $compteBanque = PlanComptable::create([
            'company_id' => $company->id,
            'numero_de_compte' => '52110000',
            'intitule' => 'Banque BOA',
        ]);

        // 3. Simulation des Écritures Comptables
        
        // Écriture 1 : Encaissement de l'emprunt (10 000 000)
        EcritureComptable::create([
            'company_id' => $company->id,
            'journal_id' => 1, // Dummy
            'exercices_comptables_id' => $exercice->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'n_saisie' => 'ECR-DEMO-001',
            'description_operation' => 'Déblocage Prêt BOA',
            'plan_comptable_id' => $compteBanque->id,
            'debit' => 10000000,
            'credit' => 0,
            'poste_tresorerie_id' => $posteEmprunt->id, // <--- LIEN
            'statut' => 'approved' 
        ]);

        // Écriture 2 : Achat Ordinateurs (2 000 000)
        EcritureComptable::create([
            'company_id' => $company->id,
            'journal_id' => 1,
            'exercices_comptables_id' => $exercice->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'n_saisie' => 'ECR-DEMO-002',
            'description_operation' => 'Achat Dell XPS',
            'plan_comptable_id' => $compteBanque->id,
            'debit' => 0,
            'credit' => 2000000,
            'poste_tresorerie_id' => $posteInvest->id, // <--- LIEN
            'statut' => 'approved'
        ]);

        // 4. Génération du Rapport TFT
        $service = new AccountingReportingService();
        $result = $service->getTFTMatrixData($company->id, $exercice->id);
        
        // 5. Vérification des Résultats (Démonstration)
        
        // Vérif Financement (Net)
        // On s'attend à +10 000 000 dans la section Financement
        $totalFinancement = array_sum($result['flux']['financement']['net']);
        
        // Vérif Investissement (Acquisitions)
        // On s'attend à 2 000 000 dans la ligne "Acquisitions"
        $totalInvestAcq = array_sum($result['flux']['investissement']['acquisitions']);

        echo "\n--- RÉSULTATS DE LA DÉMONSTRATION TFT SYSCOHADA ---\n";
        echo "1. Flux Financement (Attendu: +10 000 000) -> Résultat : " . number_format($totalFinancement, 0, ',', ' ') . "\n";
        echo "2. Flux Investissement Acquisition (Attendu: 2 000 000) -> Résultat : " . number_format($totalInvestAcq, 0, ',', ' ') . "\n";
        
        $this->assertEquals(10000000, $totalFinancement, "Le flux de financement n'est pas correct.");
        $this->assertEquals(2000000, $totalInvestAcq, "Le flux d'acquisition n'est pas correct.");
    }
}
