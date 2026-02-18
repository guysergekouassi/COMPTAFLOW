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
                @include('components.header', ['page_title' => 'Sections <span class="text-gradient">Analytiques</span>'])

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

                        <!-- Filter Bar -->
                        <div class="glass-card p-6 mb-8">
                            <form action="{{ route('analytique.sections.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                                <div>
                                    <label class="input-label-premium">Filtrer par Axe</label>
                                    <select name="axe_id" class="input-field-premium" onchange="this.form.submit()">
                                        <option value="">Tous les axes</option>
                                        @foreach ($axes as $axe)
                                            <option value="{{ $axe->id }}" {{ request('axe_id') == $axe->id ? 'selected' : '' }}>
                                                {{ $axe->libelle }} ({{ $axe->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <button type="submit" class="btn-action px-6 py-3 bg-white border border-slate-200 rounded-2xl text-slate-700 font-semibold text-sm w-full">
                                        <i class="fas fa-filter text-blue-600 mr-2"></i>Filtrer
                                    </button>
                                </div>
                                <div class="text-right">
                                    <button type="button" data-bs-toggle="modal" data-bs-target="#modalCreateSection"
                                        class="btn-action flex items-center justify-center gap-2 px-6 py-3 bg-blue-700 text-white rounded-2xl font-semibold text-sm border-0 shadow-lg shadow-blue-200 w-full">
                                        <i class="fas fa-plus"></i>
                                        Nouvelle Section
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Table Card -->
                        <div class="glass-card overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                                <h3 class="text-lg font-bold text-slate-800 mb-0">Liste des sections</h3>
                                <span class="badge bg-blue-50 text-blue-700 border-blue-100 px-3 py-1 font-bold">{{ $totalSections }} sections</span>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-slate-50/50 border-b border-slate-100">
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Axe</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Code Section</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider">Libellé</th>
                                            <th class="px-8 py-5 text-sm font-bold text-slate-500 uppercase tracking-wider text-right">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-50">
                                        @foreach ($sections as $section)
                                        <tr class="table-row">
                                            <td class="px-8 py-6">
                                                <span class="badge bg-slate-100 text-slate-700">{{ $section->axe->libelle }}</span>
                                            </td>
                                            <td class="px-8 py-6">
                                                <span class="font-mono font-bold text-blue-700">{{ $section->code }}</span>
                                            </td>
                                            <td class="px-8 py-6">
                                                <span class="font-medium text-slate-800">{{ $section->libelle }}</span>
                                            </td>
                                            <td class="px-8 py-6 text-right">
                                                <div class="flex justify-end gap-2">
                                                    <button onclick="editSection({{ $section->json_data }})" class="p-2 text-slate-400 hover:text-blue-600 transition">
                                                        <i class="fas fa-edit text-lg"></i>
                                                    </button>
                                                    <button onclick="confirmDelete({{ $section->id }}, '{{ $section->libelle }}')" class="p-2 text-slate-400 hover:text-red-600 transition">
                                                        <i class="fas fa-trash text-lg"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                        @if($sections->isEmpty())
                                        <tr>
                                            <td colspan="4" class="text-center py-12">
                                                <div class="flex flex-col items-center">
                                                    <i class="fa-solid fa-folder-open text-slate-200 text-5xl mb-4"></i>
                                                    <p class="text-slate-400 font-medium">Aucune section trouvée.</p>
                                                </div>
                                            </td>
                                        </tr>
                                        @endif
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
    <div class="modal fade" id="modalCreateSection" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('analytique.sections.store') }}" method="POST" class="w-full">
                @csrf
                <div class="modal-content premium-modal-content">
                    <div class="text-center mb-6">
                        <h1 class="text-xl font-extrabold text-slate-900">Nouvelle <span class="text-blue-600">Section</span></h1>
                        <div class="h-1 w-8 bg-blue-700 mx-auto mt-2 rounded-full"></div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="input-label-premium">Axe Analytique</label>
                            <select name="axe_id" class="input-field-premium" required>
                                <option value="">Choisir un axe...</option>
                                @foreach ($axes as $axe)
                                    <option value="{{ $axe->id }}">{{ $axe->libelle }} ({{ $axe->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="input-label-premium">Code Section</label>
                            <input type="text" name="code" class="input-field-premium" placeholder="Ex: S01" required>
                        </div>
                        <div>
                            <label class="input-label-premium">Libellé</label>
                            <input type="text" name="libelle" class="input-field-premium" placeholder="Ex: Département Nord" required>
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
    <div class="modal fade" id="modalEditSection" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="editForm" method="POST" class="w-full">
                @csrf
                @method('PUT')
                <div class="modal-content premium-modal-content">
                    <div class="text-center mb-6">
                        <h1 class="text-xl font-extrabold text-slate-900">Modifier la <span class="text-blue-600">Section</span></h1>
                        <div class="h-1 w-8 bg-blue-700 mx-auto mt-2 rounded-full"></div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="input-label-premium">Axe Analytique</label>
                            <select id="edit_axe_id" name="axe_id" class="input-field-premium" required>
                                @foreach ($axes as $axe)
                                    <option value="{{ $axe->id }}">{{ $axe->libelle }} ({{ $axe->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="input-label-premium">Code Section</label>
                            <input type="text" id="edit_code" name="code" class="input-field-premium" required>
                        </div>
                        <div>
                            <label class="input-label-premium">Libellé</label>
                            <input type="text" id="edit_libelle" name="libelle" class="input-field-premium" required>
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
    <div class="modal fade" id="modalDeleteSection" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content premium-modal-content">
                <div class="text-center mb-6">
                    <div class="w-12 h-12 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-trash-alt text-red-600 text-xl"></i>
                    </div>
                    <h1 class="text-xl font-extrabold text-slate-900">Supprimer la <span class="text-red-600">Section</span></h1>
                </div>
                <p class="text-center text-slate-500 font-medium mb-8">
                    Êtes-vous sûr de vouloir supprimer la section <span id="deleteSectionName" class="text-slate-900 font-bold"></span> ?
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
        window.editSection = function(section) {
            $('#edit_axe_id').val(section.axe_id);
            $('#edit_code').val(section.code);
            $('#edit_libelle').val(section.libelle);
            $('#editForm').attr('action', `/analytique/sections/${section.id}`);
            new bootstrap.Modal(document.getElementById('modalEditSection')).show();
        };

        window.confirmDelete = function(id, name) {
            $('#deleteSectionName').text(name);
            $('#deleteForm').attr('action', `/analytique/sections/${id}`);
            new bootstrap.Modal(document.getElementById('modalDeleteSection')).show();
        };
    </script>
</body>
</html>
