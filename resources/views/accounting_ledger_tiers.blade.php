<!DOCTYPE html>

<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="../assets/"
    data-template="vertical-menu-template-free">

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

        .text-blue-gradient-premium {
            background: linear-gradient(to right, #1e40af, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
        }

        .table-premium thead th {
            background-color: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            color: #475569 !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            font-size: 0.75rem !important;
            padding: 1.25rem 2rem !important;
        }

        .table-row {
            transition: background-color 0.2s;
        }

        .table-row:hover {
            background-color: #f1f5f9;
        }

        .table-row td {
            padding: 1rem 2rem !important;
            vertical-align: middle;
            color: #1e293b;
            font-weight: 500;
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

        .btn-action {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 1rem;
            font-weight: 600;
            font-size: 0.875rem;
            border: 0;
        }

        .btn-premium-blue {
            background-color: #1e40af;
            color: white;
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.2);
        }

        .btn-premium-blue:hover {
            background-color: #1e3a8a;
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(30, 64, 175, 0.3);
            color: white;
        }

        .btn-premium-outline {
            background: white;
            border: 1px solid #e2e8f0;
            color: #475569;
        }

        .btn-premium-outline:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #1e293b;
            transform: translateY(-2px);
        }

        .premium-modal-content-wide {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.1);
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            padding: 1.5rem !important;
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

                @include('components.header', ['page_title' => 'Grand Livre des <span class="text-gradient">Tiers</span> <span class="inline-block px-3 py-0.5 text-xs font-bold tracking-widest text-blue-700 uppercase bg-blue-50 rounded-full ml-3">États comptables</span>'])

                <!-- / Navbar -->

                <!-- Content wrapper -->

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">

                        <div class="text-center mb-8 -mt-4">
                            <p class="text-slate-500 font-medium max-w-xl mx-auto">
                                Consultation et génération des grands livres auxiliaires détaillés par tiers.
                            </p>
                        </div>

                        {{-- Alertes premium --}}
                        @if (session('success'))
                            <div class="alert alert-success border-0 shadow-sm rounded-2xl flex items-center p-4 mb-6" style="background: #f0fdf4; color: #166534;">
                                <div class="bg-green-100 p-2 rounded-xl mr-4 flex items-center justify-center">
                                    <i class="bx bx-check-circle text-xl"></i>
                                </div>
                                <div class="font-bold flex-grow">{{ session('success') }}</div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger border-0 shadow-sm rounded-2xl flex items-center p-4 mb-6" style="background: #fef2f2; color: #991b1b;">
                                <div class="bg-red-100 p-2 rounded-xl mr-4 flex items-center justify-center">
                                    <i class="bx bx-error-circle text-xl"></i>
                                </div>
                                <div class="font-bold flex-grow">{{ session('error') }}</div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Actions Bar -->
                        <div class="flex justify-between items-center mb-8 w-full gap-4">
                            <div class="flex items-center">
                                <button type="button" id="toggleFilterBtn" onclick="window.toggleAdvancedFilter()"
                                    class="btn-action flex items-center gap-2 px-6 py-3 bg-white border border-slate-200 rounded-2xl text-slate-700 font-semibold text-sm">
                                    <i class="fas fa-filter text-blue-600"></i>
                                    Filtrer
                                </button>
                            </div>
                            <div class="flex flex-wrap items-center justify-end gap-3">
                                <button type="button" data-bs-toggle="modal" data-bs-target="#modalCenterCreate"
                                    class="btn-action flex items-center gap-2 px-6 py-3 bg-blue-700 text-white rounded-2xl font-semibold text-sm border-0 shadow-lg shadow-blue-200">
                                    <i class="fas fa-plus"></i>
                                    Générer Grand Livre
                                </button>
                            </div>
                        </div>

                        <!-- Advanced Filter Panel (Identical to Plan Tiers) -->
                        <div id="advancedFilterPanel" style="display: none;" class="mb-8 transition-all duration-300">
                            <div class="glass-card p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="relative w-full">
                                        <input type="text" id="filter-client" placeholder="Filtrer par informations..."
                                            class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-sm">
                                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    </div>
                                    <div class="relative w-full">
                                        <select id="filter-status" class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-sm appearance-none">
                                            <option value="">Tous les statuts</option>
                                            <option value="Active">Active</option>
                                            <option value="Inactive">Inactive</option>
                                        </select>
                                        <i class="fas fa-check-circle absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
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

                        <!-- Section table -->
                        <div class="glass-card overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-100">
                                <h3 class="text-lg font-bold text-slate-800">Grand Livre (Tiers)</h3>
                                <p class="text-sm text-slate-500">Grands livres par tiers et périodes générées</p>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-premium mb-0">
                                    <thead>
                                        <tr>
                                            <th>Période</th>
                                            <th>Date de génération</th>
                                            <th>Format</th>
                                            <th>De (Tiers)</th>
                                            <th>À (Tiers)</th>
                                            <th>Fichier</th>
                                            <th class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($grandLivre as $livre)
                                            <tr class="table-row">
                                                <td class="px-8 py-4">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 d-flex align-items-center justify-content-center">
                                                            <i class="bx bx-calendar-event"></i>
                                                        </div>
                                                        <span class="font-bold">
                                                            {{ \Carbon\Carbon::parse($livre->date_debut)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($livre->date_fin)->format('d/m/Y') }}
                                                        </span>
                                                    </div>
                                                </td>

                                                <td>
                                                    <div class="d-flex flex-column text-xs">
                                                        <span class="text-slate-500">{{ $livre->updated_at->format('d/m/Y') }}</span>
                                                        <span class="text-slate-400">{{ $livre->updated_at->format('H:i') }}</span>
                                                    </div>
                                                </td>

                                                <td>
                                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $livre->format == 'pdf' ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700' }} uppercase tracking-wider">
                                                        {{ $livre->format }}
                                                    </span>
                                                </td>

                                                <td>
                                                    <code class="text-blue-600 font-bold bg-blue-50 px-2 py-1 rounded text-xs">
                                                        {{ $livre->planTiers1->numero_de_tiers ?? 'N/A' }}
                                                    </code>
                                                </td>

                                                <td>
                                                    <code class="text-blue-600 font-bold bg-blue-50 px-2 py-1 rounded text-xs">
                                                        {{ $livre->planTiers2->numero_de_tiers ?? 'N/A' }}
                                                    </code>
                                                </td>

                                                <td>
                                                    <button class="btn btn-sm btn-premium-outline d-flex align-items-center gap-2 btn-preview-pdf"
                                                        data-pdf-url="{{ asset('grand_livres_tiers/' . $livre->grand_livre_tiers) }}"
                                                        data-bs-toggle="modal" data-bs-target="#pdfPreviewModal">
                                                        <i class="bx bxs-file-pdf text-red-500"></i>
                                                        <span>Voir PDF</span>
                                                    </button>
                                                </td>
                                                
                                                <td>
                                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                                        <a href="{{ asset('grand_livres_tiers/' . $livre->grand_livre_tiers) }}" download
                                                            class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 d-flex align-items-center justify-content-center hover:bg-blue-100 transition-colors"
                                                            title="Télécharger">
                                                            <i class='bx bx-download fs-5'></i>
                                                        </a>

                                                        <button type="button"
                                                            class="w-8 h-8 rounded-lg bg-red-50 text-red-600 d-flex align-items-center justify-content-center hover:bg-red-100 transition-colors border-0"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteConfirmationModal"
                                                            data-id="{{ $livre->id }}"
                                                            data-filename="{{ $livre->grand_livre_tiers }}">
                                                            <i class="bx bx-trash fs-5"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-8">
                                                    <div class="d-flex flex-column align-items-center gap-3">
                                                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-content-center">
                                                            <i class="bx bx-file text-slate-300 fs-1"></i>
                                                        </div>
                                                        <p class="text-slate-400 font-medium mb-0">Aucun Grand Livre trouvé.</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Modal Creation-->
                        <div class="modal fade" id="modalCenterCreate" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-xl" role="document" style="max-height: 90vh; margin: auto;">
                                <form method="POST" action="{{ route('accounting_ledger_tiers.generateGrandLivre') }}" id="grandLivreForm">
                                    @csrf
                                    <div class="modal-content premium-modal-content-wide" style="padding: 2rem; max-height: 90vh; overflow-y: auto;">
                                        <!-- Header -->
                                        <div class="text-center mb-5 position-relative">
                                            <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Fermer" style="top: -0.5rem; right: -0.5rem;"></button>
                                            <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); border-radius: 16px; box-shadow: 0 8px 16px rgba(30, 64, 175, 0.2);">
                                                <i class="bx bx-book-content" style="font-size: 28px; color: white;"></i>
                                            </div>
                                            <h1 class="text-xl font-extrabold tracking-tight text-slate-900 mb-2" style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.5rem;">
                                                Générer un <span class="text-blue-gradient-premium">Grand Livre Tiers</span>
                                            </h1>
                                            <p class="text-muted mb-0" style="font-size: 0.9rem; color: #64748b;">Sélectionnez les paramètres pour générer votre état auxiliaire détaillé</p>
                                        </div>

                                        <div class="modal-body" style="padding: 0;">
                                            <div class="row g-4">
                                                <!-- Période Card -->
                                                <div class="col-12">
                                                    <div class="card border-0 shadow-sm" style="border-radius: 16px; background: #f8fafc;">
                                                        <div class="card-body p-4">
                                                            <div class="d-flex align-items-center mb-3">
                                                                <div class="d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%); border-radius: 10px;">
                                                                    <i class="bx bx-calendar" style="font-size: 20px; color: white;"></i>
                                                                </div>
                                                                <h6 class="mb-0" style="font-weight: 700; font-size: 0.95rem; color: #1e293b;">Période de génération</h6>
                                                            </div>
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label for="date_debut_modal" class="input-label-premium">Date de début</label>
                                                                    <input type="date" id="date_debut_modal" name="date_debut" class="input-field-premium" 
                                                                        value="{{ $exerciceEnCours ? \Carbon\Carbon::parse($exerciceEnCours->date_debut)->format('Y-m-d') : '' }}"
                                                                        min="{{ $exerciceEnCours ? \Carbon\Carbon::parse($exerciceEnCours->date_debut)->format('Y-m-d') : '' }}"
                                                                        max="{{ $exerciceEnCours ? \Carbon\Carbon::parse($exerciceEnCours->date_fin)->format('Y-m-d') : '' }}"
                                                                        required />
                                                                    <div class="invalid-feedback">Veuillez renseigner la date de début.</div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="date_fin_modal" class="input-label-premium">Date de fin</label>
                                                                    <input type="date" id="date_fin_modal" name="date_fin" class="input-field-premium" 
                                                                        value="{{ $exerciceEnCours ? \Carbon\Carbon::parse($exerciceEnCours->date_fin)->format('Y-m-d') : '' }}"
                                                                        min="{{ $exerciceEnCours ? \Carbon\Carbon::parse($exerciceEnCours->date_debut)->format('Y-m-d') : '' }}"
                                                                        max="{{ $exerciceEnCours ? \Carbon\Carbon::parse($exerciceEnCours->date_fin)->format('Y-m-d') : '' }}"
                                                                        required />
                                                                    <div class="invalid-feedback">Veuillez renseigner la date de fin.</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Plage de Tiers Card -->
                                                <div class="col-12">
                                                    <div class="card border-0 shadow-sm" style="border-radius: 16px; background: #f8fafc;">
                                                        <div class="card-body p-4">
                                                            <div class="d-flex align-items-center mb-3">
                                                                <div class="d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%); border-radius: 10px;">
                                                                    <i class="bx bx-user-circle" style="font-size: 20px; color: white;"></i>
                                                                </div>
                                                                <h6 class="mb-0" style="font-weight: 700; font-size: 0.95rem; color: #1e293b;">Plage de Tiers</h6>
                                                            </div>
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label for="plan_tiers_id_1" class="input-label-premium">Tiers de début</label>
                                                                    <select id="plan_tiers_id_1" name="plan_tiers_id_1" class="selectpicker w-100 input-field-premium" data-width="100%" data-live-search="true" required>
                                                                        <option value="">-- Sélectionnez un compte --</option>
                                                                        @foreach ($PlanTiers as $plan)
                                                                            <option value="{{ $plan->id }}">
                                                                                {{ $plan->numero_de_tiers }} - {{ $plan->intitule }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    <div class="invalid-feedback">Veuillez sélectionner un compte.</div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="plan_tiers_id_2" class="input-label-premium">Tiers de fin</label>
                                                                    <select id="plan_tiers_id_2" name="plan_tiers_id_2" class="selectpicker w-100 input-field-premium" data-width="100%" data-live-search="true" required>
                                                                        <option value="">-- Sélectionnez un compte --</option>
                                                                        @foreach ($PlanTiers as $plan)
                                                                            <option value="{{ $plan->id }}">
                                                                                {{ $plan->numero_de_tiers }} - {{ $plan->intitule }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    <div class="invalid-feedback" id="compte2-error">Veuillez sélectionner un compte.</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Format Card -->
                                                <div class="col-12">
                                                    <div class="card border-0 shadow-sm" style="border-radius: 16px; background: #f8fafc;">
                                                        <div class="card-body p-4">
                                                            <div class="d-flex align-items-center mb-3">
                                                                <div class="d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%); border-radius: 10px;">
                                                                    <i class="bx bx-cog" style="font-size: 20px; color: white;"></i>
                                                                </div>
                                                                <h6 class="mb-0" style="font-weight: 700; font-size: 0.95rem; color: #1e293b;">Options de génération</h6>
                                                            </div>
                                                            <div class="row g-3">
                                                                <div class="col-12">
                                                            <div class="row g-3">
                                                                <div class="col-md-6">
                                                                    <label for="format_fichier" class="input-label-premium">Format de sortie</label>
                                                                    <select id="format_fichier" name="format_fichier" class="selectpicker w-100 input-field-premium" data-width="100%" data-live-search="false" required>
                                                                        <option value="pdf" selected>📄 PDF</option>
                                                                        <option value="excel">📊 EXCEL</option>
                                                                        <option value="csv">📋 CSV</option>
                                                                    </select>
                                                                    <div class="invalid-feedback">Veuillez sélectionner une option.</div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <label for="display_mode" class="input-label-premium">Affichage des numéros de tiers</label>
                                                                    <select id="display_mode" name="display_mode" class="selectpicker w-100 input-field-premium" data-width="100%" data-live-search="false" required>
                                                                        <option value="origine" selected>Compte d'origine</option>
                                                                        <option value="comptaflow">Compte ComptaFlow</option>
                                                                        <option value="both">Compte ComptaFlow et origine</option>
                                                                    </select>
                                                                    <small class="text-muted d-block mt-2" style="font-size: 0.65rem;">
                                                                        <strong>Origine :</strong> Numéros originaux importés<br>
                                                                        <strong>ComptaFlow :</strong> Numéros standardisés<br>
                                                                        <strong>Both :</strong> Les deux (origine en dessous)
                                                                    </small>
                                                                </div>
                                                            </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Footer Actions -->
                                        <div class="flex gap-4 mt-8 pt-6 border-t border-slate-100">
                                            <button type="button" class="btn-action flex-fill justify-center bg-slate-100 text-slate-600 hover:bg-slate-200" data-bs-dismiss="modal" id="btnCloseModal" >
                                                <i class="fas fa-times mr-2"></i>Annuler
                                            </button>
                                            <button type="button" class="btn-action flex-fill justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 border border-blue-200" id="btnPreview" >
                                                <i class="fas fa-eye mr-2"></i>Prévisualiser
                                            </button>
                                            <button type="submit" class="btn-action flex-fill justify-center bg-blue-700 text-white shadow-lg shadow-blue-200 hover:bg-blue-800" id="btnSaveModal" >
                                                <i class="fas fa-check mr-2"></i>Générer Le Grand Livre
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- previsualisation avant sauvegarde --}}
                        <div class="modal fade" id="modalPreviewPDF" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-xl" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Prévisualisation du Grand Livre</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Fermer"></button>
                                    </div>
                                    <div class="modal-body">
                                        <iframe id="pdfPreviewFrame" style="width:100%;height:80vh;"
                                            frameborder="0"></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>





                        <!-- Modal Creation plan update-->
                        <div class="modal fade" id="modalCenterUpdate" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalCenterTitle">
                                            Créer un plan
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Fermer"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label for="nameWithTitle" class="form-label">Nom</label>
                                                <input type="text" id="nameWithTitle" class="form-control"
                                                    placeholder="Entrer le nom" />
                                            </div>
                                            <div class="col-6">
                                                <label for="emailWithTitle" class="form-label">Email</label>
                                                <input type="email" id="emailWithTitle" class="form-control"
                                                    placeholder="xxx@xxx.xx" />
                                            </div>
                                            <div class="col-6">
                                                <label for="dobWithTitle" class="form-label">Date de naissance</label>
                                                <input type="date" id="dobWithTitle" class="form-control" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-label-secondary"
                                            data-bs-dismiss="modal">
                                            Fermer
                                        </button>
                                        <button type="button" class="btn btn-primary">
                                            Enregistrer
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>


                        {{-- modal pdf --}}
                        <div class="modal fade" id="pdfPreviewModal" tabindex="-1" aria-labelledby="pdfModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-xl" style="max-width:90%;">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="pdfModalLabel">Prévisualisation du PDF</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Fermer"></button>
                                    </div>
                                    <div class="modal-body" style="height: 80vh;">
                                        <iframe id="pdfViewer" src="" frameborder="0"
                                            style="width: 100%; height: 100%;"></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <!-- Modal Confirmation de suppression -->
                        <!-- Modal Confirmation de suppression -->
                        <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered" role="document">
                                <div class="modal-content premium-modal-content">
                                    <!-- Header -->
                                    <div class="text-center mb-6 position-relative">
                                        <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                        <div class="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-content mx-auto mb-4" style="width: 3rem; height: 3rem; display: flex; align-items: center; justify-content: center; background-color: #fef2f2; border-radius: 1rem; margin: 0 auto 1rem;">
                                            <i class="fas fa-trash-alt text-red-600 text-xl" style="color: #dc2626; font-size: 1.25rem;"></i>
                                        </div>
                                        <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                                            Confirmer la <span style="color: #dc2626; font-weight: 800;">Suppression</span>
                                        </h1>
                                    </div>

                                    <div class="text-center space-y-3 mb-8">
                                        <p class="text-slate-500 text-sm font-medium leading-relaxed">
                                            Êtes-vous sûr de vouloir supprimer ce Grand Livre ?<br>
                                            Cette action est irréversible.
                                        </p>
                                        <p class="text-slate-900 font-bold" id="fileNameToDelete"></p>
                                    </div>

                                    <!-- Actions -->
                                    <div class="grid grid-cols-2 gap-4">
                                        <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">
                                            Annuler
                                        </button>
                                        <form id="deleteForm" method="POST" class="w-full">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-save-premium" style="background-color: #dc2626 !important;">
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>
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

    <!-- Core JS -->

    @include('components.footer')

    <script>
        $(document).ready(function() {
            $('.selectpicker').selectpicker();
        });
    </script>

    <script>
        const accounting_ledger_tiersDeleteUrl = "{{ route('accounting_ledger_tiers.destroy', ['id' => '__ID__']) }}";
        const accounting_ledgerpreviewGrandLivreTiersUrl = "{{ route('accounting_ledger_tiers.previewGrandLivreTiers') }}";

        window.toggleAdvancedFilter = function() {
            const panel = document.getElementById('advancedFilterPanel');
            if (panel) {
                panel.style.display = (panel.style.display === 'none' || panel.style.display === '') ? 'block' : 'none';
            }
        };

        window.resetAdvancedFilters = function() {
            document.getElementById('filter-client').value = '';
            document.getElementById('filter-status').value = '';
            $(".table-premium tbody tr").show();
        };

        window.applyAdvancedFilters = function() {
            const clientVal = document.getElementById('filter-client').value.toLowerCase();
            const statusVal = document.getElementById('filter-status').value.toLowerCase();
            
            $(".table-premium tbody tr").filter(function() {
                const text = $(this).text().toLowerCase();
                const matchesClient = text.indexOf(clientVal) > -1;
                const matchesStatus = statusVal === "" || text.indexOf(statusVal) > -1;
                $(this).toggle(matchesClient && matchesStatus);
            });
        };
    </script>


    <script src="{{ asset('js/acc_ledger_tiers.js') }}"></script>


</body>

</html>
