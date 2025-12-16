document.addEventListener("DOMContentLoaded", function () {
    // Création modal (pour rappel, à adapter si besoin)
    const typeFieldCreate = document.getElementById("type");
    const formCreate = document.getElementById("formCodeJournal");
    const modalCreate = document.getElementById("modalCreateCodeJournal");

    const groupContrepartieCreate = document.getElementById(
        "group_compte_de_contrepartie"
    );
    const groupTresorerieCreate = document.getElementById(
        "group_compte_de_tresorerie"
    );
    const groupRapprochementCreate = document.getElementById(
        "group_rapprochement_sur"
    );

    if (typeFieldCreate) {
        typeFieldCreate.addEventListener("change", function () {
            const selectedType = this.value;
            groupContrepartieCreate.classList.add("d-none");
            groupTresorerieCreate.classList.add("d-none");
            groupRapprochementCreate.classList.add("d-none");

            if (selectedType === "General") {
                groupContrepartieCreate.classList.remove("d-none");
            } else if (selectedType === "Tresorerie") {
                groupTresorerieCreate.classList.remove("d-none");
                groupRapprochementCreate.classList.remove("d-none");
            }
        });
    }

    if (modalCreate) {
        modalCreate.addEventListener("hidden.bs.modal", function () {
            formCreate.reset();
            formCreate.classList.remove("was-validated");
            groupContrepartieCreate.classList.add("d-none");
            groupTresorerieCreate.classList.add("d-none");
            groupRapprochementCreate.classList.add("d-none");
        });
    }

    // --- Update modal ---

    const updateModal = document.getElementById("modalCenterUpdate");
    const formUpdate = document.getElementById("formCodeJournalUpdate");

    const groupContrepartieUpdate = document.getElementById(
        "update_group_compte_de_contrepartie"
    );
    const groupTresorerieUpdate = document.getElementById(
        "update_group_compte_de_tresorerie"
    );
    const groupRapprochementUpdate = document.getElementById(
        "update_group_rapprochement_sur"
    );

    const typeFieldUpdate = document.getElementById("update_type");

    // Fonction pour afficher/cacher les groupes conditionnels update selon le type
    function handleTypeChangeUpdate(selectedType) {
        groupContrepartieUpdate.classList.add("d-none");
        groupTresorerieUpdate.classList.add("d-none");
        groupRapprochementUpdate.classList.add("d-none");

        if (selectedType === "General") {
            groupContrepartieUpdate.classList.remove("d-none");
        } else if (selectedType === "Tresorerie") {
            groupTresorerieUpdate.classList.remove("d-none");
            groupRapprochementUpdate.classList.remove("d-none");
        }
    }

    if (typeFieldUpdate) {
        typeFieldUpdate.addEventListener("change", function () {
            handleTypeChangeUpdate(this.value);
        });
    }

    if (updateModal) {
        updateModal.addEventListener("show.bs.modal", function (event) {
            const button = event.relatedTarget;

            const id = button.getAttribute("data-id");
            // const annee = button.getAttribute("data-annee");
            // const mois = button.getAttribute("data-mois");
            const code = button.getAttribute("data-code");
            const type = button.getAttribute("data-type");
            const intitule = button.getAttribute("data-intitule");
            const traitement = button.getAttribute("data-traitement") ?? "0";
            const contrepartie = button.getAttribute(
                "data-compte_de_contrepartie"
            );
            const tresorerie = button.getAttribute("data-compte_de_tresorerie");
            const rapprochement = button.getAttribute("data-rapprochement_sur");

            document.getElementById("update_journal_id").value = id;

            const updateUrl = accounting_journalsUpdateBaseUrl.replace('__ID__', id);
            formUpdate.action = updateUrl;


            // formUpdate.action = `/accounting_journals/${id}`;

            // document.getElementById("update_annee").value = annee;
            // document.getElementById("update_mois").value = mois;
            document.getElementById("update_code_journal").value = code;
            typeFieldUpdate.value = type;
            document.getElementById("update_intitule").value = intitule;
            document.getElementById("update_traitement_analytique").value =
                traitement;

            // Gérer affichage des champs conditionnels selon type
            handleTypeChangeUpdate(type);

            // Remplir les champs conditionnels si présents
            if (contrepartie) {
                groupContrepartieUpdate.classList.remove("d-none");
                document.getElementById("update_compte_de_contrepartie").value =
                    contrepartie;
            } else {
                document.getElementById("update_compte_de_contrepartie").value =
                    "";
            }

            if (tresorerie) {
                groupTresorerieUpdate.classList.remove("d-none");
                document.getElementById("update_compte_de_tresorerie").value =
                    tresorerie;
            } else {
                document.getElementById("update_compte_de_tresorerie").value =
                    "";
            }

            if (rapprochement) {
                groupRapprochementUpdate.classList.remove("d-none");
                document.getElementById("update_rapprochement_sur").value =
                    rapprochement;
            } else {
                document.getElementById("update_rapprochement_sur").value = "";
            }
        });

        updateModal.addEventListener("hidden.bs.modal", function () {
            formUpdate.reset();
            formUpdate.classList.remove("was-validated");

            groupContrepartieUpdate.classList.add("d-none");
            groupTresorerieUpdate.classList.add("d-none");
            groupRapprochementUpdate.classList.add("d-none");
        });
    }

    // Optional: validation Bootstrap native, add was-validated class on submit attempt if invalid
    if (formUpdate) {
        formUpdate.addEventListener("submit", function (event) {
            if (!formUpdate.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                formUpdate.classList.add("was-validated");
            }
        });
    }
});

// suppression
document.addEventListener("DOMContentLoaded", function () {
    const deleteModal = document.getElementById("deleteConfirmationModal");
    const nameDisplay = document.getElementById("journalToDeleteName");
    const deleteForm = document.getElementById("deleteJournalForm");

    deleteModal.addEventListener("show.bs.modal", function (event) {
        const button = event.relatedTarget;
        const journalId = button.getAttribute("data-id");
        const journalName = button.getAttribute("data-name");

        // Affiche le nom dans le modal
        nameDisplay.textContent = journalName;

        // Met à jour l'URL d'action du formulaire

        const deleteUrl = accounting_journalsDeleteUrl.replace('__ID__', journalId);
        deleteForm.action = deleteUrl;


        // deleteForm.action = `/accounting_journals/${journalId}`;
    });
});

// filtre

document.addEventListener("DOMContentLoaded", function () {
    const codeInput = document.getElementById("filter-code");
    const intituleInput = document.getElementById("filter-intitule");
    const rows = document.querySelectorAll("#journalTable tbody tr");

    // Appliquer les filtres
    document
        .getElementById("apply-filters")
        .addEventListener("click", function () {
            const codeFilter = codeInput.value.toLowerCase();
            const intituleFilter = intituleInput.value.toLowerCase();

            rows.forEach((row) => {
                const code = row.cells[0].textContent.toLowerCase();
                const intitule = row.cells[2].textContent.toLowerCase();

                const match =
                    code.includes(codeFilter) &&
                    intitule.includes(intituleFilter);

                row.style.display = match ? "" : "none";
            });
        });

    // Réinitialiser les filtres
    document
        .getElementById("reset-filters")
        .addEventListener("click", function () {
            codeInput.value = "";
            intituleInput.value = "";

            rows.forEach((row) => (row.style.display = ""));
        });
});

// envoi des données vers une autre page
document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".show-accounting-entry");
    buttons.forEach((button) => {
        button.addEventListener("click", () => {
            const params = {
                id_journal: button.getAttribute("data-id"),
                annee: button.getAttribute("data-annee"),
                mois: button.getAttribute("data-mois"),
                code: button.getAttribute("data-code"),
                type: button.getAttribute("data-type"),
                intitule: button.getAttribute("data-intitule"),
                traitement: button.getAttribute("data-traitement"),
                compte_de_contrepartie: button.getAttribute(
                    "data-compte_de_contrepartie"
                ),
                compte_de_tresorerie: button.getAttribute(
                    "data-compte_de_tresorerie"
                ),
                rapprochement_sur: button.getAttribute(
                    "data-rapprochement_sur"
                ),
            };

            const queryString = new URLSearchParams(params).toString();
            window.location.href = "/accounting_entry_real?" + queryString;
        });
    });
});
