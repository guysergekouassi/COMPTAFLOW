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

    .layout-page {
        background-color: #f8fafc !important;
    }
    .content-wrapper {
        padding: 1.5rem !important;
    }
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', [
                    'page_title' => 'Pilotage <span class="text-gradient">Performance</span>',
                    'company_name' => ($currentCompany->company_name ?? 'Dashboard Administration') . ' (VUE GLOBALE)'
                ])

                <div class="content-wrapper">
                    <div class="max-w-7xl mx-auto">

                        <!-- En-tête Admin -->
                        <div class="text-center mb-8">
                            <h2 class="text-2xl font-black text-slate-800">Tableau de Bord <span class="text-gradient">Administrateur</span></h2>
                            <p class="text-base font-medium text-slate-500">Vue agrégée de toutes les activités de l'entreprise.</p>
                        </div>

                        <!-- Actions Rapides Stylisées -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <a href="{{ route('compta.create') }}" class="glass-card p-5 d-flex align-items-center justify-content-between text-decoration-none transition-all hover:bg-slate-50 border-l-4 border-l-indigo-600">
                                <div class="d-flex align-items-center">
                                    <div class="p-3 bg-indigo-50 text-indigo-600 rounded-2xl me-4">
                                        <i class="fa-solid fa-folder-plus text-xl"></i>
                                    </div>
                                    <div>
                                        <h6 class="font-bold text-slate-800 mb-0">Comptabilité</h6>
                                        <small class="text-slate-500">Nouvelle entité</small>
                                    </div>
                                </div>
                                <i class="fa-solid fa-chevron-right text-slate-300"></i>
                            </a>

                            <a href="{{ route('admin.admins.create') }}" class="glass-card p-5 d-flex align-items-center justify-content-between text-decoration-none transition-all hover:bg-slate-50 border-l-4 border-l-blue-600">
                                <div class="d-flex align-items-center">
                                    <div class="p-3 bg-blue-50 text-blue-600 rounded-2xl me-4">
                                        <i class="fa-solid fa-user-shield text-xl"></i>
                                    </div>
                                    <div>
                                        <h6 class="font-bold text-slate-800 mb-0">Administrateur</h6>
                                        <small class="text-slate-500">Ajouter gestionnaire</small>
                                    </div>
                                </div>
                                <i class="fa-solid fa-chevron-right text-slate-300"></i>
                            </a>

                            <a href="{{ route('admin.users.create') }}" class="glass-card p-5 d-flex align-items-center justify-content-between text-decoration-none transition-all hover:bg-slate-50 border-l-4 border-l-emerald-600">
                                <div class="d-flex align-items-center">
                                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl me-4">
                                        <i class="fa-solid fa-user-plus text-xl"></i>
                                    </div>
                                    <div>
                                        <h6 class="font-bold text-slate-800 mb-0">Collaborateur</h6>
                                        <small class="text-slate-500">Inviter comptable</small>
                                    </div>
                                </div>
                                <i class="fa-solid fa-chevron-right text-slate-300"></i>
                            </a>
                        </div>

                        <!-- Section Statistiques Globales (KPIs) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
                            <div class="glass-card p-5 border-l-4 border-l-blue-700">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Activité Mensuelle</p>
                                        <h3 class="text-2xl font-black text-slate-800 mt-1">{{ number_format($monthlyEntries ?? 0, 0, ',', ' ') }}</h3>
                                    </div>
                                    <div class="p-3 bg-blue-50 text-blue-700 rounded-2xl">
                                        <i class="fas fa-chart-line text-lg"></i>
                                    </div>
                                </div>
                                <p class="text-xs text-green-600 mt-4 font-bold">
                                    <i class="fas fa-users mr-1"></i> Toutes saisies confondues
                                </p>
                            </div>

                            <div class="glass-card p-5 border-l-4 border-l-indigo-600">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Trésorerie Globale</p>
                                        <h3 class="text-2xl font-black text-slate-800 mt-1">
                                            {{ number_format($cashBalance ?? 0, 0, ',', ' ') }} <span class="text-sm font-medium">FCFA</span>
                                        </h3>
                                    </div>
                                    <div class="p-3 bg-indigo-50 text-indigo-600 rounded-2xl">
                                        <i class="fas fa-vault text-lg"></i>
                                    </div>
                                </div>
                                <p class="text-xs text-slate-500 mt-4 italic">Solde consolidé</p>
                            </div>

                            <div class="glass-card p-5 border-l-4 border-l-emerald-500">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Revenus vs Charges</p>
                                        <h3 class="text-2xl font-black text-slate-800 mt-1">
                                            {{ number_format(($totalRevenue ?? 0) - ($totalExpenses ?? 0), 0, ',', ' ') }}
                                        </h3>
                                    </div>
                                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl">
                                        <i class="fas fa-scale-balanced text-lg"></i>
                                    </div>
                                </div>
                                <div class="flex gap-2 mt-4 text-[10px] font-bold uppercase">
                                    <span class="text-blue-600">{{ number_format($totalRevenue ?? 0, 0, ',', ' ') }} Rev.</span>
                                    <span class="text-slate-300">|</span>
                                    <span class="text-red-600">{{ number_format($totalExpenses ?? 0, 0, ',', ' ') }} Chg.</span>
                                </div>
                            </div>

                            <div class="glass-card p-5 border-l-4 border-l-slate-800">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Exercice Actif</p>
                                        <h3 class="text-2xl font-black text-slate-800 mt-1">{{ $exerciceYear ?? date('Y') }}</h3>
                                    </div>
                                    <div class="p-3 bg-slate-100 text-slate-800 rounded-2xl">
                                        <i class="fas fa-calendar-alt text-lg"></i>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <div class="w-full bg-slate-100 rounded-full h-1.5 mb-1">
                                        <div class="bg-indigo-600 h-1.5 rounded-full" style="width: {{ $exerciceProgress ?? 0 }}%"></div>
                                    </div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase">Progression : {{ $exerciceProgress ?? 0 }}%</p>
                                </div>
                            </div>
                        </div>

                        <!-- Graphiques & Activités -->
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            
                            <!-- Graphique Revenus vs Dépenses -->
                            <div class="lg:col-span-2 space-y-8">
                                <div class="glass-card p-6">
                                    <div class="flex items-center justify-between mb-6">
                                        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                            <i class="fas fa-chart-area text-indigo-600"></i> Performance Financière
                                        </h3>
                                        <div class="flex gap-2">
                                            <span class="flex items-center gap-1 text-[10px] font-bold text-slate-500 uppercase">
                                                <span class="w-2 h-2 rounded-full bg-blue-600"></span> Revenus
                                            </span>
                                        </div>
                                    </div>
                                    <div class="chart-container">
                                        <canvas id="revenueChart"></canvas>
                                    </div>
                                </div>

                                <!-- Dernières Écritures (Vue Admin avec Utilisateur) -->
                                <div class="glass-card p-6">
                                    <div class="flex items-center justify-between mb-6">
                                        <h3 class="text-lg font-bold text-slate-800">Flux d'Écritures Collaborateurs</h3>
                                        <a href="{{ route('accounting_entry_list') }}" class="text-sm text-blue-700 font-bold hover:underline">Auditer</a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-borderless table-hover align-middle mb-0">
                                            <thead class="text-[10px] uppercase font-bold text-slate-400 border-b">
                                                <tr>
                                                    <th>Collaborateur</th>
                                                    <th>Description</th>
                                                    <th>Date</th>
                                                    <th class="text-end">Montant</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-sm">
                                                @forelse($recentEntries ?? [] as $entry)
                                                <tr>
                                                    <td>
                                                        <div class="flex items-center gap-2">
                                                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-indigo-600 uppercase border border-white shadow-sm">
                                                                {{ substr($entry['user_name'] ?? 'U', 0, 2) }}
                                                            </div>
                                                            <span class="font-semibold text-slate-700">{{ $entry['user_name'] ?? 'Système' }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="max-w-[200px] truncate text-slate-500">{{ $entry['description'] }}</td>
                                                    <td class="text-slate-400 text-xs">{{ $entry['date'] }}</td>
                                                    <td class="text-end">
                                                        <span class="font-black {{ $entry['type'] == 'income' ? 'text-green-600' : 'text-slate-800' }}">
                                                            {{ number_format($entry['amount'], 0, ',', ' ') }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="4" class="text-center py-6 text-slate-400 italic">Aucune écriture récente</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Sidebar Droite : Distribution & Tiers -->
                            <div class="space-y-8">
                                <div class="glass-card p-6">
                                    <h3 class="text-base font-bold text-slate-800 mb-6 flex items-center gap-2">
                                        <i class="fas fa-pie-chart text-emerald-500"></i> Répartition des Charges
                                    </h3>
                                    <div class="chart-container" style="height: 250px;">
                                        <canvas id="expenseChart"></canvas>
                                    </div>
                                </div>

                                <div class="glass-card p-6 bg-slate-900 text-white border-none">
                                    <h3 class="text-base font-bold mb-4">Portefeuille Tiers</h3>
                                    <div class="flex justify-between items-center mb-6">
                                        <div>
                                            <p class="text-[10px] text-slate-400 uppercase font-bold">Total Partenaires</p>
                                            <h4 class="text-2xl font-black mb-0">{{ ($clientCount ?? 0) + ($supplierCount ?? 0) }}</h4>
                                        </div>
                                        <div class="p-3 bg-white/10 rounded-2xl text-emerald-400 font-bold text-xs">
                                            + {{ $clientCount ?? 0 }} Clients
                                        </div>
                                    </div>
                                    <div class="space-y-4">
                                        <div class="p-3 bg-white/5 rounded-xl flex items-center justify-between border border-white/10">
                                            <div class="flex items-center gap-3">
                                                <i class="fas fa-user-tie text-blue-400"></i>
                                                <span class="text-xs font-semibold">Fournisseurs</span>
                                            </div>
                                            <span class="text-xs font-black">{{ $supplierCount ?? 0 }}</span>
                                        </div>
                                        <button class="w-full mt-4 py-3 bg-indigo-600 hover:bg-indigo-700 rounded-xl text-xs font-bold transition">
                                            Gérer Plan Tiers
                                        </button>
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

    <!-- ChartJS Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Revenue Chart
            const revCtx = document.getElementById('revenueChart').getContext('2d');
            new Chart(revCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($revenueChartData['labels'] ?? []) !!},
                    datasets: [{
                        label: 'Revenus (FCFA)',
                        data: {!! json_encode($revenueChartData['data'] ?? []) !!},
                        borderColor: '#2563eb',
                        backgroundColor: (context) => {
                            const chart = context.chart;
                            const {ctx, chartArea} = chart;
                            if (!chartArea) return null;
                            const gradient = ctx.createLinearGradient(0, chartArea.bottom, 0, chartArea.top);
                            gradient.addColorStop(0, 'rgba(37, 99, 235, 0)');
                            gradient.addColorStop(1, 'rgba(37, 99, 235, 0.1)');
                            return gradient;
                        },
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#fff',
                        pointBorderColor: '#2563eb',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, grid: { borderDash: [5, 5], color: '#e2e8f0' }, ticks: { font: { size: 10 } } },
                        x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                    }
                }
            });

            // Expense Category Chart
            const expCtx = document.getElementById('expenseChart').getContext('2d');
            new Chart(expCtx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode(array_column($expenseChartData ?? [], 'category')) !!},
                    datasets: [{
                        data: {!! json_encode(array_column($expenseChartData ?? [], 'total')) !!},
                        backgroundColor: ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#6366f1', '#8b5cf6'],
                        borderWidth: 0,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10, weight: 'bold' }, padding: 20 } }
                    }
                }
            });
        });
    </script>
</body>
</html>
