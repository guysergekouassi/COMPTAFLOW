@php
    $companyId = Session::get('current_company_id') ?? auth()->user()->company_id;
    $axesAnalytiques = \App\Models\AxeAnalytique::where('company_id', $companyId)
        ->with(['sections' => function($q) use ($companyId) {
            $q->where('company_id', $companyId);
        }])
        ->get()
        ->map(function($axe) {
            return [
                'id' => $axe->id,
                'libelle' => $axe->libelle,
                'sections' => $axe->sections->map(function($s) {
                    return [
                        'id' => $s->id,
                        'code' => $s->code,
                        'libelle' => $s->libelle
                    ];
                })
            ];
        });
@endphp

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
                    <!-- Dynamic axes container -->
                    <div id="axes_ventilation_container" class="space-y-4">
                        <!-- Cards will be dynamically injected here for each axe -->
                    </div>
                </form>

                <div class="d-flex justify-content-center gap-3 pt-5">
                    <button type="button" class="btn btn-outline-secondary px-5" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary px-5" onclick="window.validerVentilation()">
                        <i class="bx bx-check-circle me-1"></i> Valider
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.sections_analytiques_cache = @json($axesAnalytiques);

    document.addEventListener('DOMContentLoaded', function() {
        
        window.initialiserTableauVentilation = function(montantTotal) {
            const container = document.getElementById('axes_ventilation_container');
            if (!container) return;
            container.innerHTML = '';

            if (!window.sections_analytiques_cache || window.sections_analytiques_cache.length === 0) {
                container.innerHTML = '<div class="alert alert-warning text-center">Aucun axe analytique configuré. Veuillez créer des axes et sections analytiques dans le paramétrage.</div>';
                return;
            }

            // Map each section ID to its parent Axe ID for easy routing during loading
            const sectionToAxe = {};
            window.sections_analytiques_cache.forEach(axe => {
                axe.sections.forEach(sec => {
                    sectionToAxe[sec.id] = axe.id;
                });
            });

            // Create card structures for all active axes
            window.sections_analytiques_cache.forEach(axe => {
                const card = document.createElement('div');
                card.className = 'card mb-4 border border-light-subtle shadow-xs';
                card.style.borderRadius = '15px';
                card.innerHTML = `
                    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2 px-3" style="border-radius: 15px 15px 0 0;">
                        <h6 class="mb-0 fw-bold text-slate-800" style="font-size: 0.9rem;">
                            <i class="bx bx-git-commit me-1 text-primary"></i> Axe : ${axe.libelle}
                        </h6>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary" id="total_pct_axe_${axe.id}" style="font-size: 0.8rem;">0.00 %</span>
                            <span class="text-muted small fw-medium" id="total_mnt_axe_${axe.id}" style="font-size: 0.75rem;">0.00 FCFA</span>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-2" id="table_axe_${axe.id}">
                                <thead>
                                    <tr class="text-secondary text-uppercase" style="font-size: 0.7rem; border-bottom: 1px solid #f1f5f9;">
                                        <th style="width: 50%;">Section Analytique</th>
                                        <th style="width: 20%;">%</th>
                                        <th style="width: 25%;">Montant</th>
                                        <th style="width: 5%;"></th>
                                    </tr>
                                </thead>
                                <tbody id="tbody_axe_${axe.id}">
                                    <!-- Ventilation lines -->
                                </tbody>
                            </table>
                        </div>
                        <div class="text-start mt-2">
                            <button type="button" class="btn btn-outline-primary btn-xs rounded-pill fw-bold px-3 py-1" style="font-size: 0.75rem; border-width: 1.5px;" onclick="window.ajouterLigneVentilationAxe(${axe.id})">
                                <i class="bx bx-plus-circle me-1"></i> Ajouter une section
                            </button>
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });

            // Populate existing ventilations
            const dataToLoad = window.currentRowForVentilation 
                ? (window.currentRowForVentilation.dataset.ventilations ? JSON.parse(window.currentRowForVentilation.dataset.ventilations) : [])
                : (window.ventilations_temporaires || []);

            if (dataToLoad && dataToLoad.length > 0) {
                dataToLoad.forEach(v => {
                    const axeId = sectionToAxe[v.section_id];
                    if (axeId) {
                        window.ajouterLigneVentilationAxe(axeId, v.section_id, v.pourcentage, v.montant);
                    }
                });
            }

            window.mettreAJourMontantsVentilation();
        };

        window.ajouterLigneVentilationAxe = function(axeId, sectionId = '', pourcentage = '', montant = '') {
            const tbody = document.querySelector(`#tbody_axe_${axeId}`);
            if (!tbody) return;

            const tr = document.createElement('tr');
            const axe = window.sections_analytiques_cache.find(a => a.id == axeId);
            if (!axe) return;

            let options = '<option value="" disabled selected>Sélectionner une section...</option>';
            axe.sections.forEach(s => {
                options += `<option value="${s.id}" ${s.id == sectionId ? 'selected' : ''}>${s.code} - ${s.libelle}</option>`;
            });

            tr.innerHTML = `
                <td style="padding: 0.5rem 0.25rem; width: 50%;">
                    <select class="form-select form-select-sm select2-vent section-select" required style="font-size: 0.85rem; border-radius: 12px; padding: 0.5rem;">
                        ${options}
                    </select>
                </td>
                <td style="padding: 0.5rem 0.25rem; width: 20%;">
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control vent-pct pct-input" step="0.01" min="0" max="100" value="${pourcentage}" placeholder="0.00" style="font-size: 0.85rem; border-radius: 10px 0 0 10px;">
                        <span class="input-group-text" style="font-size: 0.8rem; padding: 0.3rem 0.5rem;">%</span>
                    </div>
                </td>
                <td style="padding: 0.5rem 0.25rem; width: 25%;">
                    <div class="input-group input-group-sm">
                        <input type="number" class="form-control vent-mnt amt-input" step="0.01" min="0" value="${montant}" placeholder="0.00" style="font-size: 0.85rem; border-radius: 10px 0 0 10px;">
                        <span class="input-group-text" style="font-size: 0.75rem; padding: 0.3rem 0.5rem;">FCFA</span>
                    </div>
                </td>
                <td class="text-center" style="padding: 0.5rem 0.25rem; width: 5%;">
                    <button type="button" class="btn btn-link text-danger p-0" onclick="this.closest('tr').remove(); window.mettreAJourMontantsVentilation();">
                        <i class="bx bx-trash fs-4"></i>
                    </button>
                </td>
            `;

            tbody.appendChild(tr);

            const select = tr.querySelector('.select2-vent');
            $(select).select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownParent: $('#modalVentilationAnalytique')
            });

            tr.querySelector('.vent-pct').oninput = () => calculerDepuisPct(tr);
            tr.querySelector('.vent-mnt').oninput = () => calculerDepuisMnt(tr);
            
            $(select).on('change', () => window.mettreAJourMontantsVentilation());
            window.mettreAJourMontantsVentilation();
        };

        const calculerDepuisPct = (tr) => {
            const totalStr = document.getElementById('montant_a_ventiler_display').innerText.replace(/\s/g, '').replace(',', '.');
            const total = parseFloat(totalStr) || 0;
            const pct = parseFloat(tr.querySelector('.vent-pct').value) || 0;
            tr.querySelector('.vent-mnt').value = (total * pct / 100).toFixed(2);
            window.mettreAJourMontantsVentilation();
        };

        const calculerDepuisMnt = (tr) => {
            const totalStr = document.getElementById('montant_a_ventiler_display').innerText.replace(/\s/g, '').replace(',', '.');
            const total = parseFloat(totalStr) || 0;
            const mnt = parseFloat(tr.querySelector('.vent-mnt').value) || 0;
            tr.querySelector('.vent-pct').value = total > 0 ? (mnt / total * 100).toFixed(2) : 0;
            window.mettreAJourMontantsVentilation();
        };

        window.mettreAJourMontantsVentilation = function() {
            if (!window.sections_analytiques_cache) return;
            
            window.sections_analytiques_cache.forEach(axe => {
                let totalPct = 0;
                let totalMnt = 0;
                
                document.querySelectorAll(`#tbody_axe_${axe.id} tr`).forEach(tr => {
                    totalPct += parseFloat(tr.querySelector('.vent-pct').value) || 0;
                    totalMnt += parseFloat(tr.querySelector('.vent-mnt').value) || 0;
                });

                const pctBadge = document.getElementById(`total_pct_axe_${axe.id}`);
                if (pctBadge) {
                    pctBadge.innerText = totalPct.toFixed(2) + ' %';
                    if (totalPct === 0) {
                        pctBadge.className = 'badge bg-secondary';
                    } else if (Math.abs(totalPct - 100) < 0.05) {
                        pctBadge.className = 'badge bg-success';
                    } else {
                        pctBadge.className = 'badge bg-danger';
                    }
                }
                
                const mntDisplay = document.getElementById(`total_mnt_axe_${axe.id}`);
                if (mntDisplay) {
                    mntDisplay.innerText = totalMnt.toLocaleString('fr-FR', { minimumFractionDigits: 2 }) + ' FCFA';
                }
            });
        };

        window.validerVentilation = function() {
            const ventilations = [];
            let hasValidationError = false;
            let errorMessage = '';
            let hasAtLeastOneVentilation = false;

            if (!window.sections_analytiques_cache) return;

            window.sections_analytiques_cache.forEach(axe => {
                let totalPct = 0;
                const rows = document.querySelectorAll(`#tbody_axe_${axe.id} tr`);
                
                if (rows.length > 0) {
                    hasAtLeastOneVentilation = true;
                    rows.forEach(tr => {
                        const sectionSelect = tr.querySelector('.section-select');
                        const sectionId = sectionSelect.value;
                        const pct = parseFloat(tr.querySelector('.vent-pct').value) || 0;
                        const mnt = parseFloat(tr.querySelector('.vent-mnt').value) || 0;

                        if (!sectionId) {
                            hasValidationError = true;
                            errorMessage = 'Veuillez sélectionner une section pour toutes les lignes ajoutées.';
                        }

                        totalPct += pct;
                        ventilations.push({
                            section_id: sectionId,
                            pourcentage: pct,
                            montant: mnt
                        });
                    });

                    if (Math.abs(totalPct - 100) > 0.05) {
                        hasValidationError = true;
                        errorMessage = `Le total des pourcentages pour l'axe "${axe.libelle}" doit être égal à 100%. Actuellement : ${totalPct.toFixed(2)}%`;
                    }
                }
            });

            if (hasValidationError) {
                Swal.fire('Erreur de validation', errorMessage, 'warning');
                return;
            }

            if (!hasAtLeastOneVentilation) {
                Swal.fire('Attention', 'Veuillez ajouter au moins une ligne de ventilation sur un axe.', 'warning');
                return;
            }

            // Save to current row
            if (window.currentRowForVentilation) {
                window.currentRowForVentilation.dataset.ventilations = JSON.stringify(ventilations);
                
                const libCell = window.currentRowForVentilation.querySelector('td:nth-child(3)') || window.currentRowForVentilation.querySelector('.row-lib')?.closest('td');
                if (libCell) {
                    let badge = libCell.querySelector('.ventilated-badge');
                    if (!badge) {
                        badge = document.createElement('span');
                        badge.className = 'badge bg-label-info ms-2 ventilated-badge';
                        badge.innerHTML = '<i class="bx bx-pie-chart-alt me-1"></i>Ventilé';
                        libCell.appendChild(badge);
                    }
                }
            }
            
            // Sync with global temporaries
            window.ventilations_temporaires = ventilations;

            const modalEl = document.getElementById('modalVentilationAnalytique');
            if (modalEl) {
                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.hide();
            }
            
            Swal.fire({
                icon: 'success',
                title: 'Ventilation validée',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        };
    });
</script>
