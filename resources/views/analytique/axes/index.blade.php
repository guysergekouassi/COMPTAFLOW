<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact">

@include('components.head')

<style>
    .bg-slate-50\/50 { background-color: rgb(248 250 252 / 0.5); }
    .text-gradient {
        background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .glass-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
    }
    .btn-action { transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1); }
    .btn-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(30, 64, 175, 0.2);
    }
    .table-row { transition: background-color 0.2s; }
    .table-row:hover { background-color: #f1f5f9; }

    /* Premium Modal Styles */
    .premium-modal-content {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255, 255, 255, 1);
        border-radius: 20px;
        box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.1);
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        max-width: 450px;
        margin: auto;
        padding: 1.5rem !important;
    }
    .input-field-premium {
        transition: all 0.2s ease;
        border: 2px solid #f1f5f9 !important;
        background-color: #f8fafc !important;
        border-radius: 12px !important;
        padding: 0.75rem 1rem !important;
        font-size: 0.85rem !important;
        font-weight: 500 !important;
        width: 100%;
    }
    .input-field-premium:focus {
        border-color: #1e40af !important;
        background-color: #ffffff !important;
        outline: none !important;
    }
    .input-label-premium {
        font-size: 0.75rem !important;
        font-weight: 700 !important;
        color: #64748b !important;
        text-transform: uppercase !important;
        margin-bottom: 0.5rem !important;
        display: block !important;
    }
    .btn-save-premium {
        padding: 0.75rem 1rem !important;
        border-radius: 12px !important;
        background-color: #1e40af !important;
        color: white !important;
        font-weight: 700 !important;
        width: 100%;
        border: none !important;
    }
    .btn-cancel-premium {
        padding: 0.75rem 1rem !important;
        border-radius: 12px !important;
        color: #94a3b8 !important;
        font-weight: 700 !important;
        background: transparent !important;
        width: 100%;
        border: none !important;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')

            <div class="layout-page">
                @include('components.header', ['page_title' => 'Axes <span class="text-gradient">Analytiques</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <!-- Notifications -->
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Stats Sections -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div class="glass-card p-6 flex items-center">
                                <div class="p-4 rounded-2xl bg-blue-100 text-blue-600 mr-4">
                                    <i class="fa-solid fa-layer-group text-2xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-500 font-medium">Total des axes</p>
                                    <h3 class="text-2xl font-bold text-slate-800">{{ $totalAxes }}</h3>
                                </div>
                            </div>
                            <div class="glass-card p-6 flex items-center">
                                <div class="p-4 rounded-2xl bg-indigo-100 text-indigo-600 mr-4">
                                    <i class="fa-solid fa-shapes text-2xl"></i>
                                </div>
                                <div>
                                    <p class="text-sm text-slate-500 font-medium">Total des sections</p>
                                    <h3 class="text-2xl font-bold text-slate-800">{{ $totalSections }}</h3>
                                </div>
                            </div>
                        </div>

                        <!-- Actions Bar -->
                        <div class="flex justify-between items-center mb-6">
                            <h4 class="text-slate-800 font-bold mb-0">Liste des axes</h4>
                            <button type="button" data-bs-toggle="modal" data-bs-target="#modalCreateAxe"
                                class="btn-action flex items-center gap-2 px-6 py-3 bg-blue-700 text-white rounded-2xl font-semibold text-sm border-0 shadow-lg shadow-blue-200">
                                <i class="fas fa-plus"></i>
                                Nouvel Axe
                            </button>
                        </div>

                        <!-- Table Card -->
                        <div class="glass-card overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50/50 border-b border-slate-100">
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Code</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Libellé</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Type</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @foreach ($axes as $axe)
                                        <tr class="table-row">
                                            <td class="px-8 py-6">
                                                <span class="font-mono font-bold text-blue-700">{{ $axe->code }}</span>
                                            </td>
                                            <td class="px-8 py-6">
                                                <span class="font-medium text-slate-800">{{ $axe->libelle }}</span>
                                            </td>
                                            <td class="px-8 py-6 text-sm text-slate-600">
                                                {{ ucfirst($axe->type) }}
                                            </td>
                                            <td class="px-8 py-6 text-right">
                                                <div class="flex justify-end gap-2">
                                                    <button onclick="editAxe({{ $axe->json_data }})" class="p-2 text-slate-400 hover:text-blue-600 transition">
                                                        <i class="fas fa-edit text-lg"></i>
                                                    </button>
                                                    <button onclick="confirmDelete({{ $axe->id }}, '{{ $axe->libelle }}')" class="p-2 text-slate-400 hover:text-red-600 transition">
                                                        <i class="fas fa-trash text-lg"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Create -->
    <div class="modal fade" id="modalCreateAxe" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('analytique.axes.store') }}" method="POST" class="w-full">
                @csrf
                <div class="modal-content premium-modal-content">
                    <div class="text-center mb-6">
                        <h1 class="text-xl font-extrabold text-slate-900">Nouvel <span class="text-blue-600">Axe</span></h1>
                        <div class="h-1 w-8 bg-blue-700 mx-auto mt-2 rounded-full"></div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="input-label-premium">Code</label>
                            <input type="text" name="code" class="input-field-premium" placeholder="Ex: PROJ" required>
                        </div>
                        <div>
                            <label class="input-label-premium">Libellé</label>
                            <input type="text" name="libelle" class="input-field-premium" placeholder="Ex: Par Projet" required>
                        </div>
                        <div>
                            <label class="input-label-premium">Type</label>
                            <select name="type" class="input-field-premium">
                                <option value="projet">Projet</option>
                                <option value="departement">Département</option>
                                <option value="agence">Agence</option>
                                <option value="divers">Divers</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 pt-8">
                        <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn-save-premium">Enregistrer</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEditAxe" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="editForm" method="POST" class="w-full">
                @csrf
                @method('PUT')
                <div class="modal-content premium-modal-content">
                    <div class="text-center mb-6">
                        <h1 class="text-xl font-extrabold text-slate-900">Modifier l'<span class="text-blue-600">Axe</span></h1>
                        <div class="h-1 w-8 bg-blue-700 mx-auto mt-2 rounded-full"></div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="input-label-premium">Code</label>
                            <input type="text" id="edit_code" name="code" class="input-field-premium" required>
                        </div>
                        <div>
                            <label class="input-label-premium">Libellé</label>
                            <input type="text" id="edit_libelle" name="libelle" class="input-field-premium" required>
                        </div>
                        <div>
                            <label class="input-label-premium">Type</label>
                            <select id="edit_type" name="type" class="input-field-premium">
                                <option value="projet">Projet</option>
                                <option value="departement">Département</option>
                                <option value="agence">Agence</option>
                                <option value="divers">Divers</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 pt-8">
                        <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn-save-premium">Mettre à jour</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Delete -->
    <div class="modal fade" id="modalDeleteAxe" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content premium-modal-content">
                <div class="text-center mb-6">
                    <div class="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-trash-alt text-red-600 text-xl"></i>
                    </div>
                    <h1 class="text-xl font-extrabold text-slate-900">Supprimer l'<span class="text-red-600">Axe</span></h1>
                </div>
                <p class="text-center text-slate-500 font-medium mb-8">
                    Êtes-vous sûr de vouloir supprimer l'axe <span id="deleteAxeName" class="text-slate-900 font-bold"></span> ?
                </p>
                <form id="deleteForm" method="POST" class="grid grid-cols-2 gap-4">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn-cancel-premium" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn-save-premium !bg-red-600">Supprimer</button>
                </form>
            </div>
        </div>
    </div>

    @include('components.footer')

    <script>
        window.editAxe = function(axe) {
            $('#edit_code').val(axe.code);
            $('#edit_libelle').val(axe.libelle);
            $('#edit_type').val(axe.type);
            $('#editForm').attr('action', `/analytique/axes/${axe.id}`);
            new bootstrap.Modal(document.getElementById('modalEditAxe')).show();
        };

        window.confirmDelete = function(id, name) {
            $('#deleteAxeName').text(name);
            $('#deleteForm').attr('action', `/analytique/axes/${id}`);
            new bootstrap.Modal(document.getElementById('modalDeleteAxe')).show();
        };
    </script>
</body>
</html>
