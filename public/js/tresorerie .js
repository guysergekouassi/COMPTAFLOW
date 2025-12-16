// public/js/tresorerie.js

document.addEventListener("DOMContentLoaded", function () {
    const updateModal = document.getElementById("modalCenterUpdate");
    const deleteModal = document.getElementById("deleteConfirmationModal");

    // ✅ Remplir les champs lors du clic sur "Modifier"
    updateModal?.addEventListener("show.bs.modal", function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute("data-id");
        const nom = button.getAttribute("data-nom");
        const type = button.getAttribute("data-type");
        const solde = button.getAttribute("data-solde");

        updateModal.querySelector("#update_tresorerieId").value = id;
        updateModal.querySelector("#update_nom").value = nom;
        updateModal.querySelector("#update_type_tresorerie").value = type;
        updateModal.querySelector("#update_solde").value = solde;

        // Mise à jour de l’action du formulaire
        const form = document.getElementById("updateTresorerieForm");
        form.action = `/gestion_tresorerie/${id}`;
    });

    // 🗑️ Confirmation de suppression
    deleteModal?.addEventListener("show.bs.modal", function (event) {
        const button = event.relatedTarget;
        const id = button.getAttribute("data-id");
        const nom = button.getAttribute("data-nom");

        document.getElementById("tresorerieToDeleteName").textContent = nom;

        const form = document.getElementById("deleteTresorerieForm");
        form.action = `/gestion_tresorerie/${id}`;
    });

    // 🎨 Gestion de l'effet visuel sur les filtres
    const filterCards = document.querySelectorAll(".filter-card");
    filterCards.forEach(card => {
        card.addEventListener("click", () => {
            filterCards.forEach(c => c.classList.remove("filter-active"));
            card.classList.add("filter-active");
        });
    });

    // 🔍 Filtrage dynamique de la table
    const filterAll = document.getElementById("filter-all");
    const filterBanque = document.getElementById("filter-banque");
    const filterCaisse = document.getElementById("filter-caisse");
    const tableRows = document.querySelectorAll("#tresorerieTable tbody tr");

    function filterTresoreries(type) {
        tableRows.forEach(row => {
            const cellType = row.cells[1]?.textContent.trim().toLowerCase();

            if (type === "all" || cellType === type) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    }

    // 🧩 Assignation des événements sur les cartes
    filterAll?.addEventListener("click", () => filterTresoreries("all"));
    filterBanque?.addEventListener("click", () => filterTresoreries("banque"));
    filterCaisse?.addEventListener("click", () => filterTresoreries("caisse"));

    // Si tu veux ajouter Mobile Money plus tard :
    // document.getElementById("filter-mobile")?.addEventListener("click", () => filterTresoreries("mobile_money"));
});
