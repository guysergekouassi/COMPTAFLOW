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

    .category-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 14px;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 700;
        background: #e0e7ff;
        color: #4338ca;
        border: 1px solid #c7d2fe;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Catégories de <span class="text-primary">Trésorerie</span>'])

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
                                    <h1 class="font-black mb-2 tracking-tighter">Catégories de Trésorerie</h1>
                                    <p class="opacity-70 font-medium">Définissez ici les catégories pour organiser vos postes de trésorerie pour l'entreprise <strong>{{ $mainCompany->name }}</strong>.</p>
                                </div>
                                <div class="col-lg-4 text-end">
                                    <button class="btn btn-premium" data-bs-toggle="modal" data-bs-target="#modalCreateCategory">
                                        <i class="bx bx-plus me-2"></i> Nouvelle Catégorie
                                    </button>
                                </div>
                            </div>
                            <div class="position-absolute end-0 top-0 opacity-10" style="transform: translate(20%, -20%) rotate(-15deg);">
                                <i class="bx bx-category fa-10x" style="font-size: 10rem;"></i>
                            </div>
                        </div>

                        <div class="glass-table-card overflow-hidden">
                            <div class="p-8 border-b border-slate-100 d-flex justify-content-between align-items-center bg-white">
                                <div>
                                    <h4 class="font-black mb-0">Liste des Catégories</h4>
                                    <p class="text-slate-400 text-sm mb-0">Classification des postes de trésorerie.</p>
                                </div>
                                <div class="d-flex gap-2">
                                    <form action="{{ route('admin.config.load_standard_treasury_categories') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary rounded-xl px-4 py-2">
                                            <i class="bx bx-download me-2"></i> Charger les Flux TFT (I, II, III)
                                        </button>
                                    </form>
                                    <input type="text" id="searchCategory" class="form-control border-slate-200 rounded-xl" placeholder="Rechercher...">
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="categoriesTable">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="ps-8 py-5 text-uppercase text-xs font-black text-slate-400">Nom de la Catégorie</th>
                                            <th class="py-5 text-uppercase text-xs font-black text-slate-400">Nombre de Postes</th>
                                            <th class="pe-8 py-5 text-uppercase text-xs font-black text-slate-400 text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white">
                                        @foreach($categories as $category)
                                        <tr>
                                            <td class="ps-8 py-6">
                                                <span class="font-black text-slate-700 fs-5">{{ $category->name }}</span>
                                            </td>
                                            <td class="py-6">
                                                <span class="category-badge">
                                                    <i class="bx bx-wallet me-1"></i>
                                                    {{ $category->postes_count }} {{ $category->postes_count > 1 ? 'postes' : 'poste' }}
                                                </span>
                                            </td>
                                            <td class="pe-8 py-6 text-end">
                                                <div class="btn-group">
                                                    <button class="btn btn-icon btn-sm btn-outline-primary border-0 rounded-circle" 
                                                        onclick="editCategory({{ $category->id }}, '{{ addslashes($category->name) }}')" 
                                                        title="Modifier">
                                                        <i class="bx bx-edit-alt"></i>
                                                    </button>
                                                    <form action="{{ route('admin.config.delete_treasury_category', $category->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette catégorie ? (Impossible si des postes l\'utilisent)');">
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
                                        @if($categories->isEmpty())
                                        <tr>
                                            <td colspan="3" class="text-center py-10 text-slate-400 italic">
                                                Aucune catégorie définie pour le moment.
                                            </td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="p-6 bg-slate-50 text-center">
                                <p class="text-xs text-slate-400 mb-0 font-medium">Ces catégories permettent d'organiser les postes de trésorerie.</p>
                            </div>
                        </div>

                    </div>
                    @include('components.footer')
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Create -->
    <div class="modal fade" id="modalCreateCategory" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden">
                <form action="{{ route('admin.config.store_treasury_category') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-slate-900 p-6">
                        <h5 class="modal-title text-white font-black">Nouvelle Catégorie</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-8">
                        <div class="mb-4">
                            <label class="form-label font-black text-slate-700 uppercase text-xs">Nom de la Catégorie</label>
                            <input type="text" name="name" class="form-control border-slate-200 py-3 rounded-xl shadow-none focus:border-primary" placeholder="Ex: Banques" required>
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
    <div class="modal fade" id="modalEditCategory" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden">
                <form id="editCategoryForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-slate-900 p-6">
                        <h5 class="modal-title text-white font-black">Modifier la Catégorie</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-8">
                        <div class="mb-4">
                            <label class="form-label font-black text-slate-700 uppercase text-xs">Nom de la Catégorie</label>
                            <input type="text" name="name" id="edit_name" class="form-control border-slate-200 py-3 rounded-xl shadow-none focus:border-primary" required>
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
        document.getElementById('searchCategory')?.addEventListener('input', function() {
            const searchValue = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#categoriesTable tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchValue) ? '' : 'none';
            });
        });

        // Edition
        function editCategory(id, name) {
            const form = document.getElementById('editCategoryForm');
            form.action = `/admin/config/update-treasury-category/${id}`;
            
            document.getElementById('edit_name').value = name;
            
            new bootstrap.Modal(document.getElementById('modalEditCategory')).show();
        }
    </script>
</body>
</html>
