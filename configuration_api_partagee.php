<?php
// Configuration partagée pour l'API Gemini
// À partager avec votre responsable

echo "=== CONFIGURATION API GEMINI PARTAGÉE ===\n\n";

// 1. PARTAGE DE LA CLÉ API
echo "🔑 ÉTAPE 1 - PARTAGE DE LA CLÉ API :\n";
echo "   • Clé API actuelle : AIzaSyDuwMm9cdo_vTqBe9j3degykq4rL-kOKVU\n";
echo "   • Cette clé a un quota limité (429 errors)\n";
echo "   • Solution : Créer une nouvelle clé pour le responsable\n\n";

// 2. CRÉATION D'UN FICHIER DE CONFIGURATION PARTAGÉ
echo "⚙️  ÉTAPE 2 - FICHIER DE CONFIGURATION :\n";
echo "   • Créer un fichier .env partagé\n";
echo "   • Ajouter la clé API dans les deux environnements\n\n";

// 3. SYNCHRONISATION DES FICHIERS
echo "📁 ÉTAPE 3 - FICHIERS À SYNCHRONISER :\n";
echo "   • ia_traitement_standalone.php ✅\n";
echo "   • resources/views/accounting/scan.blade.php ✅\n";
echo "   • routes/web.php (route standalone) ✅\n\n";

// 4. CONFIGURATION MULTI-ENVIRONNEMENT
echo "🌍 ÉTAPE 4 - CONFIGURATION MULTI-ENVIRONNEMENT :\n\n";

$config_env = [
    'local' => [
        'api_key' => 'AIzaSyDuwMm9cdo_vTqBe9j3degykq4rL-kOKVU',
        'model' => 'gemini-flash-latest',
        'url' => 'http://127.0.0.1:8000/ia_traitement_standalone.php'
    ],
    'production' => [
        'api_key' => 'VOTRE_CLÉ_API_PRODUCTION',
        'model' => 'gemini-flash-latest',
        'url' => 'https://votresite.com/ia_traitement_standalone.php'
    ]
];

foreach ($config_env as $env => $config) {
    echo "📋 ENVIRONNEMENT $env :\n";
    foreach ($config as $key => $value) {
        echo "   • $key: $value\n";
    }
    echo "\n";
}

echo "🚀 ACTIONS À FAIRE :\n";
echo "   1. Créer une nouvelle clé API Gemini pour le responsable\n";
echo "   2. Ajouter la clé dans le .env de production\n";
echo "   3. Push tous les fichiers modifiés\n";
echo "   4. Le responsable pull les changements\n";
echo "   5. Tester sur les deux environnements\n\n";

echo "📝 FICHIER .ENV EXEMPLE :\n";
echo "GEMINI_API_KEY=AIzaSyDuwMm9cdo_vTqBe9j3degykq4rL-kOKVU\n";
echo "GEMINI_MODEL=gemini-flash-latest\n\n";

echo "✅ RÉSULTAT :\n";
echo "   • Même API sur les deux environnements\n";
echo "   • Mêmes résultats de scan\n";
echo "   • Système synchronisé\n";
?>
