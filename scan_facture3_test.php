<?php
/**
 * Test de scan IA pour la facture3
 * Script de test pour vérifier le fonctionnement du système avec facture3.jpg
 */

echo "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test Scan IA - Facture3</title>
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
        .preview-img {
            max-width: 100%;
            max-height: 400px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .result-box {
            background: #f8f9ff;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 1rem;
            border-left: 4px solid #667eea;
        }
        .loading {
            display: none;
            text-align: center;
            padding: 2rem;
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
                            Test Scan IA - Facture3
                        </h2>
                        
                        <div class='row'>
                            <div class='col-md-6'>
                                <h4>Image source</h4>
                                <img src='facture3.jpg' class='preview-img' alt='Facture3'>
                                <p class='text-muted mt-2'>
                                    <i class='bi bi-file-image'></i>
                                    facture3.jpg (3.3 MB)
                                </p>
                            </div>
                            
                            <div class='col-md-6'>
                                <h4>Analyse IA</h4>
                                <div id='loading' class='loading'>
                                    <div class='spinner-border text-primary' role='status'>
                                        <span class='visually-hidden'>Analyse en cours...</span>
                                    </div>
                                    <p class='mt-2'>🧠 Analyse par l'IA SYSCOHADA...</p>
                                </div>
                                
                                <button id='btn_analyser' class='btn btn-primary btn-lg w-100'>
                                    <i class='bi bi-search'></i>
                                    Lancer l'analyse IA
                                </button>
                                
                                <div id='resultat' class='result-box' style='display:none;'>
                                    <h5><i class='bi bi-clipboard-data'></i> Résultat de l'analyse</h5>
                                    <div id='resultat_content'></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class='mt-4'>
                            <h4>Informations système</h4>
                            <div class='row'>
                                <div class='col-md-6'>
                                    <strong>Fichier test:</strong> facture3.jpg<br>
                                    <strong>Taille:</strong> " . number_format(filesize('facture3.jpg') / 1024 / 1024, 2) . " MB<br>
                                    <strong>Date modification:</strong> " . date('d/m/Y H:i:s', filemtime('facture3.jpg')) . "
                                </div>
                                <div class='col-md-6'>
                                    <strong>API:</strong> Gemini Flash<br>
                                    <strong>Modèle:</strong> SYSCOHADA CI<br>
                                    <strong>Statut:</strong> <span class='badge bg-success'>Prêt</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src='https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js'></script>
    <script>
        $(document).ready(function() {
            $('#btn_analyser').click(function() {
                var btn = $(this);
                var loading = $('#loading');
                var resultat = $('#resultat');
                var resultatContent = $('#resultat_content');
                
                // Afficher le chargement
                btn.prop('disabled', true);
                loading.show();
                resultat.hide();
                
                // Créer FormData pour l'upload
                var formData = new FormData();
                
                // Récupérer l'image facture3.jpg
                fetch('facture3.jpg')
                    .then(res => res.blob())
                    .then(blob => {
                        formData.append('facture', blob, 'facture3.jpg');
                        
                        // Envoyer à l'API
                        $.ajax({
                            url: 'ia_traitement.php',
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                try {
                                    var data = typeof response === 'string' ? JSON.parse(response) : response;
                                    
                                    if (data.error) {
                                        resultatContent.html('<div class=\"alert alert-danger\"><strong>Erreur:</strong> ' + data.error + '</div>');
                                    } else {
                                        // Afficher les résultats
                                        var html = '<div class=\"row\">';
                                        html += '<div class=\"col-md-6\"><strong>Type document:</strong> ' + (data.type_document || 'N/A') + '</div>';
                                        html += '<div class=\"col-md-6\"><strong>Tiers:</strong> ' + (data.tiers || 'N/A') + '</div>';
                                        html += '<div class=\"col-md-6\"><strong>Date:</strong> ' + (data.date || 'N/A') + '</div>';
                                        html += '<div class=\"col-md-6\"><strong>Référence:</strong> ' + (data.reference || 'N/A') + '</div>';
                                        html += '<div class=\"col-md-4\"><strong>Montant HT:</strong> ' + (data.montant_ht || 0) + ' XOF</div>';
                                        html += '<div class=\"col-md-4\"><strong>Montant TVA:</strong> ' + (data.montant_tva || 0) + ' XOF</div>';
                                        html += '<div class=\"col-md-4\"><strong>Montant TTC:</strong> ' + (data.montant_ttc || 0) + ' XOF</div>';
                                        html += '</div>';
                                        
                                        if (data.analyse) {
                                            html += '<div class=\"mt-3\"><strong>Analyse:</strong> ' + data.analyse + '</div>';
                                        }
                                        
                                        if (data.ecriture && data.ecriture.length > 0) {
                                            html += '<h6 class=\"mt-3\">Écriture comptable:</h6>';
                                            html += '<table class=\"table table-sm\">';
                                            html += '<thead><tr><th>Compte</th><th>Libellé</th><th>Débit</th><th>Crédit</th></tr></thead>';
                                            html += '<tbody>';
                                            
                                            var totalDebit = 0, totalCredit = 0;
                                            data.ecriture.forEach(function(ligne) {
                                                html += '<tr>';
                                                html += '<td>' + ligne.compte + '</td>';
                                                html += '<td>' + ligne.intitule + '</td>';
                                                html += '<td>' + (ligne.debit || 0) + '</td>';
                                                html += '<td>' + (ligne.credit || 0) + '</td>';
                                                html += '</tr>';
                                                totalDebit += parseFloat(ligne.debit || 0);
                                                totalCredit += parseFloat(ligne.credit || 0);
                                            });
                                            
                                            html += '<tr class=\"table-primary fw-bold\">';
                                            html += '<td colspan=\"2\">TOTAUX</td>';
                                            html += '<td>' + totalDebit.toFixed(2) + '</td>';
                                            html += '<td>' + totalCredit.toFixed(2) + '</td>';
                                            html += '</tr>';
                                            html += '</tbody></table>';
                                            
                                            var equilibre = Math.abs(totalDebit - totalCredit) < 0.01;
                                            var badgeClass = equilibre ? 'success' : 'danger';
                                            var texteEquilibre = equilibre ? '✅ Écriture équilibrée' : '❌ Écriture non équilibrée';
                                            html += '<div class=\"mt-2\"><span class=\"badge bg-' + badgeClass + '\">' + texteEquilibre + '</span></div>';
                                        }
                                        
                                        resultatContent.html(html);
                                    }
                                } catch (e) {
                                    resultatContent.html('<div class=\"alert alert-danger\"><strong>Erreur de parsing:</strong> ' + e.message + '<br><pre>' + response + '</pre></div>');
                                }
                                
                                loading.hide();
                                resultat.show();
                                btn.prop('disabled', false);
                            },
                            error: function(xhr, status, error) {
                                resultatContent.html('<div class=\"alert alert-danger\"><strong>Erreur AJAX:</strong> ' + error + '<br>Status: ' + status + '<br>Réponse: ' + xhr.responseText + '</div>');
                                loading.hide();
                                resultat.show();
                                btn.prop('disabled', false);
                            }
                        });
                    })
                    .catch(err => {
                        resultatContent.html('<div class=\"alert alert-danger\"><strong>Erreur de chargement de l\'image:</strong> ' + err.message + '</div>');
                        loading.hide();
                        resultat.show();
                        btn.prop('disabled', false);
                    });
            });
        });
    </script>
</body>
</html>";
?>
