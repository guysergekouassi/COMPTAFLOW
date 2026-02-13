@include('components.head')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap');

    body {
        background-color: #f1f5f9;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .master-header {
        background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);
        border-radius: 24px;
        padding: 3rem;
        color: white;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .glass-table-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 24px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
    }

    .btn-premium {
        background: #059669;
        color: white;
        border: none;
        padding: 0.8rem 2rem;
        border-radius: 12px;
        font-weight: 700;
        transition: all 0.3s ease;
    }
    .btn-premium:hover {
        background: #047857;
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(5, 150, 105, 0.3);
    }

    .journal-badge {
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 0.65rem;
        padding: 0.4rem 1rem;
        border-radius: 30px;
    }
    
    /* Force la visibilité du titre du modal en production */
    .modal-header .modal-title {
         color: #ffffff !important;
        font-weight: 900 !important;
        display: block !important;
        visibility: visible !important;
    }

    /* Fix pour éviter que les clics sur les labels ferment les menus */
    .form-check-label, .form-check-input {
        pointer-events: auto;
    }

    /* Empêche les Selects de Classe 5, Analytique et Rapprochement d'être perturbés par le modal */
    select.form-select {
        position: relative;
        z-index: 1060; /* S'assure que la liste déroulante reste au-dessus du fond du modal */
    }

    /* Fix spécifique pour le focus sur les champs instables */
    .form-select:focus, .form-control:focus {
        border-color: #059669 !important;
        box-shadow: 0 0 0 0.25 margin-right: rgba(5, 150, 105, 0.25) !important;
}

</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Modèle <span class="text-emerald-600">Master</span> des Journaux'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <div class="master-header shadow-2xl">
                            <div class="row align-items-center">
                                <div class="col-lg-8">
                                    <span class="badge border border-emerald-400 text-emerald-100 mb-4 d-inline-block rounded-pill px-4 py-1 text-xs font-black uppercase">Standardisation Flux</span>
                                    <h1 class="font-black mb-2 tracking-tighter">Modèle des Journaux</h1>
                                    <p class="opacity-70 font-medium">Configurez les codes journaux (ACH, VEN, CSH) standards du groupe. Cette structure garantit la cohérence des rapports consolidés à travers toutes vos filiales.</p>
                                </div>
                                <div class="col-lg-4 text-end d-flex flex-column gap-2 border-start border-white/10 ps-6">
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-premium w-100" data-bs-toggle="modal" data-bs-target="#modalCreateCodeJournal">
                                            <i class="fa-solid fa-folder-plus me-2"></i> Nouveau Code
                                        </button>
                                        <form action="{{ route('admin.config.master_reset_journals') }}" method="POST" onsubmit="return confirm('Voulez-vous vider tous les journaux du modèle ?');">
                                            @csrf
                                            <button type="submit" class="btn btn-danger rounded-xl px-4 py-3" title="Annuler / Vider les journaux">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <form action="{{ route('admin.config.master_load_journals') }}" method="POST" class="w-100">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-light w-100 border-2 font-black rounded-xl">
                                            <i class="fa-solid fa-bolt-lightning me-2"></i> Charger par défaut
                                        </button>
                                    </form>
                                    <div class="d-flex flex-column gap-2 mt-2">
                                        <button class="btn btn-outline-warning w-100 border-2 font-black rounded-xl" data-bs-toggle="modal" data-bs-target="#modalJournalSettings">
                                            <i class="fa-solid fa-gears me-2"></i> Configurer (Code: {{ $mainCompany->journal_code_digits ?? '3' }} {{ strtoupper(substr($mainCompany->journal_code_type ?? 'alpha', 0, 3)) }})
                                        </button>
                                        <button class="btn btn-outline-light w-100 border-2 font-black rounded-xl" data-bs-toggle="modal" data-bs-target="#modalImportJournals">
                                            <i class="fa-solid fa-file-import me-2"></i> Importer Journaux (Excel/CSV)
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="position-absolute end-0 top-0 opacity-10" style="transform: translate(20%, -20%) rotate(-15deg);">
                                <i class="fa-solid fa-swatchbook fa-10x"></i>
                            </div>
                        </div>

                        <div class="glass-table-card overflow-hidden">
                            <div class="p-8 border-b border-slate-100 d-flex justify-content-between align-items-center bg-white">
                                <div>
                                    <h4 class="font-black mb-0">Nomenclature des Journaux</h4>
                                    <p class="text-slate-400 text-sm mb-0">Définition des flux de trésorerie et d'opérations.</p>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-emerald-50/30">
                                        <tr>
                                            <th class="ps-8 py-5 text-uppercase text-xs font-black text-emerald-700">Type</th>
                                            <th class="py-5 text-uppercase text-xs font-black text-emerald-700">Code</th>
                                            <th class="py-5 text-uppercase text-xs font-black text-emerald-700">Intitulé</th>
                                            <th class="py-5 text-uppercase text-xs font-black text-emerald-700">Compte</th>
                                            <th class="py-5 text-uppercase text-xs font-black text-emerald-700">Traitement Analytique</th>
                                            <th class="py-5 text-uppercase text-xs font-black text-emerald-700">État Rapprochement</th>
                                            <th class="pe-8 py-5 text-uppercase text-xs font-black text-emerald-700 text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white">
                                        @foreach($journals as $journal)
                                        <tr>
                                            <td class="ps-8 py-6">
                                                <span class="journal-badge border border-emerald-200 text-emerald-700 bg-emerald-50">{{ strtoupper($journal->type) }}</span>
                                            </td>
                                            <td class="py-6">
                                                <div class="d-flex flex-column">
                                                    <span class="font-black text-emerald-700 fs-5">{{ $journal->code_journal }}</span>
                                                    @if(!empty($journal->numero_original))
                                                        <div class="text-[10px] text-slate-400 font-medium italic mt-1 d-flex align-items-center gap-1">
                                                            <i class="fa-solid fa-file-import text-[8px]"></i> Original: {{ $journal->numero_original }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="py-6 font-bold text-slate-800">
                                                {{ $journal->intitule }}
                                            </td>
                                            <td class="py-6">
                                                @if(in_array($journal->type, ['Banque', 'Caisse', 'Trésorerie', 'Tresorerie']))
                                                    <span class="text-xs font-black text-slate-500">{{ $journal->code_tresorerie_display }}</span>
                                                @else
                                                    <span class="text-xs text-slate-400 italic">-</span>
                                                @endif
                                            </td>
                                            <td class="py-6">
                                                <span class="badge {{ $journal->traitement_analytique ? 'bg-label-success' : 'bg-label-secondary' }} font-bold">
                                                    {{ $journal->traitement_analytique ? 'OUI' : 'NON' }}
                                                </span>
                                            </td>
                                            <td class="py-6 font-semibold text-slate-600">
                                                {{ $journal->rapprochement_sur ?? '-' }}
                                            </td>
                                            <td class="pe-8 py-6 text-end">
                                                <div class="btn-group">
                                                    <button class="btn btn-icon btn-sm btn-outline-emerald border-0 rounded-circle" onclick="editJournal({{ $journal->id }}, '{{ $journal->code_journal }}', '{{ addslashes($journal->intitule) }}', '{{ $journal->type }}', '{{ $journal->code_tresorerie_display }}', '{{ $journal->poste_tresorerie }}', '{{ $journal->compte_de_contrepartie }}', '{{ $journal->traitement_analytique ? 'oui' : 'non' }}', '{{ $journal->rapprochement_sur }}')" title="Modifier"><i class="fa-solid fa-sliders"></i></button>
                                                    <form action="{{ route('admin.config.master_delete_journal', $journal->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce journal du modèle master ?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-icon btn-sm btn-outline-danger border-0 rounded-circle" title="Supprimer"><i class="fa-solid fa-trash-can"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                    @include('components.footer')
                </div>
            </div>
        </div>
    </div>


    <!-- Modal Create Journal -->
    <div class="modal fade" id="modalCreateCodeJournal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden">
                <form action="{{ route('admin.config.master_store_journal') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-emerald-900 p-6">
                        <h5 class="modal-title text-white fw-bold">Interface d'Administration - Configuration des Journaux</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-8">
                        <div class="row g-6">
                            <div class="col-md-6">
                                <label class="form-label font-black text-slate-700">Code Journal</label>
                                <input type="text" name="code_journal" id="create_code_journal" class="form-control border-slate-200 py-3 rounded-xl shadow-none focus:border-emerald-500" placeholder="Ex: ACH" maxlength="{{ $mainCompany->journal_code_digits ?? 3 }}" required readonly style="background-color: #f8fafc; cursor: not-allowed;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-black text-slate-700">Type</label>
                                <select name="type" class="form-select border-slate-200 py-3 rounded-xl focus:border-emerald-500" id="journal_type_select" onchange="toggleTresorerieFields(this.value, 'create'); updateJournalCode('create')" required>
                                    <option value="" disabled selected>-- Choisir un type --</option>
                                    <option value="Achats">Achats</option>
                                    <option value="Ventes">Ventes</option>
                                    <option value="Tresorerie">Trésorerie</option>
                                    <option value="Opérations Diverses">Opérations Diverses</option>
                                    <option value="Standard">Standard</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label font-black text-slate-700">Intitulé</label>
                                <input type="text" name="intitule" class="form-control border-slate-200 py-3 rounded-xl focus:border-emerald-500" placeholder="Ex: JOURNAL DES ACHATS" required>
                            </div>

                            <div class="col-12">
                                <label class="form-label font-black text-slate-700">Traitement Analytique</label>
                                <select name="traitement_analytique" class="form-select border-slate-200 py-3 rounded-xl focus:border-emerald-500">
                                    <option value="non">Non</option>
                                    <option value="oui">Oui</option>
                                </select>
                            </div>

                            <div id="tresorerie_fields_create" class="row g-6 mt-0 d-none">
                                <div class="col-12">
                                    <label class="form-label font-black text-slate-700">Compte (Classe 5)</label>
                                    <select name="compte_de_tresorerie" class="form-select border-slate-200 py-3 rounded-xl">
                                        <option value="">-- Sélectionner un compte --</option>
                                        @foreach($plansComptables as $plan)
                                            @if(str_starts_with($plan->numero_de_compte, '5'))
                                                <option value="{{ $plan->numero_de_compte }}">{{ $plan->numero_de_compte }} - {{ $plan->intitule }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label font-black text-slate-700">Type de Trésorerie</label>
                                    <div class="d-flex gap-4 p-4 bg-slate-50 rounded-xl border border-slate-100">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="poste_tresorerie" id="treso_caisse_create" value="Caisse" onchange="handleTresoChange('create'); updateJournalCode('create')">
                                            <label class="form-check-label font-bold text-slate-700" for="treso_caisse_create" onclick="event.stopPropagation()">Caisse</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="poste_tresorerie" id="treso_banque_create" value="Banque" onchange="handleTresoChange('create'); updateJournalCode('create')">
                                            <label class="form-check-label font-bold text-slate-700" for="treso_banque_create" onclick="event.stopPropagation()">Banque</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mt-2">
                                    <label class="form-label font-black text-slate-700">Autre</label>
                                    <input type="text" name="poste_tresorerie_autre" id="treso_autre_create" class="form-control border-slate-200 py-3 rounded-xl" placeholder="Saisir un autre libellé..." oninput="handleOtherInput('create'); updateJournalCode('create')">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-black text-slate-700">État de Rapprochement</label>
                                    <select name="rapprochement_sur" id="edit_rapprochement_sur" class="form-select border-slate-200 py-3 rounded-xl">
                                        <option value="">Aucun</option>
                                        <option value="Manuel">Manuel</option>
                                        <option value="Automatique">Automatique</option>
                                    </select>
                                </div>
                                <input type="hidden" name="compte_de_contrepartie" value="">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-slate-50 p-6 border-0">
                        <button type="button" class="btn btn-outline-secondary font-bold px-6 py-3 rounded-xl" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-emerald font-black px-8 py-3 rounded-xl shadow-lg shadow-emerald/20 text-white" style="background: #059669;">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Import Journals -->
    <div class="modal fade" id="modalImportJournals" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden">
                <form action="{{ route('admin.import.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="journals">
                    <input type="hidden" name="source" value="excel">
                    <div class="modal-header bg-emerald-900 p-6">
                        <h5 class="modal-title text-white font-black">Importer des Journaux</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-8">
                        <div class="bg-emerald-50 p-6 rounded-2xl mb-6 border border-emerald-100">
                            <h6 class="font-black text-emerald-800 mb-2"><i class="fa-solid fa-circle-info me-2"></i> Format Requis</h6>
                            <p class="text-sm text-emerald-600 mb-0">Colonnes obligatoires : <strong>code_journal</strong>, <strong>intitule</strong>, <strong>type</strong>.</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label font-black text-slate-700">Sélectionner le fichier (Excel/CSV)</label>
                            <input type="file" name="file" class="form-control border-slate-200 py-3 rounded-xl" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-slate-50 p-6 border-0 justify-content-between">
                        <button type="button" class="btn btn-warning font-bold px-6 py-3 rounded-xl" data-bs-toggle="modal" data-bs-target="#modalImportInstructions">
                            <i class="fa-solid fa-graduation-cap me-2"></i> Instructions
                        </button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary font-bold px-6 py-3 rounded-xl" data-bs-dismiss="modal">Fermer</button>
                            <button type="submit" class="btn btn-emerald font-black px-8 py-3 rounded-xl shadow-lg shadow-emerald/20 text-white" style="background: #059669;">Lancer l'importation</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Journal -->
    <div class="modal fade" id="modalEditJournal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden">
                <form id="editJournalForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-emerald-900 p-6">
                        <h5 class="modal-title text-white font-black">Modifier Journal Master</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-8">
                        <div class="row g-6">
                            <div class="col-md-6">
                                <label class="form-label font-black text-slate-700">Code Journal</label>
                                <input type="text" name="code_journal" id="edit_code_journal" class="form-control border-slate-200 py-3 rounded-xl shadow-none focus:border-emerald-500" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-black text-slate-700">Type</label>
                                <select name="type" id="edit_type_journal" class="form-select border-slate-200 py-3 rounded-xl focus:border-emerald-500" onchange="toggleTresorerieFields(this.value, 'edit')" required>
                                    <option value="Achats">Achats</option>
                                    <option value="Ventes">Ventes</option>
                                    <option value="Tresorerie">Trésorerie</option>
                                    <option value="Opérations Diverses">Opérations Diverses</option>
                                    <option value="Standard">Standard</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label font-black text-slate-700">Intitulé</label>
                                <input type="text" name="intitule" id="edit_intitule_journal" class="form-control border-slate-200 py-3 rounded-xl focus:border-emerald-500" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-black text-slate-700">Traitement Analytique</label>
                                <select name="traitement_analytique" id="edit_traitement_analytique" class="form-select border-slate-200 py-3 rounded-xl">
                                    <option value="non">Non</option>
                                    <option value="oui">Oui</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label font-black text-slate-700">Compte (Classe 5)</label>
                                <select name="compte_de_tresorerie" id="edit_compte_tresorerie" class="form-select border-slate-200 py-3 rounded-xl">
                                  <option value="">Sélectionner un compte...</option>
                                  @foreach($plansComptables as $plan)
                                  @if(str_starts_with($plan->numero_de_compte, '5'))
                                  <option value="{{ $plan->numero_de_compte }}">
                                    {{ $plan->numero_de_compte }} - {{ $plan->intitule }}
                                  </option>
                                  @endif
                                  @endforeach
                                </select>
                            </div>
                                <div class="col-12">
                                    <label class="form-label font-black text-slate-700">Type de Trésorerie</label>
                                    <div class="d-flex gap-4 p-4 bg-slate-50 rounded-xl border border-slate-100">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="poste_tresorerie" id="edit_treso_caisse" value="Caisse" onchange="handleTresoChange('edit')">
                                            <label class="form-check-label font-bold text-slate-700" for="edit_treso_caisse" onclick="event.stopPropagation()">Caisse</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="poste_tresorerie" id="edit_treso_banque" value="Banque" onchange="handleTresoChange('edit')">
                                            <label class="form-check-label font-bold text-slate-700" for="edit_treso_banque" onclick="event.stopPropagation()">Banque</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 mt-2">
                                    <label class="form-label font-black text-slate-700">Autre</label>
                                    <input type="text" name="poste_tresorerie_autre" id="edit_treso_autre" class="form-control border-slate-200 py-3 rounded-xl" placeholder="Saisir un autre libellé..." oninput="handleOtherInput('edit')">
                                </div>
                                <div class="col-12">
                                    <label class="form-label font-black text-slate-700">État de Rapprochement Bancaire</label>
                                    <select name="rapprochement_sur" id="edit_rapprochement_sur" class="form-select border-slate-200 py-3 rounded-xl">
                                        <option value="">-- Aucun --</option>
                                        <option value="Manuel">Manuel</option>
                                        <option value="Automatique">Automatique</option>
                                    </select>
                                </div>
                                <input type="hidden" name="compte_de_contrepartie" id="edit_compte_contrepartie" value="">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-slate-50 p-6 border-0">
                        <button type="button" class="btn btn-outline-secondary font-bold px-6 py-3 rounded-xl" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-emerald font-black px-8 py-3 rounded-xl shadow-lg shadow-emerald/20 text-white" style="background: #059669;">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Journal Settings -->
    <div class="modal fade" id="modalJournalSettings" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden">
                <form action="{{ route('admin.config.update_settings') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-slate-900 p-6">
                        <h5 class="modal-title text-white font-black">Configuration des Journaux</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-8">
                        <div class="row g-6">
                            <div class="col-md-6">
                                <label class="form-label font-black text-slate-700 uppercase text-xs">Longueur des Codes</label>
                                <select name="journal_code_digits" class="form-select border-slate-200 py-3 rounded-xl font-bold">
                                    @foreach([2,3,4,5,6] as $digit)
                                        <option value="{{ $digit }}" {{ ($mainCompany->journal_code_digits ?? 3) == $digit ? 'selected' : '' }}>{{ $digit }} caractères</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label font-black text-slate-700 uppercase text-xs">Type de Code</label>
                                <select name="journal_code_type" class="form-select border-slate-200 py-3 rounded-xl font-bold">
                                    <option value="alphanumeric" {{ ($mainCompany->journal_code_type ?? 'alphanumeric') == 'alphanumeric' ? 'selected' : '' }}>Alphanumérique</option>
                                </select>
                            </div>
                            <input type="hidden" name="accounting_system" value="{{ $mainCompany->accounting_system }}">
                            <input type="hidden" name="account_digits" value="{{ $mainCompany->account_digits }}">
                        </div>
                    </div>
                    <div class="modal-footer bg-slate-50 p-6 border-0">
                        <button type="button" class="btn btn-outline-secondary font-bold px-6 py-3 rounded-xl" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-primary font-black px-8 py-3 rounded-xl shadow-lg shadow-primary/20">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. STABILITÉ DES CHAMPS
        const unstableElements = [
            'edit_compte_tresorerie', 'edit_traitement_analytique', 'edit_rapprochement_sur',
            'create_compte_tresorerie', 'create_traitement_analytique', 'create_rapprochement_sur'
        ];
        unstableElements.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('click', (e) => e.stopPropagation());
                el.addEventListener('mousedown', (e) => e.stopPropagation());
            }
        });

        // 2. ÉCOUTEURS POUR LE GRISAGE (Banque/Caisse vs Autre)
        // Pour le mode Création
        ['treso_caisse_create', 'treso_banque_create'].forEach(id => {
            const el = document.getElementById(id);
            if(el) el.addEventListener('change', () => handleTresoChange('create'));
        });
        const autreCreate = document.getElementById('treso_autre_create');
        if(autreCreate) autreCreate.addEventListener('input', () => handleOtherInput('create'));

        // Pour le mode Édition
        ['edit_treso_caisse', 'edit_treso_banque'].forEach(id => {
            const el = document.getElementById(id);
            if(el) el.addEventListener('change', () => handleTresoChange('edit'));
        });
        const autreEdit = document.getElementById('edit_treso_autre');
        if(autreEdit) autreEdit.addEventListener('input', () => handleOtherInput('edit'));
    });

    // --- LOGIQUE DE GRISAGE (FIXED) ---
    function handleTresoChange(mode) {
        const caisse = document.getElementById(mode === 'edit' ? 'edit_treso_caisse' : 'treso_caisse_create');
        const banque = document.getElementById(mode === 'edit' ? 'edit_treso_banque' : 'treso_banque_create');
        const autre = document.getElementById(mode === 'edit' ? 'edit_treso_autre' : 'treso_autre_create');
        
        if (caisse.checked || banque.checked) {
            autre.value = '';
            autre.disabled = true;
            autre.classList.add('bg-slate-50', 'opacity-50');
        } else {
            autre.disabled = false;
            autre.classList.remove('bg-slate-50', 'opacity-50');
        }
        updateJournalCode(mode); // Relance la génération de code
    }

    function handleOtherInput(mode) {
        const caisse = document.getElementById(mode === 'edit' ? 'edit_treso_caisse' : 'treso_caisse_create');
        const banque = document.getElementById(mode === 'edit' ? 'edit_treso_banque' : 'treso_banque_create');
        const autre = document.getElementById(mode === 'edit' ? 'edit_treso_autre' : 'treso_autre_create');
        
        if (autre.value.trim() !== '') {
            caisse.checked = false;
            banque.checked = false;
            caisse.disabled = true;
            banque.disabled = true;
        } else {
            caisse.disabled = false;
            banque.disabled = false;
        }
        updateJournalCode(mode); // Relance la génération de code
    }

    // --- GÉNÉRATION DE CODE (FIXED) ---
    function updateJournalCode(mode) {
        const typeSelect = document.getElementById(mode === 'edit' ? 'edit_type_journal' : 'journal_type_select');
        const codeInput = document.getElementById(mode === 'edit' ? 'edit_code_journal' : 'create_code_journal');
        
        if (!typeSelect || !codeInput) return;

        let type = typeSelect.value;
        let prefix = '';
        
        if (type === 'Achats') prefix = 'ACH';
        else if (type === 'Ventes') prefix = 'VEN';
        else if (type === 'Opérations Diverses') prefix = 'OD';
        else if (['Tresorerie', 'Trésorerie', 'Banque', 'Caisse'].includes(type)) {
            const caisse = document.getElementById(mode === 'edit' ? 'edit_treso_caisse' : 'treso_caisse_create');
            const banque = document.getElementById(mode === 'edit' ? 'edit_treso_banque' : 'treso_banque_create');
            const autre = document.getElementById(mode === 'edit' ? 'edit_treso_autre' : 'treso_autre_create');
            
            if (caisse && caisse.checked) prefix = 'CAI';
            else if (banque && banque.checked) prefix = 'BQ';
            else if (autre && autre.value.trim() !== '') {
                prefix = autre.value.trim().substring(0, 3).toUpperCase();
            }
        }
        
        if (prefix) {
            fetch(`/admin/config/get-next-journal-code?prefix=${prefix}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) codeInput.value = data.code;
                })
                .catch(() => {
                    const digits = {{ $mainCompany->journal_code_digits ?? 3 }};
                    codeInput.value = prefix.padEnd(digits, '0').substring(0, digits);
                });
        }
    }

    // --- SYNC AFFICHAGE CHAMPS ---
    function toggleTresorerieFields(type, mode) {
        const container = document.getElementById(`tresorerie_fields_${mode}`);
        if (container) {
            if (['Tresorerie', 'Trésorerie', 'Banque', 'Caisse'].includes(type)) {
                container.classList.remove('d-none');
            } else {
                container.classList.add('d-none');
            }
        }
    }
</script>
    @include('components.import_instructions_journals')
</body>
</html>