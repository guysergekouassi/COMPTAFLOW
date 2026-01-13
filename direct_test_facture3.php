<?php
/**
 * Test direct du scan IA pour facture3
 * Script PHP qui simule l'upload et teste l'API
 */

// Configuration
$api_url = 'http://localhost/COMPTAFLOW/ia_traitement.php';
$image_path = __DIR__ . '/facture3.jpg';

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test Direct Scan IA - Facture3</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <style>
        .test-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem;
        }
        .test-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        .code-block {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            white-space: pre-wrap;
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class='test-container'>
        <div class='container'>
            <div class='row justify-content-center'>
                <div class='col-lg-10'>
                    <div class='test-card'>
                        <h2 class='text-center mb-4'>
                            <i class='bi bi-robot'></i>
                            Test Direct Scan IA - Facture3
                        </h2>";

// Vérifier si le fichier existe
if (!file_exists($image_path)) {
    echo "<div class='alert alert-danger'>
        <strong>Erreur:</strong> Le fichier facture3.jpg n'existe pas à l'emplacement attendu.
        <br>Chemin recherché: " . htmlspecialchars($image_path) . "
    </div>";
} else {
    echo "<div class='row mb-4'>
        <div class='col-md-6'>
            <h4>Informations fichier</h4>
            <ul class='list-unstyled'>
                <li><strong>Fichier:</strong> facture3.jpg</li>
                <li><strong>Taille:</strong> " . number_format(filesize($image_path) / 1024 / 1024, 2) . " MB</li>
                <li><strong>Type MIME:</strong> " . mime_content_type($image_path) . "</li>
                <li><strong>Date modification:</strong> " . date('d/m/Y H:i:s', filemtime($image_path)) . "</li>
            </ul>
        </div>
        <div class='col-md-6'>
            <h4>Aperçu</h4>
            <img src='facture3.jpg' class='img-fluid rounded' style='max-height: 200px;' alt='Facture3'>
        </div>
    </div>";

    // Préparer et exécuter la requête
    echo "<h4>Test de l'API Gemini</h4>";
    
    // Lire l'image
    $image_data = file_get_contents($image_path);
    $mime_type = mime_content_type($image_path);
    
    // Créer le fichier temporaire pour simuler l'upload
    $temp_file = tempnam(sys_get_temp_dir(), 'facture3_');
    file_put_contents($temp_file, $image_data);
    
    // Préparer les données POST
    $post_data = [
        'facture' => new CURLFile($temp_file, $mime_type, 'facture3.jpg')
    ];
    
    // Initialiser cURL
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: COMPTAFLOW-Test/1.0'
    ]);
    
    echo "<div class='mb-3'>
        <strong>URL de l'API:</strong> " . htmlspecialchars($api_url) . "<br>
        <strong>Méthode:</strong> POST<br>
        <strong>Fichier envoyé:</strong> facture3.jpg (" . $mime_type . ")
    </div>";
    
    // Exécuter la requête
    echo "<h5>Réponse de l'API:</h5>";
    echo "<div class='code-block'>";
    
    $start_time = microtime(true);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $total_time = microtime(true) - $start_time;
    
    curl_close($ch);
    
    // Nettoyer le fichier temporaire
    unlink($temp_file);
    
    if ($error) {
        echo "Erreur cURL: " . htmlspecialchars($error) . "\n";
    } else {
        echo "Code HTTP: " . $http_code . "\n";
        echo "Temps de réponse: " . number_format($total_time, 2) . " secondes\n\n";
        echo "Réponse brute:\n" . htmlspecialchars($response) . "\n\n";
        
        // Essayer de parser le JSON
        $decoded = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "JSON valide!\n\n";
            echo "Données structurées:\n";
            echo json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } else {
            echo "JSON invalide: " . json_last_error_msg() . "\n";
        }
    }
    
    echo "</div>";
    
    // Afficher un résumé
    echo "<div class='mt-4'>";
    if ($http_code === 200 && !$error) {
        $decoded = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE && !isset($decoded['error'])) {
            echo "<div class='alert alert-success'>
                <strong>✅ Test réussi!</strong> L'API a correctement analysé la facture3.
            </div>";
        } else {
            echo "<div class='alert alert-warning'>
                <strong>⚠️ Test partiel:</strong> L'API a répondu mais avec des erreurs.
            </div>";
        }
    } else {
        echo "<div class='alert alert-danger'>
            <strong>❌ Test échoué:</strong> L'API n'a pas pu traiter la demande.
        </div>";
    }
    echo "</div>";
}

echo "
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>";
?>
