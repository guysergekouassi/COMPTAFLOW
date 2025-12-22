// --- Validation du formulaire (Création) ---
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("planComptableForm");
    const modal = document.getElementById("modalCenterCreate");

    if (form) {
        form.addEventListener("submit", function (e) {
            let isValid = true;
            const fields = ["numero_de_compte", "intitule"];

            fields.forEach((id) => {
                const field = document.getElementById(id);
                if (field && !field.value.trim()) {
                    isValid = false;
                    field.classList.add("is-invalid");
                } else if (field) {
                    field.classList.remove("is-invalid");
                }
            });

            if (!isValid) e.preventDefault();
        });

        modal.addEventListener("hidden.bs.modal", function () {
            form.reset();
            form.querySelectorAll(".is-invalid").forEach((el) =>
                el.classList.remove("is-invalid"),
            );
        });
    }
});

// --- Gestion de la mise à jour (Update) ---
document.addEventListener("DOMContentLoaded", function () {
    const editButtons = document.querySelectorAll(
        '[data-bs-target="#modalCenterUpdate"]',
    );

    editButtons.forEach((button) => {
        button.addEventListener("click", function () {
            const id = this.dataset.id;
            document.getElementById("update_planId").value = id;
            document.getElementById("update_numero_de_compte").value = this.dataset.numero_de_compte;
            document.getElementById("update_intitule").value = this.dataset.intitule;

            const form = document.getElementById("updatePlanForm");
            if (form) {
                const updateUrl = planComptableUpdateBaseUrl.replace('__ID__', id);
                form.action = updateUrl;
            }
        });
    });

    // Validation Update
    const updateForm = document.getElementById("updatePlanForm");
    const updateModal = document.getElementById("modalCenterUpdate");

    if (updateForm) {
        updateForm.addEventListener("submit", function (e) {
            let isValid = true;
            const fields = ["update_numero_de_compte", "update_intitule"];

            fields.forEach((id) => {
                const field = document.getElementById(id);
                if (field && !field.value.trim()) {
                    isValid = false;
                    field.classList.add("is-invalid");
                } else if (field) {
                    field.classList.remove("is-invalid");
                }
            });

            if (!isValid) e.preventDefault();
        });

        updateModal.addEventListener("hidden.bs.modal", function () {
            updateForm.reset();
            updateForm.querySelectorAll(".is-invalid").forEach((el) =>
                el.classList.remove("is-invalid"),
            );
        });
    }
});

// --- Gestion de la suppression ---
document.addEventListener("DOMContentLoaded", function () {
    const deleteModal = document.getElementById("deleteConfirmationModal");
    const planToDeleteName = document.getElementById("planToDeleteName");
    const deleteForm = document.getElementById("deletePlanForm");

    if (deleteModal) {
        deleteModal.addEventListener("show.bs.modal", function (event) {
            const button = event.relatedTarget;
            const planId = button.getAttribute("data-id");
            const planIntitule = button.getAttribute("data-intitule");

            planToDeleteName.textContent = planIntitule;
            const deleteUrl = plan_comptableDeleteUrl.replace('__ID__', planId);
            deleteForm.action = deleteUrl;
        });
    }
});

// --- Gestion de la consultation (View) ---
document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".donnees-plan-comptable");

    buttons.forEach((button) => {
        button.addEventListener("click", () => {
            const params = {
                id_plan_comptable: button.getAttribute("data-id"),
                intitule: button.getAttribute("data-intitule"),
                numero_de_compte: button.getAttribute("data-numero_de_compte"),
            };
            const queryString = new URLSearchParams(params).toString();
            window.location.href = plan_comptable_ecrituresSaisisUrl + "?" + queryString;
        });
    });
});

// --- Gestion par défaut ---
document.addEventListener("DOMContentLoaded", function () {
    const Plan_defaut = document.getElementById("Plan_defaut");
    if (Plan_defaut) {
        Plan_defaut.addEventListener("show.bs.modal", function () {
            Plan_defaut.action = planComptableDefautUrl;
        });
    }
});

// --- Validation Numéro de compte (AJAX) ---
$(document).ready(function () {
    let lastPaddedValue = "";

    $("#numero_de_compte").on("input", function () {
        let rawInput = $(this).val().replace(/\D/g, "");
        let padded = rawInput.padEnd(8, "0");

        if (rawInput.length === 0) {
            $("#numero_compte_feedback").text("").removeClass("text-danger text-success");
            return;
        }

        if (padded === lastPaddedValue) return;
        lastPaddedValue = padded;

        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        });

        $.ajax({
            url: verifierNumeroUrl,
            method: "POST",
            data: { numero_de_compte: padded },
            success: function (response) {
                if (response.exists) {
                    $("#numero_compte_feedback")
                        .text("❌ Ce numéro de compte existe déjà.")
                        .removeClass("text-success")
                        .addClass("text-danger");
                } else {
                    $("#numero_compte_feedback")
                        .text("✅ Ce compte peut être créé.")
                        .removeClass("text-danger")
                        .addClass("text-success");
                }
            },
            error: function (xhr, status, error) {
                console.error("Erreur AJAX :", status, error);
            },
        });
    });
});

// --- Fin du fichier ---
