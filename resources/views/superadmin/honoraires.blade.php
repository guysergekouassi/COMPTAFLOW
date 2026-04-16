<!doctype html>
<html lang="fr" class="layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">

@include('components.head')
<meta name="google" content="notranslate">
<meta http-equiv="Content-Language" content="fr">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
/* ═══════════════════════════════════════════════
   DESIGN SYSTEM — HONORAIRES
═══════════════════════════════════════════════ */
:root {
    --hon-rhflow:     #6366f1;
    --hon-comptaflow: #0ea5e9;
    --hon-taskflow:   #f59e0b;
    --hon-selflow:    #10b981;
    --hon-legalflow:  #8b5cf6;
    --radius-card: 18px;
    --shadow-card: 0 4px 24px rgba(0,0,0,.07);
    --shadow-hover: 0 12px 40px rgba(0,0,0,.13);
}

body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f8fafc; }

/* ── Gradient Badge ── */
.text-gradient { background: linear-gradient(135deg,#0f172a,#334155); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }

/* ── Global Summary Card ── */
.global-card {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%);
    border-radius: 24px;
    padding: 2.5rem;
    color: #fff;
    position: relative; overflow: hidden;
    box-shadow: 0 20px 60px rgba(15,23,42,.35);
}
.global-card::before {
    content:''; position:absolute; top:-60px; right:-60px;
    width:220px; height:220px;
    background: radial-gradient(circle, rgba(99,102,241,.3) 0%, transparent 70%);
    border-radius:50%;
}
.global-card-stat { text-align:center; }
.global-card-stat .amount { font-size:2.2rem; font-weight:800; letter-spacing:-1px; }
.global-card-stat .label  { font-size:.75rem; text-transform:uppercase; letter-spacing:.1em; opacity:.65; margin-top:.3rem; }
.global-divider { width:1px; background:rgba(255,255,255,.15); min-height:60px; align-self:center; }

/* ── App Cards ── */
.app-card {
    background:#fff;
    border-radius: var(--radius-card);
    box-shadow: var(--shadow-card);
    border: 1px solid rgba(226,232,240,.8);
    padding: 1.75rem;
    cursor: pointer;
    transition: all .3s cubic-bezier(.4,0,.2,1);
    position: relative; overflow: hidden;
}
.app-card::after {
    content:''; position:absolute; bottom:0; left:0; right:0;
    height:4px; border-radius:0 0 var(--radius-card) var(--radius-card);
    opacity:0; transition: opacity .3s;
}
.app-card:hover { transform:translateY(-6px); box-shadow: var(--shadow-hover); }
.app-card:hover::after { opacity:1; }

.app-card[data-app="RHFLOW"]     { --app-color: var(--hon-rhflow);     }
.app-card[data-app="COMPTAFLOW"] { --app-color: var(--hon-comptaflow); }
.app-card[data-app="TASKFLOW"]   { --app-color: var(--hon-taskflow);   }
.app-card[data-app="SELFLOW"]    { --app-color: var(--hon-selflow);    }
.app-card[data-app="LEGALFLOW"]  { --app-color: var(--hon-legalflow);  }

.app-card::after { background: var(--app-color); }

.app-icon {
    width:52px; height:52px; border-radius:14px;
    display:flex; align-items:center; justify-content:center;
    font-size:1.3rem; color:#fff;
    background: var(--app-color);
    box-shadow: 0 6px 20px rgba(0,0,0,.15);
    flex-shrink:0;
}

.app-name { font-size:1rem; font-weight:800; color:#0f172a; }
.app-metric { text-align:right; }
.app-metric .value { font-size:1.4rem; font-weight:800; color:#0f172a; }
.app-metric .unit  { font-size:.7rem; color:#94a3b8; font-weight:600; text-transform:uppercase; }

.stat-pill {
    display:inline-flex; align-items:center; gap:.4rem;
    padding:.25rem .75rem; border-radius:999px; font-size:.72rem; font-weight:600;
}
.stat-pill.pending  { background:#fef3c7; color:#92400e; }
.stat-pill.overdue  { background:#fee2e2; color:#991b1b; }
.stat-pill.paid     { background:#d1fae5; color:#065f46; }

.pack-badge {
    font-size:.68rem; font-weight:700; padding:.2rem .6rem;
    border-radius:6px; letter-spacing:.05em;
    background: color-mix(in srgb, var(--app-color) 12%, white);
    color: var(--app-color);
    border:1px solid color-mix(in srgb, var(--app-color) 25%, white);
}

/* ── Service Cards ── */
.service-card {
    background:#fff;
    border-radius: 14px;
    box-shadow: var(--shadow-card);
    border: 1px solid rgba(226,232,240,.8);
    padding:1rem;
    transition: all .3s ease;
    position:relative; overflow:hidden;
    display:flex; flex-direction:column;
}
.service-card:hover { transform:translateY(-4px); box-shadow: var(--shadow-hover); }
.service-icon {
    width:36px; height:36px; border-radius:10px;
    display:flex; align-items:center; justify-content:center;
    font-size:.95rem; color:#fff;
    flex-shrink:0;
}
.decl-badge {
    font-size:.62rem; font-weight:700; padding:.15rem .45rem;
    border-radius:6px; letter-spacing:.04em;
}

/* ── Company Table ── */
.company-row { transition: background .15s; }
.company-row:hover { background:#f8fafc; }

/* ── Section Titles ── */
.section-title {
    display:flex; align-items:center; justify-content:space-between;
    padding-bottom:.8rem;
    border-bottom:2px solid #f1f5f9;
    margin-bottom:1.5rem;
}
.section-title-left {
    display:flex; align-items:center; gap:.55rem;
    font-size:.95rem; font-weight:800; color:#0f172a;
}
.section-title-left i { font-size:.9rem; flex-shrink:0; }

/* ── Modal ── */
.modal-card {
    background:#fff; border-radius:16px;
    box-shadow:0 25px 80px rgba(0,0,0,.2);
    border:1px solid #e2e8f0;
}
.calc-box {
    background:linear-gradient(135deg,#f8fafc,#f1f5f9);
    border-radius:12px; padding:1rem 1.25rem;
    border:1px solid #e2e8f0;
    display:flex; flex-direction:column; gap:.2rem;
}
.calc-box .c-label { font-size:.7rem; color:#94a3b8; text-transform:uppercase; font-weight:700; }
.calc-box .c-amount{ font-size:1.3rem; font-weight:800; color:#0f172a; }

/* ── Responsive ── */
@media (max-width:768px) {
    .global-card { padding:1.5rem; }
    .global-card-stat .amount { font-size:1.6rem; }
}
</style>

<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('components.sidebar')

        <div class="layout-page">
            @include('components.header', ['page_title' => 'Honoraires & Abonnements'])

            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">

                    {{-- ═══════════════════════════════════════════════
                         HEADER PAGE
                    ═══════════════════════════════════════════════ --}}
                    <div class="d-flex justify-content-between align-items-center mb-6">
                        <div>
                            <h5 class="mb-1 text-gradient">Honoraires & Abonnements — Vue Globale</h5>
                            <p class="text-muted small mb-0">Suivi des revenus par application et par service pour chaque client.</p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            {{-- Sélecteur de période (filtre) --}}
                            <div style="position:relative;">
                                <input type="month"
                                       id="filtre-periode"
                                       value="{{ request('periode', now()->format('Y-m')) }}"
                                       style="appearance:none;-webkit-appearance:none;
                                              background:#fff;
                                              border:1.5px solid #e2e8f0;
                                              border-radius:10px;
                                              padding:.45rem 1rem .45rem 2.4rem;
                                              font-size:.8rem;
                                              font-weight:700;
                                              color:#0f172a;
                                              cursor:pointer;
                                              box-shadow:0 2px 8px rgba(0,0,0,.06);
                                              outline:none;
                                              transition:border-color .2s;"
                                       onchange="filtrerParPeriode(this.value)"
                                       title="Filtrer par période">
                                <i class="fa-solid fa-calendar-days"
                                   style="position:absolute;left:.7rem;top:50%;transform:translateY(-50%);color:#6366f1;font-size:.8rem;pointer-events:none;"></i>
                            </div>
                            <button id="btn-parametres" onclick="toggleParametres()"
                                    class="btn btn-sm d-flex align-items-center gap-2"
                                    style="background:linear-gradient(135deg,#ef4444,#dc2626);color:#fff;border:none;border-radius:10px;font-size:.78rem;font-weight:700;padding:.5rem 1rem;box-shadow:0 4px 14px rgba(239,68,68,.3);transition:all .3s;">
                                <i class="fa-solid fa-sliders"></i>
                                <span id="btn-param-label">Paramètres</span>
                            </button>
                        </div>
                    </div>

                    {{-- ═══════════════════════════════════════════════
                         CARTE GLOBALE
                    ═══════════════════════════════════════════════ --}}
                    <div class="global-card mb-6">
                        <div class="row align-items-center g-4">
                            <div class="col-12 col-md-4">
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <div style="width:48px;height:48px;background:rgba(255,255,255,.15);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;">
                                        💰
                                    </div>
                                    <div>
                                        <div style="font-size:.7rem;text-transform:uppercase;letter-spacing:.12em;opacity:.6;font-weight:700;">Bilan Financier Global</div>
                                        <div style="font-size:1.05rem;font-weight:800;opacity:.9;">Toutes Applications & Services</div>
                                    </div>
                                </div>
                                <div style="font-size:2.6rem;font-weight:900;letter-spacing:-2px;line-height:1.1;">
                                    {{ number_format($grandTotalDu, 0, ',', ' ') }}
                                </div>
                                <div style="font-size:.8rem;opacity:.6;font-weight:600;margin-top:.3rem;">FCFA — Total cumulé dû</div>
                            </div>
                            <div class="col-12 col-md-8">
                                <div class="d-flex flex-wrap gap-3 justify-content-md-end">
                                    <div class="global-card-stat">
                                        <div class="amount">{{ number_format($grandTotalMensuel, 0, ',', ' ') }}</div>
                                        <div class="label">Revenu Mensuel Global</div>
                                    </div>
                                    <div class="global-divider d-none d-md-block"></div>
                                    <div class="global-card-stat">
                                        <div class="amount">{{ number_format($totalAppsDu, 0, ',', ' ') }}</div>
                                        <div class="label">Dû — Applications</div>
                                    </div>
                                    <div class="global-divider d-none d-md-block"></div>
                                    <div class="global-card-stat">
                                        <div class="amount">{{ number_format($totalServicesDu, 0, ',', ' ') }}</div>
                                        <div class="label">Dû — Services</div>
                                    </div>
                                    <div class="global-divider d-none d-md-block"></div>
                                    <div class="global-card-stat">
                                        <div class="amount">{{ number_format($grandTotalMensuel * 12, 0, ',', ' ') }}</div>
                                        <div class="label">Projection Annuelle</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ═══════════════════════════════════════════════
                         SECTION APPLICATIONS
                    ═══════════════════════════════════════════════ --}}
                    <div class="section-title">
                        <div class="section-title-left">
                            <i class="fa-solid fa-grid-2 text-primary"></i>
                            Abonnements par Application
                        </div>
                        <span class="badge bg-primary-subtle text-primary">{{ $totalAppsClients }} contrats</span>
                    </div>

                    @php
                        $appsMeta = [
                            'RHFLOW'     => ['icon'=>'fa-users',         'color'=>'#6366f1', 'desc'=>'Gestion RH & Paie'],
                            'COMPTAFLOW' => ['icon'=>'fa-calculator',    'color'=>'#0ea5e9', 'desc'=>'Comptabilité SYSCOHADA'],
                            'TASKFLOW'   => ['icon'=>'fa-list-check',    'color'=>'#f59e0b', 'desc'=>'Gestion des Tâches'],
                            'SELFLOW'    => ['icon'=>'fa-chart-bar',     'color'=>'#10b981', 'desc'=>'CRM & Ventes'],
                            'LEGALFLOW'  => ['icon'=>'fa-gavel',         'color'=>'#8b5cf6', 'desc'=>'LegalTech & Conformité'],
                        ];
                    @endphp

                    <div class="row g-3 mb-6">
                        @foreach($statsParApp as $app => $stat)
                            @php $meta = $appsMeta[$app]; @endphp
                            <div class="col-6 col-md-4 col-xl">
                                <div class="app-card" data-app="{{ $app }}"
                                     style="--app-color:{{ $meta['color'] }}"
                                     onclick="openAppModal('{{ $app }}')"
                                     role="button" title="Voir détails {{ $app }}">

                                    {{-- Header --}}
                                    <div class="d-flex align-items-start justify-content-between mb-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="app-icon">
                                                <i class="fa-solid {{ $meta['icon'] }}"></i>
                                            </div>
                                            <div>
                                                <div class="app-name" translate="no">{{ $app }}</div>
                                                <div style="font-size:.72rem;color:#64748b;font-weight:500;">{{ $meta['desc'] }}</div>
                                            </div>
                                        </div>
                                        <i class="fa-solid fa-arrow-up-right-from-square" style="color:#cbd5e1;font-size:.8rem;margin-top:.2rem;"></i>
                                    </div>

                                    {{-- Metric Principal --}}
                                    <div class="d-flex justify-content-between align-items-end mb-3">
                                        <div>
                                            <div style="font-size:.7rem;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.08em;">Total dû</div>
                                            <div class="app-metric">
                                                <span class="value">{{ number_format($stat['total_du'], 0, ',', ' ') }}</span>
                                                <span class="unit ms-1">FCFA</span>
                                            </div>
                                        </div>
                                        <div style="text-align:right;">
                                            <div style="font-size:.7rem;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.08em;">Mensuel</div>
                                            <div style="font-size:1rem;font-weight:700;color:#334155;">{{ number_format($stat['total_mensuel'], 0, ',', ' ') }} <span style="font-size:.65rem;color:#94a3b8;">FCFA</span></div>
                                        </div>
                                    </div>

                                    {{-- Footer Stats --}}
                                    <div class="d-flex align-items-center justify-content-between pt-3" style="border-top:1px solid #f1f5f9;">
                                        <div style="font-size:.75rem;color:#64748b;font-weight:600;">
                                            <i class="fa-solid fa-building me-1" style="color:{{ $meta['color'] }};"></i>
                                            {{ $stat['total_clients'] }} client{{ $stat['total_clients'] > 1 ? 's' : '' }}
                                        </div>
                                        <div class="d-flex gap-1">
                                            @if($stat['en_attente'] > 0)
                                                <span class="stat-pill pending">{{ $stat['en_attente'] }} en attente</span>
                                            @endif
                                            @if($stat['en_retard'] > 0)
                                                <span class="stat-pill overdue">{{ $stat['en_retard'] }} retard</span>
                                            @endif
                                            @if($stat['en_attente'] == 0 && $stat['en_retard'] == 0)
                                                <span class="stat-pill paid">✓ À jour</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- ═══════════════════════════════════════════════
                         SECTION SERVICES PROFESSIONNELS
                    ═══════════════════════════════════════════════ --}}
                    <div class="section-title">
                        <div class="section-title-left">
                            <i class="fa-solid fa-briefcase text-warning"></i>
                            Services Professionnels
                        </div>
                        <span class="badge bg-warning-subtle text-warning">{{ $totalServicesClients }} contrats</span>
                    </div>

                    @php
                        $declColors = ['CNPS'=>'#10b981','FNE'=>'#0ea5e9','CMU'=>'#6366f1','TE'=>'#f59e0b'];
                    @endphp

                    <div style="display:grid; grid-template-columns: repeat(4, 1fr); gap:1rem; margin-bottom:2rem;">
                        @foreach($statsParService as $key => $data)
                            @php $cat = $data['catalogue']; @endphp
                            <div class="service-card">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="service-icon" style="background:{{ $cat['color'] }}">
                                        <i class="fa-solid {{ $cat['icon'] }}"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight:800;color:#0f172a;font-size:.82rem;" translate="no">{{ $cat['label'] }}</div>
                                        <div style="font-size:.66rem;color:#94a3b8;font-weight:500;">
                                            {{ $data['total_clients'] }} client{{ $data['total_clients'] > 1 ? 's' : '' }}
                                        </div>
                                    </div>
                                </div>

                                <p style="font-size:.68rem;color:#64748b;line-height:1.5;margin-bottom:.6rem;">
                                    {{ $cat['description'] }}
                                </p>

                                {{-- Déclarations incluses --}}
                                @if(!empty($cat['declarations']))
                                    <div style="margin-bottom:.6rem;">
                                        <div style="font-size:.6rem;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-bottom:.25rem;">Déclarations incluses</div>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($cat['declarations'] as $decl)
                                                <span class="decl-badge" style="background:{{ $declColors[$decl] ?? '#94a3b8' }}1a;color:{{ $declColors[$decl] ?? '#94a3b8' }};border:1px solid {{ $declColors[$decl] ?? '#94a3b8' }}30;" translate="no">
                                                    {{ $decl }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- Prix & Total --}}
                                <div class="d-flex justify-content-between align-items-center pt-2 mt-auto" style="border-top:1px solid #f1f5f9;">
                                    @if($data['total_du'] > 0)
                                        <div>
                                            <div style="font-size:.6rem;color:#94a3b8;font-weight:700;text-transform:uppercase;">Total dû</div>
                                            <div style="font-size:.9rem;font-weight:800;color:#0f172a;">
                                                {{ number_format($data['total_du'], 0, ',', ' ') }}
                                                <span style="font-size:.6rem;color:#94a3b8;font-weight:600;">FCFA</span>
                                            </div>
                                        </div>
                                    @else
                                        <span style="font-size:.68rem;color:#94a3b8;font-style:italic;">Prix à définir</span>
                                    @endif
                                    <span style="font-size:.6rem;padding:.2rem .5rem;border-radius:6px;background:{{ $cat['color'] }}15;color:{{ $cat['color'] }};font-weight:700;" translate="no">
                                        {{ $cat['label'] }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>


                    {{-- ═══════════════════════════════════════════════
                         TABLEAU DES CLIENTS
                    ═══════════════════════════════════════════════ --}}
                    <div class="section-title">
                        <div class="section-title-left">
                            <i class="fa-solid fa-building text-success"></i>
                            Récapitulatif par Client
                        </div>
                        <span class="badge bg-success-subtle text-success">{{ $companies->count() }} client(s)</span>
                    </div>

                    <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" style="font-size:.82rem;">
                                    <thead style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                                        <tr>
                                            <th class="ps-4 py-3 fw-700 text-slate-600">Client</th>
                                            <th class="py-3 fw-700 text-slate-600">Applications souscrites</th>
                                            <th class="py-3 fw-700 text-slate-600">Services actifs</th>
                                            <th class="py-3 fw-700 text-slate-600 text-end">Revenu mensuel</th>
                                            <th class="py-3 fw-700 text-slate-600 text-end">Total dû</th>
                                            <th class="py-3 fw-700 text-slate-600 text-center">Statuts</th>
                                            <th class="pe-4 py-3"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($companies as $company)
                                            <tr class="company-row">
                                                <td class="ps-4 py-3">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#6366f1,#8b5cf6);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:.8rem;flex-shrink:0;">
                                                            {{ strtoupper(substr($company->company_name, 0, 2)) }}
                                                        </div>
                                                        <div>
                                                            <div class="fw-600 text-dark">{{ $company->company_name }}</div>
                                                            <div style="font-size:.68rem;color:#94a3b8;">{{ $company->city ?? 'N/A' }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="py-3">
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($company->appSubscriptions->groupBy('app_name') as $app => $subs)
                                                            <span style="font-size:.65rem;font-weight:700;padding:.2rem .55rem;border-radius:6px;background:{{ $appsMeta[$app]['color'] ?? '#94a3b8' }}15;color:{{ $appsMeta[$app]['color'] ?? '#94a3b8' }};border:1px solid {{ $appsMeta[$app]['color'] ?? '#94a3b8' }}30;">
                                                                {{ $app }}
                                                            </span>
                                                        @endforeach
                                                        @if($company->appSubscriptions->isEmpty())
                                                            <span style="color:#94a3b8;font-size:.72rem;font-style:italic;">Aucun</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="py-3">
                                                    <div class="d-flex flex-wrap gap-1">
                                                        @foreach($company->serviceHonoraires as $srv)
                                                            @php $cat = \App\Models\ServiceHonoraire::catalogue()[$srv->service_name] ?? null; @endphp
                                                            @if($cat)
                                                                <span style="font-size:.65rem;font-weight:700;padding:.2rem .55rem;border-radius:6px;background:{{ $cat['color'] }}15;color:{{ $cat['color'] }};border:1px solid {{ $cat['color'] }}30;">
                                                                    {{ $cat['label'] }}
                                                                </span>
                                                            @endif
                                                        @endforeach
                                                        @if($company->serviceHonoraires->isEmpty())
                                                            <span style="color:#94a3b8;font-size:.72rem;font-style:italic;">Aucun</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="py-3 text-end fw-700 text-dark">
                                                    {{ number_format(
                                                        $company->appSubscriptions->sum('prix_mensuel') +
                                                        $company->serviceHonoraires->sum('prix_mensuel'), 0, ',', ' '
                                                    ) }}
                                                    <span style="font-size:.65rem;color:#94a3b8;">FCFA</span>
                                                </td>
                                                <td class="py-3 text-end fw-800" style="color:#0f172a;">
                                                    {{ number_format($company->total_du, 0, ',', ' ') }}
                                                    <span style="font-size:.65rem;color:#94a3b8;">FCFA</span>
                                                </td>
                                                <td class="py-3 text-center">
                                                    @php
                                                        $allStatuts = $company->appSubscriptions->pluck('statut_paiement')
                                                            ->merge($company->serviceHonoraires->pluck('statut_paiement'));
                                                        $hasOverdue = $allStatuts->contains('overdue');
                                                        $hasPending = $allStatuts->contains('pending');
                                                    @endphp
                                                    @if($hasOverdue)
                                                        <span class="stat-pill overdue">En retard</span>
                                                    @elseif($hasPending)
                                                        <span class="stat-pill pending">En attente</span>
                                                    @else
                                                        <span class="stat-pill paid">À jour</span>
                                                    @endif
                                                </td>
                                                <td class="pe-4 py-3">
                                                    <button class="btn btn-sm btn-outline-primary rounded-pill"
                                                            onclick="openClientModal({{ $company->id }}, '{{ addslashes($company->company_name) }}')"
                                                            style="font-size:.7rem;padding:.25rem .75rem;">
                                                        <i class="fa-solid fa-eye me-1"></i>Détail
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center py-5 text-muted">
                                                    <i class="fa-solid fa-inbox fa-2x mb-2 d-block opacity-30"></i>
                                                    Aucun client avec un abonnement actif.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <br><br>


                    {{-- ═══════════════════════════════════════════════
                         SECTION PARAMÈTRES
                    ═══════════════════════════════════════════════ --}}
                    <div id="section-parametres" style="display:none;" class="mb-6">

                        <div class="section-title">
                            <i class="fa-solid fa-sliders text-danger"></i>
                            Paramétrage — Catalogue de Prix
                            <span class="badge bg-danger-subtle text-danger ms-auto">SuperAdmin uniquement</span>
                        </div>

                        {{-- Info --}}
                        <div class="alert alert-info d-flex align-items-start gap-3 mb-4" style="border-radius:14px;border:none;background:linear-gradient(135deg,#eff6ff,#dbeafe);">
                            <i class="fa-solid fa-circle-info text-primary mt-1"></i>
                            <div>
                                <div class="fw-700 text-primary mb-1" style="font-size:.85rem;">Comment fonctionne le paramétrage ?</div>
                                <div style="font-size:.78rem;color:#475569;line-height:1.6;">
                                    Les prix définis ici s'appliquent comme <strong>prix catalogue de référence</strong> pour les nouveaux abonnements.
                                    Vous pouvez marquer un pack comme <strong>«&nbsp;Sur mesure&nbsp;»</strong> (prix négocié au cas par cas) ou le <strong>désactiver</strong> pour le masquer.
                                </div>
                            </div>
                        </div>

                        {{-- Onglets --}}
                        <div class="card border-0 shadow-sm" style="border-radius:18px;overflow:hidden;">
                            <div class="card-header border-0 p-0" style="background:#f8fafc;">
                                <ul class="nav nav-tabs border-0" id="paramTabs" style="padding:.75rem 1.25rem 0;">
                                    @php
                                        $tabApps = ['RHFLOW'=>'#6366f1','COMPTAFLOW'=>'#0ea5e9','TASKFLOW'=>'#f59e0b','SELFLOW'=>'#10b981','LEGALFLOW'=>'#8b5cf6'];
                                    @endphp
                                    @foreach($tabApps as $tApp => $tColor)
                                        <li class="nav-item">
                                            <button class="nav-link {{ $loop->first ? 'active' : '' }}" data-bs-toggle="tab"
                                                    data-bs-target="#tab-{{ strtolower($tApp) }}"
                                                    translate="no"
                                                    style="font-size:.78rem;font-weight:700;border-radius:10px 10px 0 0;
                                                           color:{{ $tColor }};border:2px solid transparent;">
                                                {{ $tApp }}
                                            </button>
                                        </li>
                                    @endforeach
                                    <li class="nav-item">
                                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-services"
                                                style="font-size:.78rem;font-weight:700;border-radius:10px 10px 0 0;color:#f59e0b;">
                                            <i class="fa-solid fa-briefcase me-1"></i>Services
                                        </button>
                                    </li>
                                </ul>
                            </div>

                            <div class="card-body p-4">
                                <div class="tab-content" id="paramTabsContent">

                                    {{-- Tab par Application --}}
                                    @php
                                        $appsParam = [
                                            'RHFLOW'     => ['Basic', 'Pro', 'Basic Edge', 'Pro Edge', 'Pro Max', 'Pro Master', 'Pro Day'],
                                            'COMPTAFLOW' => ['Starter', 'Pro', 'Enterprise'],
                                            'TASKFLOW'   => ['Starter', 'Pro', 'Enterprise'],
                                            'SELFLOW'    => ['Starter', 'Pro', 'Enterprise'],
                                            'LEGALFLOW'  => ['Starter', 'Pro', 'Enterprise'],
                                        ];
                                        $defaultPrix = [
                                            'RHFLOW'     => ['Basic'=>5000,'Pro'=>10000,'Basic Edge'=>20000,'Pro Edge'=>35000,'Pro Max'=>50000,'Pro Master'=>null,'Pro Day'=>null],
                                            'COMPTAFLOW' => ['Starter'=>15000,'Pro'=>30000,'Enterprise'=>60000],
                                            'TASKFLOW'   => ['Starter'=>8000,'Pro'=>18000,'Enterprise'=>40000],
                                            'SELFLOW'    => ['Starter'=>10000,'Pro'=>22000,'Enterprise'=>45000],
                                            'LEGALFLOW'  => ['Starter'=>12000,'Pro'=>28000,'Enterprise'=>55000],
                                        ];
                                    @endphp

                                    @foreach($tabApps as $tabApp => $tabColor)
                                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-{{ strtolower($tabApp) }}">
                                            <div class="row g-2">
                                                @foreach($appsParam[$tabApp] as $packName)
                                                    @php
                                                        $defPrix = $defaultPrix[$tabApp][$packName] ?? null;
                                                        $isSurMesure = is_null($defPrix);
                                                    @endphp
                                                    <div class="col-6 col-md-4 col-lg-3">
                                                        <div class="param-card" style="border:1.5px solid {{ $tabColor }}20;border-radius:12px;padding:1rem;background:{{ $tabColor }}05;position:relative;">
                                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                                <div>
                                                                    <div style="font-weight:800;font-size:.82rem;color:#0f172a;" translate="no">{{ $packName }}</div>
                                                                    <div style="font-size:.62rem;color:#94a3b8;font-weight:600;text-transform:uppercase;letter-spacing:.08em;" translate="no">{{ $tabApp }}</div>
                                                                </div>
                                                                <span class="sur-mesure-badge {{ $isSurMesure ? '' : 'd-none' }}" id="badge-{{ strtolower($tabApp) }}-{{ preg_replace('/[^a-z0-9]+/', '-', strtolower($packName)) }}"
                                                                      style="font-size:.62rem;font-weight:700;padding:.2rem .55rem;border-radius:6px;background:#f59e0b20;color:#92400e;border:1px solid #f59e0b40;">
                                                                    Sur mesure
                                                                </span>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label style="font-size:.68rem;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:.08em;display:block;margin-bottom:.35rem;">
                                                                    Prix mensuel (FCFA)
                                                                </label>
                                                                <div class="input-group input-group-sm">
                                                                    <input type="number"
                                                                           id="prix-{{ strtolower($tabApp) }}-{{ preg_replace('/[^a-z0-9]+/', '-', strtolower($packName)) }}"
                                                                           value="{{ $defPrix ?? '' }}"
                                                                           placeholder="{{ $isSurMesure ? 'Sur mesure' : 'Entrer le prix' }}"
                                                                           {{ $isSurMesure ? 'disabled' : '' }}
                                                                           min="0" step="500"
                                                                           class="form-control prix-input"
                                                                           style="border-radius:8px 0 0 8px;font-weight:700;font-size:.85rem;border-color:{{ $tabColor }}40;"
                                                                           data-app="{{ $tabApp }}" data-pack="{{ $packName }}">
                                                                    <span class="input-group-text" style="font-size:.72rem;font-weight:700;background:{{ $tabColor }}12;border-color:{{ $tabColor }}40;color:{{ $tabColor }};">FCFA</span>
                                                                </div>
                                                            </div>

                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <label class="d-flex align-items-center gap-2" style="font-size:.72rem;font-weight:600;color:#64748b;cursor:pointer;">
                                                                    <input type="checkbox" class="form-check-input sur-mesure-toggle" style="width:14px;height:14px;"
                                                                           {{ $isSurMesure ? 'checked' : '' }}
                                                                           data-app="{{ $tabApp }}" data-pack="{{ $packName }}"
                                                                           data-target="prix-{{ strtolower($tabApp) }}-{{ preg_replace('/[^a-z0-9]+/', '-', strtolower($packName)) }}"
                                                                           data-badge="badge-{{ strtolower($tabApp) }}-{{ preg_replace('/[^a-z0-9]+/', '-', strtolower($packName)) }}">
                                                                    Sur mesure
                                                                </label>
                                                                <button class="btn btn-sm save-prix-btn"
                                                                        style="font-size:.68rem;padding:.3rem .8rem;border-radius:8px;background:{{ $tabColor }};color:#fff;font-weight:700;border:none;"
                                                                        data-type="app"
                                                                        data-app="{{ $tabApp }}"
                                                                        data-pack="{{ $packName }}"
                                                                        data-input="prix-{{ strtolower($tabApp) }}-{{ preg_replace('/[^a-z0-9]+/', '-', strtolower($packName)) }}"
                                                                        data-surm="badge-{{ strtolower($tabApp) }}-{{ preg_replace('/[^a-z0-9]+/', '-', strtolower($packName)) }}">
                                                                    <i class="fa-solid fa-floppy-disk me-1"></i>Sauv.
                                                                </button>
                                                            </div>

                                                            {{-- Projection --}}
                                                            <div class="mt-3 pt-2" style="border-top:1px dashed {{ $tabColor }}25;">
                                                                <div class="d-flex justify-content-between" style="font-size:.7rem;color:#94a3b8;">
                                                                    <span>/ année :</span>
                                                                    <span class="fw-700 annee-calc" id="annee-{{ strtolower($tabApp) }}-{{ preg_replace('/[^a-z0-9]+/', '-', strtolower($packName)) }}" style="color:{{ $tabColor }};">
                                                                        {{ $defPrix ? number_format($defPrix * 12, 0, ',', ' ') . ' FCFA' : '—' }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach

                                    {{-- Tab Services --}}
                                    <div class="tab-pane fade" id="tab-services">
                                        <div class="row g-3">
                                            @php
                                                $servicesParam = ['COMPTABILITE','FISCALITE','DROIT','JURIDIQUE','SOCIAL','AUDIT','CONSEIL'];
                                                $serviceColors = ['COMPTABILITE'=>'#0ea5e9','FISCALITE'=>'#6366f1','DROIT'=>'#8b5cf6','JURIDIQUE'=>'#8b5cf6','SOCIAL'=>'#10b981','AUDIT'=>'#f59e0b','CONSEIL'=>'#ec4899'];
                                                $serviceIcons  = ['COMPTABILITE'=>'fa-calculator','FISCALITE'=>'fa-landmark','DROIT'=>'fa-gavel','JURIDIQUE'=>'fa-handshake','SOCIAL'=>'fa-users','AUDIT'=>'fa-magnifying-glass-chart','CONSEIL'=>'fa-lightbulb'];
                                            @endphp
                                            @foreach($servicesParam as $svc)
                                                @php $sc = $serviceColors[$svc] ?? '#94a3b8'; @endphp
                                                <div class="col-12 col-sm-6 col-md-4">
                                                    <div class="param-card" style="border:1.5px solid {{ $sc }}20;border-radius:14px;padding:1.25rem;background:{{ $sc }}05;">
                                                        <div class="d-flex align-items-center gap-3 mb-3">
                                                            <div style="width:38px;height:38px;border-radius:10px;background:{{ $sc }};display:flex;align-items:center;justify-content:center;color:#fff;font-size:.9rem;flex-shrink:0;">
                                                                <i class="fa-solid {{ $serviceIcons[$svc] ?? 'fa-briefcase' }}"></i>
                                                            </div>
                                                            <div>
                                                                <div style="font-weight:800;font-size:.85rem;color:#0f172a;">{{ $svc }}</div>
                                                                <div style="font-size:.68rem;color:#94a3b8;font-weight:600;">Service Professionnel</div>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label style="font-size:.68rem;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:.08em;display:block;margin-bottom:.35rem;">
                                                                Prix mensuel (FCFA)
                                                            </label>
                                                            <div class="input-group input-group-sm">
                                                                <input type="number"
                                                                       id="prix-service-{{ strtolower($svc) }}"
                                                                       placeholder="À définir"
                                                                       min="0" step="500"
                                                                       class="form-control prix-input"
                                                                       style="border-radius:8px 0 0 8px;font-weight:700;font-size:.85rem;border-color:{{ $sc }}40;"
                                                                       data-app="" data-pack="{{ $svc }}">
                                                                <span class="input-group-text" style="font-size:.72rem;font-weight:700;background:{{ $sc }}12;border-color:{{ $sc }}40;color:{{ $sc }};">FCFA</span>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <label class="d-flex align-items-center gap-2" style="font-size:.72rem;font-weight:600;color:#64748b;cursor:pointer;">
                                                                <input type="checkbox" class="form-check-input sur-mesure-toggle" style="width:14px;height:14px;"
                                                                       data-app="" data-pack="{{ $svc }}"
                                                                       data-target="prix-service-{{ strtolower($svc) }}"
                                                                       data-badge="">
                                                                Sur mesure
                                                            </label>
                                                            <button class="btn btn-sm save-prix-btn"
                                                                    style="font-size:.68rem;padding:.3rem .8rem;border-radius:8px;background:{{ $sc }};color:#fff;font-weight:700;border:none;"
                                                                    data-type="service"
                                                                    data-app=""
                                                                    data-pack="{{ $svc }}"
                                                                    data-input="prix-service-{{ strtolower($svc) }}"
                                                                    data-surm="">
                                                                <i class="fa-solid fa-floppy-disk me-1"></i>Sauvegarder
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
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
    <div class="layout-overlay layout-menu-toggle"></div>
</div>

{{-- ═══════════════════════════════════════════════
     MODAL DÉTAIL CLIENT
═══════════════════════════════════════════════ --}}
<div class="modal fade" id="clientModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content modal-card border-0">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div>
                    <h5 class="modal-title fw-800 text-dark" id="clientModalTitle">Détail Honoraires</h5>
                    <p class="text-muted small mb-0" id="clientModalSubtitle">Calcul par période</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-4" id="clientModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted small">Chargement…</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════
     MODAL PAR APPLICATION (liste des clients)
═══════════════════════════════════════════════ --}}
<div class="modal fade" id="appModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modal-card border-0">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-800" id="appModalTitle">Clients — Application</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-4" id="appModalBody"></div>
        </div>
    </div>
</div>

<script>
// ═══════════════════════════════════════════════
// DONNÉES (passées depuis PHP)
// ═══════════════════════════════════════════════
const allSubscriptions = @json($subsForJs);

const appColors = {
    RHFLOW:     '#6366f1',
    COMPTAFLOW: '#0ea5e9',
    TASKFLOW:   '#f59e0b',
    SELFLOW:    '#10b981',
    LEGALFLOW:  '#8b5cf6',
};

const fmt = (n) => new Intl.NumberFormat('fr-FR').format(Math.round(n));

// ═══════════════════════════════════════════════
// MODAL APP (Clients de cette app)
// ═══════════════════════════════════════════════
function openAppModal(app) {
    const color = appColors[app] || '#64748b';
    const subs  = allSubscriptions.filter(s => s.app_name === app);

    document.getElementById('appModalTitle').innerHTML =
        `<span style="color:${color};"><i class="fa-solid fa-grid-2 me-2"></i></span> ${app} — ${subs.length} client(s)`;

    let html = '';
    if (!subs.length) {
        html = '<p class="text-muted text-center py-4">Aucun abonnement pour cette application.</p>';
    } else {
        html = `<div class="table-responsive"><table class="table table-hover" style="font-size:.82rem;">
            <thead style="background:#f8fafc;"><tr>
                <th>Client</th><th>Pack</th><th class="text-end">Mensuel</th>
                <th class="text-end">Mois écoulés</th><th class="text-end">Total dû</th><th>Statut</th>
            </tr></thead><tbody>`;
        subs.forEach(s => {
            const statBadge = s.statut === 'paid' ? 'success' : s.statut === 'pending' ? 'warning' : 'danger';
            const statutFr = s.statut === 'paid' ? 'À jour' : s.statut === 'pending' ? 'En attente' : 'En retard';
            html += `<tr>
                <td class="fw-600">${s.company_name}</td>
                <td><span style="font-size:.68rem;font-weight:700;padding:.2rem .6rem;border-radius:6px;background:${color}15;color:${color};border:1px solid ${color}30;">${s.pack_name}</span></td>
                <td class="text-end fw-700">${fmt(s.prix_mensuel)} FCFA</td>
                <td class="text-end">${s.mois} mois</td>
                <td class="text-end fw-800" style="color:#0f172a;">${fmt(s.total_du)} FCFA</td>
                <td><span class="badge bg-${statBadge}-subtle text-${statBadge} fw-700">${statutFr}</span></td>
            </tr>`;
        });
        html += '</tbody></table></div>';
        const total = subs.reduce((acc, s) => acc + s.total_du, 0);
        html += `<div class="p-3 rounded-3 text-end fw-800" style="background:#f8fafc;border:1px solid #e2e8f0;color:#0f172a;font-size:1rem;">
            Total dû (${app}) : <span style="color:${color};">${fmt(total)} FCFA</span>
        </div>`;
    }

    document.getElementById('appModalBody').innerHTML = html;
    new bootstrap.Modal(document.getElementById('appModal')).show();
}

// ═══════════════════════════════════════════════
// MODAL CLIENT (Détail complet avec calculs)
// ═══════════════════════════════════════════════
async function openClientModal(companyId, companyName) {
    document.getElementById('clientModalTitle').textContent = companyName;
    document.getElementById('clientModalSubtitle').textContent = 'Chargement des données…';
    document.getElementById('clientModalBody').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-2 text-muted small">Chargement…</div>
        </div>`;
    new bootstrap.Modal(document.getElementById('clientModal')).show();

    try {
        const res  = await fetch(`/superadmin/honoraires/${companyId}`);
        const data = await res.json();

        document.getElementById('clientModalSubtitle').textContent =
            `Total dû : ${fmt(data.total_du)} FCFA`;

        let html = '';

        // ── Abonnements Apps ──────────────────────────────────────────────────
        if (data.subscriptions.length) {
            html += `<div class="mb-4">
                <div style="font-size:.8rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:#64748b;margin-bottom:1rem;">
                    <i class="fa-solid fa-grid-2 me-2 text-primary"></i>Applications
                </div>
                <div class="row g-3">`;

            data.subscriptions.forEach(s => {
                const color = appColors[s.app] || '#64748b';
                const statColor = s.statut === 'paid' ? '#10b981' : s.statut === 'pending' ? '#f59e0b' : '#ef4444';
                html += `<div class="col-12 col-md-6">
                    <div style="border:1px solid ${color}25;border-radius:14px;padding:1.25rem;background:${color}04;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div style="font-weight:800;color:#0f172a;">${s.app}</div>
                            <span style="font-size:.68rem;font-weight:700;padding:.25rem .7rem;border-radius:6px;background:${color}15;color:${color};border:1px solid ${color}30;">${s.pack}</span>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="calc-box">
                                    <div class="c-label">Par mois</div>
                                    <div class="c-amount">${fmt(s.prix_mensuel)}</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="calc-box">
                                    <div class="c-label">Par année</div>
                                    <div class="c-amount">${fmt(s.par_annee)}</div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center" style="border-top:1px solid #f1f5f9;padding-top:.75rem;">
                            <div style="font-size:.72rem;color:#64748b;">${s.mois_ecoules} mois écoulés (depuis ${s.date_debut})</div>
                            <div class="d-flex align-items-center gap-2">
                                <div style="font-size:1rem;font-weight:800;color:${color};">${fmt(s.total_du)} FCFA</div>
                                <span style="width:8px;height:8px;border-radius:50%;background:${statColor};display:inline-block;"></span>
                            </div>
                        </div>
                    </div>
                </div>`;
            });
            html += '</div></div>';
        }

        // ── Services ─────────────────────────────────────────────────────────
        if (data.services.length) {
            html += `<div class="mb-3">
                <div style="font-size:.8rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:#64748b;margin-bottom:1rem;">
                    <i class="fa-solid fa-briefcase me-2 text-warning"></i>Services Professionnels
                </div>
                <div class="row g-3">`;

            data.services.forEach(s => {
                const prix = s.prix_mensuel ? `${fmt(s.prix_mensuel)} FCFA` : '— Prix à définir';
                const total = s.total_du ? `${fmt(s.total_du)} FCFA` : '—';
                const declHtml = (s.declarations || []).map(d =>
                    `<span style="font-size:.65rem;font-weight:700;padding:.15rem .5rem;border-radius:5px;background:#f59e0b1a;color:#92400e;border:1px solid #f59e0b30;">${d}</span>`
                ).join(' ');

                html += `<div class="col-12 col-md-6">
                    <div style="border:1px solid #e2e8f0;border-radius:14px;padding:1.25rem;background:#fafbfc;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div style="font-weight:800;color:#0f172a;">${s.service}</div>
                            <span style="font-size:.7rem;font-weight:700;color:${s.statut==='paid'?'#065f46':s.statut==='pending'?'#92400e':'#991b1b'};">${s.statut}</span>
                        </div>
                        ${declHtml ? `<div class="d-flex flex-wrap gap-1 mb-2">${declHtml}</div>` : ''}
                        <div class="d-flex justify-content-between align-items-center pt-2" style="border-top:1px solid #f1f5f9;">
                            <span style="font-size:.75rem;color:#64748b;">${prix} / mois</span>
                            <span style="font-size:.95rem;font-weight:800;color:#0f172a;">${total}</span>
                        </div>
                    </div>
                </div>`;
            });
            html += '</div></div>';
        }

        // ── Grand Total ───────────────────────────────────────────────────────
        html += `<div class="p-4 rounded-3 mt-2" style="background:linear-gradient(135deg,#0f172a,#1e3a5f);color:#fff;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div style="font-size:.7rem;opacity:.6;text-transform:uppercase;letter-spacing:.1em;font-weight:700;">Total général dû</div>
                    <div style="font-size:.85rem;opacity:.8;">${data.company} — Tous services & applications</div>
                </div>
                <div style="font-size:2rem;font-weight:900;letter-spacing:-1px;">${fmt(data.total_du)} <span style="font-size:.9rem;opacity:.7;">FCFA</span></div>
            </div>
        </div>`;

        document.getElementById('clientModalBody').innerHTML = html;
    } catch(e) {
        document.getElementById('clientModalBody').innerHTML =
            `<div class="alert alert-danger">Erreur lors du chargement : ${e.message}</div>`;
    }
}

// ═══════════════════════════════════════════════
// FILTRE PÉRIODE
// ═══════════════════════════════════════════════

function filtrerParPeriode(valeur) {
    if (!valeur) return;
    const input = document.getElementById('filtre-periode');
    if (input) { input.style.borderColor='#6366f1'; input.style.boxShadow='0 0 0 3px rgba(99,102,241,.15)'; }
    const url = new URL(window.location.href);
    url.searchParams.set('periode', valeur);
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('filtre-periode');
    if (!input) return;
    const now = new Date();
    const moisActuel = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
    if (input.value && input.value !== moisActuel) {
        input.style.borderColor = '#6366f1';
        input.style.background  = '#eef2ff';
        const badge = document.createElement('span');
        badge.innerHTML = '<i class="fa-solid fa-rotate-left me-1"></i>Réinitialiser';
        badge.style.cssText = 'font-size:.72rem;color:#6366f1;font-weight:700;cursor:pointer;margin-left:.5rem;';
        badge.onclick = () => { const u = new URL(window.location.href); u.searchParams.delete('periode'); window.location.href = u.toString(); };
        input.parentNode.insertAdjacentElement('afterend', badge);
    }
});

// ═══════════════════════════════════════════════
// PARAMÈTRES — Toggle + Sauvegarde
// ═══════════════════════════════════════════════

let parametresVisible = false;

function toggleParametres() {
    parametresVisible = !parametresVisible;
    const section = document.getElementById('section-parametres');
    const label   = document.getElementById('btn-param-label');
    const btn     = document.getElementById('btn-parametres');

    if (parametresVisible) {
        section.style.display = 'block';
        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        label.textContent = 'Fermer Paramètres';
        btn.style.background = 'linear-gradient(135deg,#64748b,#475569)';
        btn.style.boxShadow  = 'none';
    } else {
        section.style.display = 'none';
        label.textContent = 'Paramètres';
        btn.style.background = 'linear-gradient(135deg,#ef4444,#dc2626)';
        btn.style.boxShadow  = '0 4px 14px rgba(239,68,68,.3)';
    }
}

// ── Sur-mesure toggle ─────────────────────────────────────────────────────────
document.addEventListener('change', function(e) {
    if (!e.target.classList.contains('sur-mesure-toggle')) return;
    const cb      = e.target;
    const inputId = cb.dataset.target;
    const badgeId = cb.dataset.badge;
    const input   = document.getElementById(inputId);
    const badge   = badgeId ? document.getElementById(badgeId) : null;

    if (input) {
        input.disabled = cb.checked;
        if (cb.checked) { input.value = ''; input.placeholder = 'Sur mesure'; }
        else            { input.placeholder = 'Entrer le prix'; }
    }
    if (badge) {
        badge.classList.toggle('d-none', !cb.checked);
    }
});

// ── Input : mise à jour projection annuelle en temps réel ────────────────────
document.addEventListener('input', function(e) {
    if (!e.target.classList.contains('prix-input')) return;
    const app  = e.target.dataset.app;
    const pack = e.target.dataset.pack;
    const val  = parseFloat(e.target.value) || 0;
    const slug = (app + '-' + pack).toLowerCase().replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
    const anneeEl = document.getElementById('annee-' + slug);
    if (anneeEl) {
        anneeEl.textContent = val > 0
            ? new Intl.NumberFormat('fr-FR').format(val * 12) + ' FCFA'
            : '—';
    }
});

// ── Sauvegarde AJAX ───────────────────────────────────────────────────────────
document.addEventListener('click', function(e) {
    const btn = e.target.closest('.save-prix-btn');
    if (!btn) return;

    const inputEl = document.getElementById(btn.dataset.input);
    const surmEl  = btn.dataset.surm ? document.getElementById(btn.dataset.surm) : null;
    const isSurm  = surmEl ? !surmEl.classList.contains('d-none') : false;
    const prix    = inputEl ? parseFloat(inputEl.value) || null : null;

    const payload = {
        type:         btn.dataset.type,
        app_name:     btn.dataset.app  || null,
        pack_name:    btn.dataset.pack,
        prix_mensuel: isSurm ? null : prix,
        sur_mesure:   isSurm ? 1 : 0,
        actif:        1,
        _token:       '{{ csrf_token() }}'
    };

    const origHtml = btn.innerHTML;
    btn.innerHTML  = '<i class="fa-solid fa-spinner fa-spin"></i>';
    btn.disabled   = true;

    fetch('{{ route("superadmin.honoraires.update_prix") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            btn.innerHTML = '<i class="fa-solid fa-check me-1"></i>Sauvegardé !';
            btn.style.background = '#10b981';
            setTimeout(() => { btn.innerHTML = origHtml; btn.style.background = ''; btn.disabled = false; }, 2000);
        } else {
            btn.innerHTML = '<i class="fa-solid fa-xmark me-1"></i>Erreur';
            btn.style.background = '#ef4444';
            setTimeout(() => { btn.innerHTML = origHtml; btn.style.background = ''; btn.disabled = false; }, 2000);
        }
    })
    .catch(() => {
        btn.innerHTML = '<i class="fa-solid fa-xmark me-1"></i>Erreur réseau';
        btn.style.background = '#ef4444';
        setTimeout(() => { btn.innerHTML = origHtml; btn.style.background = ''; btn.disabled = false; }, 2000);
    });
});
</script>

</body>
</html>
