{{--
    Aperçu plein écran d'un document.

    Un état comptable se lit en grand : la fenêtre prend toute la page, en
    hauteur comme en largeur, et le document occupe tout ce qui reste sous le
    bandeau. L'iframe est posée en absolu dans un corps en position relative :
    c'est ce qui lui donne une hauteur ferme, là où un height:100% dans un
    conteneur souple se replie sur la hauteur par défaut, d'où le petit carré.

    Variables : $id (identifiant de la fenêtre), $frame (identifiant de l'iframe),
                $titre, $icone (facultatifs)
--}}
@php
    $id = $id ?? 'modalPreviewPDF';
    $frame = $frame ?? 'pdfPreviewFrame';
    $titre = $titre ?? 'Aperçu du document';
    $icone = $icone ?? 'bxs-file-pdf';
@endphp

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen m-0" role="document">
        <div class="modal-content border-0"
             style="height:100vh;max-height:100vh;border-radius:0;display:flex;flex-direction:column;">
            <div class="modal-header border-0 py-2 px-3 flex-shrink-0" style="background:#0f172a;">
                <div class="d-flex align-items-center gap-2">
                    <div style="width:30px;height:30px;border-radius:8px;background:#2563eb;display:flex;align-items:center;justify-content:center;">
                        <i class="bx {{ $icone }} text-white fs-5"></i>
                    </div>
                    <h5 class="modal-title text-white fw-bold mb-0 fs-6">{{ $titre }}</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body p-0" style="flex:1 1 auto;min-height:0;position:relative;background:#525659;">
                <iframe id="{{ $frame }}" src="about:blank" frameborder="0"
                        style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;display:block;"></iframe>
            </div>
        </div>
    </div>
</div>
