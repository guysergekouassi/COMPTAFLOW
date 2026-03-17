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
                        <button type="button" class="btn btn-outline-primary btn-xs rounded-pill fw-bold px-3" onclick="window.ajouterLigneVentilation()">
                            <i class="bx bx-plus-circle me-1"></i> Ajouter une section
                        </button>
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
    document.addEventListener('DOMContentLoaded', function() {
        const SECTIONS_ANALYTIQUES = @json(\App\Models\SectionAnalytique::with('axe')->get());

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
                <td class="text-center"><button type="button" class="btn btn-link text-danger p-0" onclick="this.closest('tr').remove(); window.mettreAJourMontantsVentilation();"><i class="bx bx-trash"></i></button></td>
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

        window.mettreAJourMontantsVentilation = () => {
            let totalPct = 0;
            let totalMnt = 0;
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
            const totalPctStr = document.getElementById('total_pourcentage').innerText.split(' ')[0];
            const totalPct = parseFloat(totalPctStr) || 0;
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

            if (window.currentRowForVentilation) {
                window.currentRowForVentilation.dataset.ventilations = JSON.stringify(ventilations);
                const libCell = window.currentRowForVentilation.querySelector('td:nth-child(3)') || window.currentRowForVentilation.querySelector('.row-lib').closest('td');
                
                let badge = libCell.querySelector('.ventilated-badge');
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'badge bg-label-info ms-2 ventilated-badge';
                    badge.innerHTML = '<i class="bx bx-pie-chart-alt me-1"></i>Ventilé';
                    libCell.appendChild(badge);
                }
            }

            const modal = bootstrap.Modal.getInstance(document.getElementById('modalVentilationAnalytique'));
            if (modal) modal.hide();
        };
    });
</script>
