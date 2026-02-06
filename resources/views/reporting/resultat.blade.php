<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">

@include('components.head')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    body {
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        background-color: #f4f7fe;
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
    .amount-font {
        font-family: 'Inter', monospace;
        font-weight: 700;
    }
    .table-sig {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0 8px;
    }
    .table-sig tr.sig-row {
        background: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        transition: transform 0.2s ease;
    }
    .table-sig tr.sig-row:hover {
        transform: scale(1.005);
    }
    .table-sig td {
        padding: 15px 20px;
        border: none;
        vertical-align: middle;
    }
    .table-sig td:first-child { border-radius: 10px 0 0 10px; }
    .table-sig td:last-child { border-radius: 0 10px 10px 0; }
    
    .sig-label {
        font-weight: 600;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.85rem;
    }
    .sig-amount {
        font-family: 'Inter', monospace;
        font-weight: 700;
        font-size: 1rem;
        color: #1e293b;
    }
    .sig-main-row {
        background: linear-gradient(to right, #f8fafc, #ffffff) !important;
        border-left: 4px solid #6366f1;
    }
    .sig-main-label {
        color: #6366f1;
        font-weight: 800;
        font-size: 0.95rem;
    }
    
    /* Styles pour le mode détail */
    .detail-row {
        background-color: #f8fafc;
        font-size: 0.85rem;
    }
    .detail-row td {
        padding: 8px 20px;
        border-bottom: 1px solid #e2e8f0;
    }
    .detail-row:last-child td {
        border-bottom: none;
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
                @include('components.header', ['page_title' => 'Compte de <span class="text-gradient">Résultat</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Actions Header & Filters -->
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-6 gap-4">
                            <div>
                                <h3 class="fw-extrabold mb-1 text-premium-gradient">Soldes Intermédiaires de Gestion</h3>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-label-primary px-3 py-2 rounded-pill">
                                        <i class="bx bx-calendar me-1"></i> {{ $exercice->intitule }}
                                    </span>
                                </div>
                            </div>

                            <!-- Filter Form -->
                            <form action="{{ route('reporting.resultat') }}" method="GET" class="d-flex align-items-center gap-3 bg-white p-2 rounded-3 shadow-sm" style="position: relative; z-index: 10;">
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
                                    <a href="{{ route('reporting.resultat.export', ['format' => 'pdf'] + request()->all()) }}" class="btn btn-sm btn-light text-danger" data-bs-toggle="tooltip" title="Exporter en PDF">
                                        <i class="bx bxs-file-pdf fs-4"></i>
                                    </a>
                                    <a href="{{ route('reporting.resultat.export', ['format' => 'excel'] + request()->all()) }}" class="btn btn-sm btn-light text-success" data-bs-toggle="tooltip" title="Exporter en Excel">
                                        <i class="bx bxs-file-json fs-4"></i>
                                    </a>
                                </div>
                            </form>
                        </div>

                        <!-- Main Result KPI -->
                        <div class="d-flex gap-4 mb-6 overflow-x-auto pb-3" style="scroll-behavior: smooth;">
                            <div class="flex-shrink-0" style="min-width: 320px; flex: 1;">
                                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white;">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <p class="text-white text-opacity-75 text-uppercase fw-bold fs-7 mb-1">Chiffre d'Affaires</p>
                                                <h3 class="text-white mb-0 amount-font">{{ number_format($data['ventes_marchandises'] + $data['prod_vendue'], 0, ',', ' ') }}</h3>
                                            </div>
                                            <div class="avatar bg-white bg-opacity-10 rounded p-2">
                                                <i class="bx bx-store-alt text-white fs-4"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-shrink-0" style="min-width: 320px; flex: 1;">
                                <div class="card border-0 shadow-sm h-100 bg-white">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <p class="text-muted text-uppercase fw-bold fs-7 mb-1">Résultat d'Exploitation</p>
                                                <h3 class="mb-0 amount-font {{ $data['resultat_exploitation'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ number_format($data['resultat_exploitation'], 0, ',', ' ') }}
                                                </h3>
                                            </div>
                                            <div class="avatar bg-label-primary rounded p-2">
                                                <i class="bx bx-briefcase fs-4"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex-shrink-0" style="min-width: 320px; flex: 1;">
                                <div class="card border-0 shadow-sm h-100" style="background: {{ $data['resultat_net'] >= 0 ? 'linear-gradient(135deg, #059669 0%, #047857 100%)' : 'linear-gradient(135deg, #dc2626 0%, #b91c1c 100%)' }}; color: white;">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                            <div>
                                                <p class="text-white text-opacity-75 text-uppercase fw-bold fs-7 mb-1">Résultat Net</p>
                                                <h3 class="text-white mb-0 amount-font">{{ number_format($data['resultat_net'], 0, ',', ' ') }}</h3>
                                            </div>
                                            <div class="avatar bg-white bg-opacity-10 rounded p-2">
                                                <i class="bx {{ $data['resultat_net'] >= 0 ? 'bx-trending-up' : 'bx-trending-down' }} text-white fs-4"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SIG Table -->
                        <table class="table-sig">
                            <tbody>
                                <!-- 1. MARGE COMMERCIALE -->
                                <tr class="sig-row">
                                    <td><span class="sig-label">Ventes de marchandises</span></td>
                                    <td class="text-end text-success fw-bold">+ {{ number_format($data['ventes_marchandises'], 0, ',', ' ') }}</td>
                                </tr>
                                <tr class="sig-row">
                                    <td><span class="sig-label">Achats de marchandises</span> <small class="text-muted ms-2">(y compris variation stock)</small></td>
                                    <td class="text-end text-danger fw-bold">- {{ number_format($data['achats_marchandises'] + $data['var_stock_march'], 0, ',', ' ') }}</td>
                                </tr>
                                <tr class="sig-row sig-main-row">
                                    <td><span class="sig-main-label">MARGE COMMERCIALE</span></td>
                                    <td class="text-end"><span class="sig-amount">{{ number_format($data['marge_commerciale'], 0, ',', ' ') }}</span></td>
                                </tr>

                                <tr><td colspan="2" style="height: 10px;"></td></tr>

                                <!-- 2. VALEUR AJOUTEE -->
                                <tr class="sig-row">
                                    <td><span class="sig-label">Production de l'exercice</span></td>
                                    <td class="text-end text-success fw-bold">+ {{ number_format($data['production_exercice'], 0, ',', ' ') }}</td>
                                </tr>
                                <tr class="sig-row">
                                    <td><span class="sig-label">Consommation de l'exercice</span></td>
                                    <td class="text-end text-danger fw-bold">- {{ number_format($data['consommation_exercice'], 0, ',', ' ') }}</td>
                                </tr>
                                <tr class="sig-row sig-main-row">
                                    <td><span class="sig-main-label">VALEUR AJOUTÉE</span></td>
                                    <td class="text-end"><span class="sig-amount">{{ number_format($data['valeur_ajoutee'], 0, ',', ' ') }}</span></td>
                                </tr>

                                <tr><td colspan="2" style="height: 10px;"></td></tr>

                                <!-- 3. EXCEDENT BRUT D'EXPLOITATION -->
                                <tr class="sig-row">
                                    <td><span class="sig-label">Subventions d'exploitation</span></td>
                                    <td class="text-end text-success fw-bold">+ {{ number_format($data['subventions_expl'], 0, ',', ' ') }}</td>
                                </tr>
                                <tr class="sig-row">
                                    <td><span class="sig-label">Impôts et Taxes</span></td>
                                    <td class="text-end text-danger fw-bold">- {{ number_format($data['impots_taxes'], 0, ',', ' ') }}</td>
                                </tr>
                                <tr class="sig-row">
                                    <td><span class="sig-label">Charges de Personnel</span></td>
                                    <td class="text-end text-danger fw-bold">- {{ number_format($data['charges_personnel'], 0, ',', ' ') }}</td>
                                </tr>
                                <tr class="sig-row sig-main-row">
                                    <td><span class="sig-main-label">EXCÉDENT BRUT D'EXPLOITATION (EBE)</span></td>
                                    <td class="text-end"><span class="sig-amount">{{ number_format($data['ebe'], 0, ',', ' ') }}</span></td>
                                </tr>

                                <tr><td colspan="2" style="height: 10px;"></td></tr>

                                <!-- 4. RESULTAT D'EXPLOITATION -->
                                <tr class="sig-row">
                                    <td><span class="sig-label">Reprises d'amortissements et transferts de charges</span></td>
                                    <td class="text-end text-success fw-bold">+ {{ number_format($data['reprises_amort_prov'] + $data['transfert_charges'], 0, ',', ' ') }}</td>
                                </tr>
                                <tr class="sig-row">
                                    <td><span class="sig-label">Dotations aux amortissements</span></td>
                                    <td class="text-end text-danger fw-bold">- {{ number_format($data['dotations_amort_prov'], 0, ',', ' ') }}</td>
                                </tr>
                                <tr class="sig-row sig-main-row">
                                    <td><span class="sig-main-label">RÉSULTAT D'EXPLOITATION</span></td>
                                    <td class="text-end"><span class="sig-amount">{{ number_format($data['resultat_exploitation'], 0, ',', ' ') }}</span></td>
                                </tr>

                                <tr><td colspan="2" style="height: 10px;"></td></tr>

                                <!-- 5. RESULTAT FINANCIER -->
                                <tr class="sig-row">
                                    <td><span class="sig-label">Revenus financiers et assimilés</span></td>
                                    <td class="text-end text-success fw-bold">+ {{ number_format($data['revenus_financiers'] + $data['reprises_fin'] + $data['transfert_fin'], 0, ',', ' ') }}</td>
                                </tr>
                                <tr class="sig-row">
                                    <td><span class="sig-label">Frais financiers et assimilés</span></td>
                                    <td class="text-end text-danger fw-bold">- {{ number_format($data['frais_financiers'] + $data['dotations_fin'], 0, ',', ' ') }}</td>
                                </tr>
                                <tr class="sig-row sig-main-row">
                                    <td><span class="sig-main-label">RÉSULTAT FINANCIER</span></td>
                                    <td class="text-end"><span class="sig-amount">{{ number_format($data['resultat_financier'], 0, ',', ' ') }}</span></td>
                                </tr>

                                <tr><td colspan="2" style="height: 10px;"></td></tr>
                                
                                <tr class="sig-row bg-light">
                                    <td><span class="sig-main-label text-dark">RÉSULTAT DES ACTIVITÉS ORDINAIRES (R.A.O)</span></td>
                                    <td class="text-end"><span class="sig-amount">{{ number_format($data['resultat_activites_ordinaires'], 0, ',', ' ') }}</span></td>
                                </tr>
                                
                                <tr><td colspan="2" style="height: 10px;"></td></tr>

                                <!-- 6. RESULTAT HAO -->
                                <tr class="sig-row">
                                    <td><span class="sig-label">Produits H.A.O</span></td>
                                    <td class="text-end text-success fw-bold">+ {{ number_format($data['produits_hao'], 0, ',', ' ') }}</td>
                                </tr>
                                <tr class="sig-row">
                                    <td><span class="sig-label">Charges H.A.O</span></td>
                                    <td class="text-end text-danger fw-bold">- {{ number_format($data['charges_hao'], 0, ',', ' ') }}</td>
                                </tr>
                                <tr class="sig-row sig-main-row">
                                    <td><span class="sig-main-label">RÉSULTAT H.A.O</span></td>
                                    <td class="text-end"><span class="sig-amount">{{ number_format($data['resultat_hao'], 0, ',', ' ') }}</span></td>
                                </tr>
                                
                                <tr><td colspan="2" style="height: 10px;"></td></tr>
                                
                                <!-- 7. RESULTAT NET -->
                                <tr class="sig-row">
                                    <td><span class="sig-label">Impôts sur le Résultat</span></td>
                                    <td class="text-end text-danger fw-bold">- {{ number_format($data['impots_resultat'], 0, ',', ' ') }}</td>
                                </tr>
                                
                                <tr class="sig-row" style="background: #1e293b !important; color: white;">
                                    <td><span class="sig-main-label text-white">RÉSULTAT NET</span></td>
                                    <td class="text-end"><span class="sig-amount text-white">{{ number_format($data['resultat_net'], 0, ',', ' ') }}</span></td>
                                </tr>
                            </tbody>
                        </table>
                        
                         <!-- DETAILS COMPTES (Affichés seulement si mode détail actif) -->
                        <!-- DETAILS COMPTES (Affichés seulement si mode détail actif) -->
                        @if(request('detail'))
                            @if(!empty($data['details']))
                            <div class="mt-8">
                                <h5 class="fw-bold mb-4 text-premium-gradient">Détail des Comptes</h5>
                                
                                @foreach($data['details'] as $category => $items)
                                    <div class="card mb-4 border-0 shadow-sm">
                                        <div class="card-header bg-white border-bottom fw-bold text-uppercase small text-muted">
                                            {{ $category }}
                                        </div>
                                        <div class="card-body p-0">
                                            <table class="table mb-0">
                                                <tbody>
                                                    @foreach($items as $item)
                                                        <tr class="detail-row">
                                                            <td style="width: 15%;">
                                                                <span class="account-badge">{{ $item['numero'] }}</span>
                                                            </td>
                                                            <td>{{ $item['intitule'] }}</td>
                                                            <td class="text-end fw-bold">{{ number_format($item['solde'], 0, ',', ' ') }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @else
                            <div class="mt-8">
                                <div class="alert alert-secondary d-flex align-items-center" role="alert">
                                    <i class="bx bx-info-circle fs-4 me-2"></i>
                                    <div>
                                        <strong>Mode Détail Activé :</strong> Aucune écriture détaillée n'a été trouvée pour les critères sélectionnés.
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endif

                        <div class="alert bg-label-info border-0 mt-8 rounded-4 p-4 d-flex align-items-center">
                            <i class="bx bx-info-circle fs-3 me-4"></i>
                            <div class="small">
                                Ce tableau des Soldes Intermédiaires de Gestion (SIG) est généré selon les normes SYSCOHADA Révisé.
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
