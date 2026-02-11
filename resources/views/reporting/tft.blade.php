<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">

@include('components.head')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    body {
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        background-color: #f4f7fe;
    }
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.4);
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        padding: 20px;
        overflow-x: auto; /* Allow horizontal scroll for large matrix */
    }
    .text-premium-gradient {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .table-matrix {
        width: 100%;
        min-width: 1000px;
        border-collapse: separate; 
        border-spacing: 0;
        font-size: 0.85rem;
    }
    .table-matrix th, .table-matrix td {
        padding: 10px 12px;
        border-bottom: 1px solid #e2e8f0;
    }
    .table-matrix th {
        background: #f8fafc;
        font-weight: 700;
        color: #475569;
        text-transform: uppercase;
        font-size: 0.75rem;
        position: sticky;
        top: 0;
    }
    .table-matrix td {
        color: #1e293b;
        text-align: right;
        font-family: 'Inter', monospace;
        white-space: nowrap !important;
    }
    .table-matrix td:first-child {
        text-align: left;
        font-weight: 600;
        position: sticky;
        left: 0;
        background: white;
        z-index: 1;
        border-right: 1px solid #e2e8f0;
    }
    .table-matrix tr:hover td {
        background-color: #f1f5f9;
    }
    .section-row td {
        background-color: #e2e8f0;
        font-weight: 800;
        color: #1e293b;
        text-transform: uppercase;
        font-size: 0.8rem;
    }
    .total-row td {
        background-color: #f8fafc;
        font-weight: 700;
        border-top: 2px solid #cbd5e1;
    }
    .main-total-row td {
        background-color: #1e293b !important;
        color: white !important;
        font-weight: 800;
    }
    .table-matrix tr.main-total-row:hover td {
        background-color: #1e293b !important;
        color: white !important;
    }
    .detail-row td {
        background-color: #fff;
        font-style: italic;
        color: #64748b;
        font-size: 0.8rem;
    }
    .detail-row td:first-child {
        padding-left: 30px;
        font-weight: 400;
    }
    /* Toggle switch styling */
    .switch-toggle input {
        display: none;
    }
    .switch-toggle label {
        cursor: pointer;
        background: #e2e8f0;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .switch-toggle input:checked + label {
        background: #6366f1;
        color: white;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Tableau des Flux de <span class="text-gradient">Trésorerie</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Actions Header & Filters -->
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-6 gap-4">
                            <div>
                                <h3 class="fw-extrabold mb-1 text-premium-gradient">Flux de Trésorerie</h3>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-label-primary px-3 py-2 rounded-pill me-3">
                                        <i class="bx bx-calendar me-1"></i> {{ $exercice->intitule }}
                                    </span>
                                </div>
                            </div>

                            <!-- Filter Form -->
                            <form action="{{ route('reporting.tft') }}" method="GET" class="d-flex align-items-center gap-3 bg-white p-2 rounded-3 shadow-sm">
                                
                                <div class="switch-toggle">
                                    <input type="checkbox" id="detailSwitch" name="detail" value="1" {{ request('detail') ? 'checked' : '' }} onchange="this.form.submit()">
                                    <label for="detailSwitch">
                                        <i class="bx bx-list-ul"></i>
                                        {{ request('detail') ? 'Vue Détaillée' : 'Vue en Masse' }}
                                    </label>
                                </div>

                                <div class="border-start ps-2 d-flex gap-2">
                                    <a href="{{ route('reporting.tft.export', ['format' => 'pdf'] + request()->all()) }}" class="btn btn-sm btn-light text-danger" data-bs-toggle="tooltip" title="Exporter en PDF">
                                        <i class="bx bxs-file-pdf fs-4"></i>
                                    </a>
                                    <a href="{{ route('reporting.tft.export', ['format' => 'excel'] + request()->all()) }}" class="btn btn-sm btn-light text-success" data-bs-toggle="tooltip" title="Exporter en Excel">
                                        <i class="bx bxs-file-json fs-4"></i>
                                    </a>
                                </div>
                            </form>
                        </div>

                        <!-- Summary Cards -->
                        <div class="d-flex align-items-stretch gap-4 mb-4" style="overflow-x: auto; padding-bottom: 5px;">
                            <!-- Opérationnel -->
                            <div class="card border-0 shadow-sm rounded-4 flex-grow-1" style="min-width: 200px;">
                                <div class="card-body p-4 position-relative">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h6 class="text-uppercase text-muted fw-bold small mb-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Opérationnel</h6>
                                        <div class="p-2 rounded-3 bg-label-primary text-primary">
                                            <i class="bx bx-briefcase fs-4"></i>
                                        </div>
                                    </div>
                                    <h4 class="mb-2 fw-extrabold text-dark">{{ number_format(array_sum($data['flux']['operationnel']['net']), 0, ',', ' ') }}</h4>
                                    <div class="small fw-semibold text-primary">
                                        <i class="bx bx-check-circle me-1"></i> Flux Net
                                    </div>
                                </div>
                            </div>

                            <!-- Investissement -->
                            <div class="card border-0 shadow-sm rounded-4 flex-grow-1" style="min-width: 200px;">
                                <div class="card-body p-4 position-relative">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h6 class="text-uppercase text-muted fw-bold small mb-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Investissement</h6>
                                        <div class="p-2 rounded-3 bg-label-info text-info">
                                            <i class="bx bx-building fs-4"></i>
                                        </div>
                                    </div>
                                    <h4 class="mb-2 fw-extrabold text-dark">{{ number_format(array_sum($data['flux']['investissement']['net']), 0, ',', ' ') }}</h4>
                                    <div class="small fw-semibold text-info">
                                        <i class="bx bx-pie-chart-alt me-1"></i> Actifs
                                    </div>
                                </div>
                            </div>

                            <!-- Financement -->
                            <div class="card border-0 shadow-sm rounded-4 flex-grow-1" style="min-width: 200px;">
                                <div class="card-body p-4 position-relative">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h6 class="text-uppercase text-muted fw-bold small mb-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Financement</h6>
                                        <div class="p-2 rounded-3 bg-label-warning text-warning">
                                            <i class="bx bx-money fs-4"></i>
                                        </div>
                                    </div>
                                    <h4 class="mb-2 fw-extrabold text-dark">{{ number_format(array_sum($data['flux']['financement']['net']), 0, ',', ' ') }}</h4>
                                    <div class="small fw-semibold text-warning">
                                        <i class="bx bx-line-chart me-1"></i> Capitaux
                                    </div>
                                </div>
                            </div>

                            <!-- Variation -->
                            @php
                                $varTotal = array_sum($data['flux']['tresorerie']['variation']);
                                $varColor = $varTotal >= 0 ? 'success' : 'danger';
                                $varIcon = $varTotal >= 0 ? 'bx-trending-up' : 'bx-trending-down';
                            @endphp
                            <div class="card border-0 shadow-sm rounded-4 flex-grow-1" style="min-width: 200px;">
                                <div class="card-body p-4 position-relative">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h6 class="text-uppercase text-muted fw-bold small mb-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Variation Totale</h6>
                                        <div class="p-2 rounded-3 bg-label-{{ $varColor }} text-{{ $varColor }}">
                                            <i class="bx {{ $varIcon }} fs-4"></i>
                                        </div>
                                    </div>
                                    <h4 class="mb-2 fw-extrabold text-{{ $varColor }}">{{ number_format($varTotal, 0, ',', ' ') }}</h4>
                                    <div class="small fw-semibold text-{{ $varColor }}">
                                        <i class="bx bx-stats me-1"></i> Global
                                    </div>
                                </div>
                            </div>

                            <!-- Solde Final -->
                            @php
                                $soldeFin = $data['flux']['tresorerie']['solde_fin'][count($data['months'])-1] ?? 0;
                                $soldeColor = $soldeFin >= 0 ? 'primary' : 'danger';
                            @endphp
                            <div class="card border-0 shadow-sm rounded-4 flex-grow-1" style="min-width: 200px;">
                                <div class="card-body p-4 position-relative">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h6 class="text-uppercase text-muted fw-bold small mb-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Solde Trésorerie</h6>
                                        <div class="p-2 rounded-3 bg-label-{{ $soldeColor }} text-{{ $soldeColor }}">
                                            <i class="bx bx-wallet fs-4"></i>
                                        </div>
                                    </div>
                                    <h4 class="mb-2 fw-extrabold text-{{ $soldeColor }}">
                                        {{ number_format($soldeFin, 0, ',', ' ') }} <span class="fs-6 text-muted fw-normal">FCFA</span>
                                    </h4>
                                    <div class="small fw-semibold text-muted font-italic">
                                        Mise à jour temps réel
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="glass-card">
                            <table class="table-matrix">
                                <thead>
                                    <tr>
                                        <th>Flux de trésorerie</th>
                                        @foreach($data['months'] as $month)
                                            <th class="text-center">{{ $month['name'] }}</th>
                                        @endforeach
                                        <th class="text-center">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="section-row">
                                        <td colspan="{{ count($data['months']) + 2 }}">I. Flux de trésorerie des activités opérationnelles (Méthode Indirecte)</td>
                                    </tr>
                                    
                                    <!-- CAF -->
                                    <tr class="section-row">
                                        <td colspan="{{ count($data['months']) + 2 }}" style="font-size: 0.75rem; color: #475569; background: #fff;">A. Capacité d'Autofinancement (CAF)</td>
                                    </tr>
                                    <tr>
                                        <td>Produits encaissables (+)</td>
                                        @foreach($data['months'] as $i => $m)
                                            <td class="text-success">+ {{ number_format($data['flux']['operationnel']['caf']['produits_encaissables'][$i], 0, ',', ' ') }}</td>
                                        @endforeach
                                        <td class="fw-bold">{{ number_format(array_sum($data['flux']['operationnel']['caf']['produits_encaissables']), 0, ',', ' ') }}</td>
                                    </tr>
                                    @if(request('detail'))
                                        @foreach($data['flux']['operationnel']['caf']['details']['produits'] as $compte)
                                        <tr class="detail-row">
                                            <td>{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                                            @foreach($data['months'] as $i => $m)
                                                <td>{{ isset($compte['months'][$i]) ? number_format($compte['months'][$i], 0, ',', ' ') : '-' }}</td>
                                            @endforeach
                                            <td>{{ number_format(array_sum($compte['months'] ?? []), 0, ',', ' ') }}</td>
                                        </tr>
                                        @endforeach
                                    @endif

                                    <tr>
                                        <td>Charges décaissables (-)</td>
                                        @foreach($data['months'] as $i => $m)
                                            <td class="text-danger">- {{ number_format($data['flux']['operationnel']['caf']['charges_decaissables'][$i], 0, ',', ' ') }}</td>
                                        @endforeach
                                        <td class="fw-bold">- {{ number_format(array_sum($data['flux']['operationnel']['caf']['charges_decaissables']), 0, ',', ' ') }}</td>
                                    </tr>
                                    @if(request('detail'))
                                        @foreach($data['flux']['operationnel']['caf']['details']['charges'] as $compte)
                                        <tr class="detail-row">
                                            <td>{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                                            @foreach($data['months'] as $i => $m)
                                                <td>- {{ isset($compte['months'][$i]) ? number_format($compte['months'][$i], 0, ',', ' ') : '-' }}</td>
                                            @endforeach
                                            <td>- {{ number_format(array_sum($compte['months'] ?? []), 0, ',', ' ') }}</td>
                                        </tr>
                                        @endforeach
                                    @endif

                                    <tr class="total-row">
                                        <td>Marge Brute d'Autofinancement (CAF)</td>
                                        @foreach($data['months'] as $i => $m)
                                            <td>{{ number_format($data['flux']['operationnel']['caf']['total'][$i], 0, ',', ' ') }}</td>
                                        @endforeach
                                        <td>{{ number_format(array_sum($data['flux']['operationnel']['caf']['total']), 0, ',', ' ') }}</td>
                                    </tr>

                                    <!-- BFR -->
                                    <tr class="section-row">
                                        <td colspan="{{ count($data['months']) + 2 }}" style="font-size: 0.75rem; color: #475569; background: #fff;">B. Variation du BFR</td>
                                    </tr>

                                    <tr>
                                        <td>Variation Stocks</td>
                                        @foreach($data['months'] as $i => $m)
                                            <td>{{ number_format($data['flux']['operationnel']['bfr']['variation_stocks'][$i], 0, ',', ' ') }}</td>
                                        @endforeach
                                        <td class="fw-bold">{{ number_format(array_sum($data['flux']['operationnel']['bfr']['variation_stocks']), 0, ',', ' ') }}</td>
                                    </tr>
                                    @if(request('detail'))
                                        @foreach($data['flux']['operationnel']['bfr']['details']['stocks'] as $compte)
                                        <tr class="detail-row">
                                            <td>{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                                            @foreach($data['months'] as $i => $m)
                                                <td>{{ isset($compte['months'][$i]) ? number_format($compte['months'][$i], 0, ',', ' ') : '-' }}</td>
                                            @endforeach
                                            <td>{{ number_format(array_sum($compte['months'] ?? []), 0, ',', ' ') }}</td>
                                        </tr>
                                        @endforeach
                                    @endif

                                    <tr>
                                        <td>Variation Créances</td>
                                        @foreach($data['months'] as $i => $m)
                                            <td>{{ number_format($data['flux']['operationnel']['bfr']['variation_creances'][$i], 0, ',', ' ') }}</td>
                                        @endforeach
                                        <td class="fw-bold">{{ number_format(array_sum($data['flux']['operationnel']['bfr']['variation_creances']), 0, ',', ' ') }}</td>
                                    </tr>
                                    @if(request('detail'))
                                        @foreach($data['flux']['operationnel']['bfr']['details']['creances'] as $compte)
                                        <tr class="detail-row">
                                            <td>{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                                            @foreach($data['months'] as $i => $m)
                                                <td>{{ isset($compte['months'][$i]) ? number_format($compte['months'][$i], 0, ',', ' ') : '-' }}</td>
                                            @endforeach
                                            <td>{{ number_format(array_sum($compte['months'] ?? []), 0, ',', ' ') }}</td>
                                        </tr>
                                        @endforeach
                                    @endif

                                     <tr>
                                        <td>Variation Dettes Circulantes</td>
                                        @foreach($data['months'] as $i => $m)
                                            <td>{{ number_format($data['flux']['operationnel']['bfr']['variation_dettes'][$i], 0, ',', ' ') }}</td>
                                        @endforeach
                                        <td class="fw-bold">{{ number_format(array_sum($data['flux']['operationnel']['bfr']['variation_dettes']), 0, ',', ' ') }}</td>
                                    </tr>
                                    @if(request('detail'))
                                        @foreach($data['flux']['operationnel']['bfr']['details']['dettes'] as $compte)
                                        <tr class="detail-row">
                                            <td>{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                                            @foreach($data['months'] as $i => $m)
                                                <td>{{ isset($compte['months'][$i]) ? number_format($compte['months'][$i], 0, ',', ' ') : '-' }}</td>
                                            @endforeach
                                            <td>{{ number_format(array_sum($compte['months'] ?? []), 0, ',', ' ') }}</td>
                                        </tr>
                                        @endforeach
                                    @endif

                                    <tr class="total-row">
                                        <td>Variation Totale du BFR</td>
                                        @foreach($data['months'] as $i => $m)
                                            <td>{{ number_format($data['flux']['operationnel']['bfr']['total'][$i], 0, ',', ' ') }}</td>
                                        @endforeach
                                        <td>{{ number_format(array_sum($data['flux']['operationnel']['bfr']['total']), 0, ',', ' ') }}</td>
                                    </tr>

                                    <tr class="main-total-row">
                                        <td>I. Flux Net Opérationnel (A + B)</td>
                                        @foreach($data['months'] as $i => $m)
                                            <td>{{ number_format($data['flux']['operationnel']['net'][$i], 0, ',', ' ') }}</td>
                                        @endforeach
                                        <td>{{ number_format(array_sum($data['flux']['operationnel']['net']), 0, ',', ' ') }}</td>
                                    </tr>

                                    <tr class="section-row">
                                        <td colspan="{{ count($data['months']) + 2 }}">II. Flux de trésorerie des activités d'investissement</td>
                                    </tr>
                                    <tr>
                                        <td>Cessions d'immobilisations (+)</td>
                                        @foreach($data['months'] as $i => $m)
                                            <td class="text-success">+ {{ number_format($data['flux']['investissement']['cessions'][$i], 0, ',', ' ') }}</td>
                                        @endforeach
                                        <td class="fw-bold">{{ number_format(array_sum($data['flux']['investissement']['cessions']), 0, ',', ' ') }}</td>
                                    </tr>
                                    <tr>
                                        <td>Acquisitions d'immobilisations (-)</td>
                                        @foreach($data['months'] as $i => $m)
                                            <td class="text-danger">- {{ number_format($data['flux']['investissement']['acquisitions'][$i], 0, ',', ' ') }}</td>
                                        @endforeach
                                        <td class="fw-bold">- {{ number_format(array_sum($data['flux']['investissement']['acquisitions']), 0, ',', ' ') }}</td>
                                    </tr>
                                    @if(request('detail'))
                                        @foreach($data['flux']['investissement']['details']['acquisitions'] as $compte)
                                        <tr class="detail-row">
                                            <td>{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                                            @foreach($data['months'] as $i => $m)
                                                <td>- {{ isset($compte['months'][$i]) ? number_format($compte['months'][$i], 0, ',', ' ') : '-' }}</td>
                                            @endforeach
                                            <td>- {{ number_format(array_sum($compte['months'] ?? []), 0, ',', ' ') }}</td>
                                        </tr>
                                        @endforeach
                                    @endif

                                    <tr class="main-total-row">
                                        <td>II. Flux Net Investissement</td>
                                        @foreach($data['months'] as $i => $m)
                                            <td>{{ number_format($data['flux']['investissement']['net'][$i], 0, ',', ' ') }}</td>
                                        @endforeach
                                        <td>{{ number_format(array_sum($data['flux']['investissement']['net']), 0, ',', ' ') }}</td>
                                    </tr>

                                    <tr class="section-row">
                                        <td colspan="{{ count($data['months']) + 2 }}">III. Flux de trésorerie des activités de financement</td>
                                    </tr>
                                    <tr>
                                        <td>Flux Net Financement</td>
                                        @foreach($data['months'] as $i => $m)
                                            <td>{{ number_format($data['flux']['financement']['net'][$i], 0, ',', ' ') }}</td>
                                        @endforeach
                                        <td class="fw-bold">{{ number_format(array_sum($data['flux']['financement']['net']), 0, ',', ' ') }}</td>
                                    </tr>
                                    @if(request('detail'))
                                        @foreach($data['flux']['financement']['details']['net'] as $compte)
                                        <tr class="detail-row">
                                            <td>{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                                            @foreach($data['months'] as $i => $m)
                                                <td>{{ isset($compte['months'][$i]) ? number_format($compte['months'][$i], 0, ',', ' ') : '-' }}</td>
                                            @endforeach
                                            <td>{{ number_format(array_sum($compte['months'] ?? []), 0, ',', ' ') }}</td>
                                        </tr>
                                        @endforeach
                                    @endif

                                    <tr class="main-total-row">
                                        <td>III. Flux Net Financement</td>
                                        @foreach($data['months'] as $i => $m)
                                            <td>{{ number_format($data['flux']['financement']['net'][$i], 0, ',', ' ') }}</td>
                                        @endforeach
                                        <td>{{ number_format(array_sum($data['flux']['financement']['net']), 0, ',', ' ') }}</td>
                                    </tr>

                                    <tr class="main-total-row" style="background: #0f172a;">
                                        <td>VARIATION DE TRÉSORERIE (I+II+III)</td>
                                        @foreach($data['months'] as $i => $m)
                                            <td>{{ number_format($data['flux']['tresorerie']['variation'][$i], 0, ',', ' ') }}</td>
                                        @endforeach
                                        <td>{{ number_format(array_sum($data['flux']['tresorerie']['variation']), 0, ',', ' ') }}</td>
                                    </tr>

                                    <tr class="total-row">
                                        <td>Solde Trésorerie Fin de Période (Cumulé)</td>
                                        @foreach($data['months'] as $i => $m)
                                            <td>{{ number_format($data['flux']['tresorerie']['solde_fin'][$i], 0, ',', ' ') }}</td>
                                        @endforeach
                                        <td>-</td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>

                    </div>
                    @include('components.footer')
                </div>
            </div>
        </div>
    </div>
</body>
</html>
