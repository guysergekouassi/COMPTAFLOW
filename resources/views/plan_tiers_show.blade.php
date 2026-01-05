@include('components.head')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            @include('components.sidebar')
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->
                @include('components.header', ['page_title' => 'Détails du <span class="text-gradient">Tiers</span> <span class="inline-block px-3 py-0.5 text-xs font-bold tracking-widest text-blue-700 uppercase bg-blue-50 rounded-full ml-3">Vue détaillée</span>'])
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Notifications -->
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show mb-6 rounded-2xl shadow-sm border-0 bg-green-50 text-green-800" role="alert">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-check-circle text-xl"></i>
                                    <span class="font-medium">{{ session('success') }}</span>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show mb-6 rounded-2xl shadow-sm border-0 bg-red-50 text-red-800" role="alert">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-exclamation-circle text-xl"></i>
                                    <span class="font-medium">{{ session('error') }}</span>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif

                        <!-- Breadcrumb -->
                        <nav class="flex mb-8" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                                <li class="inline-flex items-center">
                                    <a href="{{ route('plan_tiers') }}" class="inline-flex items-center text-sm font-medium text-slate-600 hover:text-blue-700">
                                        <i class="bx bx-group mr-2"></i>
                                        Plan Tiers
                                    </a>
                                </li>
                                <li aria-current="page">
                                    <div class="flex items-center">
                                        <i class="bx bx-chevron-right text-slate-400 mx-2"></i>
                                        <span class="text-sm font-medium text-slate-500">{{ $tier->intitule }}</span>
                                    </div>
                                </li>
                            </ol>
                        </nav>

                        <!-- Header Card -->
                        <div class="glass-card mb-8 overflow-hidden border-0 shadow-2xl">
                            <div class="bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-700 p-8 text-white relative overflow-hidden">
                                <!-- Background Pattern -->
                                <div class="absolute inset-0 opacity-10">
                                    <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full -mr-32 -mt-32"></div>
                                    <div class="absolute bottom-0 left-0 w-48 h-48 bg-white rounded-full -ml-24 -mb-24"></div>
                                </div>
                                
                                <!-- Content -->
                                <div class="relative z-10">
                                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                                        <!-- Left Section -->
                                        <div class="flex items-center space-x-4">
                                            <div class="w-20 h-20 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center shadow-lg border border-white/30">
                                                @if(\Illuminate\Support\Str::contains(strtolower($tier->type_de_tiers), 'client'))
                                                    <i class="bx bx-user text-4xl"></i>
                                                @elseif(\Illuminate\Support\Str::contains(strtolower($tier->type_de_tiers), 'fournisseur'))
                                                    <i class="bx bx-building text-4xl"></i>
                                                @else
                                                    <i class="bx bx-user-circle text-4xl"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <h1 class="text-3xl font-bold mb-2 tracking-tight">{{ $tier->intitule }}</h1>
                                                <div class="flex flex-wrap items-center gap-3">
                                                    <span class="px-4 py-2 bg-white/20 backdrop-blur-md rounded-xl text-xs font-black uppercase tracking-wider border border-white/30 shadow-sm">
                                                        {{ $tier->type_de_tiers }}
                                                    </span>
                                                    <div class="flex items-center space-x-2 bg-white/10 backdrop-blur-sm px-3 py-2 rounded-lg border border-white/20">
                                                        <i class="bx bx-hash text-sm"></i>
                                                        <span class="font-mono font-semibold">{{ $tier->numero_de_tiers }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Right Section -->
                                        <div class="flex flex-col sm:flex-row gap-3 lg:ml-8">
                                            <a href="{{ route('plan_tiers') }}" 
                                               class="inline-flex items-center justify-center px-6 py-3 bg-white/20 backdrop-blur-md rounded-xl font-semibold hover:bg-white/30 transition-all duration-300 border border-white/30 shadow-sm hover:shadow-lg hover:-translate-y-0.5">
                                                <i class="bx bx-arrow-back mr-2"></i>
                                                Retour liste
                                            </a>
                                            <button type="button" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalCenterUpdate"
                                                    class="inline-flex items-center justify-center px-6 py-3 bg-white text-indigo-700 rounded-xl font-semibold hover:bg-indigo-50 transition-all duration-300 shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                                                <i class="bx bx-edit-alt mr-2"></i>
                                                Modifier
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics Cards -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                            <div class="glass-card !p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                        <i class="bx bx-file-text text-blue-600 text-xl"></i>
                                    </div>
                                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total</span>
                                </div>
                                <h3 class="text-2xl font-bold text-slate-800">{{ number_format($stats['total_ecritures'], 0, ',', ' ') }}</h3>
                                <p class="text-sm text-slate-500 mt-1">Écritures comptables</p>
                            </div>

                            <div class="glass-card !p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                                        <i class="bx bx-arrow-from-left text-green-600 text-xl"></i>
                                    </div>
                                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Débit</span>
                                </div>
                                <h3 class="text-2xl font-bold text-green-600">{{ number_format($stats['total_debit'], 0, ',', ' ') }} FCFA</h3>
                                <p class="text-sm text-slate-500 mt-1">Total débité</p>
                            </div>

                            <div class="glass-card !p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                                        <i class="bx bx-arrow-to-right text-orange-600 text-xl"></i>
                                    </div>
                                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Crédit</span>
                                </div>
                                <h3 class="text-2xl font-bold text-orange-600">{{ number_format($stats['total_credit'], 0, ',', ' ') }} FCFA</h3>
                                <p class="text-sm text-slate-500 mt-1">Total crédité</p>
                            </div>

                            <div class="glass-card !p-6">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="w-12 h-12 {{ $stats['solde'] >= 0 ? 'bg-indigo-100' : 'bg-red-100' }} rounded-xl flex items-center justify-center">
                                        <i class="bx bx-balance {{ $stats['solde'] >= 0 ? 'text-indigo-600' : 'text-red-600' }} text-xl"></i>
                                    </div>
                                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Solde</span>
                                </div>
                                <h3 class="text-2xl font-bold {{ $stats['solde'] >= 0 ? 'text-indigo-600' : 'text-red-600' }}">
                                    {{ number_format(abs($stats['solde']), 0, ',', ' ') }} FCFA
                                </h3>
                                <p class="text-sm text-slate-500 mt-1">{{ $stats['solde'] >= 0 ? 'Solde débiteur' : 'Solde créditeur' }}</p>
                            </div>
                        </div>

                        <!-- Information Cards -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                            <!-- Informations générales -->
                            <div class="glass-card">
                                <div class="p-6 border-b border-slate-100">
                                    <h3 class="text-lg font-bold text-slate-800 flex items-center">
                                        <i class="bx bx-info-circle text-blue-600 mr-2"></i>
                                        Informations générales
                                    </h3>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="flex justify-between items-center py-3 border-b border-slate-50">
                                        <span class="text-sm font-medium text-slate-500">Numéro de tiers</span>
                                        <span class="font-mono font-bold text-blue-700">{{ $tier->numero_de_tiers }}</span>
                                    </div>
                                    <div class="flex justify-between items-center py-3 border-b border-slate-50">
                                        <span class="text-sm font-medium text-slate-500">Nom / Raison sociale</span>
                                        <span class="font-semibold text-slate-800">{{ $tier->intitule }}</span>
                                    </div>
                                    <div class="flex justify-between items-center py-3 border-b border-slate-50">
                                        <span class="text-sm font-medium text-slate-500">Type de tiers</span>
                                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-lg text-xs font-black uppercase tracking-wider">
                                            {{ $tier->type_de_tiers }}
                                        </span>
                                    </div>
                                    @if($tier->compte)
                                    <div class="flex justify-between items-center py-3">
                                        <span class="text-sm font-medium text-slate-500">Compte rattaché</span>
                                        <div class="text-right">
                                            <span class="font-mono font-bold text-slate-800">{{ $tier->compte->numero_de_compte }}</span>
                                            <p class="text-xs text-slate-500 mt-1">{{ $tier->compte->intitule }}</p>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Résumé financier -->
                            <div class="glass-card">
                                <div class="p-6 border-b border-slate-100">
                                    <h3 class="text-lg font-bold text-slate-800 flex items-center">
                                        <i class="bx bx-chart text-green-600 mr-2"></i>
                                        Résumé financier
                                    </h3>
                                </div>
                                <div class="p-6">
                                    <div class="space-y-4">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm font-medium text-slate-500">Total des opérations</span>
                                            <span class="font-bold text-slate-800">{{ number_format($stats['total_debit'] + $stats['total_credit'], 0, ',', ' ') }} FCFA</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm font-medium text-slate-500">Solde actuel</span>
                                            <span class="font-bold {{ $stats['solde'] >= 0 ? 'text-indigo-600' : 'text-red-600' }}">
                                                {{ number_format(abs($stats['solde']), 0, ',', ' ') }} FCFA
                                                {{ $stats['solde'] >= 0 ? ' (D)' : ' (C)' }}
                                            </span>
                                        </div>
                                        <div class="mt-6 p-4 bg-slate-50 rounded-xl">
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Répartition</span>
                                            </div>
                                            @php
                                                $totalOperations = $stats['total_debit'] + $stats['total_credit'];
                                                $debitPercentage = $totalOperations > 0 ? ($stats['total_debit'] / $totalOperations) * 100 : 0;
                                                $creditPercentage = $totalOperations > 0 ? ($stats['total_credit'] / $totalOperations) * 100 : 0;
                                            @endphp
                                            <div class="space-y-2">
                                                <div class="flex items-center">
                                                    <span class="text-xs font-medium text-green-600 w-16">Débit</span>
                                                    <div class="flex-1 bg-slate-200 rounded-full h-2 overflow-hidden">
                                                        <div class="bg-green-500 h-full rounded-full" style="width: {{ $debitPercentage }}%"></div>
                                                    </div>
                                                    <span class="text-xs font-medium text-slate-600 ml-2">{{ round($debitPercentage) }}%</span>
                                                </div>
                                                <div class="flex items-center">
                                                    <span class="text-xs font-medium text-orange-600 w-16">Crédit</span>
                                                    <div class="flex-1 bg-slate-200 rounded-full h-2 overflow-hidden">
                                                        <div class="bg-orange-500 h-full rounded-full" style="width: {{ $creditPercentage }}%"></div>
                                                    </div>
                                                    <span class="text-xs font-medium text-slate-600 ml-2">{{ round($creditPercentage) }}%</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Transactions -->
                        @if($tier->ecritures && $tier->ecritures->count() > 0)
                        <div class="glass-card">
                            <div class="p-6 border-b border-slate-100">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-bold text-slate-800 flex items-center">
                                        <i class="bx bx-history text-indigo-600 mr-2"></i>
                                        Dernières écritures
                                    </h3>
                                    <span class="text-sm text-slate-500">10 plus récentes</span>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead class="bg-slate-50/50 border-b border-slate-100">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Référence</th>
                                            <th class="px-6 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Libellé</th>
                                            <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Débit</th>
                                            <th class="px-6 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Crédit</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @foreach($tier->ecritures as $ecriture)
                                        <tr class="hover:bg-slate-50 transition">
                                            <td class="px-6 py-4 text-sm text-slate-600">{{ \Carbon\Carbon::parse($ecriture->date)->format('d/m/Y') }}</td>
                                            <td class="px-6 py-4 text-sm font-mono text-slate-800">{{ $ecriture->n_saisie }}</td>
                                            <td class="px-6 py-4 text-sm text-slate-800">{{ $ecriture->description_operation }}</td>
                                            <td class="px-6 py-4 text-sm text-right font-medium text-green-600">
                                                {{ $ecriture->debit > 0 ? number_format($ecriture->debit, 0, ',', ' ') . ' FCFA' : '-' }}
                                            </td>
                                            <td class="px-6 py-4 text-sm text-right font-medium text-orange-600">
                                                {{ $ecriture->credit > 0 ? number_format($ecriture->credit, 0, ',', ' ') . ' FCFA' : '-' }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @else
                        <div class="glass-card">
                            <div class="p-12 text-center">
                                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="bx bx-inbox text-2xl text-slate-400"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-slate-800 mb-2">Aucune écriture</h3>
                                <p class="text-sm text-slate-500">Ce tiers n'a aucune écriture comptable enregistrée.</p>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('components.footer')

    <!-- Modal Modification -->
    <div class="modal fade" id="modalCenterUpdate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <form method="POST" action="{{ route('plan_tiers.update', ['id' => $tier->id]) }}" id="updateTiersForm" class="w-full">
                @csrf
                @method('PUT')
                <input type="hidden" id="update_id" name="id" value="{{ $tier->id }}">
                
                <div class="modal-content premium-modal-content">
                    <!-- En-tête -->
                    <div class="text-center mb-6 position-relative">
                        <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                            Modifier <span class="text-blue-gradient-premium">Tiers</span>
                        </h1>
                        <div class="h-1 w-8 bg-blue-700 mx-auto mt-2 rounded-full"></div>
                    </div>

                    <div class="space-y-3">
                        <!-- Catégorie -->
                        <div class="space-y-1">
                            <label class="input-label-premium">Catégorie</label>
                            <select id="update_type_de_tiers" name="type_de_tiers" class="input-field-premium" required>
                                <option value="">Sélectionner une catégorie</option>
                                @foreach (['Fournisseur', 'Client', 'Personnel', 'Impots', 'CNPS', 'Associé', 'Divers Tiers'] as $type)
                                    <option value="{{ $type }}" {{ $tier->type_de_tiers == $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Compte de Rattachement -->
                        <div class="space-y-1">
                            <label class="input-label-premium">Compte de Rattachement</label>
                            <select id="update_compte" name="compte_general" class="input-field-premium" required>
                                <option value="">-- Sélectionnez un compte --</option>
                                @foreach (\App\Models\PlanComptable::where('numero_de_compte', 'LIKE', '4%')->orderByRaw("LPAD(numero_de_compte, 20, '0')")->get() as $compte)
                                    <option value="{{ $compte->id }}" data-numero="{{ $compte->numero_de_compte }}" {{ $tier->compte_general == $compte->id ? 'selected' : '' }}>
                                        {{ $compte->numero_de_compte }} - {{ $compte->intitule }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Numéro de tiers -->
                        <div class="space-y-1">
                            <label class="input-label-premium">Numéro de tiers</label>
                            <input type="text" id="update_numero" name="numero_de_tiers" 
                                class="input-field-premium opacity-75" value="{{ $tier->numero_de_tiers }}" required readonly>
                        </div>

                        <!-- Nom / Raison Sociale -->
                        <div class="space-y-1">
                            <label class="input-label-premium">Nom / Raison Sociale</label>
                            <input type="text" id="update_intitule" name="intitule" 
                                class="input-field-premium" value="{{ $tier->intitule }}" required>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="grid grid-cols-2 gap-4 pt-6">
                        <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">
                            Annuler
                        </button>
                        <button type="submit" class="btn-save-premium">
                            Mettre à jour
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <style>
        .glass-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 35px -5px rgba(0, 0, 0, 0.1);
        }

        .premium-modal-content {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 1);
            border-radius: 20px;
            box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.1);
            font-family: 'Plus Jakarta Sans', sans-serif;
            max-width: 500px;
            margin: auto;
            padding: 1.5rem !important;
        }

        .input-field-premium {
            transition: all 0.2s ease;
            border: 2px solid #f1f5f9 !important;
            background-color: #f8fafc !important;
            border-radius: 12px !important;
            padding: 0.75rem 1rem !important;
            font-size: 0.8rem !important;
            font-weight: 600 !important;
            color: #0f172a !important;
            width: 100%;
        }

        .input-field-premium:focus {
            border-color: #1e40af !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.05) !important;
            outline: none !important;
        }

        .text-blue-gradient-premium {
            background: linear-gradient(to right, #1e40af, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
        }

        .input-label-premium {
            font-size: 0.7rem !important;
            font-weight: 800 !important;
            color: #64748b !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            margin-left: 0.1rem !important;
            margin-bottom: 0.35rem !important;
            display: block !important;
        }

        .btn-save-premium {
            padding: 0.75rem 1rem !important;
            border-radius: 12px !important;
            background-color: #1e40af !important;
            color: white !important;
            font-weight: 800 !important;
            font-size: 0.7rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            box-shadow: 0 4px 6px -1px rgba(30, 64, 175, 0.1) !important;
            transition: all 0.2s ease !important;
            border: none !important;
        }

        .btn-save-premium:hover {
            background-color: #1e3a8a !important;
            transform: translateY(-2px) !important;
        }

        .btn-cancel-premium {
            padding: 0.75rem 1rem !important;
            border-radius: 12px !important;
            color: #94a3b8 !important;
            font-weight: 700 !important;
            font-size: 0.7rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            transition: all 0.2s ease !important;
            border: none !important;
            background: transparent !important;
        }

        .btn-cancel-premium:hover {
            background-color: #f8fafc !important;
            color: #475569 !important;
        }
    </style>
</body>
</html>
