{{--
    Grille des habilitations d'accès.

    Un rôle nommé ne dit pas ce qu'il permet : on coche, et l'on sait. Reprise
    partout où l'on accorde des droits (création d'un collaborateur, affectation
    à une comptabilité), pour que la même lecture serve dans tous les écrans.

    Variables attendues :
      $prefixe  identifiant unique de la grille sur la page
      $cochees  tableau des clés déjà accordées (facultatif)
      $titre    intitulé du cadre (facultatif)
--}}
@php
    $prefixe = $prefixe ?? 'hab';
    $cochees = $cochees ?? [];
    $titre = $titre ?? "Configuration des habilitations d'accès";
    $sections = collect(config('accounting_permissions.permissions', []))
        ->filter(fn ($perms, $section) => is_array($perms) && !str_contains($section, 'Super Admin'));
@endphp

<div class="habilitations-grid border rounded-3 p-3" id="{{ $prefixe }}-grille">
    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
        <div class="fw-bold text-dark" style="font-size: 0.9rem;">
            <i class="fa-solid fa-shield-halved text-primary me-2"></i>{{ $titre }}
        </div>
        <label class="d-flex align-items-center gap-2 mb-0" style="font-size: 0.8rem; cursor: pointer;">
            <input type="checkbox" class="form-check-input mt-0" id="{{ $prefixe }}-tout">
            <span class="text-muted">Tout cocher</span>
        </label>
    </div>

    <div class="row g-3">
        @foreach($sections as $section => $permissions)
            <div class="col-md-4">
                <div class="text-uppercase fw-bold text-muted mb-2 pb-1 border-bottom"
                     style="font-size: 0.68rem; letter-spacing: 0.05em;">{{ $section }}</div>
                @foreach($permissions as $cle => $libelle)
                    <label class="d-flex align-items-start gap-2 mb-1" style="font-size: 0.8rem; cursor: pointer;">
                        <input type="checkbox" class="form-check-input mt-1 {{ $prefixe }}-case"
                               name="habilitations[]" value="{{ $cle }}"
                               {{ array_key_exists($cle, $cochees) || in_array($cle, $cochees, true) ? 'checked' : '' }}>
                        <span class="text-dark">{{ $libelle }}</span>
                    </label>
                @endforeach
            </div>
        @endforeach
    </div>

    <div class="mt-3 pt-2 border-top text-muted" style="font-size: 0.74rem;">
        <span id="{{ $prefixe }}-compteur">0</span> habilitation(s) cochée(s).
    </div>
</div>

<script>
(function () {
    const racine = document.getElementById('{{ $prefixe }}-grille');
    if (!racine) return;

    const tout = document.getElementById('{{ $prefixe }}-tout');
    const cases = racine.querySelectorAll('.{{ $prefixe }}-case');
    const compteur = document.getElementById('{{ $prefixe }}-compteur');

    function rafraichir() {
        const coches = racine.querySelectorAll('.{{ $prefixe }}-case:checked').length;
        compteur.textContent = coches;
        tout.checked = coches === cases.length && cases.length > 0;
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
