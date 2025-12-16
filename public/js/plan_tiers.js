// 🔁 Réinitialise le formulaire de création quand le modal se ferme
const modalCreate = document.getElementById("modalCenterCreate");
modalCreate?.addEventListener("hidden.bs.modal", function () {
    const form = modalCreate.querySelector("form");
    if (form) {
        form.reset();
        form.querySelectorAll(".is-invalid").forEach((el) => {
            el.classList.remove("is-invalid");
        });
    }
});

// Gère le formulaire de mise à jour
document.addEventListener("DOMContentLoaded", function () {
    const updateModal = document.getElementById("modalCenterUpdate");
    const updateForm = document.getElementById("updateTiersForm");

    // Quand le modal s'ouvre, remplir les champs avec les données
    updateModal.addEventListener("show.bs.modal", function (event) {
        const button = event.relatedTarget;

        const id = button.getAttribute("data-id");
        const numero = button.getAttribute("data-numero");
        const intitule = button.getAttribute("data-intitule");
        const type = button.getAttribute("data-type");
        const compte = button.getAttribute("data-compte");

        document.getElementById("update_id").value = id;
        document.getElementById("update_numero").value = numero;
        document.getElementById("update_intitule").value = intitule;
        document.getElementById("update_type_de_tiers").value = type;
        document.getElementById("update_compte").value = compte;
        // console.log(compte);

        // Mise à jour de l'action du formulaire

        const updateUrl = plan_tiersUpdateBaseUrl.replace('__ID__', id);
        updateForm.action = updateUrl;

        // updateForm.action = `/plan_tiers/${id}`;
    });

    // Validation JS avant l'envoi du formulaire
    updateForm.addEventListener("submit", function (e) {
        const numero = document.getElementById("update_numero").value.trim();
        const intitule = document
            .getElementById("update_intitule")
            .value.trim();
        const type = document.getElementById("update_type_de_tiers").value;
        const compte = document.getElementById("update_compte").value;

        if (!numero || !intitule || !type || !compte) {
            e.preventDefault();
        }
    });

    //Réinitialisation du formulaire à la fermeture du modal
    updateModal.addEventListener("hidden.bs.modal", function () {
        updateForm.reset();
    });
});

// suppression

document.addEventListener("DOMContentLoaded", function () {
    const deleteModal = document.getElementById("deleteConfirmationModalTiers");
    const planNameDisplay = document.getElementById("planToDeleteNameTiers");
    const deleteForm = document.getElementById("deletePlanFormTiers");

    deleteModal.addEventListener("show.bs.modal", function (event) {
        const button = event.relatedTarget;
        const tierId = button.getAttribute("data-id");
        const tierName = button.getAttribute("data-name");

        // Met à jour le texte affiché
        planNameDisplay.textContent = tierName;

        // Met à jour l’action du formulaire

        const DeleteUrl = plan_tiersDeleteUrl.replace('__ID__', tierId);
        deleteForm.action = DeleteUrl;

        // deleteForm.action = `/plan_tiers/${tierId}`;
    });
});

// filtre

document.addEventListener("DOMContentLoaded", function () {
    const applyBtn = document.getElementById("apply-filters");
    const resetBtn = document.getElementById("reset-filters");

    const intituleInput = document.getElementById("filter-intitule");
    const typeInput = document.getElementById("filter-type");

    const rows = document.querySelectorAll("#tiersTable tbody tr");

    applyBtn.addEventListener("click", function () {
        const intituleVal = intituleInput.value.toLowerCase().trim();
        const typeVal = typeInput.value.toLowerCase().trim();

        rows.forEach((row) => {
            const [_, intituleCell, typeCell] = row.querySelectorAll("td");

            const matchIntitule = intituleCell.textContent
                .toLowerCase()
                .includes(intituleVal);
            const matchType = typeCell.textContent
                .toLowerCase()
                .includes(typeVal);

            row.style.display = matchIntitule && matchType ? "" : "none";
        });
    });

    resetBtn.addEventListener("click", function () {
        intituleInput.value = "";
        typeInput.value = "";

        rows.forEach((row) => (row.style.display = ""));
    });
});

// envoi données
document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".donnees-plan-tiers");

    buttons.forEach((button) => {
        button.addEventListener("click", () => {

            // console.log("URL générée :", plan_tiers_ecrituresSaisisUrl);

            const params = {
                id_plan_tiers: button.getAttribute("data-id"),
                intitule: button.getAttribute("data-intitule"),
                numero_de_tiers: button.getAttribute("data-numero_de_tiers"),
            };

            // // Affichage propre des données dans une alerte
            // let message = "Données récupérées :\n";
            // for (const [key, value] of Object.entries(params)) {
            //     message += `${key} : ${value}\n`;
            // }

            // alert(message);

            // Pour redirection plus tard
            const queryString = new URLSearchParams(params).toString();
            window.location.href = plan_tiers_ecrituresSaisisUrl + "?" + queryString;
        });
    });
});