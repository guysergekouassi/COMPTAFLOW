@include('components.head')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap');

    body {
        background-color: #f1f5f9;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .master-header {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
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
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 24px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
    }

    .btn-premium {
        background: #4338ca;
        color: white;
        border: none;
        padding: 0.8rem 2rem;
        border-radius: 12px;
        font-weight: 700;
        transition: all 0.3s ease;
    }
    .btn-premium:hover {
        background: #3730a3;
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(67, 56, 202, 0.3);
    }

    .badge-tiers-indigo {
        background: #e0e7ff;
        color: #3730a3;
        border: 1px solid #c7d2fe;
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
                @include('components.header', ['page_title' => 'Gestion <span class="text-indigo-600">Master</span> des Tiers'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <div class="master-header shadow-2xl">
                            <div class="row align-items-center">
                                <div class="col-lg-8">
                                    <span class="badge-tiers-indigo mb-4 d-inline-block">Partenaires du Groupe</span>
                                    <h1 class="font-black mb-2 tracking-tighter">Modèle de Plan Tiers</h1>
                                    <p class="opacity-70 font-medium">Définissez vos clients, fournisseurs et partenaires stratégiques au niveau groupe. Ils seront instantanément disponibles pour toutes vos comptabilités filiales.</p>
                                </div>
                                <div class="col-lg-4 text-end d-flex flex-column gap-2 border-start border-white/10 ps-6">
                                    <button class="btn btn-premium w-100" data-bs-toggle="modal" data-bs-target="#modalCenterCreate">
                                        <i class="fa-solid fa-user-plus me-2"></i> Nouveau Complice
                                    </button>
                                    <button class="btn btn-outline-light w-100 border-2 font-black rounded-xl" data-bs-toggle="modal" data-bs-target="#modalImportTiers">
                                        <i class="fa-solid fa-file-import me-2"></i> Importer Excel/CSV
                                    </button>
                                </div>
                            </div>
                            <div class="position-absolute end-0 top-0 opacity-10" style="transform: translate(20%, -20%) rotate(-15deg);">
                                <i class="fa-solid fa-address-book fa-10x"></i>
                            </div>
                        </div>

                        <div class="glass-table-card overflow-hidden">
                            <div class="p-8 border-b border-slate-100 d-flex justify-content-between align-items-center bg-white">
                                <div>
                                    <h4 class="font-black mb-0">Répertoire des Tiers Master</h4>
                                    <p class="text-slate-400 text-sm mb-0">Modèles pré-configurés avec comptes de rattachement.</p>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-indigo-50/30">
                                        <tr>
                                            <th class="ps-8 py-5 text-uppercase text-xs font-black text-indigo-400">Identifiant</th>
                                            <th class="py-5 text-uppercase text-xs font-black text-indigo-400">Nom / Raison Sociale</th>
                                            <th class="py-5 text-uppercase text-xs font-black text-indigo-400">Catégorie</th>
                                            <th class="pe-8 py-5 text-uppercase text-xs font-black text-indigo-400 text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white">
                                        @foreach($planTiers as $tier)
                                        <tr>
                                            <td class="ps-8 py-6">
                                                <span class="font-black text-indigo-700 fs-6 bg-indigo-50 px-3 py-1 rounded-lg">{{ $tier->numero_de_tiers }}</span>
                                            </td>
                                            <td class="py-6">
                                                <span class="font-bold text-slate-800">{{ $tier->intitule }}</span>
                                                <div class="text-xs text-slate-400">Compte: {{ $tier->compte->numero_de_compte ?? 'Non lié' }}</div>
                                            </td>
                                            <td class="py-6">
                                                <span class="badge border border-indigo-200 text-indigo-600 bg-indigo-50 rounded-pill font-black px-4">{{ strtoupper($tier->type_de_tiers) }}</span>
                                            </td>
                                            <td class="pe-8 py-6 text-end">
                                                <div class="btn-group">
                                                    <button class="btn btn-icon btn-sm btn-outline-indigo border-0 rounded-circle"><i class="fa-solid fa-user-gear"></i></button>
                                                    <button class="btn btn-icon btn-sm btn-outline-danger border-0 rounded-circle"><i class="fa-solid fa-user-minus"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                    @include('components.footer')
                </div>
            </div>
        </div>
    </div>
    </div>

    <!-- Modal Import Tiers -->
    <div class="modal fade" id="modalImportTiers" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden">
                <form action="{{ route('admin.config.import_tiers') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header bg-slate-900 p-6">
                        <h5 class="modal-title text-white font-black">Importer des Tiers</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-8">
                        <div class="bg-indigo-50 p-6 rounded-2xl mb-6 border border-indigo-100">
                            <h6 class="font-black text-indigo-800 mb-2"><i class="fa-solid fa-circle-info me-2"></i> Format Requis</h6>
                            <p class="text-sm text-indigo-600 mb-0">Colonnes obligatoires : <strong>numero_de_tiers</strong>, <strong>intitule</strong>, <strong>type_de_tiers</strong>.</p>
                        </div>
                        <div class="col-12">
                            <label class="form-label font-black text-slate-700">Sélectionner le fichier (Excel/CSV)</label>
                            <input type="file" name="file" class="form-control border-slate-200 py-3 rounded-xl" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-slate-50 p-6 border-0">
                        <button type="button" class="btn btn-outline-secondary font-bold px-6 py-3 rounded-xl" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-indigo font-black px-8 py-3 rounded-xl shadow-lg shadow-indigo/20 text-white" style="background: #4338ca;">Lancer l'importation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

