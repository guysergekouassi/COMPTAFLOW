document.addEventListener("DOMContentLoaded", function () {
    const modalElement = document.getElementById("modalCenterUpdate");
    modalElement.addEventListener("hidden.bs.modal", function () {
        const previewContainer = document.getElementById(
            "preview_piece_justificative",
        );
        const previewLink = document.getElementById("lien_piece_justificative");

        if (previewContainer && previewLink) {
            previewLink.href = "#";
            previewContainer.style.display = "none";
        }
    });

    const lignes = document.querySelectorAll(".clickable-row");
    const compteGeneral = document.getElementById("compte_general");
    const compteTiersWrapper = document.getElementById("compte_tiers_wrapper");
    const compteTiers = document.getElementById("compte_tiers");
    const planAnalytique = document.getElementById("plan_analytique");
    const debitInput = document.getElementById("debit");
    const creditInput = document.getElementById("credit");

    // Fonction pour forcer l'affichage/masquage du champ compte tiers
    function handleCompteTiersDisplay() {
        const selectedOption =
            compteGeneral.options[compteGeneral.selectedIndex];
        const numeroCompte =
            selectedOption?.getAttribute("data-intitule_compte_general") || "";

        if (numeroCompte.startsWith("4")) {
            compteTiersWrapper.style.display = "block";
        } else {
            compteTiersWrapper.style.display = "none";
            if (compteTiers) compteTiers.value = "";
        }
    }

    // Fonction pour empêcher les deux montants à la fois
    function toggleMontantFields() {
        const debitValue = parseFloat(debitInput.value) || 0;
        const creditValue = parseFloat(creditInput.value) || 0;

        if (debitValue > 0) {
            creditInput.disabled = true;
        } else {
            creditInput.disabled = false;
        }

        if (creditValue > 0) {
            debitInput.disabled = true;
        } else {
            debitInput.disabled = false;
        }

        if (debitValue === 0 && creditValue === 0) {
            debitInput.disabled = false;
            creditInput.disabled = false;
        }
    }

    // Attacher l'écouteur sur changement du compte général
    if (compteGeneral) {
        compteGeneral.addEventListener("change", handleCompteTiersDisplay);
    }

    // Attacher l’écouteur sur input des montants
    if (debitInput && creditInput) {
        debitInput.addEventListener("input", toggleMontantFields);
        creditInput.addEventListener("input", toggleMontantFields);
    }

    // Lors du clic sur une ligne à modifier
    lignes.forEach((ligne) => {
        ligne.addEventListener("click", function () {
            const data = this.dataset;
            const modal = new bootstrap.Modal(modalElement);

            // Remplir les champs texte
            document.getElementById("n_saisie").value = data.n_saisie || "";
            document.getElementById("date").value = data.date || "";
            document.getElementById("description_operation").value =
                data.description_operation || "";
            document.getElementById("reference_piece").value =
                data.reference_piece || "";

            if (document.getElementById("imputation")) {
                document.getElementById("imputation").value =
                    data.code_journals_id || "";
            }

            // Compte général
            if (compteGeneral) {
                compteGeneral.value = data.plan_comptable_id || "";
                handleCompteTiersDisplay(); // mettre à jour le champ compte tiers en fonction du nouveau compte général
            }

            // Compte tiers
            if (compteTiers && data.plan_tiers_id) {
                compteTiers.value = data.plan_tiers_id;
            }

            // Plan analytique
            if (planAnalytique && data.plan_analytique !== undefined) {
                planAnalytique.value = data.plan_analytique;
            }

            // Débit / Crédit
            if (debitInput) debitInput.value = data.debit || "";
            if (creditInput) creditInput.value = data.credit || "";
            toggleMontantFields(); // ajuster blocage si un champ contient une valeur

            // Pièce justificative
            const pieceJustif = data.piece_justificatif;
            const previewContainer = document.getElementById(
                "preview_piece_justificative",
            );
            const previewLink = document.getElementById(
                "lien_piece_justificative",
            );

            if (pieceJustif && previewContainer && previewLink) {
                const baseUrl = "/justificatifs/"; // ← ajuste le chemin si différent !
                previewLink.href = baseUrl + pieceJustif;
                previewLink.textContent = "Voir la pièce justificative";
                previewContainer.style.display = "block";
            } else if (previewContainer) {
                previewContainer.style.display = "none";
            }

            // alert(data.id)

            const form = document.getElementById("formEcriture");
            const baseAction = form.getAttribute("data-base-action"); // à définir côté Blade (voir ci-dessous)
            form.action = baseAction.replace("ID_REPLACE", data.id);

            modal.show();
        });
    });
});

function enregistrerEcritures() {
    document.getElementById("formEcriture").submit();
}

// alerte
// let hasMismatch = totalDebit !== totalCredit;

// window.addEventListener("beforeunload", function (e) {
//     if (hasMismatch) {
//         // Affiche un message de confirmation (pas très personnalisable)
//         const confirmationMessage =
//             "Le total du débit est différent du crédit. Êtes-vous sûr de vouloir quitter ?";
//         (e || window.event).returnValue = confirmationMessage;
//         return confirmationMessage;
//     }
// });

// // Bonus : si l'utilisateur clique sur un lien ou un bouton de navigation interne
// document.querySelectorAll("a, button").forEach((el) => {
//     el.addEventListener("click", function (e) {
//         if (hasMismatch && !el.closest(".modal")) {
//             e.preventDefault();
//             let modal = new bootstrap.Modal(
//                 document.getElementById("modalMismatch"),
//             );
//             modal.show();
//         }
//     });
// });

// modal 2
function exporterTableau() {

    

    const rows = document.querySelectorAll("#tableau-statique tbody tr");
    let data = [];
    let totalDebit = 0;
    let totalCredit = 0;

    rows.forEach((row) => {
        const cells = row.querySelectorAll("td");

        const selectCompteGeneral = cells[5].querySelector("select");
        const selectTiers = cells[6].querySelector("select");
        const selectAnalytique = cells[7].querySelector("select");

        const debit =
            parseFloat(
                cells[8].innerText.trim().replace(/\s/g, "").replace(",", "."),
            ) || 0;
        const credit =
            parseFloat(
                cells[9].innerText.trim().replace(/\s/g, "").replace(",", "."),
            ) || 0;

        totalDebit += debit;
        totalCredit += credit;

        data.push({
            id: cells[0].innerText.trim(),
            date: cells[1].innerText.trim(),
            n_saisie: cells[2].innerText.trim(),
            reference: cells[3].innerText.trim(),
            description: cells[4].innerText.trim(),
            compte_general: selectCompteGeneral
                ? selectCompteGeneral.value
                : "",
            compte_tiers: selectTiers ? selectTiers.value : "",
            plan_analytique: selectAnalytique ? selectAnalytique.value : "",
            debit: debit.toFixed(2),
            credit: credit.toFixed(2),
        });
    });

    // Affiche les totaux
    document.getElementById("total-debit").textContent =
        totalDebit.toLocaleString("fr-FR", { minimumFractionDigits: 2 });
    document.getElementById("total-credit").textContent =
        totalCredit.toLocaleString("fr-FR", { minimumFractionDigits: 2 });

    const erreurEquilibre = document.getElementById("erreur-equilibre");

    if (Math.abs(totalDebit - totalCredit) > 0.01) {
        erreurEquilibre.classList.remove("d-none");
        return;
    } else {
        erreurEquilibre.classList.add("d-none");
    }

    document.getElementById('modalLoaderOverlay').classList.remove('d-none');

    fetch(accounting_entry_real_goupesUpdateBaseUrl, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                .content,
        },
        body: JSON.stringify({ lignes: data }),
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error("Échec de la mise à jour.");
            }
            return response.json();
        })
        .then((result) => {
            // Affichage alerte succès
            const successAlert = document.getElementById("groupSuccessAlert");
            const successMessage = document.getElementById(
                "groupSuccessMessage",
            );
            successMessage.textContent =
                result.message || "Mise à jour réussie !";
            successAlert.classList.remove("d-none");

            // Replier après 5 secondes
            setTimeout(() => {
                successAlert.classList.add("d-none");
            }, 5000);

            // Optionnel : rafraîchissement ou fermeture modal
            location.reload();
        })
        .catch((error) => {
            // Affichage alerte erreur
            const errorAlert = document.getElementById("groupErrorAlert");
            const errorMessage = document.getElementById("groupErrorMessage");
            errorMessage.textContent =
                error.message || "Erreur lors de la mise à jour.";
            errorAlert.classList.remove("d-none");

            setTimeout(() => {
                errorAlert.classList.add("d-none");
            }, 5000);
        });
}

// function exporterTableau() {
//     const rows = document.querySelectorAll("#tableau-statique tbody tr");
//     let data = [];
//     let totalDebit = 0;
//     let totalCredit = 0;

//     rows.forEach((row) => {
//         const cells = row.querySelectorAll("td");

//         const selectCompteGeneral = cells[5].querySelector("select");
//         const selectTiers = cells[6].querySelector("select");
//         const selectAnalytique = cells[7].querySelector("select");

//         const debit =
//             parseFloat(
//                 cells[8].innerText.trim().replace(/\s/g, "").replace(",", "."),
//             ) || 0;
//         const credit =
//             parseFloat(
//                 cells[9].innerText.trim().replace(/\s/g, "").replace(",", "."),
//             ) || 0;

//         totalDebit += debit;
//         totalCredit += credit;

//         data.push({
//             id: cells[0].innerText.trim(),
//             date: cells[1].innerText.trim(),
//             n_saisie: cells[2].innerText.trim(),
//             reference: cells[3].innerText.trim(),
//             description: cells[4].innerText.trim(),
//             compte_general: selectCompteGeneral
//                 ? selectCompteGeneral.value
//                 : "",
//             compte_tiers: selectTiers ? selectTiers.value : "",
//             plan_analytique: selectAnalytique ? selectAnalytique.value : "",
//             debit: debit.toFixed(2),
//             credit: credit.toFixed(2),
//         });
//     });

//     // Affiche les totaux
//     document.getElementById("total-debit").textContent =
//         totalDebit.toLocaleString("fr-FR", { minimumFractionDigits: 2 });
//     document.getElementById("total-credit").textContent =
//         totalCredit.toLocaleString("fr-FR", { minimumFractionDigits: 2 });

//     const erreurEquilibre = document.getElementById("erreur-equilibre");

//     if (Math.abs(totalDebit - totalCredit) > 0.01) {
//         erreurEquilibre.classList.remove("d-none");
//         return;
//     } else {
//         erreurEquilibre.classList.add("d-none");
//     }

//     // Exporter si équilibré
//     const blob = new Blob([JSON.stringify(data, null, 2)], {
//         type: "application/json",
//     });
//     const url = URL.createObjectURL(blob);
//     const a = document.createElement("a");
//     a.href = url;
//     a.download = "tableau_statique.json";
//     a.click();
//     URL.revokeObjectURL(url);
// }

function recalculerTotaux() {
    const rows = document.querySelectorAll("#tableau-statique tbody tr");
    let totalDebit = 0;
    let totalCredit = 0;

    rows.forEach((row) => {
        const debitCell = row.cells[8];
        const creditCell = row.cells[9];

        const debit =
            parseFloat(
                debitCell.innerText.trim().replace(/\s/g, "").replace(",", "."),
            ) || 0;
        const credit =
            parseFloat(
                creditCell.innerText
                    .trim()
                    .replace(/\s/g, "")
                    .replace(",", "."),
            ) || 0;

        totalDebit += debit;
        totalCredit += credit;
    });

    // Mettre à jour les affichages
    document.getElementById("total-debit").textContent =
        totalDebit.toLocaleString("fr-FR", { minimumFractionDigits: 2 });
    document.getElementById("total-credit").textContent =
        totalCredit.toLocaleString("fr-FR", { minimumFractionDigits: 2 });

    // Message d’erreur si déséquilibre
    const erreurEquilibre = document.getElementById("erreur-equilibre");
    if (Math.abs(totalDebit - totalCredit) > 0.01) {
        erreurEquilibre.classList.remove("d-none");
    } else {
        erreurEquilibre.classList.add("d-none");
    }
}

// Recalcul au chargement du modal
document
    .getElementById("modalTableauStatique")
    .addEventListener("shown.bs.modal", function () {
        recalculerTotaux();
    });

// Recalcul dynamique à chaque modification de cellule
document
    .querySelector("#tableau-statique tbody")
    .addEventListener("input", function (event) {
        // Cible uniquement les colonnes Débit et Crédit
        const tdIndex =
            event.target.cellIndex ?? event.target.parentElement.cellIndex;
        if ([8, 9].includes(tdIndex)) {
            recalculerTotaux();
        }
    });

// empecher la saisie double
document.addEventListener("DOMContentLoaded", () => {
    const tableau = document.getElementById("tableau-statique");

    tableau.addEventListener("input", function (e) {
        const target = e.target;

        if (target.classList.contains("cell-debit")) {
            const creditCell =
                target.parentElement.querySelector(".cell-credit");
            if (target.textContent.trim() !== "") {
                creditCell.textContent = "";
            }
        }

        if (target.classList.contains("cell-credit")) {
            const debitCell = target.parentElement.querySelector(".cell-debit");
            if (target.textContent.trim() !== "") {
                debitCell.textContent = "";
            }
        }
    });
});

// recharger apres avoir appuyer sur fermer
