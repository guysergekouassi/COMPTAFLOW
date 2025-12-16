function afficherErreur(id, message) {
    const element = document.getElementById(id);
    element.textContent = message;
    element.previousElementSibling.classList.add("is-invalid");
}

function effacerErreurs() {
    document
        .querySelectorAll(".invalid-feedback")
        .forEach((div) => (div.textContent = ""));
    document
        .querySelectorAll(".form-control, .form-select")
        .forEach((input) => input.classList.remove("is-invalid"));
}

function validerCreationUtilisateur(event) {
    event.preventDefault(); // empêcher l'envoi par défaut

    effacerErreurs();

    const nom = document.getElementById("name").value.trim();
    const prenom = document.getElementById("last_name").value.trim();
    const email = document.getElementById("email_adresse").value.trim();
    const password = document.getElementById("password").value;
    const confirmPassword = document.getElementById("confirmPassword").value;
    const role = document.getElementById("role").value;

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const passwordRegex = /^(?=.*[A-Z])(?=.*\d).{8,}$/;

    let isValid = true;

    if (!nom) {
        afficherErreur("errorFirstName", "Le nom est requis.");
        isValid = false;
    }

    if (!prenom) {
        afficherErreur("errorLastName", "Le prénom est requis.");
        isValid = false;
    }

    if (!email) {
        afficherErreur("errorEmail", "L'email est requis.");
        isValid = false;
    } else if (!emailRegex.test(email)) {
        afficherErreur("errorEmail", "Adresse email invalide.");
        isValid = false;
    }

    if (!password) {
        afficherErreur("errorPassword", "Le mot de passe est requis.");
        isValid = false;
    } else if (!passwordRegex.test(password)) {
        afficherErreur(
            "errorPassword",
            "Minimum 8 caractères, une majuscule et un chiffre."
        );
        isValid = false;
    }

    if (!confirmPassword) {
        afficherErreur("errorConfirmPassword", "Confirmez le mot de passe.");
        isValid = false;
    } else if (password !== confirmPassword) {
        afficherErreur(
            "errorConfirmPassword",
            "Les mots de passe ne correspondent pas."
        );
        isValid = false;
    }

    if (!role) {
        afficherErreur("errorRole", "Veuillez sélectionner un rôle.");
        isValid = false;
    }

    if (isValid) {
        // Tout est OK, envoyer le formulaire manuellement
        document.getElementById("createUserForm").submit();
    }

    return false; // toujours empêcher la soumission automatique
}

document.addEventListener("DOMContentLoaded", function () {
    const createUserModal = document.getElementById("modalCenterCreate");

    if (createUserModal) {
        createUserModal.addEventListener("hidden.bs.modal", function () {
            // Réinitialise les champs de validation
            document
                .querySelectorAll(
                    "#createUserForm .form-control, #createUserForm .form-select"
                )
                .forEach((input) => {
                    input.classList.remove("is-invalid");
                });

            // Efface les messages d'erreur
            document
                .querySelectorAll("#createUserForm .invalid-feedback")
                .forEach((div) => {
                    div.textContent = "";
                });

            // Vide tous les champs (optionnel)
            // document.getElementById("createUserForm").reset();
        });
    }
});

// disparition alerte success
document.addEventListener("DOMContentLoaded", function () {
    const alertBox = document.getElementById("successAlert");
    if (alertBox) {
        setTimeout(() => {
            // Option 1: Pour une disparition douce avec Bootstrap fade
            alertBox.classList.remove("show"); // Pour fade out Bootstrap
            alertBox.classList.add("fade"); // Assure le fade out
            // Optionnel : suppression complète après animation
            setTimeout(() => alertBox.remove(), 1000);
        }, 5000);
    }
});

// voir les elements sans modification
document.addEventListener("DOMContentLoaded", function () {
    const seeButtons = document.querySelectorAll(".btn-see-user");

    seeButtons.forEach((button) => {
        button.addEventListener("click", function () {
            // Récupération des données depuis les attributs data-*
            const name = button.getAttribute("data-user-name");
            const lastName = button.getAttribute("data-user-lastname");
            const email = button.getAttribute("data-user-email");
            const role = button.getAttribute("data-user-role");

            // Injection dans le modal
            document.getElementById("seeFirstName").value = name;
            document.getElementById("seeLastName").value = lastName;
            document.getElementById("seeEmail").value = email;
            document.getElementById("seeRole").value =
                role.charAt(0).toUpperCase() + role.slice(1);
        });
    });
});

// preremplir enfin de permettre la MAJ
document.addEventListener("DOMContentLoaded", function () {
    document.querySelectorAll(".btn-edit-user").forEach((button) => {
        button.addEventListener("click", () => {
            const userId = button.dataset.userId;

            // Remplissage des champs
            document.getElementById("updateUserId").value = userId;
            document.getElementById("updateFirstName").value =
                button.dataset.userName;
            document.getElementById("updateLastName").value =
                button.dataset.userLastname;
            document.getElementById("updateEmail").value =
                button.dataset.userEmail;
            document.getElementById("updateRole").value =
                button.dataset.userRole;

            // Mise à jour de l'action du formulaire
            const form = document.getElementById("updateUserForm");

            const updateUrl = usersUpdateBaseUrl.replace('__ID__', userId);
            form.action = updateUrl;


            // form.action = `/users/${userId}`;
        });
    });
});

// verification pour la MAJ
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("updateUserForm");

    form.addEventListener("submit", function (event) {
        // On récupère les champs
        const firstName = document.getElementById("updateFirstName");
        const lastName = document.getElementById("updateLastName");
        const email = document.getElementById("updateEmail");
        const role = document.getElementById("updateRole");

        let isValid = true;

        // Reset messages d'erreurs
        document
            .querySelectorAll(".invalid-feedback")
            .forEach((div) => (div.textContent = ""));
        document
            .querySelectorAll(".form-control, .form-select")
            .forEach((input) => input.classList.remove("is-invalid"));

        // Vérification du prénom
        if (firstName.value.trim() === "") {
            showError(firstName, "Le prénom est requis");
            isValid = false;
        }

        // Vérification du nom de famille
        if (lastName.value.trim() === "") {
            showError(lastName, "Le nom de famille est requis");
            isValid = false;
        }

        // Vérification de l’e-mail
        if (email.value.trim() === "") {
            showError(email, "L’email est requis");
            isValid = false;
        } else if (!validateEmail(email.value)) {
            showError(email, "Format d’email invalide");
            isValid = false;
        }

        // Vérification du rôle
        if (role.value.trim() === "") {
            showError(role, "Veuillez sélectionner un rôle");
            isValid = false;
        }

        // Empêche la soumission si invalide
        if (!isValid) {
            event.preventDefault();
        }
    });

    // Fonction pour afficher un message d’erreur
    function showError(inputElement, message) {
        const errorDiv =
            inputElement.parentElement.querySelector(".invalid-feedback");
        inputElement.classList.add("is-invalid");
        if (errorDiv) errorDiv.textContent = message;
    }

    // Vérifie l’email avec regex simple
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }
});
document.addEventListener("DOMContentLoaded", function () {
    const updateModal = document.getElementById("modalCenterUpdate");

    updateModal.addEventListener("hidden.bs.modal", function () {
        // Réinitialise les champs de validation
        document
            .querySelectorAll(
                "#updateUserForm .form-control, #updateUserForm .form-select"
            )
            .forEach((input) => {
                input.classList.remove("is-invalid");
            });

        document
            .querySelectorAll("#updateUserForm .invalid-feedback")
            .forEach((div) => {
                div.textContent = "";
            });

        // Optionnel : vide les champs
        // document.getElementById('updateUserForm').reset();
    });
});

// modal de suppression
document.addEventListener("DOMContentLoaded", function () {
    const deleteUserModal = document.getElementById("deleteUserModal");
    const userToDeleteText = document.getElementById("userToDelete");
    const deleteUserForm = document.getElementById("deleteUserForm");

    deleteUserModal.addEventListener("show.bs.modal", function (event) {
        const button = event.relatedTarget;
        const userName = button.getAttribute("data-user-name");
        const userId = button.getAttribute("data-user-id");

        userToDeleteText.textContent = userName;

        // Adapter l’URL de suppression ici

        const deleteUrl = usersDeleteUrl.replace('__ID__', userId);
        deleteUserForm.action = deleteUrl;


        // deleteUserForm.action = `/users/${userId}`;
    });

    // Réinitialisation lors de la fermeture du modal
    deleteUserModal.addEventListener("hidden.bs.modal", function () {
        userToDeleteText.textContent = "";
        deleteUserForm.action = "#";
    });
});

// filtre
document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("filter-name");
    const tableBody = document.getElementById("userTableBody");

    input.addEventListener("input", function () {
        const filter = this.value.toLowerCase();

        // Parcours toutes les lignes du tbody
        Array.from(tableBody.getElementsByTagName("tr")).forEach((tr) => {
            const fullNameCell = tr.cells[0]; // 1ère colonne = Nom complet
            const fullNameText = fullNameCell.textContent.toLowerCase();

            if (fullNameText.includes(filter)) {
                tr.style.display = ""; // Montre la ligne
            } else {
                tr.style.display = "none"; // Cache la ligne
            }
        });
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const roleSelect = document.getElementById("role");
    const habilitationsGroup = document.getElementById("habilitationsGroup");

    roleSelect.addEventListener("change", function () {
        if (this.value === "comptable") {
            habilitationsGroup.classList.remove("d-none");
        } else {
            habilitationsGroup.classList.add("d-none");
            // Décoche les cases si on change de rôle
            habilitationsGroup
                .querySelectorAll('input[type="checkbox"]')
                .forEach((cb) => (cb.checked = false));
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const roleSelect = document.getElementById("updateRole");
    const habilitationsSection = document.getElementById(
        "updateHabilitationsSection"
    );

    roleSelect.addEventListener("change", function () {
        if (this.value === "comptable") {
            habilitationsSection.style.display = "block";
        } else {
            habilitationsSection.style.display = "none";
            // Optionnel : décocher toutes les habilitations si non comptable
            habilitationsSection
                .querySelectorAll('input[type="checkbox"]')
                .forEach((cb) => (cb.checked = true));
        }
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const seeModal = document.getElementById("modalCenterSee");

    seeModal.addEventListener("show.bs.modal", function (event) {
        const button = event.relatedTarget;

        // Données utilisateur
        const userFirstName = button.getAttribute("data-user-name") || "";
        const userLastName = button.getAttribute("data-user-lastname") || "";
        const userEmail = button.getAttribute("data-user-email") || "";
        const userRole = button.getAttribute("data-user-role") || "";
        const userHabilitationsRaw = button.getAttribute(
            "data-user-habilitations"
        );

        document.getElementById("seeFirstName").value = userFirstName;
        document.getElementById("seeLastName").value = userLastName;
        document.getElementById("seeEmail").value = userEmail;
        document.getElementById("seeRole").value = userRole;

        // Traitement des habilitations
        let habilitations = {};
        try {
            habilitations = JSON.parse(userHabilitationsRaw);
        } catch (error) {
            console.error("Erreur JSON habilitations :", error);
        }

        const section = document.getElementById("seeHabilitationsSection");

        // Tout décocher d'abord
        section
            .querySelectorAll('input[type="checkbox"]')
            .forEach((cb) => (cb.checked = false));

        let atLeastOneChecked = false;

        // Cocher uniquement les champs à true
        for (const key in habilitations) {
            const checkbox = document.getElementById("see_" + key);
            if (habilitations[key] && checkbox) {
                checkbox.checked = true;
                atLeastOneChecked = true;
            }
        }

        // Afficher ou masquer la section
        section.style.display = atLeastOneChecked ? "block" : "none";
    });
});

// update modal
// document.addEventListener("DOMContentLoaded", function () {
//     const updateModal = document.getElementById("modalCenterUpdate");

//     updateModal.addEventListener("show.bs.modal", function (event) {
//         const button = event.relatedTarget;

//         // Récupérer les data-*
//         const userId = button.getAttribute("data-user-id");
//         const firstName = button.getAttribute("data-user-name") || "";
//         const lastName = button.getAttribute("data-user-lastname") || "";
//         const email = button.getAttribute("data-user-email") || "";
//         const role = button.getAttribute("data-user-role") || "";
//         const habilitationsRaw = button.getAttribute("data-user-habilitations");

//         // Mettre à jour les champs
//         document.getElementById("updateUserId").value = userId;
//         document.getElementById("updateFirstName").value = firstName;
//         document.getElementById("updateLastName").value = lastName;
//         document.getElementById("updateEmail").value = email;
//         document.getElementById("updateRole").value = role;

//         // Mettre à jour dynamiquement l'URL du formulaire
//         const form = document.getElementById("updateUserForm");
//         form.action = form.action.replace("__ID__", userId);

//         // Gérer les habilitations
//         let habilitations = {};
//         try {
//             habilitations = JSON.parse(habilitationsRaw);
//         } catch (e) {
//             console.error("Erreur parsing habilitations :", e);
//         }

//         // Tout décocher
//         document
//             .querySelectorAll(
//                 '#updateHabilitationsSection input[type="checkbox"]'
//             )
//             .forEach((cb) => {
//                 cb.checked = false;
//             });

//         // Cocher ceux à true
//         for (const key in habilitations) {
//             const checkbox = document.getElementById("update_" + key);
//             if (habilitations[key] && checkbox) {
//                 checkbox.checked = true;
//             }
//         }
//     });
// });

document.addEventListener("DOMContentLoaded", function () {
    const updateModal = document.getElementById("modalCenterUpdate");
    const habilitationsSection = document.getElementById(
        "updateHabilitationsSection"
    );
    const updateRoleSelect = document.getElementById("updateRole");

    function toggleHabilitationsDisplay(role) {
        if (role === "admin") {
            habilitationsSection.style.display = "none";
        } else if (role === "comptable") {
            habilitationsSection.style.display = "block";
        } else {
            habilitationsSection.style.display = "none"; // par défaut cacher
        }
    }

    updateModal.addEventListener("show.bs.modal", function (event) {
        const button = event.relatedTarget;

        // Récupérer les data-*
        const userId = button.getAttribute("data-user-id");
        const firstName = button.getAttribute("data-user-name") || "";
        const lastName = button.getAttribute("data-user-lastname") || "";
        const email = button.getAttribute("data-user-email") || "";
        const role = button.getAttribute("data-user-role") || "";
        const habilitationsRaw = button.getAttribute("data-user-habilitations");

        // Mettre à jour les champs
        document.getElementById("updateUserId").value = userId;
        document.getElementById("updateFirstName").value = firstName;
        document.getElementById("updateLastName").value = lastName;
        document.getElementById("updateEmail").value = email;
        updateRoleSelect.value = role;

        // Mettre à jour dynamiquement l'URL du formulaire
        const form = document.getElementById("updateUserForm");
        form.action = form.action.replace("__ID__", userId);

        // Gérer l'affichage des habilitations selon le rôle
        toggleHabilitationsDisplay(role);

        // Gérer les habilitations
        let habilitations = {};
        try {
            habilitations = JSON.parse(habilitationsRaw);
        } catch (e) {
            console.error("Erreur parsing habilitations :", e);
        }

        // Tout décocher
        document
            .querySelectorAll(
                '#updateHabilitationsSection input[type="checkbox"]'
            )
            .forEach((cb) => {
                cb.checked = false;
            });

        // Cocher ceux à true
        for (const key in habilitations) {
            const checkbox = document.getElementById("update_" + key);
            if (habilitations[key] && checkbox) {
                checkbox.checked = true;
            }
        }
    });

    // Sur changement de rôle en live dans le modal
    updateRoleSelect.addEventListener("change", function () {
        toggleHabilitationsDisplay(this.value);
    });
});
