{{-- modal direct --}}

<div class="modal fade" id="saisieRedirectModal" tabindex="-1" aria-labelledby="saisieRedirectModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="saisieRedirectForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="saisieRedirectModalLabel">Informations de
                        la saisie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label for="exercice_id" class="form-label">Exercice</label>
                        <select class="selectpicker w-100" data-live-search="true" id="exercice_id" name="exercice_id"
                            data-exercice-actif="{{ isset($exerciceActif) ? $exerciceActif->id : '' }}"
                            required>
                            <option value="" disabled {{ isset($exerciceActif) ? '' : 'selected' }} hidden>
                                {{ isset($exerciceActif) ? '-- Sélectionnez un exercice --' : 'Aucun exercice actif trouvé' }}
                            </option>
                            @foreach ($exercices->unique('intitule') as $exercice)
                                <option value="{{ $exercice->id }}"
                                    data-annee="{{ \Carbon\Carbon::parse($exercice->date_debut)->format('Y') }}"
                                    {{ isset($exerciceActif) && $exerciceActif->id == $exercice->id ? 'selected' : '' }}>
                                    {{ $exercice->intitule }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    <div class="mb-2">
                        <label for="code_journal" class="form-label">Journals</label>
                        <select class="selectpicker w-100" data-live-search="true" id="code_journal" name="code_journal"
                            required>
                            <option value="" disabled selected hidden>-- Sélectionner un journal --</option>
                            @foreach ($code_journaux->unique('id') as $code_j)
                                @php
                                    $codeTresorerie = $code_j->code_tresorerie_display ?? null; // Utilise la nouvelle propriété
                                    $displayCode = $code_j->code_journal;

                                    if (!empty($codeTresorerie)) {
                                        $displayCode .= ' (Trésor: ' . $codeTresorerie . ')';
                                    }
                                @endphp
                                <option value="{{ $code_j->id }}"
                                    data-code_journal_j="{{ $code_j->code_journal }}"
                                    data-intitule_j="{{ $code_j->intitule }}"
                                    data-type_j="{{ $code_j->type }}"
                                    data-code_tresorerie_j="{{ $codeTresorerie ?? '' }}">

                                    {{ $displayCode }} - {{ $code_j->intitule }}
                                </option>
                            @endforeach
                        </select>
                    </div>




                    <div class="mb-2">
                        <label for="mois" class="form-label">Mois</label>

                        <select class="selectpicker w-100" data-live-search="true" id="mois" name="mois"
                            required>
                            <option value="" disabled selected hidden>-- Sélectionner un mois --</option>
                            <option value="1">Janvier</option>
                            <option value="2">Février</option>
                            <option value="3">Mars</option>
                            <option value="4">Avril</option>
                            <option value="5">Mai</option>
                            <option value="6">Juin</option>
                            <option value="7">Juillet</option>
                            <option value="8">Août</option>
                            <option value="9">Septembre</option>
                            <option value="10">Octobre</option>
                            <option value="11">Novembre</option>
                            <option value="12">Décembre</option>
                        </select>
                    </div>
                </div>




                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" id="btnRedirectToSaisie" class="btn btn-primary">Aller à la saisie</button>

                </div>
            </form>
        </div>
    </div>
</div>






<script>
    const accounting_entry_realSaisisUrl = "{{ route('accounting_entry_list') }}";
    const journaux_saisisfindSaisisUrl = "{{ route('journaux_saisis.find') }}";
    // const journaux_saisisfindSaisisUrl = "{{ url('journaux_saisis/find') }}";


    document.getElementById("btnRedirectToSaisie").addEventListener("click", function() {
        const journalSelect = document.getElementById("code_journal");
        const selectedOption = journalSelect.options[journalSelect.selectedIndex];

        const moisSelect = document.getElementById("mois");
        const selectedOptionM = moisSelect.options[moisSelect.selectedIndex];
        const moisValeur = selectedOptionM.value;
        const moisTexte = selectedOptionM.text;

        const exerciceSelect = document.getElementById("exercice_id");
        const selectedOptionA = exerciceSelect.options[exerciceSelect.selectedIndex];
        const anneeValue = selectedOptionA.dataset.annee;

        async function getJournalId() {
            const data = {
                exercice_id: selectedOptionA.value,
                annee: anneeValue,
                code_journal_id: selectedOption.value,
                mois: moisValeur,
            };

            try {
                const response = await fetch(journaux_saisisfindSaisisUrl + '?' + new URLSearchParams(data));
                const result = await response.json();

                if (result.success) {
                    console.log("ID du journal saisi :", result.id);
                    return result.id;
                } else {
                    console.log(result.message);
                    return null;
                }
            } catch (err) {
                console.error("Erreur:", err);
                return null;
            }
        }

        (async () => {


            const idSaisi = await getJournalId();
            if (!idSaisi) return; // stop si aucun ID récupéré

            const params = new URLSearchParams({
                id_exercice: selectedOptionA.value,
                id_journal: idSaisi,
                annee: anneeValue,
                mois: moisValeur,
                code: selectedOption.dataset.code_journal_j,

                type: selectedOption.dataset.type_j,
                intitule: selectedOption.dataset.intitule_j,
                id_code: selectedOption.value,
            });

            window.location.href = accounting_entry_realSaisisUrl + "?" + params.toString();
        })();
    });
</script>

