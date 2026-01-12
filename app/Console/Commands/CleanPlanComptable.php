<?php

namespace App\Console\Commands;

use App\Models\PlanComptable;
use Illuminate\Console\Command;

class CleanPlanComptable extends Command
{
    protected $signature = 'plan:clean';
    protected $description = 'Nettoyer les comptes pour respecter le format 8 chiffres SYSCOHADA';

    public function handle()
    {
        $this->info('=== NETTOYAGE DES COMPTES SYSCOHADA ===');
        
        // Plan comptable SYSCOHADA CI officiel
        $planSyscohada = [
            // Classe 1 - Comptes de capitaux
            '10110000' => ['Capital', 1],
            '10610000' => ['Réserves', 1],
            '10910000' => ['Actionnaires, capital souscrit - non appelé', 1],
            '12010000' => ['Résultat de l\'exercice', 1],
            '13010000' => ['Résultat en instance d\'affectation', 1],
            '14010000' => ['Produits des cessions d\'immobilisations', 1],
            '16010000' => ['Emprunts et dettes assimilées', 1],
            
            // Classe 2 - Comptes d'immobilisations
            '21010000' => ['Immobilisations incorporelles', 2],
            '23010000' => ['Immobilisations corporelles', 2],
            '24010000' => ['Immobilisations en cours', 2],
            '28010000' => ['Amortissements des immobilisations', 2],
            
            // Classe 3 - Comptes de stocks
            '31010000' => ['Matières premières et fournitures', 3],
            '32010000' => ['Autres approvisionnements', 3],
            '37010000' => ['Stocks de marchandises', 3],
            '39010000' => ['Provisions pour dépréciation des stocks', 3],
            
            // Classe 4 - Comptes de tiers
            '40110000' => ['Fournisseurs', 4],
            '40310000' => ['Fournisseurs - Effets à payer', 4],
            '41110000' => ['Clients', 4],
            '41310000' => ['Clients - Effets à recevoir', 4],
            '42110000' => ['Personnel - Rémunérations dues', 4],
            '43110000' => ['Sécurité sociale', 4],
            '44110000' => ['État - Subventions à recevoir', 4],
            '44510000' => ['État - Taxes sur le chiffre d\'affaires', 4],
            '44521000' => ['État - TVA due intracommunautaire', 4],
            '44551000' => ['État - TVA à décaisser', 4],
            '45110000' => ['Associés - Comptes courants', 4],
            '46210000' => ['Créditeurs divers', 4],
            '47110000' => ['Comptes d\'attente', 4],
            '48110000' => ['Charges à répartir sur plusieurs exercices', 4],
            
            // Classe 5 - Comptes de trésorerie
            '50110000' => ['Valeurs mobilières de placement', 5],
            '52110000' => ['Banques', 5],
            '53110000' => ['Caisse', 5],
            '57110000' => ['Effets à recevoir', 5],
            
            // Classe 6 - Comptes de charges
            '60110000' => ['Achats de matières premières', 6],
            '60310000' => ['Variations des stocks de matières premières', 6],
            '60710000' => ['Achats de marchandises', 6],
            '61310000' => ['Locations', 6],
            '61710000' => ['Charges de personnel', 6],
            '62210000' => ['Rémunérations d\'intermédiaires et honoraires', 6],
            '62410000' => ['Transports', 6],
            '62610000' => ['Frais postaux et de télécommunications', 6],
            '62710000' => ['Services bancaires et assimilés', 6],
            '63510000' => ['Autres impôts, taxes et versements assimilés', 6],
            '64110000' => ['Impôts et taxes', 6],
            '65110000' => ['Redevances pour concessions, brevets, licences', 6],
            '65410000' => ['Primes d\'assurance', 6],
            '66110000' => ['Charges d\'intérêts', 6],
            '67110000' => ['Charges exceptionnelles', 6],
            '68110000' => ['Dotations aux amortissements', 6],
            
            // Classe 7 - Comptes de produits
            '70110000' => ['Ventes de produits finis', 7],
            '70610000' => ['Prestations de services', 7],
            '70710000' => ['Ventes de marchandises', 7],
            '70810000' => ['Produits des activités annexes', 7],
            '71310000' => ['Variations des stocks de produits', 7],
            '72210000' => ['Production immobilisée', 7],
            '73610000' => ['Charges constatées d\'avance', 7],
            '75110000' => ['Produits des participations', 7],
            '75410000' => ['Revenus des valeurs mobilières de placement', 7],
            '76110000' => ['Produits des titres de participation et autres titres immobilisés', 7],
            '77110000' => ['Produits exceptionnels', 7],
            '78110000' => ['Reprises sur amortissements', 7],
            
            // Classe 8 - Comptes spéciaux
            '80110000' => ['Marge brute', 8],
            '81110000' => ['Valeur ajoutée', 8],
            '82110000' => ['Excédent brut d\'exploitation', 8],
            '83110000' => ['Résultat d\'exploitation', 8],
            '84110000' => ['Résultat courant avant impôts', 8],
            '85110000' => ['Résultat net de l\'exercice', 8],
            '89110000' => ['Soldes caractéristiques de gestion', 8]
        ];

        // 1. Supprimer tous les comptes existants
        $this->info('🗑️  Suppression des comptes existants...');
        $deletedCount = PlanComptable::count();
        PlanComptable::truncate();
        $this->info("   ✅ {$deletedCount} comptes supprimés");

        // 2. Insérer les comptes SYSCOHADA officiels
        $this->info('📝 Insertion des comptes SYSCOHADA CI...');
        $insertedCount = 0;
        
        foreach ($planSyscohada as $compte => $data) {
            PlanComptable::create([
                'numero_de_compte' => $compte,
                'intitule' => $data[0],
                'classe' => $data[1],
                'adding_strategy' => 'auto',
                'user_id' => 1, // ID utilisateur par défaut
                'company_id' => 1, // ID entreprise par défaut
            ]);
            $insertedCount++;
        }
        
        $this->info("   ✅ {$insertedCount} comptes SYSCOHADA insérés");

        // 3. Vérification finale
        $this->info('🔍 Vérification finale...');
        $totalComptes = PlanComptable::count();
        $comptes8Chiffres = PlanComptable::whereRaw('LENGTH(numero_de_compte) = 8')->count();
        
        $this->info("   📊 Total comptes : {$totalComptes}");
        $this->info("   ✅ Comptes 8 chiffres : {$comptes8Chiffres}");
        
        if ($totalComptes === $comptes8Chiffres) {
            $this->info('🎉 TOUS LES COMPTES SONT AU FORMAT 8 CHIFFRES SYSCOHADA !');
        } else {
            $this->error('❌ ERREUR : Certains comptes ne sont pas au bon format');
        }

        return 0;
    }
}
