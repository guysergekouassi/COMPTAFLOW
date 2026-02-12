    @include('components.head')
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

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
        .premium-modal-content-tiers {
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(15px) !important;
            border: 1px solid rgba(255, 255, 255, 1) !important;
            border-radius: 20px !important;
            box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.1) !important;
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            width: 400px !important;
            max-width: 400px !important;
            margin: auto !important;
            padding: 1.25rem !important;
            overflow: hidden !important;
        }

        .premium-modal-dialog {
            width: 400px !important;
            max-width: 400px !important;
            margin: 1.75rem auto !important;
        }

        @media (max-width: 576px) {
            .premium-modal-dialog {
                width: 95% !important;
                max-width: 95% !important;
                margin: 0.5rem auto !important;
            }
            .premium-modal-content-tiers {
                width: 100% !important;
                max-width: 100% !important;
            }
        }

        .input-field-premium {
            transition: all 0.2s ease;
            border: 2px solid #f1f5f9 !important;
            background-color: #f8fafc !important;
            border-radius: 16px !important;
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
                                <button type="button" id="toggleFilterBtn"
                                    class="btn-action flex items-center gap-2 px-6 py-3 bg-white border border-slate-200 rounded-2xl text-slate-700 font-semibold text-sm">
                                    <i class="fas fa-filter text-blue-600"></i>
                                    Filtrer
                                </button>
                            </div>

                            <!-- Right Group: Actions -->
                            <div class="flex flex-wrap items-center justify-end gap-3">
                                <!-- Bouton Charger depuis l'Admin [NOUVEAU] -->
                                @if (session('current_company_id') && session('current_company_id') != auth()->user()->company_id)
                                <button type="button" id="btnSyncAdminTiers"
                                    class="btn-action flex items-center gap-2 px-6 py-3 bg-indigo-50 border border-indigo-200 rounded-2xl text-indigo-700 font-semibold text-sm">
                                    <i class="fas fa-sync-alt"></i>
                                    Charger Modèle Admin
                                </button>
                                @endif

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
                                    <button type="button" class="btn btn-secondary rounded-xl px-6 font-semibold" id="reset-filters">
                                        <i class="fas fa-undo me-2"></i>Réinitialiser
                                    </button>
                                    <button type="button" class="btn btn-primary rounded-xl px-6 font-semibold" id="apply-filters">
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
                                                    <div class="flex flex-col">
                                                        <div class="flex items-center gap-2 mb-1">
                                                            <i class="fa-solid fa-pen text-blue-400 text-xs"></i>
                                                            <span class="font-mono text-base font-bold text-blue-700">{{ $tier->numero_de_tiers }}</span>
                                                        </div>
                                                        @if(!empty($tier->numero_original))
                                                            <div class="text-[10px] text-slate-400 font-medium italic d-flex items-center gap-1">
                                                                <i class="fa-solid fa-file-import text-[8px]"></i> Original: {{ $tier->numero_original }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-8 py-6">
                                                    <p class="font-semibold text-slate-800">{{ $tier->intitule }}</p>
                                                    <div class="text-xs text-slate-600 font-medium mt-1">
                                                        <div class="mb-1">
                                                            <span class="text-slate-400">Rattaché au compte:</span> 
                                                            @if($tier->compte)
                                                                <span class="font-bold">{{ $tier->compte->numero_de_compte }}</span>
                                                                <!-- Affichage du numéro original du COMPTE -->
                                                                @if(!empty($tier->compte->numero_original))
                                                                    <div class="text-[10px] text-indigo-400 font-bold mt-1 uppercase tracking-tighter flex items-center gap-1">
                                                                        <i class="fa-solid fa-link text-[8px]"></i> Origine: {{ $tier->compte->numero_original }}
                                                                    </div>
                                                                @endif
                                                            @else
                                                                <span class="text-red-500">Non lié</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-8 py-6">
                                                    @php
                                                        $prefix = substr($tier->numero_de_tiers, 0, 2);
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
                                                        
                                                        $badgeColor = 'bg-indigo-50 text-indigo-700 border-indigo-200';
                                                    @endphp
                                                    <span class="px-3 py-1 {{ $badgeColor }} rounded-lg text-[10px] font-black uppercase tracking-wider border text-nowrap">
                                                        {{ strtoupper($catName) }}
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
                                    <div class="modal-dialog modal-dialog-centered premium-modal-dialog" role="document">
                                        <form id="planTiersForm" method="POST" action="{{ route('plan_tiers.store') }}" class="w-full">
                                            @csrf
                                            <div class="modal-content premium-modal-content-tiers">
                                                
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
                                                        <select id="type_de_tiers" name="type_de_tiers" class="input-field-premium" onchange="handleCategoryChange(this, 'create')" required>
                                                            <option value="" disabled selected>Sélectionner une catégorie</option>
                                                            <option value="Fournisseur" data-prefix="40">Fournisseur</option>
                                                            <option value="Client" data-prefix="41">Client</option>
                                                            <option value="Personnel" data-prefix="42">Personnel</option>
                                                            <option value="CNPS" data-prefix="43">Organisme sociaux / CNPS</option>
                                                            <option value="Impots" data-prefix="44">Impôt</option>
                                                            <option value="Organisme international" data-prefix="45">Organisme international</option>
                                                            <option value="Associé" data-prefix="46">Associé</option>
                                                            <option value="Divers Tiers" data-prefix="47">Divers Tiers</option>
                                                        </select>
                                                    </div>

                                                    <!-- Numéro de tiers -->
                                                    <div class="space-y-1">
                                                        <label class="input-label-premium">Numéro de tiers</label>
                                                        <input type="text" id="numero_de_tiers" name="numero_de_tiers" 
                                                            class="input-field-premium opacity-75" placeholder="Généré automatiquement" required readonly>
                                                    </div>

                                                    <!-- Compte de Rattachement (Compte général associé) -->
                                                    <div class="space-y-1">
                                                        <label class="input-label-premium">Compte de Rattachement (Optionnel)</label>
                                                        <div class="flex gap-2">
                                                            <select id="compte_general" name="compte_general" class="input-field-premium flex-grow">
                                                                <option value="" disabled selected>-- Sélectionnez un compte --</option>
                                                                {{-- Injecté par JS --}}
                                                            </select>
                                                            <button class="px-3 py-2 border rounded-xl bg-slate-50 text-slate-500" type="button" onclick="showAllAccounts('create')" title="Afficher tous les comptes de classe 4">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
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
                                    <div class="modal-dialog modal-dialog-centered premium-modal-dialog" role="document">
                                        <form method="POST" action="{{ route('plan_tiers.update', ['id' => '__ID__']) }}" id="updateTiersForm" class="w-full">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" id="update_id" name="id">
                                            
                                            <div class="modal-content premium-modal-content-tiers">
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
                                                        <select id="update_type_de_tiers" name="type_de_tiers" class="input-field-premium" onchange="handleCategoryChange(this, 'edit')" required>
                                                            <option value="" disabled selected>Sélectionner une catégorie</option>
                                                            <option value="Fournisseur" data-prefix="40">Fournisseur</option>
                                                            <option value="Client" data-prefix="41">Client</option>
                                                            <option value="Personnel" data-prefix="42">Personnel</option>
                                                            <option value="CNPS" data-prefix="43">Organisme sociaux / CNPS</option>
                                                            <option value="Impots" data-prefix="44">Impôt</option>
                                                            <option value="Organisme international" data-prefix="45">Organisme international</option>
                                                            <option value="Associé" data-prefix="46">Associé</option>
                                                            <option value="Divers Tiers" data-prefix="47">Divers Tiers</option>
                                                        </select>
                                                    </div>

                                                    <!-- Numéro de tiers -->
                                                    <div class="space-y-1">
                                                        <label class="input-label-premium">Numéro de tiers</label>
                                                        <input type="text" id="update_numero" name="numero_de_tiers" 
                                                            class="input-field-premium opacity-75" placeholder="Généré automatiquement" required readonly>
                                                    </div>

                                                    <!-- Compte de Rattachement (Compte général associé) -->
                                                    <div class="space-y-1">
                                                        <label class="input-label-premium">Compte de Rattachement (Optionnel)</label>
                                                        <div class="flex gap-2">
                                                            <select id="update_compte" name="compte_general" class="input-field-premium flex-grow">
                                                                <option value="" disabled selected>-- Sélectionnez un compte --</option>
                                                                @foreach ($comptesGeneraux as $compte)
                                                                    <option value="{{ $compte->id }}" data-numero="{{ $compte->numero_de_compte }}" class="update-acc-option">
                                                                        {{ $compte->numero_de_compte }} - {{ $compte->intitule }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <button class="px-3 py-2 border rounded-xl bg-slate-50 text-slate-500" type="button" onclick="showAllAccounts('edit')" title="Afficher tous les comptes de classe 4">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
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
                                    <div class="modal-dialog modal-dialog-centered premium-modal-dialog" style="max-width: 400px !important; width: 400px !important;">
                                        <div class="modal-content premium-modal-content-tiers">
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
                                                <p class="text-slate-900 font-bold" id="planToDeleteNameTiers"></p>
                                            </div>

                                            <!-- Actions -->
                                            <div class="grid grid-cols-2 gap-4">
                                                <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">
                                                    Annuler
                                                </button>
                                                <form method="POST" id="deletePlanFormTiers" class="w-full">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-save-premium !bg-red-600 hover:!bg-red-700 shadow-red-200" style="background-color: #dc2626 !important;">
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
                (function($) {
                    "use strict";

                    // --- FONCTIONS GLOBALES (Exposées sur window) ---
                    window.genererNumero = (prefix, targetInput) => {
                        if (!prefix) { targetInput.value = ''; return; }
                        targetInput.value = 'Calcul...';
                        fetch(`/plan_tiers/${prefix}`)
                            .then(r => r.json())
                            .then(data => { targetInput.value = data.numero || ''; })
                            .catch(e => { console.error(e); targetInput.value = ''; });
                    };

                    window.handleCategoryChange = (selectElement, mode) => {
                        const prefix = selectElement.options[selectElement.selectedIndex].getAttribute('data-prefix');
                        const accountSelect = document.getElementById(mode === 'create' ? 'compte_general' : 'update_compte');
                        const targetInput = document.getElementById(mode === 'create' ? 'numero_de_tiers' : 'update_numero');
                        
                        window.updateAccountOptions(selectElement, accountSelect, targetInput, prefix);
                        if (prefix) {
                            window.genererNumero(prefix, targetInput);
                        }
                    };

                    window.updateAccountOptions = (typeSelect, accountSelect, targetInput, prefix) => {
                        if (!typeSelect || !accountSelect) return;
                        const selectedType = typeSelect.value;
                        // On utilise les correspondances si dispo, sinon on filtre par préfixe
                        const comptes = (window.correspondancesTiers && window.correspondancesTiers[selectedType]) 
                                        ? window.correspondancesTiers[selectedType] 
                                        : [];
                        
                        accountSelect.innerHTML = `<option value="" disabled selected>-- Sélectionnez un compte --</option>`;
                        
                        // Si on a des correspondances chargées par le controlleur
                        if (comptes.length > 0) {
                            comptes.forEach(c => {
                                const opt = document.createElement('option');
                                opt.value = c.id;
                                opt.setAttribute('data-numero', c.numero);
                                opt.textContent = `${c.numero} - ${c.intitule}`;
                                accountSelect.appendChild(opt);
                            });
                        } else if (prefix) {
                            // Sinon on peut essayer de filtrer manuellement si on a accès à tous les comptes (pour l'edit par exemple)
                            // Mais normalement correspondancesTiers est complet.
                        }
                    };

                    window.showAllAccounts = (mode) => {
                        const accountSelect = document.getElementById(mode === 'create' ? 'compte_general' : 'update_compte');
                        // Récupérer tous les comptes de classe 4 depuis les correspondances
                        accountSelect.innerHTML = `<option value="" disabled selected>-- Sélectionnez un compte --</option>`;
                        Object.values(window.correspondancesTiers).flat().forEach(c => {
                            if (c.numero.startsWith('4')) {
                                const opt = document.createElement('option');
                                opt.value = c.id;
                                opt.setAttribute('data-numero', c.numero);
                                opt.textContent = `${c.numero} - ${c.intitule}`;
                                accountSelect.appendChild(opt);
                            }
                        });
                    };

                    window.toggleAdvancedFilter = function() {
                        $('#advancedFilterPanel').slideToggle();
                    };

                    // --- INITIALISATION SÉCURISÉE ---
                    function initDataTable(retryCount = 0) {
                        if (typeof $.fn.DataTable !== 'function') {
                            if (retryCount < 5) {
                                console.warn(`[PlanTiers] DataTable non trouvé, tentative ${retryCount + 1}/5...`);
                                setTimeout(() => initDataTable(retryCount + 1), 200);
                            } else {
                                console.error("[PlanTiers] ÉCHEC CRITIQUE : DataTables n'est pas chargé.");
                            }
                            return;
                        }

                        console.log("[PlanTiers] Initialisation DataTables...");
                        const table = $('#tiersTable').DataTable({
                            destroy: true,
                            pageLength: 5,
                            order: [], 
                            language: {
                                zeroRecords: "Aucun tiers trouvé",
                                info: "Affichage de _START_ à _END_ sur _TOTAL_ tiers",
                                paginate: { next: "Suivant", previous: "Précédent" }
                            },
                            dom: 't',
                            drawCallback: function() { updatePagination(this.api()); }
                        });

                        window.tiersDataTable = table;
                        setupEventListeners(table);
                    }

                    function setupEventListeners(table) {
                        // Filtres KPI
                        $(document).off('click', '.filtre-tiers').on('click', '.filtre-tiers', function() {
                            const type = $(this).data('type');
                            $('.filtre-tiers').removeClass('filter-active ring-2 ring-blue-500 bg-blue-50/50');
                            $(this).addClass('filter-active ring-2 ring-blue-500 bg-blue-50/50');
                            
                            if (type === 'all') table.column(2).search('').draw();
                            else table.column(2).search(type).draw();
                        });

                        // Filtres avancés
                        $(document).off('click', '#apply-filters').on('click', '#apply-filters', function() {
                            table.column(0).search($('#filter-id').val().trim());
                            table.column(1).search($('#filter-intitule').val().trim());
                            table.column(2).search($('#filter-type').val().trim());
                            table.draw();
                        });

                        $(document).off('click', '#reset-filters').on('click', '#reset-filters', function() {
                            $('#filter-id, #filter-intitule, #filter-type').val('');
                            table.search('').columns().search('').draw();
                        });

                        // Navigation UI
                        $(document).off('click', '#toggleFilterBtn').on('click', '#toggleFilterBtn', function() {
                            $('#advancedFilterPanel').slideToggle();
                        });
                    }

                    function updatePagination(table) {
                        const info = table.page.info();
                        const $footer = $('#tableFooter');
                        if (info.recordsDisplay > 0) {
                            $footer.html(`
                                <div class="flex items-center justify-between w-full mt-4">
                                    <div class="text-sm text-slate-500 italic">Affichage de ${info.start + 1} à ${info.end} sur ${info.recordsDisplay} tiers</div>
                                    <div class="flex gap-2">
                                        <button class="px-4 py-2 border rounded-xl bg-white text-slate-400 hover:text-blue-700 hover:border-blue-200 transition ${info.page === 0 ? 'opacity-50 cursor-not-allowed' : ''}" onclick="window.tiersDataTable.page('previous').draw('page')" ${info.page === 0 ? 'disabled' : ''}>
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        <button class="px-4 py-2 bg-blue-600 text-white rounded-xl font-bold">${info.page + 1}</button>
                                        <button class="px-4 py-2 border rounded-xl bg-white text-slate-400 hover:text-blue-700 hover:border-blue-200 transition ${info.page >= info.pages - 1 ? 'opacity-50 cursor-not-allowed' : ''}" onclick="window.tiersDataTable.page('next').draw('page')" ${info.page >= info.pages - 1 ? 'disabled' : ''}>
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </div>
                                </div>
                            `);
                        } else {
                            $footer.html('<div class="text-center text-slate-500 py-6 font-medium italic">Aucun tiers ne correspond à votre recherche</div>');
                        }
                    }

                    // --- LIFE CYCLE ---
                    $(function() {
                        window.correspondancesTiers = @json($correspondances);
                        initDataTable();

                        $('#type_de_tiers').on('change', function() {
                            window.updateAccountOptions(this, document.getElementById('compte_general'), document.getElementById('numero_de_tiers'));
                        });

                        $('#compte_general').on('change', function() {
                            const num = $(this).find(':selected').data('numero');
                            if (num) window.genererNumero(num, document.getElementById('numero_de_tiers'));
                        });

                        // Modal logic
                        const updateModal = document.getElementById('modalCenterUpdate');
                        if(updateModal) {
                            updateModal.addEventListener('show.bs.modal', function(event) {
                                const btn = $(event.relatedTarget);
                                const id = btn.data('id');
                                const type = btn.data('type');
                                const compteId = btn.data('compte');

                                $('#update_id').val(id);
                                $('#update_intitule').val(btn.data('intitule'));
                                $('#update_numero').val(btn.data('numero'));
                                
                                const typeSelect = document.getElementById('update_type_de_tiers');
                                for (let i = 0; i < typeSelect.options.length; i++) {
                                    if (typeSelect.options[i].value === type || typeSelect.options[i].text.includes(type)) {
                                        typeSelect.selectedIndex = i;
                                        break;
                                    }
                                }

                                const prefix = typeSelect.options[typeSelect.selectedIndex] ? typeSelect.options[typeSelect.selectedIndex].getAttribute('data-prefix') : null;
                                window.updateAccountOptions(typeSelect, document.getElementById('update_compte'), document.getElementById('update_numero'), prefix);
                                
                                $('#update_compte').val(compteId);
                                $('#updateTiersForm').attr('action', plan_tiersUpdateBaseUrl.replace('__ID__', id));
                            });
                        }

                        const deleteModal = document.getElementById('deleteConfirmationModalTiers');
                        if(deleteModal) {
                            deleteModal.addEventListener('show.bs.modal', function(event) {
                                const btn = $(event.relatedTarget);
                                $('#planToDeleteNameTiers').text(btn.data('name'));
                                $('#deletePlanFormTiers').attr('action', plan_tiersDeleteUrl.replace('__ID__', btn.data('id')));
                            });
                        }
                    });

                })(jQuery);
            </script>

            <!-- Plan Tiers JavaScript - Chargé après tout le reste -->
            <!-- App Scripts consolidated in Blade to avoid conflicts -->

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
