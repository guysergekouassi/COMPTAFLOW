@include('components.head')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap');

    body {
        background-color: #f1f5f9;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .master-header {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
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
        background: #4338ca;
        color: white;
        border: none;
        padding: 0.8rem 2rem;
        border-radius: 12px;
        font-weight: 700;
        transition: all 0.3s ease;
    }
    .btn-premium:hover {
        background: #3730a3;
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(67, 56, 202, 0.3);
    }

    .badge-tiers-indigo {
        background: #e0e7ff;
        color: #3730a3;
        border: 1px solid #c7d2fe;
        padding: 0.5rem 1rem;
        border-radius: 30px;
        font-weight: 700;
        font-size: 0.7rem;
        text-transform: uppercase;
    }

    /* Premium Modal Design */
    .premium-modal-content {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 1);
        border-radius: 20px;
        box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.1);
        width: 400px !important;
        max-width: 400px !important;
        margin: auto !important;
        padding: 1.25rem !important;
    }
    .premium-modal-dialog {
        width: 400px !important;
        max-width: 400px !important;
        margin: 1.75rem auto !important;
    }
    
    .btn-save-premium {
        padding: 0.75rem 1rem !important;
        border-radius: 12px !important;
        background-color: #1e40af !important;
        color: white !important;
        font-weight: 800 !important;
        font-size: 0.7rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        box-shadow: 0 4px 6px -1px rgba(30, 64, 175, 0.1) !important;
        transition: all 0.2s ease !important;
        border: none !important;
        width: 100%;
    }

    .btn-save-premium:hover {
        background-color: #1e3a8a !important;
        transform: translateY(-2px) !important;
    }

    .btn-cancel-premium {
        padding: 0.75rem 1rem !important;
        border-radius: 12px !important;
        color: #94a3b8 !important;
        font-weight: 700 !important;
        font-size: 0.7rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        transition: all 0.2s ease !important;
        border: none !important;
        background: transparent !important;
        width: 100%;
    }

    .btn-cancel-premium:hover {
        background-color: #f8fafc !important;
        color: #475569 !important;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Gestion <span class="text-indigo-600">Master</span> des Tiers'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <div class="master-header shadow-2xl">
                            <div class="row align-items-center">
                                <div class="col-lg-8">
                                    <span class="badge-tiers-indigo mb-4 d-inline-block">Partenaires du Groupe</span>
                                    <h1 class="font-black mb-2 tracking-tighter">Modèle de Plan Tiers</h1>
                                    <p class="opacity-70 font-medium">Définissez vos clients, fournisseurs et partenaires stratégiques au niveau groupe. Ils seront instantanément disponibles pour toutes vos comptabilités filiales.</p>
                                </div>
                                <div class="col-lg-4 text-end d-flex flex-column gap-2 border-start border-white/10 ps-6">
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-premium w-100" data-bs-toggle="modal" data-bs-target="#modalCenterCreate">
                                            <i class="fa-solid fa-user-plus me-2"></i> Nouveau Complice
                                        </button>
                                        <form action="{{ route('admin.config.reset_tiers') }}" method="POST" onsubmit="return confirm('Voulez-vous vider tout le répertoire des tiers master ?');">
                                            @csrf
                                            <button type="submit" class="btn btn-danger rounded-xl px-4 py-3" title="Annuler / Vider les tiers">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <button class="btn btn-outline-light w-100 border-2 font-black rounded-xl" data-bs-toggle="modal" data-bs-target="#modalImportTiers">
                                        <i class="fa-solid fa-file-import me-2"></i> Importer Excel/CSV
                                    </button>
                                    <button class="btn btn-outline-warning w-100 border-2 font-black rounded-xl mt-2" data-bs-toggle="modal" data-bs-target="#modalTierConfig">
                                        <i class="fa-solid fa-gears me-2"></i> Configurer Longueur (Actuelle: {{ $mainCompany->tier_digits ?? 8 }})
                                    </button>
                                </div>
                            </div>
                            <div class="position-absolute end-0 top-0 opacity-10" style="transform: translate(20%, -20%) rotate(-15deg);">
                                <i class="fa-solid fa-address-book fa-10x"></i>
                            </div>
                        </div>

                        <div class="glass-table-card overflow-hidden">
                            <div class="p-8 border-b border-slate-100 d-flex justify-content-between align-items-center bg-white">
                                <div>
                                    <h4 class="font-black mb-0">Répertoire des Tiers Master</h4>
                                    <p class="text-slate-400 text-sm mb-0">Modèles pré-configurés avec comptes de rattachement.</p>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-indigo-50/30">
                                        <tr>
                                            <th class="ps-8 py-5 text-uppercase text-xs font-black text-indigo-400">N° TIERS / IDENTIFIANT</th>
                                            <th class="py-5 text-uppercase text-xs font-black text-indigo-400">Nom / Intitulé</th>
                                            <th class="py-5 text-uppercase text-xs font-black text-indigo-400">Catégorie</th>
                                            <th class="pe-8 py-5 text-uppercase text-xs font-black text-indigo-400 text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white">
                                        @foreach($planTiers as $tier)
                                        <tr>
                                            <td class="ps-8 py-6">
                                                <div class="d-flex flex-column">
                                                    <div class="d-flex align-items-center gap-2 mb-1">
                                                        <i class="fa-solid fa-pen text-primary fs-small"></i>
                                                        <span class="font-black text-indigo-700 fs-6 bg-indigo-50 px-3 py-1 rounded-lg">{{ $tier->numero_de_tiers }}</span>
                                                    </div>
                                                    @if(!empty($tier->numero_original))
                                                        <div class="text-[10px] text-slate-400 font-medium italic d-flex align-items-center gap-1">
                                                            <i class="fa-solid fa-file-import text-[8px]"></i> Original: {{ $tier->numero_original }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="py-6">
                                                <span class="font-bold text-slate-800">{{ $tier->intitule }}</span>
                                                <div class="text-[10px] text-slate-400">Rattaché au compte: {{ $tier->compte->numero_de_compte ?? 'Non lié' }}</div>
                                                @if(!empty($tier->compte) && !empty($tier->compte->numero_original))
                                                    <div class="text-[10px] text-indigo-400 font-bold mt-1 uppercase tracking-tighter d-flex align-items-center gap-1">
                                                        <i class="fa-solid fa-link text-[8px]"></i> Origine: {{ $tier->compte->numero_original }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="py-6">
                                                @php
                                                    $num = $tier->numero_de_tiers;
                                                    $prefix = substr($num, 0, 2);
                                                    $catName = "AUTRE";
                                                    $cats = [
                                                        '40' => 'Fournisseurs',
                                                        '41' => 'Clients',
                                                        '42' => 'Salarié',
                                                        '43' => 'Organisme sociaux',
                                                        '44' => 'Impôt',
                                                        '45' => 'Organisme international',
                                                        '46' => 'Associés',
                                                        '47' => 'Divers',
                                                        '48' => 'Dettes sur Immo',
                                                        '49' => 'Dépréciation'
                                                    ];
                                                    $catName = $cats[$prefix] ?? $tier->type_de_tiers ?? "AUTRE";
                                                @endphp
                                                <span class="badge border border-indigo-200 text-indigo-600 bg-indigo-50 rounded-pill font-black px-4">{{ strtoupper($catName) }}</span>
                                            </td>
                                            <td class="pe-8 py-6 text-end">
                                                <div class="btn-group">
                                                    <button class="btn btn-icon btn-sm btn-outline-indigo border-0 rounded-circle" onclick="editTier({{ $tier->id }}, '{{ $tier->numero_de_tiers }}', '{{ addslashes($tier->intitule) }}', '{{ $tier->type_de_tiers }}', {{ $tier->compte_general ?? 'null' }})" title="Modifier"><i class="fa-solid fa-user-gear"></i></button>
                                                    <button type="button" class="btn btn-icon btn-sm btn-outline-danger border-0 rounded-circle" 
                                                        title="Supprimer"
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteConfirmationModal"
                                                        data-name="{{ $tier->intitule }}"
                                                        data-url="{{ route('admin.config.delete_tier', $tier->id) }}">
                                                        <i class="fa-solid fa-user-minus"></i>
                                                    </button>
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


    <!-- Modal Create Tiers -->
    <div class="modal fade" id="modalCenterCreate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden">
                <form action="{{ route('admin.config.store_tier') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-slate-900 p-6">
                        <h5 class="modal-title text-white font-black" id="modalCenterTitle">Nouveau Tiers Master</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-8">
                        <div class="row g-6">
                            <div class="col-12">
                                <label class="form-label font-black text-slate-400 text-xs uppercase tracking-wider mb-2">Catégorie</label>
                                <select name="type_de_tiers" id="create_type_tiers" class="form-select border-slate-100 py-3 rounded-xl bg-slate-50 focus:bg-white transition-all font-bold" onchange="handleCategoryChange(this, 'create')" required>
                                    <option value="">Sélectionner une catégorie</option>
                                    <option value="Fournisseurs" data-prefix="40">Fournisseurs</option>
                                    <option value="Clients" data-prefix="41">Clients</option>
                                    <option value="Salarié" data-prefix="42">Salarié</option>
                                    <option value="Organisme sociaux" data-prefix="43">Organisme sociaux</option>
                                    <option value="Impôt" data-prefix="44">Impôt</option>
                                    <option value="Organisme international" data-prefix="45">Organisme international</option>
                                    <option value="Associés" data-prefix="46">Associés</option>
                                    <option value="Divers" data-prefix="47">Divers</option>
                                    <option value="Dettes sur acquis. Immo" data-prefix="48">Dettes sur acquis. Immo (48)</option>
                                    <option value="Dépréciation" data-prefix="49">Dépréciation (49)</option>
                                    <option value="Autre" data-prefix="47" selected>Autre</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label font-black text-slate-400 text-xs uppercase tracking-wider mb-2">Numéro de tiers</label>
                                <input type="text" name="numero_de_tiers" id="create_numero_tiers" class="form-control border-slate-100 py-3 rounded-xl bg-slate-100 font-black text-primary" placeholder="Généré automatiquement" readonly required>
                            </div>
                            <div class="col-12">
                                <label class="form-label font-black text-slate-400 text-xs uppercase tracking-wider mb-2">Compte général (Optionnel)</label>
                                <div class="d-flex gap-2">
                                    <select name="compte_general" id="create_compte_general" class="form-select border-slate-100 py-3 rounded-xl bg-slate-50 focus:bg-white transition-all font-bold" style="flex: 1;">
                                        <option value="">-- Sélectionnez un compte --</option>
                                        @foreach($plansComptables as $acc)
                                            {{-- On stocke toutes les options, mais on ne les affiche pas toutes par défaut --}}
                                            <option value="{{ $acc->id }}" data-numero="{{ $acc->numero_de_compte }}" class="acc-option">{{ $acc->numero_de_compte }} - {{ $acc->intitule }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-outline-secondary rounded-xl border-slate-100 d-flex align-items-center justify-content-center" type="button" onclick="showAllAccounts('create')" title="Afficher tous les comptes de classe 4" style="width: 50px; flex-shrink: 0;">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label font-black text-slate-400 text-xs uppercase tracking-wider mb-2">Nom / Intitulé</label>
                                <input type="text" name="intitule" id="create_intitule_tier" class="form-control border-slate-100 py-3 rounded-xl bg-slate-50 focus:bg-white transition-all font-bold" placeholder="Entrez le nom de l'entité" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-slate-50 p-6 border-0">
                        <button type="button" class="btn btn-outline-secondary font-bold px-6 py-3 rounded-xl" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary font-black px-8 py-3 rounded-xl shadow-lg shadow-blue-500/20">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Import Tiers -->
    <div class="modal fade" id="modalImportTiers" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden">
                <form action="{{ route('admin.import.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="type" value="tiers">
                    <input type="hidden" name="source" value="excel">
                    <div class="modal-header bg-slate-900 p-6">
                        <h5 class="modal-title text-white font-black">Importer des Tiers</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-8">
                        <div class="bg-indigo-50 p-6 rounded-2xl mb-6 border border-indigo-100">
                            <h6 class="font-black text-indigo-800 mb-2"><i class="fa-solid fa-circle-info me-2"></i> Format Requis</h6>
                            <p class="text-sm text-indigo-600 mb-0">Colonnes obligatoires : <strong>numero_de_tiers</strong>, <strong>intitule</strong>, <strong>type_de_tiers</strong>.</p>
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
                            <button type="submit" class="btn btn-indigo font-black px-8 py-3 rounded-xl shadow-lg shadow-indigo/20 text-white" style="background: #4338ca;">Lancer l'importation</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal Tier Config -->
    <div class="modal fade" id="modalTierConfig" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden">
                <form action="{{ route('admin.config.update_settings') }}" method="POST">
                    @csrf
                    <input type="hidden" name="accounting_system" value="{{ $mainCompany->accounting_system }}">
                    <input type="hidden" name="account_digits" value="{{ $mainCompany->account_digits }}">
                    <div class="modal-header bg-slate-900 p-6">
                        <h5 class="modal-title text-white font-black">Configuration des Tiers</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-8">
                        <div class="row g-6">
                            <div class="col-12">
                                <label class="form-label font-black text-slate-400 text-xs uppercase tracking-wider mb-2">Longueur des Numéros</label>
                                <p class="text-[10px] text-slate-400 mb-3">Nombre total de caractères pour l'identifiant tiers.</p>
                                <select name="tier_digits" class="form-select border-slate-100 py-3 rounded-xl bg-slate-50 focus:bg-white transition-all font-bold">
                                    @for($i = 4; $i <= 15; $i++)
                                        <option value="{{ $i }}" {{ ($mainCompany->tier_digits ?? 8) == $i ? 'selected' : '' }}>{{ $i }} caractères</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="col-12 mt-4">
                                <label class="form-label font-black text-slate-400 text-xs uppercase tracking-wider mb-2">Type de Numérotation</label>
                                <p class="text-[10px] text-slate-400 mb-3">Choisissez comment vos tiers seront identifiés.</p>
                                <select name="tier_id_type" class="form-select border-slate-100 py-3 rounded-xl bg-slate-50 focus:bg-white transition-all font-bold">
                                    <option value="numeric" {{ ($mainCompany->tier_id_type ?? 'numeric') == 'numeric' ? 'selected' : '' }}>Numérique (Ex: 4100001)</option>
                                    <option value="alphanumeric" {{ ($mainCompany->tier_id_type ?? 'numeric') == 'alphanumeric' ? 'selected' : '' }}>Alpha-Numérique (Ex: 40AGM1)</option>
                                </select>
                                <div class="mt-3 p-3 bg-blue-50 rounded-xl">
                                    <p class="text-[10px] text-blue-800 mb-0 leading-relaxed">
                                        <i class="fa-solid fa-lightbulb text-blue-500 me-1"></i>
                                        <strong>Logique :</strong> Racine (2 car.) + Nom (3 car.) + Séquence.
                                    </p>
                                </div>
                            </div>
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
    <!-- Modal Edit Tier -->
    <div class="modal fade" id="modalEditTier" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden">
                <form id="editTierForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-slate-900 p-6">
                        <h5 class="modal-title text-white font-black">Modifier Tiers Master</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-8">
                        <div class="row g-6">
                            <div class="col-12">
                                <label class="form-label font-black text-slate-400 text-xs uppercase tracking-wider mb-2">Catégorie</label>
                                <select name="type_de_tiers" id="edit_type_tiers" class="form-select border-slate-100 py-3 rounded-xl bg-slate-50 focus:bg-white transition-all font-bold" onchange="handleCategoryChange(this, 'edit')" required>
                                    <option value="Fournisseurs" data-prefix="40">Fournisseurs</option>
                                    <option value="Clients" data-prefix="41">Clients</option>
                                    <option value="Salarié" data-prefix="42">Salarié</option>
                                    <option value="Organisme sociaux" data-prefix="43">Organisme sociaux</option>
                                    <option value="Impôt" data-prefix="44">Impôt</option>
                                    <option value="Organisme international" data-prefix="45">Organisme international</option>
                                    <option value="Associés" data-prefix="46">Associés</option>
                                    <option value="Divers" data-prefix="47">Divers</option>
                                    <option value="Dettes sur acquis. Immo" data-prefix="48">Dettes sur acquis. Immo (48)</option>
                                    <option value="Dépréciation" data-prefix="49">Dépréciation (49)</option>
                                    <option value="Autre" data-prefix="47">Autre</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label font-black text-slate-400 text-xs uppercase tracking-wider mb-2">Numéro de tiers</label>
                                <input type="text" name="numero_de_tiers" id="edit_numero_tiers" class="form-control border-slate-100 py-3 rounded-xl bg-slate-100 font-black text-slate-500" readonly required>
                                <p class="text-[10px] text-slate-400 mt-2"><i class="fa-solid fa-lock me-1"></i> Ce numéro est l'identifiant unique du tiers et ne peut être modifié.</p>
                            </div>
                            <div class="col-12">
                                <label class="form-label font-black text-slate-400 text-xs uppercase tracking-wider mb-2">Compte général (Optionnel)</label>
                                <div class="input-group">
                                    <select name="compte_general" id="edit_compte_general" class="form-select border-slate-100 py-3 rounded-s-xl bg-slate-50 focus:bg-white transition-all font-bold">
                                        <option value="">-- Sélectionnez un compte --</option>
                                        @foreach($plansComptables as $acc)
                                            <option value="{{ $acc->id }}" data-numero="{{ $acc->numero_de_compte }}" class="acc-option-edit">{{ $acc->numero_de_compte }} - {{ $acc->intitule }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-outline-secondary rounded-e-xl border-slate-100" type="button" onclick="showAllAccounts('edit')" title="Afficher tous les comptes de classe 4">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label font-black text-slate-400 text-xs uppercase tracking-wider mb-2">Nom / Intitulé</label>
                                <input type="text" name="intitule" id="edit_intitule_tier" class="form-control border-slate-100 py-3 rounded-xl bg-slate-50 focus:bg-white transition-all font-bold" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-slate-50 p-6 border-0">
                        <button type="button" class="btn btn-outline-secondary font-bold px-6 py-3 rounded-xl" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary font-black px-8 py-3 rounded-xl shadow-lg shadow-blue-500/20">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Stockage global des options pour les reconstruire
        let allAccountOptions = [];

        document.addEventListener('DOMContentLoaded', function() {
            // Initialiser la liste maître des options depuis le DOM
            const sourceSelect = document.getElementById('create_compte_general');
            if (sourceSelect) {
                const options = sourceSelect.querySelectorAll('option');
                options.forEach(opt => {
                    if (opt.value) { // Ignorer l'option par défaut vide
                        allAccountOptions.push({
                            value: opt.value,
                            text: opt.textContent,
                            numero: opt.getAttribute('data-numero')
                        });
                    }
                });
            }
        });

        function handleCategoryChange(selectElement, mode) {
            const category = selectElement.value;
            const prefix = selectElement.options[selectElement.selectedIndex].getAttribute('data-prefix');
            
            filterAccountsByPrefix(prefix, mode);
            if (prefix) {
                generateTierNumberFromPrefix(prefix, mode);
            }
        }

        function filterAccountsByPrefix(prefix, mode) {
            const select = document.getElementById(mode === 'create' ? 'create_compte_general' : 'edit_compte_general');
            if (!select) return;

            // Vider le select
            select.innerHTML = '<option value="">-- Sélectionnez un compte --</option>';

            // Filtrer et reconstruire
            if (allAccountOptions.length > 0) {
                let filtered = [];
                // Logique spéciale "Divers" ou préfixe standard
                if (prefix === '47' || !prefix) {
                     // Pour "Divers" ou sans préfixe, on pourrait vouloir tout afficher ou une logique spécifique
                     // Ici, si pas de préfixe, on ne filtre pas (tout afficher ?) ou on attend
                     // Si préfixe existe, on filtre.
                     if (prefix) {
                        filtered = allAccountOptions.filter(opt => opt.numero.startsWith(prefix));
                     }
                } else {
                    filtered = allAccountOptions.filter(opt => opt.numero.startsWith(prefix));
                }

                // Ajouter les options filtrées
                filtered.forEach(opt => {
                    const el = document.createElement('option');
                    el.value = opt.value;
                    el.textContent = opt.text;
                    el.setAttribute('data-numero', opt.numero);
                    select.appendChild(el);
                });
            }
        }

        function showAllAccounts(mode) {
            const select = document.getElementById(mode === 'create' ? 'create_compte_general' : 'edit_compte_general');
            if (!select) return;

            // Vider le select
            select.innerHTML = '<option value="">-- Tous les comptes de classe 4 --</option>';

            // Récupérer tous les comptes de classe 4
            const filtered = allAccountOptions.filter(opt => opt.numero.startsWith('4'));

            // Ajouter les options
            filtered.forEach(opt => {
                const el = document.createElement('option');
                el.value = opt.value;
                el.textContent = opt.text;
                el.setAttribute('data-numero', opt.numero);
                select.appendChild(el);
            });
        }


        function generateTierNumberFromPrefix(prefix, mode) {
            if (!prefix) return;
            
            const numField = document.getElementById(mode === 'create' ? 'create_numero_tiers' : 'edit_numero_tiers');
            const nameField = document.getElementById(mode === 'create' ? 'create_intitule_tier' : 'edit_intitule_tier');
            const intitule = nameField ? nameField.value : '';

            // Petit indicateur visuel
            numField.style.opacity = '0.5';

            $.get('{{ route("admin.config.get_next_tier") }}', { 
                prefix: prefix,
                intitule: intitule
            }, function(response) {
                if (response.success) {
                    numField.value = response.next_id;
                } else {
                    numField.value = "Erreur";
                }
                numField.style.opacity = '1';
            }).fail(function() {
                numField.value = "Erreur AJAX";
                numField.style.opacity = '1';
            });
        }

        // Écouteur pour la saisie du nom (Alphanumérique dynamique)
        document.addEventListener('DOMContentLoaded', function() {
            ['create', 'edit'].forEach(mode => {
                const nameInput = document.getElementById(mode + '_intitule_tier');
                const typeSelect = document.getElementById(mode + '_type_tiers');
                
                if (nameInput && typeSelect) {
                    let timeout = null;
                    nameInput.addEventListener('input', function() {
                        clearTimeout(timeout);
                        timeout = setTimeout(() => {
                            const prefix = typeSelect.options[typeSelect.selectedIndex].getAttribute('data-prefix');
                            if (prefix) {
                                generateTierNumberFromPrefix(prefix, mode);
                            }
                        }, 500); // Debounce 500ms
                    });
                }
            });
        });

        function generateTierNumber(accountId, mode) {
            // Cette fonction peut être conservée pour compatibilité si on change de compte manuellement,
            // mais le préfixe de la catégorie primera normalement.
            if (!accountId) return;
            const select = document.getElementById(mode === 'create' ? 'create_compte_general' : 'edit_compte_general');
            const num = select.options[select.selectedIndex].getAttribute('data-numero');
            const prefix = num.substring(0, 2);
            generateTierNumberFromPrefix(prefix, mode);
        }

        function editTier(id, numero, intitule, type, compteId) {
            const form = document.getElementById('editTierForm');
            form.action = `/admin/config/update-tier/${id}`;
            
            const typeSelect = document.getElementById('edit_type_tiers');
            // Trouver l'option par texte ou valeur si possible (attention à la casse et pluriels)
            for (let i = 0; i < typeSelect.options.length; i++) {
                if (typeSelect.options[i].value === type || typeSelect.options[i].text.includes(type)) {
                    typeSelect.selectedIndex = i;
                    break;
                }
            }

            const prefix = typeSelect.options[typeSelect.selectedIndex].getAttribute('data-prefix');
            filterAccountsByPrefix(prefix, 'edit');
            
            document.getElementById('edit_compte_general').value = compteId || '';
            document.getElementById('edit_numero_tiers').value = numero;
            document.getElementById('edit_intitule_tier').value = intitule;
            
            new bootstrap.Modal(document.getElementById('modalEditTier')).show();
        }

        // Gestion de la modale de suppression
        document.addEventListener('DOMContentLoaded', function() {
            const deleteModal = document.getElementById('deleteConfirmationModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function(event) {
                    const button = event.relatedTarget;
                    const name = button.getAttribute('data-name');
                    const url = button.getAttribute('data-url');
                    
                    const modalName = deleteModal.querySelector('#tierToDeleteName');
                    const modalForm = deleteModal.querySelector('#deleteTierForm');
                    
                    modalName.textContent = name;
                    modalForm.action = url;
                });
            }
        });
    </script>
    <!-- Modal de confirmation de suppression -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content premium-modal-content">
                <!-- Header -->
                <div class="text-center mb-6 position-relative">
                    <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    <div class="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-trash-alt text-red-600 text-xl"></i>
                    </div>
                    <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                        Confirmer la <span class="text-red-600">Suppression</span>
                    </h1>
                </div>

                <div class="text-center space-y-3 mb-8">
                    <p class="text-slate-500 text-sm font-medium leading-relaxed">
                        Êtes-vous sûr de vouloir supprimer ce tiers ? Cette action est irréversible.
                    </p>
                    <div id="tierToDeleteName" class="text-slate-900 font-bold"></div>
                </div>

                <!-- Actions -->
                <div class="grid grid-cols-2 gap-4">
                    <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">
                        Annuler
                    </button>
                    <form id="deleteTierForm" method="POST" action="" class="w-full">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-save-premium !bg-red-600 hover:!bg-red-700 shadow-red-200">
                            Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('components.import_instructions_tiers')
</body>
</html>

