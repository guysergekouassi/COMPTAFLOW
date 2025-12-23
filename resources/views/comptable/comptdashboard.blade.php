<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">

@include('components.head')

<style>
    body {
        background-color: #f8fafc;
        font-family: 'Inter', sans-serif;
        color: #1a1a1a;
    }
    .glass-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .glass-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
    .text-gradient {
        background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .nav-button {
        transition: all 0.2s;
        border: 1px solid #e2e8f0;
    }
    .nav-button:hover {
        border-color: #1e40af;
        background-color: #eff6ff;
        color: #1e40af;
    }
    .pulse {
        animation: pulse-animation 2s infinite;
    }
    @keyframes pulse-animation {
        0% { box-shadow: 0 0 0 0px rgba(30, 64, 175, 0.2); }
        100% { box-shadow: 0 0 0 10px rgba(30, 64, 175, 0); }
    }

    /* Layout adjustments to match the theme */
    .layout-page {
        background-color: #f8fafc !important;
    }
    .content-wrapper {
        padding: 2rem !important;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', [
                    'page_title' => 'Tableau de <span class="text-gradient">Bord</span>',
                    'company_name' => $currentCompany->company_name ?? 'Espace Comptable'
                ])

                <div class="content-wrapper">
                    <div class="max-w-7xl mx-auto">

                        <!-- En-tête Centré Dynamique -->
                        <!-- En-tête Centré Dynamique -->
                        <div class="text-center mb-12">
                            <p class="text-slate-500 font-medium">Bienvenue, voici l'état actuel de votre exercice comptable.</p>
                        </div>

                        <!-- Section Statistiques (KPIs) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                            <div class="glass-card p-6 border-l-4 border-l-blue-700">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Écritures du mois</p>
                                        <h3 class="text-3xl font-black text-slate-800 mt-1">{{ number_format($monthlyEntries ?? 0, 0, ',', ' ') }}</h3>
                                    </div>
                                    <div class="p-3 bg-blue-50 text-blue-700 rounded-2xl">
                                        <i class="fas fa-pen-nib text-xl"></i>
                                    </div>
                                </div>
                                <p class="text-xs text-green-600 mt-4 font-bold">
                                    <i class="fas fa-arrow-up mr-1"></i> Synthèse mensuelle
                                </p>
                            </div>

                            <div class="glass-card p-6 border-l-4 border-l-indigo-600">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Solde Trésorerie</p>
                                        <h3 class="text-3xl font-black text-slate-800 mt-1">
                                            {{ number_format($cashBalance ?? 0, 0, ',', ' ') }} <span class="text-sm font-medium">FCFA</span>
                                        </h3>
                                    </div>
                                    <div class="p-3 bg-indigo-50 text-indigo-600 rounded-2xl">
                                        <i class="fas fa-wallet text-xl"></i>
                                    </div>
                                </div>
                                <p class="text-xs text-slate-500 mt-4 italic">Mise à jour en temps réel</p>
                            </div>

                            <div class="glass-card p-6 border-l-4 border-l-emerald-500">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tiers Actifs</p>
                                        <h3 class="text-3xl font-black text-slate-800 mt-1">{{ ($clientCount ?? 0) + ($supplierCount ?? 0) }}</h3>
                                    </div>
                                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl">
                                        <i class="fas fa-users text-xl"></i>
                                    </div>
                                </div>
                                <div class="flex gap-2 mt-4 text-[10px] font-bold uppercase">
                                    <span class="text-blue-600">{{ $clientCount ?? 0 }} Clients</span>
                                    <span class="text-slate-300">|</span>
                                    <span class="text-orange-600">{{ $supplierCount ?? 0 }} Fourn.</span>
                                </div>
                            </div>

                            <div class="glass-card p-6 border-l-4 border-l-slate-800">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Exercice en cours</p>
                                        <h3 class="text-3xl font-black text-slate-800 mt-1">{{ $exerciceYear ?? date('Y') }}</h3>
                                    </div>
                                    <div class="p-3 bg-slate-100 text-slate-800 rounded-2xl">
                                        <i class="fas fa-calendar-check text-xl"></i>
                                    </div>
                                </div>
                                <p class="text-xs text-blue-700 mt-4 font-bold italic underline">Période : {{ \Carbon\Carbon::now()->translatedFormat('F') }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                            <!-- Section Actions Rapides (Modules) -->
                            <div class="lg:col-span-2 space-y-8">
                                <div class="glass-card p-8">
                                    <h2 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                                        <i class="fas fa-th-large text-blue-700"></i> Vos Modules de Travail
                                    </h2>

                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                                        <!-- Traitement -->
                                        <div class="space-y-3">
                                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b pb-2">Traitement</h4>
                                            <a href="{{ route('accounting_entry_real') }}" class="nav-button w-full flex items-center gap-3 p-3 rounded-xl text-sm font-semibold pulse bg-blue-700 text-white border-none transition-all hover:bg-blue-800">
                                                <i class="fas fa-keyboard w-5"></i> Nouvelle Saisie
                                            </a>
                                            <a href="{{ route('accounting_entry_list') }}" class="nav-button w-full flex items-center gap-3 p-3 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50">
                                                <i class="fas fa-list-ul w-5"></i> Écritures
                                            </a>
                                            <a href="{{ route('exercice_comptable') }}" class="nav-button w-full flex items-center gap-3 p-3 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50">
                                                <i class="fas fa-history w-5"></i> Exercice
                                            </a>
                                        </div>

                                        <!-- Paramétrage -->
                                        <div class="space-y-3">
                                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b pb-2">Paramétrage</h4>
                                            <a href="{{ route('plan_comptable') }}" class="nav-button w-full flex items-center gap-3 p-3 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50">
                                                <i class="fas fa-book w-5"></i> Plan Comptable
                                            </a>
                                            <a href="{{ route('plan_tiers') }}" class="nav-button w-full flex items-center gap-3 p-3 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50">
                                                <i class="fas fa-address-book w-5"></i> Plan Tiers
                                            </a>
                                            <a href="{{ route('accounting_journals') }}" class="nav-button w-full flex items-center gap-3 p-3 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50">
                                                <i class="fas fa-journal-whills w-5"></i> Journaux
                                            </a>
                                        </div>

                                        <!-- Rapports -->
                                        <div class="space-y-3">
                                            <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest border-b pb-2">Rapports</h4>
                                            <a href="{{ route('accounting_ledger') }}" class="nav-button w-full flex items-center gap-3 p-3 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50">
                                                <i class="fas fa-file-invoice w-5"></i> Grand Livre
                                            </a>
                                            <a href="{{ route('accounting_balance') }}" class="nav-button w-full flex items-center gap-3 p-3 rounded-xl text-sm font-semibold text-slate-700 bg-white hover:bg-slate-50">
                                                <i class="fas fa-balance-scale w-5"></i> Balance
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Activité Récente / Journaux de Trésorerie -->
                                <div class="glass-card p-8">
                                    <div class="flex items-center justify-between mb-6">
                                        <h2 class="text-xl font-bold text-slate-800">Derniers Journaux de Trésorerie</h2>
                                        <button class="text-sm text-blue-700 font-bold hover:underline">Voir tout</button>
                                    </div>
                                    <div class="space-y-4">
                                        @forelse($recentTreasuryEntries ?? [] as $entry)
                                        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-100 transition-hover hover:bg-slate-100">
                                            <div class="flex items-center gap-4">
                                                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-blue-700 shadow-sm">
                                                    <i class="fas fa-{{ $entry['icon'] ?? 'university' }}"></i>
                                                </div>
                                                <div>
                                                    <p class="font-bold text-slate-800">{{ $entry['title'] }}</p>
                                                    <p class="text-xs text-slate-500">Poste : {{ $entry['poste'] }}</p>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <p class="font-black {{ $entry['amount'] > 0 ? 'text-slate-800' : 'text-red-600' }}">
                                                    {{ $entry['amount'] > 0 ? '+' : '' }} {{ number_format($entry['amount'], 0, ',', ' ') }}
                                                </p>
                                                <p class="text-[10px] text-slate-400">{{ $entry['date'] }}</p>
                                            </div>
                                        </div>
                                        @empty
                                        <div class="text-center py-6 text-slate-400 italic">
                                            Aucune opération de trésorerie récente.
                                        </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                            <!-- Barre Latérale Droite : Alertes & Statut -->
                            <div class="space-y-8">
                                <div class="glass-card p-8 bg-slate-900 text-white border-none shadow-xl shadow-blue-900/20">
                                    <h2 class="text-lg font-bold mb-4">Statut Clôture</h2>
                                    <div class="w-full bg-slate-800 rounded-full h-2 mb-2">
                                        <div class="bg-blue-500 h-2 rounded-full transition-all duration-1000" style="width: {{ $exerciceProgress ?? 0 }}%"></div>
                                    </div>
                                    <p class="text-xs text-slate-400">Exercice {{ $exerciceYear ?? date('Y') }} complété à {{ $exerciceProgress ?? 0 }}%</p>
                                    <button class="w-full mt-6 py-3 bg-white/10 hover:bg-white/20 rounded-xl text-sm font-bold transition">
                                        Générer Pré-Bilan
                                    </button>
                                </div>

                                <div class="glass-card p-8">
                                    <h2 class="text-lg font-bold text-slate-800 mb-4">Notifications</h2>
                                    <div class="space-y-6">
                                        @forelse($alerts ?? [] as $alert)
                                        <div class="flex gap-4">
                                            <div class="mt-1">
                                                <span class="flex h-2 w-2 rounded-full bg-{{ $alert['priority'] == 'high' ? 'red' : 'blue' }}-600"></span>
                                            </div>
                                            <p class="text-xs text-slate-600 leading-relaxed">
                                                <span class="font-bold text-slate-900">{{ $alert['title'] }} :</span> {{ $alert['description'] }}
                                            </p>
                                        </div>
                                        @empty
                                        <div class="text-xs text-slate-500 italic">
                                            Aucune notification pour le moment.
                                        </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    @include('components.footer')
                </div>
            </div>
        </div>
        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
</body>
</html>
