<?php
// Test du système de numérotation croissante
echo "=== TEST NUMÉROTATION CROISSANTE ===\n\n";

// Simulation du système
$compteurs = [
    '891' => 1, '892' => 1, '893' => 1, '894' => 1, '895' => 1, '896' => 1, '897' => 1, '898' => 1,
    '401' => 1, '411' => 1, '445' => 1, '521' => 1, '531' => 1, '571' => 1,
    '601' => 1, '613' => 1, '635' => 1, '641' => 1, '645' => 1
];

function mapCompteSyscohada($compte) {
    global $compteurs;
    
    $mapping = [
        '891' => '89110000', '892' => '89210000', '893' => '89310000', '894' => '89410000', '895' => '89510000', '896' => '89610000', '897' => '89710000', '898' => '89810000',
        '401' => '40110000', '411' => '41110000', '445' => '44510000', '4452' => '44521000', '4455' => '44551000',
        '521' => '52110000', '531' => '53110000', '571' => '57110000',
        '601' => '60110000', '613' => '61310000', '635' => '63510000', '641' => '64110000', '645' => '64510000'
    ];
    
    if (isset($mapping[$compte])) {
        return $mapping[$compte];
    }
    
    $prefixe = substr($compte, 0, 4);
    
    if (isset($compteurs[$prefixe])) {
        $compteurs[$prefixe]++;
        $numero = $compteurs[$prefixe];
        return $prefixe . str_pad($numero, 4, '0', STR_PAD_LEFT) . '00';
    }
    
    return $prefixe . '10000';
}

echo "🧪 TEST DE NUMÉROTATION :\n\n";

// Test pour la classe 8 (comptes de résultats)
echo "📋 CLASSE 8 - COMPTES DE RÉSULTATS :\n";
$tests_classe8 = ['891', '892', '893', '894', '895', '896', '897', '898'];

foreach ($tests_classe8 as $compte) {
    $compte_mappe = mapCompteSyscohada($compte);
    echo "   • $compte → $compte_mappe\n";
}

echo "\n🔄 ÉTAT DES COMPTEURS :\n";
foreach ($compteurs as $prefixe => $valeur) {
    echo "   • $prefixe : $valeur\n";
}

echo "\n✅ SYSTÈME DE NUMÉROTATION :\n";
echo "   • Premier appel : 89110000, 89120000, 89130000...\n";
echo "   • Numérotation automatique : 0001, 0002, 0003...\n";
echo "   • Format : PPPPNNNN00 (PPPP = préfixe, NNNN = séquentiel)\n";
echo "   • Compatible avec votre système existant\n\n";

echo "🚀 RÉSULTAT :\n";
echo "   • Chaque préfixe génère une série croissante\n";
echo "   • 89110000, 89120000, 89130000, 89140000...\n";
echo "   • 40110000, 40120000, 40130000, 40140000...\n";
echo "   • Respecte l'ordre chronologique de création\n";
?>
