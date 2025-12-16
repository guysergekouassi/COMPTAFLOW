
// Verif create type de flux
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("formCreateTresorerie");

    form.addEventListener("submit", function (event) {
        let valid = true;

        // Récupérer les champs
        const categorie = document.getElementById("categorie");
        const nature = document.getElementById("nature");

        // Réinitialiser les erreurs
        document.querySelectorAll(".text-danger").forEach(el => el.textContent = "");

        // Vérif Categorie
        if (categorie.value.trim() === "") {
            document.querySelector("#categorie").nextElementSibling.textContent = "La catégorie est obligatoire.";
            valid = false;
        } else if (categorie.value.trim().length < 3) {
            document.querySelector("#categorie").nextElementSibling.textContent = "La catégorie doit contenir au moins 3 caractères.";
            valid = false;
        }

        // Vérif Nature
        if (nature.value.trim() === "") {
            document.querySelector("#nature").nextElementSibling.textContent = "La nature est obligatoire.";
            valid = false;
        } else if (nature.value.trim().length < 3) {
            document.querySelector("#nature").nextElementSibling.textContent = "La nature doit contenir au moins 3 caractères.";
            valid = false;
        }

        // Bloquer l'envoi si erreurs
        if (!valid) {
            event.preventDefault();
        }
    });
});

// update
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bs-target="#modalCenterUpdate"]').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const categorie = this.getAttribute('data-categorie');
            const nature = this.getAttribute('data-nature');
            const plan1 = this.getAttribute('data-plan-comptable1'); // <- minuscules
            const plan2 = this.getAttribute('data-plan-comptable2');

            // Remplir le formulaire
            document.getElementById('update_id').value = id;
            document.getElementById('update_categorie').value = categorie;
            document.getElementById('update_nature').value = nature;

            // Mettre à jour les selects et rafraîchir Bootstrap-select
            $('#update_plan_comptable_id_1').val(plan1).selectpicker('refresh');
            $('#update_plan_comptable_id_2').val(plan2).selectpicker('refresh');
        });
    });
});



// delete
document.addEventListener('DOMContentLoaded', function () {
    // Préremplir le modal de suppression
    document.querySelectorAll('[data-bs-target="#modalDeleteFlux"]').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const label = this.getAttribute('data-label');

            document.getElementById('delete_id').value = id;
            document.getElementById('delete_label').textContent = label;
        });
    });
});


