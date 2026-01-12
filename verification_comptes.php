<?php
// Script pour vérifier et corriger les comptes SYSCOHADA
require_once 'bootstrap/app.php';

echo "=== VÉRIFICATION DES COMPTES SYSCOHADA ===\n\n";

// Récupérer tous les plans comptables
$plans = \App\Models\PlanComptable::all(['id', 'numero_de_compte', 'intitule', 'classe']);

echo "📊 COMPTES ACTUELS :\n";
foreach ($plans as $plan) {
    $numero = $plan->numero_de_compte;
    $longueur = strlen($numero);
    $statut = $longueur == 8 ? '✅' : '❌';
    
    echo "   • $numero ({$longueur} chiffres) $statut - {$plan->intitule}\n";
}

echo "\n🔧 COMPTES À CORRIGER :\n";
$comptesIncorrects = $plans->filter(function($plan) {
    return strlen($plan->numero_de_compte) != 8;
});

foreach ($comptesIncorrects as $plan) {
    echo "   • {$plan->numero_de_compte} → DOIT ÊTRE 8 CHIFFRES\n";
}

echo "\n🎯 PLAN COMPTABLE SYSCOHADA CI :\n";
$planSyscohada = [
    // Classe 1 - Comptes de capitaux
    '10110000' => 'Capital',
    '10610000' => 'Réserves',
    '10910000' => 'Actionnaires, capital souscrit - non appelé',
    '12010000' => 'Résultat de l\'exercice',
    '13010000' => 'Résultat en instance d\'affectation',
    '14010000' => 'Produits des cessions d\'immobilisations',
    '16010000' => 'Emprunts et dettes assimilées',
    
    // Classe 2 - Comptes d'immobilisations
    '21010000' => 'Immobilisations incorporelles',
    '23010000' => 'Immobilisations corporelles',
    '24010000' => 'Immobilisations en cours',
    '28010000' => 'Amortissements des immobilisations',
    
    // Classe 3 - Comptes de stocks
    '31010000' => 'Matières premières et fournitures',
    '32010000' => 'Autres approvisionnements',
    '37010000' => 'Stocks de marchandises',
    '39010000' => 'Provisions pour dépréciation des stocks',
    
    // Classe 4 - Comptes de tiers
    '40110000' => 'Fournisseurs',
    '40310000' => 'Fournisseurs - Effets à payer',
    '41110000' => 'Clients',
    '41310000' => 'Clients - Effets à recevoir',
    '42110000' => 'Personnel - Rémunérations dues',
    '43110000' => 'Sécurité sociale',
    '44110000' => 'État - Subventions à recevoir',
    '44510000' => 'État - Taxes sur le chiffre d\'affaires',
    '44521000' => 'État - TVA due intracommunautaire',
    '44551000' => 'État - TVA à décaisser',
    '45110000' => 'Associés - Comptes courants',
    '46210000' => 'Créditeurs divers',
    '47110000' => 'Comptes d\'attente',
    '48110000' => 'Charges à répartir sur plusieurs exercices',
    
    // Classe 5 - Comptes de trésorerie
    '50110000' => 'Valeurs mobilières de placement',
    '52110000' => 'Banques',
    '53110000' => 'Caisse',
    '57110000' => 'Effets à recevoir',
    
    // Classe 6 - Comptes de charges
    '60110000' => 'Achats de matières premières',
    '60310000' => 'Variations des stocks de matières premières',
    '60710000' => 'Achats de marchandises',
    '61310000' => 'Locations',
    '61710000' => 'Charges de personnel',
    '62210000' => 'Rémunérations d\'intermédiaires et honoraires',
    '62410000' => 'Transports',
    '62610000' => 'Frais postaux et de télécommunications',
    '62710000' => 'Services bancaires et assimilés',
    '63510000' => 'Autres impôts, taxes et versements assimilés',
    '64110000' => 'Impôts et taxes',
    '65110000' => 'Redevances pour concessions, brevets, licences',
    '65410000' => 'Primes d\'assurance',
    '66110000' => 'Charges d\'intérêts',
    '67110000' => 'Charges exceptionnelles',
    '68110000' => 'Dotations aux amortissements',
    
    // Classe 7 - Comptes de produits
    '70110000' => 'Ventes de produits finis',
    '70610000' => 'Prestations de services',
    '70710000' => 'Ventes de marchandises',
    '70810000' => 'Produits des activités annexes',
    '71310000' => 'Variations des stocks de produits',
    '72210000' => 'Production immobilisée',
    '73610000' => 'Charges constatées d\'avance',
    '75110000' => 'Produits des participations',
    '75410000' => 'Revenus des valeurs mobilières de placement',
    '76110000' => 'Produits des titres de participation et autres titres immobilisés',
    '77110000' => 'Produits exceptionnels',
    '78110000' => 'Reprises sur amortissements',
    
    // Classe 8 - Comptes spéciaux
    '80110000' => 'Marge brute',
    '81110000' => 'Valeur ajoutée',
    '82110000' => 'Excédent brut d\'exploitation',
    '83110000' => 'Résultat d\'exploitation',
    '84110000' => 'Résultat courant avant impôts',
    '85110000' => 'Résultat net de l\'exercice',
    '89110000' => 'Soldes caractéristiques de gestion'
];

foreach ($planSyscohada as $compte => $libelle) {
    echo "   • $compte : $libelle\n";
}

echo "\n✅ TOTAL : " . count($planSyscohada) . " comptes SYSCOHADA CI\n";
?>
