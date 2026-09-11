{{--
    Aperçu plein écran d'un document.

    Deux pièges expliquaient l'aperçu minuscule sur fond noir :

    1. La classe plein écran de Bootstrap se faisait rogner par les feuilles de
       style du gabarit. On ne s'y fie plus : la taille est imposée ici, sur
       l'identifiant de la fenêtre, hors d'atteinte des autres règles.

    2. Le document était chargé pendant que la fenêtre était encore fermée. Le
       lecteur PDF ajuste la page à la largeur qu'il trouve au chargement, et
       ne la reprend pas ensuite : la page restait à la taille d'une fenêtre
       invisible. On ne charge donc le document qu'une fois la fenêtre ouverte,
       à sa vraie largeur.

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
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <iframe id="{{ $frame }}" class="apercu-cadre" src="about:blank" frameborder="0"></iframe>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const fenetre = document.getElementById(@json($id));
    const cadre = document.getElementById(@json($frame));
    if (!fenetre || !cadre) return;

    const REGLAGE = '#toolbar=0&navpanes=0&scrollbar=1&statusbar=0&view=FitH';

    function adresseVoulue() {
        const src = cadre.getAttribute('src') || '';
        if (!src || src === 'about:blank') return null;
        return src.includes('#') ? src : src + REGLAGE;
    }

    // Le document est rechargé à l'ouverture, quand la fenêtre a sa vraie
    // largeur : c'est là que le lecteur ajuste la page.
    fenetre.addEventListener('shown.bs.modal', function () {
        const adresse = adresseVoulue();
        if (!adresse) return;
        cadre.src = 'about:blank';
        window.requestAnimationFrame(function () { cadre.src = adresse; });
    });

    // Refermer libère le document : on ne garde pas un PDF en mémoire.
    fenetre.addEventListener('hidden.bs.modal', function () {
        cadre.src = 'about:blank';
    });
})();
</script>
