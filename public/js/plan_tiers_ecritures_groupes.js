function exporterTableau2() {

    

    const rows = document.querySelectorAll("#tableau-statique tbody tr");
    let data = [];
    let totalDebit = 0;
    let totalCredit = 0;

    rows.forEach((row) => {
        const cells = row.querySelectorAll("td");

        const selectCompteGeneral = cells[6].querySelector("select");
        const selectJournal = cells[5].querySelector("select");
        const selectTiers = cells[7].querySelector("select");
        const selectAnalytique = cells[8].querySelector("select");

        const debit =
            parseFloat(
                cells[9].innerText.trim().replace(/\s/g, "").replace(",", "."),
            ) || 0;
        const credit =
            parseFloat(
                cells[10].innerText.trim().replace(/\s/g, "").replace(",", "."),
            ) || 0;

        totalDebit += debit;
        totalCredit += credit;

        data.push({
            id: cells[0].innerText.trim(),
            date: cells[1].innerText.trim(),
            n_saisie: cells[2].innerText.trim(),
            reference: cells[3].innerText.trim(),
            description: cells[4].innerText.trim(),
            journal_saisie: selectJournal ? selectJournal.value : "",
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

    // --- On envoie à Laravel si équilibré ---
    fetch(plan_tiers_ecritures_groupesUpdateBaseUrl, {
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

            // Optionnel : rafraîchissement ou fermeture modal
            location.reload();

            // Replier après 5 secondes
            setTimeout(() => {
                successAlert.classList.add("d-none");
            }, 5000);
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

function exporterTableau() {
    const rows = document.querySelectorAll("#tableau-statique tbody tr");
    let data = [];
    let totalDebit = 0;
    let totalCredit = 0;

    rows.forEach((row) => {
        const cells = row.querySelectorAll("td");

        const selectCompteGeneral = cells[6].querySelector("select");
        const selectJournal = cells[5].querySelector("select");
        const selectTiers = cells[7].querySelector("select");
        const selectAnalytique = cells[8].querySelector("select");

        const debit =
            parseFloat(
                cells[9].innerText.trim().replace(/\s/g, "").replace(",", "."),
            ) || 0;
        const credit =
            parseFloat(
                cells[10].innerText.trim().replace(/\s/g, "").replace(",", "."),
            ) || 0;

        totalDebit += debit;
        totalCredit += credit;

        data.push({
            id: cells[0].innerText.trim(),
            date: cells[1].innerText.trim(),
            n_saisie: cells[2].innerText.trim(),
            reference: cells[3].innerText.trim(),
            description: cells[4].innerText.trim(),
            journal_saisie: selectJournal ? selectJournal.value : "",
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

    // Exporter si équilibré
    const blob = new Blob([JSON.stringify(data, null, 2)], {
        type: "application/json",
    });
    const url = URL.createObjectURL(blob);
    const a = document.createElement("a");
    a.href = url;
    a.download = "tableau_statique.json";
    a.click();
    URL.revokeObjectURL(url);
}

function recalculerTotaux() {
    const rows = document.querySelectorAll("#tableau-statique tbody tr");
    let totalDebit = 0;
    let totalCredit = 0;

    rows.forEach((row) => {
        const debitCell = row.cells[9];
        const creditCell = row.cells[10];

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
        if ([9, 10].includes(tdIndex)) {
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
