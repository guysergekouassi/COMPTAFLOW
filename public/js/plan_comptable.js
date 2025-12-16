document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("planComptableForm");
    const modal = document.getElementById("modalCenterCreate");

    // Validation JS
    form.addEventListener("submit", function (e) {
        let isValid = true;
        const fields = [
            "numero_de_compte",
            "intitule",

            // "type_de_compte",
            // "poste",
            // "extrait_du_compte",
            // "traitement_analytique",
            // "classe",
        ];

        fields.forEach((id) => {
            const field = document.getElementById(id);
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add("is-invalid");
            } else {
                field.classList.remove("is-invalid");
            }
        });

        // const classeVal = parseInt(document.getElementById("classe").value);
        // if (isNaN(classeVal) || classeVal < 1 || classeVal > 8) {
        //     isValid = false;
        //     document.getElementById("classe").classList.add("is-invalid");
        // }

        if (!isValid) {
            e.preventDefault();
        } else {
            // Optionnel : tu peux envoyer via AJAX ici
            // alert("Formulaire valide, prêt à soumettre !");
            // e.preventDefault(); // à retirer si tu envoies réellement
        }
    });

    // Réinitialisation à la fermeture
    modal.addEventListener("hidden.bs.modal", function () {
        form.reset();
        form.querySelectorAll(".is-invalid").forEach((el) =>
            el.classList.remove("is-invalid"),
        );
    });
});

// Pour la maj

document.addEventListener("DOMContentLoaded", function () {
    const editButtons = document.querySelectorAll(
        '[data-bs-target="#modalCenterUpdate"]',
    );

    editButtons.forEach((button) => {
        button.addEventListener("click", function () {
            document.getElementById("update_planId").value = this.dataset.id;
            document.getElementById("update_numero_de_compte").value =
                this.dataset.numero_de_compte;
            document.getElementById("update_intitule").value =
                this.dataset.intitule;

            // document.getElementById("update_type_de_compte").value =
            //     this.dataset.type_de_compte;
            // document.getElementById("update_poste").value = this.dataset.poste;
            // document.getElementById("update_extrait_du_compte").value =
            //     this.dataset.extrait_du_compte;
            // document.getElementById("update_traitement_analytique").value =
            //     this.dataset.traitement_analytique;
            // document.getElementById("update_classe").value =
            //     this.dataset.classe;

            // Change dynamiquement l'action du formulaire
            const form = document.getElementById("updatePlanForm");
            const id = this.dataset.id; 
            const updateUrl = planComptableUpdateBaseUrl.replace('__ID__', id);
            form.action = updateUrl;

        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("updatePlanForm");
    const modal = document.getElementById("modalCenterUpdate");

    form.addEventListener("submit", function (e) {
        let isValid = true;
        const fields = [
            "update_numero_de_compte",
            "update_intitule",

            // "update_type_de_compte",
            // "update_poste",
            // "update_extrait_du_compte",
            // "update_traitement_analytique",
            // "update_classe",
        ];

        fields.forEach((id) => {
            const field = document.getElementById(id);
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add("is-invalid");
            } else {
                field.classList.remove("is-invalid");
            }
        });

        // const classeInput = document.getElementById("update_classe");
        // const classeVal = parseInt(classeInput.value);
        // if (isNaN(classeVal) || classeVal < 1 || classeVal > 8) {
        //     isValid = false;
        //     classeInput.classList.add("is-invalid");
        // }

        if (!isValid) {
            e.preventDefault();
        }
    });

    modal.addEventListener("hidden.bs.modal", function () {
        form.reset();
        form.querySelectorAll(".is-invalid").forEach((el) =>
            el.classList.remove("is-invalid"),
        );
    });
});

// suppression

document.addEventListener("DOMContentLoaded", function () {
    const deleteModal = document.getElementById("deleteConfirmationModal");
    const planToDeleteName = document.getElementById("planToDeleteName");
    const deleteForm = document.getElementById("deletePlanForm");

    deleteModal.addEventListener("show.bs.modal", function (event) {
        const button = event.relatedTarget; // bouton qui a déclenché le modal
        const planId = button.getAttribute("data-id");
        const planIntitule = button.getAttribute("data-intitule");

        // Afficher le nom dans le modal
        planToDeleteName.textContent = planIntitule;

        // Modifier l'URL d'action du formulaire

        // const id = this.dataset.id; 
        const deleteUrl = plan_comptableDeleteUrl.replace('__ID__', planId);
        deleteForm.action = deleteUrl;
    });
});

// filtre
document.addEventListener("DOMContentLoaded", function () {
    const applyBtn = document.getElementById("apply-filters");
    const resetBtn = document.getElementById("reset-filters");

    const numeroInput = document.getElementById("filter-numero");
    const intituleInput = document.getElementById("filter-intitule");
    const addingStrategyInput = document.getElementById(
        "filter-adding_strategy",
    );

    const rows = document.querySelectorAll("#planComptableTable tbody tr");

    applyBtn.addEventListener("click", function () {
        const numeroVal = numeroInput.value.toLowerCase();
        const intituleVal = intituleInput.value.toLowerCase();
        const addingStrategyVal = addingStrategyInput.value.toLowerCase();

        rows.forEach((row) => {
            const [numeroCell, intituleCell, addingStrategyCell] =
                row.querySelectorAll("td");

            const matchNumero = numeroCell.textContent
                .toLowerCase()
                .includes(numeroVal);
            const matchIntitule = intituleCell.textContent
                .toLowerCase()
                .includes(intituleVal);
            const matchStrategy =
                addingStrategyVal === "" ||
                addingStrategyCell.textContent
                    .toLowerCase()
                    .includes(addingStrategyVal);

            if (matchNumero && matchIntitule && matchStrategy) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });

    resetBtn.addEventListener("click", function () {
        numeroInput.value = "";
        intituleInput.value = "";
        addingStrategyInput.value = "";

        rows.forEach((row) => (row.style.display = ""));
    });
});

// document
//     .getElementById("type_de_compte")
//     .addEventListener("change", function () {
//         const posteSelect = document.getElementById("poste");
//         const selectedType = this.value;

//         // Vide les options existantes
//         posteSelect.innerHTML = '<option value="">-- Choisir --</option>';

//         if (selectedType === "Bilan") {
//             const options = [
//                 "Immobilisations",
//                 "Stock",
//                 "Client",
//                 "Autres creances",
//                 "Banque",
//                 "Caisse",
//                 "Capitaux propres",
//                 "Dettes financieres",
//                 "Fourniseurs d’exploitation",
//                 "Fourniseurs d’immobilisations",
//                 "Decouvert",
//             ];

//             options.forEach((opt) => {
//                 const option = document.createElement("option");
//                 option.value = opt;
//                 option.text = opt;
//                 posteSelect.appendChild(option);
//             });
//         } else if (selectedType === "Compte resultat") {
//             const options = [
//                 "Achats",
//                 "Ventes",
//                 "Salaires",
//                 "Impôts",
//                 "Amortissements",
//                 "Charges financières",
//                 "Produits financiers",
//             ];

//             options.forEach((opt) => {
//                 const option = document.createElement("option");
//                 option.value = opt;
//                 option.text = opt;
//                 posteSelect.appendChild(option);
//             });
//         }
//     });

// envoi données

document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".donnees-plan-comptable");

    buttons.forEach((button) => {
        button.addEventListener("click", () => {

             // console.log("URL générée :", plan_comptable_ecrituresSaisisUrl);
            const params = {
                id_plan_comptable: button.getAttribute("data-id"),
                intitule: button.getAttribute("data-intitule"),
                numero_de_compte: button.getAttribute("data-numero_de_compte"),
            };

            // // Affichage propre des données dans une alerte
            // let message = "Données récupérées :\n";
            // for (const [key, value] of Object.entries(params)) {
            //     message += `${key} : ${value}\n`;
            // }

            // alert(message);

            // Pour redirection plus tard
            const queryString = new URLSearchParams(params).toString();
            window.location.href = plan_comptable_ecrituresSaisisUrl + "?" + queryString;
        });
    });
});

// compte par defauts

document.addEventListener("DOMContentLoaded", function () {
    const Plan_defaut = document.getElementById("Plan_defaut");
    const Plandefaut = document.getElementById("Plandefaut");

    Plan_defaut.addEventListener("show.bs.modal", function () {
        // L'action du formulaire est déjà définie dans le HTML, mais on peut la redéfinir si besoin :
        Plan_defaut.action = planComptableDefautUrl; // route Laravel POST
    });
});

// verification du numero<script>
$(document).ready(function () {
    let lastPaddedValue = "";

    $("#numero_de_compte").on("input", function () {
        // alert('test')
        let rawInput = $(this).val().replace(/\D/g, ""); // chiffres uniquement
        let padded = rawInput.padEnd(8, "0"); // zéros à la fin

        if (rawInput.length === 0) {
            $("#numero_compte_feedback")
                .text("")
                .removeClass("text-danger text-success");
            return;
        }

        if (padded === lastPaddedValue) return;
        lastPaddedValue = padded;

        // alert(padded)

        // Important : à placer une seule fois dans ton script JS global
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        });

        $.ajax({
            url: verifierNumeroUrl,
            method: "POST",
            data: {
                numero_de_compte: padded,
            },
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

                // $("#numero_de_compte").val(response.numero_formatte);
            },
            error: function (xhr, status, error) {
                console.error("Erreur AJAX :", status, error);
                console.log(xhr.responseText); // pour voir l'erreur côté Laravel
            },
        });
    });
});
