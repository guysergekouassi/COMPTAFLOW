    @include('components.head')
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">


    <body>
        <!-- Layout wrapper -->
        <div class="layout-wrapper layout-content-navbar">
            <div class="layout-container">
                <!-- Menu -->

                @include('components.sidebar')
                <!-- / Menu -->

                <!-- Layout container -->
                <div class="layout-page">
                    <!-- Navbar -->

                    @include('components.header', ['page_title' => 'Plan <span class="text-gradient">Tiers</span> <span class="inline-block px-3 py-0.5 text-xs font-bold tracking-widest text-blue-700 uppercase bg-blue-50 rounded-full ml-3">Gestion des tiers</span>'])

                    <!-- / Navbar -->

                    <!-- Content wrapper -->

                    <div class="content-wrapper">
                        <!-- Version Marker for debugging -->
                        <span id="view-version" data-version="1.0.5" style="display:none;"></span>
                        <style>
                            .glass-card {
                                background: #ffffff;
                                border: 1px solid #e2e8f0;
                                border-radius: 16px;
                                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
                                transition: all 0.3s ease;
                            }

                            .filter-card:hover {
                                transform: translateY(-5px);
                                border-color: #3b82f6;
                                cursor: pointer;
                            }

                            .btn-action {
                                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
                            }

                            .btn-action:hover {
                                transform: translateY(-2px);
                                box-shadow: 0 4px 12px rgba(30, 64, 175, 0.2);
                            }

                            /* Styles spécifiques pour les boutons d'action */
                            .btn-action-edit {
                                transition: all 0.2s ease !important;
                            }
                            .btn-action-edit:hover {
                                background-color: #2563eb !important;
                                color: white !important;
                                border-color: #2563eb !important;
                                transform: translateY(-2px);
                                box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
                            }

                            .btn-action-delete {
                                transition: all 0.2s ease !important;
                            }
                            .btn-action-delete:hover {
                                background-color: #dc2626 !important;
                                color: white !important;
                                border-color: #dc2626 !important;
                                transform: translateY(-2px);
                                box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
                            }

                            .btn-action-view {
                                transition: all 0.2s ease !important;
                            }
                            .btn-action-view:hover {
                                background-color: #4f46e5 !important;
                                color: white !important;
                                border-color: #4f46e5 !important;
                                transform: translateY(-2px);
                                box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
                            }

                            .table-row {
                                transition: background-color 0.2s;
                            }

                            .table-row:hover {
                                background-color: #f1f5f9;
                            }

                            #tiersTable_wrapper .dataTables_length,
                            #tiersTable_wrapper .dataTables_filter {
                                display: none;
                            }

                            #tiersTable {
                                border-collapse: separate !important;
                                border-spacing: 0 !important;
                            }

                            #tiersTable thead th {
                                background-color: #f8fafc;
                                border-bottom: 1px solid #e2e8f0;
                            }

                            /* Nouveau Design Premium pour les Modaux */
                            .premium-modal-content {
                                background: rgba(255, 255, 255, 0.98);
                                backdrop-filter: blur(15px);
                                border: 1px solid rgba(255, 255, 255, 1);
                                border-radius: 20px;
                                box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.1);
                                font-family: 'Plus Jakarta Sans', sans-serif;
                                max-width: 400px;
                                margin: auto;
                                padding: 1.25rem !important;
                            }

                            .input-field-premium {
                                transition: all 0.2s ease;
                                border: 2px solid #f1f5f9 !important;
                                background-color: #f8fafc !important;
                                border-radius: 12px !important;
                                padding: 0.75rem 1rem !important;
                                font-size: 0.8rem !important;
                                font-weight: 600 !important;
                                color: #0f172a !important;
                                width: 100%;
                            }

                            .input-field-premium:focus {
                                border-color: #1e40af !important;
                                background-color: #ffffff !important;
                                box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.05) !important;
                                outline: none !important;
                            }

                            .text-blue-gradient-premium {
                                background: linear-gradient(to right, #1e40af, #3b82f6);
                                -webkit-background-clip: text;
                                -webkit-text-fill-color: transparent;
                                font-weight: 800;
                            }

                            .input-label-premium {
                                font-size: 0.7rem !important;
                                font-weight: 800 !important;
                                color: #64748b !important;
                                text-transform: uppercase !important;
                                letter-spacing: 0.05em !important;
                                margin-left: 0.1rem !important;
                                margin-bottom: 0.35rem !important;
                                display: block !important;
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
                            }

                            .btn-cancel-premium:hover {
                                background-color: #f8fafc !important;
                                color: #475569 !important;
                            }

                            select.input-field-premium {
                                appearance: none;
                                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E") !important;
                                background-repeat: no-repeat !important;
                                background-position: right 1rem center !important;
                                background-size: 1.2em !important;
                            }
                        </style>

                    <div class="container-xxl flex-grow-1 container-p-y">

                        <!-- Badge Section -->
                        <div class="text-center mb-8 -mt-4">
                            <p class="text-slate-500 font-medium max-w-xl mx-auto">
                                Organisez et gérez vos partenaires commerciaux (clients, fournisseurs) avec la structure COMPTAFLOW.
                            </p>
                        </div>

                        <!-- KPI Summary Cards (Style Plan Comptable) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
                            <div class="glass-card !p-6 flex items-center cursor-pointer filter-card filtre-tiers" data-type="all">
                                <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                                    <i class="bx bx-group text-2xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-500">Total Tiers</p>
                                    <h3 class="text-2xl font-bold text-slate-800">{{ $totalPlanTiers }}</h3>
                                </div>
                            </div>

                            @foreach ($tiersParType as $type => $count)
                                @php
                                    $color = 'blue';
                                    if (\Illuminate\Support\Str::contains(strtolower($type), 'client')) {
                                        $color = 'green';
                                    } elseif (\Illuminate\Support\Str::contains(strtolower($type), 'fournisseur')) {
                                        $color = 'indigo';
                                    }
                                @endphp
                                <div class="glass-card !p-6 flex items-center cursor-pointer filter-card filtre-tiers" data-type="{{ $type }}">
                                    <div class="p-3 rounded-full bg-{{ $color }}-100 text-{{ $color }}-600 mr-4">
                                        <i class="bx bx-user text-2xl"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-slate-500">{{ $type }}s</p>
                                        <h3 class="text-2xl font-bold text-slate-800">{{ $count }}</h3>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Notifications -->
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show mb-6" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Fermer"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show mb-6" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Fermer"></button>
                            </div>
                        @endif

                        <!-- Actions Bar (Identical to Plan Comptable) -->
                        <div class="flex justify-between items-center mb-8 w-full gap-4">
                            <!-- Left Group: Filter -->
                            <div class="flex items-center">
                                <button type="button" id="toggleFilterBtn" onclick="window.toggleAdvancedFilter()"
                                    class="btn-action flex items-center gap-2 px-6 py-3 bg-white border border-slate-200 rounded-2xl text-slate-700 font-semibold text-sm">
                                    <i class="fas fa-filter text-blue-600"></i>
                                    Filtrer
                                </button>
                            </div>

                            <!-- Right Group: Actions -->
                            <div class="flex flex-wrap items-center justify-end gap-3">
                                <button type="button" data-bs-toggle="modal" data-bs-target="#modalCenterCreate"
                                    class="btn-action flex items-center gap-2 px-6 py-3 bg-blue-700 text-white rounded-2xl font-semibold text-sm border-0 shadow-lg shadow-blue-200">
                                    <i class="fas fa-plus"></i>
                                    Nouveau Tiers
                                </button>
                            </div>
                        </div>

                        <!-- Advanced Filter Panel (Identical Layout) -->
                        <div id="advancedFilterPanel" style="display: none;" class="mb-8 transition-all duration-300">
                            <div class="glass-card p-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <!-- Filter Identifiant -->
                                    <div class="relative w-full">
                                        <input type="text" id="filter-id" placeholder="Filtrer par Identifiant..."
                                            class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-sm">
                                        <i class="fas fa-hashtag absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    </div>
                                    <!-- Filter Nom / Raison Sociale -->
                                    <div class="relative w-full">
                                        <input type="text" id="filter-intitule" placeholder="Filtrer par Nom / Raison Sociale..."
                                            class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-sm">
                                        <i class="fas fa-font absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    </div>
                                    <!-- Filter Catégorie -->
                                    <div class="relative w-full">
                                        <input type="text" id="filter-type" placeholder="Filtrer par Catégorie..."
                                            class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-sm">
                                        <i class="fas fa-tag absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    </div>
                                </div>
                                <div class="flex justify-end gap-3 mt-4">
                                    <button type="button" class="btn btn-secondary rounded-xl px-6 font-semibold" id="reset-filters" onclick="window.resetAdvancedFilters()">
                                        <i class="fas fa-undo me-2"></i>Réinitialiser
                                    </button>
                                    <button type="button" class="btn btn-primary rounded-xl px-6 font-semibold" id="apply-filters" onclick="window.applyAdvancedFilters()">
                                        <i class="fas fa-search me-2"></i>Rechercher
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Main Table Card -->
                        <div class="glass-card overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-100">
                                <h3 class="text-lg font-bold text-slate-800">Plan Tiers</h3>
                                <p class="text-sm text-slate-500">Liste des clients et fournisseurs enregistrés</p>
                            </div>
                            <div class="table-responsive">
                                <table class="w-full text-left border-collapse" id="tiersTable">
                                    <thead>
                                        <tr class="bg-slate-50/50 border-b border-slate-100">
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">
                                                Identifiant</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">
                                                Nom / Raison Sociale</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">
                                                Catégorie</th>
                                            <th
                                                class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider text-right">
                                                Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @forelse($tiers as $tier)
                                            <tr class="table-row">
                                                <td class="px-8 py-6">
                                                    <span class="font-mono text-base font-bold text-blue-700">{{ $tier->numero_de_tiers }}</span>
                                                </td>
                                                <td class="px-8 py-6">
                                                    <p class="font-semibold text-slate-800">{{ $tier->intitule }}</p>
                                                    <div class="text-xs text-slate-600 font-medium">
                                                        <div class="mb-1">
                                                            <span class="font-semibold">Compte rattaché :</span> 
                                                            @if($tier->compte)
                                                                {{ $tier->compte->numero_de_compte }} - {{ $tier->compte->intitule }}
                                                            @else
                                                                <span class="text-red-500">Aucun compte associé (ID: {{ $tier->compte_general ?? 'null' }})</span>
                                                            @endif
                                                        </div>
                                                        <div class="text-slate-400 italic">
                                                            Code tiers: {{ $tier->numero_de_tiers }}
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-8 py-6">
                                                    @php
                                                        $badgeColor = 'bg-slate-100 text-slate-700 border-slate-200';
                                                        if (\Illuminate\Support\Str::contains(strtolower($tier->type_de_tiers), 'client')) {
                                                            $badgeColor = 'bg-green-100 text-green-700 border-green-200';
                                                        } elseif (\Illuminate\Support\Str::contains(strtolower($tier->type_de_tiers), 'fournisseur')) {
                                                            $badgeColor = 'bg-blue-100 text-blue-700 border-blue-200';
                                                        }
                                                    @endphp
                                                    <span class="px-3 py-1 {{ $badgeColor }} rounded-lg text-[10px] font-black uppercase tracking-wider border text-nowrap">
                                                        {{ $tier->type_de_tiers }}
                                                    </span>
                                                </td>
                                                <td class="px-8 py-6 text-right">
                                                    <div class="flex justify-end gap-2">
                                                        <button type="button"
                                                            class="w-10 h-10 flex items-center justify-center rounded-xl border border-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white transition shadow-sm bg-white btn-action btn-action-edit"
                                                            title="Modifier" data-bs-toggle="modal"
                                                            data-bs-target="#modalCenterUpdate"
                                                            data-id="{{ $tier->id }}"
                                                            data-numero="{{ $tier->numero_de_tiers }}"
                                                            data-intitule="{{ $tier->intitule }}"
                                                            data-type="{{ $tier->type_de_tiers }}"
                                                            data-compte="{{ $tier->compte_general }}">
                                                            <i class="fas fa-user-edit"></i>
                                                        </button>

                                                        <button type="button"
                                                            class="w-10 h-10 flex items-center justify-center rounded-xl border border-red-100 text-red-600 hover:bg-red-600 hover:text-white transition shadow-sm bg-white btn-action btn-action-delete"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteConfirmationModalTiers"
                                                            data-id="{{ $tier->id }}"
                                                            data-name="{{ $tier->intitule }}">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>

                                                        <a href="{{ route('plan_tiers.show', ['plan_tier' => $tier->id]) }}?t={{ time() }}"
                                                            class="w-10 h-10 flex items-center justify-center rounded-xl border border-indigo-100 text-indigo-600 hover:bg-indigo-600 hover:text-white transition shadow-sm bg-white btn-action btn-action-view"
                                                            title="Voir">
                                                            <i class='bx bx-eye fs-5'></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="px-8 py-12 text-center">
                                                    <div class="flex flex-col items-center">
                                                        <i class="bx bx-folder-open text-5xl text-slate-200 mb-3"></i>
                                                        <p class="text-slate-500 font-medium">Aucun tiers trouvé</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Footer / Pagination Area (Managed by DataTable or Manual) -->
                            <div class="px-8 py-5 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-4" id="tableFooter">
                                <!-- Contenu injecté par JS pour DataTable -->
                            </div>
                        </div>

                    </div>

                                <!-- Nouveau Tiers (Premium Redesign) -->
                                <div class="modal fade" id="modalCenterCreate" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <form id="planTiersForm" method="POST" action="{{ route('plan_tiers.store') }}" class="w-full">
                                            @csrf
                                            <div class="modal-content premium-modal-content">
                                                
                                                <!-- En-tête -->
                                                <div class="text-center mb-6 position-relative">
                                                    <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                                    <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                                                        Nouveau <span class="text-blue-gradient-premium">Tiers</span>
                                                    </h1>
                                                    <div class="h-1 w-8 bg-blue-700 mx-auto mt-2 rounded-full"></div>
                                                </div>

                                                <div class="space-y-3">
                                                    
                                                    <!-- Catégorie (Type de tiers) -->
                                                    <div class="space-y-1">
                                                        <label class="input-label-premium">Catégorie</label>
                                                        <select id="type_de_tiers" name="type_de_tiers" class="input-field-premium" required>
                                                            <option value="" disabled selected>Sélectionner une catégorie</option>
                                                            @foreach (['Fournisseur', 'Client', 'Personnel', 'Impots', 'CNPS', 'Associé', 'Divers Tiers'] as $type)
                                                                <option value="{{ $type }}">{{ $type }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <!-- Compte de Rattachement (Compte général associé) -->
                                                    <div class="space-y-1">
                                                        <label class="input-label-premium">Compte de Rattachement</label>
                                                        <select id="compte_general" name="compte_general" class="input-field-premium" required>
                                                            <option value="" disabled selected>-- Sélectionnez un compte --</option>
                                                            {{-- Injecté par JS --}}
                                                        </select>
                                                    </div>

                                                    <!-- Numéro de tiers -->
                                                    <div class="space-y-1">
                                                        <label class="input-label-premium">Numéro de tiers</label>
                                                        <input type="text" id="numero_de_tiers" name="numero_de_tiers" 
                                                            class="input-field-premium opacity-75" placeholder="Généré automatiquement" required readonly>
                                                    </div>

                                                    <!-- Nom / Raison Sociale (Intitulé) -->
                                                    <div class="space-y-1">
                                                        <label class="input-label-premium">Nom / Raison Sociale</label>
                                                        <input type="text" id="intitule" name="intitule" 
                                                            class="input-field-premium" placeholder="Entrez le nom de l'entité" required>
                                                    </div>

                                                </div>

                                                <!-- Actions -->
                                                <div class="grid grid-cols-2 gap-4 pt-8">
                                                    <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">
                                                        Annuler
                                                    </button>
                                                    <button type="submit" class="btn-save-premium">
                                                        Enregistrer
                                                    </button>
                                                </div>

                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Modal Modification Plan Tiers (Premium Redesign) -->
                                <div class="modal fade" id="modalCenterUpdate" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <form method="POST" action="{{ route('plan_tiers.update', ['id' => '__ID__']) }}" id="updateTiersForm" class="w-full">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" id="update_id" name="id">
                                            
                                            <div class="modal-content premium-modal-content">
                                                <!-- En-tête -->
                                                <div class="text-center mb-6 position-relative">
                                                    <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                                    <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                                                        Modifier <span class="text-blue-gradient-premium">Tiers</span>
                                                    </h1>
                                                    <div class="h-1 w-8 bg-blue-700 mx-auto mt-2 rounded-full"></div>
                                                </div>

                                                <div class="space-y-3">
                                                    <!-- Catégorie (Type de tiers) -->
                                                    <div class="space-y-1">
                                                        <label class="input-label-premium">Catégorie</label>
                                                        <select id="update_type_de_tiers" name="type_de_tiers" class="input-field-premium" required>
                                                            <option value="" disabled selected>Sélectionner une catégorie</option>
                                                            @foreach (['Fournisseur', 'Client', 'Personnel', 'Impots', 'CNPS', 'Associé', 'Divers Tiers'] as $type)
                                                                <option value="{{ $type }}">{{ $type }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <!-- Compte de Rattachement (Compte général associé) -->
                                                    <div class="space-y-1">
                                                        <label class="input-label-premium">Compte de Rattachement</label>
                                                        <select id="update_compte" name="compte_general" class="input-field-premium" required>
                                                            <option value="" disabled selected>-- Sélectionnez un compte --</option>
                                                            @foreach ($comptesGeneraux as $compte)
                                                                <option value="{{ $compte->id }}" data-numero="{{ $compte->numero_de_compte }}">
                                                                    {{ $compte->numero_de_compte }} - {{ $compte->intitule }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <!-- Numéro de tiers -->
                                                    <div class="space-y-1">
                                                        <label class="input-label-premium">Numéro de tiers</label>
                                                        <input type="text" id="update_numero" name="numero_de_tiers" 
                                                            class="input-field-premium opacity-75" placeholder="Généré automatiquement" required readonly>
                                                    </div>

                                                    <!-- Nom / Raison Sociale (Intitulé) -->
                                                    <div class="space-y-1">
                                                        <label class="input-label-premium">Nom / Raison Sociale</label>
                                                        <input type="text" id="update_intitule" name="intitule" 
                                                            class="input-field-premium" required>
                                                    </div>
                                                </div>

                                                <!-- Actions -->
                                                <div class="grid grid-cols-2 gap-4 pt-6">
                                                    <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">
                                                        Annuler
                                                    </button>
                                                    <button type="submit" class="btn-save-premium">
                                                        Mettre à jour
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Modal de confirmation de suppression -->
                                <div class="modal fade" id="deleteConfirmationModalTiers" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-sm">
                                        <div class="modal-content premium-modal-content">
                                            <div class="text-center">
                                                <div class="w-16 h-16 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                                                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                                                </div>
                                                <h3 class="text-lg font-bold text-slate-900 mb-2">Supprimer ce tiers ?</h3>
                                                <p class="text-sm text-slate-500 mb-6">
                                                    Êtes-vous sûr de vouloir supprimer <span id="planToDeleteNameTiers" class="font-bold text-slate-900"></span> ?
                                                </p>
                                                
                                                <form method="POST" id="deletePlanFormTiers" class="grid grid-cols-2 gap-3">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn-cancel-premium !p-3" data-bs-dismiss="modal">Annuler</button>
                                                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-2xl transition shadow-lg shadow-red-100">
                                                        Supprimer
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <!-- Content wrapper -->
                        </div>
                        <!-- / Layout page -->
                    </div>

                    <!-- Overlay -->
                    <div class="layout-overlay layout-menu-toggle"></div>
                </div>
                <!-- / Layout wrapper -->

            @include('components.footer')

            <!-- Global URLs for JavaScript -->
            <script>
                const plan_tiers_ecrituresSaisisUrl = "{{ route('plan_tiers_ecritures') }}";
                const plan_tiersUpdateBaseUrl = "{{ route('plan_tiers.update', ['id' => '__ID__']) }}";
                const plan_tiersDeleteUrl = "{{ route('plan_tiers.destroy', ['id' => '__ID__']) }}";
                console.log("URLs définies:", {
                    plan_tiers_ecrituresSaisisUrl,
                    plan_tiersUpdateBaseUrl,
                    plan_tiersDeleteUrl
                });
            </script>

            <!-- Core JS Interactivity -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // --- 1. CONFIGURATION & DATA ---
                    const correspondances = @json($correspondances);
                    const getDernierNumeroUrl = "{{ url('/plan_tiers') }}";
                    const updateBaseUrl = "{{ route('plan_tiers.update', ['id' => '__ID__']) }}";
                    const deleteBaseUrl = "{{ route('plan_tiers.destroy', ['id' => '__ID__']) }}";

                    // --- 2. MODAL HELPER FUNCTIONS ---
                    const genererNumero = (numeroCompte, targetInput) => {
                        if (!numeroCompte) {
                            targetInput.value = '';
                            return;
                        }
                        targetInput.value = 'Calcul...';
                        const racine = numeroCompte.replace(/0+$/, '');
                        fetch(`${getDernierNumeroUrl}/${racine}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.numero) {
                                    targetInput.value = data.numero;
                                } else {
                                    targetInput.value = '';
                                }
                            })
                            .catch(error => {
                                console.error("[ERREUR] génération numéro:", error);
                                targetInput.value = '';
                            });
                    };

                    const updateAccountOptions = (typeSelect, accountSelect, targetNumeroInput, autoSelect = true) => {
                        const selectedType = typeSelect.value;
                        const comptes = correspondances[selectedType] || [];
                        
                        accountSelect.innerHTML = `<option value="" disabled selected>-- Sélectionnez un compte --</option>`;
                        comptes.forEach(compte => {
                            const option = document.createElement('option');
                            option.value = compte.id;
                            option.setAttribute('data-numero', compte.numero);
                            option.textContent = `${compte.numero} - ${compte.intitule}`;
                            accountSelect.appendChild(option);
                        });

                        if (autoSelect && comptes.length > 0) {
                            accountSelect.selectedIndex = 1;
                            genererNumero(comptes[0].numero, targetNumeroInput);
                        }
                    };

                    // --- 3. CREATE MODAL LOGIC ---
                    const createTypeTiers = document.getElementById('type_de_tiers');
                    const createCompteGeneral = document.getElementById('compte_general');
                    const createNumeroTiers = document.getElementById('numero_de_tiers');

                    createTypeTiers?.addEventListener('change', () => {
                        updateAccountOptions(createTypeTiers, createCompteGeneral, createNumeroTiers);
                    });

                    createCompteGeneral?.addEventListener('change', function() {
                        const numeroCompte = this.options[this.selectedIndex].getAttribute('data-numero');
                        if (numeroCompte) genererNumero(numeroCompte, createNumeroTiers);
                    });

                    // --- 4. UPDATE MODAL LOGIC ---
                    const updateModal = document.getElementById('modalCenterUpdate');
                    const updateForm = document.getElementById('updateTiersForm');
                    const updateTypeTiers = document.getElementById('update_type_de_tiers');
                    const updateCompteGeneral = document.getElementById('update_compte');
                    const updateNumeroTiers = document.getElementById('update_numero');

                    updateModal?.addEventListener('show.bs.modal', function(event) {
                        const btn = event.relatedTarget;
                        const id = btn.getAttribute('data-id');
                        const numero = btn.getAttribute('data-numero');
                        const intitule = btn.getAttribute('data-intitule');
                        const type = btn.getAttribute('data-type');
                        const compteId = btn.getAttribute('data-compte');

                        // Fill basic fields
                        document.getElementById('update_id').value = id;
                        document.getElementById('update_intitule').value = intitule;
                        updateNumeroTiers.value = numero;
                        updateTypeTiers.value = type;

                        // Update account list based on type
                        updateAccountOptions(updateTypeTiers, updateCompteGeneral, updateNumeroTiers, false);
                        updateCompteGeneral.value = compteId;

                        // Set form action
                        updateForm.action = updateBaseUrl.replace('__ID__', id);
                    });

                    updateTypeTiers?.addEventListener('change', () => {
                        updateAccountOptions(updateTypeTiers, updateCompteGeneral, updateNumeroTiers);
                    });

                    updateCompteGeneral?.addEventListener('change', function() {
                        const numeroCompte = this.options[this.selectedIndex].getAttribute('data-numero');
                        if (numeroCompte) genererNumero(numeroCompte, updateNumeroTiers);
                    });

                    // --- 5. DELETE MODAL LOGIC ---
                    const deleteModal = document.getElementById('deleteConfirmationModalTiers');
                    const deleteForm = document.getElementById('deletePlanFormTiers');
                    const deleteNameDisplay = document.getElementById('planToDeleteNameTiers');

                    deleteModal?.addEventListener('show.bs.modal', function(event) {
                        const btn = event.relatedTarget;
                        const id = btn.getAttribute('data-id');
                        const name = btn.getAttribute('data-name');
                        
                        deleteNameDisplay.textContent = name;
                        deleteForm.action = deleteBaseUrl.replace('__ID__', id);
                    });

                    // --- 6. DATATABLES INITIALIZATION ---
                    const initDataTable = () => {
                        if (typeof $ !== 'undefined' && $.fn.dataTable) {
                            window.tiersTable = $('#tiersTable').DataTable({
                                pageLength: 5,
                                lengthMenu: [5, 10, 25, 50],
                                language: {
                                    search: "Rechercher :",
                                    lengthMenu: "_MENU_",
                                    info: "Affichage de _START_ à _END_ sur _TOTAL_ tiers",
                                    paginate: { first: "Premier", last: "Dernier", next: "Suivant", previous: "Précédent" },
                                },
                                dom: '<"top">rt<"bottom"ip><"clear">',
                                drawCallback: function() {
                                    $('#tableFooter').html($('#tiersTable_paginate').detach());
                                    $('.dataTables_paginate').addClass('flex gap-2');
                                }
                            });

                            $('.filtre-tiers').on('click', function() {
                                const type = $(this).data('type');
                                $('.filtre-tiers').removeClass('ring-2 ring-blue-500 bg-blue-50/50');
                                $(this).addClass('ring-2 ring-blue-500 bg-blue-50/50');
                                if (type === 'all') {
                                    window.tiersTable.column(2).search('').draw();
                                } else {
                                    window.tiersTable.column(2).search('^' + type + '$', true, false).draw();
                                }
                            });

                        } else {
                            setTimeout(initDataTable, 100);
                        }
                    };

                    initDataTable();

                    // --- 7. ADVANCED FILTERS ---
                    window.applyAdvancedFilters = function() {
                        const idVal = document.getElementById('filter-id').value;
                        const intituleVal = document.getElementById('filter-intitule').value;
                        const typeVal = document.getElementById('filter-type').value;
                        if (window.tiersTable) {
                            window.tiersTable.column(0).search(idVal);
                            window.tiersTable.column(1).search(intituleVal);
                            window.tiersTable.column(2).search(typeVal);
                            window.tiersTable.draw();
                        }
                    };

                    window.resetAdvancedFilters = function() {
                        document.getElementById('filter-id').value = '';
                        document.getElementById('filter-intitule').value = '';
                        document.getElementById('filter-type').value = '';
                        if (window.tiersTable) {
                            window.tiersTable.column(0).search('');
                            window.tiersTable.column(1).search('');
                            window.tiersTable.column(2).search('');
                            window.tiersTable.draw();
                        }
                    };

                    window.toggleAdvancedFilter = function() {
                        const panel = document.getElementById('advancedFilterPanel');
                        if (panel) {
                            panel.style.display = (panel.style.display === 'none' || panel.style.display === '') ? 'block' : 'none';
                        }
                    };
                });
            </script>

            <!-- Plan Tiers JavaScript - Chargé après tout le reste -->
            <script src="{{ asset('js/plan_tiers.js') }}"></script>

            @if(session('reload'))
            <script>
                // Recharger la page après 1 seconde pour s'assurer que le message de succès est affiché
                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            </script>
            @endif
        </div>
    </div>
</body>

</html>
