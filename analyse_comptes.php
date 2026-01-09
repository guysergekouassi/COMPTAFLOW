<?php
// Analyse du plan comptable SYSCOHADA CI à 8 chiffres
echo "=== ANALYSE PLAN COMPTABLE SYSCOHADA CI ===\n\n";

// Données extraites de votre base de données
$comptes_existant = [
    '401000' => 'Fournisseur',
    '411000' => 'Client', 
    '601000' => 'Achats marchandise',
    '60200000' => 'Achats de marchandise'
];

$comptes_tiers = [
    '401001' => 'Fournissseur de Pains',
    '401002' => 'Fournisseur de carburant',
    '401003' => 'Fournisseur de repas',
    '41100004' => 'Client client'
];

echo "📊 COMPTES EXISTANTS :\n";
foreach ($comptes_existant as $numero => $intitule) {
    echo "   • $numero : $intitule\n";
}

echo "\n🏪 COMPTES TIERS :\n";
foreach ($comptes_tiers as $numero => $intitule) {
    echo "   • $numero : $intitule\n";
}

echo "\n🔍 ANALYSE DE LA LOGIQUE :\n";
echo "   • Préfixe 401 : Fournisseurs (401000 + 001, 002, 003...)\n";
echo "   • Préfixe 411 : Clients (411000 + 0004...)\n";
echo "   • Préfixe 601 : Achats marchandises (601000)\n";
echo "   • Format : 8 chiffres maximum\n\n";

// Mapping SYSCOHADA CI vers vos comptes
$mapping_syscohada = [
    // Classe 4 - Comptes de tiers
    '401' => '401000',    // Fournisseurs
    '4011' => '401000',   // Fournisseurs - Fournisseurs d'exploitation
    '411' => '411000',    // Clients
    '4111' => '411000',   // Clients - Clients
    
    // Classe 5 - Comptes de trésorerie
    '521' => '52100000',  // Banques
    '531' => '53100000',  // Chèques postaux
    '571' => '57100000',  // Caisse
    
    // Classe 6 - Comptes de charges
    '601' => '601000',    // Achats marchandises
    '602' => '60200000',  // Achats matières premières
    '603' => '60300000',  // Achats fournitures de bureau
    '604' => '60400000',  // Achats d'études et prestations de services
    '605' => '60500000',  // Achats non stockés de matières et fournitures
    '606' => '60600000',  // Achats non stockés de matières et fournitures
    '607' => '60700000',  // Achats non stockés de matières et fournitures
    '608' => '60800000',  // Achats non stockés de matières et fournitures
    
    // Services extérieurs
    '612' => '61200000',  // Transports
    '613' => '61300000',  // Locations et charges locatives
    '614' => '61400000',  // Entretien et réparations
    '615' => '61500000',  // Primes d'assurances
    '616' => '61600000',  // Rétributions d'intermédiaires et honoraires
    '617' => '61700000',  // Services bancaires et assimilés
    '618' => '61800000',  // Divers services extérieurs
    '619' => '61900000',  // Rabais, remises, ristournes obtenus sur services extérieurs
    
    // Impôts et taxes
    '641' => '64100000',  // Impôts et taxes directs
    '642' => '64200000',  // Impôts et taxes indirects
    '643' => '64300000',  // Impôts et taxes indirects
    '644' => '64400000',  // Impôts et taxes indirects
    '645' => '64500000',  // Impôts et taxes indirects
    '646' => '64600000',  // Impôts et taxes indirects
    '647' => '64700000',  // Impôts et taxes indirects
    
    // Charges de personnel
    '661' => '66100000',  // Rémunérations du personnel
    '662' => '66200000',  // Charges sociales
    '663' => '66300000',  // Charges sociales
    '664' => '66400000',  // Charges sociales
    '665' => '66500000',  // Charges sociales
    '666' => '66600000',  // Charges sociales
    '667' => '66700000',  // Charges sociales
    '668' => '66800000',  // Autres charges sociales
    
    // Charges financières
    '671' => '67100000',  // Intérêts des emprunts et dettes
    '672' => '67200000',  // Intérêts des emprunts et dettes
    '673' => '67300000',  // Intérêts des emprunts et dettes
    '674' => '67400000',  // Intérêts des emprunts et dettes
    '675' => '67500000',  // Escomptes accordés
    '676' => '67600000',  // Pertes de change
    '677' => '67700000',  // Pertes sur créances liées à des participations
    '678' => '67800000',  // Charges exceptionnelles
    
    // Classe 7 - Comptes de produits
    '701' => '70100000',  // Ventes de marchandises
    '702' => '70200000',  // Ventes de produits finis
    '703' => '70300000',  // Ventes de produits intermédiaires
    '704' => '70400000',  // Ventes de produits résiduels
    '705' => '70500000',  // Travaux facturés
    '706' => '70600000',  // Services facturés
    '707' => '70700000',  // Ventes de produits accessoires
    '708' => '70800000',  // Produits des activités annexes
    
    // Subventions
    '711' => '71100000',  // Subventions d'exploitation
    '712' => '71200000',  // Subventions d'équilibre
    '713' => '71300000',  // Subventions d'investissement
    
    // Produits financiers
    '771' => '77100000',  // Intérêts de prêts
    '772' => '77200000',  // Revenus des titres
    '773' => '77300000',  // Revenus des titres
    '774' => '77400000',  // Revenus des titres
    '775' => '77500000',  // Escomptes obtenus
    '776' => '77600000',  // Gains de change
    '777' => '77700000',  // Produits exceptionnels
    
    // Classe 8 - Comptes de résultats
    '801' => '80100000',  // Marge brute sur marchandises
    '803' => '80300000',  // Marge brute sur matières
    '811' => '81100000',  // Valeur ajoutée
    '812' => '81200000',  // Valeur ajoutée
    '813' => '81300000',  // Valeur ajoutée
    '814' => '81400000',  // Valeur ajoutée
    '815' => '81500000',  // Valeur ajoutée
    '816' => '81600000',  // Valeur ajoutée
    '817' => '81700000',  // Valeur ajoutée
    '818' => '81800000',  // Valeur ajoutée
    
    // Impôts sur le résultat
    '891' => '89100000',  // Impôt sur le résultat
    '892' => '89200000',  // Impôt sur le résultat
    '893' => '89300000',  // Impôt sur le résultat
    '894' => '89400000',  // Impôt sur le résultat
    '895' => '89500000',  // Impôt sur le résultat
    '896' => '89600000',  // Impôt sur le résultat
    '897' => '89700000',  // Impôt sur le résultat
    '898' => '89800000',  // Impôt sur le résultat
    
    // TVA - Comptes spéciaux
    '445' => '44500000',  // TVA
    '4452' => '44520000', // TVA déductible
    '4455' => '44550000', // TVA collectée
    '4456' => '44560000', // TVA due
    '4457' => '44570000', // TVA à décaisser
];

echo "🎯 MAPPING SYSCOHADA CI → VOS COMPTES (8 chiffres) :\n";
foreach ($mapping_syscohada as $prefixe => $compte) {
    echo "   • $prefixe → $compte\n";
}

echo "\n✅ RÈGLES DE CONVERSION :\n";
echo "   1. Préfixe SYSCOHADA (4 chiffres) → Compte 8 chiffres\n";
echo "   2. Ajout de zéros pour atteindre 8 chiffres\n";
echo "   3. Format : PPPP0000 (PPPP = préfixe)\n";
echo "   4. Comptes tiers : PPPP00NN (NN = numéro séquentiel)\n\n";

echo "🚀 EXEMPLES DE CONVERSION :\n";
echo "   • 401 (Fournisseurs) → 40100000\n";
echo "   • 613 (Locations) → 61300000\n";
echo "   • 4452 (TVA déductible) → 44520000\n";
echo "   • 571 (Caisse) → 57100000\n\n";

echo "💡 LOGIQUE TVA :\n";
echo "   • Si TVA sur facture → Compte 44520000 automatique\n";
echo "   • Si pas de TVA → Bouton 'Appliquer TVA 18%' visible\n";
echo "   • Bouton caché si TVA déjà présente\n";
?>
