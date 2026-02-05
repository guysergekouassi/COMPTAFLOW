<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">
@include('components.head')

<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('components.sidebar')

        <div class="layout-page">
            @include('components.header', ['page_title' => 'Nouvelle Immobilisation'])

            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    
                    <div class="row justify-content-center">
                        <div class="col-lg-10">
                            
                            @if(isset($ecriture))
                            <div class="alert alert-primary d-flex align-items-center mb-4 shadow-sm" role="alert">
                                <i class="bx bx-link-alt fs-4 me-3"></i>
                                <div>
                                    <strong>Création automatique</strong>
                                    <div class="small">Données importées de l'écriture : {{ $ecriture->libelle ?? $ecriture->description_operation }} ({{ \Carbon\Carbon::parse($ecriture->date)->format('d/m/Y') }})</div>
                                </div>
                            </div>
                            @endif

                            <div class="card shadow-sm border-0">
                                <div class="card-header border-bottom bg-white py-3">
                                    <h5 class="mb-0 fw-bold text-primary">Formulaire d'Immobilisation</h5>
                                </div>
                                <div class="card-body p-4">
                                    <form action="{{ route('immobilisations.store') }}" method="POST">
                                        @csrf
                                        
                                        @if(isset($ecriture))
                                            <input type="hidden" name="ecriture_id" value="{{ $ecriture->id }}">
                                        @endif

                                        <!-- Section 1 -->
                                        <h6 class="text-muted text-uppercase small fw-bold mb-3 mt-1"><i class="bx bx-id-card me-1"></i> Identification</h6>
                                        <div class="row g-3 mb-4">
                                            <div class="col-md-6">
                                                <label class="form-label">Libellé du bien <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="libelle" required 
                                                    value="{{ old('libelle', isset($ecriture) ? ($ecriture->libelle ?? $ecriture->description_operation) : '') }}" 
                                                    {{ isset($ecriture) ? 'readonly' : '' }} style="{{ isset($ecriture) ? 'background-color: #f8f9fa;' : '' }}">
                                                @if(isset($ecriture)) <div class="form-text text-primary"><i class="bx bx-check"></i> Pré-rempli depuis l'écriture</div> @endif
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Code Inventaire</label>
                                                <input type="text" class="form-control" name="code" placeholder="Auto ou manuel" value="{{ old('code') }}">
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label">Catégorie <span class="text-danger">*</span></label>
                                                <select class="form-select" name="categorie" required>
                                                    <option value="corporelle">Corporelle</option>
                                                    <option value="incorporelle">Incorporelle</option>
                                                    <option value="financiere">Financière</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Section 2 -->
                                        <h6 class="text-muted text-uppercase small fw-bold mb-3 border-top pt-3"><i class="bx bx-building-house me-1"></i> Comptes Comptables</h6>
                                        <div class="row g-3 mb-4">
                                            <div class="col-md-4">
                                                <label class="form-label">Compte Immo (Cl. 2) <span class="text-danger">*</span></label>
                                                <select class="form-select" name="compte_immobilisation_id" required>
                                                    <option value="">Sélectionner...</option>
                                                    @foreach($comptesImmobilisation as $compte)
                                                        <option value="{{ $compte->id }}" 
                                                            {{ (old('compte_immobilisation_id') == $compte->id || (isset($ecriture) && $ecriture->plan_comptable_id == $compte->id)) ? 'selected' : '' }}>
                                                            {{ $compte->numero_de_compte }} - {{ $compte->intitule }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Compte Amort. (Cl. 28) <span class="text-danger">*</span></label>
                                                <select class="form-select" name="compte_amortissement_id" required>
                                                    <option value="">Sélectionner...</option>
                                                    @foreach($comptesAmortissement as $compte)
                                                        <option value="{{ $compte->id }}" {{ old('compte_amortissement_id') == $compte->id ? 'selected' : '' }}>
                                                            {{ $compte->numero_de_compte }} - {{ $compte->intitule }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Compte Dotation (Cl. 68) <span class="text-danger">*</span></label>
                                                <select class="form-select" name="compte_dotation_id" required>
                                                    <option value="">Sélectionner...</option>
                                                    @foreach($comptesDotation as $compte)
                                                        <option value="{{ $compte->id }}" {{ old('compte_dotation_id') == $compte->id ? 'selected' : '' }}>
                                                            {{ $compte->numero_de_compte }} - {{ $compte->intitule }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Section 3 -->
                                        <h6 class="text-muted text-uppercase small fw-bold mb-3 border-top pt-3"><i class="bx bx-money me-1"></i> Valeurs & Dates</h6>
                                        <div class="row g-3 mb-4">
                                            <div class="col-md-4">
                                                <label class="form-label">Date Acquisition <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" name="date_acquisition" required 
                                                    value="{{ old('date_acquisition', isset($ecriture) ? \Carbon\Carbon::parse($ecriture->date)->format('Y-m-d') : date('Y-m-d')) }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Valeur d'Acquisition <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="number" step="0.01" class="form-control fw-bold" name="valeur_acquisition" required 
                                                        value="{{ old('valeur_acquisition', isset($ecriture) ? $ecriture->debit : '') }}"
                                                        {{ isset($ecriture) ? 'readonly' : '' }} style="{{ isset($ecriture) ? 'background-color: #f8f9fa;' : '' }}">
                                                    <span class="input-group-text">FCFA</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Date Mise en Service <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" name="date_mise_en_service" required 
                                                    value="{{ old('date_mise_en_service', isset($ecriture) ? \Carbon\Carbon::parse($ecriture->date)->format('Y-m-d') : date('Y-m-d')) }}">
                                            </div>
                                        </div>

                                        <!-- Section 4 -->
                                        <h6 class="text-muted text-uppercase small fw-bold mb-3 border-top pt-3"><i class="bx bx-bar-chart-alt-2 me-1"></i> Amortissement</h6>
                                        <div class="row g-3 mb-4 bg-light p-3 rounded mx-0">
                                            <div class="col-md-4">
                                                <label class="form-label">Durée (Années)</label>
                                                <input type="number" class="form-control" id="duree" name="duree_amortissement" min="1" value="5" required oninput="calcTaux()">
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label">Méthode</label>
                                                <select class="form-select" id="methode" name="methode_amortissement" required onchange="calcTaux()">
                                                    <option value="lineaire">Linéaire</option>
                                                    <option value="degressif">Dégressif</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 d-flex align-items-center">
                                                <div class="d-flex flex-column">
                                                    <span class="text-muted small">Taux calculé :</span>
                                                    <span class="fs-4 fw-bold text-primary" id="taux_visuel">20%</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-end border-top pt-4">
                                            <a href="{{ route('immobilisations.index') }}" class="btn btn-outline-secondary me-2">Annuler</a>
                                            <button type="submit" class="btn btn-primary px-4"><i class="bx bx-save me-1"></i> Enregistrer l'Immobilisation</button>
                                        </div>

                                    </form>
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

<script>
    function calcTaux() {
        let d = parseFloat(document.getElementById('duree').value) || 0;
        let m = document.getElementById('methode').value;
        let t = 0;
        if(d > 0) {
            t = 100 / d;
            if(m === 'degressif') {
                let coef = (d <= 4) ? 1.5 : (d <= 6 ? 2.0 : 2.5);
                t = t * coef;
            }
        }
        document.getElementById('taux_visuel').innerText = t.toFixed(2) + '%';
    }
    document.addEventListener('DOMContentLoaded', calcTaux);
</script>
</body>
</html>
