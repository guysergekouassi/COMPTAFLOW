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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Defined early so onclick handlers work even if the rest of the page is slow to load
    let currentFilter = 'all';
    function filterTable(type, clickedEl) {
        if (type) currentFilter = type;
        const searchText = (document.getElementById('stagingSearch') || {value:''}).value.toLowerCase();
        const rows = document.querySelectorAll('.table-staging tbody tr');
        rows.forEach(row => {
            const rowStatus = row.classList.contains('row-valid') ? 'valid'
                : (row.classList.contains('row-error') ? 'error'
                : (row.classList.contains('row-warning') ? 'ignored' : ''));
            let textMatch = true;
            if (searchText) {
                const targets = row.querySelectorAll('.search-target');
                textMatch = Array.from(targets).some(td => td.textContent.toLowerCase().includes(searchText));
            }
            let statusMatch = true;
            if (currentFilter === 'valid') statusMatch = (rowStatus === 'valid');
            else if (currentFilter === 'error') statusMatch = (rowStatus === 'error');
            row.style.display = (textMatch && statusMatch) ? '' : 'none';
        });
        if (type) {
            document.querySelectorAll('.card-filter').forEach(c => c.classList.remove('active','border-primary'));
            if (clickedEl && clickedEl.classList) clickedEl.classList.add('active','border-primary');
        }
    }
    function filterTableDebounced() { clearTimeout(window._ft); window._ft = setTimeout(() => filterTable(), 300); }
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
            if (result.isConfirmed && result.value.success) {
                Swal.fire('SupprimÃ© !', result.value.message, 'success').then(() => { window.location.reload(); });
            }
        });
    }
    function editStagingRow(btn) {
        const importId = btn.dataset.importId;
        const rowIndex = btn.dataset.rowIndices || btn.dataset.rowIndex;
        const rowData = JSON.parse(btn.dataset.rowData);
        const mapping = JSON.parse(btn.dataset.mapping);
        let html = '<div class="text-start">';
        Object.entries(mapping).forEach(([fieldKey, colIndex]) => {
            if (fieldKey.toLowerCase().includes('header') || colIndex === null || colIndex === '' || colIndex === 'AUTO') return;
            let label = fieldKey.replace(/_/g, ' ').toUpperCase();
            let val = rowData[fieldKey] || '';
            html += `<div class="mb-3"><label class="form-label text-xs font-bold text-slate-500">${label}</label><input type="text" class="form-control swal-edit-input" data-field="${fieldKey}" data-col="${colIndex}" value="${val}" oninput="syncSameColInputs(this)"></div>`;
        });
        html += '</div>';
        Swal.fire({
            title: 'Modifier la ligne', html: html,
            showCancelButton: true, confirmButtonText: 'Enregistrer', cancelButtonText: 'Annuler',
            customClass: { confirmButton: 'btn btn-primary rounded-xl px-4 me-2', cancelButton: 'btn btn-label-secondary rounded-xl px-4' },
            buttonsStyling: false,
            preConfirm: () => {
                let values = {};
                document.querySelectorAll('.swal-edit-input').forEach(input => { values[input.dataset.col] = input.value; values[input.dataset.field] = input.value; });
                return values.length === 0 ? null : values;
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                Swal.showLoading();
                fetch(`/admin/import/update-row/${importId}/${rowIndex}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '' },
                    body: JSON.stringify({ values: result.value })
                }).then(r => r.json()).then(data => {
                    if (data.success) { window.location.reload(); } else { Swal.fire('Erreur', data.message || 'Erreur lors de la mise Ã  jour.', 'error'); }
                }).catch(err => { Swal.fire('Erreur', err.message, 'error'); });
            }
        });
    }
    function showRowDetails(btn) {
        const data = JSON.parse(btn.dataset.rowData);
        const errors = JSON.parse(btn.dataset.errors);
        let dataHtml = '<div class="text-start"><div class="bg-slate-50 p-4 rounded-2xl mb-4 border border-slate-100">';
        Object.entries(data).forEach(([key, val]) => {
            if (val !== null && !key.toLowerCase().includes('header') && !['debit_val','credit_val','auto_num'].includes(key)) {
                dataHtml += `<div class="d-flex justify-content-between border-bottom py-2"><span class="text-xs font-bold text-slate-500">${key.replace(/_/g,' ').toUpperCase()}</span><span class="text-xs fw-black text-slate-800">${val}</span></div>`;
            }
        });
        dataHtml += '</div>';
        if (errors && errors.length > 0) {
            dataHtml += '<div class="p-4 rounded-2xl bg-rose-50 border border-rose-100"><ul class="ps-4 mb-0">';
            errors.forEach(err => { dataHtml += '<li class="text-rose-700 text-xs font-bold mb-1">' + err + '</li>'; });
            dataHtml += '</ul></div>';
        } else {
            dataHtml += "<div class=\"p-4 rounded-2xl bg-emerald-50 border border-emerald-100\"><div class=\"text-xs font-bold text-emerald-700\">Cette ligne est prête pour l'importation.</div></div>";
        }
        dataHtml += '</div>';
        Swal.fire({ title: 'Détails de la ligne', html: dataHtml, icon: (errors && errors.length > 0) ? 'warning' : 'info', confirmButtonText: 'Fermer', customClass: { confirmButton: 'btn btn-primary rounded-xl px-12 py-3' }, buttonsStyling: false });
    }
    function addStagingRow(btn) {
        const importId = btn.dataset.importId;
        const mapping = JSON.parse(btn.dataset.mapping);
        
        let prefillData = {};
        const checkedBox = document.querySelector('.row-checkbox:checked');
        if (checkedBox) {
            const tr = checkedBox.closest('tr');
            if (tr) {
                const infoBtn = tr.querySelector('[data-row-data]');
                if (infoBtn) {
                    try {
                        prefillData = JSON.parse(infoBtn.getAttribute('data-row-data'));
                    } catch(e) {}
                }
            }
        }

        let html = '<div class="text-start">';
        Object.entries(mapping).forEach(([fieldKey, colIndex]) => {
            if (fieldKey.toLowerCase().includes('header') || colIndex === null || colIndex === "" || colIndex === "AUTO") return;
            let label = fieldKey.replace(/_/g, ' ').toUpperCase();
            
            // Logique de pré-remplissage
            let value = "";
            if (prefillData && prefillData[colIndex] !== undefined) {
                // Sauf les montants
                if (!['debit', 'credit', 'montant'].includes(fieldKey.toLowerCase())) {
                    value = prefillData[colIndex];
                }
            }

            html += `<div class="mb-3"><label class="form-label text-xs font-bold text-slate-500">${label}</label><input type="text" class="form-control swal-add-input" data-field="${fieldKey}" data-col="${colIndex}" value="${value ? value.replace(/"/g, '&quot;') : ''}"></div>`;
        });
        html += '</div>';
        Swal.fire({
            title: 'Ajouter une ligne', html: html,
            showCancelButton: true, confirmButtonText: 'Ajouter', cancelButtonText: 'Annuler',
            customClass: { confirmButton: 'btn btn-primary rounded-xl px-4 me-2', cancelButton: 'btn btn-label-secondary rounded-xl px-4' },
            buttonsStyling: false,
            preConfirm: () => {
                let values = {};
                document.querySelectorAll('.swal-add-input').forEach(input => { values[input.dataset.col] = input.value; });
                return values;
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                Swal.showLoading();
                const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                fetch(`/admin/import/add-row/${importId}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfMeta ? csrfMeta.content : '' },
                    body: JSON.stringify({ values: result.value })
                }).then(r => r.json()).then(data => {
                    if (data.success) { window.location.reload(); } else { Swal.fire('Erreur', data.message, 'error'); }
                });
            }
        });
    }

    // === CRÉATION RAPIDE D'ENTITÉS MANQUANTES ===
    // Ces fonctions doivent être ici (premier bloc) car elles sont appelées
    // depuis des boutons onclick dans les alertes HTML en haut de page.

    async function quickCreateAccount(numero, libelle) {
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
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                    body: JSON.stringify({ numero_compte: result.value.numero, intitule: result.value.libelle, type_de_compte: 'Bilan' })
                }).then(r => r.json()).then(data => {
                    if (data.success) { Swal.fire({ title: 'Succès', text: data.message, icon: 'success', timer: 1500, showConfirmButton: false }).then(() => window.location.reload()); }
                    else { Swal.fire('Erreur', data.message, 'error'); }
                }).catch(() => Swal.fire('Erreur', 'Une erreur est survenue.', 'error'));
            }
        });
    }

    async function quickCreateTier(numero, libelle) {
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
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                    body: JSON.stringify({ numero_tiers: result.value.numero, intitule: result.value.libelle, type_de_tiers: 'Client' })
                }).then(r => r.json()).then(data => {
                    if (data.success) { Swal.fire({ title: 'Succès', text: data.message, icon: 'success', timer: 1500, showConfirmButton: false }).then(() => window.location.reload()); }
                    else { Swal.fire('Erreur', data.message, 'error'); }
                }).catch(() => Swal.fire('Erreur', 'Une erreur est survenue.', 'error'));
            }
        });
    }

    async function quickCreateJournal(code, libelle) {
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
                    method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                    body: JSON.stringify({ code_journal: result.value.numero, intitule: result.value.libelle, type_journal: 'Opérations diverses' })
                }).then(r => r.json()).then(data => {
                    if (data.success) { Swal.fire({ title: 'Succès', text: data.message, icon: 'success', timer: 1500, showConfirmButton: false }).then(() => window.location.reload()); }
                    else { Swal.fire('Erreur', data.message, 'error'); }
                }).catch(() => Swal.fire('Erreur', 'Une erreur est survenue.', 'error'));
            }
        });
    }
</script>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Importation / <span class="text-primary">' . $importTitle . '</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show rounded-[20px] mb-4 border-0 shadow-sm" role="alert">
                                <div class="d-flex align-items-center">
                                    <div class="bg-danger/10 p-2 rounded-lg me-3">
                                        <i class="fa-solid fa-triangle-exclamation text-danger fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="alert-heading font-black mb-1">Erreur d'importation</h6>
                                        <p class="mb-0 text-sm font-medium">{{ session('error') }}</p>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(!empty($missingAccounts) || !empty($missingJournals) || !empty($missingTiers))
                            <div class="alert alert-warning rounded-[20px] mb-4 shadow-sm border border-warning-subtle">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="fa-solid fa-exclamation-triangle text-warning fs-3 me-3"></i>
                                    <h5 class="mb-0 fw-bold">Entités Manquantes Détectées</h5>
                                </div>
                                <p class="text-sm mb-3">Certaines données font référence à des comptes, journaux ou tiers inexistants. Cliquez sur les boutons ci-dessous pour les créer rapidement avec des valeurs par défaut.</p>
                                
                                <div class="d-flex flex-wrap gap-2">
                                    @foreach($missingJournals ?? [] as $code => $intitule)
                                        <button class="btn btn-sm btn-outline-danger rounded-pill" onclick="quickCreateJournal('{{ $code }}', '{{ addslashes($intitule) }}')" title="Créer automatiquement le journal {{ $code }}">
                                            <i class="fa-solid fa-book me-1"></i> Créer Journal : {{ $code }}
                                        </button>
                                    @endforeach

                                    @foreach($missingAccounts ?? [] as $num => $intitule)
                                        <button class="btn btn-sm btn-outline-danger rounded-pill" onclick="quickCreateAccount('{{ $num }}', '{{ addslashes($intitule) }}')" title="Créer automatiquement le compte {{ $num }}">
                                            <i class="fa-solid fa-hashtag me-1"></i> Créer Compte : {{ $num }}
                                        </button>
                                    @endforeach

                                    @foreach($missingTiers ?? [] as $num => $intitule)
                                        <button class="btn btn-sm btn-outline-danger rounded-pill" onclick="quickCreateTier('{{ $num }}', '{{ addslashes($intitule) }}')" title="Créer automatiquement le tiers {{ $num }}">
                                            <i class="fa-solid fa-user-tag me-1"></i> Créer Tiers : {{ $num }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show rounded-[20px] mb-4 border-0 shadow-sm" role="alert">
                                <div class="d-flex align-items-center">
                                    <div class="bg-success/10 p-2 rounded-lg me-3">
                                        <i class="fa-solid fa-circle-check text-success fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="alert-heading font-black mb-1">SuccÃ¨s !</h6>
                                        <p class="mb-0 text-sm font-medium">{{ session('success') }}</p>
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if($errorCount > 0)
                        <div class="row mb-6">
                            <div class="col-12">
                                <div class="bg-white p-6 rounded-[24px] border border-rose-200 bg-rose-50/20 shadow-sm">
                                    <h5 class="font-black mb-4 d-flex align-items-center gap-2 text-rose-800">
                                        <i class="fa-solid fa-clipboard-check"></i> Rapport de Validation & Manuel de Correction
                                    </h5>
                                    
                                    <div class="row g-4">
                                        @php
                                            $rowsForTotals = collect($rowsWithStatus)->filter(fn($r) => ($r['status'] ?? null) !== 'ignored');
                                            $totalDebit = $rowsForTotals->sum('debit');
                                            $totalCredit = $rowsForTotals->sum('credit');
                                            $balance = abs($totalDebit - $totalCredit);
                                        @endphp

                                        @if($balance > 0.01)
                                        <div class="col-md-4">
                                            <div class="bg-white p-4 rounded-2xl border border-rose-100 shadow-sm h-100">
                                                <div class="d-flex align-items-center gap-3 mb-2">
                                                    <div class="bg-rose-100 text-rose-600 p-2 rounded-lg">
                                                        <i class="fa-solid fa-scale-unbalanced"></i>
                                                    </div>
                                                    <h6 class="font-bold mb-0">DÃ©sÃ©quilibre ({{ number_format($balance, 0, ',', ' ') }})</h6>
                                                </div>
                                                <p class="text-xs text-slate-500 mb-3">Le total débit ne correspond pas au total crédit.</p>
                                                <div class="alert alert-danger py-2 px-3 text-[10px] mb-0 border-0">
                                                    VÃ©rifiez les montants ou les lignes manquantes.
                                                </div>
                                            </div>
                                        </div>
                                        @endif

                                        <div class="col-md-4">
                                            <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm h-100">
                                                <div class="d-flex align-items-center gap-3 mb-2">
                                                    <div class="bg-blue-100 text-blue-600 p-2 rounded-lg">
                                                        <i class="fa-solid fa-info-circle"></i>
                                                    </div>
                                                    <h6 class="font-bold mb-0">Diagnostic</h6>
                                                </div>
                                                <p class="text-xs text-slate-500 mb-2">Utilisez le bouton <i class="fa-solid fa-eye text-info"></i> pour voir les erreurs dÃ©taillÃ©es par ligne.</p>
                                                <p class="text-[10px] text-slate-400 italic">Corrigez les comptes inconnus via le bouton modifier <i class="fa-solid fa-pen text-primary"></i>.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="row mb-6">
                            <div class="col-12">
                                <div class="bg-emerald-50 p-6 rounded-[24px] border border-emerald-200 shadow-sm d-flex align-items-center gap-4">
                                    <div class="bg-emerald-500 text-white p-3 rounded-full pulse">
                                        <i class="fa-solid fa-check-double fa-xl"></i>
                                    </div>
                                    <div>
                                        <h5 class="font-black text-emerald-900 mb-0">Ã‰critures Ã‰quilibrÃ©es et Valides !</h5>
                                        <p class="text-emerald-700 text-sm mb-0">Toutes les lignes sont conformes aux rÃ¨gles comptables.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="row mb-6">
                            <div class="col-md-3">
                                <div class="bg-emerald-50 p-4 rounded-2xl border {{ $statusFilter == 'valid' ? 'border-emerald-500 active' : 'border-emerald-100' }} cursor-pointer card-filter" onclick="window.location.href='{{ request()->fullUrlWithQuery(['status' => 'valid', 'page' => 1]) }}'">
                                    <div class="text-xs font-bold text-emerald-600 uppercase mb-1">Lignes Valides</div>
                                    <div class="h4 font-black text-emerald-700 mb-0">{{ $validCount }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="bg-rose-50 p-4 rounded-2xl border {{ $statusFilter == 'error' ? 'border-rose-500 active' : 'border-rose-100' }} cursor-pointer card-filter" onclick="window.location.href='{{ request()->fullUrlWithQuery(['status' => 'error', 'page' => 1]) }}'">
                                    <div class="text-xs font-bold text-rose-600 uppercase mb-1">Erreurs détectées</div>
                                    <div class="h4 font-black text-rose-700 mb-0">{{ $errorCount }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex gap-3 h-100">
                                    <div class="bg-white p-4 rounded-2xl border {{ $statusFilter == 'all' ? 'border-primary active' : 'border-slate-100' }} cursor-pointer card-filter flex-grow-1" onclick="window.location.href='{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => 1]) }}'">
                                        <div class="text-xs font-bold text-slate-400 uppercase mb-2">Tout afficher</div>
                                        <div class="d-flex gap-4">
                                            <div class="text-xs d-flex align-items-center gap-2">
                                                <span class="status-indicator bg-emerald-500"></span> Prêt à l'import
                                            </div>
                                            <div class="text-xs d-flex align-items-center gap-2">
                                                <span class="status-indicator bg-rose-500"></span> Erreur bloquante
                                            </div>
                                            <div class="text-xs d-flex align-items-center gap-2">
                                                <span class="status-indicator bg-warning"></span> Attention / Hors OHADA
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-white p-4 rounded-2xl border border-slate-100 d-flex align-items-center" style="width: 300px;">
                                        <div class="input-group input-group-merge border-0 bg-slate-50 rounded-xl px-2">
                                            <span class="input-group-text border-0 bg-transparent"><i class="fa-solid fa-magnifying-glass text-slate-400"></i></span>
                                            <input type="text" id="stagingSearch" class="form-control border-0 bg-transparent ps-0" placeholder="Filtrer numéro / libellé..." value="{{ $searchFilter }}" onkeyup="if(event.key === 'Enter') window.location.href='{{ request()->fullUrlWithQuery(['search' => '']) }}'.replace('search=', 'search=' + encodeURIComponent(this.value))">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4 d-flex justify-content-center gap-2">
                            <button type="button" id="addStagingRowBtn" class="btn btn-sm btn-primary" 
                                    data-import-id="{{ $import->id }}"
                                    data-mapping="{{ json_encode($mapping) }}"
                                    onclick="addStagingRow(this)">
                                <i class="fa-solid fa-plus-circle me-1"></i> Ajouter une ligne
                            </button>
                            @if($errorCount > 0)
                            <button type="button" id="bulkDeleteErrorsBtn" class="btn btn-sm btn-outline-danger" onclick="selectAndBulkDeleteErrors()">
                                <i class="fa-solid fa-trash-can me-1"></i> Suppression Totale ({{ $errorCount }} erreurs)
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="exportErrorsToCSV()">
                                <i class="fa-solid fa-file-excel me-1"></i> Exporter Erreurs
                            </button>
                            @endif
                        </div>

                        <div class="staging-card p-0 mb-6">
                            <div class="staging-table-container" style="overflow: hidden;">
                                <div class="table-responsive" style="max-height: 600px; overflow-y: auto; overflow-x: auto;">
                                    <table class="table table-staging mb-0" style="min-width: 1200px;">
                                        <thead>
                                            <tr>
                                                <th style="width: 40px;" class="text-center">
                                                    <input type="checkbox" id="masterCheckbox" class="form-check-input" onclick="toggleAllCheckboxes(this)">
                                                </th>
                                                <th style="width: 50px;">STATUT</th>
                                                @if($import->type == 'initial')
                                                    <th>NUMÉRO DE COMPTE</th>
                                                    <th>INTITULÉ DU COMPTE</th>
                                                @elseif($import->type == 'journals')
                                                    <th>CODE JOURNAL</th>
                                                    <th>INTITULÉ DU JOURNAL</th>
                                                    <th>TYPE</th>
                                                    <th>COMPTE</th>
                                                    <th>ANALYTIQUE</th>
                                                    <th>RAPPROCHEMENT</th>
                                                @elseif($import->type == 'tiers')
                                                    <th>NÂ° TIERS / IDENTIFIANT</th>
                                                    <th>NOM / RAISON SOCIALE</th>
                                                    <th>CATÉGORIE</th>
                                                    <th>COMPTE GÉNÉRAL</th>
                                                @else
                                                    <th>N° SAISIE</th>
                                                    <th>ÉQUILIBRE</th>
                                                    <th>DATE</th>
                                                    <th>JOURNAL</th>
                                                    <th>RÉFÉRENCE</th>
                                                    <th>COMPTE</th>
                                                    <th>TIERS</th>
                                                    <th>LIBELLÉ</th>
                                                    <th class="text-end">DÉBIT</th>
                                                <th class="text-end">CRÉDIT</th>
                                                @endif
                                                <th class="text-center">ACTIONS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $mapping = $import->mapping;
                                            @endphp
 
                                            @foreach($rowsWithStatusPaged as $rowIndex => $row)
                                                <tr class="staging-row {{ $row['status'] == 'valid' ? 'row-valid' : ($row['status'] == 'ignored' ? 'row-warning' : 'row-error') }}" data-status="{{ $row['status'] }}">
                                                    <td class="text-center">
                                                        <input type="checkbox" class="row-checkbox form-check-input" data-row-index="{{ $row['index'] ?? $rowIndex }}">
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="status-indicator {{ $row['status'] == 'valid' ? 'bg-emerald-500' : ($row['status'] == 'ignored' ? 'bg-warning' : 'bg-rose-500') }}" 
                                                               title="{{ count($row['errors'] ?? []) ? implode(', ', $row['errors']) : 'Erreur de validation inconnue' }}"></span>
                                                    </td>
                                                     
                                                     @if($import->type == 'initial')
                                                         <td class="@if($row['status'] == 'error') cell-error @endif search-target">
                                                             {{ $row['data']['numero_de_compte'] ?? '-' }}
                                                         </td>
                                                         <td class="search-target">{{ $row['data']['intitule'] ?? '-' }}</td>
                                                     @elseif($import->type == 'journals')
                                                         <td class="@if($row['status'] == 'error') cell-error @endif search-target">
                                                             {{ $row['data']['code_journal'] ?? '-' }}
                                                         </td>
                                                         <td class="search-target">{{ $row['data']['intitule'] ?? '-' }}</td>
                                                         <td>
                                                             <span class="badge bg-label-info">
                                                                 {{ $row['data']['type'] ?? 'Achats' }}
                                                             </span>
                                                         </td>
                                                         <td>{{ $row['data']['compte_de_tresorerie'] ?? '-' }}</td>
                                                         <td>
                                                             <span class="badge {{ !empty($row['data']['traitement_analytique']) && strtolower($row['data']['traitement_analytique']) == 'oui' ? 'bg-label-success' : 'bg-label-secondary' }}">
                                                                 {{ !empty($row['data']['traitement_analytique']) && strtolower($row['data']['traitement_analytique']) == 'oui' ? 'OUI' : 'NON' }}
                                                             </span>
                                                         </td>
                                                         <td>{{ $row['data']['rapprochement_sur'] ?? '-' }}</td>
                                                     @elseif($import->type == 'tiers')
                                                         <td class="@if($row['status'] == 'error' && empty($row['data']['numero_de_tiers']) && empty($row['data']['auto_num'])) cell-warning @endif search-target">
                                                             @if(!empty($row['data']['auto_num']))
                                                                 <span class="text-primary font-black"><i class="fa-solid fa-magic me-1"></i> {{ $row['data']['auto_num'] }}</span>
                                                             @elseif(!empty($row['data']['numero_de_tiers']))
                                                                 {{ $row['data']['numero_de_tiers'] }}
                                                             @else
                                                                 <span class="badge bg-label-warning italic text-[10px]">Sera auto-gÃ©nÃ©rÃ©</span>
                                                             @endif
                                                         </td>
                                                         <td class="fw-bold search-target">{{ $row['data']['intitule'] ?? '-' }}</td>
                                                         <td>
                                                             <span class="badge bg-label-primary">
                                                                 {{ $row['data']['type_de_tiers'] ?? 'Client' }}
                                                             </span>
                                                         </td>
                                                         <td class="font-mono">{{ $row['data']['compte_general'] ?? '-' }}</td>
                                                     @else
                                                        <td class="search-target">{{ $row['data']['n_saisie'] ?? '-' }}</td>
                                                        <td>
                                                            @php $gd = $row['group_diff'] ?? null; @endphp
                                                            @if($row['status'] == 'ignored')
                                                                <span class="badge bg-label-warning">IgnorÃ©e</span>
                                                            @elseif($gd === null)
                                                                <span class="badge bg-label-secondary">-</span>
                                                            @elseif(abs((float)$gd) <= 0.01)
                                                                <span class="badge bg-label-success">Ã‰quilibrÃ©</span>
                                                            @else
                                                                <span class="badge bg-label-danger">DÃ©sÃ©quilibrÃ© ({{ number_format(abs((float)$gd), 0, ',', ' ') }})</span>
                                                            @endif

                                                            @if(($row['group_debit'] ?? null) !== null && ($row['group_credit'] ?? null) !== null)
                                                                <div class="text-[10px] text-slate-400 italic">D={{ number_format((float)$row['group_debit'], 0, ',', ' ') }} / C={{ number_format((float)$row['group_credit'], 0, ',', ' ') }}</div>
                                                            @endif
                                                        </td>
                                                        <td>{{ $row['data']['jour'] ?? '-' }}</td>
                                                        <td>
                                                            <div class="fw-bold">{{ $row['data']['journal'] ?? '-' }}</div>
                                                            @if(!empty($row['data']['code_original_journal']))
                                                                <div class="text-[10px] text-slate-400 italic">{{ $row['data']['code_original_journal'] }}</div>
                                                            @endif
                                                        </td>
                                                        <td>{{ $row['data']['reference'] ?? '-' }}</td>
                                                        <td class="@if(in_array('error', $row['errors']) || str_contains(implode(' ', $row['errors']), 'Compte')) cell-error @endif search-target" 
                                                            title="{{ implode(', ', array_filter($row['errors'], fn($e) => str_contains($e, 'Compte'))) }}">
                                                            <div class="fw-bold">{{ $row['data']['compte'] ?? '-' }}</div>
                                                             @if(!empty($row['data']['numero_original_compte']))
                                                                 <div class="text-[10px] text-slate-400 italic">{{ $row['data']['numero_original_compte'] }}</div>
                                                             @endif
                                                        </td>
                                                        <td class="@if(str_contains(implode(' ', $row['errors']), 'Tiers inconnu')) cell-error @endif">
                                                            <div class="fw-bold">{{ $row['data']['tiers'] ?? '-' }}</div>
                                                             @if(!empty($row['data']['numero_original_tiers']))
                                                                 <div class="text-[10px] text-slate-400 italic">{{ $row['data']['numero_original_tiers'] }}</div>
                                                             @endif
                                                        </td>
                                                        <td class="search-target">{{ Str::limit($row['data']['libelle'] ?? '-', 30) }}</td>
                                                        <td class="text-end fw-bold">{{ number_format($row['debit'], 0, ',', ' ') }}</td>
                                                        <td class="text-end fw-bold">{{ number_format($row['credit'], 0, ',', ' ') }}</td>
                                                     @endif

                                                    <td class="text-center">
                                                        <div class="d-flex justify-content-center">
                                                            @if(!in_array($import->type, ['initial', 'journals']) && $row['status'] == 'error' && str_contains(implode(' ', $row['errors']), 'Compte'))
                                                                <button class="btn btn-icon btn-sm btn-label-success rounded-pill me-1" 
                                                                        data-compte="{{ $row["data"]["compte"] ?? '' }}"
                                                                        data-libelle="{{ $row["data"]["libelle"] ?? '' }}"
                                                                        onclick="quickCreateAccount(this)"
                                                                        title="CrÃ©er ce compte Ã  la volÃ©e">
                                                                    <i class="fa-solid fa-plus-circle"></i>
                                                                </button>
                                                            @endif
                                                            <button class="btn btn-icon btn-sm btn-label-primary rounded-pill me-1" 
                                                                    data-import-id="{{ $import->id }}"
                                                                    data-row-index="{{ $row['index'] }}"
                                                                    data-row-data="{{ json_encode($row['data']) }}"
                                                                    data-mapping="{{ json_encode($mapping) }}"
                                                                    onclick="editStagingRow(this)"
                                                                    title="Modifier cette ligne">
                                                                <i class="fa-solid fa-pen"></i>
                                                            </button>
                                                            <button class="btn btn-icon btn-sm btn-label-info rounded-pill" 
                                                                    data-row-data="{{ json_encode($row['data']) }}"
                                                                    data-errors="{{ json_encode($row['errors']) }}"
                                                                    onclick="showRowDetails(this)"
                                                                    title="Voir les dÃ©tails">
                                                                <i class="fa-solid fa-eye"></i>
                                                            </button>
                                                            <button class="btn btn-icon btn-sm btn-label-danger rounded-pill ms-1" 
                                                                    onclick="deleteStagingRow({{ $import->id }}, {{ $row['index'] }})"
                                                                    title="Supprimer cette ligne de l'import">
                                                                <i class="fa-solid fa-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- PAGINATION --}}
                            @if($totalPages > 1)
                            <div class="d-flex justify-content-between align-items-center mt-4 px-2">
                                <div class="text-slate-500 text-sm">
                                    Page <strong>{{ $currentPage }}</strong> / {{ $totalPages }} &nbsp;&mdash;&nbsp;
                                    Affichage lignes {{ (($currentPage-1)*$perPage)+1 }} â€“ {{ min($currentPage*$perPage, $totalRows) }}
                                    sur <strong>{{ $totalRows }}</strong>
                                </div>
                                <div class="d-flex gap-2">
                                    @if($currentPage > 1)
                                        <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}" class="btn btn-sm btn-outline-secondary rounded-xl px-4">
                                            <i class="fa-solid fa-chevron-left me-1"></i> PrÃ©c.
                                        </a>
                                    @endif
                                    @for($p = max(1, $currentPage - 2); $p <= min($totalPages, $currentPage + 2); $p++)
                                        <a href="{{ request()->fullUrlWithQuery(['page' => $p]) }}"
                                           class="btn btn-sm {{ $p == $currentPage ? 'btn-primary' : 'btn-outline-secondary' }} rounded-xl px-3">
                                            {{ $p }}
                                        </a>
                                    @endfor
                                    @if($currentPage < $totalPages)
                                        <a href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}" class="btn btn-sm btn-outline-secondary rounded-xl px-4">
                                            Suiv. <i class="fa-solid fa-chevron-right ms-1"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                            @endif

                        <div class="d-flex justify-content-between align-items-center bg-white p-6 rounded-[24px] shadow-sm border border-slate-100">
                            <div>
                                <a href="{{ route('admin.import.mapping', $import->id) }}" class="btn btn-outline-secondary rounded-xl px-6 py-3">
                                    <i class="fa-solid fa-arrow-left me-2"></i> Retour au mapping
                                </a>
                            </div>
                            <div class="d-flex gap-3">
                                <form action="{{ route('admin.import.cancel', $import->id) }}" method="POST" onsubmit="return confirm('Voulez-vous vraiment annuler cette importation ? Toutes les donnÃ©es temporaires seront supprimÃ©es.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-label-danger rounded-xl px-6 py-3 border-0">
                                        <i class="fa-solid fa-trash me-2"></i> Annuler l'import
                                    </button>
                                </form>
                                <form action="{{ route('admin.import.commit', $import->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary rounded-xl px-10 py-3 font-bold shadow-lg shadow-primary/20">
                                        <i class="fa-solid fa-cloud-arrow-down me-2"></i> Lancer la migration finale
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                    @include('components.footer')
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
        function syncSameColInputs(input) {
            const col = input.dataset.col;
            const value = input.value;
            document.querySelectorAll(`.swal-edit-input[data-col="${col}"]`).forEach(other => {
                if (other !== input) other.value = value;
            });
        }

        // Les fonctions filterTable() et exportErrorsToCSV() ont Ã©tÃ© remplacÃ©es par une logique cÃ´tÃ© serveur
        // pour gÃ©rer les gros volumes de donnÃ©es (33k+ lignes).
        
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
                    fetch(`/admin/config/import-staging/delete-row/${importId}/${rowIndex}`, {
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
            const rowIndex = btn.dataset.rowIndices || btn.dataset.rowIndex;
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
                            throw new Error(`RÃ©ponse serveur invalide (${response.status}).`);
                        }
                    })
                    .then(data => {
                        console.log("Staging Edit - Response data:", data);
                        if (data.success) {
                            window.location.reload();
                        } else {
                            Swal.fire('Erreur', data.message || 'Erreur lors de la mise Ã  jour.', 'error');
                        }
                    })
                    .catch(err => {
                        console.error("Staging Edit - Fetch error:", err);
                        Swal.fire('Erreur', 'DÃ©tail : ' + err.message, 'error');
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

                // PrioritÃ© 1 : DÃ©tection DDMMYY (exactement 6 chiffres)
                // Ex: 010126 = 01/01/2026, 050126 = 05/01/2026
                // Ce cas DOIT Ãªtre traitÃ© avant la dÃ©tection des serials Excel
                // car des valeurs comme 050126 tombent dans la plage des serials Excel (30000-60000)
                if (/^\d{6}$/.test(originalValue)) {
                    const day   = originalValue.substring(0, 2);
                    const month = originalValue.substring(2, 4);
                    const yr2   = parseInt(originalValue.substring(4, 6), 10);
                    // Pivot 70 : 00-69 => 2000-2069, 70-99 => 1970-1999
                    const year  = yr2 < 70 ? 2000 + yr2 : 1900 + yr2;
                    // VÃ©rification basique que c'est une date valide (mois entre 01-12, jour entre 01-31)
                    const dayN = parseInt(day, 10);
                    const monN = parseInt(month, 10);
                    if (monN >= 1 && monN <= 12 && dayN >= 1 && dayN <= 31) {
                        cell.innerHTML = `<span class="text-success fw-bold">${day}/${month}/${year}</span>`;
                        return;
                    }
                }

                // PrioritÃ© 2 : Serial Excel (plage 30000-60000, typiquement 1982-2064)
                // On exclut les 6 chiffres dÃ©jÃ  traitÃ©s ci-dessus
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

        // --- GESTION DE LA SUPPRESSION GROUPÃ‰E ---

        function toggleAllCheckboxes(master) {
            const visibleRows = Array.from(document.querySelectorAll('.staging-row')).filter(row => row.style.display !== 'none');
            visibleRows.forEach(row => {
                const cb = row.querySelector('.row-checkbox');
                if (cb) cb.checked = master.checked;
            });
        }

        function selectAndBulkDeleteErrors() {
            // 1. SÃ©lectionner toutes les lignes en erreur (mÃªme cachÃ©es par filtre, mais le bouton est explicite)
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
    </script>
</body>
</html>
