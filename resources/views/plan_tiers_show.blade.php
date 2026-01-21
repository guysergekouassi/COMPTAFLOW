@include('components.head')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --premium-blue: #1e40af;
        --premium-blue-light: #3b82f6;
        --premium-indigo: #4f46e5;
        --premium-slate-900: #0f172a;
        --premium-slate-800: #1e293b;
        --premium-slate-600: #475569;
        --premium-slate-500: #64748b;
        --premium-glass-bg: rgba(255, 255, 255, 0.85);
        --premium-glass-border: rgba(255, 255, 255, 1);
        --premium-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.04), 0 20px 25px -5px rgba(0, 0, 0, 0.02);
    }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        background-color: #f8fafc;
    }

    .glass-card {
        background: var(--premium-glass-bg);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--premium-glass-border);
        border-radius: 24px;
        box-shadow: var(--premium-shadow);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }

    .glass-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.08);
    }

    .kpi-card {
        padding: 1.5rem;
        border-radius: 20px;
        background: white;
        border: 1px solid #f1f5f9;
        transition: all 0.2s ease;
    }

    .kpi-card:hover {
        border-color: var(--premium-blue-light);
        background: #fdfeff;
    }

    .icon-box {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: transform 0.3s ease;
    }

    .kpi-card:hover .icon-box {
        transform: scale(1.1) rotate(5deg);
    }

    .text-gradient {
        background: linear-gradient(135deg, var(--premium-blue), var(--premium-indigo));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .header-immersive {
        background: linear-gradient(135deg, #1e40af 0%, #4f46e5 100%);
        border-radius: 30px;
        padding: 3rem 2.5rem;
        color: white;
        position: relative;
        overflow: hidden;
        margin-bottom: 2rem;
        box-shadow: 0 25px 50px -12px rgba(30, 64, 175, 0.25);
    }

    .header-immersive::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .header-immersive::after {
        content: '';
        position: absolute;
        bottom: -20%;
        left: 5%;
        width: 250px;
        height: 250px;
        background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
        border-radius: 50%;
    }

    .badge-premium {
        padding: 0.5rem 1rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
    }

    .table-premium thead th {
        background: #f8fafc;
        padding: 1.25rem 1.5rem;
        font-size: 0.75rem;
        font-weight: 800;
        color: var(--premium-slate-500);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 2px solid #f1f5f9;
    }

    .table-premium tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid #f8fafc;
    }

    .table-premium tbody tr:hover {
        background: #f1f5f9;
        transform: scale(1.002);
    }

    .table-premium td {
        padding: 1.25rem 1.5rem;
    }

    .status-badge {
        padding: 6px 14px;
        border-radius: 10px;
        font-size: 0.7rem;
        font-weight: 800;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .premium-modal-content {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        border: 1px solid white;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        padding: 2rem !important;
    }

    .input-premium {
        border: 2px solid #f1f5f9 !important;
        background: #f8fafc !important;
        border-radius: 14px !important;
        padding: 0.8rem 1.25rem !important;
        font-weight: 600 !important;
        transition: all 0.2s !important;
    }

    .input-premium:focus {
        border-color: var(--premium-blue) !important;
        background: white !important;
        box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.08) !important;
    }

    .btn-premium-action {
        padding: 0.8rem 1.5rem;
        border-radius: 14px;
        font-weight: 800;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .btn-premium-blue {
        background: var(--premium-blue);
        color: white;
        box-shadow: 0 10px 15px -3px rgba(30, 64, 175, 0.3);
    }

    .btn-premium-blue:hover {
        background: var(--premium-indigo);
        transform: translateY(-2px);
        box-shadow: 0 15px 25px -5px rgba(30, 64, 175, 0.4);
    }

    .btn-premium-glass {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: white;
    }

    .btn-premium-glass:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px);
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Vue <span class="text-gradient">Premium</span> de Tiers'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Header Immersif -->
                        <div class="header-immersive">
                            <div class="flex flex-col lg:flex-row items-center justify-between gap-8 relative z-10">
                                <div class="flex flex-col md:flex-row items-center gap-8">
                                    <div class="w-28 h-28 bg-white/20 backdrop-blur-xl rounded-[32px] flex items-center justify-center border border-white/30 shadow-2xl group transition-all duration-500 hover:rotate-6">
                                        @if(\Illuminate\Support\Str::contains(strtolower($tier->type_de_tiers), 'client'))
                                            <i class="bx bx-user text-5xl text-white"></i>
                                        @elseif(\Illuminate\Support\Str::contains(strtolower($tier->type_de_tiers), 'fournisseur'))
                                            <i class="bx bx-store-alt text-5xl text-white"></i>
                                        @else
                                            <i class="bx bx-id-card text-5xl text-white"></i>
                                        @endif
                                    </div>
                                    <div class="text-center md:text-left">
                                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 mb-3">
                                            <span class="badge-premium">{{ $tier->type_de_tiers }}</span>
                                            <span class="flex items-center gap-2 bg-black/20 px-3 py-1 rounded-full text-[0.65rem] font-bold tracking-tighter uppercase border border-white/10">
                                                <i class="bx bx-hash"></i> {{ $tier->numero_de_tiers }}
                                            </span>
                                        </div>
                                        <h1 class="text-4xl md:text-5xl font-extrabold tracking-tighter mb-2">{{ $tier->intitule }}</h1>
                                        <p class="text-white/70 font-medium flex items-center justify-center md:justify-start gap-2">
                                            <i class="bx bx-map-pin"></i> Siège Social • Côte d'Ivoire
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="flex flex-wrap items-center justify-center gap-4">
                                    <a href="{{ route('plan_tiers') }}" class="btn-premium-action btn-premium-glass">
                                        <i class="bx bx-chevron-left text-lg"></i> Retour
                                    </a>
                                    <button data-bs-toggle="modal" data-bs-target="#modalCenterUpdate" class="btn-premium-action bg-white !text-indigo-700 hover:bg-white/90">
                                        <i class="bx bx-edit-alt text-lg"></i> Modifier le profil
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- KPI Dashboard -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 mt-[-1rem]">
                            <div class="glass-card !p-0">
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="icon-box bg-blue-50 text-blue-600">
                                            <i class="bx bx-layer"></i>
                                        </div>
                                        <span class="text-[0.6rem] font-black uppercase text-slate-400 tracking-widest">Activité</span>
                                    </div>
                                    <h3 class="text-3xl font-black text-slate-800 tracking-tighter">{{ number_format($stats['total_ecritures'], 0, ',', ' ') }}</h3>
                                    <p class="text-xs font-bold text-slate-500 mt-1 uppercase tracking-tighter">Écritures totales</p>
                                </div>
                                <div class="h-1 w-full bg-blue-600 opacity-20"></div>
                            </div>

                            <div class="glass-card !p-0">
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="icon-box bg-emerald-50 text-emerald-600">
                                            <i class="bx bx-trending-up"></i>
                                        </div>
                                        <span class="text-[0.6rem] font-black uppercase text-slate-400 tracking-widest">Flux Entrant</span>
                                    </div>
                                    <h3 class="text-3xl font-black text-emerald-600 tracking-tighter">{{ number_format($stats['total_debit'], 0, ',', ' ') }}</h3>
                                    <p class="text-xs font-bold text-slate-500 mt-1 uppercase tracking-tighter">Total Débit (FCFA)</p>
                                </div>
                                <div class="h-1 w-full bg-emerald-500 opacity-20"></div>
                            </div>

                            <div class="glass-card !p-0">
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="icon-box bg-rose-50 text-rose-600">
                                            <i class="bx bx-trending-down"></i>
                                        </div>
                                        <span class="text-[0.6rem] font-black uppercase text-slate-400 tracking-widest">Flux Sortant</span>
                                    </div>
                                    <h3 class="text-3xl font-black text-rose-600 tracking-tighter">{{ number_format($stats['total_credit'], 0, ',', ' ') }}</h3>
                                    <p class="text-xs font-bold text-slate-500 mt-1 uppercase tracking-tighter">Total Crédit (FCFA)</p>
                                </div>
                                <div class="h-1 w-full bg-rose-500 opacity-20"></div>
                            </div>

                            <div class="glass-card !p-0">
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="icon-box {{ $stats['solde'] >= 0 ? 'bg-indigo-50 text-indigo-600' : 'bg-amber-50 text-amber-600' }}">
                                            <i class="bx {{ $stats['solde'] >= 0 ? 'bx-check-double' : 'bx-info-circle' }}"></i>
                                        </div>
                                        <span class="text-[0.6rem] font-black uppercase text-slate-400 tracking-widest">Position</span>
                                    </div>
                                    <h3 class="text-3xl font-black {{ $stats['solde'] >= 0 ? 'text-indigo-600' : 'text-amber-600' }} tracking-tighter">
                                        {{ number_format(abs($stats['solde']), 0, ',', ' ') }}
                                    </h3>
                                    <p class="text-xs font-bold text-slate-500 mt-1 uppercase tracking-tighter">
                                        Solde {{ $stats['solde'] >= 0 ? 'Débiteur' : 'Créditeur' }}
                                    </p>
                                </div>
                                <div class="h-1 w-full {{ $stats['solde'] >= 0 ? 'bg-indigo-600' : 'bg-amber-500' }} opacity-20"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                            <!-- Left: Details -->
                            <div class="lg:col-span-4 space-y-8">
                                <div class="glass-card p-8">
                                    <h4 class="text-lg font-black text-slate-800 mb-6 flex items-center gap-3">
                                        <span class="w-8 h-8 bg-blue-600 text-white rounded-lg flex items-center justify-center text-sm">
                                            <i class="bx bx-detail"></i>
                                        </span>
                                        Fiche Signalétique
                                    </h4>
                                    
                                    <div class="space-y-6">
                                        <div class="group">
                                            <label class="text-[0.65rem] font-black text-slate-400 uppercase tracking-widest block mb-1">Désignation</label>
                                            <p class="text-slate-900 font-bold group-hover:text-blue-600 transition">{{ $tier->intitule }}</p>
                                        </div>
                                        <div class="group">
                                            <label class="text-[0.65rem] font-black text-slate-400 uppercase tracking-widest block mb-1">Identifiant Tiers</label>
                                            <p class="font-mono text-slate-700 font-bold bg-slate-50 px-2 py-1 rounded inline-block">{{ $tier->numero_de_tiers }}</p>
                                        </div>
                                        <div class="group">
                                            <label class="text-[0.65rem] font-black text-slate-400 uppercase tracking-widest block mb-1">Rattachement Comptable</label>
                                            @if($tier->compte)
                                                <div class="flex items-center gap-3 mt-1">
                                                    <div class="bg-indigo-50 text-indigo-700 px-3 py-1 rounded-xl font-black text-xs">
                                                        {{ $tier->compte->numero_de_compte }}
                                                    </div>
                                                    <p class="text-xs font-bold text-slate-600 truncate">{{ $tier->compte->intitule }}</p>
                                                </div>
                                            @else
                                                <p class="text-slate-400 italic text-sm">Non rattaché</p>
                                            @endif
                                        </div>
                                        <div class="pt-4 border-t border-slate-50">
                                            <label class="text-[0.65rem] font-black text-slate-400 uppercase tracking-widest block mb-3">Santé Financière</label>
                                            @php
                                                $health = 100;
                                                if($stats['total_credit'] > $stats['total_debit'] && $stats['total_debit'] > 0) {
                                                    $health = round(($stats['total_debit'] / $stats['total_credit']) * 100);
                                                }
                                            @endphp
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-xs font-black {{ $health > 70 ? 'text-emerald-600' : 'text-amber-600' }}">Score: {{ $health }}%</span>
                                                <span class="text-[0.6rem] font-bold text-slate-500 uppercase">Ratio D/C</span>
                                            </div>
                                            <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden p-[2px]">
                                                <div class="h-full rounded-full transition-all duration-1000 {{ $health > 70 ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ $health }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="glass-card p-1 bg-gradient-to-br from-blue-600 to-indigo-700 text-white overflow-hidden group">
                                    <div class="p-7">
                                        <h4 class="text-lg font-black mb-4 flex items-center gap-3">
                                            <i class="bx bxs-zap text-amber-300"></i> Actions Rapides
                                        </h4>
                                        <div class="space-y-3">
                                            <button class="w-full py-3 px-4 bg-white/10 hover:bg-white/20 rounded-xl font-bold text-sm transition-all flex items-center justify-between group/btn">
                                                Nouveau Règlement <i class="bx bx-plus-circle text-lg opacity-50 group-hover/btn:opacity-100 transition"></i>
                                            </button>
                                            <button class="w-full py-3 px-4 bg-white/10 hover:bg-white/20 rounded-xl font-bold text-sm transition-all flex items-center justify-between group/btn">
                                                Générer Extrait <i class="bx bx-file-blank text-lg opacity-50 group-hover/btn:opacity-100 transition"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="absolute -bottom-10 -right-10 opacity-10 group-hover:rotate-12 transition-all duration-700">
                                        <i class="bx bxs-wallet text-[140px]"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Operations Table -->
                            <div class="lg:col-span-8">
                                <div class="glass-card h-full flex flex-col">
                                    <div class="p-8 border-b border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                        <div>
                                            <h4 class="text-xl font-black text-slate-800 tracking-tight">Historique des Opérations</h4>
                                            <p class="text-sm font-bold text-slate-400 mt-1">Registres comptables liés au tiers</p>
                                        </div>
                                        <div class="flex items-center p-2 bg-slate-50 rounded-2xl border border-slate-100">
                                            <div class="px-4 py-2 bg-white rounded-xl shadow-sm text-xs font-black text-blue-700 border border-blue-50">Toutes</div>
                                            <div class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-blue-600 transition cursor-pointer">Reçues</div>
                                            <div class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-blue-600 transition cursor-pointer">Payées</div>
                                        </div>
                                    </div>

                                    @if($tier->ecritures && $tier->ecritures->count() > 0)
                                        <div class="overflow-x-auto">
                                            <table class="w-full table-premium">
                                                <thead>
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Identifiant</th>
                                                        <th>Libellé de l'opération</th>
                                                        <th class="text-right">Débit</th>
                                                        <th class="text-right">Crédit</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-50">
                                                    @foreach($tier->ecritures as $ecriture)
                                                        <tr>
                                                            <td>
                                                                <div class="flex flex-col">
                                                                    <span class="text-slate-900 font-black text-sm">{{ \Carbon\Carbon::parse($ecriture->date)->format('d/m/Y') }}</span>
                                                                    <span class="text-[0.6rem] font-bold text-slate-400 uppercase">{{ \Carbon\Carbon::parse($ecriture->date)->diffForHumans() }}</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <span class="status-badge bg-blue-50 text-blue-700">
                                                                    <i class="bx bx-barcode-reader"></i> {{ $ecriture->n_saisie }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <div class="max-w-[180px]">
                                                                    <p class="text-slate-800 font-bold text-xs truncate" title="{{ $ecriture->description_operation }}">
                                                                        {{ $ecriture->description_operation }}
                                                                    </p>
                                                                    <span class="text-[0.6rem] font-black text-slate-400 uppercase italic">Opération comptable</span>
                                                                </div>
                                                            </td>
                                                            <td class="text-right">
                                                                @if($ecriture->debit > 0)
                                                                    <span class="text-emerald-600 font-black text-sm">+ {{ number_format($ecriture->debit, 0, ',', ' ') }}</span>
                                                                @else
                                                                    <span class="text-slate-300">—</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-right">
                                                                @if($ecriture->credit > 0)
                                                                    <span class="text-rose-600 font-black text-sm">- {{ number_format($ecriture->credit, 0, ',', ' ') }}</span>
                                                                @else
                                                                    <span class="text-slate-300">—</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <div class="flex-1 flex flex-col items-center justify-center p-12 text-center opacity-70">
                                            <div class="w-24 h-24 bg-slate-50 rounded-[32px] flex items-center justify-center mb-6">
                                                <i class="bx bx-data text-4xl text-slate-200"></i>
                                            </div>
                                            <h5 class="text-lg font-black text-slate-400">Désert Numérique</h5>
                                            <p class="text-sm font-bold text-slate-300 max-w-[200px] mt-2">Aucune transaction n'a encore été enregistrée pour ce tiers.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('components.footer')

    <!-- Modal Modification (Standardisé Premium) -->
    <div class="modal fade" id="modalCenterUpdate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form method="POST" action="{{ route('plan_tiers.update', ['id' => $tier->id]) }}" id="updateTiersForm" class="w-full">
                @csrf
                @method('PUT')
                <input type="hidden" id="update_id" name="id" value="{{ $tier->id }}">
                
                <div class="modal-content premium-modal-content">
                    <div class="text-center mb-8 relative">
                        <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal"></button>
                        <h1 class="text-2xl font-black tracking-tighter text-slate-900">
                            Profil <span class="text-indigo-600">Tiers</span>
                        </h1>
                        <p class="text-[0.6rem] font-black text-slate-400 uppercase tracking-widest mt-1">Édition des informations de base</p>
                        <div class="h-1.5 w-12 bg-indigo-600 mx-auto mt-4 rounded-full"></div>
                    </div>

                    <div class="space-y-5">
                        <div class="space-y-1">
                            <label class="input-label-premium">Catégorie d'entité</label>
                            <select id="update_type_de_tiers" name="type_de_tiers" class="form-select input-premium w-full" required>
                                @foreach (['Fournisseur', 'Client', 'Personnel', 'Impots', 'CNPS', 'Associé', 'Divers Tiers'] as $type)
                                    <option value="{{ $type }}" {{ $tier->type_de_tiers == $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="input-label-premium">Compte de Rattachement</label>
                            <select name="compte_general" class="form-select input-premium w-full" required>
                                @foreach (\App\Models\PlanComptable::where('numero_de_compte', 'LIKE', '4%')->orderByRaw("LPAD(numero_de_compte, 20, '0')")->get() as $compte)
                                    <option value="{{ $compte->id }}" {{ $tier->compte_general == $compte->id ? 'selected' : '' }}>
                                        {{ $compte->numero_de_compte }} - {{ $compte->intitule }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="input-label-premium">Désignation Officielle</label>
                            <input type="text" name="intitule" class="form-control input-premium" value="{{ $tier->intitule }}" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mt-10">
                        <button type="button" class="py-3 font-bold text-slate-400 hover:text-slate-600 transition" data-bs-dismiss="modal">
                            Ignorer
                        </button>
                        <button type="submit" class="btn-premium-action btn-premium-blue justify-center">
                            Enregistrer <i class="bx bx-check-shield text-lg"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
