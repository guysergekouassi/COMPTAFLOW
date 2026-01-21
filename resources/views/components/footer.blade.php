<!-- Plugins & Vendors JS -->

<!-- Bootstrap-Select JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/i18n/defaults-fr_FR.min.js" defer></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>

<!-- Perfect Scrollbar -->
<script src="https://cdn.jsdelivr.net/npm/perfect-scrollbar@1.5.5/dist/perfect-scrollbar.min.js" crossorigin="anonymous" defer></script>

<!-- Menu.js -->
<script src="{{ asset('assets/vendor/js/menu.js') }}" defer></script>

<!-- Vendors JS -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js"></script>

<!-- Main JS -->
<script src="{{ asset('assets/js/main.js') }}" defer></script>

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

        if (typeof $.fn.selectpicker === 'function') {
            $('.selectpicker').selectpicker();

            // 2. Événement de rafraîchissement pour le modal
            const modal = document.getElementById('saisieRedirectModal');
            if (modal) {
                modal.addEventListener('shown.bs.modal', function () {
                    const exerciceSelect = $('#exercice_id');
                    const exerciceActifId = exerciceSelect.data('exercice-actif');
                    if (exerciceActifId) {
                        exerciceSelect.val(exerciceActifId);
                    }
                    $('.selectpicker').selectpicker('refresh');
                });
            }
        } else {
             // Ceci devrait s'afficher dans la console si le fichier JS est manquant
             console.error("Erreur: Le plugin Bootstrap-Select n'est pas trouvé. Vérifiez les dépendances.");
        }
    });
</script>
