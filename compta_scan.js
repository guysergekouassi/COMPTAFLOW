/**
 * Système de scan intelligent COMPTAFLOW
 * Intégration IA expert SYSCOHADA Côte d'Ivoire
 */

class ComptaScanSystem {
    constructor() {
        this.apiEndpoint = '/ia_traitement_standalone.php';
        this.maxFileSize = 5 * 1024 * 1024; // 5MB
        this.supportedFormats = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupDropZone();
        this.setupFileValidation();
    }

    setupEventListeners() {
        const fileInput = document.getElementById('input_facture');
        const scanBtn = document.getElementById('btn_scan_facture');
        
        if (fileInput) {
            fileInput.addEventListener('change', (e) => this.handleFileSelect(e));
        }
        
        if (scanBtn) {
            scanBtn.addEventListener('click', () => this.triggerScan());
        }
    }

    setupDropZone() {
        const dropZone = document.getElementById('drop_zone');
        if (!dropZone) return;

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
            }, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.add('drag-over');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => {
                dropZone.classList.remove('drag-over');
            }, false);
        });

        dropZone.addEventListener('drop', (e) => {
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                this.processFile(files[0]);
            }
        }, false);
    }

    setupFileValidation() {
        const fileInput = document.getElementById('input_facture');
        if (!fileInput) return;

        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                this.validateFile(file);
            }
        });
    }

    validateFile(file) {
        // Vérification du format
        if (!this.supportedFormats.includes(file.type)) {
            this.showError('Format non supporté. Utilisez JPG, PNG ou PDF.');
            return false;
        }

        // Vérification de la taille
        if (file.size > this.maxFileSize) {
            this.showError('Fichier trop volumineux. Maximum 5MB.');
            return false;
        }

        return true;
    }

    handleFileSelect(event) {
        const file = event.target.files[0];
        if (file && this.validateFile(file)) {
            this.displayPreview(file);
        }
    }

    displayPreview(file) {
        const preview = document.getElementById('preview_image');
        if (!preview) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }

    triggerScan() {
        const fileInput = document.getElementById('input_facture');
        const file = fileInput.files[0];
        
        if (!file) {
            this.showError('Veuillez sélectionner un fichier.');
            return;
        }

        if (!this.validateFile(file)) {
            return;
        }

        this.processFile(file);
    }

    async processFile(file) {
        this.showLoading(true);
        
        const formData = new FormData();
        formData.append('facture', file);
        
        // Ajouter le token CSRF si disponible
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            formData.append('_token', csrfToken);
        }

        try {
            console.log('🚀 Analyse par IA SYSCOHADA CI...');
            
            const response = await fetch(this.apiEndpoint, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.error) {
                throw new Error(data.error);
            }

            // Traitement des données
            this.fillFormWithData(data);
            this.showSuccess('Analyse terminée avec succès !');
            
            console.log('✅ Données extraites:', data);

        } catch (error) {
            console.error('❌ Erreur:', error);
            this.showError('Erreur lors de l\'analyse: ' + error.message);
        } finally {
            this.showLoading(false);
        }
    }

    fillFormWithData(data) {
        try {
            // Remplir les champs de base
            this.fillField('date_exercice', data.date);
            this.fillField('tiers_nom', data.tiers);
            this.fillField('reference_piece', data.reference);
            this.fillField('montant_ht', data.montant_ht);
            this.fillField('montant_tva', data.montant_tva);
            this.fillField('montant_ttc', data.montant_ttc);

            // Remplir le tableau des écritures
            if (data.ecriture && Array.isArray(data.ecriture)) {
                this.fillAccountingEntries(data.ecriture);
            }

            // Afficher l'analyse
            if (data.analyse) {
                this.showAnalysis(data.analyse);
            }

        } catch (error) {
            console.error('Erreur remplissage formulaire:', error);
        }
    }

    fillField(fieldId, value) {
        const field = document.getElementById(fieldId);
        if (field && value) {
            field.value = value;
            field.dispatchEvent(new Event('input', { bubbles: true }));
            field.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    fillAccountingEntries(entries) {
        entries.forEach((ligne, index) => {
            this.fillField(`compte_${index}`, ligne.compte);
            this.fillField(`libelle_${index}`, ligne.intitule || ligne.libelle);
            this.fillField(`debit_${index}`, ligne.debit);
            this.fillField(`credit_${index}`, ligne.credit);
        });

        // Mettre à jour les totaux
        this.updateTotals();
    }

    updateTotals() {
        const debitFields = document.querySelectorAll('[id^="debit_"]');
        const creditFields = document.querySelectorAll('[id^="credit_"]');
        
        let totalDebit = 0;
        let totalCredit = 0;

        debitFields.forEach(field => {
            totalDebit += parseFloat(field.value) || 0;
        });

        creditFields.forEach(field => {
            totalCredit += parseFloat(field.value) || 0;
        });

        this.fillField('total_debit', totalDebit.toFixed(2));
        this.fillField('total_credit', totalCredit.toFixed(2));

        // Vérifier l'équilibre
        const balance = Math.abs(totalDebit - totalCredit);
        const isBalanced = balance < 0.01;
        
        this.updateBalanceIndicator(isBalanced);
    }

    updateBalanceIndicator(isBalanced) {
        const indicator = document.getElementById('balance_indicator');
        if (!indicator) return;

        if (isBalanced) {
            indicator.innerHTML = '✅ ÉQUILIBRÉ';
            indicator.className = 'badge bg-success';
        } else {
            indicator.innerHTML = '⚠️ NON ÉQUILIBRÉ';
            indicator.className = 'badge bg-warning';
        }
    }

    showAnalysis(analysis) {
        const analysisDiv = document.getElementById('ia_analysis');
        if (analysisDiv) {
            analysisDiv.innerHTML = `
                <div class="alert alert-info">
                    <strong>🧠 Analyse IA SYSCOHADA:</strong><br>
                    ${analysis}
                </div>
            `;
        }
    }

    showLoading(show) {
        const loading = document.getElementById('loading_scan');
        const btn = document.getElementById('btn_scan_facture');
        
        if (loading) {
            loading.style.display = show ? 'block' : 'none';
        }
        
        if (btn) {
            btn.disabled = show;
            btn.innerHTML = show ? 
                '<span class="spinner-border spinner-border-sm me-2"></span>ANALYSE...' : 
                '🔍 ANALYSER LA FACTURE';
        }
    }

    showError(message) {
        this.showAlert(message, 'danger');
    }

    showSuccess(message) {
        this.showAlert(message, 'success');
    }

    showAlert(message, type) {
        const alertContainer = document.getElementById('alert_container');
        if (!alertContainer) return;

        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        alertContainer.appendChild(alert);

        // Auto-suppression après 5 secondes
        setTimeout(() => {
            if (alert.parentNode) {
                alert.parentNode.removeChild(alert);
            }
        }, 5000);
    }

    // Méthode utilitaire pour compresser les images avant envoi
    async compressImage(file) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.src = e.target.result;
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const ctx = canvas.getContext('2d');
                    
                    // Redimensionner si trop grand
                    const MAX_WIDTH = 1200;
                    const MAX_HEIGHT = 1200;
                    let width = img.width;
                    let height = img.height;

                    if (width > MAX_WIDTH) {
                        height *= MAX_WIDTH / width;
                        width = MAX_WIDTH;
                    }
                    if (height > MAX_HEIGHT) {
                        width *= MAX_HEIGHT / height;
                        height = MAX_HEIGHT;
                    }

                    canvas.width = width;
                    canvas.height = height;
                    ctx.drawImage(img, 0, 0, width, height);

                    canvas.toBlob(resolve, 'image/jpeg', 0.8);
                };
            };
            reader.readAsDataURL(file);
        });
    }
}

// Initialisation automatique au chargement du DOM
document.addEventListener('DOMContentLoaded', () => {
    window.comptaScan = new ComptaScanSystem();
});

// Fonction globale pour compatibilité
window.scannerFacture = function() {
    if (window.comptaScan) {
        window.comptaScan.triggerScan();
    }
};
