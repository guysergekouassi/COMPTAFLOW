<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')

<style>
    .img-thumbnail-scan { height: 180px; object-fit: contain; cursor: pointer; transition: transform 0.2s; }
    .img-thumbnail-scan:hover { transform: scale(1.05); }
    .document-card { border-left: 4px solid #f8f9fa; }
    .document-card.border-danger { border-left-color: #dc3545; }
    .extra-small { font-size: 0.65rem; }
    .btn-indigo { background-color: #6610f2; border-color: #6610f2; }
    .btn-indigo:hover { background-color: #520dc2; border-color: #4e0caf; }
    .btn-xs { padding: 0.15rem 0.35rem; font-size: 0.75rem; line-height: 1; border-radius: 0.2rem; }
    .cursor-pointer { cursor: pointer; }
    .document-card {
        border: 1px solid #eef0f2;
        transition: transform 0.2s;
    }
    .document-card:hover {
        border-color: #ccd3d9;
    }
    .queue-item {
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 8px;
        background: #fff;
        border: 1px solid #f0f2f4;
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .queue-item:hover {
        background-color: #f8fafc;
        border-color: #cbd5e1;
        transform: translateX(4px);
    }
    .queue-item.processing {
        border-left: 4px solid #3b82f6;
        background: #f0f7ff;
    }
    .queue-item.completed {
        border-left: 4px solid #10b981;
    }
    .queue-item.error {
        border-left: 4px solid #ef4444;
        background: #fffafa;
    }
    .status-badge {
        font-size: 0.7rem;
        padding: 2px 8px;
        border-radius: 12px;
    }
    /* Select2 adjustments for the custom layout */
    .select2-container--bootstrap4 .select2-selection {
        border-radius: 8px !important;
        border: 1px solid #e2e8f0 !important;
        height: calc(1.5em + .5rem + 2px) !important;
        display: flex !important;
        align-items: center !important;
    }
    .results-scroll-container {
        max-height: 85vh;
        overflow-y: auto;
        padding: 15px;
        scroll-behavior: smooth;
        scrollbar-width: thin;
    }
    .results-scroll-container::-webkit-scrollbar { width: 6px; }
    .results-scroll-container::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    .sticky-summary {
        position: sticky;
        top: 0;
        z-index: 1000;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        border-bottom: 1px solid #eef0f2;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Centre de Scan Groupé'])

                <div class="content-wrapper">
                    <div class="container-fluid flex-grow-1 container-p-y">
                        <!-- Header -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-flex align-items-center justify-content-between bg-white p-4 rounded-3 shadow-sm">
                                    <div>
                                        <h4 class="mb-1 fw-bold text-dark"><i class="fa-solid fa-copy me-2 text-primary"></i>Centre de Scan Groupé</h4>
                                        <p class="text-muted mb-0">Importez plusieurs factures (Photos ou PDF) et laissez l'IA générer les écritures pour vous.</p>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-outline-secondary" id="btnReset">
                                            <i class="bx bx-refresh me-1"></i> Réinitialiser
                                        </button>
                                        <div class="btn-group d-none" id="btnSaveGroup">
                                            <button type="button" class="btn btn-primary" id="btnSaveSelected">
                                                <i class="bx bx-save me-1"></i> Enregistrer
                                            </button>
                                            <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                                <span class="visually-hidden">Toggle Dropdown</span>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="javascript:void(0);" id="btnSaveAllDocs"><i class="bx bx-list-check me-2"></i>Tout Enregistrer</a></li>
                                                <li><a class="dropdown-item" href="javascript:void(0);" id="btnSaveOnlySelected"><i class="bx bx-check-square me-2"></i>Enregistrer la sélection</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Sidebar: Upload & Queue -->
                            <div class="col-lg-3">
                                <div class="card border-0 shadow-sm mb-4">
                                    <div class="card-header bg-white py-3">
                                        <h5 class="card-title mb-0 fw-bold">1. Importation</h5>
                                    </div>
                                    <div class="card-body">
                                        <!-- Dropzone -->
                                        <label id="dropZone" for="fileInput" class="border-2 border-dashed rounded-3 p-4 text-center mb-3 bg-light d-block" style="cursor: pointer; transition: all 0.3s; position: relative;">
                                            <input type="file" id="fileInput" multiple accept="image/*,application/pdf" style="opacity: 0; position: absolute; top: 0; left: 0; width: 100%; height: 100%; cursor: pointer; z-index: 10;">
                                            <i class="bx bx-cloud-upload fs-1 text-primary mb-2"></i>
                                            <p class="mb-1 fw-bold">Cliquez ou glissez les fichiers</p>
                                            <p class="text-muted small">Images (JPG, PNG) ou PDF (100 max)</p>
                                        </label>

                                        <!-- Queue Progress -->
                                        <div id="queueContainer" class="d-none">
                                            <h6 class="fw-bold mb-3 d-flex justify-content-between">
                                                File d'attente 
                                                <span class="badge bg-primary rounded-pill" id="queueCount">0</span>
                                            </h6>
                                            <div class="queue-list" id="queueList" style="max-height: 400px; overflow-y: auto;">
                                                <!-- Files will be listed here -->
                                            </div>
                                            <div class="mt-3">
                                                <button id="btnStartProcessing" class="btn btn-primary w-100">
                                                    <i class="bx bx-play me-1"></i> Lancer le traitement
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 20px;">
                                    <div class="card-body p-4" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
                                        <h6 class="fw-bold mb-3 text-primary d-flex align-items-center">
                                            <i class="bx bxs-magic-wand me-2"></i>Instructions de l'IA
                                        </h6>
                                        <ul class="small text-muted ps-3 mb-0" style="line-height: 1.6;">
                                            <li class="mb-2">L'IA traitera les documents <strong>un par un</strong> pour assurer une précision maximale.</li>
                                            <li class="mb-2">Le <strong>Journal</strong> peut être modifié individuellement pour chaque facture.</li>
                                            <li class="mb-2">Les <strong>N° de Saisie</strong> sont séquentiels et affectés à la validation.</li>
                                            <li>Vérifiez l'<strong>équilibre (Débit/Crédit)</strong> avant l'enregistrement final.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Main Area: Document Cards -->
                            <div class="col-lg-9">
                                <!-- Sticky Summary Bar -->
                                <div id="summaryBar" class="sticky-summary p-3 mb-3 rounded-3 shadow-sm d-none">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex gap-4">
                                            <div class="text-center">
                                                <div class="text-muted small mb-1">Total</div>
                                                <div class="h5 mb-0 fw-bold" id="statTotal">0</div>
                                            </div>
                                            <div class="text-center">
                                                <div class="text-success small mb-1">Succès</div>
                                                <div class="h5 mb-0 fw-bold text-success" id="statSuccess">0</div>
                                            </div>
                                            <div class="text-center">
                                                <div class="text-danger small mb-1">Rejetés</div>
                                                <div class="h5 mb-0 fw-bold text-danger" id="statError">0</div>
                                            </div>
                                        </div>
                                        <div class="text-muted small">
                                            <i class="bx bx-info-circle me-1"></i> Cliquez sur un rejet dans la file pour voir le détail
                                        </div>
                                    </div>
                                </div>

                                <div id="emptyState" class="text-center py-5 bg-white rounded-3 shadow-sm border border-dashed">
                                    <div class="mb-4">
                                        <i class="bx bx-file-blank fs-1 text-muted opacity-25" style="font-size: 5rem !important;"></i>
                                    </div>
                                    <h5 class="text-dark fw-bold">Aucun document importé</h5>
                                    <p class="text-muted">Commencez par ajouter des fichiers dans la zone d'importation à gauche.</p>
                                </div>

                                <div id="resultsWrapper" class="results-scroll-container d-none">
                                    <div id="documentsContainer" class="d-flex flex-column gap-4 pb-5">
                                        <!-- Document Cards will be appended here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @include('components.footer')
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    @include('accounting.partials.modal_create_tiers')
    @include('accounting.partials.modal_ventilation_analytique')

    <!-- Scripts -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        console.log("Bulk Scan Script: Initializing...");
        let GEN_ACCOUNTS = [];
        let TIERS_LIST = [];
        let JOURNALS = [];
        let EXERCICE_ACTIF = null;
        let TREASURY_POST_LIST = [];
        let SAVE_ROUTE = "";
        let INITIAL_NS = "";

        try {
            GEN_ACCOUNTS = @json($plansComptables);
            TIERS_LIST = @json($plansTiers);
            JOURNALS = @json($codeJournaux);
            EXERCICE_ACTIF = @json($exerciceActif);
            TREASURY_POST_LIST = @json($comptesTresorerie);
            SAVE_ROUTE = "{{ route('ecritures-comptables.store-multiple') }}";
            INITIAL_NS = "{{ $nextSaisieNumber }}";
        } catch (e) {
            console.error("Bulk Scan Script: Error parsing Blade variables", e);
        }

        let filesQueue = [];
        let processedDocs = [];
        const TREASURY_POSTS_GROUPED = {};

        function initTreasuryGrouping() {
            if (!Array.isArray(TREASURY_POST_LIST)) return;
            TREASURY_POST_LIST.forEach(p => {
                const catName = p.category ? p.category.name : 'Autres';
                if (!TREASURY_POSTS_GROUPED[catName]) TREASURY_POSTS_GROUPED[catName] = [];
                TREASURY_POSTS_GROUPED[catName].push(p);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            console.log("Bulk Scan Script: DOM Loaded.");
            initTreasuryGrouping();

            const dropZone = document.getElementById('dropZone');
            const fileInput = document.getElementById('fileInput');
            const queueContainer = document.getElementById('queueContainer');
            const queueList = document.getElementById('queueList');
            const documentsContainer = document.getElementById('documentsContainer');
            const emptyState = document.getElementById('emptyState');
            const btnStartProcessing = document.getElementById('btnStartProcessing');
            const btnSaveGroup = document.getElementById('btnSaveGroup');

            if (!fileInput) {
                console.error("Bulk Scan Script: fileInput not found!");
                return;
            }

            // Handle Dropzone Events
            if (dropZone) {
                dropZone.ondragover = e => { e.preventDefault(); dropZone.classList.add('bg-primary', 'bg-opacity-10'); };
                dropZone.ondragleave = () => dropZone.classList.remove('bg-primary', 'bg-opacity-10');
                dropZone.ondrop = e => {
                    e.preventDefault();
                    dropZone.classList.remove('bg-primary', 'bg-opacity-10');
                    window.handleFiles(e.dataTransfer.files);
                };
            }

            fileInput.onchange = e => {
                console.log("Bulk Scan Script: File input changed.");
                window.handleFiles(e.target.files);
            };

            window.handleFiles = function(files) {
                console.log("Bulk Scan Script: Handling files...", files.length);
                const newFiles = Array.from(files);
                if (newFiles.length === 0) return;

                const startIdx = processedDocs.length + filesQueue.filter(i => i.status !== 'success').length + 1;
                newFiles.forEach((file, i) => {
                    const id = 'doc_' + Math.random().toString(36).substr(2, 9);
                    const item = { id, file, status: 'pending', data: null };
                    filesQueue.push(item);
                    window.addQueueItem(id, file.name);
                    window.createDocumentPlaceholder(item, startIdx + i);
                });

                if (queueContainer) queueContainer.classList.remove('d-none');
                const rw = document.getElementById('resultsWrapper');
                if (rw) rw.classList.remove('d-none');
                if (emptyState) emptyState.classList.add('d-none');
                window.updateQueueCount();
            };

            window.addQueueItem = function(id, name) {
                const div = document.createElement('div');
                div.className = 'queue-item cursor-pointer';
                div.id = 'queue_' + id;
                div.onclick = () => {
                    const card = document.getElementById('card_' + id);
                    if (card) card.scrollIntoView({ behavior: 'smooth', block: 'center' });
                };
                div.innerHTML = `
                    <div class="flex-shrink-0 status-icon"><i class="bx bxs-file-blank fs-4 text-secondary"></i></div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="text-truncate small fw-bold">${name}</div>
                        <div class="status-wrapper min-width-0">
                            <div class="status-text text-muted" style="font-size: 0.65rem;">En attente</div>
                        </div>
                    </div>
                `;
                if (queueList) queueList.appendChild(div);
            };

            window.updateQueueCount = function() {
                const countEl = document.getElementById('queueCount');
                if (countEl) countEl.innerText = filesQueue.length;
            };

            if (btnStartProcessing) {
                btnStartProcessing.onclick = async () => {
                    btnStartProcessing.disabled = true;
                    btnStartProcessing.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>En cours...';

                    const sb = document.getElementById('summaryBar');
                    if (sb) sb.classList.remove('d-none');
                    const st = document.getElementById('statTotal');
                    if (st) st.innerText = filesQueue.length;

                    const CONCURRENCY_LIMIT = 2;
                    const pendingItems = filesQueue.filter(item => item.status === 'pending');
                    
                    let index = 0;
                    const workers = [];

                    const runWorker = async () => {
                        while (index < pendingItems.length) {
                            const item = pendingItems[index++];
                            await window.processDocument(item);
                        }
                    };

                    for (let i = 0; i < Math.min(CONCURRENCY_LIMIT, pendingItems.length); i++) {
                        workers.push(runWorker());
                    }

                    await Promise.all(workers);

                    btnStartProcessing.classList.add('d-none');
                    if (btnSaveGroup) btnSaveGroup.classList.remove('d-none');
                };
            }

            window.processDocument = async function(item) {
                const queueEl = document.getElementById('queue_' + item.id);
                if (!queueEl) return;
                queueEl.classList.add('processing');
                queueEl.querySelector('.status-text').innerText = 'Traitement IA...';
                queueEl.querySelector('.status-icon').innerHTML = '<span class="spinner-border spinner-border-sm text-primary"></span>';

                try {
                    const formData = new FormData();
                    formData.append('facture', item.file);
                    
                    const response = await fetch("{{ route('ia.traiter') }}", {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: formData
                    });

                    if (!response.ok) throw new Error("Erreur serveur: " + response.status);

                    const data = await response.json();
                    if (data.success) {
                        item.data = data.data;
                        item.status = 'success';
                        queueEl.classList.replace('processing', 'success');
                        queueEl.querySelector('.status-text').innerText = 'Réussi';
                        queueEl.querySelector('.status-icon').innerHTML = '<i class="bx bxs-check-circle text-success fs-4"></i>';
                        window.updateStats(true);
                        window.updateDocumentCard(item);
                    } else {
                        throw new Error(data.error || 'Réponse invalide de l\'IA');
                    }
                } catch (error) {
                    console.error("Process Error:", error);
                    item.status = 'error';
                    queueEl.classList.replace('processing', 'error');
                    queueEl.querySelector('.status-text').innerText = 'Échec';
                    queueEl.querySelector('.status-icon').innerHTML = '<i class="bx bxs-error-circle text-danger fs-4"></i>';
                    window.updateStats(false);
                    window.updateDocumentCard(item);
                }
            };

            window.updateStats = function(isSuccess) {
                if (isSuccess) {
                    const el = document.getElementById('statSuccess');
                    if (el) el.innerText = parseInt(el.innerText || '0') + 1;
                } else {
                    const el = document.getElementById('statError');
                    if (el) el.innerText = parseInt(el.innerText || '0') + 1;
                }
            };

            window.createDocumentPlaceholder = function(item, docIndex) {
                const docId = item.id;
                const card = document.createElement('div');
                card.className = 'card border-0 shadow-sm document-card mb-4 opacity-75';
                card.id = 'card_' + docId;
                
                card.innerHTML = `
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                        <div class="d-flex align-items-center gap-3">
                            <span class="badge bg-secondary text-white border fw-bold">${docIndex}</span>
                            <h6 class="mb-0 fw-bold text-muted small">${item.file.name}</h6>
                            <span class="badge bg-info text-white px-2 placeholder-glow"><span class="placeholder col-6"></span> Analyse en cours...</span>
                        </div>
                    </div>
                    <div class="card-body py-5 text-center text-muted">
                        <div class="spinner-border text-primary mb-2" role="status"></div>
                        <p class="mb-0 small">L'IA analyse le document, veuillez patienter...</p>
                    </div>
                `;
                if (documentsContainer) documentsContainer.appendChild(card);
            };

            window.updateDocumentCard = function(item) {
                const card = document.getElementById('card_' + item.id);
                if (!card) return;
                
                const data = item.data || {};
                const docId = item.id;
                const previewUrl = URL.createObjectURL(item.file);
                card.classList.remove('opacity-75');
                
                if (item.status === 'error') {
                    card.innerHTML = `
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                             <div class="d-flex align-items-center gap-3">
                                <h6 class="mb-0 fw-bold text-muted small">${item.file.name}</h6>
                                <span class="badge bg-danger text-white px-2">ÉCHEC DU TRAITEMENT</span>
                            </div>
                            <button class="btn btn-sm btn-light text-danger" onclick="window.removeDocument('${docId}')"><i class="bx bx-trash"></i></button>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-danger mb-0 small">
                                <strong>ERREUR :</strong> Le serveur n'a pas pu traiter ce document. Vérifiez le format ou réessayez.
                            </div>
                        </div>
                    `;
                    return;
                }

                // Detect best journal
                let guessedJournalId = '';
                const lines = data.ecriture || data.lignes || [];
                const hasAchat = lines.some(l => l.compte && l.compte.toString().startsWith('6'));
                const hasVente = lines.some(l => l.compte && l.compte.toString().startsWith('7'));
                const hasBank = lines.some(l => l.compte && l.compte.toString().startsWith('5'));

                if (hasAchat) guessedJournalId = JOURNALS.find(j => j.code_journal.includes('ACH') || j.intitule.toLowerCase().includes('achat'))?.id || '';
                else if (hasVente) guessedJournalId = JOURNALS.find(j => j.code_journal.includes('VT') || j.intitule.toLowerCase().includes('vente'))?.id || '';
                else if (hasBank) guessedJournalId = JOURNALS.find(j => j.code_journal.includes('BQ') || j.intitule.toLowerCase().includes('banque'))?.id || '';

                const journalOptions = JOURNALS.map(j => `<option value="${j.id}" ${j.id == guessedJournalId ? 'selected' : ''}>${j.code_journal} - ${j.intitule}</option>`).join('');
                const vats = GEN_ACCOUNTS.filter(a => a.numero_de_compte.startsWith('445'));
                const vatOptions = vats.map(a => `<option value="${a.id}">${a.numero_de_compte} - ${a.intitule}</option>`).join('');

                const docIndex = Array.from(documentsContainer.children).indexOf(card) + 1;

                card.innerHTML = `
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-2">
                        <div class="d-flex align-items-center gap-3">
                            <input type="checkbox" class="form-check-input doc-selector" value="${docId}" ${data.est_facture !== false ? 'checked' : ''}>
                            <span class="badge bg-light text-dark border fw-bold">${docIndex}</span>
                            <h6 class="mb-0 fw-bold text-muted small">${item.file.name}</h6>
                            ${data.est_facture === false ? `
                                <span class="badge bg-warning text-white px-2">DOCUMENT REJETÉ</span>
                            ` : ''}
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <span class="balance-status text-danger small fw-bold"><i class="bx bx-error-circle me-1"></i>Déséquilibré</span>
                            <button class="btn btn-sm btn-success ms-3 btn-save-single" onclick="window.saveSingleDocument('${docId}')" ${data.est_facture === false ? 'disabled' : ''}>
                                <i class="bx bx-save me-1"></i>Enregistrer
                            </button>
                            <div class="vr mx-2"></div>
                            <button class="btn btn-sm btn-light text-danger" onclick="window.removeDocument('${docId}')"><i class="bx bx-trash"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-auto">
                                <img src="${item.file.type.includes('pdf') ? 'https://cdn-icons-png.flaticon.com/512/337/337946.png' : previewUrl}" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;" onclick="window.open('${previewUrl}')">
                            </div>
                            <div class="col-md">
                                ${data.est_facture === false ? `
                                    <div class="alert alert-soft-danger border d-flex align-items-center py-2 mb-3">
                                        <i class="bx bx-error-circle fs-4 me-3"></i>
                                        <div>
                                            <h6 class="mb-0 small fw-bold">Analyse : Non-comptabilisable</h6>
                                            <p class="mb-0 extra-small">${data.explication_rejet || 'Ce document n\'a pas été identifié comme une facture valide.'}</p>
                                        </div>
                                    </div>
                                ` : ''}
                                <div class="row g-2 mb-3 align-items-end ${data.est_facture === false ? 'opacity-50' : ''}">
                                    <div class="col-md-3">
                                        <label class="small text-muted mb-1">Journal <a href="javascript:void(0);" onclick="window.createQuickJournal('${docId}')"><i class="bx bx-plus-circle"></i></a></label>
                                        <select class="form-select form-select-sm select2 doc-journal">${journalOptions}</select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="small text-muted mb-1">Date</label>
                                        <input type="date" class="form-control form-control-sm doc-date" value="${window.formatDateForInput(data.date)}">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="small text-muted mb-1">Référence / Pièce</label>
                                        <input type="text" class="form-control form-control-sm doc-ref" value="${data.reference || data.ref || ''}">
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="d-inline-block p-2 bg-light border rounded small fw-bold text-muted">
                                            N° Saisie: <span class="text-primary">${window.calculateNextNS(docIndex)}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2 mb-2 p-2 bg-light rounded border">
                                    <span class="extra-small fw-bold text-uppercase"><i class="bx bx-bolt-circle me-1"></i>Outils Rapides</span>
                                    <div class="ms-auto d-flex align-items-center gap-2">
                                        <input type="number" class="form-control form-control-sm vat-amount-input" placeholder="Montant TVA" style="width: 110px;">
                                        <select class="form-select form-select-sm vat-account-select" style="width: 160px;">
                                            <option value="">Compte TVA...</option>
                                            ${vatOptions}
                                        </select>
                                        <button class="btn btn-sm btn-primary px-3 py-1" onclick="window.applyCustomVAT('${docId}')">APPLIQUER</button>
                                        <button class="btn btn-sm btn-dark px-3 py-1" onclick="window.apply18VAT('${docId}')">TVA 18%</button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle mb-2">
                                        <thead class="bg-light extra-small text-uppercase">
                                            <tr>
                                                <th style="width: 25%">Compte <a href="javascript:void(0);" onclick="window.createQuickAccount('${docId}')"><i class="bx bx-plus-circle"></i></a></th>
                                                <th style="width: 5%" class="text-center">TVA</th>
                                                <th style="width: 15%">Tiers <a href="javascript:void(0);" onclick="window.createQuickTier('${docId}')"><i class="bx bx-plus-circle"></i></a></th>
                                                <th style="width: 20%">Libellé</th>
                                                <th style="width: 10%">Débit</th>
                                                <th style="width: 10%">Crédit</th>
                                                <th style="width: 10%">Tréso</th>
                                                <th style="width: 5%"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="doc-entries-body"></tbody>
                                        <tfoot>
                                            <tr class="bg-light fw-bold extra-small">
                                                <td colspan="4" class="text-end">TOTAUX :</td>
                                                <td class="text-end total-debit">0</td>
                                                <td class="text-end total-credit">0</td>
                                                <td colspan="2"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                    <button class="btn btn-xs btn-outline-primary" onclick="window.addRow('${docId}')"><i class="bx bx-plus me-1"></i>Nouvelle ligne</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                processedDocs.push(item);
                const tbody = card.querySelector('.doc-entries-body');
                const ecritures = data.ecriture || data.lignes || [];
                ecritures.forEach(l => window.renderEntryRow(tbody, docId, l, data));

                $(card).find('.select2').select2({ theme: 'bootstrap4', width: '100%' });
                $(card).find('input, select').on('change input', () => window.updateDocBalance(docId));
                window.updateDocBalance(docId);
            };

            window.renderEntryRow = function(tbody, docId, lineData, docData) {
                const tr = document.createElement('tr');
                const matchedAccId = window.findBestAccount(lineData.compte, lineData.type) || lineData.compte_id || '';
                const identifiedTierName = (docData.tiers || docData.fournisseur || "").toUpperCase().trim();
                let matchedTierId = '';

                if (lineData.type === 'FOURNISSEUR' || (lineData.compte && lineData.compte.toString().startsWith('40'))) {
                    const t = TIERS_LIST.find(t => t.intitule.toUpperCase().includes(identifiedTierName));
                    if (t) matchedTierId = t.id;
                }

                const cleanAmount = val => {
                    if (typeof val === 'number') return val;
                    if (!val) return 0;
                    return parseFloat(val.toString().replace(/[^\d.-]/g, '')) || 0;
                };

                const treasuryOptions = Object.keys(TREASURY_POSTS_GROUPED).map(cat => `
                    <optgroup label="${cat}">
                        ${TREASURY_POSTS_GROUPED[cat].map(p => `<option value="${p.id}">${p.name}</option>`).join('')}
                    </optgroup>
                `).join('');

                const isClass5 = matchedAccId && GEN_ACCOUNTS.find(a => a.id == matchedAccId)?.numero_de_compte.startsWith('5');

                tr.innerHTML = `
                    <td>
                        <select class="form-select form-select-sm select2 row-acc" onchange="window.toggleTresorerie(this)">
                            <option value="">Choisir...</option>
                            ${GEN_ACCOUNTS.map(a => `<option value="${a.id}" ${a.id == matchedAccId ? 'selected' : ''}>${a.numero_de_compte} - ${a.intitule}</option>`).join('')}
                        </select>
                        ${!matchedAccId && lineData.compte ? `<div class="text-danger extra-small mt-1"><i class="bx bx-error-circle"></i> ${lineData.compte} absent. <a href="javascript:void(0);" onclick="window.createQuickAccount('${docId}', '${lineData.compte}')">Créer?</a></div>` : ''}
                    </td>
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input row-has-tva" ${lineData.apply_tva ? 'checked' : ''} title="Appliquer TVA">
                    </td>
                    <td>
                        <select class="form-select form-select-sm select2 row-tier">
                            <option value="">Néant</option>
                            ${TIERS_LIST.map(t => `<option value="${t.id}" ${t.id == matchedTierId ? 'selected' : ''}>[${t.compte_collectif_num}] ${t.intitule}</option>`).join('')}
                        </select>
                    </td>
                    <td><input type="text" class="form-control form-control-sm row-lib" value="${lineData.intitule || identifiedTierName || lineData.libelle || ''}"></td>
                    <td><input type="number" class="form-control form-control-sm text-end row-debit" value="${cleanAmount(lineData.debit) || (lineData.type === 'DEBIT' ? cleanAmount(lineData.montant) : 0)}"></td>
                    <td><input type="number" class="form-control form-control-sm text-end row-credit" value="${cleanAmount(lineData.credit) || (lineData.type === 'CREDIT' ? cleanAmount(lineData.montant) : 0)}"></td>
                    <td>
                        <select class="form-select form-select-sm row-poste-treso" ${isClass5 ? '' : 'disabled'}>
                            <option value="">Néant</option>
                            ${treasuryOptions}
                        </select>
                    </td>
                    <td class="text-center">
                        <div class="d-flex gap-1 justify-content-center">
                            <button class="btn btn-xs btn-light-primary" onclick="window.ouvrirVentilationCompte(this)" title="Ventilation"><i class="bx bx-pie-chart-alt"></i></button>
                            <button class="btn btn-xs btn-light-danger" onclick="this.closest('tr').remove(); window.updateDocBalance('${docId}')"><i class="bx bx-trash"></i></button>
                        </div>
                    </td>
                `;
                tbody.appendChild(tr);
                $(tr).find('.select2').select2({ theme: 'bootstrap4', width: '100%' });
            };

            window.toggleTresorerie = (select) => {
                const accId = select.value;
                const acc = GEN_ACCOUNTS.find(a => a.id == accId);
                const tr = select.closest('tr');
                const tresoSelect = tr.querySelector('.row-poste-treso');
                if (acc && acc.numero_de_compte.startsWith('5')) {
                    tresoSelect.disabled = false;
                } else {
                    tresoSelect.disabled = true;
                    tresoSelect.value = "";
                }
            };

            window.apply18VAT = (docId) => {
                const card = document.getElementById('card_' + docId);
                const tbody = card.querySelector('.doc-entries-body');
                const rows = Array.from(tbody.querySelectorAll('tr'));
                
                rows.forEach(tr => {
                    if (tr.querySelector('.row-has-tva').checked) {
                        const debit = parseFloat(tr.querySelector('.row-debit').value) || 0;
                        const credit = parseFloat(tr.querySelector('.row-credit').value) || 0;
                        const amount = Math.abs(debit - credit);
                        if (amount > 0) {
                            const vatAmount = Math.round(amount * 0.18);
                            const vatAcc = GEN_ACCOUNTS.find(a => a.numero_de_compte.startsWith('4451'));
                            window.renderEntryRow(tbody, docId, {
                                compte_id: vatAcc ? vatAcc.id : '',
                                libelle: 'TVA 18%',
                                debit: debit > 0 ? vatAmount : 0,
                                credit: credit > 0 ? vatAmount : 0
                            }, {});
                        }
                    }
                });
                window.updateDocBalance(docId);
            };

            window.applyCustomVAT = (docId) => {
                const card = document.getElementById('card_' + docId);
                const vatAmount = parseFloat(card.querySelector('.vat-amount-input').value) || 0;
                const vatAccId = card.querySelector('.vat-account-select').value;
                
                if (!vatAmount || !vatAccId) {
                    Swal.fire('Info', 'Veuillez saisir un montant et choisir un compte TVA.', 'info');
                    return;
                }

                const tbody = card.querySelector('.doc-entries-body');
                const lastRowWithAmount = Array.from(tbody.querySelectorAll('tr')).findLast(tr => {
                    return (parseFloat(tr.querySelector('.row-debit').value) || parseFloat(tr.querySelector('.row-credit').value)) > 0;
                });

                const type = (lastRowWithAmount && parseFloat(lastRowWithAmount.querySelector('.row-debit').value) > 0) ? 'DEBIT' : 'CREDIT';

                window.renderEntryRow(tbody, docId, {
                    compte_id: vatAccId,
                    libelle: 'TVA Appliquée',
                    debit: type === 'DEBIT' ? vatAmount : 0,
                    credit: type === 'CREDIT' ? vatAmount : 0
                }, {});
                window.updateDocBalance(docId);
            };

            window.createQuickJournal = (docId) => {
                Swal.fire({
                    title: 'Créer un Code Journal',
                    html: `
                        <input id="swal-journal-code" class="swal2-input" placeholder="Code (ex: ACH)">
                        <input id="swal-journal-name" class="swal2-input" placeholder="Intitulé">
                        <select id="swal-journal-type" class="swal2-select">
                            <option value="Achats">Achat</option>
                            <option value="Ventes">Vente</option>
                            <option value="Tresorerie">Trésorerie</option>
                            <option value="Divers">Opérations Diverses</option>
                        </select>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Créer',
                    preConfirm: () => {
                        const code = document.getElementById('swal-journal-code').value;
                        const name = document.getElementById('swal-journal-name').value;
                        if (!code || !name) { Swal.showValidationMessage('Veuillez remplir tous les champs'); return false; }
                        return { code_journal: code, intitule: name, type: document.getElementById('swal-journal-type').value, traitement_analytique: 'non' }
                    }
                }).then(async result => {
                    if (result.isConfirmed) {
                        try {
                            const res = await fetch("{{ route('accounting_journals.store') }}", {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                                body: JSON.stringify(result.value)
                            });
                            const json = await res.json();
                            if (json.success) {
                                JOURNALS.push(json.journal);
                                window.updateAllJournalSelects(json.journal);
                                Swal.fire('Succès', 'Journal créé et appliqué.', 'success');
                            } else {
                                Swal.fire('Erreur', json.message, 'error');
                            }
                        } catch (e) { Swal.fire('Erreur', 'Impossible de créer le journal.', 'error'); }
                    }
                });
            };

            window.updateAllJournalSelects = (newJournal) => {
                document.querySelectorAll('.doc-journal').forEach(select => {
                    const option = new Option(`${newJournal.code_journal} - ${newJournal.intitule}`, newJournal.id);
                    $(select).append(option).trigger('change');
                });
            };

            window.createQuickAccount = (docId, presetNum = '') => {
                Swal.fire({
                    title: 'Créer un Compte Général',
                    html: `
                        <input id="swal-acc-num" class="swal2-input" placeholder="Numéro de compte" value="${presetNum}">
                        <input id="swal-acc-name" class="swal2-input" placeholder="Intitulé du compte">
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Créer',
                    preConfirm: () => {
                        const num = document.getElementById('swal-acc-num').value;
                        const name = document.getElementById('swal-acc-name').value;
                        if (!num || !name) { Swal.showValidationMessage('Veuillez remplir tous les champs'); return false; }
                        return { numero_de_compte: num, intitule: name }
                    }
                }).then(async result => {
                    if (result.isConfirmed) {
                        try {
                            const res = await fetch("{{ route('plan_comptable.store') }}", {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                                body: JSON.stringify(result.value)
                            });
                            const json = await res.json();
                            if (json.success) {
                                const newAcc = { id: json.id, numero_de_compte: json.numero_de_compte, intitule: json.intitule };
                                GEN_ACCOUNTS.push(newAcc);
                                window.updateAllAccountSelects(newAcc);
                                Swal.fire('Succès', 'Compte créé.', 'success');
                            } else {
                                Swal.fire('Erreur', json.error || 'Erreur inconnue', 'error');
                            }
                        } catch (e) { Swal.fire('Erreur', 'Impossible de créer le compte.', 'error'); }
                    }
                });
            };

            window.updateAllAccountSelects = (newAcc) => {
                document.querySelectorAll('.row-acc').forEach(select => {
                    const option = new Option(`${newAcc.numero_de_compte} - ${newAcc.intitule}`, newAcc.id);
                    $(select).append(option);
                });
            };

            window.createQuickTier = (docId) => {
                const modalEl = document.getElementById('createTiersModal');
                if (!modalEl) return;
                const modal = new bootstrap.Modal(modalEl);
                window.currentTierSelect = null;
                modal.show();
            };

            window.createTiersSimple = (e) => {
                if (e) e.preventDefault();
                const btn = document.getElementById('btnCreateTiers');
                const form = document.getElementById('createTiersForm');
                if (!form || !form.checkValidity()) { if (form) form.reportValidity(); return; }
                
                const data = { 
                    type_de_tiers: document.getElementById('type_tiers').value, 
                    compte_general: document.getElementById('compte_general_tiers').value, 
                    intitule: document.getElementById('intitule_tiers').value, 
                    numero_de_tiers: document.getElementById('numero_tiers').value 
                };
                
                if (btn) { btn.disabled = true; btn.innerText = "Création..."; }
                fetch('{{ route("plan_tiers.store") }}', { 
                    method: 'POST', 
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }, 
                    body: JSON.stringify(data) 
                }).then(r => r.json()).then(res => {
                    if (res.success) {
                        const newTier = { id: res.id, numero_de_tiers: res.numero_de_tiers, intitule: res.intitule, compte_collectif_num: res.compte_collectif_num };
                        TIERS_LIST.push(newTier);
                        document.querySelectorAll('.row-tier').forEach(sel => {
                            const newOption = new Option(`[${res.compte_collectif_num}] ${res.intitule}`, res.id);
                            $(sel).append(newOption);
                        });
                        const m = bootstrap.Modal.getInstance(document.getElementById('createTiersModal'));
                        if (m) m.hide();
                        form.reset();
                        Swal.fire('Succès', 'Tiers créé.', 'success');
                    } else alert("Erreur: " + res.error);
                }).finally(() => { if (btn) { btn.disabled = false; btn.innerText = "Enregistrer le Tiers"; } });
            };

            window.updateDocBalance = (docId) => {
                const card = document.getElementById('card_' + docId);
                if (!card) return;
                const rows = card.querySelectorAll('.doc-entries-body tr');
                let d = 0, c = 0;
                rows.forEach(tr => {
                    d += parseFloat(tr.querySelector('.row-debit').value) || 0;
                    c += parseFloat(tr.querySelector('.row-credit').value) || 0;
                });
                
                card.querySelector('.total-debit').innerText = Math.round(d).toLocaleString();
                card.querySelector('.total-credit').innerText = Math.round(c).toLocaleString();
                
                const balanced = Math.abs(d - c) < 1 && d > 0;
                const statusEl = card.querySelector('.balance-status');
                if (balanced) {
                    statusEl.innerHTML = '<i class="bx bx-check-circle me-1"></i>Équilibré';
                    statusEl.className = 'balance-status text-success small fw-bold';
                } else {
                    statusEl.innerHTML = '<i class="bx bx-error-circle me-1"></i>Déséquilibré';
                    statusEl.className = 'balance-status text-danger small fw-bold';
                }
            };

            window.calculateNextNS = (docIndex) => {
                const parts = INITIAL_NS.split('_');
                if (parts.length < 2) return INITIAL_NS + '_' + docIndex;
                const prefix = parts[0] + '_';
                const sequenceValue = parseInt(parts[1]);
                return prefix + (sequenceValue + docIndex - 1).toString().padStart(12, '0');
            };

            window.removeDocument = (docId) => {
                const card = document.getElementById('card_' + docId);
                if (card) card.remove();
                const queue = document.getElementById('queue_' + id);
                if (queue) queue.remove();
                processedDocs = processedDocs.filter(d => d.id !== docId);
                filesQueue = filesQueue.filter(d => d.id !== docId);
                if (processedDocs.length === 0 && filesQueue.length === 0) {
                    if (emptyState) emptyState.classList.remove('d-none');
                }
                window.updateQueueCount();
            };

            window.addRow = (docId) => {
                const tbody = document.querySelector(`#card_${docId} .doc-entries-body`);
                window.renderEntryRow(tbody, docId, {compte:'', intitule:'', debit:0, credit:0}, {});
                window.updateDocBalance(docId);
            };

            window.ouvrirVentilationCompte = (btn) => {
                 const tr = btn.closest('tr');
                 window.currentRowForVentilation = tr;
                 const debit = parseFloat(tr.querySelector('.row-debit').value) || 0;
                 const credit = parseFloat(tr.querySelector('.row-credit').value) || 0;
                 const montant = Math.abs(debit - credit);
                 
                 const display = document.getElementById('montant_a_ventiler_display');
                 if (display) display.innerText = montant.toLocaleString();
                 const tbody = document.querySelector('#tableVentilation tbody');
                 if (tbody) tbody.innerHTML = '';
                 
                 const existingData = tr.dataset.ventilations ? JSON.parse(tr.dataset.ventilations) : [];
                 if (existingData.length > 0) {
                     existingData.forEach(v => window.ajouterLigneVentilation(v.section_id, v.pourcentage, v.montant));
                 } else {
                     window.ajouterLigneVentilation();
                 }
                 
                 const modalEl = document.getElementById('modalVentilationAnalytique');
                 if (modalEl) new bootstrap.Modal(modalEl).show();
                 if (window.mettreAJourMontantsVentilation) window.mettreAJourMontantsVentilation();
            };

            window.saveDocuments = async (mode) => {
                const docsToSave = [];
                const selectors = mode === 'all' ? document.querySelectorAll('.doc-selector') : document.querySelectorAll('.doc-selector:checked');

                selectors.forEach(checkbox => {
                    const docId = checkbox.value;
                    const item = filesQueue.find(d => d.id === docId);
                    const card = document.getElementById('card_' + docId);
                    if (!card) return;
                    
                    const journalId = card.querySelector('.doc-journal').value;
                    const dDate = card.querySelector('.doc-date').value;
                    const dRef = card.querySelector('.doc-ref').value;
                    const dNSaisie = card.querySelector('.text-primary').innerText;

                    if (!journalId) return;

                    const ecritures = [];
                    card.querySelectorAll('.doc-entries-body tr').forEach(tr => {
                        const accId = tr.querySelector('.row-acc').value;
                        if (!accId) return;
                        const debit = parseFloat(tr.querySelector('.row-debit').value) || 0;
                        const credit = parseFloat(tr.querySelector('.row-credit').value) || 0;
                        const vnts = tr.dataset.ventilations ? JSON.parse(tr.dataset.ventilations) : null;

                        ecritures.push({
                            date: dDate, n_saisie: dNSaisie, description_operation: tr.querySelector('.row-lib').value,
                            reference_piece: dRef, plan_comptable_id: accId, plan_tiers_id: tr.querySelector('.row-tier').value || null,
                            debit, credit, code_journal_id: journalId, exercices_comptables_id: EXERCICE_ACTIF.id,
                            poste_tresorerie_id: tr.querySelector('.row-poste-treso')?.value || null,
                            ventilations: vnts, plan_analytique: vnts ? 1 : 0
                        });
                    });
                    
                    if (ecritures.length > 0) docsToSave.push({ id: docId, ecritures, file: item.file });
                });

                if (docsToSave.length === 0) { Swal.fire('Attention', 'Aucun document valide sélectionné.', 'warning'); return; }

                const btn = document.getElementById('btnSaveOnlySelected');
                if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>...'; }

                let successCount = 0;
                for (const doc of docsToSave) {
                    try {
                        const formData = new FormData();
                        formData.append('piece_justificatif', doc.file);
                        formData.append('ecritures', JSON.stringify(doc.ecritures));
                        const res = await fetch(SAVE_ROUTE, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }, body: formData });
                        const json = await res.json();
                        if (json.success) { successCount++; window.removeDocument(doc.id); }
                    } catch (e) { console.error(e); }
                }

                Swal.fire('Succès', `${successCount} documents enregistrés.`, 'success');
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bx bx-save me-1"></i> Enregistrer'; }
            };

            window.saveSingleDocument = async (docId) => {
                const card = document.getElementById('card_' + docId);
                const item = filesQueue.find(d => d.id === docId);
                if (!card || !item) return;

                const journalId = card.querySelector('.doc-journal').value;
                if (!journalId) { Swal.fire('Journal Manquant', 'Sélectionnez un journal.', 'warning'); return; }

                const dNSaisie = card.querySelector('.text-primary').innerText;
                const dDate = card.querySelector('.doc-date').value;
                const dRef = card.querySelector('.doc-ref').value;

                const ecritures = [];
                card.querySelectorAll('.doc-entries-body tr').forEach(tr => {
                    const accId = tr.querySelector('.row-acc').value;
                    if (!accId) return;
                    const vnts = tr.dataset.ventilations ? JSON.parse(tr.dataset.ventilations) : null;
                    ecritures.push({
                        date: dDate, n_saisie: dNSaisie, description_operation: tr.querySelector('.row-lib').value,
                        reference_piece: dRef, plan_comptable_id: accId, plan_tiers_id: tr.querySelector('.row-tier').value || null,
                        debit: parseFloat(tr.querySelector('.row-debit').value) || 0,
                        credit: parseFloat(tr.querySelector('.row-credit').value) || 0,
                        code_journal_id: journalId, exercices_comptables_id: EXERCICE_ACTIF.id,
                        poste_tresorerie_id: tr.querySelector('.row-poste-treso')?.value || null,
                        ventilations: vnts, plan_analytique: vnts ? 1 : 0
                    });
                });

                const btn = card.querySelector('.btn-save-single');
                const originalHtml = btn.innerHTML;
                btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                try {
                    const formData = new FormData();
                    formData.append('piece_justificatif', item.file);
                    formData.append('ecritures', JSON.stringify(ecritures));
                    const res = await fetch(SAVE_ROUTE, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }, body: formData });
                    const json = await res.json();
                    if (json.success) { 
                        Swal.fire({ icon: 'success', title: 'Enregistré', timer: 1000, showConfirmButton: false });
                        window.removeDocument(docId); 
                    } else Swal.fire('Erreur', json.message || 'Erreur', 'error');
                } catch (e) { Swal.fire('Erreur', 'Serveur injoignable', 'error'); }
                finally { btn.disabled = false; btn.innerHTML = originalHtml; }
            };

            window.formatDateForInput = (dateStr) => {
                if (!dateStr) return '';
                if (dateStr.includes('/')) {
                    const parts = dateStr.split('/');
                    if (parts.length === 3) return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
                }
                return dateStr;
            };

            window.findBestAccount = (code, type) => {
                if (!code) return null;
                const sCode = code.toString();
                let match = GEN_ACCOUNTS.find(a => a.numero_de_compte === sCode);
                if (match) return match.id;
                match = GEN_ACCOUNTS.find(a => a.numero_de_compte.startsWith(sCode.substring(0, 4)));
                return match ? match.id : null;
            };

            const btnReset = document.getElementById('btnReset');
            if (btnReset) btnReset.onclick = () => window.location.reload();
            
            const btnSaveAllDocs = document.getElementById('btnSaveAllDocs');
            if (btnSaveAllDocs) btnSaveAllDocs.onclick = () => window.saveDocuments('all');
            
            const btnSaveOnlySelected = document.getElementById('btnSaveOnlySelected');
            if (btnSaveOnlySelected) btnSaveOnlySelected.onclick = () => window.saveDocuments('selected');
            
            window.updateQueueCount();
        });
    </script>
</body>
</html>
