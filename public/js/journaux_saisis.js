// envoyer les donnees
document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".show-accounting-journals");

    buttons.forEach((button) => {
        button.addEventListener("click", () => {

            // console.log("URL générée :", accounting_entry_realSaisisUrl);
            const params = {
                id_journal: button.getAttribute("data-id"),
                annee: button.getAttribute("data-annee"),
                mois: button.getAttribute("data-mois"),
                id_exercice: button.getAttribute(
                    "data-exercices_comptables_id",
                ),
                id_code: button.getAttribute("data-code_journals_id"),
                code: button.getAttribute("data-code_journal"),
                traitement: button.getAttribute("data-traitement"),
                compte_de_contrepartie: button.getAttribute(
                    "data-compte_de_contrepartie",
                ),
                compte_de_tresorerie: button.getAttribute(
                    "data-compte_de_tresorerie",
                ),
                rapprochement_sur: button.getAttribute(
                    "data-rapprochement_sur",
                ),
                intitule: button.getAttribute("data-intitule"),
                type: button.getAttribute("data-type"),
            };

            // // Affichage propre des données dans une alerte
            // let message = "Données récupérées :\n";
            // for (const [key, value] of Object.entries(params)) {
            //     message += `${key} : ${value}\n`;
            // }

            // alert(message);

            // Pour redirection plus tard
            const queryString = new URLSearchParams(params).toString();
            window.location.href = accounting_entry_realSaisisUrl + "?" + queryString;
        });
    });
});



// filtre

document.getElementById("apply-filters").addEventListener("click", function () {
    const annee = document.getElementById("filter-annee").value.toLowerCase();
    const mois = document.getElementById("filter-mois").value;
    const code = document.getElementById("filter-code").value.toLowerCase();
    const type = document.getElementById("filter-type").value.toLowerCase();

    const rows = document.querySelectorAll("table tbody tr");

    rows.forEach((row) => {
        const rowAnnee = row.cells[0]?.textContent.trim().toLowerCase();
        const rowMois = row.cells[1]?.getAttribute("data-month"); // à ajouter ci-dessous
        const rowCode = row.cells[2]?.textContent.trim().toLowerCase();
        const rowType = row.cells[4]?.textContent.trim().toLowerCase();

        const show =
            (!annee || rowAnnee.includes(annee)) &&
            (!mois || rowMois === mois) &&
            (!code || rowCode.includes(code)) &&
            (!type || rowType.includes(type));

        row.style.display = show ? "" : "none";
    });
});

document.getElementById("reset-filters").addEventListener("click", function () {
    document.getElementById("filter-annee").value = "";
    document.getElementById("filter-mois").value = "";
    document.getElementById("filter-code").value = "";
    document.getElementById("filter-type").value = "";

    const rows = document.querySelectorAll("table tbody tr");
    rows.forEach((row) => (row.style.display = ""));
});

