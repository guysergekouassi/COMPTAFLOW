@include('components.head')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/boxicons/2.1.4/css/boxicons.min.css" />

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap');

    body {
        background-color: #f1f5f9;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .master-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border-radius: 24px;
        padding: 3rem;
        color: white;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .glass-table-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        border-radius: 24px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
    }

    .btn-premium {
        background: #1d4ed8;
        color: white;
        border: none;
        padding: 0.8rem 2rem;
        border-radius: 12px;
        font-weight: 700;
        transition: all 0.3s ease;
    }
    .btn-premium:hover {
        background: #1e40af;
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(29, 78, 216, 0.3);
    }

    .badge-info-gold {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
        padding: 0.5rem 1rem;
        border-radius: 30px;
        font-weight: 700;
        font-size: 0.7rem;
        text-transform: uppercase;
    }

    .status-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Définition <span class="text-primary">Postes</span> Tresorerie'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Notifications -->
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show mb-4 rounded-xl border-0 shadow-sm" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-check-circle me-2 fs-4"></i>
                                    <div>{{ session('success') }}</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show mb-4 rounded-xl border-0 shadow-sm" role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-error-circle me-2 fs-4"></i>
                                    <div>{{ session('error') }}</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                            </div>
                        @endif

                        <div class="master-header shadow-2xl">
                            <div class="row align-items-center">
                                <div class="col-lg-8">
                                    <span class="badge-info-gold mb-4 d-inline-block">Configuration de la Trésorerie</span>
                                    <h1 class="font-black mb-2 tracking-tighter">Postes de Trésorerie</h1>
                                    <p class="opacity-70 font-medium">Définissez ici les rubriques budgétaires et flux de trésorerie pour l'entreprise <strong>{{ $mainCompany->name }}</strong>.</p>
                                </div>
                                <div class="col-lg-4 text-end">
                                    <button class="btn btn-premium" data-bs-toggle="modal" data-bs-target="#modalCreatePoste">
                                        <i class="bx bx-plus me-2"></i> Nouveau Poste
                                    </button>
                                </div>
                            </div>
                            <div class="position-absolute end-0 top-0 opacity-10" style="transform: translate(20%, -20%) rotate(-15deg);">
                                <i class="bx bx-wallet fa-10x" style="font-size: 10rem;"></i>
                            </div>
                        </div>

                        <div class="glass-table-card overflow-hidden">
                            <div class="p-8 border-b border-slate-100 d-flex justify-content-between align-items-center bg-white">
                                <div>
                                    <h4 class="font-black mb-0">Liste des Postes</h4>
                                    <p class="text-slate-400 text-sm mb-0">Classification des flux monétaires.</p>
                                </div>
                                <div class="d-flex gap-2">
                                    <input type="text" id="searchPoste" class="form-control border-slate-200 rounded-xl" placeholder="Rechercher...">
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="postesTable">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="ps-8 py-5 text-uppercase text-xs font-black text-slate-400">Intitulé du Poste</th>
                                            <th class="py-5 text-uppercase text-xs font-black text-slate-400">Catégorie</th>
                                            <th class="pe-8 py-5 text-uppercase text-xs font-black text-slate-400 text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white">
                                        @foreach($postesTresorerie as $poste)
                                        <tr>
                                            <td class="ps-8 py-6">
                                                <span class="font-black text-slate-700 fs-5">{{ $poste->name }}</span>
                                            </td>
                                            <td class="py-6">
                                                @if($poste->category)
                                                    <span class="status-pill border bg-indigo-100 text-indigo-700 border-indigo-200">
                                                        <i class="bx bx-category me-1"></i>
                                                        {{ $poste->category->name }}
                                                    </span>
                                                @else
                                                    <span class="status-pill border bg-slate-100 text-slate-600 border-slate-200">
                                                        Non catégorisé
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="pe-8 py-6 text-end">
                                                <div class="btn-group">
                                                    <button class="btn btn-icon btn-sm btn-outline-primary border-0 rounded-circle" 
                                                        onclick="editPoste({{ $poste->id }}, '{{ addslashes($poste->name) }}', {{ $poste->category_id ?? 'null' }})" 
                                                        title="Modifier">
                                                        <i class="bx bx-edit-alt"></i>
                                                    </button>
                                                    <form action="{{ route('admin.config.delete_tresorerie_post', $poste->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce poste de trésorerie ?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-icon btn-sm btn-outline-danger border-0 rounded-circle" title="Supprimer">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                        @if($postesTresorerie->isEmpty())
                                        <tr>
                                            <td colspan="3" class="text-center py-10 text-slate-400 italic">
                                                Aucun poste de trésorerie défini pour le moment.
                                            </td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="p-6 bg-slate-50 text-center">
                                <p class="text-xs text-slate-400 mb-0 font-medium">Ces postes seront disponibles pour les utilisateurs dans l'espace Trésorerie.</p>
                            </div>
                        </div>

                    </div>
                    @include('components.footer')
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Create -->
    <div class="modal fade" id="modalCreatePoste" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden">
                <form action="{{ route('admin.config.store_tresorerie_post') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-slate-900 p-6">
                        <h5 class="modal-title text-white font-black">Nouveau Poste de Trésorerie</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-8">
                        <div class="row g-6">
                            <div class="col-12 mb-4">
                                <label class="form-label font-black text-slate-700 uppercase text-xs">Nom du Poste</label>
                                <input type="text" name="name" class="form-control border-slate-200 py-3 rounded-xl shadow-none focus:border-primary" placeholder="Ex: Ventes de marchandises" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label font-black text-slate-700 uppercase text-xs">Catégorie</label>
                                <select name="category_id" class="form-select border-slate-200 py-3 rounded-xl focus:border-primary shadow-none font-bold" required>
                                    <option value="" disabled selected>-- Sélectionner une catégorie --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @if($categories->isEmpty())
                                    <small class="text-muted">
                                        <i class="bx bx-info-circle"></i>
                                        Aucune catégorie disponible. 
                                        <a href="{{ route('admin.config.treasury_categories') }}" class="text-primary">Créer une catégorie</a>
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-slate-50 p-6 border-0">
                        <button type="button" class="btn btn-outline-secondary font-bold px-6 py-3 rounded-xl" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary font-black px-8 py-3 rounded-xl shadow-lg shadow-primary/20">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEditPoste" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden">
                <form id="editPosteForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-slate-900 p-6">
                        <h5 class="modal-title text-white font-black">Modifier le Poste</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-8">
                        <div class="row g-6">
                            <div class="col-12 mb-4">
                                <label class="form-label font-black text-slate-700 uppercase text-xs">Nom du Poste</label>
                                <input type="text" name="name" id="edit_name" class="form-control border-slate-200 py-3 rounded-xl shadow-none focus:border-primary" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label font-black text-slate-700 uppercase text-xs">Catégorie</label>
                                <select name="category_id" id="edit_category_id" class="form-select border-slate-200 py-3 rounded-xl focus:border-primary shadow-none font-bold" required>
                                    <option value="" disabled>-- Sélectionner une catégorie --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-slate-50 p-6 border-0">
                        <button type="button" class="btn btn-outline-secondary font-bold px-6 py-3 rounded-xl" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary font-black px-8 py-3 rounded-xl shadow-lg shadow-primary/20">Mettre à jour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Recherche
        document.getElementById('searchPoste')?.addEventListener('input', function() {
            const searchValue = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#postesTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });

        // Edition
        function editPoste(id, name, categoryId) {
            const form = document.getElementById('editPosteForm');
            form.action = `/admin/config/update-tresorerie-post/${id}`;
            
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_category_id').value = categoryId;
            
            new bootstrap.Modal(document.getElementById('modalEditPoste')).show();
        }
    </script>
</body>
</html>
