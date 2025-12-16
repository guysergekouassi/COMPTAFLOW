document.addEventListener("DOMContentLoaded", function () {
    const debitInput = document.getElementById("debit");
    const creditInput = document.getElementById("credit");

    function toggleFields() {
        if (debitInput.value && parseFloat(debitInput.value) > 0) {
            creditInput.disabled = true;
            creditInput.value = "";
        } else {
            creditInput.disabled = false;
        }

        if (creditInput.value && parseFloat(creditInput.value) > 0) {
            debitInput.disabled = true;
            debitInput.value = "";
        } else {
            debitInput.disabled = false;
        }
    }

    debitInput.addEventListener("input", toggleFields);
    creditInput.addEventListener("input", toggleFields);
});

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("formEcriture");
    const debitInput = document.getElementById("debit");
    const creditInput = document.getElementById("credit");
    const debitError = document.getElementById("debitError");
    const creditError = document.getElementById("creditError");

    // Activer/désactiver crédit/débit automatiquement
    debitInput.addEventListener("input", () => {
        if (debitInput.value && parseFloat(debitInput.value) !== 0) {  // <-- au lieu de > 0
            creditInput.disabled = true;
            creditInput.value = "";
            creditInput.classList.remove("is-invalid");
            creditError.textContent = "";
        } else {
            creditInput.disabled = false;
        }
    });

    creditInput.addEventListener("input", () => {
        if (creditInput.value && parseFloat(creditInput.value) !== 0) {  // <-- idem
            debitInput.disabled = true;
            debitInput.value = "";
            debitInput.classList.remove("is-invalid");
            debitError.textContent = "";
        } else {
            debitInput.disabled = false;
        }
    });

    // Fonction de validation complète
    window.validerEcriture = function () {
        let isValid = true;

        const requiredFields = [
            "date",
            "n_saisie",
            // "imputation",
            "description_operation",
            "compte_general",
            // "compte_tiers",
            "plan_analytique",
            // "piece_justificatif",
        ];

        requiredFields.forEach((id) => {
            const field = document.getElementById(id);
            if (!field.value || field.value.trim() === "") {
                field.classList.add("is-invalid");
                isValid = false;
            } else {
                field.classList.remove("is-invalid");
            }
        });

        // Validation fichier : vérifier le type et la taille (optionnel)
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            const allowedTypes = ["application/pdf", "image/jpeg", "image/png"];
            if (!allowedTypes.includes(file.type)) {
                fileInput.classList.add("is-invalid");
                fileInput.nextElementSibling.textContent =
                    "Format non autorisé. Utilisez PDF ou image (.jpg/.png).";
                isValid = false;
            } else {
                fileInput.classList.remove("is-invalid");
                fileInput.nextElementSibling.textContent = ""; // Nettoie le message d’erreur
            }
        } else {
            fileInput.classList.remove("is-invalid"); // Champ vide accepté
            fileInput.nextElementSibling.textContent = "";
        }

        // Validation des champs débit/crédit (au moins l’un)
        const debit = parseFloat(debitInput.value) || 0;
        const credit = parseFloat(creditInput.value) || 0;

        if (debit === 0 && credit === 0) {   // <-- vérifier uniquement qu’au moins un ≠ 0
            debitInput.classList.add("is-invalid");
            creditInput.classList.add("is-invalid");
            debitError.textContent =
                "Saisissez un montant (positif ou négatif) dans débit ou crédit.";
            creditError.textContent =
                "Saisissez un montant (positif ou négatif) dans débit ou crédit.";
            isValid = false;
        }


        if (isValid) {
            alert("Écriture comptable prête à être enregistrée.");
            // Ici tu peux faire un submit ou appeler une API :
            // form.submit();
        }
    };
});

// premier version

// document.addEventListener("DOMContentLoaded", function () {
//     const modal = document.getElementById("modalCenterCreate");
//     const form = document.getElementById("formEcriture");

//     // Lorsque le modal est complètement caché
//     modal.addEventListener("hidden.bs.modal", function () {
//         form.reset(); // Réinitialise les champs
//         form.querySelectorAll(".is-invalid").forEach((el) =>
//             el.classList.remove("is-invalid"),
//         ); // Nettoie les erreurs
//         document.getElementById("debit").disabled = false;
//         document.getElementById("credit").disabled = false;
//     });
// });

// deuxieme version
document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("modalCenterCreate");
    const form = document.getElementById("formEcriture");

    modal.addEventListener("hidden.bs.modal", function () {
        // Réinitialisation du formulaire
        form.reset();

        // Nettoyage des validations Bootstrap
        form.querySelectorAll(".is-invalid").forEach((el) =>
            el.classList.remove("is-invalid")
        );

        document.getElementById("debit").disabled = false;
        document.getElementById("credit").disabled = false;

        // Fix du backdrop persistant
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
    });
});


let ecritures = [];

function ajouterEcriture() {
    const form = document.getElementById("formEcriture");
    const debit = document.getElementById("debit");
    const credit = document.getElementById("credit");
    // AJOUTER CETTE LIGNE : Récupère l'ID du poste de trésorerie
    const tresorerieId = $('#compteTresorerieField').val();
    const typeFlux = $('#typeFlux').val();
    const typeFluxValue = $('#typeFluxField').val();

    const debitVal = parseFloat(debit.value) || 0;
    const creditVal = parseFloat(credit.value) || 0;

    if (!form.checkValidity() || (debitVal === 0 && creditVal === 0)) {
        form.classList.add("was-validated");

        if (debitVal === 0 && creditVal === 0) {
            debit.classList.add("is-invalid");
            credit.classList.add("is-invalid");
        }
        return;
    }


    // Conserver les champs souhaités
    const dateValue = form.date.value;
    const nSaisieValue = form.n_saisie.value;
    const descriptionValue = form.description_operation.value;
    const referencePieceValue = form.reference_piece.value;
// AJOUTER CES DEUX LIGNES

    // const selectedOption = form.compte_general.options[form.compte_general.selectedIndex];
    // const selectedOption2 = form.compte_tiers.options[form.compte_tiers.selectedIndex];
    // const selectedOption3 = form.imputation.input[form.imputation.selectedIndex];

    const data = {
        date: dateValue,
        n_saisie: nSaisieValue,

        journal: form.imputation.value,
        journal_code: form.imputation.getAttribute("data-code_imputation"),
        typeFlux: typeFlux,
        tresorerieFields: tresorerieId,
        exercices_comptables_id: form.exercices_comptables_id.value,
        journaux_saisis_id: form.journaux_saisis_id.value,
        description: descriptionValue,
        reference: referencePieceValue,

        compte_general: form.compte_general.value,
        compte_general_intitule: form.compte_general.options[
            form.compte_general.selectedIndex
        ].getAttribute("data-intitule_compte_general"),

        compte_tiers: form.compte_tiers.value,
        compte_tiers_intitule: form.compte_tiers.options[
            form.compte_tiers.selectedIndex
        ].getAttribute("data-intitule_tiers"),

        debit: parseFloat(form.debit.value) || 0,
        credit: parseFloat(form.credit.value) || 0,
        piece_justificatif: form.piece_justificatif.files[0] || null,
        analytique: form.plan_analytique.value === "1" ? "Oui" : "Non",
        // AJOUT : Poste de trésorerie et type de flux
        tresorerieFields: tresorerieId || null,
        tresorerieNom: tresorerieId ? $('#compteTresorerieField option:selected').text() : '-',
        typeFlux: typeFlux || null,
        typeFlux: typeFluxValue || null,
        typeFluxNom: typeFlux ? $('#typeFlux option:selected').text() : '-',
    };

    ecritures.push(data);
    afficherTableau();

    form.reset();
    // $('.selectpicker').selectpicker('deselectAll')


    // $('#compte_general').val('').selectpicker('deselectAll');
    // $('#compte_tiers').val('').selectpicker('deselectAll');
    // $('#plan_analytique').val('').selectpicker('deselectAll');

    $('#compte_general').selectpicker('val', '');
    $('#compte_tiers').selectpicker('val', '');
    $('#plan_analytique').selectpicker('val', '0'); // remettre sur "Non" par défaut


    // $('#compte_general option').prop('selected', function () {
    //     return this.defaultSelected;
    // });

    // $('#compte_general').selectpicker('refresh');

    form.classList.remove("was-validated");
    document.getElementById("debit").classList.remove("is-invalid");
    document.getElementById("credit").classList.remove("is-invalid");

    // Réappliquer les valeurs à conserver
    form.date.value = dateValue;
    form.n_saisie.value = nSaisieValue;
    form.description_operation.value = descriptionValue;
    form.reference_piece.value = referencePieceValue;

    // Rendre la date non modifiable (grisée)
    form.date.disabled = true;
    document.getElementById("debit").disabled = false;
    document.getElementById("credit").disabled = false;


}

function afficherTableau() {
    const tbody = document.querySelector("#tableEcritures tbody");
    tbody.innerHTML = "";

    let totalDebit = 0;
    let totalCredit = 0;

    ecritures.forEach((e, index) => {
        totalDebit += e.debit;
        totalCredit += e.credit;

        const row = `<tr>
      <td>${e.date}</td>
      <td>${e.n_saisie}</td>
      <td>${e.journal_code}</td>
      <td>${e.description}</td>
      <td>${e.reference}</td>
      <td>${e.compte_general_intitule}</td>
      <td>${e.compte_tiers_intitule}</td>
      <td>${e.debit.toFixed(2)}</td>
      <td>${e.credit.toFixed(2)}</td>
      <td>${e.tresorerieNom || '-'}</td>
      <td>${e.typeFluxNom || '-'}</td>
      <td>${e.piece_justificatif}</td>
      <td>${e.analytique}</td>
      <td><button class="btn btn-success" style="padding: 0.2rem 0.4rem; font-size: 0.75rem;" onclick="rechargerEcriture(${index})">Modifier</button></td>
      <td><button class="btn btn-danger" style="padding: 0.2rem 0.4rem; font-size: 0.75rem;" onclick="supprimerEcriture(${index})">Supprimer</button></td>
    </tr>`;
        tbody.insertAdjacentHTML("beforeend", row);
    });

    // Met à jour les totaux affichés
    // Format personnalisé FCFA sans afficher "FCFA"
    document.getElementById("totalDebit").textContent =
        totalDebit.toLocaleString("fr-FR", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });

    document.getElementById("totalCredit").textContent =
        totalCredit.toLocaleString("fr-FR", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
}

// function recalculerTotaux() {
//     let totalDebit = 0;
//     let totalCredit = 0;

//     const debitInputs = document.querySelectorAll("input[name^='debit-']");
//     const creditInputs = document.querySelectorAll("input[name^='credit-']");

//     debitInputs.forEach(input => {
//         totalDebit += parseFloat(input.value) || 0;
//     });

//     creditInputs.forEach(input => {
//         totalCredit += parseFloat(input.value) || 0;
//     });

//     document.getElementById("totalDebit").textContent = totalDebit.toLocaleString("fr-FR", {
//         minimumFractionDigits: 2,
//         maximumFractionDigits: 2,
//     });

//     document.getElementById("totalCredit").textContent = totalCredit.toLocaleString("fr-FR", {
//         minimumFractionDigits: 2,
//         maximumFractionDigits: 2,
//     });
// }

// function afficherTableau() {
//     const tbody = document.querySelector("#tableEcritures tbody");
//     tbody.innerHTML = "";

//     let totalDebit = 0;
//     let totalCredit = 0;

//     ecritures.forEach((e, index) => {
//         totalDebit += e.debit;
//         totalCredit += e.credit;

//         const row = `<tr>
//       <td><input type="date" class="form-control form-control-sm auto-width" value="${e.date}" name="date-${index}"></td>
//       <td><input type="text" class="form-control form-control-sm auto-width" value="${e.n_saisie}" name="n_saisie-${index}" readonly></td>
//       <td><input type="text" class="form-control form-control-sm auto-width" value="${e.journal_code}" name="journal-${index}"></td>
//       <td><input type="text" class="form-control form-control-sm auto-width" value="${e.description}" name="description-${index}"></td>
//       <td><input type="text" class="form-control form-control-sm auto-width" value="${e.reference}" name="reference-${index}"></td>
//       <td><input type="text" class="form-control form-control-sm auto-width" value="${e.compte_general_intitule}" name="compte_general-${index}"></td>
//       <td><input type="text" class="form-control form-control-sm auto-width" value="${e.compte_tiers_intitule}" name="compte_tiers-${index}"></td>
//       <td><input type="number" step="0.01" class="form-control form-control-sm auto-width" value="${e.debit}" name="debit-${index}" onchange="recalculerTotaux()"></td>
//       <td><input type="number" step="0.01" class="form-control form-control-sm auto-width" value="${e.credit}" name="credit-${index}" onchange="recalculerTotaux()"></td>
//       <td><input type="text" class="form-control form-control-sm auto-width" value="${e.piece_justificatif}" name="piece-${index}"></td>
//       <td><input type="text" class="form-control form-control-sm auto-width" value="${e.analytique}" name="analytique-${index}"></td>
//       <td><button class="btn btn-sm btn-danger" onclick="supprimerEcriture(${index})">Supprimer</button></td>
//     </tr>`;
//         tbody.insertAdjacentHTML("beforeend", row);
//     });

//     function ajusterLargeurInputs() {
//         document.querySelectorAll(".auto-width").forEach(input => {
//             input.style.width = "1px"; // reset width
//             input.style.width = (input.scrollWidth + 10) + "px";
//         });
//     }

//     // Appelle une première fois après le rendu
//     ajusterLargeurInputs();

//     // Et ajoute un listener pour que la largeur suive la saisie
//     document.addEventListener("input", function (e) {
//         if (e.target.classList.contains("auto-width")) {
//             e.target.style.width = "1px";
//             e.target.style.width = (e.target.scrollWidth + 10) + "px";
//         }
//     });



//     // Met à jour les totaux affichés
//     // Format personnalisé FCFA sans afficher "FCFA"
//     document.getElementById("totalDebit").textContent =
//         totalDebit.toLocaleString("fr-FR", {
//             minimumFractionDigits: 2,
//             maximumFractionDigits: 2,
//         });

//     document.getElementById("totalCredit").textContent =
//         totalCredit.toLocaleString("fr-FR", {
//             minimumFractionDigits: 2,
//             maximumFractionDigits: 2,
//         });
// }

function supprimerEcriture(index) {
    ecritures.splice(index, 1);
    afficherTableau();
}


function rechargerEcriture(index) {
    const ecriture = ecritures[index];

    // Préremplir les champs du modal
    const form = document.getElementById("formEcriture");

    form.date.value = ecriture.date;
    form.n_saisie.value = ecriture.n_saisie;
    form.description_operation.value = ecriture.description;
    form.reference_piece.value = ecriture.reference;

    // Imputation
    form.imputation.value = ecriture.journal;
    form.imputation.setAttribute("data-code_imputation", ecriture.journal_code);

    // Compte général
    form.compte_general.value = ecriture.compte_general;

    // Déclenche le comportement d'affichage du compte tiers
    form.compte_general.dispatchEvent(new Event('change'));

    // Compte tiers (s’il est affiché)
    if (form.compte_tiers) {
        form.compte_tiers.value = ecriture.compte_tiers;
    }

    // Plan analytique
    form.plan_analytique.value = ecriture.analytique === "Oui" ? "1" : "0";

    // Débit / Crédit
    form.debit.value = ecriture.debit;
    form.credit.value = ecriture.credit;

    // Remettre la date active si désactivée précédemment
    form.date.disabled = false;

    // Ouvrir le modal
    const modal = new bootstrap.Modal(document.getElementById('modalCenterCreate'));
    modal.show();

    // Supprimer immédiatement l'écriture
    ecritures.splice(index, 1);
    afficherTableau();
}


function enregistrerEcritures() {



    let totalDebit = 0;
    let totalCredit = 0;



    ecritures.forEach((e) => {
        totalDebit += e.debit;
        totalCredit += e.credit;
    });

    if (ecritures.length === 0) {
        const alertDiv = document.getElementById("ecritures-warning");

        // Affiche l'alerte
        alertDiv.classList.remove("d-none");
        alertDiv.classList.add("show");

        // Masque après 3 secondes (3000 ms)
        setTimeout(() => {
            alertDiv.classList.remove("show");
            alertDiv.classList.add("d-none");
        }, 3000);

        return;
    }

    if (totalDebit !== totalCredit) {
        const alertDiv = document.getElementById("balance-warning");

        // Affiche l'alerte
        alertDiv.classList.remove("d-none");
        alertDiv.classList.add("show");

        // Masque après 3 secondes (3000 ms)
        setTimeout(() => {
            alertDiv.classList.remove("show");
            alertDiv.classList.add("d-none");
        }, 3000);

        return;
    }

    const formData = new FormData();


    // const btn = document.getElementById("btnEnregistrer");
    const btnText = document.getElementById("btnText");
    const btnSpinner = document.getElementById("btnSpinner");

    // Afficher le spinner et masquer le texte
    btnText.classList.add("d-none");
    btnSpinner.classList.remove("d-none");

    ecritures.forEach((e, index) => {
        formData.append(`ecritures[${index}][date]`, e.date);
        formData.append(`ecritures[${index}][n_saisie]`, e.n_saisie);
        formData.append(`ecritures[${index}][journal]`, e.journal);
        formData.append(
            `ecritures[${index}][exercices_comptables_id]`,
            e.exercices_comptables_id,
        );
        formData.append(
            `ecritures[${index}][journaux_saisis_id]`,
            e.journaux_saisis_id,
        );
        formData.append(`ecritures[${index}][description]`, e.description);
        formData.append(`ecritures[${index}][reference]`, e.reference);
        formData.append(
            `ecritures[${index}][compte_general]`,
            e.compte_general,
        );
        formData.append(`ecritures[${index}][compte_tiers]`, e.compte_tiers);
        formData.append(`ecritures[${index}][debit]`, e.debit);
        formData.append(`ecritures[${index}][credit]`, e.credit);
        formData.append(`ecritures[${index}][analytique]`, e.analytique);
        // AJOUT : Poste de trésorerie et type de flux
        if (e.tresorerieFields) {
            formData.append(`ecritures[${index}][tresorerieFields]`, e.tresorerieFields);
        }
        if (e.typeFlux) {
            formData.append(`ecritures[${index}][typeFlux]`, e.typeFlux);
        }

        // Ajouter le fichier s’il existe
        if (e.piece_justificatif instanceof File) {
            formData.append(
                `ecritures[${index}][piece_justificatif]`,
                e.piece_justificatif,
            );
        }
    });

    // // 🔍 Affichage des données contenues dans formData
    // console.log("=== Données envoyées ===");
    // for (let pair of formData.entries()) {
    //     if (pair[1] instanceof File) {
    //         console.log(`${pair[0]}: [Fichier] ${pair[1].name}`);
    //     } else {
    //         console.log(`${pair[0]}: ${pair[1]}`);
    //     }
    // }

    document.getElementById('modalLoaderOverlay').classList.remove('d-none');

    fetch(accounting_entry_real_StoreSaisisUrl, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
        },
        body: formData,
    })
        .then(async (response) => {
            if (!response.ok) {
                // Gestion d'erreur HTTP (500, 404, etc.)
                const errorData = await response.json();
                throw new Error(errorData.details || 'Erreur inconnue.');
            }
            return response.json();
        })
        .then((data) => {
            // Réinitialisation des données côté client
            ecritures = [];
            document.querySelector("#tableEcritures tbody").innerHTML = "";

            // Affichage du message de succès
            const alertBox = document.getElementById("successAlert");
            const message = document.getElementById("successMessage");

            message.textContent = data.message || "Écritures enregistrées avec succès.";
            alertBox.classList.remove("d-none");

            // Replier l’alerte automatiquement après 5 secondes
            setTimeout(() => {
                alertBox.classList.add("d-none");
            }, 5000);

            // ✅ Rechargement de la page uniquement si tout s’est bien passé
            location.reload();
        })
        .catch((error) => {
            // Affichage d'une alerte d'erreur
            console.error(error);

            const errorBox = document.getElementById("errorAlert");
            const errorMessage = document.getElementById("errorMessage");

            if (errorBox && errorMessage) {
                errorMessage.textContent = error.message || "Erreur lors de l'enregistrement.";
                errorBox.classList.remove("d-none");

                // Réaffiche le texte et cache le spinner
                btnText.classList.remove("d-none");
                btnSpinner.classList.add("d-none");


                setTimeout(() => {
                    errorBox.classList.add("d-none");
                }, 7000);
            } else {
                alert("Erreur : " + error.message);
            }
        });

}

document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("modalCenterCreate");
    const form = document.getElementById("formEcriture");

    modal.addEventListener("hidden.bs.modal", function () {
        // Réinitialise tous les champs
        form.date.disabled = false;
        form.reset();
        ecritures = [];
        document.querySelector("#tableEcritures tbody").innerHTML = "";
        document.getElementById("totalDebit").textContent = (0).toLocaleString(
            "fr-FR",
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            },
        );

        document.getElementById("totalCredit").textContent = (0).toLocaleString(
            "fr-FR",
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            },
        );

        totalDebit = 0;
        totalCredit = 0;

        // Supprime les erreurs visuelles
        form.querySelectorAll(".is-invalid").forEach((el) =>
            el.classList.remove("is-invalid"),
        );
        form.classList.remove("was-validated");

        // Réactive les champs désactivés (débit/crédit)
        document.getElementById("debit").disabled = false;
        document.getElementById("credit").disabled = false;

        // Supprime les messages d’erreur personnalisés
        document.getElementById("debitError").textContent = "";
        document.getElementById("creditError").textContent = "";
    });
});

// filtre
document.getElementById("apply-filters").addEventListener("click", function () {
    const dateFilter = document.getElementById("filter-date").value.trim();
    const refFilter = document
        .getElementById("filter-ref")
        .value.trim()
        .toLowerCase();
    const compteGeneralFilter = document
        .getElementById("filter-compte-general")
        .value.trim()
        .toLowerCase();
    const compteTiersFilter = document
        .getElementById("filter-compte-tiers")
        .value.trim()
        .toLowerCase();

    const table = document.getElementById("table-ecritures");
    const rows = table.querySelectorAll("tbody tr");

    rows.forEach((row) => {
        const cells = row.cells;

        const dateText = cells[0].innerText.trim();
        const refText = cells[3].innerText.trim().toLowerCase();
        const compteGeneralText = cells[4].innerText.trim().toLowerCase();
        const compteTiersText = cells[5].innerText.trim().toLowerCase();

        let show = true;

        if (dateFilter && dateText !== dateFilter) {
            show = false;
        }
        if (refFilter && !refText.includes(refFilter)) {
            show = false;
        }
        if (
            compteGeneralFilter &&
            !compteGeneralText.includes(compteGeneralFilter)
        ) {
            show = false;
        }
        if (compteTiersFilter && !compteTiersText.includes(compteTiersFilter)) {
            show = false;
        }

        row.style.display = show ? "" : "none";
    });
});

// Fonction pour réinitialiser les filtres
document.getElementById("reset-filters").addEventListener("click", function () {
    document.getElementById("filter-date").value = "";
    document.getElementById("filter-ref").value = "";
    document.getElementById("filter-compte-general").value = "";
    document.getElementById("filter-compte-tiers").value = "";

    const table = document.getElementById("table-ecritures");
    const rows = table.querySelectorAll("tbody tr");
    rows.forEach((row) => {
        row.style.display = "";
    });
});

// In your Javascript (external .js resource or <script> tag)
// $(document).ready(function () {
//     $("#compte_general").select2();
// });

// envoyer les données
document.addEventListener("DOMContentLoaded", function () {
    const rows = document.querySelectorAll(".clickable-row");

    rows.forEach((row) => {
        row.addEventListener("click", () => {

            // console.log("URL générée :", accounting_entry_real_goupesSaisisUrl);

            const params = {
                n_saisie: row.getAttribute("data-n_saisie"),
                id_journal: row.getAttribute("data-id"),
                annee: row.getAttribute("data-annee"),
                mois: row.getAttribute("data-mois"),
                id_exercice: row.getAttribute("data-exercices_comptables_id"),
                id_code: row.getAttribute("data-code_journals_id"),
                code: row.getAttribute("data-code_journal"),
                traitement: row.getAttribute("data-traitement"),
                compte_de_contrepartie: row.getAttribute(
                    "data-compte_de_contrepartie",
                ),
                compte_de_tresorerie: row.getAttribute(
                    "data-compte_de_tresorerie",
                ),
                rapprochement_sur: row.getAttribute("data-rapprochement_sur"),
                intitule: row.getAttribute("data-intitule"),
                type: row.getAttribute("data-type"),
            };

            const queryString = new URLSearchParams(params).toString();
            window.location.href = accounting_entry_real_goupesSaisisUrl + "?" + queryString;
        });
    });
});



