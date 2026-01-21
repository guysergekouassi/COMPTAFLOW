document.addEventListener("DOMContentLoaded", function () {
    // === ÉLÉMENTS DU DOM ===
    const modalCreate = document.getElementById("modalCenterCreate");
    const grandLivreForm = document.getElementById("grandLivreForm"); // Note: it has the same ID as Grand Livre form in Blade
    const btnSaveModal = document.getElementById("btnSaveModal");
    const btnCloseModal = document.getElementById("btnCloseModal");
    const btnPreview = document.getElementById("btnPreview");

    const accountSearch = document.getElementById("accountSearch");
    const selectAllCheck = document.getElementById("selectAllAccounts");
    const accountSelects = [
        document.getElementById("plan_comptable_id_1"),
        document.getElementById("plan_comptable_id_2")
    ];

    const toggleFilterBtn = document.getElementById("toggleFilterBtn");
    const applyFiltersBtn = document.getElementById("apply-filters");
    const resetFiltersBtn = document.getElementById("reset-filters");
    const advancedFilterPanel = document.getElementById("advancedFilterPanel");

    // === INITIALISATION ===
    if (window.jQuery && $.fn.selectpicker) {
        console.log("AccBalance: Initialisation des selectpickers...");
        $('.selectpicker').selectpicker('destroy');
        $('.selectpicker').selectpicker();
    }

    // === GESTION DES FILTRES AVANCÉS (TABLEAU PRINCIPAL) ===
    window.toggleAdvancedFilter = function () {
        if (advancedFilterPanel) {
            const isHidden = advancedFilterPanel.style.display === 'none' || advancedFilterPanel.style.display === '';
            advancedFilterPanel.style.display = isHidden ? 'block' : 'none';
        }
    };

    window.applyAdvancedFilters = function () {
        const q = (document.getElementById('filter-client')?.value || '').toLowerCase();
        const status = (document.getElementById('filter-status')?.value || '').toLowerCase();
        const rows = document.querySelectorAll('table.table-premium tbody tr');
        rows.forEach((tr) => {
            const text = (tr.textContent || '').toLowerCase();
            const matchQ = !q || text.includes(q);
            const matchStatus = !status || text.includes(status);
            tr.style.display = (matchQ && matchStatus) ? '' : 'none';
        });
    };

    window.resetAdvancedFilters = function () {
        const qInput = document.getElementById('filter-client');
        const statusSelect = document.getElementById('filter-status');
        if (qInput) qInput.value = '';
        if (statusSelect) statusSelect.value = '';
        window.applyAdvancedFilters();
    };

    if (toggleFilterBtn) {
        toggleFilterBtn.onclick = window.toggleAdvancedFilter;
    }

    // === GESTION DU MODAL (PLAGE DE COMPTES) ===
    if (modalCreate) {
        // Recherche par classe ou texte
        if (accountSearch) {
            accountSearch.addEventListener("input", function () {
                const query = this.value.trim().toLowerCase();
                console.log("AccBalance: Recherche lancée pour :", query);

                accountSelects.forEach(select => {
                    if (!select) return;
                    const options = Array.from(select.querySelectorAll('option'));
                    let visibleCount = 0;

                    options.forEach(option => {
                        const val = option.value;
                        if (val === "") return;

                        const text = option.text.toLowerCase();
                        const isMatch = !query || text.startsWith(query) || text.includes(query);

                        if (isMatch) {
                            option.disabled = false;
                            option.hidden = false;
                            option.style.display = "";
                            visibleCount++;
                        } else {
                            option.disabled = true;
                            option.hidden = true;
                            option.style.display = "none";
                        }
                    });

                    console.log(`Select ${select.id}: ${visibleCount} options visibles`);

                    if (window.jQuery && $.fn.selectpicker) {
                        $(select).selectpicker('refresh');
                    }
                });
            });
        }

        // Validation Range
        if (grandLivreForm) {
            grandLivreForm.addEventListener("submit", function (e) {
                if (selectAllCheck && selectAllCheck.checked) return;
                const v1 = accountSelects[0]?.options[accountSelects[0].selectedIndex]?.text.split(" - ")[0].trim();
                const v2 = accountSelects[1]?.options[accountSelects[1].selectedIndex]?.text.split(" - ")[0].trim();
                if (v1 && v2 && v1 > v2) {
                    e.preventDefault();
                    accountSelects[1].classList.add("is-invalid");
                    const errorDiv = document.getElementById("compte2-error");
                    if (errorDiv) {
                        errorDiv.innerText = "Le compte de fin doit être >= au compte de début.";
                        errorDiv.style.display = "block";
                    }
                    if (window.jQuery && $.fn.selectpicker) $(accountSelects[1]).selectpicker('refresh');
                }
            });
        }

        // Tout sélectionner
        if (selectAllCheck) {
            selectAllCheck.addEventListener("change", function () {
                const isChecked = this.checked;
                accountSelects.forEach(select => {
                    if (!select) return;
                    select.disabled = isChecked;
                    if (isChecked) {
                        const validOptions = Array.from(select.options).filter(opt => opt.value !== "");
                        if (validOptions.length > 0) {
                            const val = (select.id === "plan_comptable_id_1") ? validOptions[0].value : validOptions[validOptions.length - 1].value;
                            if (window.jQuery && $.fn.selectpicker) $(select).selectpicker('val', val);
                            else select.value = val;
                        }
                    }
                    if (window.jQuery && $.fn.selectpicker) $(select).selectpicker('refresh');
                });

                if (accountSearch) {
                    accountSearch.disabled = isChecked;
                    if (isChecked) {
                        accountSearch.value = "";
                        accountSearch.dispatchEvent(new Event('input'));
                    }
                }
            });
        }

        // Prévisualisation
        if (btnPreview) {
            btnPreview.addEventListener("click", function (e) {
                e.preventDefault();

                // Validation minimale avant l'envoi
                const dateD = document.getElementById("date_debut")?.value;
                const dateF = document.getElementById("date_fin")?.value;
                if (!dateD || !dateF) {
                    alert("Veuillez renseigner les dates de début et de fin.");
                    return;
                }

                const formData = new FormData(grandLivreForm);
                if (selectAllCheck?.checked) {
                    accountSelects.forEach(select => {
                        if (select) {
                            if (!select.value) {
                                // Forcer la valeur si vide alors que Tout Sélectionner est coché (cas limite)
                                const validOptions = Array.from(select.options).filter(opt => opt.value !== "");
                                if (validOptions.length > 0) {
                                    const val = (select.id === "plan_comptable_id_1") ? validOptions[0].value : validOptions[validOptions.length - 1].value;
                                    formData.set(select.name, val);
                                }
                            } else {
                                formData.set(select.name, select.value);
                            }
                        }
                    });
                }

                // Vérifier si les comptes sont présents
                if (!formData.get("plan_comptable_id_1") || !formData.get("plan_comptable_id_2")) {
                    alert("Veuillez sélectionner une plage de comptes valide.");
                    return;
                }

                fetch(accounting_balancePreviewUrl, {
                    method: "POST",
                    body: formData,
                    headers: { "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content") }
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const frame = document.getElementById("pdfPreviewFrame");
                            if (frame) frame.src = data.url;
                            const modalEl = document.getElementById("modalPreviewPDF");
                            if (modalEl) bootstrap.Modal.getOrCreateInstance(modalEl).show();
                        } else { alert(data.error || "Erreur lors de la prévisualisation."); }
                    })
                    .catch(err => { console.error("Erreur :", err); alert("Impossible de générer la prévisualisation."); });
            });
        }

        // Reset
        const resetForm = () => {
            if (grandLivreForm) grandLivreForm.reset();
            if (selectAllCheck) { selectAllCheck.checked = false; selectAllCheck.dispatchEvent(new Event('change')); }
            accountSelects.forEach(select => {
                if (!select) return;
                select.classList.remove("is-invalid");
                if (window.jQuery && $.fn.selectpicker) $(select).selectpicker('val', '').selectpicker('refresh');
            });
            const errorDiv = document.getElementById("compte2-error");
            if (errorDiv) errorDiv.style.display = "none";
        };
        if (btnCloseModal) btnCloseModal.addEventListener("click", resetForm);
        modalCreate.addEventListener("hidden.bs.modal", resetForm);
    }

    // Modal de suppression
    const deleteModal = document.getElementById("deleteConfirmationModal");
    if (deleteModal) {
        deleteModal.addEventListener("show.bs.modal", function (event) {
            const button = event.relatedTarget;
            const id = button?.getAttribute("data-id");
            if (id) {
                const deleteForm = document.getElementById("deleteForm");
                if (deleteForm) deleteForm.action = accounting_balanceDeleteUrl.replace('__ID__', id);
            }
        });
    }
});