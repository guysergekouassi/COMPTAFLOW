@include('components.head')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap');

    :root {
        --premium-blue: #1e40af;
        --premium-blue-light: #3b82f6;
        --glass-bg: rgba(255, 255, 255, 0.95);
    }

    body {
        background-color: #f8fafc;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .hub-card {
        background: var(--glass-bg);
        border-radius: 24px;
        border: 1px solid rgba(226, 232, 240, 0.8);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        height: 100%;
        overflow: hidden;
        position: relative;
    }

    .hub-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        border-color: var(--premium-blue-light);
    }

    .hub-icon-wrapper {
        width: 64px;
        height: 64px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }

    .hub-card:hover .hub-icon-wrapper {
        transform: scale(1.1) rotate(5deg);
    }

    .status-badge {
        position: absolute;
        top: 20px;
        right: 20px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .btn-import-link {
        text-decoration: none;
        color: inherit;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Configuration / <span class="text-primary">Importation</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <div class="row mb-8">
                            <div class="col-12">
                                <div class="bg-white p-8 rounded-[32px] shadow-sm d-flex align-items-center justify-content-between border border-slate-100 overflow-hidden position-relative">
                                    <div class="position-relative" style="z-index: 2;">
                                        <h2 class="font-black mb-1 text-slate-900">Hub d'Importation</h2>
                                    <p class="text-slate-500 mb-4 max-w-xl">
                                        Migrez vos données depuis des logiciels externes (Sage, SAP, Quadratus) en toute simplicité. Suivez notre tunnel intelligent pour une migration sans erreurs.
                                    </p>
                                    <button type="button" class="btn btn-outline-primary rounded-xl font-bold" data-bs-toggle="modal" data-bs-target="#modalImportInstructions">
                                        <i class="fa-solid fa-circle-info me-2"></i> Guide d'Importation
                                    </button>
                                </div>
                                    <div class="bg-blue-50 p-6 rounded-[24px] position-relative" style="z-index: 2;">
                                        <i class="fa-solid fa-rocket fa-3x text-blue-600"></i>
                                    </div>
                                    <!-- Décoration fond -->
                                    <div class="position-absolute" style="top: -20px; right: -20px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(59, 130, 246, 0.05) 0%, transparent 70%); z-index: 1;"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-6">
                            <!-- Option 1: Import Initial -->
                            <div class="col-md-4">
                                <a href="{{ route('admin.config.external_import') }}?type=initial" class="btn-import-link">
                                    <div class="hub-card p-8">
                                        <div class="hub-icon-wrapper bg-emerald-100">
                                            <i class="fa-solid fa-database fa-2x text-emerald-600"></i>
                                        </div>
                                        <h4 class="font-black text-slate-900 mb-3">Import Plan/Tiers</h4>
                                        <p class="text-slate-500 text-sm mb-6">
                                            Chargez votre <strong>Plan Comptable</strong> et votre <strong>Fichier Tiers</strong> (Clients/Fournisseurs) pour démarrer.
                                        </p>
                                        <hr class="border-slate-100 mb-6">
                                        <ul class="list-unstyled space-y-3">
                                            <li class="d-flex align-items-center gap-2 text-xs text-slate-600">
                                                <i class="fa-solid fa-check text-emerald-500"></i> Dossier de base
                                            </li>
                                        </ul>
                                    </div>
                                </a>
                            </div>

                            <!-- New Option: Import Journaux -->
                            <div class="col-md-4">
                                <a href="{{ route('admin.config.external_import') }}?type=journals" class="btn-import-link">
                                    <div class="hub-card p-8">
                                        <div class="hub-icon-wrapper bg-amber-100">
                                            <i class="fa-solid fa-swatchbook fa-2x text-amber-600"></i>
                                        </div>
                                        <h4 class="font-black text-slate-900 mb-3">Import Journaux</h4>
                                        <p class="text-slate-500 text-sm mb-6">
                                            Importez vos propres <strong>Codes Journaux</strong> personnalisés (Banques, Ha, Ve, OD).
                                        </p>
                                        <hr class="border-slate-100 mb-6">
                                        <ul class="list-unstyled space-y-3">
                                            <li class="d-flex align-items-center gap-2 text-xs text-slate-600">
                                                <i class="fa-solid fa-check text-amber-500"></i> Codes personnalisés
                                            </li>
                                        </ul>
                                    </div>
                                </a>
                            </div>

                            <!-- Option 2: Import Courant -->
                            <div class="col-md-4">
                                <a href="{{ route('admin.config.external_import') }}?type=courant" class="btn-import-link">
                                    <div class="hub-card p-8 border-2 border-primary/20">
                                        <span class="status-badge bg-blue-100 text-blue-700">Recommandé</span>
                                        <div class="hub-icon-wrapper bg-blue-100">
                                            <i class="fa-solid fa-file-invoice-dollar fa-2x text-blue-600"></i>
                                        </div>
                                        <h4 class="font-black text-slate-900 mb-3">Import Écritures</h4>
                                        <p class="text-slate-500 text-sm mb-6">
                                            Migrez vos <strong>Écritures Historiques</strong>. Idéal pour reprendre un dossier en cours d'exercice.
                                        </p>
                                        <hr class="border-slate-100 mb-6">
                                        <ul class="list-unstyled space-y-3">
                                            <li class="d-flex align-items-center gap-2 text-xs text-slate-600">
                                                <i class="fa-solid fa-check text-blue-500"></i> Grand Livre & Journaux
                                            </li>
                                        </ul>
                                    </div>
                                </a>
                            </div>

                            <!-- Option 3: Import Spécifique -->
                            <div class="col-md-4">
                                <div class="hub-card p-8 opacity-75 grayscale hover:grayscale-0">
                                    <span class="status-badge bg-slate-100 text-slate-500">Bientôt</span>
                                    <div class="hub-icon-wrapper bg-slate-100">
                                        <i class="fa-solid fa-building-columns fa-2x text-slate-600"></i>
                                    </div>
                                    <h4 class="font-black text-slate-900 mb-3">Import Bancaire</h4>
                                    <p class="text-slate-500 text-sm mb-6">
                                        Importez vos <strong>Relevés Bancaires</strong> aux formats OFX, CSV ou via API bancaire (DSP2).
                                    </p>
                                    <hr class="border-slate-100 mb-6">
                                    <ul class="list-unstyled space-y-3">
                                        <li class="d-flex align-items-center gap-2 text-xs text-slate-400">
                                            <i class="fa-solid fa-clock"></i> Format OFX / QIF
                                        </li>
                                        <li class="d-flex align-items-center gap-2 text-xs text-slate-400">
                                            <i class="fa-solid fa-clock"></i> Synchronisation API
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Section Historique & Aide -->
                        <div class="row mt-8 g-6">
                            <div class="col-lg-8">
                                <div class="bg-white p-6 rounded-[24px] shadow-sm border border-slate-100">
                                    <div class="d-flex align-items-center justify-content-between mb-6">
                                        <h5 class="font-black mb-0">Historique des Importations</h5>
                                        <button class="btn btn-sm btn-outline-secondary rounded-pill">Voir tout</button>
                                    </div>
                                    <div class="table-responsive">
                                            <thead>
                                                <tr class="text-slate-400 text-xs">
                                                    <th>DATE</th>
                                                    <th>FICHIER</th>
                                                    <th>TYPE</th>
                                                    <th>STATUT</th>
                                                    <th class="text-end">ACTION</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($imports as $imp)
                                                <tr class="align-middle">
                                                    <td><span class="text-xs font-bold text-slate-500">{{ $imp->created_at->format('d/m H:i') }}</span></td>
                                                    <td style="max-width: 200px;" class="text-truncate">
                                                        <span class="text-xs font-black" title="{{ $imp->file_name }}">{{ $imp->file_name }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-label-info text-[10px] uppercase">
                                                            {{ $imp->type == 'initial' ? 'Plan/Tiers' : ($imp->type == 'journals' ? 'Journaux' : ($imp->type == 'courant' ? 'Écritures' : 'Autre')) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($imp->status == 'upload')
                                                            <span class="badge bg-label-secondary text-[10px]">Mapping</span>
                                                        @elseif($imp->status == 'staging')
                                                            <span class="badge bg-label-primary text-[10px]">Staging</span>
                                                        @elseif($imp->status == 'completed')
                                                            <span class="badge bg-label-success text-[10px]">Terminé</span>
                                                        @else
                                                            <span class="badge bg-label-warning text-[10px]">{{ $imp->status }}</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-end">
                                                        <div class="d-flex justify-content-end gap-2">
                                                            @if($imp->status != 'completed')
                                                                <a href="{{ $imp->status == 'upload' ? route('admin.import.mapping', $imp->id) : route('admin.import.staging', $imp->id) }}" 
                                                                   class="btn btn-icon btn-sm btn-label-primary rounded-pill shadow-sm" title="Reprendre">
                                                                    <i class="fa-solid fa-play text-[10px]"></i>
                                                                </a>
                                                            @endif
                                                            <form action="{{ route('admin.import.cancel', $imp->id) }}" method="POST" onsubmit="return confirm('Voulez-vous supprimer cet import ?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-icon btn-sm btn-label-danger rounded-pill shadow-sm" title="Supprimer">
                                                                    <i class="fa-solid fa-trash text-[10px]"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="5" class="text-center py-12 text-slate-400">
                                                        <i class="fa-solid fa-inbox fa-3x mb-4 d-block opacity-20"></i>
                                                        <div class="font-bold">Aucun import historique trouvé.</div>
                                                        <div class="text-[10px]">Démarrez une nouvelle importation ci-dessus.</div>
                                                    </td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="bg-slate-900 text-white p-8 rounded-[24px] shadow-lg h-100 position-relative overflow-hidden">
                                    <h5 class="font-black text-white mb-4">Besoin d'aide ?</h5>
                                    
                                    <!-- Modèles Excel ComptaFlow -->
                                    <div class="mb-6">
                                        <label class="text-[10px] font-black text-slate-500 uppercase mb-3 d-block letter-spacing-wider">Modèles Excel à Télécharger</label>
                                        <div class="d-flex flex-column gap-3">
                                            <!-- Modèle Plan -->
                                            <div class="model-item">
                                                <a href="{{ asset('templates/import/modele_plan_comptable.xlsx') }}" class="p-3 rounded-xl bg-primary/10 border border-primary/20 d-flex align-items-center gap-3 text-decoration-none hover:bg-primary/20 transition-all mb-2">
                                                    <i class="fa-solid fa-file-excel text-success fs-5"></i>
                                                    <div class="flex-grow-1">
                                                        <div class="text-xs font-bold text-white">Modèle Plan Comptable</div>
                                                        <div class="text-[10px] text-slate-400">Comptes & Classes</div>
                                                    </div>
                                                    <i class="fa-solid fa-download text-slate-500 text-xs"></i>
                                                </a>
                                                <p class="text-[10px] text-slate-500 px-2 leading-relaxed">
                                                    Utilisez ce fichier pour charger votre Plan Comptable. Colonnes requises : <strong>Numéro de compte</strong>, <strong>Intitulé</strong>, <strong>Type</strong> (Bilan/Résultat).
                                                </p>
                                            </div>

                                            <!-- Modèle Journaux -->
                                            <div class="model-item">
                                                <a href="{{ asset('templates/import/modele_codes_journaux.xlsx') }}" class="p-3 rounded-xl bg-primary/10 border border-primary/20 d-flex align-items-center gap-3 text-decoration-none hover:bg-primary/20 transition-all mb-2">
                                                    <i class="fa-solid fa-file-excel text-success fs-5"></i>
                                                    <div class="flex-grow-1">
                                                        <div class="text-xs font-bold text-white">Modèle Codes Journaux</div>
                                                        <div class="text-[10px] text-slate-400">Structure des journaux</div>
                                                    </div>
                                                    <i class="fa-solid fa-download text-slate-500 text-xs"></i>
                                                </a>
                                                <p class="text-[10px] text-slate-500 px-2 leading-relaxed">
                                                    Définissez vos codes journaux personnalisés. Colonnes : <strong>Code</strong> (ex: ACH), <strong>Intitulé</strong> (ex: Achats), <strong>Type</strong> (Achats/Ventes/etc).
                                                </p>
                                            </div>

                                            <!-- Modèle Écritures -->
                                            <div class="model-item">
                                                <a href="{{ asset('templates/import/modele_ecritures.xlsx') }}" class="p-3 rounded-xl bg-primary/10 border border-primary/20 d-flex align-items-center gap-3 text-decoration-none hover:bg-primary/20 transition-all mb-2">
                                                    <i class="fa-solid fa-file-excel text-success fs-5"></i>
                                                    <div class="flex-grow-1">
                                                        <div class="text-xs font-bold text-white">Modèle Écritures</div>
                                                        <div class="text-[10px] text-slate-400">Journaux de saisie</div>
                                                    </div>
                                                    <i class="fa-solid fa-download text-slate-500 text-xs"></i>
                                                </a>
                                                <p class="text-[10px] text-slate-500 px-2 leading-relaxed">
                                                    Le format standard pour importer vos écritures. Inclut : <strong>Date</strong>, <strong>Journal</strong>, <strong>Compte</strong>, <strong>Libellé</strong>, <strong>Débit</strong> et <strong>Crédit</strong>.
                                                </p>
                                            </div>

                                            <!-- Modèle Tiers -->
                                            <div class="model-item">
                                                <a href="{{ asset('templates/import/modele_plan_tiers.xlsx') }}" class="p-3 rounded-xl bg-primary/10 border border-primary/20 d-flex align-items-center gap-3 text-decoration-none hover:bg-primary/20 transition-all mb-2">
                                                    <i class="fa-solid fa-file-excel text-success fs-5"></i>
                                                    <div class="flex-grow-1">
                                                        <div class="text-xs font-bold text-white">Modèle Plan Tiers</div>
                                                        <div class="text-[10px] text-slate-400">Clients & Fournisseurs</div>
                                                    </div>
                                                    <i class="fa-solid fa-download text-slate-500 text-xs"></i>
                                                </a>
                                                <p class="text-[10px] text-slate-500 px-2 leading-relaxed">
                                                    Importez vos comptes de tiers. Colonnes : <strong>Compte</strong> (ex: 401...), <strong>Nom</strong>, <strong>Type</strong> (Client/Fournisseur).
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <label class="text-[10px] font-black text-slate-500 uppercase mb-3 d-block letter-spacing-wider">Guides d'Exportation</label>
                                    <p class="text-slate-400 text-sm mb-6">
                                        Nos experts préparent des guides pour vous aider à exporter vos données.
                                    </p>
                                    <div class="d-flex flex-column gap-3">
                                        <!-- Guide Sage -->
                                        <div class="d-flex align-items-center justify-content-between p-4 rounded-xl" style="background: #1e293b; border: 1px solid #334155;">
                                            <div class="d-flex align-items-center gap-4">
                                                <div class="bg-rose-500/10 p-2 rounded-lg">
                                                    <i class="fa-solid fa-file-pdf text-rose-500"></i> 
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="text-white font-bold text-sm">Guide Sage 100</span>
                                                    <span class="text-slate-500" style="font-size: 0.7rem;">Exportation Grand Livre</span>
                                                </div>
                                            </div>
                                            <span class="badge bg-slate-700 text-slate-400 text-[10px] font-black uppercase">Bientôt</span>
                                        </div>

                                        <!-- Guide Quadratus -->
                                        <div class="d-flex align-items-center justify-content-between p-4 rounded-xl" style="background: #1e293b; border: 1px solid #334155;">
                                            <div class="d-flex align-items-center gap-4">
                                                <div class="bg-rose-500/10 p-2 rounded-lg">
                                                    <i class="fa-solid fa-file-pdf text-rose-500"></i> 
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="text-white font-bold text-sm">Guide Quadratus</span>
                                                    <span class="text-slate-500" style="font-size: 0.7rem;">Exportation écritures</span>
                                                </div>
                                            </div>
                                            <span class="badge bg-slate-700 text-slate-400 text-[10px] font-black uppercase">Bientôt</span>
                                        </div>

                                        <!-- Guide Cegid -->
                                        <div class="d-flex align-items-center justify-content-between p-4 rounded-xl" style="background: #1e293b; border: 1px solid #334155;">
                                            <div class="d-flex align-items-center gap-4">
                                                <div class="bg-rose-500/10 p-2 rounded-lg">
                                                    <i class="fa-solid fa-file-pdf text-rose-500"></i> 
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="text-white font-bold text-sm">Guide Cegid</span>
                                                    <span class="text-slate-500" style="font-size: 0.7rem;">Format Expert</span>
                                                </div>
                                            </div>
                                            <span class="badge bg-slate-700 text-slate-400 text-[10px] font-black uppercase">Bientôt</span>
                                        </div>
                                    </div>
                                    <!-- Déco -->
                                    <div class="position-absolute" style="bottom: -20px; right: -20px; opacity: 0.05;">
                                        <i class="fa-solid fa-life-ring fa-6x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    @include('components.footer')
                </div>
            </div>
        </div>
    </div>
    @include('components.import_instructions')
</body>
</html>
