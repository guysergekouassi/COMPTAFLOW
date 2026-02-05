<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">

@include('components.head')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    body {
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        background-color: #f8fafc;
    }
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 16px;
        box-shadow: 0 4px 20px 0 rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    .text-premium-gradient {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .btn-premium {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        border: none;
        border-radius: 12px;
        padding: 10px 20px;
        font-weight: 600;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
        transition: all 0.3s ease;
    }
    .section-title {
        font-size: 0.85rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #64748b;
        margin-bottom: 1.5rem;
        border-left: 4px solid #6366f1;
        padding-left: 12px;
    }
    .table-custom {
        border-collapse: separate;
        border-spacing: 0 8px;
    }
    .table-custom tr.main-row {
        background: #fdfdfd;
        border-radius: 12px;
        transition: transform 0.2s ease;
    }
    .table-custom tr.main-row:hover {
        transform: translateX(5px);
        background: #ffffff;
    }
    .table-custom td {
        padding: 14px 20px;
        border: none;
    }
    .table-custom tr.main-row td:first-child {
        border-radius: 12px 0 0 12px;
    }
    .table-custom tr.main-row td:last-child {
        border-radius: 0 12px 12px 0;
    }
    .flux-total-row {
        background: #f1f5f9 !important;
        font-weight: 700;
        color: #1e293b;
    }
    .grand-total-card {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: white;
        padding: 24px;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .amount-display {
        font-family: 'Inter', monospace;
        font-weight: 700;
    }
    
    /* Details Styling */
    .details-container {
        display: block;
        padding: 0 20px 10px 20px;
    }
    .table-details {
        width: 100%;
        font-size: 0.85rem;
        color: #64748b;
    }
    .table-details td {
        padding: 8px 10px;
        border-bottom: 1px dashed #e2e8f0;
    }
    .account-badge {
        background-color: #e2e8f0;
        color: #475569;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-right: 8px;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Tableau des <span class="text-gradient">Flux de Trésorerie</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Actions Header & Filters -->
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-6 gap-4">
                            <div>
                                <h4 class="fw-bold mb-1 text-premium-gradient">Analyse des flux (TFT)</h4>
                                <p class="text-muted small mb-0">
                                    <i class="bx bx-calendar-event me-1"></i> Exercice : <strong>{{ $exercice->intitule }}</strong>
                                </p>
                            </div>

                             <!-- Filter Form -->
                             <form action="{{ route('reporting.tft') }}" method="GET" class="d-flex align-items-center gap-3 bg-white p-2 rounded-3 shadow-sm" style="position: relative; z-index: 10;">
                                <select name="month" class="form-select border-0 bg-light" onchange="this.form.submit()" style="width: 150px; font-weight: 600;">
                                    <option value="all" {{ request('month') == 'all' ? 'selected' : '' }}>Tout l'exercice</option>
                                    @foreach(range(1, 12) as $m)
                                        <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create()->month($m)->locale('fr')->monthName }}
                                        </option>
                                    @endforeach
                                </select>
                                
                                <div class="form-check form-switch mb-0 d-flex align-items-center gap-2 ps-2 border-start">
                                    <input class="form-check-input" type="checkbox" id="detailSwitch" name="detail" value="1" {{ request('detail') ? 'checked' : '' }} onchange="this.form.submit()">
                                    <label class="form-check-label small fw-bold text-uppercase" for="detailSwitch">Détails</label>
                                </div>

                                <div class="border-start ps-2">
                                    <a href="{{ route('reporting.tft.export', ['format' => 'pdf'] + request()->all()) }}" class="btn btn-sm btn-light text-danger" data-bs-toggle="tooltip" title="Exporter en PDF">
                                        <i class="bx bxs-file-pdf fs-4"></i>
                                    </a>
                                </div>
                            </form>
                        </div>

                        <!-- Quick Stats -->
                        <div class="row g-6 mb-6">
                            <div class="col-sm-6 col-lg-3">
                                <div class="glass-card p-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar me-3">
                                            <span class="avatar-initial rounded bg-label-secondary"><i class="bx bx-hourglass-top"></i></span>
                                        </div>
                                        <h6 class="mb-0 text-muted small fw-bold text-uppercase">TRESORERIE INITIALE</h6>
                                    </div>
                                    <h4 class="ms-10 mb-0 amount-display">{{ number_format($data['tresorerie']['initiale'], 0, ',', ' ') }}</h4>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="glass-card p-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar me-3">
                                            <span class="avatar-initial rounded bg-label-success"><i class="bx bx-trending-up"></i></span>
                                        </div>
                                        <h6 class="mb-0 text-muted small fw-bold text-uppercase">FLUX EXPLOITATION</h6>
                                    </div>
                                    <h4 class="ms-10 mb-0 amount-display">{{ number_format($data['operationnel']['total'], 0, ',', ' ') }}</h4>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="glass-card p-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar me-3">
                                            <span class="avatar-initial rounded bg-label-info"><i class="bx bx-refresh"></i></span>
                                        </div>
                                        <h6 class="mb-0 text-muted small fw-bold text-uppercase">AUTO-FINANCEMENT</h6>
                                    </div>
                                    <h4 class="ms-10 mb-0 amount-display">{{ number_format($data['operationnel']['caf'], 0, ',', ' ') }}</h4>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="glass-card p-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar me-3">
                                            <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-flag"></i></span>
                                        </div>
                                        <h6 class="mb-0 text-muted small fw-bold text-uppercase">TRESORERIE FINALE</h6>
                                    </div>
                                    <h4 class="ms-10 mb-0 amount-display">{{ number_format($data['tresorerie']['finale'], 0, ',', ' ') }}</h4>
                                </div>
                            </div>
                        </div>

                        <div class="glass-card p-6">
                            
                            <!-- SECTION OPERATIONNELLE -->
                            <div class="mb-8">
                                <h6 class="section-title">Activités Opérationnelles</h6>
                                <div class="table-responsive">
                                    <table class="table table-custom mb-0">
                                        <tbody>
                                            <tr class="main-row">
                                                <td class="text-slate-600">Capacité d'Autofinancement (CAF)</td>
                                                <td class="text-end fw-bold amount-display">{{ number_format($data['operationnel']['caf'], 0, ',', ' ') }}</td>
                                            </tr>
                                            <tr class="main-row">
                                                <td class="text-slate-600">Variation du Besoin en Fonds de Roulement (BFR)</td>
                                                <td class="text-end fw-bold amount-display">{{ number_format($data['operationnel']['variation_bfr'], 0, ',', ' ') }}</td>
                                            </tr>
                                            <tr class="flux-total-row main-row">
                                                <td>FLUX NET DE TRÉSORERIE EXPLOITATION (B)</td>
                                                <td class="text-end amount-display">{{ number_format($data['operationnel']['total'], 0, ',', ' ') }} <small>FCFA</small></td>
                                            </tr>
                                            @if(request('detail'))
                                            @if(!empty($data['operationnel']['details']))
                                            <tr>
                                                <td colspan="2" class="p-0">
                                                    <div class="details-container">
                                                        <div class="px-2 py-1 bg-slate-50 text-uppercase small fw-bold text-muted mb-2">Détails Exploitation</div>
                                                        <table class="table-details">
                                                            @foreach($data['operationnel']['details'] as $item)
                                                            <tr>
                                                                <td width="20%"><span class="account-badge">{{ $item['numero'] }}</span></td>
                                                                <td>{{ $item['intitule'] }}</td>
                                                                <td class="text-end">{{ number_format($item['solde'], 0, ',', ' ') }}</td>
                                                            </tr>
                                                            @endforeach
                                                        </table>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endif
                                            @if(empty($data['operationnel']['details']))
                                            <tr><td colspan="2" class="p-4 text-center text-muted fst-italic small">Aucune écriture trouvée</td></tr>
                                            @endif
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- SECTION INVESTISSEMENT -->
                            <div class="mb-8">
                                <h6 class="section-title">Activités d'Investissement</h6>
                                <div class="table-responsive">
                                    <table class="table table-custom mb-0">
                                        <tbody>
                                            <tr class="main-row">
                                                <td class="text-slate-600">Produits des cessions d'immobilisations (+)</td>
                                                <td class="text-end fw-bold text-success amount-display">+ {{ number_format($data['investissement']['cessions'], 0, ',', ' ') }}</td>
                                            </tr>
                                            <tr class="main-row">
                                                <td class="text-slate-600">Acquisitions d'immobilisations (-)</td>
                                                <td class="text-end fw-bold text-danger amount-display">- {{ number_format($data['investissement']['acquisitions'], 0, ',', ' ') }}</td>
                                            </tr>
                                            <tr class="flux-total-row main-row">
                                                <td>FLUX NET DE TRÉSORERIE INVESTISSEMENT (C)</td>
                                                <td class="text-end amount-display">{{ number_format($data['investissement']['total'], 0, ',', ' ') }} <small>FCFA</small></td>
                                            </tr>
                                            @if(request('detail'))
                                            @if(!empty($data['investissement']['details']))
                                            <tr>
                                                <td colspan="2" class="p-0">
                                                    <div class="details-container">
                                                        <div class="px-2 py-1 bg-slate-50 text-uppercase small fw-bold text-muted mb-2">Détails Investissement</div>
                                                        <table class="table-details">
                                                            @foreach($data['investissement']['details'] as $item)
                                                            <tr>
                                                                <td width="20%"><span class="account-badge">{{ $item['numero'] }}</span></td>
                                                                <td>{{ $item['intitule'] }}</td>
                                                                <td class="text-end">{{ number_format($item['solde'], 0, ',', ' ') }}</td>
                                                            </tr>
                                                            @endforeach
                                                        </table>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endif
                                            @if(empty($data['investissement']['details']))
                                            <tr><td colspan="2" class="p-4 text-center text-muted fst-italic small">Aucune écriture trouvée</td></tr>
                                            @endif
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- SECTION FINANCEMENT -->
                            <div class="mb-8">
                                <h6 class="section-title">Activités de Financement</h6>
                                <div class="table-responsive">
                                    <table class="table table-custom mb-0">
                                        <tbody>
                                            <tr class="main-row">
                                                <td class="text-slate-600">Variations de Capital (+)</td>
                                                <td class="text-end fw-bold amount-display">{{ number_format($data['financement']['capital'], 0, ',', ' ') }}</td>
                                            </tr>
                                            <tr class="main-row">
                                                <td class="text-slate-600">Variations d'Emprunts et Dettes financières</td>
                                                <td class="text-end fw-bold amount-display">{{ number_format($data['financement']['emprunts'], 0, ',', ' ') }}</td>
                                            </tr>
                                            <tr class="flux-total-row main-row">
                                                <td>FLUX NET DE TRÉSORERIE FINANCEMENT (D)</td>
                                                <td class="text-end amount-display">{{ number_format($data['financement']['total'], 0, ',', ' ') }} <small>FCFA</small></td>
                                            </tr>
                                            @if(request('detail'))
                                            @if(!empty($data['financement']['details']))
                                            <tr>
                                                <td colspan="2" class="p-0">
                                                    <div class="details-container">
                                                        <div class="px-2 py-1 bg-slate-50 text-uppercase small fw-bold text-muted mb-2">Détails Financement</div>
                                                        <table class="table-details">
                                                            @foreach($data['financement']['details'] as $item)
                                                            <tr>
                                                                <td width="20%"><span class="account-badge">{{ $item['numero'] }}</span></td>
                                                                <td>{{ $item['intitule'] }}</td>
                                                                <td class="text-end">{{ number_format($item['solde'], 0, ',', ' ') }}</td>
                                                            </tr>
                                                            @endforeach
                                                        </table>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endif
                                            @if(empty($data['financement']['details']))
                                            <tr><td colspan="2" class="p-4 text-center text-muted fst-italic small">Aucune écriture trouvée</td></tr>
                                            @endif
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- SYNTHESE FINALE -->
                            <div class="row g-6 align-items-center mt-4">
                                <div class="col-md-7">
                                    <div class="p-5 rounded-4 bg-label-info border border-info border-dashed d-flex align-items-center h-100">
                                        <i class="bx bx-info-circle fs-3 me-4"></i>
                                        <p class="mb-0 small">
                                            L'équilibre de ce tableau (SYSCOHADA) est validé si la **Trésorerie Finale (E)** calculée ici correspond exactement aux disponibilités de votre Bilan.
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="grand-total-card">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="small opacity-75 text-uppercase fw-bold">Trésorerie Finale (E)</span>
                                            <i class="bx bx-check-shield fs-4"></i>
                                        </div>
                                        <h2 class="mb-0 amount-display text-white">{{ number_format($data['tresorerie']['finale'], 0, ',', ' ') }} <small class="ms-1" style="font-size: 1rem;">FCFA</small></h2>
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
</body>
</html>
