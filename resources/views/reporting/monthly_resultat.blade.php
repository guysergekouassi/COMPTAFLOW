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
        z-index: 10;
    }
    .table-matrix td {
        color: #1e293b;
        text-align: right;
        font-family: 'Inter', monospace;
    }
    .table-matrix td:first-child {
        text-align: left;
        font-weight: 600;
        position: sticky;
        left: 0;
        background: white;
        z-index: 20;
        border-right: 1px solid #e2e8f0;
        min-width: 250px;
    }
    .table-matrix tr:hover td {
        background-color: #f1f5f9;
    }
    /* Fix sticky column hover background */
    .table-matrix tr:hover td:first-child {
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
                @include('components.header', ['page_title' => 'Compte de Résultat <span class="text-gradient">Mensuel</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Actions Header & Filters -->
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-6 gap-4">
                            <div>
                                <h3 class="fw-extrabold mb-1 text-premium-gradient">Compte d'Exploitation Mensuel</h3>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-label-primary px-3 py-2 rounded-pill me-3">
                                        <i class="bx bx-calendar me-1"></i> {{ $exercice->intitule }}
                                    </span>
                                </div>
                            </div>

                            <!-- Filter Form -->
                            <form action="{{ route('reporting.monthly_resultat') }}" method="GET" class="d-flex align-items-center gap-3 bg-white p-2 rounded-3 shadow-sm">
                                
                                <div class="switch-toggle">
                                    <input type="checkbox" id="detailSwitch" name="detail" value="1" {{ request('detail') ? 'checked' : '' }} onchange="this.form.submit()">
                                    <label for="detailSwitch">
                                        <i class="bx bx-list-ul"></i>
                                        {{ request('detail') ? 'Vue Détaillée' : 'Vue en Masse' }}
                                    </label>
                                </div>

                                <div class="border-start ps-2 d-flex gap-2">
                                    <a href="{{ route('reporting.monthly_resultat.export', ['format' => 'pdf'] + request()->all()) }}" class="btn btn-sm btn-light text-danger" data-bs-toggle="tooltip" title="Exporter en PDF">
                                        <i class="bx bxs-file-pdf fs-4"></i>
                                    </a>
                                    <a href="{{ route('reporting.monthly_resultat.export', ['format' => 'excel'] + request()->all()) }}" class="btn btn-sm btn-light text-success" data-bs-toggle="tooltip" title="Exporter en Excel">
                                        <i class="bx bxs-file-json fs-4"></i>
                                    </a>
                                </div>
                            </form>
                        </div>

                        <!-- Summary Cards -->
                        <div class="d-flex align-items-stretch gap-4 mb-4" style="overflow-x: auto; padding-bottom: 5px;">
                            <!-- Produits -->
                            <div class="card border-0 shadow-sm rounded-4 flex-grow-1" style="min-width: 250px;">
                                <div class="card-body p-4 position-relative">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h6 class="text-uppercase text-muted fw-bold small mb-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Total Produits</h6>
                                        <div class="p-2 rounded-3 bg-label-success text-success">
                                            <i class="bx bx-trending-up fs-4"></i>
                                        </div>
                                    </div>
                                    <h4 class="mb-2 fw-extrabold text-success">{{ number_format(array_sum($data['data']['produits']['total']), 0, ',', ' ') }}</h4>
                                </div>
                            </div>

                            <!-- Charges -->
                            <div class="card border-0 shadow-sm rounded-4 flex-grow-1" style="min-width: 250px;">
                                <div class="card-body p-4 position-relative">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h6 class="text-uppercase text-muted fw-bold small mb-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Total Charges</h6>
                                        <div class="p-2 rounded-3 bg-label-danger text-danger">
                                            <i class="bx bx-trending-down fs-4"></i>
                                        </div>
                                    </div>
                                    <h4 class="mb-2 fw-extrabold text-danger">{{ number_format(array_sum($data['data']['charges']['total']), 0, ',', ' ') }}</h4>
                                </div>
                            </div>

                            <!-- Résultat -->
                            @php
                                $resultatTotal = array_sum($data['data']['resultat']);
                                $resColor = $resultatTotal >= 0 ? 'primary' : 'warning';
                            @endphp
                            <div class="card border-0 shadow-sm rounded-4 flex-grow-1" style="min-width: 250px;">
                                <div class="card-body p-4 position-relative">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h6 class="text-uppercase text-muted fw-bold small mb-0" style="font-size: 0.7rem; letter-spacing: 0.5px;">Résultat Net</h6>
                                        <div class="p-2 rounded-3 bg-label-{{ $resColor }} text-{{ $resColor }}">
                                            <i class="bx bx-wallet fs-4"></i>
                                        </div>
                                    </div>
                                    <h4 class="mb-2 fw-extrabold text-{{ $resColor }}">{{ number_format($resultatTotal, 0, ',', ' ') }} <span class="fs-6 text-muted fw-normal">FCFA</span></h4>
                                </div>
                            </div>
                        </div>

                        <div class="glass-card">
                            <table class="table-matrix">
                                <thead>
                                    <tr>
                                        <th>Rubrique</th>
                                        @foreach($data['months'] as $month)
                                            <th class="text-center">{{ $month['name'] }}</th>
                                        @endforeach
                                        <th class="text-center" style="background: #1e293b; color: white;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- PRODUITS -->
                                    <tr class="section-row">
                                        <td colspan="{{ count($data['months']) + 2 }}" class="text-success">Produits / Chiffre d'Affaires</td>
                                    </tr>
                                    
                                    @foreach($data['data']['produits'] as $key => $row)
                                        @if($key !== 'total')
                                            <tr>
                                                <td class="fw-bold">{{ $row['label'] }}</td>
                                                @foreach($data['months'] as $i => $m)
                                                    <td>{{ number_format($row['data'][$i], 0, ',', ' ') }}</td>
                                                @endforeach
                                                <td class="fw-bold bg-light">{{ number_format(array_sum($row['data']), 0, ',', ' ') }}</td>
                                            </tr>
                                            @if(request('detail') && !empty($row['details']))
                                                @foreach($row['details'] as $compte)
                                                <tr class="detail-row">
                                                    <td>{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                                                    @foreach($data['months'] as $i => $m)
                                                        <td>{{ isset($compte['data'][$i]) ? number_format($compte['data'][$i], 0, ',', ' ') : '-' }}</td>
                                                    @endforeach
                                                    <td>{{ number_format(array_sum($compte['data']), 0, ',', ' ') }}</td>
                                                </tr>
                                                @endforeach
                                            @endif
                                        @endif
                                    @endforeach

                                    <tr class="total-row text-success">
                                        <td>TOTAL PRODUITS</td>
                                        @foreach($data['months'] as $i => $m)
                                            <td>{{ number_format($data['data']['produits']['total'][$i], 0, ',', ' ') }}</td>
                                        @endforeach
                                        <td style="background: #dcfce7 !important;">{{ number_format(array_sum($data['data']['produits']['total']), 0, ',', ' ') }}</td>
                                    </tr>

                                    <!-- CHARGES -->
                                    <tr class="section-row">
                                        <td colspan="{{ count($data['months']) + 2 }}" class="text-danger" style="margin-top: 20px; display: table-cell;">Charges / Dépenses</td>
                                    </tr>
                                    
                                    @foreach($data['data']['charges'] as $key => $row)
                                        @if($key !== 'total')
                                            <tr>
                                                <td class="fw-bold">{{ $row['label'] }}</td>
                                                @foreach($data['months'] as $i => $m)
                                                    <td>{{ number_format($row['data'][$i], 0, ',', ' ') }}</td>
                                                @endforeach
                                                <td class="fw-bold bg-light">{{ number_format(array_sum($row['data']), 0, ',', ' ') }}</td>
                                            </tr>
                                            @if(request('detail') && !empty($row['details']))
                                                @foreach($row['details'] as $compte)
                                                <tr class="detail-row">
                                                    <td>{{ $compte['numero'] }} - {{ $compte['intitule'] }}</td>
                                                    @foreach($data['months'] as $i => $m)
                                                        <td>{{ isset($compte['data'][$i]) ? number_format($compte['data'][$i], 0, ',', ' ') : '-' }}</td>
                                                    @endforeach
                                                    <td>{{ number_format(array_sum($compte['data']), 0, ',', ' ') }}</td>
                                                </tr>
                                                @endforeach
                                            @endif
                                        @endif
                                    @endforeach

                                    <tr class="total-row text-danger">
                                        <td>TOTAL CHARGES</td>
                                        @foreach($data['months'] as $i => $m)
                                            <td>{{ number_format($data['data']['charges']['total'][$i], 0, ',', ' ') }}</td>
                                        @endforeach
                                        <td style="background: #fee2e2 !important;">{{ number_format(array_sum($data['data']['charges']['total']), 0, ',', ' ') }}</td>
                                    </tr>

                                    <!-- RÉSULTAT -->
                                    <tr style="height: 20px;"><td colspan="{{ count($data['months']) + 2 }}"></td></tr>
                                    
                                    <tr style="background-color: #1e293b; color: white; font-weight: 800; font-size: 1rem;">
                                        <td style="background-color: #1e293b; color: white;">RÉSULTAT NET</td>
                                        @foreach($data['months'] as $i => $m)
                                            <td style="color: {{ $data['data']['resultat'][$i] >= 0 ? '#4ade80' : '#f87171' }};">
                                                {{ number_format($data['data']['resultat'][$i], 0, ',', ' ') }}
                                            </td>
                                        @endforeach
                                        <td style="background-color: #0f172a; color: white;">
                                            {{ number_format(array_sum($data['data']['resultat']), 0, ',', ' ') }}
                                        </td>
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
