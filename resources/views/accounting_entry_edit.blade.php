<!doctype html>

<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/"
  data-template="vertical-menu-template-free" data-bs-theme="light">

@include('components.head')
<style>
    /* Design Premium pour l'Édition d'Écritures */
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }
    .card-header {
        background: transparent;
        border-bottom: 1px solid #f0f2f4;
        padding: 1.5rem 2rem;
    }
    .card-title {
        font-weight: 700;
        color: #32475c;
        margin: 0;
    }
    .card-body {
        padding: 2rem;
    }

    /* Labels et Contrôles */
    .form-label {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.82rem;
        letter-spacing: 0.5px;
        color: #566a7f;
        margin-bottom: 0.6rem;
    }
    .form-control {
        border: 2px solid #e9ecef;
        border-radius: 10px;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: all 0.3s ease;
    }
    .form-control:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.15);
    }

    /* Boutons */
    .btn {
        border-radius: 10px;
        font-weight: 600;
        padding: 0.75rem 1.5rem;
        transition: all 0.3s ease;
    }
    .btn-primary {
        background: linear-gradient(135deg, #696cff 0%, #5f61e6 100%);
        border: none;
    }
    .btn-secondary {
        background: #f0f2f4;
        color: #566a7f;
        border: none;
    }
</style>

<body>
  <!-- Layout wrapper -->
  <div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
      <!-- Menu -->
      @include('components.sidebar')
      <!-- / Menu -->

      <!-- Layout container -->
      <div class="layout-page">
        <!-- Navbar -->
          @include('components.header', ['page_title' => 'MODIFIER <span class="text-gradient">ÉCRITURE</span>'])
        <!-- / Navbar -->

        <!-- Content wrapper -->
        <div class="content-wrapper">
          <!-- Content -->
          <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row">
              <div class="col-12">
              </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Modifier l'écriture #{{ $ecriture->id }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('ecriture.update', $ecriture->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date" class="form-label">Date</label>
                                    <input type="date" class="form-control" id="date" name="date" value="{{ $ecriture->date }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="n_saisie" class="form-label">N° Saisie</label>
                                    <input type="text" class="form-control" id="n_saisie" name="n_saisie" value="{{ $ecriture->n_saisie }}" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code_journal_id" class="form-label">Code Journal</label>
                                    <select class="form-control" id="code_journal_id" name="code_journal_id" required>
                                        @foreach ($codeJournaux as $journal)
                                            <option value="{{ $journal->id }}" {{ $ecriture->code_journal_id == $journal->id ? 'selected' : '' }}>
                                                {{ $journal->code_journal }} - {{ $journal->intitule }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reference_piece" class="form-label">Référence Pièce</label>
                                    <input type="text" class="form-control" id="reference_piece" name="reference_piece" value="{{ $ecriture->reference_piece }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="description_operation" class="form-label">Description</label>
                                    <textarea class="form-control" id="description_operation" name="description_operation" rows="3">{{ $ecriture->description_operation }}</textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="compte_general" class="form-label">Compte Général</label>
                                    <select class="form-control" id="compte_general" name="compte_general" required>
                                        @foreach ($plansComptables as $compte)
                                            <option value="{{ $compte->id }}" {{ $ecriture->compte_general == $compte->id ? 'selected' : '' }}>
                                                {{ $compte->numero_de_compte }} - {{ $compte->intitule }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tiers" class="form-label">Compte Tiers</label>
                                    <select class="form-control" id="tiers" name="tiers">
                                        <option value="">Sélectionner un compte tiers</option>
                                        @foreach ($plansTiers as $tier)
                                            <option value="{{ $tier->id }}" {{ $ecriture->tiers == $tier->id ? 'selected' : '' }}>
                                                {{ $tier->numero_de_tiers }} - {{ $tier->intitule }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="debit" class="form-label">Débit</label>
                                    <input type="number" class="form-control" id="debit" name="debit" step="0.01" min="0" value="{{ $ecriture->debit }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="credit" class="form-label">Crédit</label>
                                    <input type="number" class="form-control" id="credit" name="credit" step="0.01" min="0" value="{{ $ecriture->credit }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="plan_analytique" class="form-label">Plan Analytique</label>
                                    <select class="form-control" id="plan_analytique" name="plan_analytique">
                                        <option value="0" {{ $ecriture->plan_analytique == 0 ? 'selected' : '' }}>Non</option>
                                        <option value="1" {{ $ecriture->plan_analytique == 1 ? 'selected' : '' }}>Oui</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 text-center">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-save"></i> Enregistrer
                                </button>
                                <a href="{{ route('accounting_entry_list') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Annuler
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
          </div>
          <!-- / Content -->

          <!-- Footer -->
          @include('components.footer')
          <!-- / Footer -->

        </div>
      </div>
      <!-- / Layout container -->

      <!-- Overlay -->
      <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
</body>
</html>
