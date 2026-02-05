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
        padding: 10px 24px;
        font-weight: 700;
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.25);
        transition: all 0.3s ease;
    }
    .section-header-badge {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #6366f1;
        background: rgba(99, 102, 241, 0.1);
        padding: 6px 14px;
        border-radius: 8px;
        display: inline-block;
        margin-bottom: 1.5rem;
    }
    .table-premium {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 10px;
    }
    .table-premium tr.main-row {
        background: white;
        border-radius: 16px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .table-premium td {
        padding: 18px 20px;
        border: none;
    }
    .table-premium tr.main-row td:first-child { border-radius: 16px 0 0 16px; }
    .table-premium tr.main-row td:last-child { border-radius: 0 16px 16px 0; }
    
    .total-footer {
        background: #1e293b;
        color: white;
        padding: 24px 30px;
        border-radius: 20px;
        margin-top: 25px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 10px 25px rgba(30, 41, 59, 0.15);
    }
    .amount-font {
        font-family: 'Inter', monospace;
        font-weight: 800;
        letter-spacing: -0.5px;
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
        background-color: #f1f5f9;
        color: #475569;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 700;
        margin-right: 8px;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Bilan <span class="text-gradient">Actif/Passif</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Actions Header & Filters -->
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-6 gap-4">
                            <div>
                                <h3 class="fw-extrabold mb-1 text-premium-gradient">Situation Patrimoniale</h3>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-label-primary px-3 py-2 rounded-pill me-3">
                                        <i class="bx bx-calendar me-1"></i> {{ $exercice->intitule }}
                                    </span>
                                    <span class="text-muted small">Mise à jour en temps réel</span>
                                </div>
                            </div>

                            <!-- Filter Form -->
                            <form action="{{ route('reporting.bilan') }}" method="GET" class="d-flex align-items-center gap-3 bg-white p-2 rounded-3 shadow-sm" style="position: relative; z-index: 10;">
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

                                <div class="border-start ps-2 d-flex gap-2">
                                    <a href="{{ route('reporting.bilan.export', ['format' => 'pdf'] + request()->all()) }}" class="btn btn-sm btn-light text-danger" data-bs-toggle="tooltip" title="Exporter en PDF">
                                        <i class="bx bxs-file-pdf fs-4"></i>
                                    </a>
                                    <a href="{{ route('reporting.bilan.export', ['format' => 'excel'] + request()->all()) }}" class="btn btn-sm btn-light text-success" data-bs-toggle="tooltip" title="Exporter en Excel">
                                        <i class="bx bxs-file-json fs-4"></i>
                                    </a>
                                </div>
                            </form>
                        </div>

                        <div class="row g-6">
                            <!-- ACTIF -->
                            <div class="col-xl-6">
                                <div class="glass-card p-6 h-100 border-top border-4 border-primary">
                                    <div class="section-header-badge">Actif (Utilisation des fonds)</div>
                                    
                                    <table class="table-premium">
                                        <tbody>
                                            <!-- Actif Immobilisé -->
                                            <tr class="main-row">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar bg-label-primary p-2 rounded-circle me-4">
                                                            <i class="bx bx-buildings fs-4"></i>
                                                        </div>
                                                        <div>
                                                            <span class="fw-bold text-slate-800 d-block">Actif Immobilisé</span>
                                                            <small class="text-muted">Investissements long terme</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <h5 class="mb-0 amount-font">{{ number_format($data['actif']['immobilise']['total'], 0, ',', ' ') }}</h5>
                                                </td>
                                            </tr>
                                            @if(request('detail'))
                                            @if(!empty($data['actif']['immobilise']['details']))
                                            <tr>
                                                <td colspan="2" class="p-0">
                                                    <div class="details-container">
                                                        <table class="table-details">
                                                            @foreach($data['actif']['immobilise']['details'] as $item)
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
                                            @if(empty($data['actif']['immobilise']['details']))
                                            <tr><td colspan="2" class="p-4 text-center text-muted fst-italic small">Aucune écriture trouvée</td></tr>
                                            @endif
                                            @endif

                                            <!-- Actif Circulant -->
                                            <tr class="main-row">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar bg-label-info p-2 rounded-circle me-4">
                                                            <i class="bx bx-package fs-4"></i>
                                                        </div>
                                                        <div>
                                                            <span class="fw-bold text-slate-800 d-block">Actif Circulant</span>
                                                            <small class="text-muted">Stocks & Créances clients</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <h5 class="mb-0 amount-font">{{ number_format($data['actif']['circulant']['total'], 0, ',', ' ') }}</h5>
                                                </td>
                                            </tr>
                                            @if(request('detail'))
                                            @if(!empty($data['actif']['circulant']['details']))
                                            <tr>
                                                <td colspan="2" class="p-0">
                                                    <div class="details-container">
                                                        <table class="table-details">
                                                            @foreach($data['actif']['circulant']['details'] as $item)
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
                                            @if(empty($data['actif']['circulant']['details']))
                                            <tr><td colspan="2" class="p-4 text-center text-muted fst-italic small">Aucune écriture trouvée</td></tr>
                                            @endif
                                            @endif

                                            <!-- Trésorerie Actif -->
                                            <tr class="main-row">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar bg-label-success p-2 rounded-circle me-4">
                                                            <i class="bx bx-wallet fs-4"></i>
                                                        </div>
                                                        <div>
                                                            <span class="fw-bold text-slate-800 d-block">Trésorerie Actif</span>
                                                            <small class="text-muted">Liquidités disponibles</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <h5 class="mb-0 amount-font">{{ number_format($data['actif']['tresorerie']['total'], 0, ',', ' ') }}</h5>
                                                </td>
                                            </tr>
                                            @if(request('detail'))
                                            @if(!empty($data['actif']['tresorerie']['details']))
                                            <tr>
                                                <td colspan="2" class="p-0">
                                                    <div class="details-container">
                                                        <table class="table-details">
                                                            @foreach($data['actif']['tresorerie']['details'] as $item)
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
                                            @if(empty($data['actif']['tresorerie']['details']))
                                            <tr><td colspan="2" class="p-4 text-center text-muted fst-italic small">Aucune écriture trouvée</td></tr>
                                            @endif
                                            @endif
                                        </tbody>
                                    </table>

                                    <div class="total-footer bg-primary">
                                        <div class="d-flex flex-column">
                                            <small class="text-white opacity-75 fw-bold text-uppercase">Total Actif</small>
                                            <span class="fw-bold small">Somme des emplois</span>
                                        </div>
                                        <h3 class="mb-0 amount-font text-white">{{ number_format($data['actif']['total'], 0, ',', ' ') }} <small class="fs-6">FCFA</small></h3>
                                    </div>
                                </div>
                            </div>

                            <!-- PASSIF -->
                            <div class="col-xl-6">
                                <div class="glass-card p-6 h-100 border-top border-4 border-slate-800">
                                    <div class="section-header-badge" style="color: #1e293b; background: rgba(30, 41, 59, 0.1);">Passif (Sources des fonds)</div>
                                    
                                    <table class="table-premium">
                                        <tbody>
                                            <!-- Capitaux -->
                                            <tr class="main-row">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar bg-label-warning p-2 rounded-circle me-4">
                                                            <i class="bx bx-shield-quarter fs-4"></i>
                                                        </div>
                                                        <div>
                                                            <span class="fw-bold text-slate-800 d-block">Capitaux Propres</span>
                                                            <small class="text-muted">Apports & Réserves</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <h5 class="mb-0 amount-font">{{ number_format($data['passif']['capitaux']['total'], 0, ',', ' ') }}</h5>
                                                </td>
                                            </tr>
                                            @if(request('detail'))
                                            @if(!empty($data['passif']['capitaux']['details']))
                                            <tr>
                                                <td colspan="2" class="p-0">
                                                    <div class="details-container">
                                                        <table class="table-details">
                                                            @foreach($data['passif']['capitaux']['details'] as $item)
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
                                            @if(empty($data['passif']['capitaux']['details']))
                                            <tr><td colspan="2" class="p-4 text-center text-muted fst-italic small">Aucune écriture trouvée</td></tr>
                                            @endif
                                            @endif

                                            <!-- Dettes -->
                                            <tr class="main-row">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar bg-label-danger p-2 rounded-circle me-4">
                                                            <i class="bx bx-credit-card fs-4"></i>
                                                        </div>
                                                        <div>
                                                            <span class="fw-bold text-slate-800 d-block">Dettes</span>
                                                            <small class="text-muted">Fournisseurs & Emprunts</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <h5 class="mb-0 amount-font">{{ number_format($data['passif']['dettes']['total'], 0, ',', ' ') }}</h5>
                                                </td>
                                            </tr>
                                            @if(request('detail'))
                                            @if(!empty($data['passif']['dettes']['details']))
                                            <tr>
                                                <td colspan="2" class="p-0">
                                                    <div class="details-container">
                                                        <table class="table-details">
                                                            @foreach($data['passif']['dettes']['details'] as $item)
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
                                            @if(empty($data['passif']['dettes']['details']))
                                            <tr><td colspan="2" class="p-4 text-center text-muted fst-italic small">Aucune écriture trouvée</td></tr>
                                            @endif
                                            @endif

                                            <!-- Trésorerie Passif -->
                                            <tr class="main-row">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar bg-label-secondary p-2 rounded-circle me-4">
                                                            <i class="bx bx-money fs-4"></i>
                                                        </div>
                                                        <div>
                                                            <span class="fw-bold text-slate-800 d-block">Trésorerie Passif</span>
                                                            <small class="text-muted">Découverts bancaires</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <h5 class="mb-0 amount-font">{{ number_format($data['passif']['tresorerie']['total'], 0, ',', ' ') }}</h5>
                                                </td>
                                            </tr>
                                            @if(request('detail'))
                                            @if(!empty($data['passif']['tresorerie']['details']))
                                            <tr>
                                                <td colspan="2" class="p-0">
                                                    <div class="details-container">
                                                        <table class="table-details">
                                                            @foreach($data['passif']['tresorerie']['details'] as $item)
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
                                            @if(empty($data['passif']['tresorerie']['details']))
                                            <tr><td colspan="2" class="p-4 text-center text-muted fst-italic small">Aucune écriture trouvée</td></tr>
                                            @endif
                                            @endif
                                        </tbody>
                                    </table>

                                    <div class="total-footer">
                                        <div class="d-flex flex-column">
                                            <small class="text-white opacity-75 fw-bold text-uppercase">Total Passif</small>
                                            <span class="fw-bold small">Somme des ressources</span>
                                        </div>
                                        <h3 class="mb-0 amount-font text-white">{{ number_format($data['passif']['total'], 0, ',', ' ') }} <small class="fs-6">FCFA</small></h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Équilibre Status -->
                        <div class="mt-8">
                            @if(!$data['equilibre'])
                            <div class="status-banner bg-danger bg-opacity-10 border-danger d-flex align-items-center rounded-4 p-4">
                                <div class="avatar bg-danger rounded-circle p-3 me-5 shadow-lg">
                                    <i class="bx bx-error-alt text-white fs-2"></i>
                                </div>
                                <div>
                                    <h4 class="text-danger fw-extrabold mb-1">Déséquilibre Détecté</h4>
                                    <p class="text-danger mb-0 fs-5">
                                        Écart de <span class="fw-black amount-font">{{ number_format($data['difference'], 0, ',', ' ') }} FCFA</span>. Veuillez vérifier vos reports à nouveau ou vos écritures en attente.
                                    </p>
                                </div>
                            </div>
                            @else
                            <div class="status-banner bg-success bg-opacity-10 border-success d-flex align-items-center rounded-4 p-4">
                                <div class="avatar bg-success rounded-circle p-3 me-5 shadow-lg">
                                    <i class="bx bx-check-shield text-white fs-2"></i>
                                </div>
                                <div>
                                    <h4 class="text-success fw-extrabold mb-1">Bilan Parfaitement Équilibré</h4>
                                    <p class="text-success mb-0 fs-5">
                                        Cohérence totale entre les emplois et les ressources. Votre comptabilité est intègre.
                                    </p>
                                </div>
                            </div>
                            @endif
                        </div>

                    </div>
                    @include('components.footer')
                </div>
            </div>
        </div>
    </div>
</body>
</html>
