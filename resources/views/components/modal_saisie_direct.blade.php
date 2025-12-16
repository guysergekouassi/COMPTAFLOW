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
                        <select class="selectpicker w-100" data-live-search="false" id="exercice_id" name="exercice_id"
                            required>
                            <option value="" disabled selected hidden>-- Sélectionnez un exercice --</option>
                            @foreach ($exercices as $exercice)
                                <option value="{{ $exercice->id }}"
                                    data-annee="{{ \Carbon\Carbon::parse($exercice->date_debut)->format('Y') }}">
                                    {{ $exercice->intitule }}
                                </option>
                            @endforeach
                        </select>
                    </div>


                    <div class="mb-2">
                        <label for="code_journal" class="form-label">Journal</label>
                        <select class="selectpicker w-100" data-live-search="true" id="code_journal" name="code_journal"
                            required>
                            <option value="" disabled selected hidden>-- Sélectionner un journal --</option>
                            
                            @if(isset($journaux_saisie) && isset($journaux_tresorerie))
                                {{-- Journaux de Saisie --}}
                                @if($journaux_saisie->count() > 0)
                                    <optgroup label="📝 Journaux de Saisie">
                                        @foreach ($journaux_saisie as $code_j)
                                            <option value="{{ $code_j->id }}" data-code_journal_j="{{ $code_j->code_journal }}"
                                                data-intitule_j="{{ $code_j->intitule }}" data-type_j="{{ $code_j->type }}">

                                                {{ $code_j->code_journal }} - {{ $code_j->intitule }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                                
                                {{-- Journaux de Trésorerie (fusionnés : code_journals + tresoreries) --}}
                                @if($journaux_tresorerie->count() > 0)
                                    <optgroup label="💰 Journaux de Trésorerie">
                                        @foreach ($journaux_tresorerie as $code_j)
                                            <option value="{{ $code_j->id }}" data-code_journal_j="{{ $code_j->code_journal }}"
                                                data-intitule_j="{{ $code_j->intitule }}" data-type_j="{{ $code_j->type ?? 'tresorerie' }}">

                                                {{ $code_j->code_journal }} - {{ $code_j->intitule }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            @elseif(isset($code_journaux))
                                {{-- Fallback : affichage simple si les variables séparées ne sont pas disponibles --}}
                                @foreach ($code_journaux as $code_j)
                                    <option value="{{ $code_j->id }}" data-code_journal_j="{{ $code_j->code_journal }}"
                                        data-intitule_j="{{ $code_j->intitule }}" data-type_j="{{ $code_j->type }}">
                                        {{ $code_j->code_journal }} - {{ $code_j->intitule }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>


                    {{-- <div class="mb-2">
                        <label for="code_journal" class="form-label">Journal</label>
                        <select class="selectpicker w-100" data-live-search="true" id="code_journal" name="code_journal"
                            required>
                            @foreach ($journaux as $journal)
                                <option value="{{ $journal->id }}"
                                    data-exercice-id="{{ $journal->exercices_comptables_id }}"
                                    data-mois="{{ $journal->mois }}" data-annee="{{ $journal->annee }}"
                                    data-code="{{ $journal->codeJournal?->code_journal }}"
                                    data-intitule="{{ $journal->codeJournal?->intitule }}"
                                    data-type="{{ $journal->codeJournal?->type }}"
                                    data-id_code="{{ $journal->codeJournal?->id }}">

                                    {{ $journal->codeJournal?->code_journal }} -
                                    {{ \Carbon\Carbon::createFromDate(null, $journal->mois ?? 1)->locale('fr')->monthName }}
                                </option>
                            @endforeach
                        </select>
                    </div> --}}

                    <div class="mb-2">
                        <label for="mois" class="form-label">Mois</label>

                        <select class="selectpicker w-100" data-live-search="false" id="mois" name="mois"
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

{{-- Script pour éviter les doublons Bootstrap Select --}}
<script>
$(document).ready(function() {
    $('#saisieRedirectModal').on('shown.bs.modal', function () {
        console.log('🔧 Réinitialisation Bootstrap Select pour éviter les doublons');
        
        // Détruire complètement l'instance Bootstrap Select du champ Journal
        $('#code_journal').selectpicker('destroy');
        
        // Réinitialiser avec les options
        $('#code_journal').selectpicker({
            liveSearch: true,
            style: 'btn-default',
            size: 10,
            noneSelectedText: '-- Sélectionner un journal --'
        });
        
        // Forcer le refresh
        $('#code_journal').selectpicker('refresh');
        
        console.log('✅ Bootstrap Select réinitialisé');
    });
});
</script>

{{-- <script>
    //Réinitialise le formulaire de création quand le modal se ferme
    const modalCreate = document.getElementById("saisieRedirectModal");
    modalCreate?.addEventListener("hidden.bs.modal", function() {
        const form = modalCreate.querySelector("form");
        if (form) {
            form.reset();
        }
    });
</script> --}}


{{-- <script>
    document.addEventListener('DOMContentLoaded', function() {
        const exerciceSelect = document.getElementById('exercice_id');
        const journalSelect = document.getElementById('code_journal');
        const allJournalOptions = Array.from(journalSelect.options);

        function filterJournalsByExercice(exerciceId) {
            journalSelect.innerHTML = ''; // Clear all options

            allJournalOptions.forEach(option => {
                if (option.dataset.exerciceId === exerciceId) {
                    journalSelect.appendChild(option);
                }
            });

            if (journalSelect.options.length === 0) {
                const emptyOption = document.createElement('option');
                emptyOption.text = 'Aucun journal pour cet exercice';
                emptyOption.disabled = true;
                journalSelect.appendChild(emptyOption);
            }
        }

        exerciceSelect.addEventListener('change', function() {
            const selectedExerciceId = this.value;
            filterJournalsByExercice(selectedExerciceId);
        });

        // Initial filter on load
        filterJournalsByExercice(exerciceSelect.value);
    });
</script> --}}



<script>
    const accounting_entry_realSaisisUrl = "{{ route('accounting_entry_real') }}";
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
            // console.log("Exercice :", selectedOptionA.value);
            // console.log("Année :", anneeValue);
            // console.log("Code journal id :", selectedOption.value);
            // console.log("Code journal code :", selectedOption.dataset.code_journal_j);
            // console.log("Code journal intitule :", selectedOption.dataset.intitule_j);
            // console.log("Code journal type :", selectedOption.dataset.type_j);
            // console.log("Mois :", moisValeur);

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

