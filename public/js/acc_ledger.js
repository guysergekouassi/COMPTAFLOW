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

    // === MATCHER SELECT2 GLOBAL ===
    if (window.jQuery && $.fn.select2) {
        $.fn.select2.defaults.set('matcher', function(params, data) {
            if ($.trim(params.term) === '') return data;
            if (typeof data.text === 'undefined') return null;
            var term = $.trim(params.term).toLowerCase();
            var text  = data.text.trim();
            var dashIdx = text.indexOf(' - ');
            var numberPart = (dashIdx !== -1 ? text.substring(0, dashIdx) : text).trim().toLowerCase();
            var namePart   = (dashIdx !== -1 ? text.substring(dashIdx + 3) : '').trim().toLowerCase();
            if (/^\d/.test(term)) {
                return numberPart.startsWith(term) ? data : null;
            } else {
                return (namePart.includes(term) || numberPart.includes(term)) ? data : null;
            }
        });
    }

    // === INITIALISATION ===
    if (modalCreate) {
        $(modalCreate).on('shown.bs.modal', function () {
            if (window.jQuery && $.fn.select2) {
                $(modalCreate).find('.select2-enable').each(function() {
                    if ($(this).data('select2')) {
                        $(this).select2('destroy');
                    }
                    $(this).select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        language: 'fr',
                        dropdownParent: $(modalCreate)
                    });
                });
            }
            if (window.jQuery && $.fn.selectpicker) {
                $(modalCreate).find('.selectpicker').selectpicker('refresh');
            }
        });

        $(modalCreate).on('hide.bs.modal', function () {
            if (window.jQuery && $.fn.select2) {
                $(modalCreate).find('.select2-enable').each(function() {
                    if ($(this).data('select2')) {
                        $(this).select2('close');
                    }
                });
            }
        });
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
        // Recherche par classe ou texte (Select2 handles search, so we trigger refresh if needed)
        if (accountSearch) {
            accountSearch.addEventListener("input", function () {
                const query = this.value.trim().toLowerCase();
                accountSelects.forEach(select => {
                    if (!select) return;
                    const options = Array.from(select.querySelectorAll('option'));
                    options.forEach(option => {
                        const val = option.value;
                        if (val === "") return;
                        const text = option.text.toLowerCase();
                        const isMatch = !query || text.startsWith(query) || text.includes(query);
                        if (isMatch) {
                            option.disabled = false;
                        } else {
                            option.disabled = true;
                        }
                    });
                    if (window.jQuery && $.fn.select2) {
                        $(select).trigger('change');
                    }
                });
            });
        }

        // Créer des champs cachés pour transmettre les valeurs même quand les selects sont disabled
        const hiddenInput1 = document.createElement('input');
        hiddenInput1.type = 'hidden';
        hiddenInput1.name = 'plan_comptable_id_1';
        hiddenInput1.id = 'hidden_plan_comptable_id_1';

        const hiddenInput2 = document.createElement('input');
        hiddenInput2.type = 'hidden';
        hiddenInput2.name = 'plan_comptable_id_2';
        hiddenInput2.id = 'hidden_plan_comptable_id_2';

        if (grandLivreForm) {
            grandLivreForm.appendChild(hiddenInput1);
            grandLivreForm.appendChild(hiddenInput2);
        }

        // Sync hidden inputs with selects whenever selects change
        function syncHiddenInputs() {
            if (accountSelects[0]) hiddenInput1.value = accountSelects[0].value || '';
            if (accountSelects[1]) hiddenInput2.value = accountSelects[1].value || '';
        }

        if (accountSelects[0]) {
            accountSelects[0].addEventListener('change', syncHiddenInputs);
            if (window.jQuery) $(accountSelects[0]).on('change', syncHiddenInputs);
        }
        if (accountSelects[1]) {
            accountSelects[1].addEventListener('change', syncHiddenInputs);
            if (window.jQuery) $(accountSelects[1]).on('change', syncHiddenInputs);
        }

        // Validation Range
        if (grandLivreForm) {
            grandLivreForm.addEventListener("submit", function (e) {
                // Sync hidden inputs before submit (handles disabled selects)
                syncHiddenInputs();

                // Si "Tout sélectionner" est coché: les champs cachés ont déjà les bonnes valeurs
                if (selectAllCheck && selectAllCheck.checked) {
                    // Désactiver temporairement le name des selects pour éviter les doublons
                    if (accountSelects[0]) accountSelects[0].removeAttribute('name');
                    if (accountSelects[1]) accountSelects[1].removeAttribute('name');
                    return;
                }

                // Retirer le name des selects si les champs cachés sont actifs
                if (accountSelects[0]) accountSelects[0].removeAttribute('name');
                if (accountSelects[1]) accountSelects[1].removeAttribute('name');

                const v1 = hiddenInput1.value;
                const v2 = hiddenInput2.value;
                const sel1 = accountSelects[0];
                const sel2 = accountSelects[1];
                const t1 = sel1?.options[sel1.selectedIndex]?.text.split(" - ")[0].trim() || '';
                const t2 = sel2?.options[sel2.selectedIndex]?.text.split(" - ")[0].trim() || '';

                if (!v1 || !v2) {
                    e.preventDefault();
                    FlowToast && FlowToast.warning("Veuillez sélectionner une plage de comptes.");
                    return;
                }

                if (t1 && t2 && t1 > t2) {
                    e.preventDefault();
                    if (sel2) sel2.classList.add("is-invalid");
                    const errorDiv = document.getElementById("compte2-error");
                    if (errorDiv) {
                        errorDiv.innerText = "Le compte de fin doit être >= au compte de début.";
                        errorDiv.style.display = "block";
                    }
                }
            });
        }

        // Tout sélectionner
        if (selectAllCheck) {
            selectAllCheck.addEventListener("change", function () {
                const isChecked = this.checked;
                accountSelects.forEach(select => {
                    if (!select) return;
                    if (window.jQuery && $.fn.select2) {
                        $(select).prop('disabled', isChecked).trigger('change');
                    } else {
                        select.disabled = isChecked;
                    }
                });
                if (isChecked && accountSelects[0] && accountSelects[1]) {
                    const validOptions = Array.from(accountSelects[0].options).filter(opt => opt.value !== "");
                    if (validOptions.length > 0) {
                        const vFirst = validOptions[0].value;
                        const vLast = validOptions[validOptions.length - 1].value;
                        if (window.jQuery && $.fn.select2) {
                            $(accountSelects[0]).val(vFirst).trigger('change');
                            $(accountSelects[1]).val(vLast).trigger('change');
                        } else {
                            accountSelects[0].value = vFirst;
                            accountSelects[1].value = vLast;
                        }
                        // Sync hidden inputs immediately
                        hiddenInput1.value = vFirst;
                        hiddenInput2.value = vLast;
                    }
                } else {
                    // Sync hidden inputs with current select values
                    syncHiddenInputs();
                }
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
                    FlowToast.warning("Veuillez renseigner les dates de début et de fin.");
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
                    FlowToast.warning("Veuillez sélectionner une plage de comptes valide.");
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
                            if (frame) frame.src = data.url + "#toolbar=0&navpanes=0&scrollbar=1&statusbar=0&view=FitH";
                            const modalEl = document.getElementById("modalPreviewPDF");
                            if (modalEl) bootstrap.Modal.getOrCreateInstance(modalEl).show();
                        } else { FlowToast.error(data.error || "Erreur lors de la prévisualisation."); }
                    })
                    .catch(err => {
                        // Masquer le spinner
                        if (spinnerPreview) spinnerPreview.classList.add("d-none");
                        if (loaderText) loaderText.classList.add("d-none");
                        if (btnPreviewLabel) btnPreviewLabel.classList.remove("d-none");
                        if (btnPreview) btnPreview.disabled = false;

                        console.error("Erreur :", err); FlowToast.error("Impossible de générer la prévisualisation.");
                    });
            });
        }

        // Reset
        const resetForm = () => {
            if (grandLivreForm) grandLivreForm.reset();
            if (selectAllCheck) { selectAllCheck.checked = false; selectAllCheck.dispatchEvent(new Event('change')); }
            accountSelects.forEach((select, idx) => {
                if (!select) return;
                select.classList.remove("is-invalid");
                // Restore name attributes that may have been removed during submit
                select.setAttribute('name', idx === 0 ? 'plan_comptable_id_1' : 'plan_comptable_id_2');
                if (window.jQuery && $.fn.select2) $(select).val('').trigger('change');
            });
            // Clear hidden inputs
            hiddenInput1.value = '';
            hiddenInput2.value = '';
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
