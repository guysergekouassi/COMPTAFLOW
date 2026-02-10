<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')

<style>
    /* Premium Design Overrides */
    .card-importer {
        background: #ffffff !important;
        border-radius: 40px !important;
        padding: 30px !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08) !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        text-align: center !important;
        min-height: 280px !important;
        border: none !important;
    }

    .card-importer h2 {
        color: #1a202c !important;
        font-weight: 800 !important;
        font-size: 2rem !important;
        margin-bottom: 25px !important;
        line-height: 1.2 !important;
    }

    .importer-dropzone {
        border: 2px dashed #cbd5e1 !important;
        border-radius: 30px !important;
        padding: 25px !important;
        width: 100% !important;
        max-width: 180px !important;
        cursor: pointer !important;
        background: #fbfcfe !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        gap: 10px !important;
    }

    .card-info-syscohada {
        background: linear-gradient(135deg, #2563eb 0%, #1e3a8a 100%) !important;
        border-radius: 50px !important;
        padding: 40px !important;
        color: #ffffff !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
        min-height: 280px !important;
        border: none !important;
        box-shadow: 0 15px 35px rgba(37, 99, 235, 0.25) !important;
    }

    .card-info-syscohada h2 {
        color: #ffffff !important;
        font-weight: 800 !important;
        font-size: 2.2rem !important;
        margin-bottom: 15px !important;
    }

    .card-info-syscohada p {
        font-size: 1.1rem !important;
        line-height: 1.5 !important;
        opacity: 0.95 !important;
        color: #ffffff !important;
    }

    /* Missing Styles Restored */
    .table-container-card {
        background: #ffffff !important;
        border-radius: 30px !important;
        padding: 30px !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.03) !important;
        margin-top: 30px !important;
    }

    .table-accounting thead th {
        background: #f8fafc !important;
        text-transform: uppercase !important;
        font-size: 0.7rem !important;
        font-weight: 800 !important;
        letter-spacing: 0.05em !important;
        color: #475569 !important;
        border-bottom: 2px solid #f1f5f9 !important;
        padding: 1.2rem 1rem !important;
    }

    .processing-overlay {
        position: absolute !important;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(255, 255, 255, 0.9) !important;
        backdrop-filter: blur(5px) !important;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 100 !important;
        border-radius: 40px !important;
    }

    .preview-mode-img {
        max-width: 100% !important;
        max-height: 220px !important;
        border-radius: 20px !important;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
        margin-top: 15px !important;
    }

    .select2-container--bootstrap4 .select2-selection {
        border-radius: 12px !important;
        border: 1px solid #e2e8f0 !important;
        height: 40px !important;
        display: flex !important;
        align-items: center !important;
    }
</style>
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'ECRITURES PAR <span class="text-primary">SCAN</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Top Cards Layout: Forced Side-by-Side -->
                        <div class="d-flex gap-3 mb-4" style="overflow-x: auto; flex-wrap: nowrap;">
                            <!-- Card 1: Importer (Narrow but with slightly larger text) -->
                            <div class="card card-importer position-relative" style="width: 180px; min-width: 180px; min-height: 240px !important;">
                                <div id="uploadContainer">
                                    <h2 style="font-size: 1.7rem !important; margin-bottom: 20px !important;">Importer<br>Facture</h2>
                                    <div id="dropZone" class="importer-dropzone mx-auto" style="padding: 12px !important; max-width: 130px !important;">
                                        <i class="bx bx-scan" style="font-size: 24px !important;"></i>
                                        <span style="font-size: 0.7rem !important;">CLIQUER OU<br>GLISSER</span>
                                    </div>
                                </div>
                                
                                <img id="imagePreview" src="" class="preview-mode-img d-none" />
                                <input type="file" id="fileInput" class="d-none" accept="image/*,.pdf" />

                                <div id="processingUI" class="processing-overlay d-none">
                                    <div class="spinner-border text-primary mb-2" style="width: 2.5rem; height: 2.5rem;" role="status"></div>
                                    <h6 class="fw-bold mb-0">ANALYSE...</h6>
                                </div>
                            </div>

                            <!-- Card 2: Automatisation (Narrower and on the same line) -->
                            <div class="card card-info-syscohada" style="width: 650px; min-width: 300px; background: linear-gradient(135deg, #2563eb 0%, #1e3a8a 100%) !important; min-height: 240px !important; border-radius: 40px !important;">
                                <h2 style="font-size: 1.8rem !important; margin-bottom: 10px !important;">Automatisation SYSCOHADA</h2>
                                <p style="font-size: 1rem !important; margin-bottom: 0 !important;">
                                    Analyse de vos factures, génère le <strong>Numéro de Saisie</strong>, 
                                    déduit le <strong>Code Journal</strong> et ventile les montants entre le <strong>Compte Général</strong> 
                                    et le <strong>Compte Tiers</strong>.
                                </p>
                            </div>
                        </div>

                        <!-- Table Row -->
                        <div class="table-container-card">
                            <div class="d-flex justify-content-between align-items-center mb-4 px-2">
                                <h5 class="mb-0 fw-extrabold text-dark"><i class="bx bx-list-check me-2 text-primary"></i>ÉCRITURES GÉNÉRÉES</h5>
                                <div class="d-flex gap-2">
                                    <button id="btnApplyVAT" class="btn btn-primary btn-sm rounded-pivot px-3 d-none" onclick="window.applyVAT18()">
                                        <i class="bx bx-plus me-1"></i>APPLIQUER TVA 18%
                                    </button>
                                    <span class="badge bg-label-secondary px-3 py-2 rounded-pivot">N° Saisie: <span id="displayNSaisie" class="fw-bold text-primary">{{ $nextSaisieNumber }}</span></span>
                                    <button id="btnReset" class="btn btn-icon btn-outline-secondary btn-sm rounded-circle"><i class="bx bx-refresh"></i></button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="tableEntries" class="table table-accounting">
                                    <thead>
                                        <tr>
                                            <th style="width: 200px;">Compte Général</th>
                                            <th style="width: 200px;">Compte Tiers</th>
                                            <th style="min-width: 200px;">Libellé / Détails</th>
                                            <th class="text-end" style="width: 130px;">Débit</th>
                                            <th class="text-end" style="width: 130px;">Crédit</th>
                                            <th style="width: 150px;">Poste Trésorerie</th>
                                            <th style="width: 50px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="entriesBody">
                                        <tr><td colspan="6" class="text-center py-5 text-muted"><i class="bx bx-info-circle me-1"></i>En attente de document pour analyse précise.</td></tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Footer Actions (Compact Container) -->
                            <div class="mt-3 pt-3 border-top">
                                <div class="row g-3 align-items-center">
                                    <div class="col-md-7">
                                        <div class="d-flex justify-content-around bg-light rounded-4 p-2 border border-dashed" style="min-height: 80px;">
                                            <div class="text-center px-2">
                                                <span class="form-label-premium">TOTAL DÉBIT</span>
                                                <div id="summaryDebit" class="total-amount" style="font-size: 1.4rem;">0</div>
                                            </div>
                                            <div class="vr mx-2"></div>
                                            <div class="text-center px-2">
                                                <span class="form-label-premium">TOTAL CRÉDIT</span>
                                                <div id="summaryCredit" class="total-amount" style="font-size: 1.4rem;">0</div>
                                            </div>
                                            <div class="vr mx-2"></div>
                                            <div class="text-center px-2 d-flex flex-column justify-content-center">
                                                <span class="form-label-premium">BALANCE</span>
                                                <div id="statusIndicator"><i class="bx bx-minus-circle text-muted fs-3"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="row g-2">
                                            <div class="col-12">
                                                <button id="btnSave" class="btn btn-primary w-100 py-3 rounded-pivot shadow-sm fw-bold fs-5" disabled>
                                                    VALIDER & ENREGISTRER L'ÉCRITURE
                                                </button>
                                            </div>
                                            <div class="col-12">
                                                <button id="btnSaveDraft" class="btn btn-outline-primary w-100 py-2 rounded-pivot shadow-sm fw-bold" disabled onclick="window.sauvegarderEnBrouillon()">
                                                    ENREGISTRER EN BROUILLON
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                @include('components.footer')
            </div>
        </div>
    </div>

    <!-- Tier Creation Modal -->
    <div class="modal fade" id="createTiersModal" tabindex="-1" aria-hidden="true" style="z-index: 10000;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content overflow-hidden" style="border-radius: 20px;">
                <div class="modal-header bg-primary text-white p-4">
                    <h5 class="modal-title fw-bold text-white"><i class="bx bx-user-plus me-2"></i>Nouveau Tiers Comptable</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createTiersForm">
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <div class="col-md-6"><label class="form-label fw-bold">Type de Tiers *</label><select id="type_tiers" name="type_de_tiers" class="form-select form-select-lg" required><option value="" selected disabled>Choisir...</option><option value="Fournisseur">Fournisseur</option><option value="Client">Client</option><option value="Personnel">Personnel</option><option value="CNPS">CNPS</option><option value="Impots">Impots</option><option value="Associé">Associé</option><option value="Divers Tiers">Divers Tiers</option></select></div>
                            <div class="col-md-6"><label class="form-label fw-bold">Compte Général de Rattachement *</label><select id="compte_general_tiers" name="compte_general" class="form-select form-select-lg" required><option value="" selected disabled>Sélectionner le type d'abord</option></select></div>
                            <div class="col-md-6"><label class="form-label fw-bold">Numéro de Compte Tiers *</label><input type="text" id="numero_tiers" name="numero_de_tiers" class="form-control form-control-lg bg-light" readonly required></div>
                            <div class="col-md-6"><label class="form-label fw-bold">Nom / Raison Sociale *</label><input type="text" id="intitule_tiers" name="intitule" class="form-control form-control-lg" placeholder="Libellé du compte tiers" required></div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3">
                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-primary" id="btnCreateTiers" onclick="window.createTiersSimple(event)">Enregistrer le Tiers</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const CONTEXT = @json($data);
        const GEN_ACCOUNTS = @json($plansComptables);
        const TIERS_LIST = @json($plansTiers);
        const TREASURY_POST_LIST = @json($comptesTresorerie);
        const SAVE_ROUTE = "{{ route('api.ecriture.storeMultiple') }}";
        let NEXT_SAISIE = "{{ $nextSaisieNumber }}";

        async function fetchNextSaisieNumber() {
            try {
                const res = await fetch("{{ route('api.next-saisie-number') }}");
                const json = await res.json();
                if (json.success) {
                    NEXT_SAISIE = json.numero;
                    const display = document.getElementById('displayNSaisie');
                    if (display) display.innerText = NEXT_SAISIE;
                }
            } catch (e) {
                console.error("Erreur sync n_saisie:", e);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('fileInput');
            const entriesBody = document.getElementById('entriesBody');
            const processingUI = document.getElementById('processingUI');
            const btnSave = document.getElementById('btnSave');
            const imagePreview = document.getElementById('imagePreview');
            const uploadContainer = document.getElementById('uploadContainer');

            // Reset file input and spinner on load to prevent auto-triggering
            fileInput.value = '';
            processingUI.classList.add('d-none');

            // Vérifier si un batch_id est présent pour charger un brouillon
            const urlParams = new URLSearchParams(window.location.search);
            const batchId = urlParams.get('batch_id');
            if (batchId) {
                chargerBrouillon(batchId);
            }

            async function chargerBrouillon(id) {
                processingUI.classList.remove('d-none');
                processingUI.querySelector('h6').innerText = "CHARGEMENT DU BROUILLON...";
                try {
                    const res = await fetch(`/api/brouillons/${id}`);
                    const json = await res.json();
                    if (json.success) {
                        // On simule une structure proche de celle de l'IA pour renderTable
                        const data = {
                            ecriture: json.brouillons.map(b => ({
                                compte: b.plan_comptable ? b.plan_comptable.numero_de_compte : '',
                                debit: b.debit,
                                credit: b.credit,
                                libelle: b.description_operation,
                                type: b.credit > 0 ? 'SOURCE' : 'DESTINATION'
                            })),
                            reference: json.summary.reference,
                            date: json.summary.date,
                            fournisseur: json.summary.description
                        };
                        renderTable(data);
                    } else {
                        alert("Erreur: " + json.message);
                    }
                } catch (e) {
                    alert("Erreur lors du chargement: " + e.message);
                } finally {
                    processingUI.classList.add('d-none');
                    processingUI.querySelector('h6').innerText = "ANALYSE...";
                }
            }

            dropZone.onclick = () => fileInput.click();
            fileInput.onchange = e => {
                if (e.target.files.length > 0) handleUpload(e.target.files[0]);
            };

            // Drag and Drop Handling
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, e => {
                    e.preventDefault();
                    e.stopPropagation();
                }, false);
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => dropZone.style.background = 'rgba(30, 64, 175, 0.05)', false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => dropZone.style.background = '#fbfcfe', false);
            });

            dropZone.addEventListener('drop', e => {
                const dt = e.dataTransfer;
                const file = dt.files[0];
                if (file) handleUpload(file);
            }, false);

            const handleUpload = async (file) => {
                if (!file) return;
                processingUI.classList.remove('d-none');
                try {
                    const compressedBase64 = await compressImage(file);
                    imagePreview.src = compressedBase64;
                    imagePreview.classList.remove('d-none');
                    uploadContainer.classList.add('d-none');
                    
                    const base64Content = compressedBase64.split(',')[1];
                    const prompt = `Tu es un expert-comptable SYSCOHADA expérimenté. Analyse cette facture et retourne UN SEUL JSON valide.

INSTRUCTIONS STRUCTUREES :
1. IDENTIFICATION : Identifie le type de document (facture, reçu, etc.)
2. EXTRACTION : Extrais les informations financières essentielles
3. COMPTABILISATION : Applique les règles SYSCOHADA strictes

RÈGLES COMPTABLES CRITIQUES :
- Si payé en espèces/reçu "OK" : Compte 571 (Caisse) au crédit
- Si facture non payée : Compte 401 (Fournisseur) au crédit  
- Location matériel (bâches, chaises, sono) : Compte 6223 OBLIGATOIRE
- Transport/livraison : Compte 611 OBLIGATOIRE
- Maintenance/entretien : Compte 6242 OBLIGATOIRE
- TVA mentionnée : Compte 445
- Services divers : Compte 611 ou 624 selon nature

FORMAT JSON EXIGÉ (respecte exactement cette structure) :
{
    "hasVAT": true/false,
    "fournisseur": "NOM EXACT FOURNISSEUR",
    "date": "AAAA-MM-JJ",
    "ref": "NUMÉRO PIÈCE",
    "lignes": [
        {
            "compte": "CODE_SYSCOHADA_EXACT",
            "type": "CHARGE|TVA|FOURNISSEUR|CAISSE|BANQUE",
            "libelle": "DESCRIPTION PRÉCISE",
            "debit": MONTANT_NUMÉRIQUE,
            "credit": MONTANT_NUMÉRIQUE
        }
    ]
}

CONTRAINTES :
- Total Débit = Total Crédit (vérification mathématique)
- Codes comptables à 6 chiffres minimum
- Montants en chiffres uniquement (pas de texte)
- Une seule ligne par compte comptable
- Structure JSON valide obligatoire

VALIDATION FINALE :
Vérifie que le JSON est parfaitement formé avant de répondre.`;

                    const payload = { 
                        contents: [{ 
                            parts: [
                                { text: prompt }, 
                                { inlineData: { mimeType: "image/jpeg", data: base64Content } }
                            ] 
                        }]
                    };
                    
                    const makeGeminiRequest = async (payload, retryCount = 0) => {
                        const MAX_RETRIES = 5;
                        const API_URL = '/ia_traitement_standalone.php';
                        
                        // Préparer les données à envoyer
                        const requestData = {
                            prompt: payload.contents[0].parts[0].text,
                            image: payload.contents[0].parts[1]?.inlineData?.data
                        };
                        
                        try {
                            // Créer FormData pour l'upload de fichier
                            const formData = new FormData();
                            
                            // Envoyer aussi le prompt au script standalone
                            if (requestData.prompt) {
                                formData.append('prompt', requestData.prompt);
                            }
                            
                            if (requestData.image) {
                                // Convertir l'image base64 en blob
                                const imageBlob = await fetch(`data:image/jpeg;base64,${requestData.image}`).then(r => r.blob());
                                formData.append('facture', imageBlob, 'facture.jpg');
                            }
                            
                            const response = await fetch(API_URL, { 
                                method: 'POST', 
                                body: formData
                            });

                            const responseData = await response.json();
                            
                            // Gestion améliorée du quota 429
                            if (response.status === 429) {
                                if (retryCount < MAX_RETRIES) {
                                    // Backoff exponentiel avec jitter
                                    const baseWait = Math.pow(2, retryCount) * 2000;
                                    const jitter = Math.random() * 1000;
                                    let waitTime = Math.min(baseWait + jitter, 30000);

                                    console.warn(`Quota atteint (${retryCount+1}/${MAX_RETRIES}). Attente ${Math.round(waitTime/1000)}s...`);
                                    
                                    const h6 = processingUI.querySelector('h6');
                                    
                                    // Compte à rebours visuel
                                    for (let i = Math.ceil(waitTime/1000); i > 0; i--) {
                                        h6.innerText = `QUOTA DÉPASSÉ (${retryCount+1}/${MAX_RETRIES}).\nPATIENTEZ ${i}s...`;
                                        await new Promise(r => setTimeout(r, 1000));
                                    }
                                    
                                    h6.innerText = "ANALYSE EN COURS...";
                                    return makeGeminiRequest(payload, retryCount + 1);
                                } else {
                                    throw new Error("Serveur Google saturé. Réessayez dans quelques minutes.");
                                }
                            }

                            if (!response.ok) {
                                const errorData = responseData;
                                throw new Error(errorData.error || `Erreur HTTP ${response.status}`);
                            }

                            // Notre API retourne directement les données, pas le format Gemini
                            return { candidates: [{ content: { parts: [{ text: JSON.stringify(responseData) }] } }] };
                        } catch (e) {
                            if (e.message.includes('quota') || e.message.includes('429')) {
                                throw e;
                            }
                            throw new Error(`Erreur de communication: ${e.message}`);
                        }
                    };

                    const rData = await makeGeminiRequest(payload);
                    
                    if (rData.error) throw new Error(rData.error.message || "Erreur API");
                    if (!rData.candidates?.[0]?.content?.parts?.[0]?.text) throw new Error("Réponse vide");

                    let textResponse = rData.candidates[0].content.parts[0].text;
                    
                    // Nettoyer la réponse
                    textResponse = textResponse.replace(/```json\s*/g, '').replace(/```\s*$/g, '').trim();
                    
                    // Essayer plusieurs stratégies d'extraction
                    let result = null;
                    const jsonStrategies = [
                        // Stratégie 1: Extraction du premier JSON valide
                        () => {
                            const jsonRegex = /\{[\s\S]*?\}/;
                            const match = textResponse.match(jsonRegex);
                            return match ? JSON.parse(match[0]) : null;
                        },
                        // Stratégie 2: Extraction du JSON le plus complet
                        () => {
                            const jsonRegex = /\{[\s\S]*\}/;
                            const matches = textResponse.match(jsonRegex);
                            if (!matches) return null;
                            
                            // Prendre le JSON le plus long (probablement le plus complet)
                            const jsonStr = matches.reduce((longest, current) => 
                                current.length > longest.length ? current : longest
                            );
                            return JSON.parse(jsonStr);
                        },
                        // Stratégie 3: Parser directement si tout est du JSON
                        () => {
                            if (textResponse.startsWith('{') && textResponse.endsWith('}')) {
                                return JSON.parse(textResponse);
                            }
                            return null;
                        }
                    ];
                    
                    // Essayer chaque stratégie
                    for (const strategy of jsonStrategies) {
                        try {
                            result = strategy();
                            if (result && (result.ecriture && Array.isArray(result.ecriture) || result.lignes && Array.isArray(result.lignes))) {
                                break;
                            }
                        } catch (e) {
                            continue;
                        }
                    }
                    
                    if (!result) {
                        throw new Error("Format JSON invalide. L'IA n'a pas pu structurer les données correctement.");
                    }
                    
                    // GESTION DES ERREURS RETOURNÉES PAR LE SCRIPT (comme Quota dépassé)
                    if (result.error) {
                        let fullMsg = result.error;
                        if (result.message) fullMsg += "\n" + result.message;
                        if (result.details) fullMsg += "\n" + result.details;
                        if (result.api_message) fullMsg += "\nGoogle API: " + result.api_message;
                        if (result.curl_error) fullMsg += "\ncURL: " + result.curl_error;
                        throw new Error(fullMsg);
                    }
                    
                    // Validation des données critiques
                    const ecritures = result.ecriture || result.ecritures || result.lignes || result.lines;
                    if (!ecritures || !Array.isArray(ecritures) || ecritures.length === 0) {
                        console.error('Réponse IA sans lignes:', result);
                        throw new Error("Aucune ligne d'écriture trouvée dans la réponse. (Vérifiez la qualité de l'image)");
                    }
                    
                    // Validation de l'équilibre Débit/Crédit
                    const totalDebit = ecritures.reduce((sum, l) => sum + (l.debit || 0), 0);
                    const totalCredit = ecritures.reduce((sum, l) => sum + (l.credit || 0), 0);
                    if (Math.abs(totalDebit - totalCredit) > 1) {
                        console.warn('Déséquilibre détecté:', { debit: totalDebit, credit: totalCredit });
                    }
                    
                    // Manage VAT button state
                    const btnVAT = document.getElementById('btnApplyVAT');
                    if (result.hasVAT) {
                        // La facture contient déjà la TVA → cacher et griser le bouton
                        btnVAT.classList.add('d-none');
                        btnVAT.disabled = true;
                    } else {
                        // La facture ne contient pas de TVA → rendre le bouton visible et actif
                        btnVAT.classList.remove('d-none');
                        btnVAT.disabled = false;
                        btnVAT.innerHTML = '<i class="bx bx-plus me-1"></i>APPLIQUER TVA 18%';
                    }

                    renderTable(result);
                } catch (e) { 
                    alert("Erreur: " + e.message); 
                    resetUI();
                } finally { 
                    processingUI.classList.add('d-none'); 
                }
            };

            const compressImage = file => new Promise(res => {
                const reader = new FileReader(); reader.onload = e => {
                    const img = new Image(); img.src = e.target.result; img.onload = () => {
                        const canvas = document.createElement('canvas'); const MAX = 1200; let w = img.width, h = img.height;
                        if (w > MAX) { h *= MAX/w; w = MAX; } canvas.width = w; canvas.height = h; canvas.getContext('2d').drawImage(img, 0,0, w, h);
                        res(canvas.toDataURL('image/jpeg', 0.8));
                    };
                }; reader.readAsDataURL(file);
            });
 
            const findBestAccount = (code, type) => {
                // Recherche exacte du code à 8 chiffres
                const exactMatch = GEN_ACCOUNTS.find(a => a.numero_de_compte === code);
                if (exactMatch) return exactMatch.id;
                
                // Recherche par préfixe (4 premiers chiffres)
                const prefix = code.substring(0, 4);
                const prefixMatch = GEN_ACCOUNTS.find(a => a.numero_de_compte.startsWith(prefix));
                if (prefixMatch) return prefixMatch.id;
                
                // Recherche par classe (2 premiers chiffres)
                const classPrefix = code.substring(0, 2);
                const classMatch = GEN_ACCOUNTS.find(a => a.numero_de_compte.startsWith(classPrefix));
                if (classMatch) return classMatch.id;
                const fallbackMap = {
                    'TVA': ['445'],
                    'FOURNISSEUR': ['401'],
                    'CAISSE': ['571', '531', '521'],
                    'BANQUE': ['521'],
                    'CHARGE': ['6'],
                    'PRODUIT': ['7'],
                    'IMMOBILISATION': ['2'],
                    'TRESORERIE': ['5']
                };
                
                if (fallbackMap[type]) {
                    for (const prefix of fallbackMap[type]) {
                        const fallback = GEN_ACCOUNTS.find(a => a.numero_de_compte.startsWith(prefix));
                        if (fallback) return fallback.id;
                    }
                }
                
                // 4. Dernier recours : préfixe court
                if (code.length >= 2) {
                    const shortCode = code.substring(0, 2);
                    const shortMatch = GEN_ACCOUNTS.find(a => a.numero_de_compte.startsWith(shortCode));
                    if (shortMatch) return shortMatch.id;
                }
                
                return null;
            };
 
            const findTreasuryPost = (accountId) => {
                if (!accountId) return { id: '', text: '' };
                // On cherche un poste de trésorerie qui utilise ce compte général
                const post = TREASURY_POST_LIST.find(p => p.plan_comptable_id == accountId);
                if (post) return { id: post.id, text: post.name };
                return { id: '', text: '' };
            };

            const renderTable = (data) => {
                entriesBody.innerHTML = '';
                const ecritures = data.ecriture || data.lignes;
                
                // Get Poste Tresorerie options for the select
                const posteTresOptions = Array.from(document.getElementById('poste_tresorerie')?.options || [])
                    .filter(opt => opt.value)
                    .map(opt => `<option value="${opt.value}">${opt.text}</option>`)
                    .join('');

                ecritures.forEach(l => {
                    const tr = document.createElement('tr');
                    const matchedAccId = findBestAccount(l.compte, l.type);
                    const accCode = l.compte ? l.compte.toString() : '';
                    const isTreasury = accCode.startsWith('5');
                    
                    if (l.type === 'FOURNISSEUR' || (l.compte && l.compte.toString().startsWith('40'))) {
                        const supplierName = (data.tiers || data.fournisseur || "").toUpperCase();
                        const t = TIERS_LIST.find(t => supplierName.includes(t.intitule.toUpperCase()) || t.intitule.toUpperCase().includes(supplierName));
                        if (t) matchedTierId = t.id;
                    }

                    const matchedPoste = isTreasury ? findTreasuryPost(matchedAccId) : { id: '', text: '' };

                    tr.innerHTML = `
                        <td><select class="form-select select2 row-acc"><option value="">Choisir...</option>${GEN_ACCOUNTS.map(a => `<option value="${a.id}" ${a.id == matchedAccId ? 'selected' : ''}>${a.numero_de_compte} - ${a.intitule}</option>`).join('')}</select></td>
                        <td><div class="d-flex gap-1"><select class="form-select select2 row-tier"><option value="">Néant</option>${TIERS_LIST.map(t => `<option value="${t.id}" ${t.id == matchedTierId ? 'selected' : ''}>${t.numero_de_tiers} - ${t.intitule}</option>`).join('')}</select><button type="button" class="btn btn-sm btn-outline-primary rounded-circle" data-bs-toggle="modal" data-bs-target="#createTiersModal"><i class="bx bx-plus"></i></button></div></td>
                        <td><input type="text" class="form-control form-control-sm row-lib" value="${l.intitule || l.libelle || ''}"><div class="small text-muted mt-1 px-1">Pièce: ${data.reference || data.ref || ''} du ${data.date || ''}</div><input type="hidden" class="row-date" value="${data.date || ''}"><input type="hidden" class="row-ref" value="${data.reference || data.ref || ''}"></td>
                        <td><input type="number" class="form-control text-end row-debit" value="${l.debit || 0}"></td>
                        <td><input type="number" class="form-control text-end row-credit" value="${l.credit || 0}"></td>
                        <td>
                            <select class="form-select select2 row-poste-treso" ${isTreasury ? '' : 'disabled'}>
                                <option value="">Néant</option>
                                ${TREASURY_POST_LIST.map(p => `<option value="${p.id}" ${p.id == matchedPoste.id ? 'selected' : ''}>${p.name}</option>`).join('')}
                            </select>
                        </td>
                        <td class="text-center"><button class="btn btn-sm btn-icon text-danger" onclick="this.closest('tr').remove(); window.updateTotals();"><i class="bx bx-trash"></i></button></td>
                    `;
                    entriesBody.appendChild(tr);
                    $(tr).find('.select2').select2({ theme: 'bootstrap4', width: '100%' }).on('change', window.updateTotals);
                    
                    // Specific logic for row-acc change to toggle row-poste-treso
                    $(tr).find('.row-acc').on('change', function() {
                        const code = $(this).find('option:selected').text().split(' ')[0];
                        const posteSelect = $(tr).find('.row-poste-treso');
                        if (code.startsWith('5')) {
                            posteSelect.prop('disabled', false);
                        } else {
                            posteSelect.val('').trigger('change').prop('disabled', true);
                        }
                    });

                    $(tr).find('input').on('input', window.updateTotals);
                });
                window.updateTotals();
            };

            window.applyVAT18 = () => {
                const rows = Array.from(document.querySelectorAll('#entriesBody tr'));
                let totalCharge = 0;
                let mainLine = null; // Line to update credit (401 or 571)
                let date = null, ref = null;

                rows.forEach(tr => {
                    const accSelect = tr.querySelector('.row-acc');
                    const accCode = accSelect.options[accSelect.selectedIndex]?.text.split(' ')[0] || "";
                    const debit = parseFloat(tr.querySelector('.row-debit').value) || 0;
                    const credit = parseFloat(tr.querySelector('.row-credit').value) || 0;

                    if (accCode.startsWith('6')) {
                        totalCharge += debit;
                    }
                    if (accCode.startsWith('40') || accCode.startsWith('57') || accCode.startsWith('41')) {
                        mainLine = tr;
                    }
                    if (!date) date = tr.querySelector('.row-date').value;
                    if (!ref) ref = tr.querySelector('.row-ref').value;
                });

                if (totalCharge > 0 && mainLine) {
                    const vataAmount = Math.round(totalCharge * 0.18);
                    
                    // Add VAT row
                    const tr = document.createElement('tr');
                    const vataAcc = GEN_ACCOUNTS.find(a => a.numero_de_compte.startsWith('445')) || { id: null, numero_de_compte: '445' };
                    
                    tr.innerHTML = `
                        <td><select class="form-select select2 row-acc"><option value="${vataAcc.id}" selected>${vataAcc.numero_de_compte} - TVA RÉCUPÉRABLE</option></select></td>
                        <td><div class="d-flex gap-1"><select class="form-select select2 row-tier"><option value="" selected>Néant</option></select></div></td>
                        <td><input type="text" class="form-control form-control-sm row-lib" value="TVA 18%"><div class="small text-muted mt-1 px-1">Pièce: ${ref} du ${date}</div><input type="hidden" class="row-date" value="${date}"><input type="hidden" class="row-ref" value="${ref}"></td>
                        <td><input type="number" class="form-control text-end row-debit" value="${vataAmount}"></td>
                        <td><input type="number" class="form-control text-end row-credit" value="0"></td>
                        <td class="text-center"><button class="btn btn-sm btn-icon text-danger" onclick="this.closest('tr').remove(); window.updateTotals();"><i class="bx bx-trash"></i></button></td>
                    `;
                    
                    // Insert VAT before main credit line
                    mainLine.parentNode.insertBefore(tr, mainLine);
                    $(tr).find('.select2').select2({ theme: 'bootstrap4', width: '100%' }).on('change', window.updateTotals);

                    // Update main credit line amount
                    const currentCredit = parseFloat(mainLine.querySelector('.row-credit').value) || 0;
                    mainLine.querySelector('.row-credit').value = currentCredit + vataAmount;

                    // Disable button to prevent multiple applications
                    const btnVAT = document.getElementById('btnApplyVAT');
                    btnVAT.disabled = true;
                    btnVAT.innerHTML = '<i class="bx bx-check me-1"></i>TVA APPLIQUÉE';
                    
                    window.updateTotals();
                }
            };

            window.updateTotals = () => {
                let d = 0, c = 0, hasNull = false, hasRows = false;
                const btnSave = document.getElementById('btnSave');
                const btnSaveDraft = document.getElementById('btnSaveDraft');
                
                document.querySelectorAll('#entriesBody tr').forEach(tr => {
                    const rowAcc = tr.querySelector('.row-acc'); if (!rowAcc) return;
                    hasRows = true;
                    const dVal = parseFloat(tr.querySelector('.row-debit').value) || 0;
                    const cVal = parseFloat(tr.querySelector('.row-credit').value) || 0;
                    d += dVal; c += cVal;
                    if (!rowAcc.value) hasNull = true;
                });
                
                document.getElementById('summaryDebit').innerText = d.toLocaleString() + ' FCFA';
                document.getElementById('summaryCredit').innerText = c.toLocaleString() + ' FCFA';
                
                const balanced = Math.abs(d - c) < 1;
                document.getElementById('statusIndicator').innerHTML = balanced ? '<i class="bx bx-check-circle text-success fs-3 animate__animated animate__bounceIn"></i>' : '<i class="bx bx-error-circle text-danger fs-3"></i>';
                
                btnSave.disabled = !balanced || hasNull || !hasRows;
                btnSaveDraft.disabled = !hasRows;
            };

            const resetUI = () => {
                imagePreview.src = ''; imagePreview.classList.add('d-none');
                uploadContainer.classList.remove('d-none');
                entriesBody.innerHTML = '<tr><td colspan="6" class="text-center py-5 text-muted">En attente de document...</td></tr>';
                document.getElementById('btnApplyVAT').classList.add('d-none');
                fetchNextSaisieNumber(); // Synchroniser le numéro
                window.updateTotals();
            };
            document.getElementById('btnReset').onclick = resetUI;

            window.enregistrerEcritures = async () => {
                const btnSave = document.getElementById('btnSave');
                const formData = new FormData();
                const file = document.getElementById('fileInput').files[0];
                const rows = Array.from(document.querySelectorAll('#entriesBody tr'));
                
                if (rows.length === 0) return;

                if (file) {
                    formData.append('piece_justificatif', file);
                }

                const ecritures = rows.map((tr) => {
                    return {
                        date: tr.querySelector('.row-date').value,
                        n_saisie: NEXT_SAISIE,
                        description_operation: tr.querySelector('.row-lib').value,
                        reference_piece: tr.querySelector('.row-ref').value,
                        plan_comptable_id: tr.querySelector('.row-acc').value,
                        plan_tiers_id: tr.querySelector('.row-tier').value || null,
                        poste_tresorerie_id: tr.querySelector('.row-poste-treso').value || null,
                        debit: tr.querySelector('.row-debit').value,
                        credit: tr.querySelector('.row-credit').value,
                        exercices_comptables_id: CONTEXT.id_exercice,
                        code_journal_id: CONTEXT.id_code
                    };
                });

                formData.append('ecritures', JSON.stringify(ecritures));

                try {
                    btnSave.disabled = true; 
                    btnSave.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>ENREGISTREMENT...';
                    
                    const res = await fetch(SAVE_ROUTE, { 
                        method: 'POST', 
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: formData 
                    });
                    
                    const json = await res.json();
                    if (json.success) { 
                        window.location.href = "{{ route('accounting_entry_list') }}?success=1"; 
                    } else {
                        throw new Error(json.error || json.message || "Erreur inconnue lors de l'enregistrement");
                    }
                } catch (e) { 
                    alert("Erreur: " + e.message); 
                    console.error('Save error:', e);
                    btnSave.disabled = false; 
                    btnSave.innerText = "VALIDER & ENREGISTRER L'ÉCRITURE"; 
                }
            };

            btnSave.onclick = window.enregistrerEcritures;

            window.sauvegarderEnBrouillon = async () => {
                const btnDraft = document.getElementById('btnSaveDraft');
                const formData = new FormData();
                const file = document.getElementById('fileInput').files[0];
                const rows = Array.from(document.querySelectorAll('#entriesBody tr'));
                
                if (rows.length === 0) return;

                if (file) {
                    formData.append('piece_justificatif', file);
                }

                const ecritures = rows.map((tr) => {
                    return {
                        date: tr.querySelector('.row-date').value,
                        description_operation: tr.querySelector('.row-lib').value,
                        reference_piece: tr.querySelector('.row-ref').value,
                        plan_comptable_id: tr.querySelector('.row-acc').value,
                        plan_tiers_id: tr.querySelector('.row-tier').value || null,
                        poste_tresorerie_id: tr.querySelector('.row-poste-treso').value || null,
                        debit: tr.querySelector('.row-debit').value,
                        credit: tr.querySelector('.row-credit').value,
                        exercices_comptables_id: CONTEXT.id_exercice,
                        code_journal_id: CONTEXT.id_code,
                        source: 'scan'
                    };
                });

                formData.append('ecritures', JSON.stringify(ecritures));

                try {
                    btnDraft.disabled = true;
                    btnDraft.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>EN COURS...';
                    
                    const res = await fetch("{{ route('api.brouillons.store') }}", {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: formData
                    });
                    
                    const json = await res.json();
                    if (json.success) {
                        window.location.href = "{{ route('brouillons.index') }}?success=Brouillon enregistré avec succès";
                    } else {
                        throw new Error(json.error || json.message || "Erreur lors de l'enregistrement du brouillon");
                    }
                } catch (e) {
                    alert("Erreur: " + e.message);
                    btnDraft.disabled = false;
                    btnDraft.innerText = "ENREGISTRER EN BROUILLON";
                }
            };

            // Modal Tiers Logic
            document.getElementById('type_tiers').onchange = (e) => {
                const type = e.target.value;
                const prefixMap = {'Fournisseur': '40', 'Client': '41', 'Personnel': '42', 'CNPS': '43', 'Impots': '44', 'Associé': '45', 'Divers Tiers': '47'};
                const prefix = prefixMap[type];
                const select = document.getElementById('compte_general_tiers');
                select.innerHTML = '<option value="" disabled selected>Choisir un compte...</option>';
                GEN_ACCOUNTS.forEach(a => { if (a.numero_de_compte.startsWith(prefix)) select.innerHTML += `<option value="${a.id}">${a.numero_de_compte} - ${a.intitule}</option>`; });
                if (prefix) fetch("/plan_tiers/" + prefix).then(r => r.json()).then(d => document.getElementById('numero_tiers').value = d.numero);
            };

            window.createTiersSimple = (e) => {
                e.preventDefault();
                const btn = document.getElementById('btnCreateTiers');
                const form = document.getElementById('createTiersForm');
                if (!form.checkValidity()) { form.reportValidity(); return; }
                
                const data = { type_de_tiers: document.getElementById('type_tiers').value, compte_general: document.getElementById('compte_general_tiers').value, intitule: document.getElementById('intitule_tiers').value, numero_de_tiers: document.getElementById('numero_tiers').value };
                
                btn.disabled = true; btn.innerText = "Création...";
                fetch('{{ route("plan_tiers.store") }}', { method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'}, body: JSON.stringify(data) }).then(r => r.json()).then(res => {
                    if (res.success) {
                        const option = new Option(`${res.numero_de_tiers} - ${res.intitule}`, res.id, true, true);
                        document.querySelectorAll('.row-tier').forEach(sel => $(sel).append(option).trigger('change'));
                        bootstrap.Modal.getInstance(document.getElementById('createTiersModal')).hide();
                        form.reset();
                    } else alert("Erreur: " + res.error);
                }).finally(() => { btn.disabled = false; btn.innerText = "Enregistrer le Tiers"; });
            };
        });
    </script>
</body>
</html>
