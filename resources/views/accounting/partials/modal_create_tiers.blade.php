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
    document.addEventListener('DOMContentLoaded', function() {
        const typeTiers = document.getElementById('type_tiers');
        const compteGeneralTiers = document.getElementById('compte_general_tiers');
        const numeroTiers = document.getElementById('numero_tiers');

        if (typeTiers) {
            typeTiers.addEventListener('change', (e) => {
                const type = e.target.value;
                const prefixMap = {
                    'Fournisseur': '40', 
                    'Client': '41', 
                    'Personnel': '42', 
                    'CNPS': '43', 
                    'Impots': '44', 
                    'Associé': '45', 
                    'Divers Tiers': '47'
                };
                const prefix = prefixMap[type];
                
                compteGeneralTiers.innerHTML = '<option value="" disabled selected>Choisir un compte...</option>';
                
                // Assuming GEN_ACCOUNTS is available globally
                if (typeof GEN_ACCOUNTS !== 'undefined') {
                    GEN_ACCOUNTS.forEach(a => { 
                        if (a.numero_de_compte.startsWith(prefix)) {
                            compteGeneralTiers.innerHTML += `<option value="${a.id}">${a.numero_de_compte} - ${a.intitule}</option>`;
                        }
                    });
                }
                
                if (compteGeneralTiers.options.length > 1) {
                    compteGeneralTiers.selectedIndex = 1; 
                }

                if (prefix) {
                    fetch("/plan_tiers/" + prefix)
                        .then(r => r.json())
                        .then(d => numeroTiers.value = d.numero);
                }
            });
        }

        window.createTiersSimple = (e) => {
            e.preventDefault();
            const btn = document.getElementById('btnCreateTiers');
            const form = document.getElementById('createTiersForm');
            if (!form.checkValidity()) { form.reportValidity(); return; }
            
            const data = { 
                type_de_tiers: document.getElementById('type_tiers').value, 
                compte_general: document.getElementById('compte_general_tiers').value, 
                intitule: document.getElementById('intitule_tiers').value, 
                numero_de_tiers: document.getElementById('numero_tiers').value 
            };
            
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
                    // Update global TIERS_LIST if exists
                    if (typeof TIERS_LIST !== 'undefined') {
                        TIERS_LIST.push({id: res.id, numero_de_tiers: res.numero_de_tiers, intitule: res.intitule});
                    }
                    
                    // Update all row-tier selects
                    document.querySelectorAll('.row-tier').forEach(sel => {
                        const isCurrent = (window.currentTierSelect === sel);
                        const newOption = new Option(`${res.numero_de_tiers} - ${res.intitule}`, res.id, isCurrent, isCurrent);
                        $(sel).append(newOption);
                        if (isCurrent) {
                            $(sel).trigger('change');
                            const warning = sel.closest('td') ? sel.closest('td').querySelector('.text-danger') : null;
                            if (warning) warning.remove();
                        }
                    });
                    
                    const modal = bootstrap.Modal.getInstance(document.getElementById('createTiersModal'));
                    if (modal) modal.hide();
                    form.reset();
                    Swal.fire('Succès', 'Tiers créé avec succès', 'success');
                } else alert("Erreur: " + res.error);
            }).finally(() => { btn.disabled = false; btn.innerText = "Enregistrer le Tiers"; });
        };
    });
</script>
