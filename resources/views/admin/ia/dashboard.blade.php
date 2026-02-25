<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/"
  data-template="vertical-menu-template-free" data-bs-theme="light">

@include('components.head')

<style>
    body {
        background-color: #f8fafc;
        font-family: 'Inter', sans-serif;
    }
    .text-gradient {
        background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 800;
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
    .bg-label-info { background-color: #e7f7ff; color: #03c3ec; }
    .bg-label-success { background-color: #e8fadf; color: #71dd37; }
    .bg-label-warning { background-color: #fff2e2; color: #ffab00; }
    .bg-label-danger { background-color: #ffe5e5; color: #ff3e1d; }
    
    /* Layout pour les cartes alignées */
    .stats-container {
        display: flex;
        gap: 1.5rem;
        margin-bottom: 2rem;
        overflow-x: auto;
        padding-bottom: 0.5rem;
    }
    .stat-card {
        flex: 1;
        min-width: 240px;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
</style>

<body>
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      @include('components.sidebar')

      <div class="layout-page">
        @include('components.header')

        <div class="content-wrapper px-4 py-4">
          <div class="container-xxl flex-grow-1 container-p-y">
            
            <!-- Titre -->
            <div class="mb-8 text-center">
                <h2 class="text-2xl font-black text-slate-800" style="font-size: 1.75rem; font-weight: 900;">
                    Audit <span class="text-gradient">IA</span>
                </h2>
                <p class="text-slate-500 font-medium">Analyse de performance et qualité du scan intelligent</p>
            </div>

            <!-- Stats Cards Alignées -->
            <div class="stats-container">
                <!-- Total Scans -->
                <div class="glass-card stat-card border-l-4 border-l-blue-700">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Scans totaux</p>
                            <h3 class="text-2xl font-black text-slate-800 mb-0">{{ $stats['total'] }}</h3>
                        </div>
                        <div class="p-3 bg-blue-50 text-blue-700 rounded-2xl">
                            <i class="fas fa-microchip text-lg"></i>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 mt-4 italic">Activité consolidée</p>
                </div>

                <!-- Taux de Succès -->
                <div class="glass-card stat-card border-l-4 border-l-emerald-500">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Taux de réussite</p>
                            @php
                                $successRate = $stats['total'] > 0 ? ($stats['success'] / $stats['total']) * 100 : 0;
                            @endphp
                            <h3 class="text-2xl font-black text-slate-800 mb-0">{{ number_format($successRate, 1) }}%</h3>
                        </div>
                        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl">
                            <i class="fas fa-check-circle text-lg"></i>
                        </div>
                    </div>
                    <p class="text-xs text-green-600 mt-4 font-bold">Analyses conformes</p>
                </div>

                <!-- Corrections -->
                <div class="glass-card stat-card border-l-4 border-l-indigo-600">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Corrections</p>
                            <h3 class="text-2xl font-black text-slate-800 mb-0">{{ $stats['corrected'] }}</h3>
                        </div>
                        <div class="p-3 bg-indigo-50 text-indigo-600 rounded-2xl">
                            <i class="fas fa-edit text-lg"></i>
                        </div>
                    </div>
                    <p class="text-xs text-indigo-600 mt-4 font-bold">Ajustements manuels</p>
                </div>

                <!-- Erreurs -->
                <div class="glass-card stat-card border-l-4 border-l-slate-800">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Erreurs</p>
                            <h3 class="text-2xl font-black text-slate-800 mb-0">{{ $stats['error'] }}</h3>
                        </div>
                        <div class="p-3 bg-slate-100 text-slate-800 rounded-2xl">
                            <i class="fas fa-exclamation-triangle text-lg"></i>
                        </div>
                    </div>
                    <p class="text-xs text-red-600 mt-4 font-bold">Échecs d'analyse</p>
                </div>
            </div>

            <!-- Logs Table -->
            <div class="glass-card p-6 overflow-hidden">
                <div class="d-flex align-items-center justify-content-between mb-6">
                    <h3 class="text-lg font-bold text-slate-800">Historique des Scans IA</h3>
                    <div class="badge bg-blue-50 text-blue-700 px-3 py-2 rounded-pill font-bold">
                        Usage moyen : {{ number_format($stats['avg_tokens']) }} tokens / scan
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-borderless table-hover align-middle mb-0">
                        <thead class="text-[10px] uppercase font-bold text-slate-400 border-b">
                            <tr>
                                <th>Date & Heure</th>
                                <th>Utilisateur</th>
                                <th>Document</th>
                                <th>Statut</th>
                                <th>Détails / Erreur</th>
                                <th class="text-end">Tokens</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            @forelse($recentLogs as $log)
                            <tr>
                                <td class="text-slate-500">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-indigo-600 uppercase border border-white shadow-sm">
                                            {{ substr($log->user->name ?? 'U', 0, 2) }}
                                        </div>
                                        <span class="font-semibold text-slate-700">{{ $log->user->name ?? 'Système' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="far fa-file-alt me-2 text-slate-400"></i>
                                        <span class="text-truncate font-medium" style="max-width: 150px;">{{ $log->image_nom }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($log->status === 'success')
                                        <span class="badge rounded-pill bg-label-success px-3">SUCCÈS</span>
                                    @elseif($log->status === 'corrected')
                                        <span class="badge rounded-pill bg-label-warning px-3">CORRIGÉ</span>
                                    @else
                                        <span class="badge rounded-pill bg-label-danger px-3">ERREUR</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->status === 'error')
                                        <small class="text-danger font-medium">{{ \Illuminate\Support\Str::limit($log->erreur_message, 50) }}</small>
                                    @else
                                        <small class="text-slate-400">Analyse optimisée</small>
                                    @endif
                                </td>
                                <td class="text-end font-bold text-slate-600">
                                    {{ ($log->prompt_tokens + $log->response_tokens) ?: '-' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-8 text-slate-400 italic">Aucun log disponible pour le moment.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($recentLogs->count() > 0)
                <div class="text-center mt-6 pt-4 border-top border-slate-100">
                    <small class="text-slate-400 font-medium">Affichage des 50 dernières activités</small>
                </div>
                @endif
            </div>
          </div>

          @include('components.footer')
          <div class="content-backdrop fade"></div>
        </div>
      </div>
    </div>
    <div class="layout-overlay layout-menu-toggle"></div>
  </div>

  <!-- Font Awesome pour les icônes -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>
