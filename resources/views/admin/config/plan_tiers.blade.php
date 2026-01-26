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
                                                <span class="font-black text-indigo-700 fs-6 bg-indigo-50 px-3 py-1 rounded-lg">{{ $tier->numero_de_tiers }}</span>
                                            </td>
                                            <td class="py-6">
                                                <span class="font-bold text-slate-800">{{ $tier->intitule }}</span>
                                                <div class="text-xs text-slate-400">Rattaché au compte: {{ $tier->compte->numero_de_compte ?? 'Non lié' }}</div>
                                            </td>
                                            <td class="py-6">
                                                <span class="badge border border-indigo-200 text-indigo-600 bg-indigo-50 rounded-pill font-black px-4">{{ strtoupper($tier->type_de_tiers) }}</span>
                                            </td>
                                            <td class="pe-8 py-6 text-end">
                                                <div class="btn-group">
                                                    <button class="btn btn-icon btn-sm btn-outline-indigo border-0 rounded-circle" onclick="editTier({{ $tier->id }}, '{{ $tier->numero_de_tiers }}', '{{ addslashes($tier->intitule) }}', '{{ $tier->type_de_tiers }}', {{ $tier->compte_general ?? 'null' }})" title="Modifier"><i class="fa-solid fa-user-gear"></i></button>
                                                    <form action="{{ route('admin.config.delete_tier', $tier->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce tiers du modèle master ?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-icon btn-sm btn-outline-danger border-0 rounded-circle" title="Supprimer"><i class="fa-solid fa-user-minus"></i></button>
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
                                <select name="type_de_tiers" id="create_type_tiers" class="form-select border-slate-100 py-3 rounded-xl bg-slate-50 focus:bg-white transition-all font-bold" onchange="filterAccountsByType(this.value, 'create')" required>
                                    <option value="">Sélectionner une catégorie</option>
                                    <option value="Client">Client</option>
                                    <option value="Fournisseur">Fournisseur</option>
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label font-black text-slate-400 text-xs uppercase tracking-wider mb-2">Compte général</label>
                                <select name="compte_general" id="create_compte_general" class="form-select border-slate-100 py-3 rounded-xl bg-slate-50 focus:bg-white transition-all font-bold" onchange="generateTierNumber(this.value, 'create')" required>
                                    <option value="">-- Sélectionnez un compte --</option>
                                    @foreach($plansComptables as $acc)
                                        <option value="{{ $acc->id }}" data-numero="{{ $acc->numero_de_compte }}" class="acc-option" style="display:none;">{{ $acc->numero_de_compte }} - {{ $acc->intitule }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label font-black text-slate-400 text-xs uppercase tracking-wider mb-2">Numéro de tiers</label>
                                <input type="text" name="numero_de_tiers" id="create_numero_tiers" class="form-control border-slate-100 py-3 rounded-xl bg-slate-100 font-black text-primary" placeholder="Généré automatiquement" readonly required>
                            </div>
                            <div class="col-12">
                                <label class="form-label font-black text-slate-400 text-xs uppercase tracking-wider mb-2">Nom / Intitulé</label>
                                <input type="text" name="intitule" class="form-control border-slate-100 py-3 rounded-xl bg-slate-50 focus:bg-white transition-all font-bold" placeholder="Entrez le nom de l'entité" required>
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
                                    <option value="numeric" {{ ($mainCompany->tier_id_type ?? 'numeric') == 'numeric' ? 'selected' : '' }}>Numérique (Ex: 41100001)</option>
                                    <option value="alphanumeric" {{ ($mainCompany->tier_id_type ?? 'numeric') == 'alphanumeric' ? 'selected' : '' }}>Alpha-Numérique (Ex: 411JD001)</option>
                                </select>
                                <div class="mt-3 p-3 bg-blue-50 rounded-xl">
                                    <p class="text-[10px] text-blue-800 mb-0 leading-relaxed">
                                        <i class="fa-solid fa-lightbulb text-blue-500 me-1"></i>
                                        <strong>Logique :</strong> Racine (3 car.) + Nom (3 car.) + Séquence.
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
                                <select name="type_de_tiers" id="edit_type_tiers" class="form-select border-slate-100 py-3 rounded-xl bg-slate-50 focus:bg-white transition-all font-bold" onchange="filterAccountsByType(this.value, 'edit')" required>
                                    <option value="Client">Client</option>
                                    <option value="Fournisseur">Fournisseur</option>
                                    <option value="Autre">Autre</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label font-black text-slate-400 text-xs uppercase tracking-wider mb-2">Compte général</label>
                                <select name="compte_general" id="edit_compte_general" class="form-select border-slate-100 py-3 rounded-xl bg-slate-50 focus:bg-white transition-all font-bold" onchange="generateTierNumber(this.value, 'edit')" required>
                                    @foreach($plansComptables as $acc)
                                        <option value="{{ $acc->id }}" data-numero="{{ $acc->numero_de_compte }}" class="acc-option-edit">{{ $acc->numero_de_compte }} - {{ $acc->intitule }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label font-black text-slate-400 text-xs uppercase tracking-wider mb-2">Numéro de tiers</label>
                                <input type="text" name="numero_de_tiers" id="edit_numero_tiers" class="form-control border-slate-100 py-3 rounded-xl bg-slate-100 font-black text-primary" readonly required>
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
        function filterAccountsByType(type, mode) {
            const select = document.getElementById(mode === 'create' ? 'create_compte_general' : 'edit_compte_general');
            const options = select.querySelectorAll(mode === 'create' ? '.acc-option' : '.acc-option-edit');
            
            select.value = "";
            document.getElementById(mode === 'create' ? 'create_numero_tiers' : 'edit_numero_tiers').value = "";

            options.forEach(opt => {
                const num = opt.getAttribute('data-numero');
                if (type === 'Client' && num.startsWith('41')) {
                    opt.style.display = 'block';
                } else if (type === 'Fournisseur' && num.startsWith('40')) {
                    opt.style.display = 'block';
                } else if (type === 'Autre') {
                    opt.style.display = 'block';
                } else {
                    opt.style.display = 'none';
                }
            });
        }

        const tierDigitsConfig = {{ $mainCompany->tier_digits ?? 8 }};
        const tierIdType = "{{ $mainCompany->tier_id_type ?? 'numeric' }}";

        function generateTierNumber(accountId, mode) {
            const intituleField = (mode === 'create') ? document.querySelector('#modalCenterCreate input[name="intitule"]') : document.getElementById('edit_intitule_tier');
            const intitule = intituleField ? intituleField.value : '';
            
            if (!accountId) return;
            
            const field = document.getElementById(mode === 'create' ? 'create_numero_tiers' : 'edit_numero_tiers');
            field.value = "Génération...";

            $.get('{{ route("admin.config.get_next_tier") }}', { 
                plan_comptable_id: accountId,
                intitule: intitule 
            }, function(response) {
                if (response.success) {
                    field.value = response.next_id;
                } else {
                    field.value = "Erreur";
                }
            }).fail(function() {
                field.value = "Erreur AJAX";
            });
        }

        // Déclenchement automatique de la génération quand l'intitule change
        document.addEventListener('DOMContentLoaded', function() {
            const createIntitule = document.querySelector('#modalCenterCreate input[name="intitule"]');
            if (createIntitule) {
                ['blur', 'input'].forEach(evt => {
                    createIntitule.addEventListener(evt, function() {
                        const accountId = document.getElementById('create_compte_general').value;
                        if (accountId) generateTierNumber(accountId, 'create');
                    });
                });
            }
            
            const editIntitule = document.getElementById('edit_intitule_tier');
            if (editIntitule) {
                ['blur', 'input'].forEach(evt => {
                    editIntitule.addEventListener(evt, function() {
                        const accountId = document.getElementById('edit_compte_general').value;
                        if (accountId) generateTierNumber(accountId, 'edit');
                    });
                });
            }
        });

        function editTier(id, numero, intitule, type, compteId) {
            const form = document.getElementById('editTierForm');
            form.action = `/admin/config/update-tier/${id}`;
            
            document.getElementById('edit_type_tiers').value = type;
            filterAccountsByType(type, 'edit');
            
            document.getElementById('edit_compte_general').value = compteId || '';
            document.getElementById('edit_numero_tiers').value = numero;
            document.getElementById('edit_intitule_tier').value = intitule;
            
            new bootstrap.Modal(document.getElementById('modalEditTier')).show();
        }
    </script>
    @include('components.import_instructions_tiers')
</body>
</html>

