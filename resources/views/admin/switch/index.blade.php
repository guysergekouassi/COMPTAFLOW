@include('components.head')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap');

    :root {
        --premium-blue: #1e40af;
        --premium-blue-light: #3b82f6;
        --premium-slate-900: #0f172a;
        --premium-slate-800: #1e293b;
        --premium-slate-400: #94a3b8;
        --glass-bg: rgba(255, 255, 255, 0.8);
        --glass-border: rgba(226, 232, 240, 0.8);
    }

    body {
        background: #f8fafc url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 86c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm66 3c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm-46-45c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zm54 24c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM57 11c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM25 34c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm23 40c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm-3-47c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm47 9c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zM9 53c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm28 24c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm33-47c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm-8 48c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm-48-8c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm54-32c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zM22 63c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm30-26c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm28-4c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zM59 71c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zM33 18c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm44 64c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zM9 29c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm28 3c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm37 13c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zM61 81c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zM4 62c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm35 24c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm47-9c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1zm7-48c.552 0 1-.448 1-1s-.448-1-1-1-1 .448-1 1 .448 1 1 1z' fill='%23e2e8f0' fill-opacity='0.4' fill-rule='evenodd'/%3E%3C/svg%3E");
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: var(--premium-slate-800);
        min-height: 100vh;
    }

    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--glass-border);
        border-radius: 24px;
        box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.08);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }
    .glass-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.12);
        border-color: rgba(59, 130, 246, 0.3);
    }

    .switch-avatar-premium {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .btn-premium-action {
        background: linear-gradient(135deg, var(--premium-blue) 0%, var(--premium-blue-light) 100%);
        color: white;
        border: none;
        padding: 0.6rem 1.5rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
    }
    .btn-premium-action:hover {
        transform: scale(1.05);
        box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.4);
        color: white;
    }

    .btn-premium-outline {
        background: transparent;
        color: var(--premium-blue);
        border: 2px solid var(--premium-blue);
        padding: 0.5rem 1.5rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.85rem;
        transition: all 0.3s ease;
    }
    .btn-premium-outline:hover {
        background: var(--premium-blue);
        color: white;
        transform: scale(1.05);
    }

    .list-group-item-premium {
        background: transparent;
        border: 1px solid transparent;
        margin-bottom: 0.5rem;
        border-radius: 16px !important;
        transition: all 0.2s ease;
        padding: 1rem 1.25rem;
    }
    .list-group-item-premium:hover {
        background: white;
        border-color: var(--glass-border);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .active-badge-premium {
        background: #dcfce7;
        color: #166534;
        font-weight: 800;
        font-size: 0.65rem;
        text-transform: uppercase;
        padding: 0.4rem 0.8rem;
        border-radius: 10px;
        letter-spacing: 0.05em;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Pilotage <span class="text-gradient">Multi-Entités</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Header & Action -->
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 mb-8">
                            <div>
                                <h4 class="text-2xl font-black text-slate-800 mb-1">Passerelle Administrative</h4>
                                <p class="text-slate-400 font-semibold mb-0">Basculez entre vos structures ou gérez les accès collaborateurs.</p>
                            </div>
                            <div>
                                <a href="{{ route('admin.companies.create') }}" class="btn-premium-action py-3 px-6 rounded-2xl">
                                    <i class="fa-solid fa-plus-circle me-2"></i> Ajouter une entreprise
                                </a>
                            </div>
                        </div>

                        <div class="row g-6">
                            <!-- Section Entreprises -->
                            <div class="col-xl-6">
                                <div class="glass-card">
                                    <div class="p-6 border-bottom bg-white/50 d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-0 font-black text-slate-800"><i class="fa-solid fa-building me-3 text-blue-600"></i>Mes Portefeuilles</h5>
                                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Structures comptables actives</p>
                                        </div>
                                        <div class="avatar-premium-initial bg-blue-100 text-blue-700" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                            {{ $managedCompanies->count() }}
                                        </div>
                                    </div>
                                    <div class="p-4">
                                        <div class="list-group list-group-flush">
                                            @foreach($managedCompanies as $company)
                                                <div class="list-group-item list-group-item-premium d-flex justify-content-between align-items-center {{ $company->parent_company_id ? 'ms-8 border-start-2 border-blue-50' : '' }}">
                                                    <div class="d-flex align-items-center">
                                                        <div class="switch-avatar-premium me-4 {{ auth()->user()->company_id == $company->id ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : ($company->parent_company_id ? 'bg-slate-100 text-slate-400' : 'bg-slate-200 text-slate-700') }}">
                                                            <i class="fa-solid {{ $company->parent_company_id ? 'fa-diagram-project' : 'fa-building-columns' }}"></i>
                                                        </div>
                                                        <div>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <h6 class="mb-0 font-black text-slate-800">{{ $company->company_name }}</h6>
                                                                <span class="text-[8px] font-black px-1.5 py-0.5 rounded border {{ $company->parent_company_id ? 'text-blue-500 border-blue-100 bg-blue-50' : 'text-slate-500 border-slate-200 bg-slate-50' }} uppercase tracking-tighter">
                                                                    {{ $company->parent_company_id ? 'Sous-entité' : 'Siège' }}
                                                                </span>
                                                            </div>
                                                            <div class="text-[10px] text-slate-400 font-bold uppercase truncate" style="max-width: 250px;">
                                                                {{ $company->email_adresse ?? 'Aucun email configuré' }}
                                                                @if($company->parent_company_id)
                                                                    <span class="text-blue-300 ms-1">• Unité de gestion</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if(auth()->user()->company_id == $company->id)
                                                        <span class="active-badge-premium border border-green-200">
                                                            <i class="fa-solid fa-circle-check me-1"></i> Dossier Actif
                                                        </span>
                                                    @else
                                                        <a href="{{ route('switch_company', $company->id) }}" class="btn-premium-action px-6 py-2">
                                                            Basculer
                                                        </a>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section Utilisateurs -->
                            <div class="col-xl-6">
                                <div class="glass-card">
                                    <div class="p-6 border-bottom bg-white/50 d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-0 font-black text-slate-800"><i class="fa-solid fa-user-shield me-3 text-indigo-600"></i>Incarner un Profil</h5>
                                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Gestion des comptes collaborateurs</p>
                                        </div>
                                        <div class="avatar-premium-initial bg-indigo-100 text-indigo-700" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                            {{ $managedUsers->count() }}
                                        </div>
                                    </div>
                                    <div class="p-4">
                                        <div class="list-group list-group-flush">
                                            @foreach($managedUsers as $user)
                                                <div class="list-group-item list-group-item-premium d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <div class="switch-avatar-premium me-4 bg-indigo-50 text-indigo-700 font-black">
                                                            {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 font-black text-slate-800">{{ $user->name }} {{ $user->last_name }}</h6>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-tight">{{ $user->role }}</span>
                                                                <span class="text-[10px] font-black text-blue-500 uppercase tracking-tight">• {{ $user->company->company_name ?? 'Sans Entité' }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <a href="{{ route('admin.impersonate', $user->id) }}" class="btn-premium-outline px-4">
                                                        <i class="fa-solid fa-user-ninja me-2"></i> Incarner
                                                    </a>
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
</body>
</html>
