<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">

@include('components.head')

<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">

        @include('components.sidebar')

        <div class="layout-page">

            @include('components.header')

            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">

                    <!-- Page Header -->
                    <div class="text-center mb-5">
                        <div class="d-inline-flex align-items-center justify-content-center mb-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 70px; height: 70px; border-radius: 20px; box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);">
                            <i class="bx bx-bank text-white" style="font-size: 32px;"></i>
                        </div>
                        <h1 class="mb-2" style="font-size: 2.5rem; font-weight: 700; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Postes de Trésorerie</h1>
                        <p class="text-muted mb-0" style="font-size: 1.1rem;"><i class="bx bx-info-circle me-1"></i>Gérez vos postes de trésorerie par catégorie</p>
                    </div>

                    {{-- FLASH SUCCESS --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- ********** LISTE DES COMPTES DE TRÉSORERIE ********** --}}
                    <div class="card mb-4" style="border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); border: none;">
                        <div class="card-header d-flex justify-content-between" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border-bottom: 2px solid #e7e9ed; padding: 1.5rem;">
                            <h5 class="mb-0" style="font-weight: 700; color: #566a7f; font-size: 1.25rem;"><i class="bx bx-list-ul me-2"></i>Liste des Postes</h5>

                            <div>
                                {{-- Bouton pour créer un NOUVEAU POSTE DE TRÉSORERIE (OUVRE LA MODAL) --}}
                                <button class="btn btn-sm" data-bs-toggle="modal" data-bs-target="#modalCreatePoste" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; font-weight: 600; box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);">
                                     <i class="bx bx-plus me-1"></i> Créer un Poste
                                </button>

                            </div>
                        </div>

                        <div class="card-body">

                            <div class="table-responsive" style="padding: 1.5rem;">
                                <table class="table table-hover align-middle" style="border-radius: 8px; overflow: hidden;">
                                    <thead style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
                                        <tr>
                                            <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem;"><i class="bx bx-text me-1"></i>Nom du poste</th>

                                            <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem;"><i class="bx bx-category me-1"></i>Catégorie</th>

                                            <th style="font-weight: 700; color: #566a7f; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; padding: 1rem; text-align: center;"><i class="bx bx-slider me-1"></i>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($comptes as $item)
                                            <tr>
                                                <td>{{ $item->name }}</td> {{-- Nom du Poste (CompteTresorerie) --}}

                                                <td>{{ $item->type ?? '—' }}</td>
                                                <td>
                                                    <button
                                                        class="btn btn-icon btn-sm btn-info btn-update-poste"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#modalUpdatePoste"
                                                        data-id="{{ $item->id }}"
                                                        data-name="{{ $item->name }}"
                                                        data-type="{{ $item->type }}"
                                                    >
                                                        <i class="bx bx-edit"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">Aucun poste de trésorerie n'a encore été créé.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>

                                </table>
                            </div>

                        </div>
                    </div>


                    {{-- ********** LISTE DES MOUVEMENTS (SI SHOW) ********** --}}
                    @isset($compte)
                        <div class="card mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    Mouvements du compte : {{ $compte->nom }}
                                    <span class="badge bg-secondary">Solde : {{ number_format($compte->solde_actuel,2,',',' ') }} F CFA</span>
                                </h5>
                            </div>

                            <div class="card-body">

                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Libellé</th>
                                            <th>Référence</th>
                                            <th>Débit (Décaissement)</th>
                                            <th>Crédit (Encaissement)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($mouvements as $m)
                                        <tr>
                                            <td>{{ $m->date_mouvement }}</td>
                                            <td>{{ $m->libelle }}</td>
                                            <td>{{ $m->reference_piece ?? '—' }}</td>
                                            <td class="text-danger">{{ $m->montant_debit ? number_format($m->montant_debit,2,',',' ') : '' }} F CFA</td>
                                            <td class="text-success">{{ $m->montant_credit ? number_format($m->montant_credit,2,',',' ') : '' }} F CFA</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>

                                <div class="mt-2">
                                    {{ $mouvements->links() }}
                                </div>

                            </div>
                        </div>
                    @endisset

                </div>
            </div>

            @include('components.footer')

        </div>
    </div>

    <div class="layout-overlay layout-menu-toggle"></div>
</div>


{{-- ********** MODAL AJOUT NOUVEAU POSTE (Déplacé de createPoste.blade.php) ********** --}}
{{-- Utilise $comptesComptablesClasse5 qui est maintenant passé par index() --}}
<div class="modal fade" id="modalCreatePoste" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
            <form action="{{ route('postetresorerie.store_poste') }}" method="POST">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Créer un nouveau Poste de Trésorerie</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                                        <div class="col-md-12 mb-3">
                        <label class="form-label" for="name">Nom du Poste de Trésorerie (Ex: Achats, Acquisition)</label>
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('nom') }}" required>
                        @error('name') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label" for="type">Catégories de Trésorerie</label>
                        <select name="type" id="type" class="form-select" required>
                            <option value="">Sélectionnez une catégorie</option>
                            <option value="Flux Des Activités Operationnelles" {{ old('type') == 'Flux Des Activités Operationnelles' ? 'selected' : '' }}>Flux Des Activités Opérationnelles</option>
                            <option value="Flux Des Activités Investissement" {{ old('type') == 'Flux Des Activités Investissement' ? 'selected' : '' }}>Flux Des Activités d'Investissement</option>
                            <option value="Flux Des Activités de Financement" {{ old('type') == 'Flux Des Activités De Financement' ? 'selected' : '' }}>Flux Des Activités De Financement</option>

                        </select>
                        @error('type') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Créer le Poste de Trésorerie</button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- ********** MODAL MODIFICATION POSTE DE TRÉSORERIE ********** --}}
<div class="modal fade" id="modalUpdatePoste" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <form id="updatePosteForm" method="POST">
                {{-- CSRF Token et méthode PATCH/PUT pour Laravel --}}
                @csrf
                @method('PUT')

                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-bottom: none;">
                    <h5 class="modal-title text-white" style="font-weight: 700;"><i class="bx bx-edit-alt me-2"></i>Modifier le Poste de Trésorerie: <span id="posteNameTitle"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="col-md-12 mb-3">
                        <label class="form-label" for="update_name">Nom du Poste de Trésorerie</label>
                        {{-- Notez l'ID unique 'update_name' pour le ciblage JS --}}
                        <input type="text" name="name" id="update_name" class="form-control" required>
                        @error('name') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label" for="update_type">Catégories de Trésorerie</label>
                        {{-- Notez l'ID unique 'update_type' pour le ciblage JS --}}
                        <select name="type" id="update_type" class="form-select" required>
                            <option value="">Sélectionnez une catégorie</option>
                            <option value="Flux Des Activités Operationnelles">Flux Des Activités Opérationnelles</option>
                            <option value="Flux Des Activités Investissement">Flux Des Activités d'Investissement</option>
                            <option value="Flux Des Activités de Financement">Flux Des Activités De Financement</option>
                        </select>
                        @error('type') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                </div>

                <div class="modal-footer" style="border-top: 1px solid #e7e9ed; padding: 1.25rem;">
                    <button class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px;">Annuler</button>
                    <button type="submit" class="btn" style="border-radius: 8px; font-weight: 600; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);">Sauvegarder les modifications</button>
                </div>

            </form>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const updateModal = document.getElementById('modalUpdatePoste');

        // Écoute l'événement d'ouverture du modal Bootstrap (show.bs.modal)
        updateModal.addEventListener('show.bs.modal', function (event) {

            // Le bouton qui a déclenché le modal
            const button = event.relatedTarget;

            // Récupération des données via les attributs data- (ce sont des chaînes de caractères)
            const posteId = button.getAttribute('data-id');
            const posteName = button.getAttribute('data-name');
            const posteType = button.getAttribute('data-type');

            // 1. Mise à jour de l'URL du formulaire pour pointer vers la route PUT/PATCH
            const form = document.getElementById('updatePosteForm');
            // L'URL doit correspondre à votre route Laravel : /poste/{id}
            form.action = `/poste/${posteId}`;

            // 2. Remplissage des champs du formulaire
            document.getElementById('posteNameTitle').textContent = posteName;
            document.getElementById('update_name').value = posteName;
            document.getElementById('update_type').value = posteType; // Définit l'option sélectionnée
        });
    });
</script>
</body>
</html>
