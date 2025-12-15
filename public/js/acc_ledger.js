document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("modalCenterCreate");
    const btnSave = document.getElementById("btnSaveModal");
    const btnClose = document.getElementById("btnCloseModal");
    const inputs = modal.querySelectorAll("input[required], select[required]");

    // ✅ Vérification avant enregistrement
    btnSave.addEventListener("click", function () {
        let isValid = true;

        inputs.forEach((input) => {
            if (!input.value.trim()) {
                input.classList.add("is-invalid");
                isValid = false;
            } else {
                input.classList.remove("is-invalid");
            }
        });

        // Si tous les champs sont valides, ici tu peux soumettre
        if (isValid) {
            console.log(
                "Formulaire valide. Soumission ou traitement à faire ici.",
            );
            // Ex : $('#modalCenterCreate').modal('hide'); après envoi
        }
    });

    // Réinitialisation quand on ferme via bouton ou croix
    const resetFormFields = () => {
        inputs.forEach((input) => {
            input.value = "";
            input.classList.remove("is-invalid");
            $('#plan_comptable_id_1').selectpicker('val', '');
            $('#plan_comptable_id_2').selectpicker('val', '');
            document.getElementById("compte2-error").style.display = "none";
        });
    };

    btnClose.addEventListener("click", resetFormFields);
    modal.addEventListener("hidden.bs.modal", resetFormFields);
});

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("grandLivreForm");

    form.addEventListener("submit", function (event) {
        const compte1Select = document.getElementById("plan_comptable_id_1");
        const compte2Select = document.getElementById("plan_comptable_id_2");
        const errorDiv = document.getElementById("compte2-error");

        const compte1Text =
            compte1Select.options[compte1Select.selectedIndex]?.text || "";
        const compte2Text =
            compte2Select.options[compte2Select.selectedIndex]?.text || "";

        const compte1Num = compte1Text.split(" - ")[0].trim();
        const compte2Num = compte2Text.split(" - ")[0].trim();

        if (compte1Num && compte2Num && compte1Num > compte2Num) {
            event.preventDefault();

            compte2Select.classList.add("is-invalid");
            errorDiv.innerText =
                "Le compte général 2 doit être supérieur ou égal au compte général 1.";
            errorDiv.style.display = "block";
        } else {
            compte2Select.classList.remove("is-invalid");
            errorDiv.style.display = "none";
        }
    });
});

// document.addEventListener("DOMContentLoaded", function () {
//     const modal = document.getElementById("modalCenterCreate");
//     const form = document.getElementById("grandLivreForm");

//     modal.addEventListener("hidden.bs.modal", function () {
//         // Réinitialise les champs du formulaire
//         form.reset();

//         // // Réinitialise les selectpicker
//         // if (window.jQuery) {
//         //     $(".selectpicker").val("").selectpicker("refresh");
//         // }

//         // Supprime les classes d'erreur Bootstrap
//         form.querySelectorAll(".is-invalid").forEach((el) => {
//             el.classList.remove("is-invalid");
//         });

//         // Réinitialise les messages d’erreur personnalisés
//         const customErrors = form.querySelectorAll(".invalid-feedback");
//         customErrors.forEach((error) => (error.style.display = "none"));
//     });
// });

//   suppression

// Récupère l'URL de suppression avec un ID fictif à remplacer dynamiquement

const deleteModal = document.getElementById("deleteConfirmationModal");
deleteModal.addEventListener("show.bs.modal", function (event) {
    const button = event.relatedTarget;
    const id = button.getAttribute("data-id");
    const filename = button.getAttribute("data-filename");

    const deleteForm = document.getElementById("deleteForm");

    // Remplace __ID__ dans l’URL Laravel par l’ID réel
    const url = accounting_ledgerDeleteUrl.replace('__ID__', id);
    deleteForm.action = url;

    // Affiche le nom de fichier dans le texte du modal
    const fileNameText = document.getElementById("fileNameToDelete");
    fileNameText.textContent = filename;
});




// previsualiser le pdf 

// document.addEventListener('DOMContentLoaded', function () {
//     const previewButtons = document.querySelectorAll('.btn-preview-pdf');
//     const pdfViewer = document.getElementById('pdfViewer');

//     previewButtons.forEach(button => {
//         button.addEventListener('click', function () {
//             const pdfUrl = this.getAttribute('data-pdf-url');
//             if (pdfUrl) {
//                 pdfViewer.src = pdfUrl;
//             }
//         });
//     });

//     // Vider l'iframe à la fermeture pour éviter conflits PDF
//     const pdfModal = document.getElementById('pdfPreviewModal');
//     pdfModal.addEventListener('hidden.bs.modal', function () {
//         pdfViewer.src = '';
//     });
// });

document.addEventListener('DOMContentLoaded', function () {
    const previewButtons = document.querySelectorAll('.btn-preview-file');
    const fileViewer = document.getElementById('fileViewer');

    previewButtons.forEach(button => {
        button.addEventListener('click', function () {
            const fileUrl = this.getAttribute('data-file-url');
            const fileType = this.getAttribute('data-file-type').toLowerCase();

            if (!fileUrl) return;

            if (fileType === 'pdf') {
                fileViewer.src = fileUrl;
            } else if (fileType === 'xlsx' || fileType === 'csv') {
                // rediriger vers Google Docs Viewer
                fileViewer.src = `https://docs.google.com/gview?url=${encodeURIComponent(fileUrl)}&embedded=true`;
            } else {
                fileViewer.src = '';
                alert("Aperçu non disponible pour ce type de fichier");
            }
        });
    });

    const modal = document.getElementById('filePreviewModal');
    modal.addEventListener('hidden.bs.modal', function () {
        fileViewer.src = '';
    });
});


// previsualiser le pdf avant sauvegarde
document.getElementById("btnPreview").addEventListener("click", function (event) {
    event.preventDefault(); // on empêche l’envoi normal du formulaire
    let form = document.getElementById("grandLivreForm");
    let formData = new FormData(form);

    const compte1Select = document.getElementById("plan_comptable_id_1");
    const compte2Select = document.getElementById("plan_comptable_id_2");
    const errorDiv = document.getElementById("compte2-error");

    const compte1Text =
        compte1Select.options[compte1Select.selectedIndex]?.text || "";
    const compte2Text =
        compte2Select.options[compte2Select.selectedIndex]?.text || "";

    const compte1Num = compte1Text.split(" - ")[0].trim();
    const compte2Num = compte2Text.split(" - ")[0].trim();

    if (compte1Num && compte2Num && compte1Num > compte2Num) {
        event.preventDefault();

        compte2Select.classList.add("is-invalid");
        errorDiv.innerText =
            "Le compte général 2 doit être supérieur ou égal au compte général 1.";
        errorDiv.style.display = "block";
        return;
    } else {
        compte2Select.classList.remove("is-invalid");
        errorDiv.style.display = "none";
    }



    fetch(accounting_ledgerpreviewGrandLivreUrl, {   // Mets bien la bonne URL
        method: "POST",
        body: formData,
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
        }
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Charger directement l’URL dans l’iframe
                document.getElementById("pdfPreviewFrame").src = data.url;

                // Afficher le modal
                let modal = new bootstrap.Modal(document.getElementById("modalPreviewPDF"));
                modal.show();
            } else {
                alert(data.error || "Erreur lors de la prévisualisation.");
            }
        })
        .catch(err => {
            console.error("Erreur :", err);
            alert("Impossible de générer la prévisualisation.");
        });
});


