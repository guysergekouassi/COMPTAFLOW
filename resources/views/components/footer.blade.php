
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

<!-- Bootstrap 5 Bundle (avec Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
  integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>

<!-- Bootstrap-Select JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/i18n/defaults-fr_FR.min.js"></script>

<!-- Perfect Scrollbar -->
<script src="https://cdn.jsdelivr.net/npm/perfect-scrollbar@1.5.5/dist/perfect-scrollbar.min.js" crossorigin="anonymous"></script>

<!-- Menu.js -->
<script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

<!-- Vendors JS -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>

<!-- Main JS -->
<script src="{{ asset('assets/js/main.js') }}"></script>

<!-- Dashboard Analytics -->
<script src="{{ asset('assets/js/dashboards-analytics.js') }}"></script>

<!-- GitHub Button -->
<script async defer src="https://buttons.github.io/buttons.js"></script>

<!-- Initialisation bootstrap-select -->
<script>
    // Utiliser document.addEventListener pour s'assurer que le DOM est prêt,
    // et jQuery pour simplifier l'interaction avec Bootstrap-Select.
    // Vous aurez peut-être besoin d'inclure jQuery si ce n'est pas déjà fait.
    document.addEventListener('DOMContentLoaded', function() {

        // Vérifie si la fonction selectpicker existe avant de l'appeler
        if (typeof $('.selectpicker').selectpicker === 'function') {

            // 1. Initialisation générale du plugin
            $('.selectpicker').selectpicker();

            // 2. Événement de rafraîchissement pour le modal
            const modal = document.getElementById('saisieRedirectModal');
            if (modal) {
                // Rafraîchir le selectpicker après que le modal soit complètement visible
                modal.addEventListener('shown.bs.modal', function () {
                    $('.selectpicker').selectpicker('refresh');

                    // Pré-sélectionner l'exercice actif si disponible
                    const exerciceSelect = $('#exercice_id');
                    const exerciceActifId = exerciceSelect.data('exercice-actif');
                    if (exerciceActifId) {
                        exerciceSelect.val(exerciceActifId);
                        exerciceSelect.selectpicker('refresh');
                    }
                });
            }
        } else {
             // Ceci devrait s'afficher dans la console si le fichier JS est manquant
             console.error("Erreur: Le plugin Bootstrap-Select n'est pas trouvé. Vérifiez les dépendances.");
        }
    });
</script>
