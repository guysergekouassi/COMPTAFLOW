{{--
    Boîte de téléchargement commune aux états financiers.

    Paramètres attendus :
      $route          nom de la route d'export (GET)
      $titre          titre affiché dans la boîte
      $exercice       exercice courant
      $exercices      exercices sélectionnables de l'entreprise
      $withMonth      true  → filtre « mois » (états annuels : bilan, résultat)
      $withMonthRange true  → filtre « de … à … » (états mensuels : TFT, exploitation mensuelle)
      $withExcel      true  → propose aussi l'export Excel
--}}
@php
    $withMonth      = $withMonth      ?? false;
    $withMonthRange = $withMonthRange ?? false;
    $withExcel      = $withExcel      ?? true;

    $svc = app(\App\Services\AccountingReportingService::class);

    // Mois de chaque exercice : permet de recalculer les listes côté client
    // quand l'utilisateur change d'exercice dans la boîte.
    $moisParExercice = [];
    foreach ($exercices as $ex) {
        $moisParExercice[$ex->id] = array_map(
            fn ($m) => $m['name'] . ' ' . $m['year'],
            $svc->getMonthsForExercice($ex->id)
        );
    }
    $moisCourants = $moisParExercice[$exercice->id] ?? [];
@endphp

<div class="modal fade" id="reportDownloadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border: none; border-radius: 18px; overflow: hidden;">
            <form method="GET" action="{{ route($route) }}" id="reportDownloadForm">
                <div class="modal-header border-0 pb-0 px-4 pt-4">
                    <div>
                        <h5 class="modal-title fw-bold mb-1">Télécharger — {{ $titre }}</h5>
                        <p class="text-muted small mb-0">Choisissez l'exercice, la période et le comparatif avant de générer le document.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>

                <div class="modal-body px-4 py-4">
                    <div class="row g-4">
                        {{-- Exercice --}}
                        <div class="col-md-6">
                            <label for="dl_exercice_id" class="form-label small fw-bold text-uppercase text-muted">Exercice</label>
                            <select name="exercice_id" id="dl_exercice_id" class="form-select">
                                @foreach($exercices as $ex)
                                    <option value="{{ $ex->id }}" {{ $ex->id == $exercice->id ? 'selected' : '' }}>
                                        {{ $ex->intitule }}
                                        ({{ \Carbon\Carbon::parse($ex->date_debut)->format('d/m/Y') }} → {{ \Carbon\Carbon::parse($ex->date_fin)->format('d/m/Y') }})
                                        {{ $ex->cloturer ? ' — clôturé' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Période --}}
                        @if($withMonth)
                            <div class="col-md-6">
                                <label for="dl_month" class="form-label small fw-bold text-uppercase text-muted">Période</label>
                                <select name="month" id="dl_month" class="form-select">
                                    <option value="all">Tout l'exercice</option>
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ (string) request('month') === (string) $m ? 'selected' : '' }}>
                                            {{ ucfirst(\Carbon\Carbon::create()->month($m)->locale('fr')->monthName) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        @if($withMonthRange)
                            <div class="col-md-3">
                                <label for="dl_month_from" class="form-label small fw-bold text-uppercase text-muted">Du mois</label>
                                <select name="month_from" id="dl_month_from" class="form-select js-month-range">
                                    @foreach($moisCourants as $i => $nom)
                                        <option value="{{ $i + 1 }}">{{ $nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="dl_month_to" class="form-label small fw-bold text-uppercase text-muted">Au mois</label>
                                <select name="month_to" id="dl_month_to" class="form-select js-month-range">
                                    @foreach($moisCourants as $i => $nom)
                                        <option value="{{ $i + 1 }}" {{ $i === count($moisCourants) - 1 ? 'selected' : '' }}>{{ $nom }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        {{-- Options --}}
                        <div class="col-12">
                            <div class="d-flex flex-wrap gap-4 p-3 rounded-3" style="background: #f8fafc;">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" id="dl_comparatif" name="comparatif" value="1">
                                    <label class="form-check-label fw-semibold" for="dl_comparatif">
                                        Comparatif N-1
                                        <span class="d-block text-muted small fw-normal">Ajoute l'exercice précédent au document</span>
                                    </label>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" id="dl_detail" name="detail" value="1" {{ request('detail') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="dl_detail">
                                        Détail des comptes
                                        <span class="d-block text-muted small fw-normal">Développe chaque poste compte par compte</span>
                                    </label>
                                </div>
                            </div>
                            <div class="alert alert-warning d-none mt-3 mb-0 py-2 px-3 small" id="dl_no_n1">
                                Aucun exercice antérieur n'est enregistré pour cette entreprise : le comparatif N-1 sera ignoré.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-0 d-flex gap-2">
                    <button type="button" class="btn btn-light flex-fill" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" name="format" value="pdf" class="btn btn-danger flex-fill fw-bold">
                        <i class="bx bxs-file-pdf me-1"></i> Télécharger en PDF
                    </button>
                    @if($withExcel)
                        <button type="submit" name="format" value="excel" class="btn btn-success flex-fill fw-bold">
                            <i class="bx bxs-spreadsheet me-1"></i> Télécharger en Excel
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function () {
    var moisParExercice = @json($moisParExercice);
    var exerciceSelect  = document.getElementById('dl_exercice_id');
    var alerteN1        = document.getElementById('dl_no_n1');
    if (!exerciceSelect) return;

    // Un exercice N-1 existe s'il y a un exercice plus ancien dans la liste.
    var ordreExercices = Array.from(exerciceSelect.options).map(function (o) { return o.value; });

    function majPlageMois() {
        var mois = moisParExercice[exerciceSelect.value] || [];
        document.querySelectorAll('.js-month-range').forEach(function (select) {
            var ancienIndex = select.selectedIndex;
            select.innerHTML = '';
            mois.forEach(function (nom, i) {
                var opt = document.createElement('option');
                opt.value = i + 1;
                opt.textContent = nom;
                select.appendChild(opt);
            });
            if (select.id === 'dl_month_to') {
                select.selectedIndex = mois.length - 1;
            } else if (ancienIndex >= 0 && ancienIndex < mois.length) {
                select.selectedIndex = ancienIndex;
            }
        });
    }

    function majAlerteN1() {
        if (!alerteN1) return;
        // Les exercices sont listés du plus récent au plus ancien : le dernier n'a pas de N-1.
        var estLePlusAncien = exerciceSelect.selectedIndex === ordreExercices.length - 1;
        alerteN1.classList.toggle('d-none', !estLePlusAncien);
    }

    exerciceSelect.addEventListener('change', function () {
        majPlageMois();
        majAlerteN1();
    });
    majAlerteN1();
})();
</script>
