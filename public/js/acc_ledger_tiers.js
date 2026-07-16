document.addEventListener("DOMContentLoaded", function () {
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

    const modal = document.getElementById("modalCenterCreate");
    const btnSave = document.getElementById("btnSaveModal");
    const btnClose = document.getElementById("btnCloseModal");

    if (modal) {
        $(modal).on('shown.bs.modal', function () {
            if (window.jQuery && $.fn.select2) {
                $(modal).find('.select2-enable').each(function() {
                    if ($(this).data('select2')) {
                        $(this).select2('destroy');
                    }
                    $(this).select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        language: 'fr',
                        dropdownParent: $(modal)
                    });
                });
            }
            if (window.jQuery && $.fn.selectpicker) {
                $(modal).find('.selectpicker').selectpicker('refresh');
            }
        });

        $(modal).on('hide.bs.modal', function () {
            if (window.jQuery && $.fn.select2) {
                $(modal).find('.select2-enable').each(function() {
                    if ($(this).data('select2')) {
                        $(this).select2('close');
                    }
                });
            }
        });
    }
    const selectAllTiersCheck = document.getElementById("selectAllTiers");
    const tiersSelects = [
        document.getElementById("plan_tiers_id_1"),
        document.getElementById("plan_tiers_id_2")
    ];
    const inputs = modal ? modal.querySelectorAll("input[required], select[required]") : [];

    // Tout sélectionner
    if (selectAllTiersCheck) {
        selectAllTiersCheck.addEventListener("change", function () {
            const isChecked = this.checked;
            tiersSelects.forEach(select => {
                if (!select) return;
                if (window.jQuery && $.fn.select2) {
                    $(select).prop('disabled', isChecked).trigger('change');
                } else {
                    select.disabled = isChecked;
                }
            });
            if (isChecked && tiersSelects[0] && tiersSelects[1]) {
                const validOptions = Array.from(tiersSelects[0].options).filter(opt => opt.value !== "");
                if (validOptions.length > 0) {
                    const vFirst = validOptions[0].value;
                    const vLast = validOptions[validOptions.length - 1].value;
                    if (window.jQuery && $.fn.select2) {
                        $(tiersSelects[0]).val(vFirst).trigger('change');
                        $(tiersSelects[1]).val(vLast).trigger('change');
                    } else {
                        tiersSelects[0].value = vFirst;
                        tiersSelects[1].value = vLast;
                    }
                }
            }
        });
    }

    // ✅ Vérification avant enregistrement
    if (btnSave) {
        btnSave.addEventListener("click", function () {
            let isValid = true;

            inputs.forEach((input) => {
                if (selectAllTiersCheck && selectAllTiersCheck.checked && (input.id === "plan_tiers_id_1" || input.id === "plan_tiers_id_2")) {
                    return;
                }
                if (!input.value.trim()) {
                    input.classList.add("is-invalid");
                    isValid = false;
                } else {
                    input.classList.remove("is-invalid");
                }
            });
        });
    }

    // Réinitialisation quand on ferme via bouton ou croix
    const resetFormFields = () => {
        inputs.forEach((input) => {
            input.value = "";
            input.classList.remove("is-invalid");
            if (window.jQuery && $.fn.select2) {
                $('#plan_tiers_id_1').val('').trigger('change');
                $('#plan_tiers_id_2').val('').trigger('change');
            } else {
                if (document.getElementById('plan_tiers_id_1')) document.getElementById('plan_tiers_id_1').value = '';
                if (document.getElementById('plan_tiers_id_2')) document.getElementById('plan_tiers_id_2').value = '';
            }
            const errorDiv = document.getElementById("compte2-error");
            if (errorDiv) errorDiv.style.display = "none";
        });
        if (selectAllTiersCheck) {
            selectAllTiersCheck.checked = false;
            selectAllTiersCheck.dispatchEvent(new Event('change'));
        }
    };

    if (btnClose) btnClose.addEventListener("click", resetFormFields);
    if (modal) modal.addEventListener("hidden.bs.modal", resetFormFields);
});

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("grandLivreForm");
    if (!form) return;

    form.addEventListener("submit", function (event) {
        const selectAllTiersCheck = document.getElementById("selectAllTiers");
        const compte1Select = document.getElementById("plan_tiers_id_1");
        const compte2Select = document.getElementById("plan_tiers_id_2");
        const errorDiv = document.getElementById("compte2-error");

        if (selectAllTiersCheck && selectAllTiersCheck.checked) {
            if (compte1Select) compte1Select.disabled = false;
            if (compte2Select) compte2Select.disabled = false;
            return;
        }

        const compte1Text =
            compte1Select.options[compte1Select.selectedIndex]?.text || "";
        const compte2Text =
            compte2Select.options[compte2Select.selectedIndex]?.text || "";

        const compte1Num = compte1Text.split(" - ")[0].trim();
        const compte2Num = compte2Text.split(" - ")[0].trim();

        if (compte1Num && compte2Num && compte1Num > compte2Num) {
            event.preventDefault();

            compte2Select.classList.add("is-invalid");
            errorDiv.innerText =
                "Le tiers de fin doit être supérieur ou égal au tiers de début.";
            errorDiv.style.display = "block";
            if (window.jQuery && $.fn.selectpicker) $(compte2Select).selectpicker('refresh');
        } else {
            compte2Select.classList.remove("is-invalid");
            errorDiv.style.display = "none";
            if (window.jQuery && $.fn.selectpicker) $(compte2Select).selectpicker('refresh');
        }
    });
});

// suppression
const deleteModal = document.getElementById("deleteConfirmationModal");
if (deleteModal) {
    deleteModal.addEventListener("show.bs.modal", function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute("data-id");
        const filename = button.getAttribute("data-filename");
        const deleteForm = document.getElementById("deleteForm");

        const url = accounting_ledger_tiersDeleteUrl.replace('__ID__', id);
        deleteForm.action = url;

        const fileNameText = document.getElementById("fileNameToDelete");
        fileNameText.textContent = filename;
    });
}

// previsualiser le pdf 
document.addEventListener('DOMContentLoaded', function () {
    const previewButtons = document.querySelectorAll('.btn-preview-pdf');
    const pdfViewer = document.getElementById('pdfViewer');

    previewButtons.forEach(button => {
        button.addEventListener('click', function () {
            const pdfUrl = this.getAttribute('data-pdf-url');
            if (pdfUrl) {
                pdfViewer.src = pdfUrl;
            }
        });
    });

    const pdfModal = document.getElementById('pdfPreviewModal');
    if (pdfModal) {
        pdfModal.addEventListener('hidden.bs.modal', function () {
            if (pdfViewer) pdfViewer.src = '';
        });
    }
});

// previsualiser le pdf avant sauvegarde
const btnPreview = document.getElementById("btnPreview");
if (btnPreview) {
    btnPreview.addEventListener("click", function (event) {
        event.preventDefault();
        let form = document.getElementById("grandLivreForm");
        let formData = new FormData(form);

        const selectAllTiersCheck = document.getElementById("selectAllTiers");
        const compte1Select = document.getElementById("plan_tiers_id_1");
        const compte2Select = document.getElementById("plan_tiers_id_2");
        const errorDiv = document.getElementById("compte2-error");

        if (selectAllTiersCheck && selectAllTiersCheck.checked) {
            const validOptions = Array.from(compte1Select.options).filter(opt => opt.value !== "");
            if (validOptions.length > 0) {
                formData.set(compte1Select.name, validOptions[0].value);
                formData.set(compte2Select.name, validOptions[validOptions.length - 1].value);
            }
        } else {
            const compte1Text =
                compte1Select.options[compte1Select.selectedIndex]?.text || "";
            const compte2Text =
                compte2Select.options[compte2Select.selectedIndex]?.text || "";

            const compte1Num = compte1Text.split(" - ")[0].trim();
            const compte2Num = compte2Text.split(" - ")[0].trim();

            if (compte1Num && compte2Num && compte1Num > compte2Num) {
                compte2Select.classList.add("is-invalid");
                errorDiv.innerText =
                    "Le tiers de fin doit être supérieur ou égal au tiers de début.";
                errorDiv.style.display = "block";
                if (window.jQuery && $.fn.selectpicker) $(compte2Select).selectpicker('refresh');
                return;
            } else {
                compte2Select.classList.remove("is-invalid");
                errorDiv.style.display = "none";
                if (window.jQuery && $.fn.selectpicker) $(compte2Select).selectpicker('refresh');
            }
        }

        fetch(accounting_ledgerpreviewGrandLivreTiersUrl, {
            method: "POST",
            body: formData,
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById("pdfPreviewFrame").src = data.url;
                    let modal = new bootstrap.Modal(document.getElementById("modalPreviewPDF"));
                    modal.show();
                } else {
                    FlowToast.error(data.error || "Erreur lors de la prévisualisation.");
                }
            })
            .catch(err => {
                console.error("Erreur :", err);
                FlowToast.error("Impossible de générer la prévisualisation.");
            });
    });
}