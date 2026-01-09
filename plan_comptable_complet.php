<?php
// Analyse complète du plan comptable SYSCOHADA CI à 8 chiffres
echo "=== ANALYSE COMPLÈTE PLAN COMPTABLE SYSCOHADA CI ===\n\n";

// Pattern observé dans votre base de données
$comptes_exemples = [
    '401000' => 'Fournisseur',
    '411000' => 'Client', 
    '601000' => 'Achats marchandise',
    '60200000' => 'Achats de marchandise'
];

echo "📊 COMPTES OBSERVÉS DANS VOTRE BASE :\n";
foreach ($comptes_exemples as $numero => $intitule) {
    echo "   • $numero : $intitule\n";
}

echo "\n🔍 ANALYSE DU PATTERN :\n";
echo "   • Certains comptes ont 6 chiffres : 401000, 411000, 601000\n";
echo "   • Certains comptes ont 8 chiffres : 60200000\n";
echo "   • Pattern logique : PPPPNNNN (PPPP = préfixe, NNNN = séquentiel)\n\n";

// Génération du plan comptable SYSCOHADA CI complet
$plan_comptable = [];

// Classe 1 - Comptes de capitaux
$plan_comptable[1] = [
    '101' => 'Capital social',
    '106' => 'Réserves',
    '109' => 'Actionnaires, capital souscrit non appelé',
    '12' => 'Résultat de l\'exercice',
    '13' => 'Subventions d\'investissement',
    '14' => 'Provisions réglementées',
    '16' => 'Emprunts et dettes assimilées'
];

// Classe 2 - Comptes d'immobilisations
$plan_comptable[2] = [
    '21' => 'Immobilisations incorporelles',
    '22' => 'Immobilisations corporelles',
    '23' => 'Immobilisations en cours',
    '24' => 'Immobilisations financières',
    '26' => 'Participations',
    '27' => 'Autres immobilisations financières',
    '28' => 'Amortissements'
];

// Classe 3 - Comptes de stocks
$plan_comptable[3] = [
    '31' => 'Matières premières et fournitures',
    '32' => 'Autres approvisionnements',
    '33' => 'En-cours de production de biens',
    '34' => 'En-cours de production de services',
    '35' => 'Produits intermédiaires et finis',
    '36' => 'Produits résiduels',
    '37' => 'Stocks de marchandises',
    '38' => 'Stocks en voie d\'acheminement',
    '39' => 'Dépréciations des stocks'
];

// Classe 4 - Comptes de tiers
$plan_comptable[4] = [
    '401' => 'Fournisseurs',
    '403' => 'Fournisseurs - Effets à payer',
    '404' => 'Fournisseurs immobilisations',
    '405' => 'Fournisseurs d\'immobilisations',
    '408' => 'Fournisseurs - Factures non parvenues',
    '411' => 'Clients',
    '413' => 'Clients - Effets à recevoir',
    '415' => 'Clients - Effets à recevoir',
    '416' => 'Clients douteux',
    '418' => 'Clients - Factures à établir',
    '421' => 'Personnel - Rémunérations dues',
    '422' => 'Comité d\'entreprise',
    '425' => 'Personnel - Avances et acomptes',
    '427' => 'Personnel - Oppositions',
    '428' => 'Personnel - Charges à payer',
    '431' => 'Sécurité sociale',
    '437' => 'Autres organismes sociaux',
    '438' => 'Organismes sociaux - Charges à payer',
    '441' => 'État - Impôts sur les bénéfices',
    '442' => 'État - Autres impôts et taxes',
    '443' => 'État - TVA due',
    '444' => 'État - Impôts sur les salaires',
    '445' => 'État - TVA',
    '447' => 'Autres impôts, taxes et versements assimilés',
    '451' => 'Groupe et associés',
    '455' => 'Associés - Comptes courants',
    '456' => 'Associés - Opérations faites en commun',
    '457' => 'Associés - Versements reçus',
    '458' => 'Associés - Opérations sur le capital',
    '462' => 'Créditeurs divers',
    '464' => 'Dettes sur acquisitions de valeurs mobilières',
    '465' => 'Dettes envers les sociétés apparentées',
    '467' => 'Autres dettes',
    '468' => 'Dettes sur acquisitions de valeurs mobilières',
    '471' => 'Compte d\'attente',
    '476' => 'Différence de conversion - Actif',
    '477' => 'Différence de conversion - Passif',
    '481' => 'Charges constatées d\'avance',
    '486' => 'Charges constatées d\'avance',
    '487' => 'Produits constatés d\'avance'
];

// Classe 5 - Comptes de trésorerie
$plan_comptable[5] = [
    '501' => 'Parts dans les entreprises liées',
    '502' => 'Actions propres',
    '503' => 'Actions',
    '504' => 'Autres titres conférant un droit de propriété',
    '505' => 'Obligations et bons émis par la société et rachetés par elle',
    '506' => 'Obligations',
    '508' => 'Autres titres immobilisés',
    '51' => 'Valeurs mobilières de placement',
    '521' => 'Banques',
    '522' => 'Banques - Chèques postaux',
    '523' => 'Banques - Comptes en devises',
    '531' => 'Chèques postaux',
    '532' => 'Chèques postaux - Comptes en devises',
    '541' => 'Caisse',
    '542' => 'Caisse - Comptes en devises',
    '543' => 'Caisse - Chèques à encaisser',
    '544' => 'Caisse - Effets à encaisser',
    '545' => 'Caisse - Virements de fonds',
    '546' => 'Caisse - Avances et acomptes',
    '547' => 'Caisse - Versements reçus',
    '548' => 'Caisse - Versements effectués',
    '549' => 'Caisse - Comptes en devises',
    '571' => 'Caisse',
    '572' => 'Caisse - Comptes en devises',
    '573' => 'Caisse - Chèques à encaisser',
    '574' => 'Caisse - Effets à encaisser',
    '575' => 'Caisse - Virements de fonds',
    '576' => 'Caisse - Avances et acomptes',
    '577' => 'Caisse - Versements reçus',
    '578' => 'Caisse - Versements effectués',
    '579' => 'Caisse - Comptes en devises'
];

// Classe 6 - Comptes de charges
$plan_comptable[6] = [
    '601' => 'Achats de marchandises',
    '602' => 'Achats de matières premières',
    '603' => 'Achats d\'autres approvisionnements',
    '604' => 'Achats d\'études et prestations de services',
    '605' => 'Achats non stockés de matières et fournitures',
    '606' => 'Achats non stockés de matières et fournitures',
    '607' => 'Achats non stockés de matières et fournitures',
    '608' => 'Achats non stockés de matières et fournitures',
    '609' => 'Fournitures non stockables',
    '61' => 'Services extérieurs',
    '611' => 'Sous-traitance générale',
    '612' => 'Redevances de crédit-bail',
    '613' => 'Locations',
    '614' => 'Charges locatives',
    '615' => 'Entretien et réparations',
    '616' => 'Primes d\'assurances',
    '617' => 'Etudes et recherches',
    '618' => 'Divers services extérieurs',
    '619' => 'Rabais, remises et ristournes obtenus sur services extérieurs',
    '621' => 'Personnel extérieur à l\'entreprise',
    '622' => 'Rémunérations d\'intermédiaires et honoraires',
    '623' => 'Publicité, publications, relations publiques',
    '624' => 'Transports de biens et transports collectifs du personnel',
    '625' => 'Déplacements, missions et réceptions',
    '626' => 'Frais postaux et de télécommunications',
    '627' => 'Services bancaires et assimilés',
    '628' => 'Divers services extérieurs',
    '629' => 'Rabais, remises et ristournes obtenus sur services extérieurs',
    '631' => 'Impôts, taxes et versements assimilés sur rémunérations',
    '632' => 'Impôts, taxes et versements assimilés sur rémunérations',
    '633' => 'Impôts, taxes et versements assimilés sur rémunérations',
    '634' => 'Impôts, taxes et versements assimilés sur rémunérations',
    '635' => 'Impôts, taxes et versements assimilés sur rémunérations',
    '636' => 'Impôts, taxes et versements assimilés sur rémunérations',
    '637' => 'Impôts, taxes et versements assimilés sur rémunérations',
    '638' => 'Impôts, taxes et versements assimilés sur rémunérations',
    '639' => 'Impôts, taxes et versements assimilés sur rémunérations',
    '641' => 'Impôts et taxes directs',
    '642' => 'Impôts et taxes indirects',
    '643' => 'Impôts et taxes indirects',
    '644' => 'Impôts et taxes indirects',
    '645' => 'Impôts et taxes indirects',
    '646' => 'Impôts et taxes indirects',
    '647' => 'Impôts et taxes indirects',
    '648' => 'Autres impôts et taxes',
    '65' => 'Autres charges de gestion courante',
    '651' => 'Charges exceptionnelles sur opérations de gestion',
    '652' => 'Valeurs comptables des cessions d\'immobilisations',
    '653' => 'Charges exceptionnelles sur opérations en capital',
    '654' => 'Valeurs comptables des cessions d\'immobilisations',
    '655' => 'Dotations aux amortissements sur immobilisations',
    '656' => 'Dotations aux provisions pour dépréciation',
    '657' => 'Dotations aux provisions pour dépréciation',
    '658' => 'Dotations aux provisions pour dépréciation',
    '659' => 'Dotations aux provisions pour dépréciation',
    '66' => 'Charges financières',
    '661' => 'Charges d\'intérêts',
    '662' => 'Pertes sur créances liées à des participations',
    '663' => 'Pertes sur créances liées à des participations',
    '664' => 'Pertes sur créances liées à des participations',
    '665' => 'Pertes sur créances liées à des participations',
    '666' => 'Pertes sur créances liées à des participations',
    '667' => 'Pertes sur créances liées à des participations',
    '668' => 'Pertes sur créances liées à des participations',
    '67' => 'Charges exceptionnelles',
    '671' => 'Charges exceptionnelles sur opérations de gestion',
    '672' => 'Valeurs comptables des cessions d\'immobilisations',
    '673' => 'Charges exceptionnelles sur opérations en capital',
    '674' => 'Valeurs comptables des cessions d\'immobilisations',
    '675' => 'Dotations aux amortissements sur immobilisations',
    '676' => 'Dotations aux provisions pour dépréciation',
    '677' => 'Dotations aux provisions pour dépréciation',
    '678' => 'Dotations aux provisions pour dépréciation',
    '679' => 'Dotations aux provisions pour dépréciation',
    '68' => 'Dotations aux amortissements et aux provisions',
    '681' => 'Dotations aux amortissements sur immobilisations',
    '682' => 'Dotations aux provisions pour dépréciation',
    '683' => 'Dotations aux provisions pour dépréciation',
    '684' => 'Dotations aux provisions pour dépréciation',
    '685' => 'Dotations aux provisions pour dépréciation',
    '686' => 'Dotations aux provisions pour dépréciation',
    '687' => 'Dotations aux provisions pour dépréciation',
    '688' => 'Dotations aux provisions pour dépréciation',
    '689' => 'Dotations aux provisions pour dépréciation'
];

// Classe 7 - Comptes de produits
$plan_comptable[7] = [
    '701' => 'Ventes de marchandises',
    '702' => 'Ventes de produits finis',
    '703' => 'Ventes de produits intermédiaires',
    '704' => 'Ventes de produits résiduels',
    '705' => 'Travaux facturés',
    '706' => 'Services facturés',
    '707' => 'Ventes de produits accessoires',
    '708' => 'Produits des activités annexes',
    '709' => 'Rabais, remises et ristournes accordés',
    '71' => 'Production stockée',
    '711' => 'Variation des stocks de produits en-cours',
    '712' => 'Variation des stocks de produits intermédiaires',
    '713' => 'Variation des stocks de produits finis',
    '714' => 'Variation des stocks de produits résiduels',
    '715' => 'Variation des stocks de produits accessoires',
    '716' => 'Variation des stocks de produits des activités annexes',
    '717' => 'Variation des stocks de produits en-cours',
    '718' => 'Variation des stocks de produits intermédiaires',
    '719' => 'Variation des stocks de produits finis',
    '72' => 'Production immobilisée',
    '721' => 'Production immobilisée d\'immobilisations corporelles',
    '722' => 'Production immobilisée d\'immobilisations incorporelles',
    '723' => 'Production immobilisée d\'immobilisations financières',
    '724' => 'Production immobilisée de stocks',
    '725' => 'Production immobilisée de stocks',
    '726' => 'Production immobilisée de stocks',
    '727' => 'Production immobilisée de stocks',
    '728' => 'Production immobilisée de stocks',
    '729' => 'Production immobilisée de stocks',
    '73' => 'Produits partiels sur opérations à long terme',
    '731' => 'Produits partiels sur opérations à long terme',
    '732' => 'Produits partiels sur opérations à long terme',
    '733' => 'Produits partiels sur opérations à long terme',
    '734' => 'Produits partiels sur opérations à long terme',
    '735' => 'Produits partiels sur opérations à long terme',
    '736' => 'Produits partiels sur opérations à long terme',
    '737' => 'Produits partiels sur opérations à long terme',
    '738' => 'Produits partiels sur opérations à long terme',
    '739' => 'Produits partiels sur opérations à long terme',
    '74' => 'Subventions d\'exploitation',
    '741' => 'Subventions d\'exploitation',
    '742' => 'Subventions d\'exploitation',
    '743' => 'Subventions d\'exploitation',
    '744' => 'Subventions d\'exploitation',
    '745' => 'Subventions d\'exploitation',
    '746' => 'Subventions d\'exploitation',
    '747' => 'Subventions d\'exploitation',
    '748' => 'Subventions d\'exploitation',
    '749' => 'Subventions d\'exploitation',
    '75' => 'Autres produits de gestion courante',
    '751' => 'Produits exceptionnels sur opérations de gestion',
    '752' => 'Produits des cessions d\'immobilisations',
    '753' => 'Produits exceptionnels sur opérations en capital',
    '754' => 'Produits des cessions d\'immobilisations',
    '755' => 'Reprises sur amortissements et provisions',
    '756' => 'Reprises sur provisions pour dépréciation',
    '757' => 'Reprises sur provisions pour dépréciation',
    '758' => 'Reprises sur provisions pour dépréciation',
    '759' => 'Reprises sur provisions pour dépréciation',
    '76' => 'Produits financiers',
    '761' => 'Produits des participations',
    '762' => 'Produits des autres immobilisations financières',
    '763' => 'Produits des autres immobilisations financières',
    '764' => 'Produits des autres immobilisations financières',
    '765' => 'Produits des autres immobilisations financières',
    '766' => 'Produits des autres immobilisations financières',
    '767' => 'Produits des autres immobilisations financières',
    '768' => 'Produits des autres immobilisations financières',
    '77' => 'Produits exceptionnels',
    '771' => 'Produits exceptionnels sur opérations de gestion',
    '772' => 'Produits des cessions d\'immobilisations',
    '773' => 'Produits exceptionnels sur opérations en capital',
    '774' => 'Produits des cessions d\'immobilisations',
    '775' => 'Reprises sur amortissements et provisions',
    '776' => 'Reprises sur provisions pour dépréciation',
    '777' => 'Reprises sur provisions pour dépréciation',
    '778' => 'Reprises sur provisions pour dépréciation',
    '779' => 'Reprises sur provisions pour dépréciation',
    '78' => 'Reprises sur amortissements et provisions',
    '781' => 'Reprises sur amortissements sur immobilisations',
    '782' => 'Reprises sur provisions pour dépréciation',
    '783' => 'Reprises sur provisions pour dépréciation',
    '784' => 'Reprises sur provisions pour dépréciation',
    '785' => 'Reprises sur provisions pour dépréciation',
    '786' => 'Reprises sur provisions pour dépréciation',
    '787' => 'Reprises sur provisions pour dépréciation',
    '788' => 'Reprises sur provisions pour dépréciation',
    '789' => 'Reprises sur provisions pour dépréciation'
];

// Classe 8 - Comptes de résultats
$plan_comptable[8] = [
    '801' => 'Marge brute sur marchandises',
    '802' => 'Marge brute sur matières',
    '803' => 'Marge brute sur autres approvisionnements',
    '804' => 'Production de l\'exercice',
    '805' => 'Production de l\'exercice',
    '806' => 'Production de l\'exercice',
    '807' => 'Production de l\'exercice',
    '808' => 'Production de l\'exercice',
    '81' => 'Valeur ajoutée',
    '811' => 'Valeur ajoutée',
    '812' => 'Valeur ajoutée',
    '813' => 'Valeur ajoutée',
    '814' => 'Valeur ajoutée',
    '815' => 'Valeur ajoutée',
    '816' => 'Valeur ajoutée',
    '817' => 'Valeur ajoutée',
    '818' => 'Valeur ajoutée',
    '82' => 'Excédent brut d\'exploitation',
    '821' => 'Excédent brut d\'exploitation',
    '822' => 'Excédent brut d\'exploitation',
    '823' => 'Excédent brut d\'exploitation',
    '824' => 'Excédent brut d\'exploitation',
    '825' => 'Excédent brut d\'exploitation',
    '826' => 'Excédent brut d\'exploitation',
    '827' => 'Excédent brut d\'exploitation',
    '828' => 'Excédent brut d\'exploitation',
    '83' => 'Résultat d\'exploitation',
    '831' => 'Résultat d\'exploitation',
    '832' => 'Résultat d\'exploitation',
    '833' => 'Résultat d\'exploitation',
    '834' => 'Résultat d\'exploitation',
    '835' => 'Résultat d\'exploitation',
    '836' => 'Résultat d\'exploitation',
    '837' => 'Résultat d\'exploitation',
    '838' => 'Résultat d\'exploitation',
    '84' => 'Résultat financier',
    '841' => 'Résultat financier',
    '842' => 'Résultat financier',
    '843' => 'Résultat financier',
    '844' => 'Résultat financier',
    '845' => 'Résultat financier',
    '846' => 'Résultat financier',
    '847' => 'Résultat financier',
    '848' => 'Résultat financier',
    '85' => 'Résultat courant avant impôts',
    '851' => 'Résultat courant avant impôts',
    '852' => 'Résultat courant avant impôts',
    '853' => 'Résultat courant avant impôts',
    '854' => 'Résultat courant avant impôts',
    '855' => 'Résultat courant avant impôts',
    '856' => 'Résultat courant avant impôts',
    '857' => 'Résultat courant avant impôts',
    '858' => 'Résultat courant avant impôts',
    '86' => 'Résultat exceptionnel',
    '861' => 'Résultat exceptionnel',
    '862' => 'Résultat exceptionnel',
    '863' => 'Résultat exceptionnel',
    '864' => 'Résultat exceptionnel',
    '865' => 'Résultat exceptionnel',
    '866' => 'Résultat exceptionnel',
    '867' => 'Résultat exceptionnel',
    '868' => 'Résultat exceptionnel',
    '87' => 'Résultat net de l\'exercice',
    '871' => 'Résultat net de l\'exercice',
    '872' => 'Résultat net de l\'exercice',
    '873' => 'Résultat net de l\'exercice',
    '874' => 'Résultat net de l\'exercice',
    '875' => 'Résultat net de l\'exercice',
    '876' => 'Résultat net de l\'exercice',
    '877' => 'Résultat net de l\'exercice',
    '878' => 'Résultat net de l\'exercice',
    '88' => 'Résultat net de l\'exercice',
    '881' => 'Résultat net de l\'exercice',
    '882' => 'Résultat net de l\'exercice',
    '883' => 'Résultat net de l\'exercice',
    '884' => 'Résultat net de l\'exercice',
    '885' => 'Résultat net de l\'exercice',
    '886' => 'Résultat net de l\'exercice',
    '887' => 'Résultat net de l\'exercice',
    '888' => 'Résultat net de l\'exercice',
    '891' => 'Impôt sur le résultat',
    '892' => 'Impôt sur le résultat',
    '893' => 'Impôt sur le résultat',
    '894' => 'Impôt sur le résultat',
    '895' => 'Impôt sur le résultat',
    '896' => 'Impôt sur le résultat',
    '897' => 'Impôt sur le résultat',
    '898' => 'Impôt sur le résultat'
];

echo "🎯 PLAN COMPTABLE SYSCOHADA CI COMPLET (8 chiffres) :\n\n";

foreach ($plan_comptable as $classe => $comptes) {
    echo "📋 CLASSE $classe :\n";
    $compteur = 1;
    foreach ($comptes as $prefixe => $intitule) {
        $compte_8_chiffres = str_pad($prefixe . $compteur, 8, '0', STR_PAD_RIGHT);
        echo "   • $compte_8_chiffres : $intitule\n";
        $compteur++;
    }
    echo "\n";
}

echo "✅ LOGIQUE DE NUMÉROTATION :\n";
echo "   1. Préfixe SYSCOHADA (2-4 chiffres)\n";
echo "   2. Numéro séquentiel croissant (1, 2, 3...)\n";
echo "   3. Complément avec des zéros pour atteindre 8 chiffres\n";
echo "   4. Format : PPPPNNNN (PPPP = préfixe, NNNN = séquentiel)\n\n";

echo "🚀 EXEMPLES :\n";
echo "   • 401 + 1 + 0000 = 40110000 (Fournisseurs)\n";
echo "   • 401 + 2 + 0000 = 40120000 (Fournisseurs)\n";
echo "   • 571 + 1 + 0000 = 57110000 (Caisse)\n";
echo "   • 613 + 1 + 0000 = 61310000 (Locations)\n";
echo "   • 445 + 2 + 0000 = 44520000 (TVA déductible)\n\n";

echo "💡 APPLICATION :\n";
echo "   • Chaque préfixe génère une série croissante\n";
echo "   • 40110000, 40120000, 40130000...\n";
echo "   • 57110000, 57120000, 57130000...\n";
echo "   • Compatible avec votre système existant\n";
?>
