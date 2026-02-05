document.addEventListener("DOMContentLoaded", function () {
    // === ÉLÉMENTS DU DOM ===
    const modalCreate = document.getElementById("modalCenterCreate");
    const grandLivreForm = document.getElementById("grandLivreForm");
    const btnSaveModal = document.getElementById("btnSaveModal");
    const btnCloseModal = document.getElementById("btnCloseModal");
    const btnPreview = document.getElementById("btnPreview");
    const spinnerPreview = document.getElementById("spinnerPreview");
    const loaderText = document.getElementById("loaderText");
    const btnPreviewLabel = document.getElementById("btnPreviewLabel");

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
        console.log("AccLedger: Initialisation des selectpickers...");
        $('.selectpicker').selectpicker('destroy'); // Nettoyage au cas où
        $('.selectpicker').selectpicker();
    }

    // === GESTION DES FILTRES AVANCÉS (TABLEAU PRINCIPAL) ===
    if (toggleFilterBtn) {
        toggleFilterBtn.addEventListener("click", function () {
            if (advancedFilterPanel) {
                const isHidden = advancedFilterPanel.style.display === 'none' || advancedFilterPanel.style.display === '';
                advancedFilterPanel.style.display = isHidden ? 'block' : 'none';
            }
        });
    }

    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener("click", function () {
            const q = (document.getElementById('filter-client')?.value || '').toLowerCase();
            const status = (document.getElementById('filter-status')?.value || '').toLowerCase();
            const rows = document.querySelectorAll('table.table-premium tbody tr');
            rows.forEach((tr) => {
                const text = (tr.textContent || '').toLowerCase();
                const matchQ = !q || text.includes(q);
                const matchStatus = !status || text.includes(status);
                tr.style.display = (matchQ && matchStatus) ? '' : 'none';
            });
        });
    }

    if (resetFiltersBtn) {
        resetFiltersBtn.addEventListener("click", function () {
            const qInput = document.getElementById('filter-client');
            const statusSelect = document.getElementById('filter-status');
            if (qInput) qInput.value = '';
            if (statusSelect) statusSelect.value = '';
            if (applyFiltersBtn) applyFiltersBtn.click();
        });
    }

    // === GESTION DU MODAL (PLAGE DE COMPTES) ===
    if (modalCreate) {
        // Recherche par classe ou texte
        if (accountSearch) {
            accountSearch.addEventListener("input", function () {
                const query = this.value.trim().toLowerCase();
                console.log("Recherche lancée pour :", query);

                accountSelects.forEach(select => {
                    if (!select) return;
                    const options = Array.from(select.querySelectorAll('option'));
                    let visibleCount = 0;

                    options.forEach(option => {
                        const val = option.value;
                        if (val === "") return;

                        const text = option.text.toLowerCase();
                        // Match si commence par (classe) OU contient (recherche textuelle)
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

                    console.log(`Select ${select.id}: ${visibleCount} options visibles sur ${options.length - 1}`);

                    if (window.jQuery && $.fn.selectpicker) {
                        $(select).selectpicker('refresh');
                    }
                });
            });
        }

        // Validation Range
        if (grandLivreForm) {
            grandLivreForm.addEventListener("submit", function (e) {
                // Si "Tout sélectionner" est coché, on active temporairement les champs pour qu'ils soient envoyés
                if (selectAllCheck && selectAllCheck.checked) {
                    accountSelects.forEach(select => {
                        if (select) select.disabled = false;
                    });
                    // On ne fait pas de e.preventDefault() ici, on laisse le formulaire partir normalement
                    return;
                }

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
                    if (window.jQuery && $.fn.selectpicker) $(select).selectpicker('refresh');
                });
                if (isChecked && accountSelects[0] && accountSelects[1]) {
                    const validOptions = Array.from(accountSelects[0].options).filter(opt => opt.value !== "");
                    if (validOptions.length > 0) {
                        const vFirst = validOptions[0].value;
                        const vLast = validOptions[validOptions.length - 1].value;
                        if (window.jQuery && $.fn.selectpicker) {
                            $(accountSelects[0]).selectpicker('val', vFirst);
                            $(accountSelects[1]).selectpicker('val', vLast);
                        } else {
                            accountSelects[0].value = vFirst;
                            accountSelects[1].value = vLast;
                        }
                    }
                }
                if (accountSearch) {
                    accountSearch.disabled = isChecked;
                    if (isChecked) {
                        // Reset search logic to show everything for class selection hidden in background
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

                // Afficher le spinner
                if (spinnerPreview) spinnerPreview.classList.remove("d-none");
                if (loaderText) loaderText.classList.remove("d-none");
                if (btnPreviewLabel) btnPreviewLabel.classList.add("d-none");
                if (btnPreview) btnPreview.disabled = true;

                fetch(accounting_ledgerpreviewGrandLivreUrl, {
                    method: "POST",
                    body: formData,
                    headers: { "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content") }
                })
                    .then(response => response.json())
                    .then(data => {
                        // Masquer le spinner
                        if (spinnerPreview) spinnerPreview.classList.add("d-none");
                        if (loaderText) loaderText.classList.add("d-none");
                        if (btnPreviewLabel) btnPreviewLabel.classList.remove("d-none");
                        if (btnPreview) btnPreview.disabled = false;

                        if (data.success) {
                            const frame = document.getElementById("pdfPreviewFrame");
                            if (frame) frame.src = data.url;
                            const modalEl = document.getElementById("modalPreviewPDF");
                            if (modalEl) bootstrap.Modal.getOrCreateInstance(modalEl).show();
                        } else { alert(data.error || "Erreur lors de la prévisualisation."); }
                    })
                    .catch(err => {
                        // Masquer le spinner
                        if (spinnerPreview) spinnerPreview.classList.add("d-none");
                        if (loaderText) loaderText.classList.add("d-none");
                        if (btnPreviewLabel) btnPreviewLabel.classList.remove("d-none");
                        if (btnPreview) btnPreview.disabled = false;

                        console.error("Erreur :", err); alert("Impossible de générer la prévisualisation.");
                    });
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
            const id = button.getAttribute("data-id");
            const filename = button.getAttribute("data-filename");
            const deleteForm = document.getElementById("deleteForm");
            const fileNameText = document.getElementById("fileNameToDelete");
            if (deleteForm) deleteForm.action = accounting_ledgerDeleteUrl.replace('__ID__', id);
            if (fileNameText) fileNameText.textContent = filename;
        });
    }
});
