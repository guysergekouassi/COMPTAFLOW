document
    .getElementById("formCreateExercice")
    .addEventListener("submit", function (e) {
        // Récupérer les champs
        const dateDebut = document.getElementById("date_debut");
        const dateFin = document.getElementById("date_fin");
        const intitule = document.getElementById("intitule_exercice");
        const btnSubmit = document.getElementById("btnSubmitExercice");

        // Récupérer les containers d'erreur
        const errorDebut = document.getElementById("error_date_debut");
        const errorFin = document.getElementById("error_date_fin");
        const errorIntitule = document.getElementById("error_intitule");

        // Réinitialiser les messages d’erreur
        errorDebut.textContent = "";
        errorFin.textContent = "";
        errorIntitule.textContent = "";

        let hasError = false;

        // Vérification date début
        if (!dateDebut.value) {
            errorDebut.textContent = "Veuillez renseigner la date de début.";
            hasError = true;
        }

        // Vérification date fin
        if (!dateFin.value) {
            errorFin.textContent = "Veuillez renseigner la date de fin.";
            hasError = true;
        }

        // Vérification logique des dates
        if (
            dateDebut.value &&
            dateFin.value &&
            new Date(dateFin.value) < new Date(dateDebut.value)
        ) {
            errorFin.textContent =
                "La date de fin ne peut pas être antérieure à la date de début.";
            hasError = true;
        }

        if (hasError) {
            e.preventDefault(); // Annuler la soumission si erreur
        } else {
            // Afficher l'état de chargement
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = `
                    <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    Enregistrement...
                `;
            }
        }
    });

// Réinitialise les champs et erreurs à la fermeture du modal
const modalCreate = document.getElementById("modalCenterCreate");
if (modalCreate) {
    modalCreate.addEventListener("hidden.bs.modal", function () {
        // Réinitialiser tous les champs du formulaire
        const form = document.getElementById("formCreateExercice");
        if (form) form.reset();

        // Réinitialiser le bouton
        const btnSubmit = document.getElementById("btnSubmitExercice");
        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = "Enregistrer";
        }

        // Effacer les messages d'erreur
        if (document.getElementById("error_date_debut")) document.getElementById("error_date_debut").textContent = "";
        if (document.getElementById("error_date_fin")) document.getElementById("error_date_fin").textContent = "";
        if (document.getElementById("error_intitule")) document.getElementById("error_intitule").textContent = "";
    });
}

// filtre
document.getElementById("apply-filters").addEventListener("click", function () {
    const dateDebutFilter = document.getElementById("filter-date-debut").value;
    const dateFinFilter = document.getElementById("filter-date-fin").value;

    const rows = document.querySelectorAll("#exerciceTable tbody tr");

    rows.forEach((row) => {
        const dateDebut = row.children[0].textContent.trim(); // format dd/mm/yyyy
        const dateFin = row.children[1].textContent.trim();

        const [dd1, mm1, yyyy1] = dateDebut.split("/");
        const [dd2, mm2, yyyy2] = dateFin.split("/");

        const rowDateDebut = new Date(`${yyyy1}-${mm1}-${dd1}`);
        const rowDateFin = new Date(`${yyyy2}-${mm2}-${dd2}`);

        const filterDebutDate = dateDebutFilter
            ? new Date(dateDebutFilter)
            : null;
        const filterFinDate = dateFinFilter ? new Date(dateFinFilter) : null;

        let show = true;

        if (filterDebutDate && rowDateDebut < filterDebutDate) {
            show = false;
        }

        if (filterFinDate && rowDateFin > filterFinDate) {
            show = false;
        }

        row.style.display = show ? "" : "none";
    });
});

document.getElementById("reset-filters").addEventListener("click", function () {
    document.getElementById("filter-date-debut").value = "";
    document.getElementById("filter-date-fin").value = "";

    const rows = document.querySelectorAll("#exerciceTable tbody tr");
    rows.forEach((row) => (row.style.display = ""));
});

// delete
document.addEventListener("DOMContentLoaded", function () {
    const deleteModal = document.getElementById("deleteConfirmationModal");
    const deleteForm = document.getElementById("deleteForm");
    const projectToDelete = document.getElementById("projectToDelete");

    deleteModal.addEventListener("show.bs.modal", function (event) {
        const button = event.relatedTarget;
        const exerciceId = button.getAttribute("data-id");
        const exerciceLabel =
            button.getAttribute("data-label") || "cet exercice";

        // Met à jour l'action du formulaire

        const deleteUrl = exercice_comptableDeleteUrl.replace('__ID__', exerciceId);
        deleteForm.action = deleteUrl;


        // deleteForm.action = `/exercice_comptable/${exerciceId}`;

        // Affiche l'intitulé dans le message (optionnel)
        projectToDelete.textContent = exerciceLabel;
    });
});

// envoi des données
document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".show-accounting-entries");

    buttons.forEach((button) => {
        button.addEventListener("click", () => {

            // console.log("URL générée :", journauxSaisisUrl);

            const params = {
                id_exercice: button.getAttribute("data-id"),
                date_debut: button.getAttribute("data-date_debut"),
                date_fin: button.getAttribute("data-date_fin"),
                intitule: button.getAttribute("data-intitule"),
            };

            // journauxSaisisUrl est défini dans Blade juste avant le script
            const queryString = new URLSearchParams(params).toString();
            window.location.href = journauxSaisisUrl + "?" + queryString;
        });
    });
});


// cloturer l'exercice
document.addEventListener("DOMContentLoaded", function () {
    const clotureButtons = document.querySelectorAll(".open-cloture-modal");
    const clotureForm = document.getElementById("clotureForm");
    const exerciceLabel = document.getElementById("exerciceToCloture");

    clotureButtons.forEach((button) => {
        button.addEventListener("click", () => {
            const exerciceId = button.getAttribute("data-id");
            const intitule = button.getAttribute("data-intitule");

            const cloturerUrl = exercice_comptableCloturerUrl.replace('__ID__', exerciceId);
            clotureForm.action = cloturerUrl;

            // clotureForm.action = `/exercice_comptable/${exerciceId}`;
            exerciceLabel.textContent = `Exercice : ${intitule}`;
        });
    });
});
