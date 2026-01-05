<!doctype html>

<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/"
  data-template="vertical-menu-template-free" data-bs-theme="light">

@include('components.head')

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
          @include('components.header', ['page_title' => 'DÉTAILS <span class="text-gradient">ÉCRITURE</span>'])
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
                    <h4 class="card-title">Détails de l'écriture #{{ $ecriture->id }}</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Date :</strong></td>
                                    <td>{{ $ecriture->date }}</td>
                                </tr>
                                <tr>
                                    <td><strong>N° Saisie :</strong></td>
                                    <td>{{ $ecriture->n_saisie }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Code Journal :</strong></td>
                                    <td>{{ $ecriture->codeJournal ? $ecriture->codeJournal->code_journal : '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Référence Pièce :</strong></td>
                                    <td>{{ $ecriture->reference_piece }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Description :</strong></td>
                                    <td>{{ $ecriture->description_operation }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Compte Général :</strong></td>
                                    <td>{{ $ecriture->planComptable ? $ecriture->planComptable->numero_de_compte . ' - ' . $ecriture->planComptable->intitule : '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Compte Tiers :</strong></td>
                                    <td>{{ $ecriture->planTiers ? $ecriture->planTiers->numero_de_tiers . ' - ' . $ecriture->planTiers->intitule : '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Analytique :</strong></td>
                                    <td>{{ (int) $ecriture->plan_analytique === 1 ? 'Oui' : 'Non' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Débit</th>
                                        <th>Crédit</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-right">{{ number_format((float) $ecriture->debit, 2, ',', ' ') }}</td>
                                        <td class="text-right">{{ number_format((float) $ecriture->credit, 2, ',', ' ') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    @if ($ecriture->piece_justificatif)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>Pièce Justificative</h5>
                            <a href="{{ asset('justificatifs/' . $ecriture->piece_justificatif) }}" target="_blank" class="btn btn-primary">
                                <i class="fas fa-download"></i> Télécharger la pièce
                            </a>
                        </div>
                    </div>
                    @endif
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <a href="{{ route('accounting_entry_list') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Retour à la liste
                            </a>
                        </div>
                    </div>
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
