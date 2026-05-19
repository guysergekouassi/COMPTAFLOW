@include('components.head')

@use('Illuminate\Support\Str')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap');

    body {
        background-color: #f8fafc;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .staging-card {
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);
    }

    .staging-table-container {
        border-radius: 16px;
        overflow: auto;
        border: 1px solid #e2e8f0;
        max-height: 70vh;
    }

    .table-staging thead th {
        background: #f8fafc;
        color: #64748b;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 1rem;
        border-bottom: 2px solid #e2e8f0;
    }

    .table-staging tbody td {
        padding: 0.75rem 1rem;
        font-size: 0.825rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        color: #334155;
    }

    .cell-error {
        background-color: #fef2f2 !important;
        border: 1px solid #fee2e2 !important;
        color: #b91c1c !important;
        position: relative;
    }

    .cell-error::after {
        content: "\f06a";
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 0.7rem;
        opacity: 0.5;
    }

    .status-indicator {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }

    .step-indicator {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.875rem;
    }

    .cursor-pointer { cursor: pointer; }
    .card-filter:hover { transform: translateY(-3px); transition: all 0.2s ease; box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1); }
    .card-filter.active { ring: 2px; ring-color: var(--bs-primary); }
</style>


<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Importation / <span class="text-primary">' . $importTitle . '</span>'])

                <div class="content-wrapper">
                    @include('admin.config.import_staging_content')
            </div>
        </div>
    </div>

    <!-- OVERLAY DE CHARGEMENT -->
    <div id="loadingOverlay" class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm hidden transition-opacity duration-300">
        <div class="bg-white p-8 rounded-[24px] shadow-2xl flex flex-col items-center justify-center max-w-md w-full mx-4 border border-slate-100">
            <div class="relative w-24 h-24 mb-6">
                <div class="absolute inset-0 border-4 border-slate-100 rounded-full"></div>
                <div class="absolute inset-0 border-4 border-primary rounded-full border-t-transparent animate-spin"></div>
                <div class="absolute inset-0 flex items-center justify-center">
                    <i class="fa-solid fa-cloud-arrow-up text-3xl text-primary animate-pulse"></i>
                </div>
            </div>
            <h3 class="text-2xl font-black text-slate-800 mb-2">Migration en cours...</h3>
            <p class="text-slate-500 text-sm mb-6 text-center">Veuillez patienter. Le système traite et sécurise vos données. Ne fermez pas cette page.</p>
            
            <div class="w-64 mx-auto bg-slate-100 rounded-full h-3 overflow-hidden mb-2">
                <div id="loadingProgress" class="bg-primary h-3 rounded-full transition-all duration-300 ease-out relative overflow-hidden" style="width: 0%"></div>
            </div>
            <div class="text-xs font-bold text-slate-600">
                <span id="loadingPercentage">0</span>%
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
        /**
         * Charge la page de staging en AJAX pour une expérience fluide (filtres, pagination)
         */
        function loadStagingPage(url) {
            const container = document.getElementById('staging-dynamic-content');
            if (!container) return;
            
            // Retrait de l'opacité pour que l'action soit INSTANTANÉE (demande utilisateur)
            
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(response => response.text())
                .then(html => {
                    // On utilise le DOMParser pour éviter les erreurs "no parent node" si cliqué plusieurs fois rapidement
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContent = doc.getElementById('staging-dynamic-content');
                    
                    const currentContainer = document.getElementById('staging-dynamic-content');
                    if (newContent && currentContainer) {
                        currentContainer.innerHTML = newContent.innerHTML;
                        window.history.pushState({url: url}, '', url);
                    }
                    
                    // Si des init supplémentaires sont nécessaires, on peut les appeler ici
                    if (typeof initializeStagingEvents === 'function') {
                        initializeStagingEvents();
                    }
                })
                .catch(error => {
                    console.error('Erreur AJAX:', error);
                    window.location.href = url; // Fallback
                });
        }

        window.addEventListener('popstate', function(e) {
            if (e.state && e.state.url) {
                loadStagingPage(e.state.url);
            } else {
                window.location.reload();
            }
        });

        let currentFilter = 'all';

        function filterTable(type, clickedEl) {
            // Cette fonction ne doit plus être utilisée pour le filtrage client
            // Le clic sur les cartes ou la recherche appelle désormais loadStagingPage côté serveur.
            console.warn("filterTable is deprecated. Use loadStagingPage instead.");
        }
        // filterTable has been removed as filtering is now server-side

        function exportErrorsToCSV() {
            window.location.href = "{{ route('admin.import.export_errors', $import->id) }}";
        }

        function selectAndBulkDeleteErrors() {
            Swal.fire({
                title: 'Supprimer TOUTES les erreurs ?',
                text: 'Cette action supprimera toutes les lignes en erreur ({{ $errorCount }} lignes) de cet import.',
                icon: 'warning', showCancelButton: true,
                confirmButtonText: 'Oui, tout supprimer', cancelButtonText: 'Annuler',
                customClass: { confirmButton: 'btn btn-danger rounded-xl px-4 me-2', cancelButton: 'btn btn-label-secondary rounded-xl px-4' },
                buttonsStyling: false,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return fetch("{{ route('admin.import.delete_errors', $import->id) }}", {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                    }).then(response => {
                        if (!response.ok) throw new Error(response.statusText);
                        return response.json();
                    }).catch(error => { Swal.showValidationMessage(`Erreur: ${error}`); });
                }
            }).then(result => {
                if (result.isConfirmed && result.value && result.value.success) {
                    Swal.fire('Supprimé !', result.value.message, 'success').then(() => { window.location.reload(); });
                }
            });
        }

        async function quickCreateAccount(numero, libelle, importId = null) {
            Swal.showLoading();
            let suggestion = numero;
            try {
                const res = await fetch(`/admin/import/suggest-number?type=account&original=${encodeURIComponent(numero)}`);
                const data = await res.json();
                if (data.success) suggestion = data.suggestion;
            } catch(e) {}
            Swal.fire({
                title: 'Création rapide du Compte',
                html: `<div class="mb-3 text-start"><label class="form-label text-xs font-bold text-slate-500">Numéro de compte suggéré</label><input type="text" id="swal-qc-numero" class="form-control font-bold text-primary" value="${suggestion}"><div class="text-muted text-xs mt-1">Original lu : <strong>${numero}</strong></div></div><div class="mb-3 text-start"><label class="form-label text-xs font-bold text-slate-500">Intitulé</label><input type="text" id="swal-qc-libelle" class="form-control" value="${libelle}"></div>`,
                icon: 'info', showCancelButton: true,
                confirmButtonText: '<i class="fa-solid fa-check me-1"></i> Créer', cancelButtonText: 'Annuler',
                customClass: { confirmButton: 'btn btn-primary rounded-xl px-4 me-2', cancelButton: 'btn btn-label-secondary rounded-xl px-4' },
                buttonsStyling: false,
                preConfirm: () => ({ numero: document.getElementById('swal-qc-numero').value, libelle: document.getElementById('swal-qc-libelle').value })
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.showLoading();
                    fetch('/admin/import/quick-account', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ numero_compte: result.value.numero, intitule: result.value.libelle, type_de_compte: 'Bilan', import_id: importId, original_numero: numero })
                    }).then(r => r.json()).then(data => {
                        if (data.success) { Swal.fire({ title: 'Succès', text: data.message, icon: 'success', timer: 1500, showConfirmButton: false }).then(() => window.location.reload()); }
                        else { Swal.fire('Erreur', data.message, 'error'); }
                    }).catch(() => Swal.fire('Erreur', 'Une erreur est survenue.', 'error'));
                }
            });
        }

        async function quickCreateTier(numero, libelle, importId = null) {
            Swal.showLoading();
            let suggestion = numero;
            try {
                const res = await fetch(`/admin/import/suggest-number?type=tier&original=${encodeURIComponent(numero)}`);
                const data = await res.json();
                if (data.success) suggestion = data.suggestion;
            } catch(e) {}
            Swal.fire({
                title: 'Création rapide du Tiers',
                html: `<div class="mb-3 text-start"><label class="form-label text-xs font-bold text-slate-500">Numéro de tiers suggéré</label><input type="text" id="swal-qc-numero" class="form-control font-bold text-primary" value="${suggestion}"><div class="text-muted text-xs mt-1">Original lu : <strong>${numero}</strong></div></div><div class="mb-3 text-start"><label class="form-label text-xs font-bold text-slate-500">Intitulé</label><input type="text" id="swal-qc-libelle" class="form-control" value="${libelle}"></div>`,
                icon: 'info', showCancelButton: true,
                confirmButtonText: '<i class="fa-solid fa-check me-1"></i> Créer', cancelButtonText: 'Annuler',
                customClass: { confirmButton: 'btn btn-primary rounded-xl px-4 me-2', cancelButton: 'btn btn-label-secondary rounded-xl px-4' },
                buttonsStyling: false,
                preConfirm: () => ({ numero: document.getElementById('swal-qc-numero').value, libelle: document.getElementById('swal-qc-libelle').value })
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.showLoading();
                    fetch('/admin/import/quick-tier', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ numero_tiers: result.value.numero, intitule: result.value.libelle, type_de_tiers: 'Client', import_id: importId, original_numero: numero })
                    }).then(r => r.json()).then(data => {
                        if (data.success) { Swal.fire({ title: 'Succès', text: data.message, icon: 'success', timer: 1500, showConfirmButton: false }).then(() => window.location.reload()); }
                        else { Swal.fire('Erreur', data.message, 'error'); }
                    }).catch(() => Swal.fire('Erreur', 'Une erreur est survenue.', 'error'));
                }
            });
        }

        async function quickCreateJournal(code, libelle, importId = null) {
            Swal.showLoading();
            let suggestion = code;
            try {
                const res = await fetch(`/admin/import/suggest-number?type=journal&original=${encodeURIComponent(code)}`);
                const data = await res.json();
                if (data.success) suggestion = data.suggestion;
            } catch(e) {}
            Swal.fire({
                title: 'Création rapide du Journal',
                html: `<div class="mb-3 text-start"><label class="form-label text-xs font-bold text-slate-500">Code Journal suggéré</label><input type="text" id="swal-qc-numero" class="form-control font-bold text-primary" value="${suggestion}"><div class="text-muted text-xs mt-1">Code original lu : <strong>${code}</strong></div></div><div class="mb-3 text-start"><label class="form-label text-xs font-bold text-slate-500">Intitulé</label><input type="text" id="swal-qc-libelle" class="form-control" value="${libelle}"></div>`,
                icon: 'info', showCancelButton: true,
                confirmButtonText: '<i class="fa-solid fa-check me-1"></i> Créer', cancelButtonText: 'Annuler',
                customClass: { confirmButton: 'btn btn-primary rounded-xl px-4 me-2', cancelButton: 'btn btn-label-secondary rounded-xl px-4' },
                buttonsStyling: false,
                preConfirm: () => ({ numero: document.getElementById('swal-qc-numero').value, libelle: document.getElementById('swal-qc-libelle').value })
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.showLoading();
                    fetch('/admin/import/quick-journal', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ code_journal: result.value.numero, intitule: result.value.libelle, type_journal: 'Opérations diverses', import_id: importId, original_numero: code })
                    }).then(r => r.json()).then(data => {
                        if (data.success) { Swal.fire({ title: 'Succès', text: data.message, icon: 'success', timer: 1500, showConfirmButton: false }).then(() => window.location.reload()); }
                        else { Swal.fire('Erreur', data.message, 'error'); }
                    }).catch(() => Swal.fire('Erreur', 'Une erreur est survenue.', 'error'));
                }
            });
        }

        function syncSameColInputs(input) {
            const col = input.dataset.col;
            const value = input.value;
            document.querySelectorAll(`.swal-edit-input[data-col="${col}"]`).forEach(other => {
                if (other !== input) other.value = value;
            });
        }

        // Les fonctions filterTable() et exportErrorsToCSV() ont été remplacées par une logique côté serveur
        // pour gérer les gros volumes de données (33k+ lignes).
        
        function deleteStagingRow(importId, rowIndex) {
            Swal.fire({
                title: 'Supprimer cette ligne ?',
                text: "Cette action retirera définitivement la ligne de l'importation en cours.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Oui, supprimer',
                cancelButtonText: 'Annuler',
                customClass: {
                    confirmButton: 'btn btn-danger rounded-xl px-4 me-2',
                    cancelButton: 'btn btn-label-secondary rounded-xl px-4'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/admin/import/delete-row/${importId}/${rowIndex}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            Swal.fire('Erreur', data.message, 'error');
                        }
                    });
                }
            });
        }

        function addStagingRow(btn) {
            const importId = btn.dataset.importId;
            const mapping = JSON.parse(btn.dataset.mapping);

            let html = '<div class="text-start">';
            Object.entries(mapping).forEach(([fieldKey, colIndex]) => {
                if (fieldKey.toLowerCase().includes('header') || colIndex === null || colIndex === "" || colIndex === "AUTO") return;
                
                let label = fieldKey.replace(/_/g, ' ').toUpperCase();
                html += `<div class="mb-3">
                            <label class="form-label text-xs font-bold text-slate-500">${label}</label>
                            <input type="text" class="form-control swal-add-input" 
                                   data-field="${fieldKey}" 
                                   data-col="${colIndex}" 
                                   value="">
                         </div>`;
            });
            html += '</div>';

            Swal.fire({
                title: 'Ajouter une ligne',
                html: html,
                showCancelButton: true,
                confirmButtonText: 'Ajouter',
                cancelButtonText: 'Annuler',
                customClass: {
                    confirmButton: 'btn btn-primary rounded-xl px-4 me-2',
                    cancelButton: 'btn btn-label-secondary rounded-xl px-4'
                },
                buttonsStyling: false,
                preConfirm: () => {
                    let values = {};
                    let inputs = document.querySelectorAll('.swal-add-input');
                    inputs.forEach(input => {
                        values[input.dataset.col] = input.value;
                    });
                    return values;
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    Swal.showLoading();
                    fetch(`/admin/import/add-row/${importId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ values: result.value })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            window.location.reload();
                        } else {
                            Swal.fire('Erreur', data.message, 'error');
                        }
                    });
                }
            });
        }

        function editStagingRow(btn) {
            const importId = btn.dataset.importId;
            const rowIndex = btn.dataset.rowIndex;
            // recordId variable removed
            const rowData = JSON.parse(btn.dataset.rowData);
            const mapping = JSON.parse(btn.dataset.mapping);

            let html = '<div class="text-start">';
            
            // On crée un champ pour chaque colonne mappée
            Object.entries(mapping).forEach(([fieldKey, colIndex]) => {
                if (fieldKey.toLowerCase().includes('header') || colIndex === null || colIndex === "" || colIndex === "AUTO") return;
                
                let label = fieldKey.replace(/_/g, ' ').toUpperCase();
                let val = rowData[fieldKey] || ""; // Use the mapped fieldkey from row data
                
                html += `<div class="mb-3">
                            <label class="form-label text-xs font-bold text-slate-500">${label}</label>
                            <input type="text" class="form-control swal-edit-input" 
                                   data-field="${fieldKey}" 
                                   data-col="${colIndex}" 
                                   value="${val}"
                                   oninput="syncSameColInputs(this)">
                         </div>`;
            });
            html += '</div>';

            Swal.fire({
                title: 'Modifier la ligne',
                html: html,
                showCancelButton: true,
                confirmButtonText: 'Enregistrer',
                cancelButtonText: 'Annuler',
                customClass: {
                    confirmButton: 'btn btn-primary rounded-xl px-4 me-2',
                    cancelButton: 'btn btn-label-secondary rounded-xl px-4'
                },
                buttonsStyling: false,
                preConfirm: () => {
                    let values = {};
                    let inputs = document.querySelectorAll('.swal-edit-input');
                    if (inputs.length === 0) return null;
                    inputs.forEach(input => {
                        // send both field and col
                        values[input.dataset.col] = input.value;
                        values[input.dataset.field] = input.value;
                    });
                    console.log("Staging Edit - Collected values:", values);
                    return values;
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    Swal.showLoading();
                    fetch(`/admin/import/update-row/${importId}/${rowIndex}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ values: result.value })
                    })
                    .then(async response => {
                        console.log("Staging Edit - Response status:", response.status);
                        const text = await response.text();
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error("Staging Edit - Invalid JSON response:", text);
                            throw new Error(`Réponse serveur invalide (${response.status}).`);
                        }
                    })
                    .then(data => {
                        console.log("Staging Edit - Response data:", data);
                        if (data.success) {
                            window.location.reload();
                        } else {
                            Swal.fire('Erreur', data.message || 'Erreur lors de la mise à jour.', 'error');
                        }
                    })
                    .catch(err => {
                        console.error("Staging Edit - Fetch error:", err);
                        Swal.fire('Erreur', 'Détail : ' + err.message, 'error');
                    });
                }
            });
        }

        function showRowDetails(btn) {
            const data = JSON.parse(btn.dataset.rowData);
            const errors = JSON.parse(btn.dataset.errors);
            let dataHtml = '<div class="text-start">';
            
            dataHtml += '<div class="bg-slate-50 p-4 rounded-2xl mb-4 border border-slate-100 shadow-inner">';
            dataHtml += '<h6 class="font-black text-[10px] uppercase text-slate-400 mb-3 tracking-widest">Fiche de la ligne</h6>';
            
            Object.entries(data).forEach(([key, val]) => {
                // Exclure les champs techniques
                if (val !== null && !key.toLowerCase().includes('header') && !['debit_val', 'credit_val', 'auto_num'].includes(key)) {
                    dataHtml += `<div class="d-flex justify-content-between border-bottom border-slate-100 py-2">
                                    <span class="text-xs font-bold text-slate-500">${key.replace(/_/g, ' ').toUpperCase()}</span>
                                    <span class="text-xs fw-black text-slate-800">${val}</span>
                                 </div>`;
                }
            });
            dataHtml += '</div>';

            if (errors && errors.length > 0) {
                dataHtml += '<div class="p-4 rounded-2xl bg-rose-50 border border-rose-100">';
                dataHtml += '<h6 class="font-black text-[10px] uppercase text-rose-600 mb-3 tracking-widest">Anomalies détectées</h6>';
                dataHtml += '<ul class="ps-4 mb-0">';
                errors.forEach(err => {
                    dataHtml += '<li class="text-rose-700 text-xs font-bold mb-1">' + err + '</li>';
                });
                dataHtml += '</ul></div>';
            } else {
                dataHtml += '<div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 d-flex align-items-center gap-3">';
                dataHtml += '<div class="bg-emerald-500 text-white p-2 rounded-full"><i class="fa-solid fa-check"></i></div>';
                dataHtml += "<div class=\"text-xs font-bold text-emerald-700\">Cette ligne est prête pour l'importation.</div></div>";
            }
            dataHtml += '</div>';

            Swal.fire({
                title: 'Détails de la ligne',
                html: dataHtml,
                icon: (errors && errors.length > 0) ? 'warning' : 'info',
                confirmButtonText: 'Fermer',
                customClass: {
                    confirmButton: 'btn btn-primary rounded-xl px-12 py-3'
                },
                buttonsStyling: false
            });
        }
        // Les fonctions quickCreateAccount, quickCreateTier, quickCreateJournal
        // sont définies dans le premier bloc <script> (avant le body) pour garantir
        // leur disponibilité dès le rendu HTML.

        // Date conversion functions
        let datesConverted = false;

        function toggleDateDisplay() {
            const btn = document.getElementById('toggleDatesBtn');
            
            if (!datesConverted) {
                convertAllDates();
                btn.innerHTML = '<i class="fa-solid fa-hashtag me-1"></i> Afficher les codes';
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-primary');
                datesConverted = true;
            } else {
                restoreAllDates();
                btn.innerHTML = '<i class="fa-solid fa-calendar me-1"></i> Afficher les dates';
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-primary');
                datesConverted = false;
            }
        }

        function convertAllDates() {
            const cells = document.querySelectorAll('.table-staging tbody td');
            cells.forEach(cell => {
                const originalValue = cell.innerText.trim();
                
                if (!cell.hasAttribute('data-original')) {
                    cell.setAttribute('data-original', originalValue);
                }

                // Priorité 1 : Détection DDMMYY (exactement 6 chiffres)
                // Ex: 010126 = 01/01/2026, 050126 = 05/01/2026
                // Ce cas DOIT être traité avant la détection des serials Excel
                // car des valeurs comme 050126 tombent dans la plage des serials Excel (30000-60000)
                if (/^\d{6}$/.test(originalValue)) {
                    const day   = originalValue.substring(0, 2);
                    const month = originalValue.substring(2, 4);
                    const yr2   = parseInt(originalValue.substring(4, 6), 10);
                    // Pivot 70 : 00-69 => 2000-2069, 70-99 => 1970-1999
                    const year  = yr2 < 70 ? 2000 + yr2 : 1900 + yr2;
                    // Vérification basique que c'est une date valide (mois entre 01-12, jour entre 01-31)
                    const dayN = parseInt(day, 10);
                    const monN = parseInt(month, 10);
                    if (monN >= 1 && monN <= 12 && dayN >= 1 && dayN <= 31) {
                        cell.innerHTML = `<span class="text-success fw-bold">${day}/${month}/${year}</span>`;
                        return;
                    }
                }

                // Priorité 2 : Serial Excel (plage 30000-60000, typiquement 1982-2064)
                // On exclut les 6 chiffres déjà traités ci-dessus
                if (isNumeric(originalValue) && originalValue.length !== 6) {
                    const num = parseFloat(originalValue);
                    if (num >= 30000 && num <= 60000) {
                        const dateStr = excelDateToJSDate(num);
                        cell.innerHTML = `<span class="text-info fw-bold">${dateStr}</span>`;
                    }
                }
            });
        }

        function restoreAllDates() {
            const cells = document.querySelectorAll('.table-staging tbody td[data-original]');
            cells.forEach(cell => {
                cell.innerHTML = cell.getAttribute('data-original');
            });
        }

        function isNumeric(n) {
            return !isNaN(parseFloat(n)) && isFinite(n);
        }

        // --- GESTION DE LA SUPPRESSION GROUPÉE ---

        function toggleAllCheckboxes(master) {
            const visibleRows = Array.from(document.querySelectorAll('.staging-row')).filter(row => row.style.display !== 'none');
            visibleRows.forEach(row => {
                const cb = row.querySelector('.row-checkbox');
                if (cb) cb.checked = master.checked;
            });
        }

        function selectAndBulkDeleteErrors() {
            // 1. Sélectionner toutes les lignes en erreur (même cachées par filtre, mais le bouton est explicite)
            const errorCheckboxes = document.querySelectorAll('.staging-row[data-status="error"] .row-checkbox');
            
            if (errorCheckboxes.length === 0) {
                Swal.fire('Info', 'Aucune ligne en erreur à supprimer.', 'info');
                return;
            }

            errorCheckboxes.forEach(cb => cb.checked = true);

            const count = errorCheckboxes.length;
            
            Swal.fire({
                title: 'Suppression Totale',
                text: `Voulez-vous supprimer les ${count} lignes en erreur ?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Oui, supprimer tout',
                cancelButtonText: 'Annuler',
                confirmButtonColor: '#d33',
                customClass: {
                    confirmButton: 'btn btn-danger rounded-xl px-4 me-2',
                    cancelButton: 'btn btn-label-secondary rounded-xl px-4'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    const indices = Array.from(errorCheckboxes).map(cb => cb.dataset.rowIndex);
                    performBulkDelete(indices);
                }
            });
        }

        function performBulkDelete(indices) {
            Swal.fire({
                title: 'Suppression en cours...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch("{{ route('admin.import.delete_rows', $import->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ indices: indices })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Supprimé !',
                        text: data.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Erreur', data.message, 'error');
                }
            })
            .catch(error => {
                console.error("Bulk Delete Error:", error);
                Swal.fire('Erreur', 'Une erreur est survenue lors de la suppression groupée.', 'error');
            });
        }

        function exportErrorsToCSV() {
            const errorRows = document.querySelectorAll('.table-staging tbody tr.row-error, .table-staging tbody tr[data-status="error"]');
            if (errorRows.length === 0) {
                Swal.fire('Info', 'Aucune erreur à exporter.', 'info');
                return;
            }

            let csvContent = "\uFEFF"; // UTF-8 BOM for Excel
            const headers = [];
            document.querySelectorAll('.table-staging thead th').forEach(th => {
                const text = th.innerText.trim();
                // Omit checkbox column, status map, and actions
                if(text !== 'STATUT' && text !== 'ACTIONS' && text !== '') {
                    headers.push('"' + text.replace(/"/g, '""') + '"');
                }
            });
            headers.push('"ANOMALIES"');
            csvContent += headers.join(';') + "\n";

            errorRows.forEach(row => {
                let rowData = [];
                const cells = row.querySelectorAll('td');
                
                cells.forEach(cell => {
                    // Skip columns we don't need
                    if (cell.querySelector('input.row-checkbox') || cell.querySelector('.status-indicator') || cell.querySelector('button[onclick*="deleteStagingRow"]')) {
                        return;
                    }
                    
                    let text = cell.innerText.trim().replace(/"/g, '""');
                    text = text.replace(/\n/g, ' | ');
                    rowData.push('"' + text + '"');
                });
                
                let errorText = "";
                const statusInd = row.querySelector('.status-indicator');
                if(statusInd && statusInd.title) {
                    errorText = statusInd.title.replace(/"/g, '""');
                }
                rowData.push('"' + errorText + '"');
                
                csvContent += rowData.join(';') + "\n";
            });

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            const url = URL.createObjectURL(blob);
            link.setAttribute("href", url);
            link.setAttribute("download", "erreurs_import.csv");
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function excelDateToJSDate(serial) {
            const totalSeconds = (serial - 25569) * 86400;
            const date = new Date(totalSeconds * 1000);
            const offset = date.getTimezoneOffset() * 60 * 1000;
            const finalDate = new Date(date.getTime() + offset); 

            const day = String(finalDate.getDate()).padStart(2, '0');
            const month = String(finalDate.getMonth() + 1).padStart(2, '0');
            const year = finalDate.getFullYear();

            return `${day}/${month}/${year}`;
        }

        // --- Logique pour restreindre à 1 seule checkbox pour "Ajouter une ligne" ---
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.row-checkbox');
            const addBtn = document.getElementById('addStagingRowBtn');
            
            checkboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
                    
                    if (checkedCount > 1) {
                        // Si plus d'une case est cochée, on désactive le bouton ajouter
                        if (addBtn) addBtn.disabled = true;
                    } else {
                        // Si 0 ou 1 case est cochée, on réactive
                        if (addBtn) addBtn.disabled = false;
                    }
                });
            });
            
            // On gère aussi le toggle All
            const masterCb = document.getElementById('masterCheckbox');
            if(masterCb) {
                masterCb.addEventListener('change', function() {
                    setTimeout(() => {
                        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
                        if(addBtn) {
                            addBtn.disabled = checkedCount > 1;
                        }
                    }, 50);
                });
            }
        });

        // OVERLAY DE CHARGEMENT POUR LA MIGRATION FINALE
        const commitForm = document.getElementById('commitForm');
        if (commitForm) {
            commitForm.addEventListener('submit', function(e) {
                if (e.defaultPrevented) return;

                document.getElementById('loadingOverlay').classList.remove('hidden');
                
                let progress = 0;
                const progressBar = document.getElementById('loadingProgress');
                const progressText = document.getElementById('loadingPercentage');
                
                const interval = setInterval(() => {
                    if (progress < 90) {
                        progress += Math.random() * 10;
                    } else if (progress < 99) {
                        progress += Math.random() * 1.5;
                    }
                    if (progress > 99) progress = 99;
                    
                    const currentProgress = Math.floor(progress);
                    progressBar.style.width = currentProgress + '%';
                    progressText.innerText = currentProgress;
                }, 500);
            });
        }
    </script>
</body>
</html>
