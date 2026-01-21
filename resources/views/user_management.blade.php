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
    }
    .glass-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.12);
        border-color: rgba(59, 130, 246, 0.3);
    }

    .stats-card-premium {
        position: relative;
        overflow: hidden;
    }
    .stats-card-premium::before {
        content: "";
        position: absolute;
        top: -50%;
        right: -50%;
        width: 150px;
        height: 150px;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
        border-radius: 50%;
        z-index: 0;
    }

    .avatar-premium-initial {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-weight: 800;
        font-size: 0.95rem;
        background: linear-gradient(135deg, var(--premium-blue) 0%, var(--premium-blue-light) 100%);
        color: white;
        box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
    }

    .badge-premium {
        padding: 0.5rem 1rem;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.75rem;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }
    .badge-premium-admin { background-color: #dbeafe; color: #1e40af; }
    .badge-premium-comptable { background-color: #f1f5f9; color: #475569; }
    .badge-premium-online { background-color: #dcfce7; color: #166534; }
    .badge-premium-offline { background-color: #fee2e2; color: #991b1b; }

    .premium-table thead th {
        background-color: #f8fafc;
        border-bottom: 2px solid #f1f5f9;
        color: var(--premium-slate-400);
        text-transform: uppercase;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        padding: 1.25rem 1.5rem;
    }
    .premium-table tbody td {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        font-weight: 500;
        color: var(--premium-slate-800);
    }
    .premium-table tbody tr:last-child td { border-bottom: none; }
    .premium-table tbody tr:hover { background-color: rgba(248, 250, 252, 0.8); }

    .btn-premium-add {
        background: linear-gradient(135deg, var(--premium-blue) 0%, var(--premium-blue-light) 100%);
        color: white;
        border: none;
        padding: 0.75rem 1.75rem;
        border-radius: 16px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        transition: all 0.3s ease;
        box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
    }
    .btn-premium-add:hover {
        transform: scale(1.02);
        box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.4);
        color: white;
    }

    .search-input-premium {
        border-radius: 16px;
        border: 2px solid transparent;
        background-color: #f1f5f9;
        padding: 0.65rem 1.25rem 0.65rem 3rem;
        font-weight: 600;
        transition: all 0.2s ease;
        width: 100%;
        max-width: 300px;
    }
    .search-input-premium:focus {
        background-color: white;
        border-color: var(--premium-blue-light);
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .premium-modal-content {
        background: #ffffff;
        border: none;
        border-radius: 28px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        padding: 2rem !important;
    }
    .input-field-premium {
        transition: all 0.2s ease;
        border: 2px solid #f1f5f9 !important;
        background-color: #f8fafc !important;
        border-radius: 14px !important;
        padding: 0.85rem 1.25rem !important;
        font-size: 0.9rem !important;
        font-weight: 600 !important;
        color: #0f172a !important;
        width: 100%;
    }
    .input-field-premium:focus {
        border-color: #3b82f6 !important;
        background-color: #ffffff !important;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important;
        outline: none !important;
    }
    .input-label-premium {
        font-size: 0.75rem !important;
        font-weight: 800 !important;
        color: #64748b !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        margin-bottom: 0.5rem !important;
        display: block !important;
    }

    .status-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 0.5rem;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar', ['habilitations'=> $habilitations])
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Équipe & <span class="text-gradient">Permissions</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Header & Action -->
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4 mb-8">
                            <div>
                                <h4 class="text-2xl font-black text-slate-800 mb-1">Gouvernance de l'équipe</h4>
                                <p class="text-slate-400 font-semibold mb-0">Pilotez vos collaborateurs et leurs accès sécurisés.</p>
                            </div>
                            <div>
                                <button type="button" class="btn-premium-add" data-bs-toggle="modal" data-bs-target="#modalCenterCreate">
                                    <i class="fa-solid fa-plus"></i> Nouveau Membre
                                </button>
                            </div>
                        </div>

                        <!-- KPIs Grid -->
                        <div class="row g-4 mb-8">
                            <div class="col-sm-6 col-xl-4">
                                <div class="glass-card p-6 h-100 stats-card-premium">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Effectif Total</p>
                                            <h2 class="mb-0 font-black text-slate-800">{{ number_format($totalUsers) }}</h2>
                                        </div>
                                        <div class="avatar-premium-initial" style="background: linear-gradient(135deg, #0f172a 0%, #334155 100%);">
                                            <i class="fa-solid fa-users"></i>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <div class="progress rounded-pill" style="height: 6px; background-color: #f1f5f9;">
                                            <div class="progress-bar rounded-pill bg-slate-800" style="width: 100%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-4">
                                <div class="glass-card p-6 h-100 stats-card-premium">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Session Active</p>
                                            <h2 class="mb-0 font-black text-success">{{ number_format($connectedUsers) }}</h2>
                                        </div>
                                        <div class="avatar-premium-initial" style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);">
                                            <i class="fa-solid fa-signal"></i>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <div class="progress rounded-pill" style="height: 6px; background-color: #f1f5f9;">
                                            @php $onlinePct = $totalUsers > 0 ? ($connectedUsers / $totalUsers) * 100 : 0; @endphp
                                            <div class="progress-bar rounded-pill bg-success" style="width: {{ $onlinePct }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xl-4">
                                <div class="glass-card p-6 h-100 stats-card-premium">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <p class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-1">Indisponibles</p>
                                            <h2 class="mb-0 font-black text-slate-400">{{ number_format($offlineUsers) }}</h2>
                                        </div>
                                        <div class="avatar-premium-initial" style="background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);">
                                            <i class="fa-solid fa-moon"></i>
                                        </div>
                                    </div>
                                    <div class="mt-4">
                                        <div class="progress rounded-pill" style="height: 6px; background-color: #f1f5f9;">
                                            @php $offlinePct = $totalUsers > 0 ? ($offlineUsers / $totalUsers) * 100 : 0; @endphp
                                            <div class="progress-bar rounded-pill bg-slate-300" style="width: {{ $offlinePct }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success border-0 shadow-sm rounded-2xl mb-6 d-flex align-items-center p-4">
                                <i class="fa-solid fa-circle-check fa-lg me-3"></i>
                                <span class="font-bold">{{ session('success') }}</span>
                                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif

                        <!-- Main Table Glass Card -->
                        <div class="glass-card overflow-hidden">
                            <div class="p-6 border-bottom bg-white/50 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-4">
                                <div>
                                    <h5 class="mb-0 font-black text-slate-800">Membres de l'organisation</h5>
                                    <p class="text-xs text-slate-400 font-bold uppercase mt-1">Liste consolidée des accès</p>
                                </div>
                                <div class="position-relative">
                                    <i class="fa-solid fa-magnifying-glass position-absolute text-slate-400" style="left: 1.25rem; top: 50%; transform: translateY(-50%);"></i>
                                    <input type="text" id="filter-name" class="search-input-premium" placeholder="Rechercher un membre...">
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table premium-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Membre</th>
                                            <th>Rôle & Accès</th>
                                            <th>Entité Rattachée</th>
                                            <th>Status</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="userTableBody">
                                        @php $allUsers = $admins->merge($comptables); @endphp
                                        @foreach($allUsers as $user)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-premium-initial me-4" style="{{ $user->role === 'admin' ? '' : 'background: linear-gradient(135deg, #334155 0%, #475569 100%);' }}">
                                                            {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr($user->last_name, 0, 1)) }}
                                                        </div>
                                                        <div>
                                                            <div class="font-black text-slate-800">{{ $user->name }} {{ $user->last_name }}</div>
                                                            <div class="text-xs text-slate-400 font-bold uppercase">{{ $user->email_adresse }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-column gap-1">
                                                        <span class="badge-premium {{ $user->role === 'admin' ? 'badge-premium-admin' : 'badge-premium-comptable' }}">
                                                            <i class="fa-solid {{ $user->role === 'admin' ? 'fa-crown' : 'fa-calculator' }} me-2"></i>
                                                            {{ $user->role === 'admin' ? 'Administrateur' : 'Comptable' }}
                                                        </span>
                                                        @if($user->role !== 'admin')
                                                            <div class="text-[10px] font-black text-slate-400 uppercase tracking-tighter">
                                                                {{ is_array($user->habilitations) ? count(array_filter($user->habilitations)) : 0 }} modules actifs
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($user->company)
                                                        <div class="bg-blue-50/50 p-2 rounded-xl d-inline-flex align-items-center">
                                                            <i class="fa-solid fa-building text-blue-500 me-2"></i>
                                                            <span class="text-sm font-black text-blue-900">{{ $user->company->company_name }}</span>
                                                        </div>
                                                    @else
                                                        <span class="text-slate-400 italic text-sm">Non assigné</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($user->is_online)
                                                        <span class="badge-premium badge-premium-online">
                                                            <span class="status-indicator bg-success"></span> Connecté
                                                        </span>
                                                    @else
                                                        <span class="badge-premium badge-premium-offline">
                                                            <span class="status-indicator bg-danger"></span> Hors ligne
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <div class="dropdown">
                                                        <button class="btn p-2 rounded-xl hover:bg-slate-50 transition-colors dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                                            <i class="fa-solid fa-ellipsis-vertical text-slate-400"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-end p-2 border-0 shadow-xl rounded-2xl">
                                                            <a class="dropdown-item py-2 rounded-xl btn-see-user" href="javascript:void(0);"
                                                               data-bs-toggle="modal" data-bs-target="#modalCenterSee"
                                                               data-user-id="{{ $user->id }}"
                                                               data-user-name="{{ $user->name }}"
                                                               data-user-lastname="{{ $user->last_name }}"
                                                               data-user-email="{{ $user->email_adresse }}"
                                                               data-user-role="{{ $user->role }}"
                                                               data-user-habilitations='@json($user->habilitations)'
                                                               data-user-company-id="{{ $user->company->id ?? '' }}"
                                                               data-user-company-name="{{ $user->company->company_name ?? 'N/A' }}">
                                                                <i class="fa-solid fa-address-card me-2 text-slate-400"></i> <span class="fw-bold text-slate-600">Profil & Accès</span>
                                                            </a>
                                                            <a class="dropdown-item py-2 rounded-xl btn-edit-user" href="javascript:void(0);"
                                                               data-bs-toggle="modal" data-bs-target="#modalCenterUpdate"
                                                               data-user-id="{{ $user->id }}"
                                                               data-user-name="{{ $user->name }}"
                                                               data-user-lastname="{{ $user->last_name }}"
                                                               data-user-email="{{ $user->email_adresse }}"
                                                               data-user-role="{{ $user->role }}"
                                                               data-user-habilitations='@json($user->habilitations)'
                                                               data-user-company-id="{{ $user->company->id ?? '' }}"
                                                               data-user-company-name="{{ $user->company->company_name ?? 'N/A' }}">
                                                                <i class="fa-solid fa-user-pen me-2 text-blue-500"></i> <span class="fw-bold text-slate-600">Modifier</span>
                                                            </a>
                                                            @if(auth()->id() !== $user->id)
                                                                <div class="dropdown-divider opacity-50"></div>
                                                                <a class="dropdown-item py-2 rounded-xl text-danger" href="javascript:void(0);"
                                                                   data-bs-toggle="modal" data-bs-target="#deleteUserModal"
                                                                   data-user-id="{{ $user->id }}"
                                                                   data-user-name="{{ $user->name }} {{ $user->last_name }}">
                                                                    <i class="fa-solid fa-trash-can me-2"></i> <span class="fw-bold">Supprimer</span>
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Modals Included -->
                        @include('user_management.modals') 

                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('components.footer')
    
    <!-- JS Variables for External Script -->
    <script>
        const usersUpdateBaseUrl = "{{ route('users.update', ['id' => '__ID__']) }}";
        const usersDeleteUrl = "{{ route('users.destroy', ['id' => '__ID__']) }}";
    </script>
    <script src="{{ asset('js/user_m.js') }}"></script>
    
    <script>
        // Inline JS for complex behaviors if needed
        document.addEventListener('DOMContentLoaded', function () {
            // Search filter
            const searchInput = document.getElementById('filter-name');
            const tableBody = document.getElementById('userTableBody');
            
            if(searchInput) {
                searchInput.addEventListener('input', function() {
                    const filter = searchInput.value.toLowerCase();
                    const rows = tableBody.getElementsByTagName('tr');
                    
                    for (let i = 0; i < rows.length; i++) {
                        const name = rows[i].cells[0].textContent.toLowerCase();
                        const email = rows[i].cells[0].querySelector('.text-xs').textContent.toLowerCase();
                        if (name.includes(filter) || email.includes(filter)) {
                            rows[i].style.display = "";
                        } else {
                            rows[i].style.display = "none";
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
