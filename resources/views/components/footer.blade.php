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

<!-- ╔══════════════════════════════════════════════════╗
     ║  FlowToast — Système de notifications globales  ║
     ╚══════════════════════════════════════════════════╝ -->
<style>
    #flow-toast-container {
        position: fixed;
        top: 1.25rem;
        right: 1.25rem;
        z-index: 99999;
        display: flex;
        flex-direction: column;
        gap: .65rem;
        pointer-events: none;
        max-width: 380px;
        width: calc(100vw - 2.5rem);
    }
    .flow-toast {
        display: flex;
        align-items: flex-start;
        gap: .85rem;
        padding: 1rem 1.15rem;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 8px 32px rgba(0,0,0,.13), 0 1.5px 6px rgba(0,0,0,.07);
        pointer-events: all;
        cursor: pointer;
        border-left: 4px solid transparent;
        animation: flowToastIn .32s cubic-bezier(.22,1,.36,1) both;
        transition: opacity .25s, transform .25s;
        overflow: hidden;
        position: relative;
    }
    .flow-toast.hiding {
        animation: flowToastOut .28s cubic-bezier(.55,0,1,.45) both;
    }
    .flow-toast--success { border-left-color: #22c55e; }
    .flow-toast--error   { border-left-color: #ef4444; }
    .flow-toast--warning { border-left-color: #f59e0b; }
    .flow-toast--info    { border-left-color: #3b82f6; }

    .flow-toast__icon {
        flex-shrink: 0;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        margin-top: 1px;
    }
    .flow-toast--success .flow-toast__icon { background: #dcfce7; color: #16a34a; }
    .flow-toast--error   .flow-toast__icon { background: #fee2e2; color: #dc2626; }
    .flow-toast--warning .flow-toast__icon { background: #fef3c7; color: #d97706; }
    .flow-toast--info    .flow-toast__icon { background: #dbeafe; color: #2563eb; }

    .flow-toast__body { flex: 1; min-width: 0; }
    .flow-toast__title {
        font-weight: 700;
        font-size: .84rem;
        color: #1e293b;
        margin-bottom: 2px;
        line-height: 1.3;
    }
    .flow-toast__msg {
        font-size: .8rem;
        color: #64748b;
        line-height: 1.45;
        word-break: break-word;
    }
    .flow-toast__close {
        flex-shrink: 0;
        background: none;
        border: none;
        color: #94a3b8;
        font-size: 16px;
        cursor: pointer;
        padding: 0;
        line-height: 1;
        margin-top: 1px;
        transition: color .15s;
    }
    .flow-toast__close:hover { color: #475569; }

    /* Progress bar */
    .flow-toast__progress {
        position: absolute;
        bottom: 0; left: 0;
        height: 3px;
        border-radius: 0 2px 2px 0;
        animation: flowProgress linear forwards;
    }
    .flow-toast--success .flow-toast__progress { background: #22c55e; }
    .flow-toast--error   .flow-toast__progress { background: #ef4444; }
    .flow-toast--warning .flow-toast__progress { background: #f59e0b; }
    .flow-toast--info    .flow-toast__progress { background: #3b82f6; }

    @keyframes flowToastIn {
        from { opacity:0; transform: translateX(60px) scale(.95); }
        to   { opacity:1; transform: translateX(0) scale(1); }
    }
    @keyframes flowToastOut {
        from { opacity:1; transform: translateX(0) scale(1); }
        to   { opacity:0; transform: translateX(60px) scale(.92); }
    }
    @keyframes flowProgress {
        from { width: 100%; }
        to   { width: 0%; }
    }
</style>

<div id="flow-toast-container"></div>

<script>
(function() {
    const ICONS = {
        success: '✓',
        error:   '✕',
        warning: '⚠',
        info:    'ℹ'
    };
    const TITLES = {
        success: 'Succès',
        error:   'Erreur',
        warning: 'Attention',
        info:    'Information'
    };

    window.FlowToast = {
        show: function(message, type, duration) {
            type     = type     || 'info';
            duration = duration || 4500;

            var container = document.getElementById('flow-toast-container');
            if (!container) return;

            var toast = document.createElement('div');
            toast.className = 'flow-toast flow-toast--' + type;
            toast.innerHTML =
                '<div class="flow-toast__icon">' + (ICONS[type] || 'ℹ') + '</div>' +
                '<div class="flow-toast__body">' +
                    '<div class="flow-toast__title">' + TITLES[type] + '</div>' +
                    '<div class="flow-toast__msg">' + message + '</div>' +
                '</div>' +
                '<button class="flow-toast__close" aria-label="Fermer">✕</button>' +
                '<div class="flow-toast__progress" style="animation-duration:' + duration + 'ms"></div>';

            container.appendChild(toast);

            function dismiss() {
                if (toast.classList.contains('hiding')) return;
                toast.classList.add('hiding');
                setTimeout(function() {
                    if (toast.parentNode) toast.parentNode.removeChild(toast);
                }, 300);
            }

            toast.querySelector('.flow-toast__close').addEventListener('click', dismiss);
            toast.addEventListener('click', dismiss);
            setTimeout(dismiss, duration);
        },
        success: function(msg, dur) { this.show(msg, 'success', dur); },
        error:   function(msg, dur) { this.show(msg, 'error',   dur); },
        warning: function(msg, dur) { this.show(msg, 'warning', dur); },
        info:    function(msg, dur) { this.show(msg, 'info',    dur); }
    };
})();
</script>
