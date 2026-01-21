/**
 * Plan Tiers JS - Gestion complète sans jQuery
 */

document.addEventListener("DOMContentLoaded", function () {

    // --- 1. RÉCUPÉRATION AUTOMATIQUE DU NUMÉRO DE TIERS ---
    const compteGeneralSelect = document.getElementById("compte_general");
    const numeroTiersInput = document.getElementById("numero_de_tiers");

    if (compteGeneralSelect && numeroTiersInput) {
        compteGeneralSelect.addEventListener("change", function () {
            // On récupère le texte de l'option sélectionnée (ex: "40110000 - FOURNISSEURS")
            const selectedOption = this.options[this.selectedIndex];
            if (!selectedOption.value) return;

            const numeroCompte = selectedOption.text.split(' - ')[0]; // On extrait "40110000"

            // Appel API pour obtenir le prochain numéro disponible
            fetch(`/get-dernier-numero/${numeroCompte}`)
                .then(response => {
                    if (!response.ok) throw new Error('Erreur réseau');
                    return response.json();
                })
                .then(data => {
                    if (data.numero) {
                        numeroTiersInput.value = data.numero;
                    }
                })
                .catch(error => {
                    console.error("Erreur lors de la récupération du numéro:", error);
                });
        });
    }

    // --- 2. GESTION DU MODAL DE CRÉATION (Réinitialisation) ---
    const modalCreate = document.getElementById("modalCenterCreate");
    if (modalCreate) {
        modalCreate.addEventListener("hidden.bs.modal", function () {
            const form = modalCreate.querySelector("form");
            if (form) {
                form.reset();
                form.querySelectorAll(".is-invalid").forEach((el) => {
                    el.classList.remove("is-invalid");
                });
            }
        });
    }

    // --- 3. GESTION DU MODAL DE MISE À JOUR (Remplissage) ---
    const updateModal = document.getElementById("modalCenterUpdate");
    const updateForm = document.getElementById("updateTiersForm");

    if (updateModal && updateForm) {
        updateModal.addEventListener("show.bs.modal", function (event) {
            const button = event.relatedTarget; // Bouton qui a ouvert le modal

            // Extraction des données des attributs data-
            const id = button.getAttribute("data-id");
            const numero = button.getAttribute("data-numero");
            const intitule = button.getAttribute("data-intitule");
            const type = button.getAttribute("data-type");
            const compte = button.getAttribute("data-compte");

            // Remplissage des champs
            document.getElementById("update_id").value = id;
            document.getElementById("update_numero").value = numero;
            document.getElementById("update_intitule").value = intitule;
            document.getElementById("update_type_de_tiers").value = type;
            document.getElementById("update_compte").value = compte;

            // Mise à jour de l'URL d'action (plan_tiersUpdateBaseUrl est défini dans le Blade)
            if (typeof plan_tiersUpdateBaseUrl !== 'undefined') {
                updateForm.action = plan_tiersUpdateBaseUrl.replace('__ID__', id);
            }
        });

        // Validation simple avant envoi
        updateForm.addEventListener("submit", function (e) {
            const fields = ["update_numero", "update_intitule", "update_type_de_tiers", "update_compte"];
            let isValid = true;

            fields.forEach(fieldId => {
                const el = document.getElementById(fieldId);
                if (!el.value.trim()) {
                    el.classList.add("is-invalid");
                    isValid = false;
                } else {
                    el.classList.remove("is-invalid");
                }
            });

            if (!isValid) e.preventDefault();
        });
    }

    // --- 4. GESTION DE LA SUPPRESSION ---
    const deleteModal = document.getElementById("deleteConfirmationModalTiers");
    const planNameDisplay = document.getElementById("planToDeleteNameTiers");
    const deleteForm = document.getElementById("deletePlanFormTiers");

    if (deleteModal && deleteForm) {
        deleteModal.addEventListener("show.bs.modal", function (event) {
            const button = event.relatedTarget;
            const tierId = button.getAttribute("data-id");
            const tierName = button.getAttribute("data-name");

            if (planNameDisplay) planNameDisplay.textContent = tierName;

            if (typeof plan_tiersDeleteUrl !== 'undefined') {
                deleteForm.action = plan_tiersDeleteUrl.replace('__ID__', tierId);
            }
        });
    }

    // --- 5. SYSTÈME DE FILTRES ---
    const applyBtn = document.getElementById("apply-filters");
    const resetBtn = document.getElementById("reset-filters");
    const rows = document.querySelectorAll("#tiersTable tbody tr");

    if (applyBtn && resetBtn) {
        applyBtn.addEventListener("click", function () {
            const intituleVal = document.getElementById("filter-intitule").value.toLowerCase().trim();
            const typeVal = document.getElementById("filter-type").value.toLowerCase().trim();

            rows.forEach((row) => {
                // On suppose : Col 0: Numéro, Col 1: Intitulé, Col 2: Type
                const cells = row.querySelectorAll("td");
                if (cells.length < 3) return;

                const txtIntitule = cells[1].textContent.toLowerCase();
                const txtType = cells[2].textContent.toLowerCase();

                const matches = txtIntitule.includes(intituleVal) && txtType.includes(typeVal);
                row.style.display = matches ? "" : "none";
            });
        });

        resetBtn.addEventListener("click", function () {
            document.getElementById("filter-intitule").value = "";
            document.getElementById("filter-type").value = "";
            rows.forEach(row => row.style.display = "");
        });
    }

    // --- 6. REDIRECTION VERS ÉCRITURES ---
    console.log("Configuration du gestionnaire pour les boutons .donnees-plan-tiers...");

    // Utiliser la délégation d'événements pour gérer les boutons dynamiques
    document.addEventListener("click", function (event) {
        const button = event.target.closest(".donnees-plan-tiers");
        if (button) {
            console.log("Clic sur le bouton voir détecté via délégation!");
            event.preventDefault();

            const params = {
                id_plan_tiers: button.getAttribute("data-id"),
                intitule: button.getAttribute("data-intitule"),
                numero_de_tiers: button.getAttribute("data-numero_de_tiers"),
            };
            console.log("Paramètres:", params);
            console.log("URL disponible:", typeof plan_tiers_ecrituresSaisisUrl !== 'undefined');

            if (typeof plan_tiers_ecrituresSaisisUrl !== 'undefined') {
                const queryString = new URLSearchParams(params).toString();
                const finalUrl = plan_tiers_ecrituresSaisisUrl + "?" + queryString;
                console.log("URL finale:", finalUrl);
                window.location.href = finalUrl;
            } else {
                console.error("plan_tiers_ecrituresSaisisUrl n'est pas défini!");
            }
        }
    });

    // --- 8. SYNCHRONISATION AVEC LE MODÈLE ADMIN [NOUVEAU] ---
    const btnSyncTiers = document.getElementById("btnSyncAdminTiers");
    if (btnSyncTiers) {
        btnSyncTiers.addEventListener("click", function () {
            if (confirm("Voulez-vous charger les fiches tiers manquantes depuis le modèle défini par l'administrateur ?")) {
                btnSyncTiers.disabled = true;
                const originalText = btnSyncTiers.innerHTML;
                btnSyncTiers.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Synchronisation...';

                // Utilisation de fetch au lieu de jQuery pour garder la cohérence du fichier
                fetch('/admin/config/sync/plan-tiers', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            window.location.reload();
                        } else {
                            alert(data.message || 'Erreur lors de la synchronisation.');
                            btnSyncTiers.disabled = false;
                            btnSyncTiers.innerHTML = originalText;
                        }
                    })
                    .catch(error => {
                        console.error("Erreur sync tiers:", error);
                        alert('Erreur réseau lors de la synchronisation.');
                        btnSyncTiers.disabled = false;
                        btnSyncTiers.innerHTML = originalText;
                    });
            }
        });
    }
});
