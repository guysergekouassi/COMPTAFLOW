@include('components.head')

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
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Modèle <span class="text-primary">Master</span> du Plan Comptable'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <div class="master-header shadow-2xl">
                            <div class="row align-items-center">
                                <div class="col-lg-8">
                                    <span class="badge-info-gold mb-4 d-inline-block">Architecture du Groupe</span>
                                    <h1 class="font-black mb-2 tracking-tighter">Modèle de Plan Comptable</h1>
                                    <p class="opacity-70 font-medium">Configurez ici le plan comptable de référence qui sera chargé par vos filiales. Toute modification ici sera disponible en "chargement" pour les comptables.</p>
                                </div>
                                <div class="col-lg-4 text-end d-flex flex-column gap-2 border-start border-white/10 ps-6">
                                    <button class="btn btn-premium w-100" data-bs-toggle="modal" data-bs-target="#modalCenterCreate">
                                        <i class="fa-solid fa-plus me-2"></i> Ajouter au Modèle
                                    </button>
                                    <form action="{{ route('admin.config.load_syscohada') }}" method="POST" class="w-100">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-light w-100 border-2 font-black rounded-xl">
                                            <i class="fa-solid fa-bolt-lightning me-2"></i> Charger Plan SYSCOHADA
                                        </button>
                                    </form>
                                    <button class="btn btn-outline-light w-100 border-2 font-black rounded-xl" data-bs-toggle="modal" data-bs-target="#modalImportAccounts">
                                        <i class="fa-solid fa-file-import me-2"></i> Importer Excel/CSV
                                    </button>
                                </div>
                            </div>
                            <div class="position-absolute end-0 top-0 opacity-10" style="transform: translate(20%, -20%) rotate(-15deg);">
                                <i class="fa-solid fa-book-bookmark fa-10x"></i>
                            </div>
                        </div>

                        <div class="glass-table-card overflow-hidden">
                            <div class="p-8 border-b border-slate-100 d-flex justify-content-between align-items-center bg-white">
                                <div>
                                    <h4 class="font-black mb-0">Nomenclature de Référence</h4>
                                    <p class="text-slate-400 text-sm mb-0">Basé sur les normes SYSCOHADA en vigueur.</p>
                                </div>
                                <div class="d-flex gap-2">
                                    <input type="text" id="masterSearch" class="form-control border-slate-200 rounded-xl" placeholder="Rechercher un compte...">
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="ps-8 py-5 text-uppercase text-xs font-black text-slate-400">Compte</th>
                                            <th class="py-5 text-uppercase text-xs font-black text-slate-400">Intitule</th>
                                            <th class="py-5 text-uppercase text-xs font-black text-slate-400 text-center">Utilisé par</th>
                                            <th class="pe-8 py-5 text-uppercase text-xs font-black text-slate-400 text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white">
                                        @foreach($plansComptables as $plan)
                                        <tr>
                                            <td class="ps-8 py-6">
                                                <span class="font-black text-blue-600 fs-5">{{ $plan->numero_de_compte }}</span>
                                            </td>
                                            <td class="py-6">
                                                <span class="font-bold text-slate-700">{{ $plan->intitule }}</span>
                                                <div class="text-xs text-slate-400">Synchronisation Automatique</div>
                                            </td>
                                            <td class="py-6 text-center">
                                                <span class="badge bg-slate-100 text-slate-600 rounded-pill font-bold">Standard</span>
                                            </td>
                                            <td class="pe-8 py-6 text-end">
                                                <div class="btn-group">
                                                    <button class="btn btn-icon btn-sm btn-outline-primary border-0 rounded-circle"><i class="fa-solid fa-pen-to-square"></i></button>
                                                    <button class="btn btn-icon btn-sm btn-outline-danger border-0 rounded-circle"><i class="fa-solid fa-trash-can"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="p-6 bg-slate-50 text-center">
                                <p class="text-xs text-slate-400 mb-0 font-medium">Ce modèle sert de base pour toutes les entités de votre groupe.</p>
                            </div>
                        </div>

                    </div>
                    @include('components.footer')
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Modal Create -->
    <div class="modal fade" id="modalCenterCreate" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden">
                <form action="{{ route('admin.config.store_account') }}" method="POST">
                    @csrf
                    <div class="modal-header bg-slate-900 p-6">
                        <h5 class="modal-title text-white font-black" id="modalCenterTitle">Nouveau Compte Master</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-8">
                        <div class="row g-6">
                            <div class="col-12">
                                <label class="form-label font-black text-slate-700">Numéro de compte</label>
                                <input type="text" name="numero_de_compte" class="form-control border-slate-200 py-3 rounded-xl shadow-none focus:border-primary" placeholder="Ex: 60110000" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label font-black text-slate-700">Intitulé du compte</label>
                                <input type="text" name="intitule" class="form-control border-slate-200 py-3 rounded-xl shadow-none focus:border-primary" placeholder="Ex: ACHATS DE MARCHANDISES" required>
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

    <!-- Modal Import -->
    <div class="modal fade" id="modalImportAccounts" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden">
                <form action="{{ route('admin.config.import_accounts') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header bg-slate-900 p-6">
                        <h5 class="modal-title text-white font-black">Importer des Comptes</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-8">
                        <div class="bg-blue-50 p-6 rounded-2xl mb-6">
                            <h6 class="font-black text-blue-800 mb-2"><i class="fa-solid fa-circle-info me-2"></i> Format Requis</h6>
                            <p class="text-sm text-blue-600 mb-0">Votre fichier doit contenir les colonnes : <strong>numero_de_compte</strong> et <strong>intitule</strong>.</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label font-black text-slate-700">Sélectionner le fichier (Excel/CSV)</label>
                            <input type="file" name="file" class="form-control border-slate-200 py-3 rounded-xl" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-slate-50 p-6 border-0">
                        <button type="button" class="btn btn-outline-secondary font-bold px-6 py-3 rounded-xl" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-primary font-black px-8 py-3 rounded-xl shadow-lg shadow-primary/20">Lancer l'importation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('masterSearch')?.addEventListener('keyup', function() {
            let value = this.value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(tr => {
                tr.style.display = tr.innerText.toLowerCase().includes(value) ? '' : 'none';
            });
        });
    </script>
</body>
</html>
