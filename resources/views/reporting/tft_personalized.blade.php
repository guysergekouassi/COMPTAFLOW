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
        overflow-x: auto;
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
        font-family: 'Plus Jakarta Sans', sans-serif;
        white-space: normal !important;
    }
    .table-matrix tr:hover td {
        background-color: #f1f5f9;
    }
    .section-row td {
        background-color: #f1f5f9;
        font-weight: 800;
        color: #0f172a;
        text-transform: uppercase;
        font-size: 0.85rem;
        border-top: 2px solid #cbd5e1;
    }
    .subsection-row td {
        background-color: #f8fafc;
        font-weight: 700;
        color: #475569;
        font-size: 0.75rem;
        text-transform: uppercase;
        padding-left: 20px !important;
    }
    .total-row td {
        background-color: #f8fafc;
        font-weight: 700;
        border-top: 1px solid #cbd5e1;
    }
    .activity-net-row td {
        background-color: #e2e8f0;
        font-weight: 800;
        color: #1e293b;
        border-top: 2px solid #94a3b8;
    }
    .main-total-row td {
        background-color: #0f172a !important;
        color: white !important;
        font-weight: 800;
    }
    .table-matrix tr.main-total-row:hover td {
        background-color: #0f172a !important;
        color: white !important;
    }
    .detail-row td {
        background-color: #fff;
        font-style: italic;
        color: #64748b;
        font-size: 0.8rem;
    }
    .detail-row td:first-child {
        padding-left: 40px !important;
        font-weight: 400;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Tableau des Flux de <span class="text-gradient">Trésorerie Mensuel</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Actions Header & Filters -->
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-6 gap-4">
                            <div>
                                <h3 class="fw-extrabold mb-1 text-premium-gradient">Tableau des Flux de Trésorerie Mensuel</h3>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-label-primary px-3 py-2 rounded-pill me-3">
                                        <i class="bx bx-calendar me-1"></i> {{ $exercice->intitule }}
                                    </span>
                                </div>
                            </div>

                            <!-- Filter Form -->
                            <form action="{{ route('reporting.tft_personalized') }}" method="GET" class="d-flex align-items-center gap-3 bg-white p-2 rounded-3 shadow-sm">
                                
                                <div class="switch-toggle">
                                    <input type="checkbox" id="detailSwitch" name="detail" value="1" {{ request('detail') ? 'checked' : '' }} onchange="this.form.submit()">
                                    <label for="detailSwitch">
                                        <i class="bx bx-list-ul"></i>
                                        {{ request('detail') ? 'Vue Détaillée' : 'Vue en Masse' }}
                                    </label>
                                </div>

                                <div class="border-start ps-2 d-flex gap-2">
                                    <a href="{{ route('reporting.tft_personalized.export', ['format' => 'pdf'] + request()->all()) }}" class="btn btn-sm btn-light text-danger" data-bs-toggle="tooltip" title="Exporter en PDF">
                                        <i class="bx bxs-file-pdf fs-4"></i>
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
                                    <h4 class="mb-2 fw-extrabold text-dark">{{ number_format(array_sum($data['activities']['operationnelle']['net']), 0, ',', ' ') }}</h4>
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
                                    <h4 class="mb-2 fw-extrabold text-dark">{{ number_format(array_sum($data['activities']['investissement']['net']), 0, ',', ' ') }}</h4>
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
                                    <h4 class="mb-2 fw-extrabold text-dark">{{ number_format(array_sum($data['activities']['financement']['net']), 0, ',', ' ') }}</h4>
                                    <div class="small fw-semibold text-warning">
                                        <i class="bx bx-line-chart me-1"></i> Capitaux
                                    </div>
                                </div>
                            </div>

                            <!-- Variation -->
                            @php
                                $varTotal = array_sum($data['global_net']);
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
                                $soldeFin = $data['cumule'][count($data['months'])-1] ?? 0;
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
                                    @php
                                        $activityNames = [
                                            'operationnelle' => 'I. Flux de trésorerie des activités opérationnelles',
                                            'investissement' => 'II. Flux de trésorerie des activités d\'investissement',
                                            'financement' => 'III. Flux de trésorerie des activités de financement'
                                        ];
                                    @endphp

                                    @foreach($data['activities'] as $key => $activity)
                                        <tr class="section-row">
                                            <td colspan="{{ count($data['months']) + 2 }}">{{ $activityNames[$key] }}</td>
                                        </tr>
                                        
                                        {{-- SOUS-SECTION ENCAISSEMENTS --}}
                                        <tr class="subsection-row">
                                            <td colspan="{{ count($data['months']) + 2 }}">ENCAISSEMENTS (+)</td>
                                        </tr>

                                        @if(request('detail'))
                                            @foreach($activity['encaissements']['categories'] as $category)
                                            <tr class="detail-row">
                                                <td>{{ $category['label'] }}</td>
                                                @foreach($data['months'] as $i => $m)
                                                    <td>{{ isset($category['data'][$i]) && $category['data'][$i] != 0 ? number_format($category['data'][$i], 0, ',', ' ') : '-' }}</td>
                                                @endforeach
                                                <td>{{ number_format(array_sum($category['data']), 0, ',', ' ') }}</td>
                                            </tr>
                                            @endforeach
                                        @endif

                                        <tr class="total-row">
                                            @php
                                                $suffix = $key == 'operationnelle' ? 'OPÉRATIONNELS' : ($key == 'investissement' ? 'D\'INVESTISSEMENT' : 'DE FINANCEMENT');
                                                $roman = $key == 'operationnelle' ? 'I' : ($key == 'investissement' ? 'II' : 'III');
                                            @endphp
                                            <td class="ps-4 fw-bold text-success">TOTAL DES ENCAISSEMENTS {{ $suffix }} ({{ $roman }})</td>
                                            @foreach($data['months'] as $i => $m)
                                                <td class="text-success fw-bold">{{ number_format($activity['encaissements']['total'][$i], 0, ',', ' ') }}</td>
                                            @endforeach
                                            <td class="text-success fw-bold">{{ number_format(array_sum($activity['encaissements']['total']), 0, ',', ' ') }}</td>
                                        </tr>

                                        {{-- SOUS-SECTION DÉCAISSEMENTS --}}
                                        <tr class="subsection-row">
                                            <td colspan="{{ count($data['months']) + 2 }}">DÉCAISSEMENTS (-)</td>
                                        </tr>

                                        @if(request('detail'))
                                            @foreach($activity['decaissements']['categories'] as $category)
                                            <tr class="detail-row">
                                                <td>{{ $category['label'] }}</td>
                                                @foreach($data['months'] as $i => $m)
                                                    <td>{{ number_format($category['data'][$i] ?? 0, 0, ',', ' ') }}</td>
                                                @endforeach
                                                <td>{{ number_format(array_sum($category['data']), 0, ',', ' ') }}</td>
                                            </tr>
                                            @endforeach
                                        @endif

                                        <tr class="total-row">
                                            @php
                                                $suffix = $key == 'operationnelle' ? 'OPÉRATIONNELS' : ($key == 'investissement' ? 'D\'INVESTISSEMENT' : 'DE FINANCEMENT');
                                                $roman = $key == 'operationnelle' ? 'I' : ($key == 'investissement' ? 'II' : 'III');
                                            @endphp
                                            <td class="ps-4 fw-bold text-danger">TOTAL DES DÉCAISSEMENTS {{ $suffix }} ({{ $roman }})</td>
                                            @foreach($data['months'] as $i => $m)
                                                <td class="text-danger fw-bold">{{ number_format($activity['decaissements']['total'][$i], 0, ',', ' ') }}</td>
                                            @endforeach
                                            <td class="text-danger fw-bold">{{ number_format(array_sum($activity['decaissements']['total']), 0, ',', ' ') }}</td>
                                        </tr>

                                        {{-- Flux Net de l'activité --}}
                                        <tr class="activity-net-row">
                                            @php
                                                $suffixNet = $key == 'operationnelle' ? 'OPÉRATIONNELLE' : ($key == 'investissement' ? 'D\'INVESTISSEMENT' : 'DE FINANCEMENT');
                                                $roman = $key == 'operationnelle' ? 'I' : ($key == 'investissement' ? 'II' : 'III');
                                            @endphp
                                            <td>FLUX NET DE L'ACTIVITÉ {{ $suffixNet }} ({{ $roman }})</td>
                                            @foreach($data['months'] as $i => $m)
                                                <td>{{ number_format($activity['net'][$i], 0, ',', ' ') }}</td>
                                            @endforeach
                                            <td>{{ number_format(array_sum($activity['net']), 0, ',', ' ') }}</td>
                                        </tr>
                                        <tr style="height: 15px;"><td colspan="{{ count($data['months']) + 2 }}" style="border: none;"></td></tr>
                                    @endforeach

                                    <!-- VARIATION FINALE -->
                                    <tr class="section-row" style="background-color: #0f172a; color: white;">
                                        <td colspan="{{ count($data['months']) + 2 }}">VARIATION GLOBALE ET TRÉSORERIE</td>
                                    </tr>

                                    <tr class="main-total-row">
                                        <td>VARIATION NETTE GLOBALE</td>
                                        @foreach($data['months'] as $i => $m)
                                            <td>{{ number_format($data['global_net'][$i], 0, ',', ' ') }}</td>
                                        @endforeach
                                        <td class="fw-bold">{{ number_format(array_sum($data['global_net']), 0, ',', ' ') }}</td>
                                    </tr>

                                    <tr class="main-total-row" style="border-top: 1px solid rgba(255,255,255,0.2);">
                                        <td class="fw-bold">TRÉSORERIE FINALE (CUMULÉE)</td>
                                        @foreach($data['months'] as $i => $m)
                                            <td class="fw-bold">{{ number_format($data['cumule'][$i], 0, ',', ' ') }}</td>
                                        @endforeach
                                        <td class="fw-bold">-</td>
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
