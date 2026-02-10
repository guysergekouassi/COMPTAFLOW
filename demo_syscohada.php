<?php

// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Company;
use App\Models\CompteTresorerie;
use App\Models\EcritureComptable;
use App\Models\PlanComptable;
use App\Models\ExerciceComptable;
use App\Services\AccountingReportingService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

echo "\n--- DÉMARRAGE DE LA DÉMONSTRATION TFT SYSCOHADA ---\n";

DB::beginTransaction();

try {
    // 1. Setup : Création Environnement
    // On utilise une compagnie temporaire
    $company = Company::create([
        'name' => 'Demo SYSCOHADA SAS', 
        'company_name' => 'Demo SYSCOHADA SAS', // <--- Champ requis ajouté
        'email' => 'demo_syscohada_' . time() . '@sas.com'
    ]);
    echo "[OK] Compagnie créée : " . $company->name . "\n";
    
    $exercice = ExerciceComptable::create([
        'company_id' => $company->id,
        'date_debut' => Carbon::now()->startOfYear()->toDateString(),
        'date_fin' => Carbon::now()->endOfYear()->toDateString(),
        'is_active' => true,
        'libelle' => 'Exercice ' . Carbon::now()->year
    ]);
    echo "[OK] Exercice créé.\n";

    // 2. Création des Postes de Trésorerie avec Mapping SYSCOHADA
    
    // Cas A : Emprunt (Financement - Entrée d'argent)
    $posteEmprunt = CompteTresorerie::create([
        'company_id' => $company->id,
        'name' => 'Ligne de Crédit BOA',
        'type' => 'Banque', 
        'category_id' => 1, // Dummy ID, on s'en fiche grâce au mapping
        'syscohada_line_id' => 'FIN_EMP', // <--- LE POINT CLÉ
        'solde_initial' => 0,
        'solde_actuel' => 0
    ]);
    echo "[OK] Poste 'Ligne de Crédit BOA' créé avec mapping 'FIN_EMP'.\n";

    // Cas B : Achat Matériel (Investissement - Sortie d'argent)
    $posteInvest = CompteTresorerie::create([
        'company_id' => $company->id,
        'name' => 'Investissement Matériel',
        'type' => 'Banque',
        'category_id' => 1,
        'syscohada_line_id' => 'INV_ACQ', // <--- LE POINT CLÉ
        'solde_initial' => 0,
        'solde_actuel' => 0
    ]);
    echo "[OK] Poste 'Investissement Matériel' créé avec mapping 'INV_ACQ'.\n";

    // Compte Banque 521
    $compteBanque = PlanComptable::create([
        'company_id' => $company->id,
        'numero_de_compte' => '52110000',
        'intitule' => 'Banque BOA',
    ]);

    // 3. Simulation des Écritures Comptables
    
    // Écriture 1 : Encaissement de l'emprunt (10 000 000)
    EcritureComptable::create([
        'company_id' => $company->id,
        'journal_id' => 1, 
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
    echo "[OK] Écriture 1 créée : Déblocage Prêt (+10 000 000) sur le poste Emprunt.\n";

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
    echo "[OK] Écriture 2 créée : Achat Matériel (-2 000 000) sur le poste Investissement.\n";

    // 4. Génération du Rapport TFT
    $service = new AccountingReportingService();
    $result = $service->getTFTMatrixData($company->id, $exercice->id);
    
    // 5. Vérification des Résultats
    $totalFinancement = array_sum($result['flux']['financement']['net'] ?? []);
    // Note: 'acquisitions' contient probablement des valeurs positives représentant les sorties
    $totalInvestAcq = array_sum($result['flux']['investissement']['acquisitions'] ?? []);
    
    echo "\n-------------------------------------------------------\n";
    echo " RÉSULTATS DU CALCUL TFT\n";
    echo "-------------------------------------------------------\n";
    echo "Flux Financement (Attendu: +10 000 000)        : " . number_format($totalFinancement, 0, ',', ' ') . "\n";
    echo "Flux Investissement Acq (Attendu: 2 000 000)   : " . number_format($totalInvestAcq, 0, ',', ' ') . "\n";
    
    if ($totalFinancement == 10000000 && $totalInvestAcq == 2000000) {
        echo "\n[SUCCÈS] Le mapping fonctionne parfaitement !\n";
    } else {
        echo "\n[ÉCHEC] Les montants ne sont pas ceux attendus.\n";
    }

} catch (\Exception $e) {
    echo "\n[ERREUR] Une exception est survenue : " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
} finally {
    // Nettoyage quoiqu'il arrive
    DB::rollBack();
    echo "\n[INFO] Rollback effectué, base de données nettoyée.\n";
}
