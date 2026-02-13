
<style>
    /* Premium Modal Styles */
    .premium-modal-content-wide {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .input-field-premium {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        background-color: white;
    }

    .input-field-premium:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .input-label-premium {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }

    .btn-save-premium {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 16px;
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        transition: all 0.2s ease;
        box-shadow: 0 4px 6px -1px rgba(30, 64, 175, 0.3);
        width: 100%;
    }

    .btn-save-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(30, 64, 175, 0.4);
    }

    .btn-cancel-premium {
        background: #f1f5f9;
        color: #64748b;
        border: 2px solid #e2e8f0;
        padding: 0.75rem 1.5rem;
        border-radius: 16px;
        font-weight: 700;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        transition: all 0.2s ease;
        width: 100%;
    }

    .btn-cancel-premium:hover {
        background: #e2e8f0;
        color: #475569;
    }

    .text-blue-gradient-premium {
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Force Selectpicker text to be black and bold */
    .bootstrap-select .filter-option-inner-inner {
        color: #1e293b !important;
        font-weight: 700 !important;
    }
</style>

{{-- modal direct --}}
<div class="modal fade" id="saisieRedirectModal" tabindex="-1" aria-labelledby="saisieRedirectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" style="max-height: 90vh; margin: auto;">
        <div class="modal-content premium-modal-content-wide" style="padding: 1.5rem; max-height: 90vh; overflow-y: auto;">
            <form id="saisieRedirectForm">
                <!-- Header -->
                <div class="text-center mb-4 position-relative">
                    <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Fermer" style="top: -0.5rem; right: -0.5rem;"></button>
                    <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); border-radius: 12px; box-shadow: 0 4px 12px rgba(30, 64, 175, 0.2);">
                        <i class="bx bx-edit" style="font-size: 24px; color: white;"></i>
                    </div>
                    <h1 class="text-xl font-extrabold tracking-tight text-slate-900 mb-2" style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.5rem;">
                        Nouvelle <span class="text-blue-gradient-premium">Saisie</span>
                    </h1>
                    <p class="text-muted mb-0" style="font-size: 0.85rem; color: #64748b;">Sélectionnez les informations de la saisie</p>
                </div>

                <div class="modal-body" style="padding: 0;">
                    <div class="row g-3">
                        <!-- Exercice Card -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #f8fafc;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%); border-radius: 8px;">
                                            <i class="bx bx-calendar-check" style="font-size: 16px; color: white;"></i>
                                        </div>
                                        <h6 class="mb-0" style="font-weight: 700; font-size: 0.85rem; color: #1e293b;">Exercice comptable</h6>
                                    </div>
                                    
                                    @php
                                        $isContextLocked = session('current_exercice_id') ? true : false;
                                        $selectedExerciceId = $isContextLocked ? session('current_exercice_id') : ($exerciceActif->id ?? null);
                                        $selectedExerciceModel = $exercices->where('id', $selectedExerciceId)->first();
                                        $annee = $selectedExerciceModel ? \Carbon\Carbon::parse($selectedExerciceModel->date_debut)->format('Y') : '';
                                    @endphp

                                    @if($isContextLocked)
                                        <div class="alert alert-soft-primary d-flex align-items-center p-2 mb-2" role="alert" style="font-size: 0.75rem;">
                                            <i class="bx bx-lock-alt me-2"></i>
                                            <div>
                                                <strong>Exercice verrouillé</strong> selon le contexte sélectionné.
                                            </div>
                                        </div>
                                        <input type="hidden" id="exercice_id" name="exercice_id" value="{{ $selectedExerciceId }}" data-annee="{{ $annee }}">
                                        <input type="text" class="form-control input-field-premium bg-light" value="{{ $selectedExerciceModel->intitule ?? 'Exercice inconnu' }} ({{ $annee }})" readonly disabled>
                                    @else
                                        <select class="selectpicker w-100 input-field-premium" data-live-search="true" id="exercice_id" name="exercice_id" required style="padding: 0.625rem 0.875rem; font-size: 0.8rem;">
                                            <option value="" disabled hidden>
                                                {{ $exerciceActif ? '-- Sélectionnez un exercice --' : 'Aucun exercice disponible' }}
                                            </option>
                                            @foreach ($exercices as $exercice)
                                                @if(isset($selectedExerciceId) && $selectedExerciceId && $exercice->id != $selectedExerciceId)
                                                    @continue
                                                @endif
                                                <option value="{{ $exercice->id }}"
                                                    data-annee="{{ \Carbon\Carbon::parse($exercice->date_debut)->format('Y') }}"
                                                    {{ (isset($exerciceActif) && $exercice->id == $exerciceActif->id) || (isset($data['id_exercice']) && $exercice->id == $data['id_exercice']) ? 'selected' : '' }}>
                                                    {{ $exercice->intitule }} ({{ \Carbon\Carbon::parse($exercice->date_debut)->format('Y') }})
                                                    @if($exercice->is_active) - ACTIVÉ 🟢 @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Journal Card -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #f8fafc;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%); border-radius: 8px;">
                                            <i class="bx bx-book" style="font-size: 16px; color: white;"></i>
                                        </div>
                                        <h6 class="mb-0" style="font-weight: 700; font-size: 0.85rem; color: #1e293b;">Journal</h6>
                                    </div>
                                    <select class="selectpicker w-100 input-field-premium" data-live-search="true" id="code_journal" name="code_journal" required style="padding: 0.625rem 0.875rem; font-size: 0.8rem;">
                                        <option value="" disabled selected hidden>-- Sélectionner un journal --</option>
                                        @foreach ($code_journaux->unique('id') as $code_j)
                                            @php
                                                $codeTresorerie = $code_j->code_tresorerie_display ?? null;
                                                $displayCode = $code_j->code_journal;
                                                if (!empty($codeTresorerie)) {
                                                    $displayCode .= '' . $codeTresorerie ;
                                                
                                                }
                                            @endphp
                                            <option value="{{ $code_j->id }}"
                                                data-code_journal_j="{{ $code_j->code_journal }}"
                                                data-intitule_j="{{ $code_j->intitule }}"
                                                data-type_j="{{ $code_j->type }}"
                                                data-code_tresorerie_j="{{ $codeTresorerie ?? '' }}">
                                                {{ rtrim($displayCode, '-') }} - {{ $code_j->intitule }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Mois Card -->
                        <div class="col-12">
                            <div class="card border-0 shadow-sm" style="border-radius: 12px; background: #f8fafc;">
                                <div class="card-body p-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%); border-radius: 8px;">
                                            <i class="bx bx-calendar" style="font-size: 16px; color: white;"></i>
                                        </div>
                                        <h6 class="mb-0" style="font-weight: 700; font-size: 0.85rem; color: #1e293b;">Période</h6>
                                    </div>
                                    <select class="selectpicker w-100 input-field-premium" data-live-search="true" id="mois" name="mois" required style="padding: 0.625rem 0.875rem; font-size: 0.8rem;">
                                        <option value="" disabled hidden>-- Sélectionner un mois --</option>
                                        @php
                                            $months = [
                                                1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
                                                5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
                                                9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
                                            ];
                                            $currentMonth = date('n');
                                        @endphp
                                        @foreach($months as $num => $name)
                                            <option value="{{ $num }}" {{ $currentMonth == $num ? 'selected' : '' }}>{{ $name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="d-flex flex-column gap-2 mt-3 pt-3" style="border-top: 1px solid #e2e8f0;">
                    <div id="initialActions" class="d-flex gap-2 w-100">
                        <button type="button" class="btn btn-cancel-premium flex-fill" data-bs-dismiss="modal" style="padding: 0.75rem 1rem; font-size: 0.8rem;">
                            <i class="bx bx-x me-1"></i>Fermer
                        </button>
                        <button type="button" id="btnRedirectToSaisie" class="btn-save-premium flex-fill" style="padding: 0.75rem 1rem; font-size: 0.8rem;">
                            <i class="bx bx-right-arrow-alt me-1"></i>Continuer
                        </button>
                    </div>
                    
                    <div id="choiceActions" class="d-none animate__animated animate__fadeInUp">
                        <p class="text-center fw-bold mb-3" style="font-size: 0.85rem; color: #1e293b;">Comment souhaitez-vous passer l'écriture ?</p>
                        <div class="d-flex gap-2 w-100">
                            <button type="button" id="btnSaisieManuelle" class="btn btn-outline-primary flex-fill" style="padding: 0.75rem 1rem; border-radius: 12px; font-weight: 700; font-size: 0.75rem; text-transform: uppercase;">
                                <i class="bx bx-keyboard me-1"></i>Saisie Manuelle
                            </button>
                            <button type="button" id="btnScannerFacture" class="btn-save-premium flex-fill" style="padding: 0.75rem 1rem; font-size: 0.75rem;">
                                <i class="bx bx-scan me-1"></i>Scanner Facture
                            </button>
                        </div>
                        <button type="button" id="btnBackToChoice" class="btn btn-link w-100 mt-2 text-muted" style="font-size: 0.75rem;">
                            <i class="bx bx-chevron-left"></i> Retour aux options
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<script>


 $(document).ready(function() {
    // Détruire toute instance existante pour éviter les doublons
    if ($('.selectpicker').data('selectpicker')) {
        $('.selectpicker').selectpicker('destroy');
    }
    
    // Initialisation propre
    $('.selectpicker').selectpicker({
        liveSearch: true,
        width: '100%',
        style: 'btn-outline-secondary',
        noneSelectedText: '-- Sélectionner --'
    });

    // Réinitialiser proprement à chaque ouverture du modal
    $('#saisieRedirectModal').on('show.bs.modal', function () {
        // Détruire et recréer pour éviter les doublons
        $('.selectpicker').selectpicker('destroy');
        $('.selectpicker').selectpicker({
            liveSearch: true,
            width: '100%',
            style: 'btn-outline-secondary',
            noneSelectedText: '-- Sélectionner --'
        });
    });
    
    // Refresh après affichage pour corriger les calculs de largeur
    $('#saisieRedirectModal').on('shown.bs.modal', function () {
        $('.selectpicker').selectpicker('refresh');
    });
});
    const accounting_entry_realUrl = "{{ route('accounting_entry_real') }}";
    const ecriture_scanUrl = "{{ route('ecriture.scan') }}";
    const journaux_saisisfindSaisisUrl = "{{ route('journaux_saisis.find') }}";

    let globalParams = null;

    document.getElementById("btnRedirectToSaisie").addEventListener("click", async function() {
        const journalSelect = document.getElementById("code_journal");
        const moisSelect = document.getElementById("mois");
        const exerciceSelect = document.getElementById("exercice_id");

        if (!journalSelect.value || !moisSelect.value || !exerciceSelect.value) {
            alert("Veuillez remplir tous les champs.");
            return;
        }

        const selectedOption = journalSelect.options[journalSelect.selectedIndex];
        const moisValeur = moisSelect.value;
        
        // Gestion dynamique selon si c'est un select ou un hidden input (locked context)
        let selectedExerciceId, anneeValue;
        
        if (exerciceSelect.tagName === 'SELECT') {
            const selectedOptionA = exerciceSelect.options[exerciceSelect.selectedIndex];
            selectedExerciceId = selectedOptionA.value;
            anneeValue = selectedOptionA.dataset.annee;
        } else {
            selectedExerciceId = exerciceSelect.value;
            anneeValue = exerciceSelect.dataset.annee;
        }

        const idSaisi = await getJournalId(selectedExerciceId, anneeValue, selectedOption.value, moisValeur);
        if (!idSaisi) {
            alert("Aucun journal trouvé pour les critères sélectionnés.");
            return;
        }

        globalParams = new URLSearchParams({
            id_exercice: selectedExerciceId,
            id_journal: idSaisi,
            annee: anneeValue,
            mois: moisValeur,
            code: selectedOption.dataset.code_journal_j,
            type: selectedOption.dataset.type_j,
            intitule: selectedOption.dataset.intitule_j,
            id_code: selectedOption.value,
        });

        // Switch button visibility
        document.getElementById("initialActions").classList.add("d-none");
        document.getElementById("choiceActions").classList.remove("d-none");
    });

    document.getElementById("btnBackToChoice").addEventListener("click", function() {
        document.getElementById("choiceActions").classList.add("d-none");
        document.getElementById("initialActions").classList.remove("d-none");
    });

    document.getElementById("btnSaisieManuelle").addEventListener("click", function() {
        if (globalParams) {
            window.location.href = accounting_entry_realUrl + "?" + globalParams.toString();
        }
    });

    document.getElementById("btnScannerFacture").addEventListener("click", function() {
        if (globalParams) {
            window.location.href = ecriture_scanUrl + "?" + globalParams.toString();
        }
    });

    async function getJournalId(exercice_id, annee, code_journal_id, mois) {
        const data = { exercice_id, annee, code_journal_id, mois };
        try {
            const response = await fetch(journaux_saisisfindSaisisUrl + '?' + new URLSearchParams(data));
            const result = await response.json();
            return result.success ? result.id : null;
        } catch (err) {
            console.error("Erreur:", err);
            return null;
        }
    }
</script>

