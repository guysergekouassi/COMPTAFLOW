<!-- Plugins & Vendors JS -->

<!-- Bootstrap-Select JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/i18n/defaults-fr_FR.min.js" defer></script>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/fr.js" defer></script>

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

        // --- 1. Initialisation Bootstrap-Select (Legacy) ---
        if (typeof $.fn.selectpicker === 'function') {
            $('.selectpicker').selectpicker();

            // Événement de rafraîchissement pour le modal
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
             console.warn("Info: Le plugin Bootstrap-Select n'est pas utilisé ou chargé.");
        }

        // --- 2. Initialisation Select2 Globale ---
        if (typeof $.fn.select2 === 'function') {
            // Fonction d'init générique
            const initSelect2 = function() {
                $('select.form-select, select.select2-enable').not('.no-search, .selectpicker, .dataTables_length select').each(function() {
                    const $this = $(this);
                    
                    // Options de base
                    let options = {
                        theme: 'bootstrap4', // Utilisation du thème bootstrap4 corrigé
                        width: '100%',
                        language: 'fr',
                        placeholder: $this.attr('placeholder') || 'Sélectionner...',
                        allowClear: $this.attr('multiple') ? false : true
                    };

                    // Correction pour les Modales Bootstrap
                    const modalParent = $this.closest('.modal');
                    if (modalParent.length) {
                        options.dropdownParent = modalParent;
                    }

                    $this.select2(options);

                    // Force le placeholder de recherche à l'ouverture
                    $this.on('select2:open', function (e) {
                        // Utiliser un timeout pour s'assurer que le DOM est rendu
                        setTimeout(function() {
                            const searchField = document.querySelector('.select2-search__field');
                            if (searchField) {
                                searchField.setAttribute('placeholder', 'Rechercher...');
                                searchField.focus();
                            }
                        }, 50);
                    });
                });
            };

            // Init au chargement
            initSelect2();

            // Ré-init lors de l'ouverture d'un modal (pour les contenus dynamiques ou cachés)
            $(document).on('shown.bs.modal', '.modal', function() {
                initSelect2();
            });
            
            // Hack CSS pour l'alignement de Select2 avec les inputs Bootstrap 5
            const style = document.createElement('style');
            style.innerHTML = `
                .select2-container--bootstrap-5 .select2-selection {
                    border: 1px solid #ced4da;
                    border-radius: 0.75rem; /* rounded-xl matches tailwind */
                    min-height: 48px; /* py-3 equivalent */
                    padding: 0.5rem 1rem;
                }
                .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
                    line-height: 28px;
                    padding-left: 0;
                    color: #334155; /* slate-700 */
                    font-weight: 600;
                }
                .select2-container--bootstrap-5 .select2-selection--single .select2-selection__arrow {
                    top: 50%;
                    transform: translateY(-50%);
                    right: 15px;
                }
                .select2-search__field {
                    border-radius: 0.5rem;
                }
                /* Ajustement si theme bootstrap4 est utilisé à la place */
                .select2-container--bootstrap4 .select2-selection {
                    border-radius: 0.75rem; 
                    min-height: 48px;
                }
            `;
            document.head.appendChild(style);

        } else {
             console.error("Erreur: Le plugin Select2 n'est pas chargé.");
        }
    });
</script>
