<!DOCTYPE html>

<html lang="en" class="layout-menu-fixed layout-compact" data-assets-path="../assets/"
    data-template="vertical-menu-template-free">

@include('components.head')

<style>
    /* Premium Modal Styles */
    .premium-modal-content {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 1);
        border-radius: 20px;
        box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.1);
        font-family: 'Plus Jakarta Sans', sans-serif !important;
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
        box-sizing: border-box;
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

    /* Premium Modal Content Wide for complex forms */
    .premium-modal-content-wide {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 1);
        border-radius: 20px;
        box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.1);
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        max-width: 90%;
        margin: auto;
        padding: 1.5rem !important;
    }

    /* Correction spécifique pour bootstrap-select dans le modal */
    .bootstrap-select .dropdown-menu {
        background-color: #ffffff !important;
        opacity: 1 !important;
        visibility: visible !important;
        z-index: 10001 !important;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
    }

    .bootstrap-select .dropdown-item {
        padding: 0.6rem 1rem !important;
        font-size: 0.85rem !important;
        font-weight: 500 !important;
        color: #475569 !important;
    }

    .bootstrap-select .dropdown-item:hover {
        background-color: #f1f5f9 !important;
        color: #1e40af !important;
    }

    .bootstrap-select .inner {
        max-height: 250px !important;
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
                @include('components.header', ['page_title' => 'Grand <span class="text-gradient">Livre</span> <span class="inline-block px-3 py-0.5 text-xs font-bold tracking-widest text-blue-700 uppercase bg-blue-50 rounded-full ml-3">États comptables</span>'])

                <div class="content-wrapper">
                    <style>
                        .glass-card {
                            background: #ffffff;
                            border: 1px solid #e2e8f0;
                            border-radius: 16px;
                            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
                            transition: all 0.3s ease;
                        }

                        .btn-action {
                            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
                        }

                        .btn-action:hover {
                            transform: translateY(-2px);
                            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.2);
                        }

                        .table-row {
                            transition: background-color 0.2s;
                        }

                        .table-row:hover {
                            background-color: #f1f5f9;
                        }

                        .table-premium {
                            border-collapse: separate !important;
                            border-spacing: 0 !important;
                        }

                        .table-premium thead th {
                            background-color: #f8fafc;
                            border-bottom: 1px solid #e2e8f0;
                            padding: 1.25rem 2rem !important;
                            font-size: 0.875rem !important;
                            font-weight: 700 !important;
                            color: #64748b !important;
                            text-transform: uppercase !important;
                            letter-spacing: 0.05em !important;
                        }
                        
                        .table-premium tbody td {
                            padding: 1.5rem 2rem !important;
                            vertical-align: middle !important;
                        }

                        /* ── Scrollbars tableau ── */
                        .scrollable-table-wrap {
                            overflow-x: auto;
                            overflow-y: auto;
                            max-height: 65vh;
                            border-radius: 0 0 16px 16px;
                        }
                        .scrollable-table-wrap::-webkit-scrollbar {
                            width: 8px;
                            height: 8px;
                        }
                        .scrollable-table-wrap::-webkit-scrollbar-track {
                            background: #f1f5f9;
                            border-radius: 4px;
                        }
                        .scrollable-table-wrap::-webkit-scrollbar-thumb {
                            background: #cbd5e1;
                            border-radius: 4px;
                        }
                        .scrollable-table-wrap::-webkit-scrollbar-thumb:hover {
                            background: #94a3b8;
                        }
                        .scrollable-table-wrap thead th {
                            position: sticky;
                            top: 0;
                            z-index: 2;
                            background: #f8fafc;
                        }

                        /* ── Scrollbars formulaires / modales ── */
                        .modal-content::-webkit-scrollbar,
                        .modal-body::-webkit-scrollbar {
                            width: 8px;
                            height: 8px;
                        }
                        .modal-content::-webkit-scrollbar-track,
                        .modal-body::-webkit-scrollbar-track {
                            background: #f1f5f9;
                            border-radius: 4px;
                        }
                        .modal-content::-webkit-scrollbar-thumb,
                        .modal-body::-webkit-scrollbar-thumb {
                            background: #cbd5e1;
                            border-radius: 4px;
                        }
                        .modal-content::-webkit-scrollbar-thumb:hover,
                        .modal-body::-webkit-scrollbar-thumb:hover {
                            background: #94a3b8;
                        }
                    </style>

                    <div class="container-fluid flex-grow-1 container-p-y">
                        
                        <!-- Badge Section -->

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show mb-6 rounded-2xl shadow-sm border-0 bg-green-50 text-green-800" role="alert">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-check-circle text-xl"></i>
                                    <span class="font-medium">{{ session('success') }}</span>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show mb-6 rounded-2xl shadow-sm border-0 bg-red-50 text-red-800" role="alert">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-exclamation-circle text-xl"></i>
                                    <span class="font-medium">{{ session('error') }}</span>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif

                        <!-- Actions Bar (même modèle que Plan Tiers) -->
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
                                <button type="button" data-bs-toggle="modal" data-bs-target="#modalCenterCreate"
                                    class="btn-action flex items-center gap-2 px-6 py-3 bg-blue-700 text-white rounded-2xl font-semibold text-sm border-0 shadow-lg shadow-blue-200">
                                    <i class="fas fa-file-export"></i>
                                    Générer Grand Livre
                                </button>
                            </div>
                        </div>

                        <!-- Advanced Filter Panel (même modèle que Plan Tiers) -->
                        <div id="advancedFilterPanel" style="display: none;" class="mb-8 transition-all duration-300">
                            <div class="glass-card p-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div class="relative w-full">
                                        <input type="text" id="filter-client" placeholder="Filtrer par description..."
                                            class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-sm">
                                        <i class="fas fa-font absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    </div>
                                    <div class="relative w-full">
                                        <select id="filter-status"
                                            class="w-full pl-10 pr-4 py-3 bg-white border border-slate-200 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition shadow-sm">
                                            <option value="">Tous les statuts</option>
                                            <option value="Active">Actif</option>
                                            <option value="Inactive">Inactif</option>
                                        </select>
                                        <i class="fas fa-tag absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    </div>
                                    <div class="flex justify-end gap-3">
                                        <button type="button" class="btn btn-secondary rounded-xl px-6 font-semibold" id="reset-filters">
                                            <i class="fas fa-undo me-2"></i>Réinitialiser
                                        </button>
                                        <button type="button" class="btn btn-primary rounded-xl px-6 font-semibold" id="apply-filters">
                                            <i class="fas fa-search me-2"></i>Rechercher
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Main Table Card -->
                        <div class="glass-card overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-100">
                                <h3 class="text-lg font-bold text-slate-800">Grand Livre</h3>
                                <p class="text-sm text-slate-500">Historique des mouvements par période et comptes</p>
                            </div>
                            <div class="scrollable-table-wrap">
                                <table class="w-full text-left border-collapse table-premium">
                                    <thead>
                                        <tr>
                                            <th>Période</th>
                                            <th>Date de génération</th>
                                            <th>Format</th>
                                            <th>De Compte</th>
                                            <th>À Compte</th>
                                            <th>Fichier</th>
                                            <th class="text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @forelse ($grandLivre as $livre)
                                            <tr class="table-row">
                                                <td class="px-8 py-4 font-medium text-slate-700">
                                                    {{ \Carbon\Carbon::parse($livre->date_debut)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($livre->date_fin)->format('d/m/Y') }}
                                                </td>
                                                <td class="px-8 py-4 text-slate-600">
                                                    {{ \Carbon\Carbon::parse($livre->updated_at)->format('d/m/Y H:i') }}
                                                </td>
                                                <td class="px-8 py-4">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 uppercase tracking-wide">
                                                        {{ strtoupper($livre->format) }}
                                                    </span>
                                                </td>
                                                <td class="px-8 py-4 font-mono text-sm text-slate-600">
                                                    {{ $livre->PlanComptable1->numero_de_compte ?? '-' }}
                                                </td>
                                                <td class="px-8 py-4 font-mono text-sm text-slate-600">
                                                    {{ $livre->PlanComptable2->numero_de_compte ?? '-' }}
                                                </td>
                                                <td class="px-8 py-4">
                                                    <a href="{{ asset('grand_livres/' . $livre->grand_livre) }}" 
                                                       class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-800 font-semibold transition-colors"
                                                       target="_blank">
                                                        <i class="fas fa-file-pdf text-xl"></i>
                                                        <span class="text-sm underline decoration-blue-200 underline-offset-4 hover:decoration-blue-600">Télécharger</span>
                                                    </a>
                                                </td>
                                                <td class="px-8 py-4 text-right">
                                                    <form action="{{ route('accounting_ledger.destroy', $livre->id) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce Grand Livre ?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg border border-red-100 text-red-500 hover:bg-red-50 hover:text-red-600 transition shadow-sm bg-white ml-auto">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="px-8 py-12 text-center text-slate-500">
                                                    <div class="flex flex-col items-center gap-3">
                                                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-slate-300">
                                                            <i class="bx bx-book-open text-3xl"></i>
                                                        </div>
                                                        <span class="font-medium">Aucun Grand Livre généré pour le moment.</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
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

    <!-- Modals moved to body end for better positioning -->
    <div class="modal fade" id="modalCenterCreate" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document" style="max-height: 90vh; margin: auto;">
            <form method="POST" action="{{ route('accounting_ledger.generateGrandLivre') }}" id="grandLivreForm">
                @csrf
                <div class="modal-content premium-modal-content-wide" style="padding: 2rem; max-height: 90vh; overflow-y: auto;">
                    <!-- Header -->
                    <div class="text-center mb-5 position-relative">
                        <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Fermer" style="top: -0.5rem; right: -0.5rem;"></button>
                        <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); border-radius: 16px; box-shadow: 0 8px 16px rgba(30, 64, 175, 0.2);">
                            <i class="bx bx-book-open" style="font-size: 28px; color: white;"></i>
                        </div>
                        <h1 class="text-xl font-extrabold tracking-tight text-slate-900 mb-2" style="font-size: 1.75rem; font-weight: 800; margin-bottom: 0.5rem;">
                            Générer un <span class="text-blue-gradient-premium">Grand Livre</span>
                        </h1>
                        <p class="text-muted mb-0" style="font-size: 0.9rem; color: #64748b;">Sélectionnez les paramètres pour générer votre rapport</p>
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
                                                <label for="date_debut" class="input-label-premium">Date de début</label>
                                                <input type="date" id="date_debut" name="date_debut" class="input-field-premium" 
                                                    value="{{ $exerciceEnCours ? \Carbon\Carbon::parse($exerciceEnCours->date_debut)->format('Y-m-d') : '' }}"
                                                    min="{{ $exerciceEnCours ? \Carbon\Carbon::parse($exerciceEnCours->date_debut)->format('Y-m-d') : '' }}"
                                                    max="{{ $exerciceEnCours ? \Carbon\Carbon::parse($exerciceEnCours->date_fin)->format('Y-m-d') : '' }}"
                                                    required />
                                                <div class="invalid-feedback">Veuillez renseigner la date de début.</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="date_fin" class="input-label-premium">Date de fin</label>
                                                <input type="date" id="date_fin" name="date_fin" class="input-field-premium" 
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

                            <!-- Comptes Généraux Card -->
                            <div class="col-12">
                                <div class="card border-0 shadow-sm" style="border-radius: 16px; background: #f8fafc;">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px; background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%); border-radius: 10px;">
                                                <i class="bx bx-list-ul" style="font-size: 20px; color: white;"></i>
                                            </div>
                                            <h6 class="mb-0" style="font-weight: 700; font-size: 0.95rem; color: #1e293b;">Plage de comptes</h6>
                                            <div class="ms-auto">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="selectAllAccounts">
                                                    <label class="form-check-label text-xs font-bold text-slate-500 uppercase" for="selectAllAccounts">Tout sélectionner</label>
                                                </div>
                                            </div>
                                        </div>
                                         <div class="row g-3">
                                             <div class="col-md-6">
                                                 <label for="plan_comptable_id_1" class="input-label-premium">Compte de début ({{ count($PlanComptable) }})</label>
                                                 <select id="plan_comptable_id_1" name="plan_comptable_id_1" class="select2-enable w-100 input-field-premium" required>
                                                     <option value="">-- Sélectionnez un compte --</option>
                                                     @foreach ($PlanComptable as $plan)
                                                         <option value="{{ $plan->id }}">{{ $plan->numero_de_compte }} - {{ trim($plan->intitule) }}</option>
                                                     @endforeach
                                                 </select>
                                                 <div class="invalid-feedback">Veuillez sélectionner un compte.</div>
                                             </div>
                                             <div class="col-md-6">
                                                 <label for="plan_comptable_id_2" class="input-label-premium">Compte de fin ({{ count($PlanComptable) }})</label>
                                                 <select id="plan_comptable_id_2" name="plan_comptable_id_2" class="select2-enable w-100 input-field-premium" required>
                                                     <option value="">-- Sélectionnez un compte --</option>
                                                     @foreach ($PlanComptable as $plan)
                                                         <option value="{{ $plan->id }}">{{ $plan->numero_de_compte }} - {{ trim($plan->intitule) }}</option>
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
                                                <i class="bx bx-file" style="font-size: 20px; color: white;"></i>
                                            </div>
                                            <h6 class="mb-0" style="font-weight: 700; font-size: 0.95rem; color: #1e293b;">Format de sortie</h6>
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="format_fichier" class="input-label-premium">Type de fichier</label>
                                                <select id="format_fichier" name="format_fichier" class="selectpicker w-100 input-field-premium" data-width="100%" data-live-search="false" required>
                                                    <option value="pdf" selected>📄 PDF</option>
                                                    <option value="excel">📊 EXCEL</option>
                                                    <option value="csv">📋 CSV</option>
                                                </select>
                                                <div class="invalid-feedback">Veuillez sélectionner une option.</div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="display_mode" class="input-label-premium">Affichage des numéros de compte</label>
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

                    <!-- Footer Actions -->
                    <div class="d-flex gap-3 mt-4 pt-4" style="border-top: 1px solid #e2e8f0;">
                        <button type="button" class="btn btn-cancel-premium flex-fill" data-bs-dismiss="modal" id="btnCloseModal" style="padding: 0.875rem 1.5rem; font-size: 0.875rem;">
                            <i class="bx bx-x me-2"></i>Annuler
                        </button>
                        <button type="button" class="btn btn-info flex-fill" id="btnPreview" style="padding: 0.875rem 1.5rem; border-radius: 12px; font-weight: 700; font-size: 0.875rem; text-transform: none; letter-spacing: normal;">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="spinnerPreview"></span>
                            <span id="loaderText" class="d-none ms-2">Veuillez patienter...</span>
                            <span id="btnPreviewLabel"><i class="bx bx-show me-2"></i>Prévisualiser</span>
                        </button>
                        <button type="submit" class="btn-save-premium flex-fill" id="btnSaveModal" style="padding: 0.875rem 1.5rem; font-size: 0.875rem;">
                            <i class="bx bx-check me-2"></i>Générer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- previsualisation avant sauvegarde --}}
    <div class="modal fade" id="modalPreviewPDF" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen" role="document">
            <div class="modal-content overflow-hidden" style="border-radius: 0;">
                <div class="modal-header border-b border-slate-200 px-6 py-4">
                    <h5 class="modal-title font-extrabold text-xl">Prévisualisation du Grand Livre</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body p-0" style="background: #525659;">
                    <iframe id="pdfPreviewFrame" style="width:100%; height: calc(100vh - 70px);" frameborder="0"></iframe>
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="nameWithTitle" class="form-label">Nom</label>
                            <input type="text" id="nameWithTitle" class="form-control" placeholder="Entrer le nom" />
                        </div>
                        <div class="col-6">
                            <label for="emailWithTitle" class="form-label">Email</label>
                            <input type="email" id="emailWithTitle" class="form-control" placeholder="xxx@xxx.xx" />
                        </div>
                        <div class="col-6">
                            <label for="dobWithTitle" class="form-label">Date de naissance</label>
                            <input type="date" id="dobWithTitle" class="form-control" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                        Fermer
                    </button>
                    <button type="button" class="btn btn-primary">
                        Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- modal -->
    <div class="modal fade" id="filePreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl" style="max-width:90%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Prévisualisation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body" style="height: 80vh;">
                    <iframe id="fileViewer" src="" frameborder="0" style="width: 100%; height: 100%;">
                    </iframe>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Confirmation de suppression -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content premium-modal-content">
                <!-- Header -->
                <div class="text-center mb-6 position-relative">
                    <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    <div class="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-4" style="width: 3rem; height: 3rem; display: flex; align-items: center; justify-content: center; background-color: #fef2f2; border-radius: 1rem; margin: 0 auto 1rem;">
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

    <script>
        $(document).ready(function() {
            $('.selectpicker').selectpicker();
        });
    </script>

    <script>
        const accounting_ledgerDeleteUrl = "{{ route('accounting_ledger.destroy', ['id' => '__ID__']) }}";
        const accounting_ledgerpreviewGrandLivreUrl = "{{ route('accounting_ledger.previewGrandLivre') }}";
    </script>

    <script src="{{ asset('js/acc_ledger.js') }}"></script>
</body>

</html>
