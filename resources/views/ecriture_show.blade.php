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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Détails de l'écriture n° {{ $primaryEcriture->n_saisie }}</h4>
                    <span class="badge bg-label-info">Journal : {{ $primaryEcriture->codeJournal ? $primaryEcriture->codeJournal->code_journal : '-' }}</span>
                </div>
                <div class="card-body">
                    @php
                        $totalDebit = $ecritures->sum('debit');
                        $totalCredit = $ecritures->sum('credit');
                        $balance = $totalDebit - $totalCredit;
                        $isBalanced = abs($balance) < 0.01;
                    @endphp

                    <div class="row g-4 mb-5">
                        <div class="col-sm-6 col-md-4">
                            <div class="d-flex align-items-center bg-light-danger border rounded p-3">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-danger"><i class="bx bx-arrow-to-right"></i></span>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-danger">Total Débit</h6>
                                    <h4 class="mb-0 fw-bold">{{ number_format($totalDebit, 2, ',', ' ') }} <small>FCFA</small></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-4">
                            <div class="d-flex align-items-center bg-light-success border rounded p-3">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-success"><i class="bx bx-arrow-from-right"></i></span>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-success">Total Crédit</h6>
                                    <h4 class="mb-0 fw-bold">{{ number_format($totalCredit, 2, ',', ' ') }} <small>FCFA</small></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-4">
                            <div class="d-flex align-items-center bg-light-{{ $isBalanced ? 'primary' : 'warning' }} border rounded p-3">
                                <div class="avatar flex-shrink-0 me-3">
                                    <span class="avatar-initial rounded bg-label-{{ $isBalanced ? 'primary' : 'warning' }}"><i class="bx {{ $isBalanced ? 'bx-check-double' : 'bx-git-commit' }}"></i></span>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-{{ $isBalanced ? 'primary' : 'warning' }}">Solde / Équilibre</h6>
                                    <h4 class="mb-0 fw-bold">{{ number_format($balance, 2, ',', ' ') }} <small>FCFA</small></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <p class="mb-1"><small class="text-muted">DATE DE L'OPÉRATION</small></p>
                            <h5 class="fw-bold">{{ $primaryEcriture->date }}</h5>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><small class="text-muted">N° DE SAISIE</small></p>
                            <h5 class="fw-bold">{{ $primaryEcriture->n_saisie }}</h5>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <p class="mb-1"><small class="text-muted">PIÈCE RÉF.</small></p>
                            <h5 class="fw-bold">{{ $primaryEcriture->reference_piece ?? '-' }}</h5>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Description</th>
                                    <th>Compte Général</th>
                                    <th>Compte Tiers</th>
                                    <th class="text-end">Débit</th>
                                    <th class="text-end">Crédit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalDebit = 0;
                                    $totalCredit = 0;
                                @endphp
                                @foreach($ecritures as $ec)
                                    @php
                                        $totalDebit += (float)$ec->debit;
                                        $totalCredit += (float)$ec->credit;
                                    @endphp
                                    <tr>
                                        <td>
                                            @php
                                                $numeroCompte = $ec->planComptable ? $ec->planComptable->numero_de_compte : '';
                                                $intituleCompte = $ec->planComptable ? strtoupper($ec->planComptable->intitule) : '';
                                                $descUpper = strtoupper($ec->description_operation);
                                                
                                                $isVatLine = (str_starts_with($numeroCompte, '443') || str_starts_with($numeroCompte, '445') || 
                                                             (str_starts_with($numeroCompte, '44') && str_contains($intituleCompte, 'TVA')));
                                                
                                                $hasPrefix = str_starts_with($descUpper, 'TVA');
                                            @endphp
                                            {{ ($isVatLine && !$hasPrefix) ? 'TVA / ' : '' }}{{ $ec->description_operation }}
                                        </td>
                                        <td>{{ $ec->planComptable ? $ec->planComptable->numero_de_compte . ' - ' . $ec->planComptable->intitule : '-' }}</td>
                                        <td>{{ $ec->planTiers ? $ec->planTiers->numero_de_tiers . ' - ' . $ec->planTiers->intitule : '-' }}</td>
                                        <td class="text-end">{{ number_format((float) $ec->debit, 2, ',', ' ') }}</td>
                                        <td class="text-end">{{ number_format((float) $ec->credit, 2, ',', ' ') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-dark">
                                <tr>
                                    <th colspan="3" class="text-end">TOTAL</th>
                                    <th class="text-end">{{ number_format($totalDebit, 2, ',', ' ') }}</th>
                                    <th class="text-end">{{ number_format($totalCredit, 2, ',', ' ') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    @if ($primaryEcriture->piece_justificatif)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="alert alert-info d-flex align-items-center" role="alert">
                                <i class="bx bx-file me-2 fs-4"></i>
                                <div>
                                    Une pièce justificative est jointe à cette écriture.
                                    <a href="{{ asset('justificatifs/' . $primaryEcriture->piece_justificatif) }}" target="_blank" class="fw-bold ms-2 text-decoration-underline">
                                        Voir le document
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <a href="{{ route('accounting_entry_list') }}" class="btn btn-outline-secondary">
                                <i class="bx bx-arrow-back me-1"></i> Retour à la liste
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
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>
</body>
</html>
