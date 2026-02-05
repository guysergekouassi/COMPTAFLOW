@include('components.head')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@200;300;400;500;600;700;800&display=swap');
    body { font-family: 'Outfit', sans-serif; background-color: #f8fafc; }
</style>
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Rapport <span class="text-blue-600">Importation</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="fw-bold py-3 mb-0">Résultats du Traitement</h4>
                        </div>

                        <!-- 1. HEADER STATUS -->
                        @if(empty($report['errors']) && $report['status'] !== 'error')
                            <div class="card bg-green-50 border-0 border-start border-4 border-green-500 shadow-sm mb-4">
                                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                                    <div>
                                        <h3 class="text-green-700 font-bold mb-1"><i class="fa-solid fa-check-circle me-2"></i>Importation Réussie</h3>
                                        <p class="text-green-600 mb-0">Vos écritures ont été importées et la balance est équilibrée.</p>
                                    </div>
                                    <div class="text-end">
                                        <a href="{{ route('admin.config.external_import') }}" class="btn btn-success rounded-lg font-bold px-5 shadow-lg shadow-green-500/30 text-white">
                                            Terminer <i class="fa-solid fa-arrow-right ms-2"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="card bg-red-50 border-0 border-start border-4 border-red-500 shadow-sm mb-4">
                                <div class="card-body p-4 d-flex align-items-center justify-content-between">
                                    <div>
                                        <h3 class="text-red-700 font-bold mb-1"><i class="fa-solid fa-exclamation-triangle me-2"></i>Échec de l'Importation</h3>
                                        <p class="text-red-600 mb-0">Des erreurs bloquantes ou un déséquilibre comptable ont empêché l'importation.</p>
                                    </div>
                                    <div class="text-end">
                                        <a href="{{ route('admin.config.external_import') }}" class="btn btn-outline-danger rounded-lg font-bold px-5">
                                            <i class="fa-solid fa-arrow-left me-2"></i> Retour
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- 2. STATS GRID -->
                        <div class="row g-4 mb-4">
                            <!-- Processed -->
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Lignes Générales (G)</div>
                                        <div class="d-flex align-items-end gap-2">
                                            <h2 class="mb-0 font-black text-slate-700">{{ $report['processed_g'] }}</h2>
                                            <span class="badge bg-green-100 text-green-600 mb-1">Traitées</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                             <!-- Filtered -->
                             <div class="col-md-3">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Analytiques / Ignorées (A)</div>
                                        <div class="d-flex align-items-end gap-2">
                                            <h2 class="mb-0 font-black text-orange-600">{{ $report['filtered_a'] }}</h2>
                                            @if($report['deduplicated'] > 0)
                                                <span class="badge bg-purple-100 text-purple-600 mb-1">+ {{ $report['deduplicated'] }} Doublons</span>
                                            @else
                                                <span class="badge bg-orange-100 text-orange-600 mb-1">Filtrées</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Debit -->
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Total Débit</div>
                                        <h3 class="mb-0 font-bold text-slate-700">{{ number_format($report['total_debit'], 2, ',', ' ') }}</h3>
                                    </div>
                                </div>
                            </div>
                            <!-- Credit -->
                            <div class="col-md-3">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Total Crédit</div>
                                        <h3 class="mb-0 font-bold text-slate-700">{{ number_format($report['total_credit'], 2, ',', ' ') }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. ERROR LOGS if any -->
                        @if(!empty($report['errors']))
                        <div class="card border-0 shadow-sm rounded-xl overflow-hidden mb-4">
                            <div class="card-header bg-red-500 text-white font-bold py-3">
                                <i class="fa-solid fa-bug me-2"></i> Journal des Erreurs
                            </div>
                            <div class="card-body bg-red-50 p-0">
                                <div class="list-group list-group-flush">
                                    @foreach($report['errors'] as $err)
                                        <div class="list-group-item bg-transparent border-red-100 text-red-700 py-3">
                                            <i class="fa-solid fa-times-circle me-2"></i> {{ $err }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @else
                        <!-- SUCCESS INFO -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="alert alert-success d-flex align-items-center p-4 border-0 shadow-sm rounded-xl">
                                    <div class="me-4 text-green-500 bg-white p-3 rounded-full shadow-sm">
                                        <i class="fa-solid fa-scale-balanced fa-2x"></i>
                                    </div>
                                    <div>
                                        <h4 class="alert-heading font-bold mb-1">Comptabilité Équilibrée !</h4>
                                        <p class="mb-0 opacity-75">Le système a vérifié l'équilibre global et par pièce. Aucune anomalie détectée.</p>
                                    </div>
                                </div>
                            </div>
                            @if($report['new_accounts'] > 0 || $report['new_tiers'] > 0)
                            <div class="col-md-12 mt-3">
                                <div class="card bg-blue-50 border-0 text-blue-800">
                                    <div class="card-body">
                                        <h6 class="font-bold"><i class="fa-solid fa-info-circle me-2"></i>Enrichissement de la base :</h6>
                                        <ul class="mb-0">
                                            @if($report['new_accounts'] > 0) <li>{{ $report['new_accounts'] }} nouveaux Comptes Généraux créés.</li> @endif
                                            @if($report['new_tiers'] > 0) <li>{{ $report['new_tiers'] }} nouveaux Tiers créés.</li> @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
