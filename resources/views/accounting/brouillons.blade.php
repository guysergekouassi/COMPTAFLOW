<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">
@include('components.head')
<style>
    .glass-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        margin-bottom: 2rem;
    }
    .glass-card:hover {
        box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.1);
    }
    .batch-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
        border-radius: 16px 16px 0 0;
    }
    .batch-title {
        font-weight: 700;
        color: #1e293b;
        font-size: 0.95rem;
    }
    .batch-meta {
        font-size: 0.8rem;
        color: #64748b;
    }
    .batch-actions {
        display: flex;
        gap: 0.75rem;
    }
    .btn-load {
        background: #1e40af;
        color: white !important;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-load:hover {
        background: #1e3a8a;
        transform: translateY(-1px);
    }
    .btn-delete-batch {
        background: #fee2e2;
        color: #dc2626;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 600;
        border: none;
        transition: all 0.2s;
    }
    .btn-delete-batch:hover {
        background: #fecaca;
        transform: translateY(-1px);
    }
    .batch-table {
        width: 100%;
        border-collapse: collapse;
    }
    .batch-table th {
        background: #ffffff;
        padding: 0.75rem 1.5rem;
        font-size: 0.7rem;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        text-align: left;
    }
    .batch-table td {
        padding: 0.75rem 1.5rem;
        font-size: 0.85rem;
        color: #334155;
        border-top: 1px solid #f1f5f9;
    }
    .source-badge {
        padding: 0.25rem 0.5rem;
        border-radius: 6px;
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .source-manuel { background: #eff6ff; color: #1e40af; }
    .source-scan { background: #f0fdf4; color: #166534; }
</style>
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Brouillons <span class="text-gradient">Enregistrés</span>'])
                <div class="content-wrapper">
                    <div class="container-fluid flex-grow-1 container-p-y">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if($drafts->isEmpty())
                            <div class="glass-card p-5 text-center">
                                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4" style="width: 80px; height: 80px; background: #f8fafc; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem;">
                                    <i class="fa-solid fa-file-pen text-3xl text-slate-300" style="font-size: 2rem; color: #cbd5e1;"></i>
                                </div>
                                <h3 class="text-xl font-bold text-slate-800 mb-2">Aucun brouillon pour le moment</h3>
                                <p class="text-slate-500 max-w-md mx-auto">Vos saisies manuelles ou scans enregistrés en brouillon apparaîtront ici.</p>
                                <div class="mt-4">
                                    <a href="{{ route('accounting_entry_real') }}" class="btn btn-primary rounded-xl">Nouvelle Saisie</a>
                                </div>
                            </div>
                        @else
                            @foreach($drafts as $batchId => $lines)
                                @php $first = $lines->first(); @endphp
                                <div class="glass-card">
                                    <div class="batch-header">
                                        <div style="display: flex; align-items: center; gap: 1rem;">
                                            <span class="source-badge source-{{ $first->source }}">
                                                {{ $first->source }}
                                            </span>
                                            <div>
                                                <span class="batch-title">
                                                    {{ $first->description_operation ?: 'Sans libellé' }}
                                                </span>
                                                <div class="batch-meta">
                                                    <i class="fa-regular fa-calendar me-1"></i> {{ \Carbon\Carbon::parse($first->created_at)->format('d/m/Y H:i') }}
                                                    <span class="mx-2">•</span>
                                                    <i class="fa-solid fa-hashtag me-1"></i> {{ $lines->count() }} ligne(s)
                                                </div>
                                            </div>
                                        </div>
                                        <div class="batch-actions">
                                            @php
                                                $route = ($first->source === 'scan') ? 'ecriture.scan' : 'accounting_entry_real';
                                            @endphp
                                            <a href="{{ route($route, ['batch_id' => $batchId]) }}" class="btn-load">
                                                <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Charger
                                            </a>
                                            <form action="{{ route('brouillons.destroy', $batchId) }}" method="POST" onsubmit="return confirm('Supprimer ce brouillon ?');" style="margin: 0;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-delete-batch">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="batch-table">
                                            <thead>
                                                <tr>
                                                    <th>Compte</th>
                                                    <th>Libellé</th>
                                                    <th class="text-end">Débit</th>
                                                    <th class="text-end">Crédit</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($lines as $line)
                                                    <tr>
                                                        <td>
                                                            <div style="font-weight: 700;">{{ $line->planComptable ? $line->planComptable->numero_de_compte : '-' }}</div>
                                                            <div style="font-size: 0.75rem; color: #94a3b8;">{{ $line->planComptable ? $line->planComptable->intitule : '' }}</div>
                                                        </td>
                                                        <td>{{ $line->description_operation }}</td>
                                                        <td class="text-end" style="font-family: monospace; color: #1e40af;">{{ number_format($line->debit, 2, ',', ' ') }}</td>
                                                        <td class="text-end" style="font-family: monospace; color: #1e40af;">{{ number_format($line->credit, 2, ',', ' ') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('components.footer')
</body>
</html>
