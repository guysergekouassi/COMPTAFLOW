{{--
    Aperçu plein écran d'un document.

    Trois pièges se sont succédé sur cet écran :

    1. La classe plein écran de Bootstrap se faisait rogner par les feuilles du
       gabarit. La taille est donc imposée ici, sur l'identifiant de la fenêtre.

    2. Le document était chargé pendant que la fenêtre était encore fermée. Le
       lecteur ajuste la page à la largeur qu'il trouve au chargement et ne la
       reprend jamais : la page gardait la taille d'une fenêtre invisible.

    3. Vider puis recharger la même adresse laissait le cadre blanc. On ne joue
       donc plus avec la propriété « src » : l'adresse attend sur l'élément, et
       n'est posée qu'une fois, à l'ouverture.

    Variables : $id (identifiant de la fenêtre), $frame (identifiant de l'iframe),
                $titre, $icone (facultatifs)
--}}
@php
    $id = $id ?? 'modalPreviewPDF';
    $frame = $frame ?? 'pdfPreviewFrame';
    $titre = $titre ?? 'Aperçu du document';
    $icone = $icone ?? 'bxs-file-pdf';
@endphp

<style>
    /* Imposé sur l'identifiant : aucune feuille du gabarit ne peut le réduire. */
    #{{ $id }} { padding: 0 !important; }
    #{{ $id }} .modal-dialog {
        width: 100vw !important;
        max-width: 100vw !important;
        height: 100vh !important;
        margin: 0 !important;
        display: flex !important;
    }
    #{{ $id }} .modal-content {
        width: 100% !important;
        max-width: none !important;
        height: 100vh !important;
        border: 0 !important;
        border-radius: 0 !important;
        display: flex !important;
        flex-direction: column !important;
        background: #e9edf2 !important;
    }
    #{{ $id }} .modal-body {
        flex: 1 1 auto !important;
        min-height: 0 !important;
        padding: 0 !important;
        position: relative !important;
        background: #e9edf2 !important;
    }
    #{{ $id }} .apercu-cadre {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        border: 0;
        display: block;
        background: #e9edf2;
    }
    #{{ $id }} .apercu-fermer {
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.35);
        color: #fff;
        font-weight: 700;
        font-size: 0.78rem;
        border-radius: 8px;
        padding: 0.35rem 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }
    #{{ $id }} .apercu-fermer:hover { background: rgba(239, 68, 68, 0.85); border-color: transparent; }
    #{{ $id }} .apercu-attente {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 0.85rem;
        font-weight: 600;
    }
</style>

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header border-0 py-2 px-3 flex-shrink-0" style="background:#0f172a;">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:30px;height:30px;border-radius:8px;background:#2563eb;display:flex;align-items:center;justify-content:center;">
                        <i class="bx {{ $icone }} text-white fs-5"></i>
                    </div>
                    <h5 class="modal-title text-white fw-bold mb-0 fs-6">{{ $titre }}</h5>
                </div>
                {{-- Un bouton nommé, jamais tributaire d'une icône de fond. --}}
                <button type="button" class="apercu-fermer" data-bs-dismiss="modal" aria-label="Fermer">
                    <i class="bx bx-x fs-5"></i> Fermer
                </button>
            </div>
            <div class="modal-body">
                <div class="apercu-attente" id="{{ $frame }}_attente">Préparation du document…</div>
                <iframe id="{{ $frame }}" class="apercu-cadre" frameborder="0"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const fenetre = document.getElementById(@json($id));
    const cadre = document.getElementById(@json($frame));
    const attente = document.getElementById(@json($frame) + '_attente');
    if (!fenetre || !cadre) return;

    const REGLAGE = '#toolbar=0&navpanes=0&scrollbar=1&statusbar=0&view=FitH';

    // Les écrans posent l'adresse avant d'ouvrir la fenêtre. On la retient,
    // sans charger : le lecteur doit connaître la largeur définitive.
    Object.defineProperty(cadre, 'src', {
        configurable: true,
        get() { return cadre.dataset.apercuSrc || ''; },
        set(valeur) {
            if (!valeur || valeur === 'about:blank') {
                delete cadre.dataset.apercuSrc;
                cadre.removeAttribute('src');
                return;
            }
            cadre.dataset.apercuSrc = valeur.includes('#') ? valeur : valeur + REGLAGE;
            if (fenetre.classList.contains('show')) charger();
        },
    });

    function charger() {
        const adresse = cadre.dataset.apercuSrc;
        if (!adresse || cadre.getAttribute('src') === adresse) return;
        if (attente) attente.style.display = 'flex';
        cadre.setAttribute('src', adresse);
    }

    cadre.addEventListener('load', function () {
        if (attente && cadre.getAttribute('src')) attente.style.display = 'none';
    });

    fenetre.addEventListener('shown.bs.modal', charger);

    fenetre.addEventListener('hidden.bs.modal', function () {
        cadre.removeAttribute('src');
        if (attente) attente.style.display = 'flex';
    });
})();
</script>
