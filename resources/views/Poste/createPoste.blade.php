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
                        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">Trésorerie /</span> Créer un nouveau Poste</h4>

                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Lier un compte comptable  à un Poste de Trésorerie</h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('postetresorerie.store_poste') }}" method="POST">
                                    @csrf

                                    {{-- <div class="mb-3">
                                        <label class="form-label" for="plan_comptable_id">Compte Comptable (Classe 5)</label>
                                        <select name="plan_comptable_id" id="plan_comptable_id" class="form-select" required>
                                            <option value="">Sélectionnez un compte 5...</option>
                                            @forelse($comptesComptablesClasse5 as $comptePC)
                                                <option value="{{ $comptePC->id }}">
                                                    {{ $comptePC->numero_de_compte }} — {{ $comptePC->intitule }}
                                                </option>
                                            @empty
                                                <option disabled>Aucun compte de classe 5 disponible pour la compagnie active.</option>
                                            @endforelse
                                        </select>
                                        @error('plan_comptable_id') <div class="text-danger">{{ $message }}</div> @enderror
                                    </div> --}}

                                    <div class="mb-3">
                                        <label class="form-label" for="nom">Nom du Poste de Trésorerie (Ex: BNP, Caisse siège)</label>
                                        <input type="text" name="nom" id="nom" class="form-control" value="{{ old('nom') }}" required>
                                        @error('nom') <div class="text-danger">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="type">Categories</label>
                                        <select name="type" id="type" class="form-select" required>
                                            <option value="">Sélectionnez une categorie</option>
                                            <option value="banque" {{ old('type') == 'banque' ? 'selected' : '' }}>Banque</option>
                                            <option value="caisse" {{ old('type') == 'caisse' ? 'selected' : '' }}>Caisse</option>
                                        </select>
                                        @error('type') <div class="text-danger">{{ $message }}</div> @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="solde_initial">Solde Initial (au début de l'exercice)</label>
                                        <input type="number" name="solde_initial" id="solde_initial" step="0.01" min="0" class="form-control" value="{{ old('solde_initial', 0) }}" required>
                                        @error('solde_initial') <div class="text-danger">{{ $message }}</div> @enderror
                                    </div>

                                    <button type="submit" class="btn btn-success">Créer le Poste de Trésorerie</button>
                                    <a href="{{ route('postetresorerie.index') }}" class="btn btn-secondary">Annuler</a>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
                @include('components.footer')
            </div>
        </div>
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
</body>
</html>
