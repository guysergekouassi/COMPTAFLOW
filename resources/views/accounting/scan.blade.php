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

    /* Hide number input spinners for cleaner accounting view */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    input[type=number] {
        -moz-appearance: textfield;
    }

    .row-debit, .row-credit {
        font-weight: 800 !important;
        padding-left: 10px !important;
        padding-right: 10px !important;
        font-size: 1.05rem !important;
        height: 42px !important;
        color: #0f172a !important;
        background-color: #fbfcfe !important;
        border: 1px solid #cbd5e1 !important;
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
                                <div class="d-flex gap-3 align-items-center">
                                    <div id="manualVATContainer" class="d-flex gap-2 align-items-center bg-light p-2 rounded-3 border d-none">
                                        <div style="width: 140px;">
                                            <input type="number" id="manualVATAmount" class="form-control form-control-sm" placeholder="Montant TVA">
                                        </div>
                                        <div style="width: 200px;">
                                            <select id="manualVATAccount" class="form-select form-select-sm select2">
                                                <option value="">Compte TVA...</option>
                                                @foreach($plansComptables->filter(fn($a) => str_starts_with($a->numero_de_compte, '445')) as $acc)
                                                    <option value="{{ $acc->id }}">{{ $acc->numero_de_compte }} - {{ $acc->intitule }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button class="btn btn-primary btn-sm" onclick="window.applyManualVAT()">APPLIQUER</button>
                                    </div>
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
                                            <th style="width: 250px;">Compte Général</th>
                                            <th style="width: 50px;" class="text-center">TVA</th>
                                            <th style="width: 200px;">Compte Tiers</th>
                                            <th style="min-width: 200px;">Libellé / Détails</th>
                                            <th class="text-end" style="width: 200px;">Débit</th>
                                            <th class="text-end" style="width: 200px;">Crédit</th>
                                            <th style="width: 150px;">Poste Trésorerie</th>
                                            <th class="text-center" style="width: 100px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="entriesBody">
                                        <tr><td colspan="8" class="text-center py-5 text-muted"><i class="bx bx-info-circle me-1"></i>En attente de document pour analyse précise.</td></tr>
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

    <!-- Modal Ventilation Analytique -->
    <div class="modal fade" id="modalVentilationAnalytique" data-bs-backdrop="static" style="z-index: 10001;">
        <div class="modal-dialog modal-dialog-centered" style="width: 800px !important; max-width: 800px !important;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 25px;">
                <div class="modal-body p-5 bg-white" style="position: relative;">
                    <div class="text-center mb-5 position-relative">
                        <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Close"></button>
                        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 mb-0">
                            Ventilation <span class="text-primary">Analytique</span>
                        </h1>
                        <div class="h-1 w-8 bg-primary mx-auto mt-2 rounded-full"></div>
                    </div>
                    <div class="mb-5 text-center p-3 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <span class="text-secondary fw-semibold">Montant à ventiler:</span> 
                        <span id="montant_a_ventiler_display" class="fs-4 fw-bold text-dark ms-1">0.00</span>
                        <span class="text-dark fw-bold ms-1">FCFA</span>
                    </div>
                    <form id="formVentilation">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="tableVentilation">
                                <thead>
                                    <tr class="text-secondary text-uppercase" style="font-size: 0.75rem; letter-spacing: 1px; border-bottom: 2px solid #f1f5f9;">
                                        <th style="width: 50%;">Section Analytique</th>
                                        <th style="width: 20%;">%</th>
                                        <th style="width: 25%;">Montant</th>
                                        <th style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr class="fw-bold border-top" style="background: #f8fafc; font-size: 0.8rem;">
                                        <td class="text-end" style="padding: 0.5rem;">Total:</td>
                                        <td id="total_pourcentage" class="text-primary" style="padding: 0.5rem;">0.00 %</td>
                                        <td id="total_montant_ventile" class="text-dark" style="padding: 0.5rem;">0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="mt-3 text-center">
                            <button type="button" class="btn btn-outline-primary btn-xs rounded-pill fw-bold px-3" onclick="ajouterLigneVentilation()">
                                <i class="bx bx-plus-circle me-1"></i> Ajouter une section
                            </button>
                        </div>
                    </form>
                    <div class="d-flex justify-content-center gap-3 pt-5">
                        <button type="button" class="btn btn-outline-secondary px-5" data-bs-dismiss="modal">Annuler</button>
                        <button type="button" class="btn btn-primary px-5" onclick="validerVentilation()">
                            <i class="bx bx-check-circle me-1"></i> Valider
                        </button>
                    </div>
                </div>
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
        const SECTIONS_ANALYTIQUES = @json(\App\Models\SectionAnalytique::with('axe')->get());
        let NEXT_SAISIE = "{{ $nextSaisieNumber }}";
        let currentRowForVentilation = null;

        async function fetchNextSaisieNumber() {
            try {
                const res = await fetch("{{ route('api.next-saisie-number') }}");
                const json = await res.json();
                if (json.success) {
                    console.log("Sync N° Saisie successful:", json.numero);
                    NEXT_SAISIE = json.numero;
                    const display = document.getElementById('displayNSaisie');
                    if (display) display.innerText = NEXT_SAISIE;
                }
            } catch (e) {
                console.error("Erreur sync n_saisie:", e);
            }
        }

        // --- GESTION TVA MANUELLE ---
        window.applyManualVAT = () => {
            const amount = parseFloat(document.getElementById('manualVATAmount').value) || 0;
            const accountId = document.getElementById('manualVATAccount').value;
            const accountNum = document.getElementById('manualVATAccount').options[document.getElementById('manualVATAccount').selectedIndex]?.text.split(' ')[0];

            if (amount <= 0 || !accountId) {
                Swal.fire('Erreur', 'Veuillez saisir un montant et sélectionner un compte de TVA.', 'error');
                return;
            }

            const rows = Array.from(document.querySelectorAll('#entriesBody tr'));
            
            // On cherche la ligne de contrepartie (401, 57, 41)
            let mainLine = rows.find(tr => {
                const accSelect = tr.querySelector('.row-acc');
                const accCode = accSelect.options[accSelect.selectedIndex]?.text.split(' ')[0] || "";
                return accCode.startsWith('40') || accCode.startsWith('57') || accCode.startsWith('41') || accCode.startsWith('52');
            });

            if (!mainLine) {
                Swal.fire('Erreur', 'Impossible de trouver la ligne de contrepartie (Fournisseur/Banque/Caisse).', 'error');
                return;
            }

            // Création de la ligne TVA
            const tr = document.createElement('tr');
            const date = mainLine.querySelector('.row-date').value;
            const ref = mainLine.querySelector('.row-ref').value;

            // Récupération des libellés des lignes cochées pour le nouveau libellé
            const checkedLabels = rows.filter(r => r.querySelector('.row-vat-check')?.checked)
                                      .map(r => r.querySelector('.row-lib')?.value || "")
                                      .filter(l => l.length > 0);
            
            const sourceLabel = checkedLabels.length > 0 ? checkedLabels.join(' / ') : "MANUELLE";
            const vataLabel = "TVA / " + sourceLabel;

            tr.innerHTML = `
                <td><select class="form-select select2 row-acc"><option value="${accountId}" selected>${accountNum} - TVA MANUELLE</option></select></td>
                <td class="text-center"><i class="bx bx-check text-success"></i></td>
                <td><div class="d-flex gap-1"><select class="form-select select2 row-tier"><option value="" selected>Néant</option></select></div></td>
                <td><input type="text" class="form-control form-control-sm row-lib" value="${vataLabel}"><div class="small text-muted mt-1 px-1">Pièce: ${ref} du ${date}</div><input type="hidden" class="row-date" value="${date}"><input type="hidden" class="row-ref" value="${ref}"></td>
                <td><input type="number" class="form-control text-end row-debit" value="${amount}"></td>
                <td><input type="number" class="form-control text-end row-credit" value="0"></td>
                <td><select class="form-select select2 row-poste-treso" disabled><option value="">Néant</option></select></td>
                <td class="text-center">
                    <div class="d-flex gap-1 justify-content-center">
                        <button class="btn btn-sm btn-icon text-danger" onclick="this.closest('tr').remove(); window.updateTotals();"><i class="bx bx-trash"></i></button>
                    </div>
                </td>
            `;

            mainLine.parentNode.insertBefore(tr, mainLine);
            $(tr).find('.select2').select2({ theme: 'bootstrap4', width: '100%' }).on('change', window.updateTotals);

            // Mise à jour de la contrepartie (On ajoute la TVA au crédit de la ligne principale)
            const currentCredit = parseFloat(mainLine.querySelector('.row-credit').value) || 0;
            const currentDebit = parseFloat(mainLine.querySelector('.row-debit').value) || 0;
            
            if (currentCredit > 0) mainLine.querySelector('.row-credit').value = currentCredit + amount;
            else if (currentDebit > 0) mainLine.querySelector('.row-debit').value = currentDebit - amount; // Cas rare d'ajustement débit

            window.updateTotals();
            // document.getElementById('manualVATContainer').classList.add('d-none'); // On ne cache plus les champs
            
            // On décoche tout après application
            document.querySelectorAll('.row-vat-check').forEach(c => c.checked = false);
        };

        // --- GESTION VENTILATION ANALYTIQUE ---
        window.ouvrirVentilation = (btn) => {
            const tr = btn.closest('tr');
            currentRowForVentilation = tr;
            
            const debit = parseFloat(tr.querySelector('.row-debit').value) || 0;
            const credit = parseFloat(tr.querySelector('.row-credit').value) || 0;
            const montant = Math.abs(debit - credit);

            document.getElementById('montant_a_ventiler_display').innerText = montant.toLocaleString();
            
            const tbody = document.querySelector('#tableVentilation tbody');
            tbody.innerHTML = '';

            // Charger les données existantes si présentes
            const existingData = tr.dataset.ventilations ? JSON.parse(tr.dataset.ventilations) : [];
            if (existingData.length > 0) {
                existingData.forEach(v => ajouterLigneVentilation(v.section_id, v.pourcentage, v.montant));
            } else {
                ajouterLigneVentilation();
            }

            const modal = new bootstrap.Modal(document.getElementById('modalVentilationAnalytique'));
            modal.show();
            mettreAJourMontantsVentilation();
        };

        window.ajouterLigneVentilation = (sectionId = '', pourcentage = '', montant = '') => {
            const tbody = document.querySelector('#tableVentilation tbody');
            const tr = document.createElement('tr');
            
            let options = '<option value="" disabled selected>Sélectionner une section...</option>';
            const grouped = {};
            SECTIONS_ANALYTIQUES.forEach(s => {
                const axeName = s.axe ? s.axe.nom : 'Autre';
                if (!grouped[axeName]) grouped[axeName] = [];
                grouped[axeName].push(s);
            });

            for (const [axe, sections] of Object.entries(grouped)) {
                options += `<optgroup label="${axe}">`;
                sections.forEach(s => {
                    options += `<option value="${s.id}" ${s.id == sectionId ? 'selected' : ''}>${s.code} - ${s.intitule}</option>`;
                });
                options += `</optgroup>`;
            }

            tr.innerHTML = `
                <td><select class="form-select form-select-sm select2-vent" required>${options}</select></td>
                <td><input type="number" class="form-control form-control-sm vent-pct" step="0.01" min="0" max="100" value="${pourcentage}" placeholder="0.00"></td>
                <td><input type="number" class="form-control form-control-sm vent-mnt" step="0.01" value="${montant}" placeholder="0.00"></td>
                <td class="text-center"><button type="button" class="btn btn-link text-danger p-0" onclick="this.closest('tr').remove(); mettreAJourMontantsVentilation();"><i class="bx bx-trash"></i></button></td>
            `;

            tbody.appendChild(tr);
            $(tr).find('.select2-vent').select2({ 
                theme: 'bootstrap4', 
                width: '100%', 
                dropdownParent: $('#modalVentilationAnalytique') 
            });

            tr.querySelector('.vent-pct').oninput = () => calculerDepuisPct(tr);
            tr.querySelector('.vent-mnt').oninput = () => calculerDepuisMnt(tr);
        };

        const calculerDepuisPct = (tr) => {
            const total = parseFloat(document.getElementById('montant_a_ventiler_display').innerText.replace(/\s/g, '')) || 0;
            const pct = parseFloat(tr.querySelector('.vent-pct').value) || 0;
            tr.querySelector('.vent-mnt').value = (total * pct / 100).toFixed(2);
            mettreAJourMontantsVentilation();
        };

        const calculerDepuisMnt = (tr) => {
            const total = parseFloat(document.getElementById('montant_a_ventiler_display').innerText.replace(/\s/g, '')) || 0;
            const mnt = parseFloat(tr.querySelector('.vent-mnt').value) || 0;
            tr.querySelector('.vent-pct').value = total > 0 ? (mnt / total * 100).toFixed(2) : 0;
            mettreAJourMontantsVentilation();
        };

        window.mettreAJourMontantsVentilation = () => {
            let totalPct = 0;
            let totalMnt = 0;
            const totalAVentiler = parseFloat(document.getElementById('montant_a_ventiler_display').innerText.replace(/\s/g, '')) || 0;

            document.querySelectorAll('#tableVentilation tbody tr').forEach(tr => {
                totalPct += parseFloat(tr.querySelector('.vent-pct').value) || 0;
                totalMnt += parseFloat(tr.querySelector('.vent-mnt').value) || 0;
            });

            document.getElementById('total_pourcentage').innerText = totalPct.toFixed(2) + ' %';
            document.getElementById('total_montant_ventile').innerText = totalMnt.toFixed(2);

            const isOk = Math.abs(totalPct - 100) < 0.1;
            document.getElementById('total_pourcentage').className = isOk ? 'text-success' : 'text-danger';
        };

        window.validerVentilation = () => {
            const totalPct = parseFloat(document.getElementById('total_pourcentage').innerText) || 0;
            if (Math.abs(totalPct - 100) > 0.1) {
                Swal.fire('Attention', 'Le total doit être égal à 100%. Actuellement : ' + totalPct.toFixed(2) + '%', 'warning');
                return;
            }

            const ventilations = [];
            let error = false;
            document.querySelectorAll('#tableVentilation tbody tr').forEach(tr => {
                const sectionId = tr.querySelector('.select2-vent').value;
                const pourcentage = tr.querySelector('.vent-pct').value;
                const montant = tr.querySelector('.vent-mnt').value;

                if (!sectionId) error = true;
                ventilations.push({ section_id: sectionId, pourcentage, montant });
            });

            if (error) {
                Swal.fire('Erreur', 'Veuillez sélectionner une section pour chaque ligne.', 'error');
                return;
            }

            currentRowForVentilation.dataset.ventilations = JSON.stringify(ventilations);
            const badge = currentRowForVentilation.querySelector('.ventilated-badge');
            if (badge) badge.classList.remove('d-none');
            else {
                const libCell = currentRowForVentilation.querySelector('td:nth-child(3)');
                const newBadge = document.createElement('span');
                newBadge.className = 'badge bg-label-info ms-2 ventilated-badge';
                newBadge.innerHTML = '<i class="bx bx-pie-chart-alt me-1"></i>Ventilé';
                libCell.appendChild(newBadge);
            }

            bootstrap.Modal.getInstance(document.getElementById('modalVentilationAnalytique')).hide();
        };

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

            // Force sync N° Saisie au chargement
            fetchNextSaisieNumber();

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

            let isScanning = false;
            const handleUpload = async (file) => {
                if (!file || isScanning) return;
                
                isScanning = true;
                processingUI.classList.remove('d-none');
                const h6 = processingUI.querySelector('h6');
                
                try {
                    const compressedBase64 = await compressImage(file);
                    imagePreview.src = compressedBase64;
                    imagePreview.classList.remove('d-none');
                    uploadContainer.classList.add('d-none');
                    
                    const base64Content = compressedBase64.split(',')[1];
                    
                    // Lancement du décompte visuel de 20 secondes
                    let countdown = 20;
                    const countdownInterval = setInterval(() => {
                        countdown--;
                        if (countdown > 0) {
                            h6.innerText = `ANALYSE EN COURS...\nPATIENTEZ ENVIRON ${countdown}s`;
                        } else {
                            h6.innerText = "FINALISATION DE L'ANALYSE...";
                            clearInterval(countdownInterval);
                        }
                    }, 1000);
                    h6.innerText = `ANALYSE EN COURS...\nPATIENTEZ ENVIRON ${countdown}s`;

                    const makeGeminiRequest = async (formData, retryCount = 0) => {
                        const MAX_RETRIES = 5;
                        const API_URL = '{{ route("ia.traiter") }}';

                        try {
                            const response = await fetch(API_URL, {
                                method: 'POST',
                                body: formData
                            });

                            const responseData = await response.json();

                            // Gestion du quota 429
                            if (response.status === 429) {
                                if (retryCount < MAX_RETRIES) {
                                    const baseWait = Math.pow(2, retryCount) * 2000;
                                    const jitter = Math.random() * 1000;
                                    let waitTime = Math.min(baseWait + jitter, 30000);
                                    
                                    for (let i = Math.ceil(waitTime/1000); i > 0; i--) {
                                        h6.innerText = `QUOTA DÉPASSÉ (${retryCount+1}/${MAX_RETRIES}).\nRE-TENTATIVE DANS ${i}s...`;
                                        await new Promise(r => setTimeout(r, 1000));
                                    }
                                    h6.innerText = "NOUVELLE TENTATIVE...";
                                    return makeGeminiRequest(formData, retryCount + 1);
                                } else {
                                    throw new Error("Serveur Google saturé. Réessayez dans quelques minutes.");
                                }
                            }

                            // Gestion de la surcharge 503 (service temporairement indisponible)
                            if (response.status === 503) {
                                if (retryCount < MAX_RETRIES) {
                                    const waitTime = Math.pow(2, retryCount) * 3000;
                                    for (let i = Math.ceil(waitTime/1000); i > 0; i--) {
                                        h6.innerText = `SERVICE SURCHARGÉ - Ré-tentative dans ${i}s... (${retryCount+1}/${MAX_RETRIES})`;
                                        await new Promise(r => setTimeout(r, 1000));
                                    }
                                    return makeGeminiRequest(formData, retryCount + 1);
                                } else {
                                    throw new Error("L'IA Gemini est temporairement surchargée. Réessayez dans quelques instants.");
                                }
                            }

                            if (!response.ok) {
                                throw new Error(responseData.error || `Erreur HTTP ${response.status}`);
                            }

                            return responseData;
                        } catch (e) {
                            const msg = e.message;
                            if (msg.includes('503') || msg.includes('surcharg')) throw new Error("⚠️ L'IA est temporairement indisponible. Réessayez dans 30 secondes.");
                            if (msg.includes('quota') || msg.includes('429')) throw new Error("⚠️ Quota IA dépassé. Réessayez dans quelques minutes.");
                            throw new Error(`Erreur de communication: ${msg}`);
                        }
                    };

                    const formData = new FormData();
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
                    
                    if (base64Content) {
                        const imageBlob = await fetch(`data:image/jpeg;base64,${base64Content}`).then(r => r.blob());
                        formData.append('facture', imageBlob, 'facture.jpg');
                    }

                    const result = await makeGeminiRequest(formData);
                    
                    // Une fois la requête terminée, on s'assure que l'intervalle est stoppé
                    clearInterval(countdownInterval);

                    if (!result || result.error) throw new Error(result?.error || "Réponse invalide de l'IA");

                    // Validation des données critiques (support des différents formats de clés possibles)
                    const ecritures = result.ecriture || result.ecritures || result.lignes || result.lines;
                    if (!ecritures || !Array.isArray(ecritures) || ecritures.length === 0) {
                        throw new Error("Aucune ligne d'écriture trouvée. Vérifiez la qualité du document.");
                    }
                    
                    // Manage VAT button state
                    const btnVAT = document.getElementById('btnApplyVAT');
                    const manualVAT = document.getElementById('manualVATContainer');
                    if (result.has_tva || (result.montant_tva && result.montant_tva > 0) || result.hasVAT) {
                        btnVAT.classList.add('d-none');
                        btnVAT.disabled = true;
                        manualVAT.classList.add('d-none');
                    } else {
                        btnVAT.classList.remove('d-none');
                        btnVAT.disabled = false;
                        btnVAT.innerHTML = '<i class="bx bx-plus me-1"></i>APPLIQUER TVA 18%';
                        manualVAT.classList.remove('d-none');
                    }

                    renderTable(result);
                } catch (e) { 
                    alert("Erreur: " + e.message); 
                    resetUI();
                } finally { 
                    isScanning = false;
                    processingUI.classList.add('d-none'); 
                    h6.innerText = "ANALYSE...";
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
                
                // Group treasury posts by category for the select
                const groupedPosts = {};
                TREASURY_POST_LIST.forEach(p => {
                    const catName = p.category ? p.category.name : 'Autres';
                    if (!groupedPosts[catName]) groupedPosts[catName] = [];
                    groupedPosts[catName].push(p);
                });

                let treasuryOptionsHtml = '<option value="">Néant</option>';
                for (const [cat, posts] of Object.entries(groupedPosts)) {
                    treasuryOptionsHtml += `<optgroup label="${cat}">`;
                    posts.forEach(p => {
                        treasuryOptionsHtml += `<option value="${p.id}">${p.name}</option>`;
                    });
                    treasuryOptionsHtml += `</optgroup>`;
                }

                ecritures.forEach(l => {
                    const tr = document.createElement('tr');
                    const matchedAccId = findBestAccount(l.compte, l.type);
                    const accCode = l.compte ? l.compte.toString() : '';
                    const isTreasury = accCode.startsWith('5');
                    const isVAT = l.type === 'TVA' || accCode.startsWith('445');
                    
                    // Nettoyage des montants (suppression des espaces insérés par l'IA)
                    const cleanAmount = (val) => {
                        if (typeof val === 'number') return val;
                        if (!val) return 0;
                        return parseFloat(val.toString().replace(/[^\d.-]/g, '')) || 0;
                    };

                    const debit = cleanAmount(l.debit);
                    const credit = cleanAmount(l.credit);

                    // Déclaration et réinitialisation à chaque ligne
                    let matchedTierId = '';
                    let identifiedTierName = (data.tiers || data.fournisseur || "").toUpperCase().trim();
                    
                    if (l.type === 'FOURNISSEUR' || (l.compte && l.compte.toString().startsWith('40')) || (l.compte && l.compte.toString().startsWith('41'))) {
                        const t = TIERS_LIST.find(t => {
                            const tierIntitule = t.intitule.toUpperCase().trim();
                            return identifiedTierName.includes(tierIntitule) || tierIntitule.includes(identifiedTierName);
                        });
                        if (t) {
                            matchedTierId = t.id;
                        }
                    }

                    const matchedPoste = isTreasury ? findTreasuryPost(matchedAccId) : { id: '', text: '' };

                    // Generate treasury options with groups and selection
                    let treasuryOptions = '<option value="">Néant</option>';
                    for (const [cat, posts] of Object.entries(groupedPosts)) {
                        treasuryOptions += `<optgroup label="${cat}">`;
                        posts.forEach(p => {
                            treasuryOptions += `<option value="${p.id}" ${p.id == matchedPoste.id ? 'selected' : ''}>${p.name}</option>`;
                        });
                        treasuryOptions += `</optgroup>`;
                    }

                    // On prépare le bouton "+" pour pré-remplir le modal si le tiers est nouveau
                    const btnPlusAttr = matchedTierId ? '' : `onclick="document.getElementById('intitule_tiers').value = '${identifiedTierName.replace(/'/g, "\\'")}'; document.getElementById('type_tiers').value = '${accCode.startsWith('41') ? 'Client' : 'Fournisseur'}'; document.getElementById('type_tiers').dispatchEvent(new Event('change'));"`;

                    tr.innerHTML = `
                        <td><select class="form-select select2 row-acc"><option value="">Choisir...</option>${GEN_ACCOUNTS.map(a => `<option value="${a.id}" ${a.id == matchedAccId ? 'selected' : ''}>${a.numero_de_compte} - ${a.intitule}</option>`).join('')}</select></td>
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input row-vat-check" style="width: 20px; height: 20px;">
                        </td>
                        <td>
                            <div class="d-flex gap-1 flex-column">
                                <div class="d-flex gap-1">
                                    <select class="form-select select2 row-tier">
                                        <option value="">${matchedTierId ? 'Néant' : 'Nouveau Tiers ?'}</option>
                                        ${TIERS_LIST.map(t => `<option value="${t.id}" ${t.id == matchedTierId ? 'selected' : ''}>${t.numero_de_tiers} - ${t.intitule}</option>`).join('')}
                                    </select>
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-circle" data-bs-toggle="modal" data-bs-target="#createTiersModal" ${btnPlusAttr}><i class="bx bx-plus"></i></button>
                                </div>
                                ${!matchedTierId && identifiedTierName ? `<span class="text-danger small fw-bold px-1 mt-1 animate__animated animate__pulse animate__infinite" style="font-size: 0.75rem;"><i class="bx bx-error-circle me-1"></i>Existe pas, créer</span>` : ''}
                            </div>
                        </td>
                        <td><input type="text" class="form-control form-control-sm row-lib" value="${l.intitule || identifiedTierName || ''}"><div class="small text-muted mt-1 px-1">Pièce: ${data.reference || data.ref || ''} du ${data.date || ''}</div><input type="hidden" class="row-date" value="${data.date || ''}"><input type="hidden" class="row-ref" value="${data.reference || data.ref || ''}"></td>
                        <td><input type="number" class="form-control text-end row-debit" value="${debit}" ${isVAT ? 'readonly style="background-color: #f8f9fa;"' : ''}></td>
                        <td><input type="number" class="form-control text-end row-credit" value="${credit}" ${isVAT ? 'readonly style="background-color: #f8f9fa;"' : ''}></td>
                        <td>
                            <select class="form-select select2 row-poste-treso" ${isTreasury ? '' : 'disabled'}>
                                ${treasuryOptions}
                            </select>
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                ${accCode.startsWith('6') ? `<button class="btn btn-sm btn-icon text-primary" onclick="window.ouvrirVentilation(this)" title="Ventiler"><i class="bx bx-pie-chart-alt"></i></button>` : ''}
                                <button class="btn btn-sm btn-icon text-danger" onclick="this.closest('tr').remove(); window.updateTotals();" title="Supprimer"><i class="bx bx-trash"></i></button>
                            </div>
                        </td>
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
                let totalBase = 0;
                let mainLine = null; // Line to update credit (401 or 571)
                let date = null, ref = null;
                let checkedCount = 0;

                rows.forEach(tr => {
                    const vatCheck = tr.querySelector('.row-vat-check');
                    const accSelect = tr.querySelector('.row-acc');
                    const accCode = accSelect?.options[accSelect.selectedIndex]?.text.split(' ')[0] || "";
                    const debit = parseFloat(tr.querySelector('.row-debit')?.value) || 0;

                    if (vatCheck && vatCheck.checked) {
                        totalBase += debit;
                        checkedCount++;
                    }

                    if (accCode.startsWith('40') || accCode.startsWith('57') || accCode.startsWith('41') || accCode.startsWith('52')) {
                        mainLine = tr;
                    }
                    if (!date) date = tr.querySelector('.row-date')?.value;
                    if (!ref) ref = tr.querySelector('.row-ref')?.value;
                });

                if (checkedCount === 0) {
                    Swal.fire('Info', 'Veuillez d\'abord cocher les lignes sur lesquelles appliquer la TVA.', 'info');
                    return;
                }

                if (totalBase > 0 && mainLine) {
                    const vataAmount = Math.round(totalBase * 0.18);
                    
                    // Récupération des libellés des lignes cochées
                    const checkedLabels = rows.filter(r => r.querySelector('.row-vat-check')?.checked)
                                              .map(r => r.querySelector('.row-lib')?.value || "")
                                              .filter(l => l.length > 0);
                    
                    const sourceLabel = checkedLabels.length > 0 ? checkedLabels.join(' / ') : "SÉLECTION";
                    const vataLabel = "TVA / " + sourceLabel;

                    // Add VAT row
                    const tr = document.createElement('tr');
                    const vataAcc = GEN_ACCOUNTS.find(a => a.numero_de_compte.startsWith('445')) || { id: null, numero_de_compte: '445' };
                    
                    tr.innerHTML = `
                        <td><select class="form-select select2 row-acc"><option value="${vataAcc.id}" selected>${vataAcc.numero_de_compte} - TVA RÉCUPÉRABLE (18%)</option></select></td>
                        <td class="text-center"><i class="bx bx-check text-success"></i></td>
                        <td><div class="d-flex gap-1"><select class="form-select select2 row-tier"><option value="" selected>Néant</option></select></div></td>
                        <td><input type="text" class="form-control form-control-sm row-lib" value="${vataLabel}"><div class="small text-muted mt-1 px-1">Pièce: ${ref} du ${date}</div><input type="hidden" class="row-date" value="${date}"><input type="hidden" class="row-ref" value="${ref}"></td>
                        <td><input type="number" class="form-control text-end row-debit" value="${vataAmount}"></td>
                        <td><input type="number" class="form-control text-end row-credit" value="0"></td>
                        <td><select class="form-select select2 row-poste-treso" disabled><option value="">Néant</option></select></td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <button class="btn btn-sm btn-icon text-danger" onclick="this.closest('tr').remove(); window.updateTotals();"><i class="bx bx-trash"></i></button>
                            </div>
                        </td>
                    `;
                    
                    // Insert VAT before main credit line
                    mainLine.parentNode.insertBefore(tr, mainLine);
                    $(tr).find('.select2').select2({ theme: 'bootstrap4', width: '100%' }).on('change', window.updateTotals);

                    // Update main credit line amount
                    const currentCredit = parseFloat(mainLine.querySelector('.row-credit').value) || 0;
                    mainLine.querySelector('.row-credit').value = currentCredit + vataAmount;

                    // On décoche tout après application
                    document.querySelectorAll('.row-vat-check').forEach(c => c.checked = false);
                    
                    window.updateTotals();
                } else if (!mainLine) {
                    Swal.fire('Erreur', 'Impossible de trouver la ligne de contrepartie (Fournisseur/Banque/Caisse).', 'error');
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
                        code_journal_id: CONTEXT.id_code,
                        ventilations: tr.dataset.ventilations ? JSON.parse(tr.dataset.ventilations) : []
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
                        source: 'scan',
                        ventilations: tr.dataset.ventilations ? JSON.parse(tr.dataset.ventilations) : []
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
                
                // Sélection automatique du premier compte disponible pour gagner du temps
                if (select.options.length > 1) {
                    select.selectedIndex = 1; 
                }

                if (prefix) fetch("/plan_tiers/" + prefix).then(r => r.json()).then(d => document.getElementById('numero_tiers').value = d.numero);
            };

            window.createTiersSimple = (e) => {
                e.preventDefault();
                const btn = document.getElementById('btnCreateTiers');
                const form = document.getElementById('createTiersForm');
                if (!form.checkValidity()) { form.reportValidity(); return; }
                
                const data = { type_de_tiers: document.getElementById('type_tiers').value, compte_general: document.getElementById('compte_general_tiers').value, intitule: document.getElementById('intitule_tiers').value, numero_de_tiers: document.getElementById('numero_tiers').value };
                
                btn.disabled = true; btn.innerText = "Création...";
                fetch('{{ route("plan_tiers.store") }}', { 
                    method: 'POST', 
                    headers: {
                        'Content-Type': 'application/json', 
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }, 
                    body: JSON.stringify(data) 
                }).then(r => r.json()).then(res => {
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
