{{--
    Case « Tout cocher » posée au-dessus d'une grille d'habilitations.

    Elle agit sur toutes les cases d'habilitation contenues dans le bloc dont
    l'identifiant est passé en $cible, et se met à jour quand on coche à la main.

    Variables : $cible (id du bloc), $libelle (facultatif)
--}}
@php
    $cible = $cible ?? 'habilitations';
    $libelle = $libelle ?? 'Tout cocher';
    $jeton = 'tc_' . preg_replace('/[^A-Za-z0-9]/', '', $cible);
@endphp

<label class="d-inline-flex align-items-center gap-2 mb-0" style="font-size: 0.8rem; cursor: pointer;">
    <input type="checkbox" class="form-check-input mt-0" id="{{ $jeton }}">
    <span class="text-muted fw-semibold">{{ $libelle }}</span>
    <span class="text-muted">(<span id="{{ $jeton }}_n">0</span>)</span>
</label>

<script>
(function () {
    const bloc = document.getElementById(@json($cible));
    const tout = document.getElementById(@json($jeton));
    const compteur = document.getElementById(@json($jeton) + '_n');
    if (!bloc || !tout) return;

    const cases = bloc.querySelectorAll('input[type="checkbox"][name^="habilitations"]');

    function rafraichir() {
        const coches = bloc.querySelectorAll('input[type="checkbox"][name^="habilitations"]:checked').length;
        compteur.textContent = coches;
        tout.checked = cases.length > 0 && coches === cases.length;
        tout.indeterminate = coches > 0 && coches < cases.length;
    }

    tout.addEventListener('change', function () {
        cases.forEach(c => { c.checked = tout.checked; });
        rafraichir();
    });

    cases.forEach(c => c.addEventListener('change', rafraichir));
    rafraichir();
})();
</script>
