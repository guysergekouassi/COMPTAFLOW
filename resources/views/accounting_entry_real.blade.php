<!doctype html>

<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/"
  data-template="vertical-menu-template-free" data-bs-theme="light">
  <head>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
      #loading-icon {
        display: none;
      }
      .loading #loading-icon {
        display: inline-block;
      }
      .form-control:disabled, .form-control[readonly] {
        background-color: #f8f9fa !important;
      }
            input:disabled {
            background-color: #e9ecef !important;
            cursor: not-allowed;
            opacity: 0.7;
        }
    </style>
  </head>

@include('components.head')
<style>
    /* Design Premium pour la Saisie d'Écritures - Inspiration Scan */
    .card {
        border: none;
        border-radius: 40px !important;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08) !important;
        background: #ffffff !important;
        overflow: hidden;
    }
    .card-header {
        background: linear-gradient(135deg, #2563eb 0%, #1e3a8a 100%) !important;
        border-bottom: none !important;
        padding: 2rem 2.5rem !important;
        color: #ffffff !important;
    }
    .card-title {
        font-weight: 800 !important;
        font-size: 2.2rem !important;
        margin: 0 !important;
        color: #ffffff !important;
        text-transform: uppercase !important;
        letter-spacing: 1px !important;
    }
    .card-subtitle {
        font-size: 1.1rem !important;
        opacity: 0.9 !important;
        margin-top: 8px !important;
        font-weight: 400 !important;
    }
    .card-body {
        padding: 2.5rem !important;
        background: #ffffff !important;
    }

    /* Sections Premium */
    .form-section {
        background: #f8fafc !important;
        border-radius: 25px !important;
        padding: 2rem !important;
        margin-bottom: 2rem !important;
        border: 1px solid #e2e8f0 !important;
        position: relative !important;
    }
    .form-section-header {
        display: flex !important;
        align-items: center !important;
        margin-bottom: 1.5rem !important;
        padding-bottom: 1rem !important;
        border-bottom: 2px solid #e2e8f0 !important;
    }
    .form-section-icon {
        width: 50px !important;
        height: 50px !important;
        border-radius: 15px !important;
        background: linear-gradient(135deg, #2563eb 0%, #1e3a8a 100%) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        margin-right: 1rem !important;
        color: #ffffff !important;
        font-size: 1.3rem !important;
    }
    .form-section-title {
        font-weight: 700 !important;
        font-size: 1.3rem !important;
        color: #1a202c !important;
        margin: 0 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
    }
    .form-section-subtitle {
        font-size: 0.9rem !important;
        color: #64748b !important;
        margin: 4px 0 0 0 !important;
    }

    /* Labels et Contrôles Premium */
    .form-label {
        font-weight: 700 !important;
        text-transform: uppercase !important;
        font-size: 0.82rem !important;
        letter-spacing: 0.5px !important;
        color: #475569 !important;
        margin-bottom: 0.6rem !important;
        display: flex !important;
        align-items: center !important;
        gap: 0.5rem !important;
    }
    .form-label i {
        color: #2563eb !important;
        font-size: 0.9rem !important;
    }
    .form-control, .form-select {
        padding: 0.9rem 1.2rem !important;
        font-size: 1rem !important;
        border-radius: 15px !important;
        border: 2px solid #e2e8f0 !important;
        background-color: #ffffff !important;
        transition: all 0.3s ease !important;
        font-weight: 500 !important;
    }
    .form-control:focus, .form-select:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 0.25rem rgba(37, 99, 235, 0.15) !important;
        transform: translateY(-1px) !important;
    }
    .form-control[readonly] {
        background-color: #f1f5f9 !important;
        border-color: #cbd5e1 !important;
        color: #64748b !important;
        cursor: not-allowed !important;
        font-weight: 600 !important;
    }

    /* Table Premium */
    .table-responsive {
        border-radius: 20px !important;
        overflow: hidden !important;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05) !important;
        border: 1px solid #e2e8f0 !important;
    }
    #tableEcritures {
        margin: 0 !important;
        border-radius: 20px !important;
        overflow: hidden !important;
    }
    #tableEcritures thead {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%) !important;
    }
    #tableEcritures th {
        background: transparent !important;
        color: #ffffff !important;
        text-transform: uppercase !important;
        font-size: 0.75rem !important;
        font-weight: 800 !important;
        letter-spacing: 0.05em !important;
        padding: 1.2rem 1rem !important;
        border: none !important;
    }
    #tableEcritures td {
        padding: 1rem !important;
        font-size: 0.9rem !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f5f9 !important;
        font-weight: 500 !important;
    }
    #tableEcritures tbody tr:hover {
        background-color: #f8fafc !important;
    }

    /* Totaux Premium */
    .totals-section {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
        border-radius: 20px !important;
        padding: 1.5rem !important;
        margin-top: 2rem !important;
        border: 2px solid #cbd5e1 !important;
    }
    .total-item {
        text-align: center !important;
        padding: 0 1rem !important;
    }
    .total-label {
        font-size: 0.85rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        color: #64748b !important;
        letter-spacing: 0.5px !important;
        margin-bottom: 0.5rem !important;
    }
    .total-amount {
        font-size: 1.8rem !important;
        font-weight: 800 !important;
        color: #1a202c !important;
        margin: 0 !important;
    }
    .total-amount.credit {
        color: #059669 !important;
    }
    .total-amount.debit {
        color: #dc2626 !important;
    }

    /* Boutons Premium */
    .btn-premium {
        padding: 0.8rem 1rem !important;
        border-radius: 10px !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.3px !important;
        transition: all 0.3s ease !important;
        border: none !important;
        position: relative !important;
        overflow: hidden !important;
        font-size: 0.8rem !important;
        min-height: 44px !important;
        max-width: 180px !important;
    }
    .btn-premium:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
    }
    .btn-primary-premium {
        background: linear-gradient(135deg, #2563eb 0%, #1e3a8a 100%) !important;
        color: #ffffff !important;
        box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3) !important;
    }
    .btn-primary-premium:hover {
        background: linear-gradient(135deg, #1d4ed8 0%, #1e3a8a 100%) !important;
        box-shadow: 0 8px 25px rgba(37, 99, 235, 0.4) !important;
    }
    .btn-success-premium {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
        color: #ffffff !important;
        box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3) !important;
    }
    .btn-success-premium:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%) !important;
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4) !important;
    }
    .btn-outline-premium {
        background: transparent !important;
        color: #64748b !important;
        border: 2px solid #e2e8f0 !important;
    }
    .btn-outline-premium:hover {
        background: #f8fafc !important;
        border-color: #cbd5e1 !important;
        transform: translateY(-1px) !important;
    }

    /* Animations */
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .form-section {
        animation: slideInUp 0.5s ease-out;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .card-body {
            padding: 1.5rem !important;
        }
        .form-section {
            padding: 1.5rem !important;
        }
        .form-section-header {
            flex-direction: column !important;
            text-align: center !important;
        }
        .form-section-icon {
            margin-right: 0 !important;
            margin-bottom: 1rem !important;
        }
    }
</style>

<!-- Styles additionnels pour le grand conteneur modal -->
<style>
    /* Grand Conteneur Modal */
    .modal-container {
        animation: slideInUp 0.6s ease-out;
    }
    
    .modal-header-premium {
        position: relative;
    }
    
    .modal-header-premium::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 100%);
        pointer-events: none;
    }
    
    .modal-body-premium {
        position: relative;
    }
    
    /* Sections Premium */
    .section-divider {
        position: relative;
        animation: fadeInUp 0.5s ease-out;
    }
    
    .section-divider:nth-child(2) {
        animation-delay: 0.1s;
    }
    
    .section-divider:nth-child(3) {
        animation-delay: 0.2s;
    }
    
    .section-divider:nth-child(4) {
        animation-delay: 0.3s;
    }
    
    .section-header {
        position: relative;
    }
    
    .section-header::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100px;
        height: 3px;
        background: linear-gradient(90deg, #2563eb 0%, transparent 100%);
        border-radius: 2px;
    }
    
    .section-icon {
        position: relative;
        overflow: hidden;
    }
    
    .section-icon::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.1) 50%, transparent 70%);
        transform: rotate(45deg);
        animation: shimmer 3s infinite;
    }
    
    /* Labels et Contrôles Premium */
    .form-label {
        font-weight: 700 !important;
        text-transform: uppercase !important;
        font-size: 0.75rem !important;
        letter-spacing: 0.5px !important;
        color: #475569 !important;
        margin-bottom: 0.5rem !important;
        display: flex !important;
        align-items: center !important;
        gap: 0.5rem !important;
    }
    .form-label i {
        color: #000000 !important;
        font-size: 0.7rem !important;
    }
    .form-control, .form-select {
        padding: 0.7rem 1rem !important;
        font-size: 0.9rem !important;
        border-radius: 12px !important;
        border: 2px solid #e2e8f0 !important;
        background-color: #ffffff !important;
        transition: all 0.3s ease !important;
        font-weight: 500 !important;
    }
    .form-control:focus, .form-select:focus {
        border-color: #2563eb !important;
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15) !important;
        transform: translateY(-1px) !important;
    }
    .form-control[readonly] {
        background-color: #f1f5f9 !important;
        border-color: #cbd5e1 !important;
        color: #64748b !important;
        cursor: not-allowed !important;
        font-weight: 600 !important;
    }

    /* Table Premium */
    .table-responsive {
        border-radius: 20px !important;
        overflow: hidden !important;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05) !important;
        border: 1px solid #e2e8f0 !important;
    }
    #tableEcritures {
        margin: 0 !important;
        border-radius: 20px !important;
        overflow: hidden !important;
    }
    #tableEcritures thead {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%) !important;
    }
    #tableEcritures th {
        background: transparent !important;
        color: #ffffff !important;
        text-transform: uppercase !important;
        font-size: 0.75rem !important;
        font-weight: 800 !important;
        letter-spacing: 0.05em !important;
        padding: 1.2rem 1rem !important;
        border: none !important;
    }
    #tableEcritures td {
        padding: 1rem !important;
        font-size: 0.9rem !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f5f9 !important;
        font-weight: 500 !important;
    }
    #tableEcritures tbody tr:hover {
        background-color: #f8fafc !important;
    }

    /* Totaux Premium */
    .total-item {
        text-align: center !important;
        padding: 0 0.5rem !important;
    }
    .total-label {
        font-size: 0.7rem !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        color: #000000 !important;
        letter-spacing: 0.3px !important;
        margin-bottom: 0.3rem !important;
    }
    .total-amount {
        font-size: 1.2rem !important;
        font-weight: 700 !important;
        color: #1a202c !important;
        margin: 0 !important;
    }
    .total-amount.credit {
        color: #059669 !important;
    }
    .total-amount.debit {
        color: #dc2626 !important;
    }

    /* Animations */
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes shimmer {
        0% {
            transform: translateX(-100%) translateY(-100%) rotate(45deg);
        }
        100% {
            transform: translateX(100%) translateY(100%) rotate(45deg);
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .modal-body-premium {
            padding: 1.5rem !important;
        }
        .section-header {
            flex-direction: column !important;
            text-align: center !important;
        }
        .section-icon {
            margin-right: 0 !important;
            margin-bottom: 1rem !important;
        }
        .section-title {
            font-size: 1.4rem !important;
        }
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
          @include('components.header', ['page_title' => 'NOUVELLE <span class="text-gradient">ÉCRITURE</span>'])
        <!-- / Navbar -->

        <!-- Content wrapper -->
        <div class="content-wrapper">
          <!-- Content -->
          <div class="container-fluid p-4">
            <div class="row justify-content-center">
              <div class="col-12">
                <!-- Grand Conteneur Modal -->
                <div class="modal-container" style="
                    background: #ffffff;
                    border-radius: 30px;
                    box-shadow: 0 20px 50px rgba(0,0,0,0.1);
                    overflow: hidden;
                    min-height: 90vh;
                    border: 1px solid #e2e8f0;
                ">
                    <!-- Body Content -->
                    <div class="modal-body-premium" style="
                        padding: 3rem;
                        background: #ffffff;
                    ">
                        <form id="formEcriture">
                            <!-- Section Informations Générales -->
                            <div class="section-divider mb-5">
                                <div class="section-header" style="
                                    display: flex;
                                    align-items: center;
                                    margin-bottom: 2rem;
                                    padding-bottom: 1rem;
                                    border-bottom: 3px solid #2563eb;
                                ">
                                    <div class="section-icon" style="
                                        width: 40px;
                                        height: 40px;
                                        border-radius: 12px;
                                        background: linear-gradient(135deg, #2563eb 0%, #1e3a8a 100%);
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        margin-right: 1rem;
                                        color: #ffffff;
                                        font-size: 1rem;
                                        box-shadow: 0 5px 15px rgba(37, 99, 235, 0.2);
                                    ">
                                        <i class="bx bx-info-circle"></i>
                                    </div>
                                    <div>
                                        <h2 class="section-title" style="
                                            font-weight: 700;
                                            font-size: 1.2rem;
                                            color: #1a202c;
                                            margin: 0;
                                            text-transform: uppercase;
                                            letter-spacing: 0.5px;
                                        ">Informations Générales</h2>
                                        <p class="section-subtitle" style="
                                            font-size: 0.9rem;
                                            color: #000000;
                                            margin: 4px 0 0 0;
                                        ">Données communes à toutes les lignes d'écriture</p>
                                    </div>
                                </div>
                                
                                <div class="row g-4">
                                    <div class="col-md-3">
                                        <label for="date" class="form-label">
                                            <i class="bx bx-calendar"></i>Date de l'écriture <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" id="date" name="date" class="form-control" required 
                                               value="{{ date('Y-m-d') }}" 
                                               min="{{ date('Y-m-d', strtotime('-1 year')) }}" 
                                               max="{{ date('Y-m-d', strtotime('+1 year')) }}" readonly/>
                                        <div class="invalid-feedback">Veuillez renseigner une date valide.</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="imputation" class="form-label">
                                            <i class="bx bx-book-bookmark"></i>Journal d'imputation
                                        </label>
                                        <input type="text" id="code_journal_affiche" class="form-control" value="{{ $data['code'] ?? 'N/A' }}" readonly />
                                        <input type="hidden" id="imputation" name="code_journal_id" value="{{ $data['id_code'] ?? 'N/A' }}" class="form-control" data-code_imputation="{{ $data['code'] ?? 'N/A' }}" />
                                        <input type="hidden" name="id_exercice" value="{{ $data['id_exercice'] ?? '' }}" />
                                        <input type="hidden" name="journaux_saisis_id" value="{{ $data['id_journal'] ?? '' }}" />
                                    </div>
                                    <div class="col-md-3">
                                        <label for="n_saisie" class="form-label">
                                            <i class="bx bx-hash"></i>N° de Saisie
                                        </label>
                                        <input type="text" id="n_saisie" name="n_saisie" class="form-control" readonly value="000000000001" style="font-weight: bold; color: #1a202c;" />
                                        <small class="form-text text-muted">Numéro automatique</small>
                                    </div>
                                    <div class="col-md-12">
                                        <label for="description_operation" class="form-label">
                                            <i class="bx bx-file-text"></i>Libellé / Description de l'opération <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" id="description_operation" name="description_operation" class="form-control" placeholder="Saisissez le libellé de l'opération..." required />
                                        <div class="invalid-feedback">Veuillez entrer la description.</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="reference_piece" class="form-label">
                                            <i class="bx bx-receipt"></i>Référence Pièce
                                        </label>
                                        <input type="text" id="reference_piece" name="reference_piece" class="form-control" placeholder="N° Facture, Chèque..." />
                                        <small class="form-text text-muted">Commun à toutes les lignes</small>
                                    </div>
                                                                    <div class="col-md-4" id="div_compte_tresorerie" style="display: none;"> <label for="compte_tresorerie" class="form-label">
                                            <i class="bx bx-receipt"></i>Compte Trésorerie
                                        </label>
                                        <select id="compte_tresorerie" name="compte_tresorerie" class="form-select select2">
                                            <option value="" selected disabled>Chargement...</option>
                                            @foreach($comptesTresorerie as $treso)
                                                <option value="{{ $treso->id }}">{{ $treso->name }}</option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">Compte automatique pour ce journal</small>
                                    </div>
                                    <div class="col-md-8">
                                        <label for="piece_justificatif" class="form-label">
                                            <i class="bx bx-file"></i>Pièce justificative (PDF, Scan...)
                                        </label>
                                        <input type="file" id="piece_justificatif" name="piece_justificatif"
                                            class="form-control" accept=".pdf,.jpg,.jpeg,.png" />
                                        <small class="form-text text-muted">Commun à toutes les lignes</small>
                                        <div class="invalid-feedback">Veuillez ajouter un fichier justificatif.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Section Détails de l'Écriture -->
                            <div class="section-divider mb-5">
                                <div class="section-header" style="
                                    display: flex;
                                    align-items: center;
                                    margin-bottom: 2rem;
                                    padding-bottom: 1rem;
                                    border-bottom: 3px solid #2563eb;
                                ">
                                    <div class="section-icon" style="
                                        width: 40px;
                                        height: 40px;
                                        border-radius: 12px;
                                        background: linear-gradient(135deg, #2563eb 0%, #1e3a8a 100%);
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        margin-right: 1rem;
                                        color: #ffffff;
                                        font-size: 1rem;
                                        box-shadow: 0 5px 15px rgba(37, 99, 235, 0.2);
                                    ">
                                        <i class="bx bx-calculator"></i>
                                    </div>
                                    <div>
                                        <h2 class="section-title" style="
                                            font-weight: 700;
                                            font-size: 1.2rem;
                                            color: #1a202c;
                                            margin: 0;
                                            text-transform: uppercase;
                                            letter-spacing: 0.5px;
                                        ">Détails de l'Écriture</h2>
                                        <p class="section-subtitle" style="
                                            font-size: 0.9rem;
                                            color: #000000;
                                            margin: 4px 0 0 0;
                                        ">Comptes et montants de la ligne actuelle</p>
                                    </div>
                                </div>
                                
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label for="compte_general" class="form-label">
                                            <i class="bx bx-folder-open"></i>Compte Général <span class="text-danger">*</span>
                                        </label>
                                        <select id="compte_general" name="compte_general"
                                            class="form-select select2 w-100" data-live-search="true"
                                            title="Sélectionner un compte général" required>
                                            <option value="" selected disabled>Sélectionner un compte</option>
                                            @if(isset($plansComptables))
                                                @foreach ($plansComptables as $plan)
                                                    <option value="{{ $plan->id }}"
                                                        data-intitule_compte_general="{{ $plan->numero_de_compte }}">
                                                        {{ $plan->numero_de_compte }} -
                                                        {{ $plan->intitule }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="compte_tiers" class="form-label">
                                            <i class="bx bx-user"></i>Compte Tiers (Le cas échéant)
                                        </label>
                                        <div class="d-flex gap-2 align-items-center">
                                            <select id="compte_tiers" name="compte_tiers"
                                                class="form-select select2" style="max-width: 250px; flex: 1;" data-live-search="true"
                                                title="Sélectionner un compte tiers">
                                                <option value="" selected disabled>Sélectionner un compte tiers</option>
                                                @if(isset($plansTiers))
                                                    @foreach ($plansTiers as $tier)
                                                        <option value="{{ $tier->id }}" data-compte-general="{{ $tier->compte_general }}">
                                                            {{ $tier->numero_de_tiers }} -
                                                            {{ $tier->intitule }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#createTiersModal" title="Créer un nouveau compte tiers" style="
                                                background: #ffffff;
                                                border: 2px solid #e2e8f0;
                                                color: #64748b;
                                                padding: 0.7rem 1rem;
                                                border-radius: 12px;
                                                font-weight: 600;
                                                transition: all 0.3s ease;
                                                white-space: nowrap;
                                            " onmouseover="this.style.borderColor='#cbd5e1'; this.style.color='#1a202c';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#64748b';">
                                                <i class="bx bx-plus"></i> Créer
                                            </button>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="debit" class="form-label">
                                            <i class="bx bx-arrow-from-left"></i>Montant Débit
                                        </label>
                                        <input type="number" id="debit" name="debit" class="form-control debit-amount" style="max-width: 200px;"
                                               step="0.01" min="0" placeholder="0.00" />
                                    </div>
                                    <div class="col-md-3">
                                        <label for="credit" class="form-label">
                                            <i class="bx bx-arrow-from-right"></i>Montant Crédit
                                        </label>
                                        <input type="number" id="credit" name="credit" class="form-control credit-amount" style="max-width: 200px;"
                                               step="0.01" min="0" placeholder="0.00" />
                                    </div>
                                    <div class="col-md-4">
                                        <label for="plan_analytique" class="form-label">
                                            <i class="bx bx-pie-chart"></i>Analytique
                                        </label>
                                        <select id="plan_analytique" name="plan_analytique"
                                            class="form-select w-100" required>
                                            <option value="1">Oui</option>
                                            <option value="0" selected>Non</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </form>
                        
                        <!-- Section Tableau des Écritures -->
                        <div class="section-divider mb-5">
                            <div class="section-header" style="
                                display: flex;
                                align-items: center;
                                margin-bottom: 2rem;
                                padding-bottom: 1rem;
                                border-bottom: 3px solid #2563eb;
                            ">
                                <div class="section-icon" style="
                                    width: 40px;
                                    height: 40px;
                                    border-radius: 12px;
                                    background: linear-gradient(135deg, #2563eb 0%, #1e3a8a 100%);
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    margin-right: 1rem;
                                    color: #ffffff;
                                    font-size: 1rem;
                                    box-shadow: 0 5px 15px rgba(37, 99, 235, 0.2);
                                ">
                                    <i class="bx bx-list-check"></i>
                                </div>
                                <div>
                                        <h2 class="section-title" style="
                                            font-weight: 700;
                                            font-size: 1.2rem;
                                            color: #1a202c;
                                            margin: 0;
                                            text-transform: uppercase;
                                            letter-spacing: 0.5px;
                                        ">Écritures Enregistrées</h2>
                                        <p class="section-subtitle" style="
                                            font-size: 0.9rem;
                                            color: #000000;
                                            margin: 4px 0 0 0;
                                        ">Liste des lignes comptables à valider</p>
                                    </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="d-flex align-items-center gap-3">
                                    <h6 class="mb-0 fw-bold text-dark" style="
                                        font-size: 1.1rem;
                                    ">
                                        <i class="bx bx-file-alt me-2 text-primary"></i>Écritures saisies :
                                    </h6>
                                    <div class="dropdown" id="brouillonMenu">
                                        <button class="btn btn-outline-premium btn-premium dropdown-toggle" type="button" id="dropdownBrouillon" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-save me-1"></i>
                                            <span id="brouillonIndicator" style="display: none;">
                                                <i class="bx bx-circle text-warning" style="font-size: 0.6rem;"></i>
                                            </span>
                                            Brouillon
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownBrouillon">
                                            <li><a class="dropdown-item" href="{{ route('brouillons.index') }}"><i class="bx bx-folder-open me-2"></i>Charger le brouillon</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="effacerBrouillon()"><i class="bx bx-trash me-2"></i>Effacer le brouillon</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm" id="tableEcritures">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>N° Saisie</th>
                                            <th>Journal</th>
                                            <th>Libellé</th>
                                            <th>Réf Pièce</th>
                                            <th>Cpte Général</th>
                                            <th>Cpte Tiers</th>
                                            <th>Débit</th>
                                            <th>Crédit</th>
                                            <th>Pièce</th>
                                            <th>ANALYTIQUE</th>
                                            <th>Modifier</th>
                                            <th>Supprimer</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Section Totaux et Actions -->
                        <div class="section-divider">
                            <div class="section-header" style="
                                display: flex;
                                align-items: center;
                                margin-bottom: 2rem;
                                padding-bottom: 1rem;
                                border-bottom: 3px solid #2563eb;
                            ">
                                <div class="section-icon" style="
                                    width: 40px;
                                    height: 40px;
                                    border-radius: 12px;
                                    background: linear-gradient(135deg, #2563eb 0%, #1e3a8a 100%);
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    margin-right: 1rem;
                                    color: #ffffff;
                                    font-size: 1rem;
                                    box-shadow: 0 5px 15px rgba(37, 99, 235, 0.2);
                                ">
                                    <i class="bx bx-calculator"></i>
                                </div>
                                <div>
                                        <h2 class="section-title" style="
                                            font-weight: 700;
                                            font-size: 1.2rem;
                                            color: #1a202c;
                                            margin: 0;
                                            text-transform: uppercase;
                                            letter-spacing: 0.5px;
                                        ">Totaux et Validation</h2>
                                        <p class="section-subtitle" style="
                                            font-size: 0.9rem;
                                            color: #000000;
                                            margin: 4px 0 0 0;
                                        ">Vérification de l'équilibre et actions finales</p>
                                    </div>
                            </div>
                            
                            <div class="totals-section" style="
                                background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
                                border-radius: 20px;
                                padding: 1.5rem;
                                margin-bottom: 1.5rem;
                                border: 2px solid #e2e8f0;
                                box-shadow: 0 5px 15px rgba(0,0,0,0.03);
                            ">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="row">
                                            <div class="col-md-4 total-item">
                                                <div class="total-label">Total Débit</div>
                                                <div id="totalDebit" class="total-amount debit">0.00</div>
                                            </div>
                                            <div class="col-md-4 total-item">
                                                <div class="total-label">Total Crédit</div>
                                                <div id="totalCredit" class="total-amount credit">0.00</div>
                                            </div>
                                            <div class="col-md-4 total-item">
                                                <div class="total-label">Balance</div>
                                                <div id="balanceIndicator" class="total-amount">
                                                    <i class="bx bx-minus-circle text-muted fs-3"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-grid gap-2">
                                            <button type="button" class="btn btn-primary-premium btn-premium w-100" onclick="ajouterEcriture()">
                                                <i class="bx bx-plus-circle me-2"></i>Ajouter une ligne
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="modal-footer-premium" style="
                        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
                        padding: 2.5rem 3rem;
                        border-top: 1px solid #e2e8f0;
                    ">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-outline-premium btn-premium" onclick="viderFormulaireComplet()">
                                        <i class="bx bx-eraser me-2"></i>Effacer tout
                                    </button>
                                    <button type="button" class="btn btn-outline-premium btn-premium" id="btnBrouillon" onclick="sauvegarderBrouillon()">
                                        <i class="bx bx-save me-2"></i>Enregistrer en brouillon
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-success-premium btn-premium w-100" id="btnEnregistrer" onclick="enregistrerEcritures()">
                                    <i class="bx bx-check-circle me-2"></i><span id="btnText">VALIDER & ENREGISTRER</span>
                                    <span id="btnSpinner" class="spinner-border spinner-border-sm d-none ms-2"
                                        role="status" aria-hidden="true"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
      </div>
      <!-- / Layout wrapper -->

      <!-- Core JS -->

      <!-- Modal pour créer un nouveau compte tiers (Design Premium) -->
      <div class="modal fade" id="createTiersModal" tabindex="-1" aria-labelledby="createTiersModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-centered">
              <div class="modal-content" style="border-radius: 25px; border: none; overflow: hidden;">
                  <div class="modal-header" style="background: linear-gradient(135deg, #2563eb 0%, #1e3a8a 100%); border: none; padding: 2rem;">
                      <h5 class="modal-title" id="createTiersModalLabel" style="color: #ffffff; font-weight: 800; font-size: 1.5rem;">
                          <i class="bx bx-user-plus me-3"></i>CRÉER UN NOUVEAU TIERS
                      </h5>
                      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
                  </div>
                  <form id="createTiersForm">
                      <div class="modal-body" style="padding: 2.5rem;">
                          <div class="row g-4">
                              <div class="col-md-6">
                                  <label for="type_tiers" class="form-label">
                                      <i class="bx bx-tag"></i>Type de tiers *
                                  </label>
                                  <select id="type_tiers" name="type_de_tiers" class="form-select" required>
                                      <option value="" selected disabled>Sélectionner un type</option>
                                      <option value="Fournisseur">Fournisseur</option>
                                      <option value="Client">Client</option>
                                      <option value="Personnel">Personnel</option>
                                      <option value="CNPS">CNPS</option>
                                      <option value="Impots">Impots</option>
                                      <option value="Associé">Associé</option>
                                      <option value="Divers Tiers">Divers Tiers</option>
                                  </select>
                              </div>
                              <div class="col-md-6">
                                  <label for="compte_general_tiers" class="form-label">
                                      <i class="bx bx-folder-open"></i>Compte général *
                                  </label>
                                  <select id="compte_general_tiers" name="compte_general" class="form-select" required>
                                      <option value="" selected disabled>Sélectionner d'abord le type de tiers</option>
                                  </select>
                              </div>
                              <div class="col-md-6">
                                  <label for="numero_tiers" class="form-label">
                                      <i class="bx bx-hash"></i>Numéro de tiers *
                                  </label>
                                  <input type="text" id="numero_tiers" name="numero_de_tiers" class="form-control" readonly placeholder="Sera généré automatiquement" required>
                              </div>
                              <div class="col-md-6">
                                  <label for="intitule_tiers" class="form-label">
                                      <i class="bx bx-user"></i>Intitulé du tiers *
                                  </label>
                                  <input type="text" id="intitule_tiers" name="intitule" class="form-control" placeholder="Nom du tiers" required>
                              </div>
                          </div>
                      </div>
                      <div class="modal-footer" style="background: #f8fafc; border: none; padding: 1.5rem 2.5rem;">
                          <button type="button" class="btn btn-outline-premium btn-premium" data-bs-dismiss="modal">
                              <i class="bx bx-x me-2"></i>Annuler
                          </button>
                          <button type="button" class="btn btn-primary-premium btn-premium" id="btnCreateTiers" onclick="window.createTiersSimple(event)">
                              <i class="bx bx-save me-2"></i>Créer le compte tiers
                          </button>
                      </div>
                  </form>
              </div>
          </div>
      </div>

      @include('components.footer')


    </body>

    </html>

<script>
// Logic for Creating Tiers and Modal Management
// Using standard Bootstrap 5 API to avoid flickering issues

document.addEventListener('DOMContentLoaded', function() {
    const createTiersModalEl = document.getElementById('createTiersModal');
 
    if (!createTiersModalEl) return;

    // Use Bootstrap's Modal instance
    const tiersModal = new bootstrap.Modal(createTiersModalEl);
    
    // Reset form when modal opens
    createTiersModalEl.addEventListener('show.bs.modal', function () {
        const form = document.getElementById('createTiersForm');
        if (form) {
            form.reset();
            const numeroTiers = document.getElementById('numero_tiers');
            const compteGeneralTiers = document.getElementById('compte_general_tiers');
            if (numeroTiers) numeroTiers.value = '';
            if (compteGeneralTiers) {
                compteGeneralTiers.innerHTML = '<option value="" selected disabled>Sélectionner d\'abord le type de tiers</option>';
            }
        }
    });

    // Handle Tier Creation
    window.createTiersSimple = function(event) {
        if (event) event.preventDefault();
        
        const typeTiers = document.getElementById('type_tiers').value;
        const compteGeneral = document.getElementById('compte_general_tiers').value;
        const intitule = document.getElementById('intitule_tiers').value.trim();
        const numeroTiersValue = document.getElementById('numero_tiers').value;
        
        if (!typeTiers || !compteGeneral || !intitule) {
            alert('Veuillez remplir tous les champs obligatoires');
            return;
        }
        
        const btn = document.getElementById('btnCreateTiers');
        const originalText = btn.innerHTML;
        
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Création...';
        btn.disabled = true;
        
        const data = {
            'type_de_tiers': typeTiers,
            'compte_general': compteGeneral,
            'intitule': intitule,
            'numero_de_tiers': numeroTiersValue
        };
        
        fetch('{{ route("plan_tiers.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                // Close modal using BS API
                tiersModal.hide();
                
                // Add to tiers select
                const select = document.getElementById('compte_tiers');
                const option = document.createElement('option');
                option.value = result.id;
                option.text = (result.numero_de_tiers || numeroTiersValue) + ' - ' + result.intitule;
                option.selected = true;
                select.appendChild(option);
                
                // Trigger change for Select2/Bootstrap-Select if needed
                if (typeof $ !== 'undefined') {
                    $(select).trigger('change');
                }
                
                alert('Compte tiers créé avec succès !');
            } else {
                alert('Erreur: ' + (result.error || 'Erreur inconnue'));
            }
        })
        .catch(error => {
            console.error('Erreur AJAX:', error);
            alert('Erreur lors de la création: ' + error.message);
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    };

    // Logic for type_tiers change (filtering and number generation)
    const typeTiersSelect = document.getElementById('type_tiers');
    if (typeTiersSelect) {
        typeTiersSelect.addEventListener('change', function() {
            const typeTiers = this.value;
            const compteGeneralSelect = document.getElementById('compte_general_tiers');
            const numeroTiersInput = document.getElementById('numero_tiers');
            
            if (!typeTiers) {
                compteGeneralSelect.innerHTML = '<option value="" selected disabled>Sélectionner d\'abord le type de tiers</option>';
                numeroTiersInput.value = '';
                return;
            }
            
            const prefixes = {
                'Fournisseur': '40',
                'Client': '41',
                'Personnel': '42',
                'CNPS': '43',
                'Impots': '44',
                'Associé': '45'
            };
            
            const mainSelect = document.getElementById('compte_general');
            if (mainSelect) {
                const options = Array.from(mainSelect.options).filter(opt => opt.value);
                let filtered = [];
                
                if (typeTiers === 'Divers Tiers') {
                    const allPrefixes = Object.values(prefixes);
                    filtered = options.filter(opt => {
                        const numero = opt.textContent.split(' - ')[0].trim();
                        return !allPrefixes.some(prefix => numero.startsWith(prefix));
                    });
                } else {
                    const prefix = prefixes[typeTiers];
                    filtered = options.filter(opt => {
                        const numero = opt.textContent.split(' - ')[0].trim();
                        return numero.startsWith(prefix);
                    });
                }
                
                compteGeneralSelect.innerHTML = '<option value="" selected disabled>Sélectionner un compte général</option>';
                filtered.forEach(opt => {
                    const newOpt = opt.cloneNode(true);
                    compteGeneralSelect.appendChild(newOpt);
                });
            }
            
            // Generate number
            if (typeTiers !== 'Divers Tiers' && prefixes[typeTiers]) {
                fetch('/plan_tiers/' + prefixes[typeTiers])
                    .then(response => response.json())
                    .then(data => {
                        if (data.numero) {
                            numeroTiersInput.value = data.numero;
                        }
                    })
                    .catch(() => {
                        const random = Math.floor(Math.random() * 9000) + 1000;
                        numeroTiersInput.value = prefixes[typeTiers] + random;
                    });
            } else {
                numeroTiersInput.value = '';
            }
        });
    }
});

// 5. Fonctions existantes inchangées
function ajouterEcriture() {
    try {
        const date = document.getElementById('date');
        const nSaisie = document.getElementById('n_saisie');
        const libelle = document.getElementById('description_operation');
        const debit = document.getElementById('debit');
        const credit = document.getElementById('credit');
        const compteGeneral = document.getElementById('compte_general');
        const referencePiece = document.getElementById('reference_piece');
        const compteTiers = document.getElementById('compte_tiers');
        const pieceFile = document.getElementById('piece_justificatif');
        const imputationInput = document.getElementById('code_journal_affiche');
        const planAnalytique = document.getElementById('plan_analytique');

        if (!date || !libelle || !compteGeneral) {
            alert('Champs du formulaire introuvables.');
            return;
        }

        if (!date.value || !libelle.value || !compteGeneral.value || compteGeneral.value === '') {
            alert('Veuillez remplir tous les champs obligatoires (Date, Description, Compte Général).');
            return;
        }

        if (!debit.value && !credit.value) {
            alert('Veuillez saisir un montant au débit ou au crédit.');
            return;
        }

        const tbody = document.querySelector('#tableEcritures tbody');
        if (!tbody) {
            alert('Tableau des écritures introuvable.');
            return;
        }
        
        // Récupérer l'ID du compte de trésorerie s'il est visible
        const compteTresorerieSelect = document.getElementById('compte_tresorerie');
        const compteTresorerieId = compteTresorerieSelect && 
                                 window.getComputedStyle(compteTresorerieSelect.parentElement).display !== 'none' ? 
                                 compteTresorerieSelect.value : '';

        const newRow = tbody.insertRow();

        const imputationValue = imputationInput ? imputationInput.value : '';
        const analytiqueValue = planAnalytique ? (planAnalytique.value === '1' ? 'Oui' : 'Non') : '';
        const compteText = compteGeneral.options[compteGeneral.selectedIndex].text;
        const compteTiersValue = compteTiers && compteTiers.value ? compteTiers.options[compteTiers.selectedIndex].text : '';
        const pieceFileName = pieceFile && pieceFile.files[0] ? pieceFile.files[0].name : '';

        // Stocker le fichier globalement pour la visualisation
        let globalPieceFile = null;
        
        // Stocker l'ID du compte de trésorerie dans la ligne
        if (compteTresorerieId) {
            newRow.setAttribute('data-compte-tresorerie-id', compteTresorerieId);
        }
        
        // Créer les cellules une par une pour pouvoir ajouter des attributs
        const cells = [
            date.value,
            nSaisie ? nSaisie.value : '',
            imputationValue,
            libelle.value,
            referencePiece ? referencePiece.value || '' : '',
            '', // Compte général - sera rempli avec l'élément personnalisé
            compteTiersValue,
            debit.value || '',
            credit.value || '',
            '', // Pièce justificative - sera rempli avec le bouton Voir
            analytiqueValue,
            compteTresorerieId // Ajout de l'ID du compte de trésorerie
        ];

        // Ajouter chaque cellule avec son contenu
        cells.forEach((content, index) => {
            const cell = newRow.insertCell();
            if (index === 5) {
                // Pour la cellule du compte général, ajouter l'attribut data-plan-comptable-id
                cell.textContent = compteText;
                cell.setAttribute('data-plan-comptable-id', compteGeneral.value);
            } else if (index === 6 && compteTiers && compteTiers.value) {
                // Pour la cellule du compte tiers, ajouter l'attribut data-tiers-id
                cell.textContent = compteTiersValue;
                cell.setAttribute('data-tiers-id', compteTiers.value);
            } else if (index === 9) {
                // Pour la cellule de la pièce justificative, ajouter un bouton Voir
                if (pieceFile) {
                    globalPieceFile = pieceFile; // Stocker le fichier globalement
                    cell.innerHTML = `<button class="btn btn-sm btn-primary-premium btn-premium" onclick="voirPieceJustificativeLocale()" style="border-radius: 10px;">
                        <i class="bx bx-eye me-1"></i>Voir
                    </button>`;
                    cell.setAttribute('data-piece-filename', pieceFile.name);
                } else {
                    cell.textContent = '';
                }
            } else {
                cell.textContent = content;
            }
        });

        const modifierCell = document.createElement('td');
        modifierCell.innerHTML = `
            <button type="button" class="btn btn-sm btn-warning btn-premium" onclick="modifierEcriture(this.closest('tr'));" style="border-radius: 10px;" title="Modifier cette ligne">
                <i class="bx bx-edit"></i>
            </button>
        `;
        newRow.appendChild(modifierCell);

        const supprimerCell = document.createElement('td');
        supprimerCell.innerHTML = `
            <button type="button" class="btn btn-sm btn-danger btn-premium" onclick="supprimerEcriture(this.closest('tr'));" style="border-radius: 10px;" title="Supprimer cette ligne">
                <i class="bx bx-trash"></i>
            </button>
        `;
        newRow.appendChild(supprimerCell);

            // Réinitialisation SEULEMENT des champs spécifiques à chaque ligne
            compteGeneral.value = '';
            if (compteTiers) compteTiers.value = '';
            debit.value = '';
            credit.value = '';
            if (planAnalytique) planAnalytique.value = '0';
            
            // Réinitialisation des états et styles via notre fonction globale
            if (typeof window.resetExclusivity === 'function') {
                window.resetExclusivity();
            }

        // Mise à jour des totaux
        updateTotals();

        alert('Écriture ajoutée avec succès !');

    } catch (error) {
        console.error('Erreur lors de l\'ajout de l\'écriture:', error);
        alert('Une erreur est survenue: ' + error.message);
    }
}

    // Fonction pour visualiser la pièce justificative locale (avant sauvegarde)
    function voirPieceJustificativeLocale() {
        const pieceFile = document.getElementById('piece_justificatif').files[0];
        if (!pieceFile) {
            alert('Aucun fichier à visualiser');
            return;
        }
        
        // Créer une URL temporaire pour le fichier local
        const fileURL = URL.createObjectURL(pieceFile);
        
        // Créer une fenêtre popup pour afficher le fichier
        const popup = window.open('', '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
        
        // Vérifier le type de fichier et afficher en conséquence
        if (pieceFile.type.startsWith('image/')) {
            // Pour les images
            popup.document.write(`
                <html>
                    <head><title>${pieceFile.name}</title></head>
                    <body style="margin:0;padding:20px;text-align:center;">
                        <img src="${fileURL}" style="max-width:100%;max-height:100%;" alt="${pieceFile.name}">
                    </body>
                </html>
            `);
        } else {
            // Pour les PDF et autres fichiers
            popup.document.write(`
                <html>
                    <head><title>${pieceFile.name}</title></head>
                    <body style="margin:0;padding:20px;text-align:center;">
                        <iframe src="${fileURL}" width="100%" height="100%" style="border:none;"></iframe>
                    </body>
                </html>
            `);
        }
        
        // Nettoyer l'URL après fermeture de la fenêtre
        popup.addEventListener('beforeunload', () => {
            URL.revokeObjectURL(fileURL);
        });
    }

    // Fonction pour visualiser la pièce justificative (après sauvegarde)
    function voirPieceJustificative(filename) {
        if (!filename) {
            alert('Aucun fichier à visualiser');
            return;
        }
        
        // Ouvrir le fichier dans une nouvelle fenêtre
        const url = `/justificatifs/${filename}`;
        window.open(url, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
    }

    // Fonction pour vider complètement le formulaire (nouvelle écriture)
    function viderFormulaireComplet() {
        // Vider les champs communs
        const date = document.getElementById('date');
        const libelle = document.getElementById('description_operation');
        const referencePiece = document.getElementById('reference_piece');
        const pieceFile = document.getElementById('piece_justificatif');
        
        if (libelle) libelle.value = '';
        if (referencePiece) referencePiece.value = '';
        if (pieceFile) pieceFile.value = '';
        
        // Vider les champs spécifiques à chaque ligne
        const compteGeneral = document.getElementById('compte_general');
        const compteTiers = document.getElementById('compte_tiers');
        const debit = document.getElementById('debit');
        const credit = document.getElementById('credit');
        const planAnalytique = document.getElementById('plan_analytique');
        
        if (compteGeneral) compteGeneral.value = '';
        if (compteTiers) compteTiers.value = '';
        if (debit) debit.value = '';
        if (credit) credit.value = '';
        if (planAnalytique) planAnalytique.value = '0';
    }

    // Fonction pour récupérer le prochain numéro de saisie du serveur
    async function fetchNextSaisieNumber() {
        try {
            const response = await fetch('{{ route("api.next-saisie-number") }}');
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    const champSaisie = document.getElementById('n_saisie');
                    if (champSaisie) {
                        champSaisie.value = data.nextSaisieNumber;
                    }
                }
            }
        } catch (e) {
            console.error('Erreur sync n_saisie:', e);
        }
    }

    // --- DÉTECTION TYPE DE JOURNAL (TRÉSORERIE) ---
    const prefixesTresorerie = ['BQ', 'CA', 'CH', 'CS', 'BANQUE', 'CAISSE'];
    
    function verifierTypeJournal(code) {
        const divTreso = document.getElementById('div_compte_tresorerie');
        const estTresorerie = prefixesTresorerie.some(prefix => 
            code && code.toUpperCase().startsWith(prefix)
        );
        
        if (divTreso) {
            if (estTresorerie) {
                divTreso.style.display = 'block';
                const compteTresorerie = document.getElementById('compte_tresorerie');
                if (compteTresorerie) compteTresorerie.setAttribute('required', 'required');
            } else {
                divTreso.style.display = 'none';
                const compteTresorerie = document.getElementById('compte_tresorerie');
                if (compteTresorerie) compteTresorerie.removeAttribute('required');
            }
        }
    }

    // Initialisation
    document.addEventListener('DOMContentLoaded', function() {
        fetchNextSaisieNumber(); // Récupérer le vrai numéro du serveur
        
        // Détection du journal au chargement
        const journalId = document.getElementById('imputation').value;
        if(journalId) {
            chargerCompteTresorerie(journalId);
        }
        const codeJournalInput = document.getElementById('code_journal_affiche');
        const codeJournal = codeJournalInput ? codeJournalInput.value : null;
        if (codeJournal && codeJournal !== 'N/A') {
            verifierTypeJournal(codeJournal);
        }
            
            // Ajouter un écouteur sur le changement de journal
            const imputationSelect = document.getElementById('imputation');
            if (imputationSelect) {
                imputationSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const codeJournal = selectedOption.getAttribute('data-code') || '';
                    verifierTypeJournal(codeJournal);
                    
                    // Charger aussi le compte de trésorerie si journal sélectionné
                    const journalId = this.value;
                    if(journalId) {
                        chargerCompteTresorerie(journalId);
                    }
                });
            }
        
        // --- EXCLUSIVITÉ DÉBIT / CRÉDIT ---
        const debitInput = document.getElementById('debit');
        const creditInput = document.getElementById('credit');
        if (debitInput && creditInput) {
            const toggleExclusivity = (source, target) => {
                const val = parseFloat(source.value) || 0;
                if (val > 0) {
                    target.value = '';
                    target.setAttribute('readonly', 'readonly');
                    target.style.backgroundColor = '#f1f5f9';
                    target.style.cursor = 'not-allowed';
                } else {
                    target.removeAttribute('readonly');
                    target.style.backgroundColor = '';
                    target.style.cursor = '';
                }
            };

            debitInput.addEventListener('input', () => toggleExclusivity(debitInput, creditInput));
            creditInput.addEventListener('input', () => toggleExclusivity(creditInput, debitInput));
            
            // Exécuter une fois pour initialiser (utile lors de l'ajout d'une ligne ou chargement)
            window.resetExclusivity = () => {
                debitInput.removeAttribute('readonly');
                debitInput.style.backgroundColor = '';
                debitInput.style.cursor = '';
                creditInput.removeAttribute('readonly');
                creditInput.style.backgroundColor = '';
                creditInput.style.cursor = '';
            };
        }
        
        // Initialisation pour charger un brouillon via URL
        const urlParams = new URLSearchParams(window.location.search);
        const batchId = urlParams.get('batch_id');
        const nSaisie = urlParams.get('n_saisie');
        if (batchId) {
            chargerBrouillonBackend(batchId);
        } else if (nSaisie) {
            chargerEcritureBackend(nSaisie);
        }
    });

    async function chargerBrouillonBackend(id) {
        try {
            const btnEnregistrer = document.getElementById('btnEnregistrer');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            
            if (btnText) btnText.textContent = 'CHARGEMENT...';
            if (btnEnregistrer) btnEnregistrer.disabled = true;
            if (btnSpinner) btnSpinner.classList.remove('d-none');

            const res = await fetch(`/api/brouillons/${id}`);
            const json = await res.json();
            
            if (json.success) {
                const tbody = document.querySelector('#tableEcritures tbody');
                if (tbody) tbody.innerHTML = '';
                
                // Remplir les champs communs
                if (json.summary.date) document.getElementById('date').value = json.summary.date;
                if (json.summary.description) document.getElementById('description_operation').value = json.summary.description;
                if (json.summary.reference) document.getElementById('reference_piece').value = json.summary.reference;
                
                // Journal
                if (json.summary.code_journal_id) {
                    const imputationHidden = document.getElementById('imputation');
                    const codeJournalAffiche = document.getElementById('code_journal_affiche');
                    if (imputationHidden) imputationHidden.value = json.summary.code_journal_id;
                    if (codeJournalAffiche) codeJournalAffiche.value = json.summary.journal_code;
                    
                    // Déclencher la vérification du type de journal (pour afficher le compte de trésorerie si nécessaire)
                    if (typeof verifierTypeJournal === 'function') {
                        verifierTypeJournal(json.summary.journal_code);
                    }
                }

                // N° de Saisie
                if (json.summary.n_saisie) {
                    const nSaisieInput = document.getElementById('n_saisie');
                    if (nSaisieInput) nSaisieInput.value = json.summary.n_saisie;
                }

                // Compte Trésorerie
                if (json.summary.compte_tresorerie_id) {
                    const selectTreso = document.getElementById('compte_tresorerie');
                    if (selectTreso) {
                        selectTreso.value = json.summary.compte_tresorerie_id;
                        if (typeof $(selectTreso).select2 === 'function') {
                            $(selectTreso).trigger('change');
                        }
                    }
                }

                // Pièce jointe (si elle existe dans le brouillon)
                if (json.summary.piece_justificatif) {
                    const form = document.getElementById('formEcriture');
                    let hiddenPiece = document.getElementById('draft_piece_filename');
                    if (!hiddenPiece) {
                        hiddenPiece = document.createElement('input');
                        hiddenPiece.type = 'hidden';
                        hiddenPiece.id = 'draft_piece_filename';
                        hiddenPiece.name = 'draft_piece_filename';
                        form.appendChild(hiddenPiece);
                    }
                    hiddenPiece.value = json.summary.piece_justificatif;
                }

                json.brouillons.forEach(b => {
                    ajouterLigneBrouillon(b);
                });
                
                updateTotals();
                showAlert('success', 'Brouillon chargé avec succès');
            } else {
                showAlert('danger', "Erreur: " + (json.message || "Impossible de charger le brouillon"));
            }
        } catch (e) {
            showAlert('danger', "Erreur lors du chargement: " + e.message);
        } finally {
            const btnEnregistrer = document.getElementById('btnEnregistrer');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            if (btnText) btnText.textContent = 'VALIDER & ENREGISTRER';
            if (btnEnregistrer) btnEnregistrer.disabled = false;
            if (btnSpinner) btnSpinner.classList.add('d-none');
            updateTotals();
        }
    }

    async function chargerEcritureBackend(nSaisie) {
        try {
            const btnEnregistrer = document.getElementById('btnEnregistrer');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            
            if (btnText) btnText.textContent = 'CHARGEMENT...';
            if (btnEnregistrer) btnEnregistrer.disabled = true;
            if (btnSpinner) btnSpinner.classList.remove('d-none');

            const res = await fetch(`/api/ecriture/load-by-saisie/${nSaisie}`);
            const json = await res.json();
            
            if (json.success) {
                const tbody = document.querySelector('#tableEcritures tbody');
                if (tbody) tbody.innerHTML = '';
                
                // Remplir les champs communs
                if (json.summary.date) document.getElementById('date').value = json.summary.date;
                if (json.summary.description) document.getElementById('description_operation').value = json.summary.description;
                if (json.summary.reference) document.getElementById('reference_piece').value = json.summary.reference;
                
                // Journal
                if (json.summary.code_journal_id) {
                    const imputationHidden = document.getElementById('imputation');
                    const codeJournalAffiche = document.getElementById('code_journal_affiche');
                    if (imputationHidden) imputationHidden.value = json.summary.code_journal_id;
                    if (codeJournalAffiche) codeJournalAffiche.value = json.summary.journal_code;
                    
                    // Déclencher la vérification du type de journal (pour afficher le compte de trésorerie si nécessaire)
                    if (typeof verifierTypeJournal === 'function') {
                        verifierTypeJournal(json.summary.journal_code);
                    }
                }

                // N° de Saisie
                if (json.summary.n_saisie) {
                    const nSaisieInput = document.getElementById('n_saisie');
                    if (nSaisieInput) nSaisieInput.value = json.summary.n_saisie;
                }

                // Compte Trésorerie
                if (json.summary.compte_tresorerie_id) {
                    const selectTreso = document.getElementById('compte_tresorerie');
                    if (selectTreso) {
                        selectTreso.value = json.summary.compte_tresorerie_id;
                        if (typeof $(selectTreso).select2 === 'function') {
                            $(selectTreso).trigger('change');
                        }
                    }
                }

                // Pièce jointe (si elle existe dans l'écriture)
                if (json.summary.piece_justificatif) {
                    const form = document.getElementById('formEcriture');
                    let hiddenPiece = document.getElementById('draft_piece_filename');
                    if (!hiddenPiece) {
                        hiddenPiece = document.createElement('input');
                        hiddenPiece.type = 'hidden';
                        hiddenPiece.id = 'draft_piece_filename';
                        hiddenPiece.name = 'draft_piece_filename';
                        form.appendChild(hiddenPiece);
                    }
                    hiddenPiece.value = json.summary.piece_justificatif;
                }

                // Ajouter les lignes d'écriture
                json.brouillons.forEach(b => {
                    ajouterLigneBrouillon(b);
                });
                
                updateTotals();
                showAlert('success', 'Écriture chargée avec succès pour modification');
            } else {
                showAlert('danger', "Erreur: " + (json.message || "Impossible de charger l'écriture"));
            }
        } catch (e) {
            showAlert('danger', "Erreur lors du chargement: " + e.message);
        } finally {
            const btnEnregistrer = document.getElementById('btnEnregistrer');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            if (btnText) btnText.textContent = 'VALIDER & ENREGISTRER';
            if (btnEnregistrer) btnEnregistrer.disabled = false;
            if (btnSpinner) btnSpinner.classList.add('d-none');
            updateTotals();
        }
    }

    function ajouterLigneBrouillon(b) {
        const tbody = document.querySelector('#tableEcritures tbody');
        const newRow = tbody.insertRow();
        
        const date = b.date || document.getElementById('date').value;
        const nSaisie = b.n_saisie || document.getElementById('n_saisie').value;
        const imputation = document.getElementById('code_journal_affiche').value;
        const description = b.description_operation || '';
        const reference = b.reference_piece || '';
        const compteText = b.plan_comptable ? `${b.plan_comptable.numero_de_compte} - ${b.plan_comptable.intitule}` : '-';
        const tiersText = b.plan_tiers ? `${b.plan_tiers.numero_de_tiers} - ${b.plan_tiers.intitule}` : '';
        const debit = b.debit || 0;
        const credit = b.credit || 0;
        const analytique = b.plan_analytique ? 'Oui' : 'Non';
        const pieceFileName = b.piece_justificatif || '';

        if (b.compte_tresorerie_id) {
            newRow.setAttribute('data-compte-tresorerie-id', b.compte_tresorerie_id);
        }

        const cells = [
            date, nSaisie, imputation, description, reference,
            compteText, tiersText, debit, credit, '', analytique
        ];

        cells.forEach((content, index) => {
            const cell = newRow.insertCell();
            if (index === 5) {
                cell.textContent = compteText;
                cell.setAttribute('data-plan-comptable-id', b.plan_comptable_id);
            } else if (index === 6 && b.plan_tiers_id) {
                cell.textContent = tiersText;
                cell.setAttribute('data-tiers-id', b.plan_tiers_id);
            } else if (index === 9) {
                if (pieceFileName) {
                    cell.innerHTML = `<button class="btn btn-sm btn-primary-premium btn-premium" onclick="voirPieceJustificative('${pieceFileName}')" style="border-radius: 10px;">
                        <i class="bx bx-eye me-1"></i>Voir
                    </button>`;
                    cell.setAttribute('data-piece-filename', pieceFileName);
                } else {
                    cell.textContent = '';
                }
            } else {
                cell.textContent = content;
            }
        });

        const modifierCell = document.createElement('td');
        modifierCell.innerHTML = `<button type="button" class="btn btn-sm btn-warning btn-premium" onclick="modifierEcriture(this.closest('tr'));" style="border-radius: 10px;" title="Modifier cette ligne"><i class="bx bx-edit"></i></button>`;
        newRow.appendChild(modifierCell);

        const supprimerCell = document.createElement('td');
        supprimerCell.innerHTML = `<button type="button" class="btn btn-sm btn-danger btn-premium" onclick="supprimerEcriture(this.closest('tr'));" style="border-radius: 10px;" title="Supprimer cette ligne"><i class="bx bx-trash"></i></button>`;
        newRow.appendChild(supprimerCell);
    }

    // ... (rest of local/draft logic can be kept or minimized if needed)

    function enregistrerEcritures() {
        const { isBalanced } = updateTotals();
        if (!isBalanced) {
            showAlert('danger', 'Les totaux débit et crédit ne sont pas équilibrés');
            return;
        }
        
        const tbody = document.querySelector('#tableEcritures tbody');
        if (!tbody || tbody.rows.length === 0) {
            showAlert('danger', 'Aucune écriture à enregistrer.');
            return;
        }

        // Récupérer les champs communs du formulaire
        const formData = new FormData(document.getElementById('formEcriture'));
        const nSaisie = document.getElementById('n_saisie').value;
        const codeJournalId = formData.get('code_journal_id');
        const dateCommune = document.getElementById('date').value;
        const descriptionCommune = document.getElementById('description_operation').value;
        const referencePieceCommune = document.getElementById('reference_piece').value;
        const urlParams = new URLSearchParams(window.location.search);
        const batchId = urlParams.get('batch_id');
        const nSaisieParam = urlParams.get('n_saisie');
        
        // Récupérer le fichier ou le nom du fichier du brouillon
        const pieceFile = document.getElementById('piece_justificatif').files[0];
        const draftPieceFilename = document.getElementById('draft_piece_filename')?.value || '';
        
        // Construction du payload correct pour le contrôleur
        const ecritures = [];
        const pieceFileName = pieceFile ? pieceFile.name : draftPieceFilename;
        
        Array.from(tbody.rows).forEach(row => {
            const cells = row.cells;
            const debit = parseFloat(cells[7].textContent.replace(/\s/g, '').replace(',', '.')) || 0;
            const credit = parseFloat(cells[8].textContent.replace(/\s/g, '').replace(',', '.')) || 0;
            
            // Récupérer l'ID du compte de trésorerie depuis l'attribut de la ligne
            const compteTresorerieId = row.getAttribute('data-compte-tresorerie-id');
            
            ecritures.push({
                date: dateCommune, // Utiliser la date commune
                n_saisie: nSaisie,
                description_operation: descriptionCommune, // Utiliser la description commune
                reference_piece: referencePieceCommune, // Utiliser la référence pièce commune
                plan_comptable_id: cells[5].getAttribute('data-plan-comptable-id'),
                plan_tiers_id: cells[6].getAttribute('data-tiers-id') || null,
                code_journal_id: codeJournalId,
                debit: debit,
                credit: credit,
                piece_justificatif: pieceFileName, // Utiliser le nom du fichier commun
                plan_analytique: cells[10].textContent.trim() === 'Oui' ? 1 : 0,
                id_exercice: formData.get('id_exercice'),
                journaux_saisis_id: formData.get('journaux_saisis_id'),
                compte_tresorerie_id: compteTresorerieId || null // Ajout de l'ID du compte de trésorerie
            });
        });

        const btnEnregistrer = document.getElementById('btnEnregistrer');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');
        
        btnText.textContent = 'VALIDATION EN COURS...';
        btnEnregistrer.disabled = true;
        btnSpinner.classList.remove('d-none');

        // Si on est en mode modification (n_saisie param présent), supprimer d'abord les anciennes écritures
        if (nSaisieParam) {
            fetch(`/ecriture-delete-by-saisie/${nSaisieParam}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(deleteResult => {
                if (deleteResult.success) {
                    // Ensuite, créer les nouvelles écritures
                    creerNouvellesEcritures(ecritures, pieceFile, batchId);
                } else {
                    showAlert('danger', 'Erreur lors de la suppression des anciennes écritures: ' + (deleteResult.message || 'Erreur inconnue'));
                }
            })
            .catch(error => {
                console.error('Erreur lors de la suppression:', error);
                showAlert('danger', 'Une erreur est survenue lors de la mise à jour.');
            })
            .finally(() => {
                btnText.textContent = 'VALIDER & ENREGISTRER';
                btnEnregistrer.disabled = false;
                btnSpinner.classList.add('d-none');
            });
        } else {
            // Mode création : créer directement les écritures
            creerNouvellesEcritures(ecritures, pieceFile, batchId);
        }
    }

    function creerNouvellesEcritures(ecritures, pieceFile, batchId) {
        // Créer FormData pour l'envoi avec fichier
        const formDataToSend = new FormData();
        formDataToSend.append('ecritures', JSON.stringify(ecritures));
        if (pieceFile) {
            formDataToSend.append('piece_justificatif', pieceFile);
        }
        if (batchId) {
            formDataToSend.append('batch_id', batchId);
        }

        fetch('/api/ecritures/multiple', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formDataToSend // Envoyer FormData au lieu de JSON
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            if (status >= 400) {
                throw new Error(body?.message || JSON.stringify(body?.errors) || 'Erreur serveur');
            }
            if (body?.success) {
                const urlParams = new URLSearchParams(window.location.search);
                const nSaisieParam = urlParams.get('n_saisie');
                
                if (nSaisieParam) {
                    showAlert('success', 'Écriture mise à jour avec succès !');
                } else {
                    showAlert('success', 'Écritures enregistrées avec succès !');
                }
                
                // Mettre à jour les boutons "Voir" avec le nom de fichier sauvegardé
                if (body.piece_filename) {
                    const tbody = document.querySelector('#tableEcritures tbody');
                    const rows = tbody.querySelectorAll('tr');
                    rows.forEach(row => {
                        const pieceCell = row.cells[9]; // Cellule pièce justificative
                        if (pieceCell && pieceCell.innerHTML.includes('voirPieceJustificativeLocale')) {
                            pieceCell.innerHTML = `<button class="btn btn-sm btn-primary-premium btn-premium" onclick="voirPieceJustificative('${body.piece_filename}')" style="border-radius: 10px;">
                                <i class="bx bx-eye me-1"></i>Voir
                            </button>`;
                            pieceCell.setAttribute('data-piece-filename', body.piece_filename);
                        }
                    });
                }
                
                tbody.innerHTML = '';
                updateTotals();
                fetchNextSaisieNumber(); // Rafraîchir le numéro depuis le serveur
                viderFormulaireComplet(); // Vider complètement le formulaire après succès
                
                // Si on était en mode modification, rediriger vers la liste des écritures
                if (nSaisieParam) {
                    setTimeout(() => {
                        window.location.href = '/accounting_entry_list';
                    }, 1500);
                }
            } else {
                throw new Error(body?.message || 'Erreur inconnue');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showAlert('danger', 'Erreur lors de l\'enregistrement: ' + error.message);
        })
        .finally(() => {
            const btnEnregistrer = document.getElementById('btnEnregistrer');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            btnText.textContent = 'VALIDER & ENREGISTRER';
            btnEnregistrer.disabled = false;
            btnSpinner.classList.add('d-none');
        });
    }

    // Fonction pour ajouter une ligne d'écriture au tableau
    function ajouterLigneEcriture(ligne = {}) {
        const tbody = document.querySelector('#tableEcritures tbody');
        if (!tbody) return;

        const tr = document.createElement('tr');
        
        // Formater les valeurs par défaut
        const date = ligne.date || document.getElementById('date').value;
        const piece = ligne.piece || document.getElementById('piece').value || '';
        const journal = ligne.journal || document.getElementById('journal').value || '';
        const compte = ligne.compte || '';
        const libelle = ligne.libelle || '';
        const tiers = ligne.tiers || '';
        const debit = ligne.debit ? parseFloat(ligne.debit.replace(/\s/g, '').replace(',', '.')) : 0;
        const credit = ligne.credit ? parseFloat(ligne.credit.replace(/\s/g, '').replace(',', '.')) : 0;
        const analytique = ligne.analytique === 'Oui';
        
        tr.innerHTML = `
            <td>${date}</td>
            <td>${piece}</td>
            <td>${journal}</td>
            <td>${compte}</td>
            <td>${libelle}</td>
            <td>${tiers}</td>
            <td class="text-end">${debit.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ' ')}</td>
            <td class="text-end">${credit.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ' ')}</td>
            <td class="text-center"><input type="checkbox" ${analytique ? 'checked' : ''}></td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-icon btn-label-warning" onclick="modifierEcriture(this.closest('tr'))">
                    <i class="fas fa-edit"></i>
                </button>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-icon btn-label-danger" onclick="supprimerLigne(this)">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(tr);
        updateTotals();
        return tr;
    }
    
    // Fonction pour sauvegarder le brouillon dans le backend
    async function sauvegarderBrouillon() {
        const tbody = document.querySelector('#tableEcritures tbody');
        if (!tbody || tbody.rows.length === 0) {
            showAlert('warning', 'Aucune ligne à enregistrer en brouillon.');
            return;
        }
        
        const btnBrouillon = document.getElementById('btnBrouillon');
        const originalContent = btnBrouillon.innerHTML;
        btnBrouillon.disabled = true;
        btnBrouillon.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>EN COURS...';

        const form = document.getElementById('formEcriture');
        const formData = new FormData(form);
        const pieceFile = document.getElementById('piece_justificatif').files[0];
        
        const ecritures = [];
        Array.from(tbody.rows).forEach(row => {
            const cells = row.cells;
            const debit = parseFloat(cells[7].textContent.replace(/\s/g, '').replace(',', '.')) || 0;
            const credit = parseFloat(cells[8].textContent.replace(/\s/g, '').replace(',', '.')) || 0;
            const compteTresorerieId = row.getAttribute('data-compte-tresorerie-id');
            
            ecritures.push({
                date: cells[0].textContent.trim(),
                n_saisie: cells[1].textContent.trim(),
                description_operation: cells[3].textContent.trim(),
                reference_piece: cells[4].textContent.trim(),
                plan_comptable_id: cells[5].getAttribute('data-plan-comptable-id'),
                plan_tiers_id: cells[6].getAttribute('data-tiers-id') || null,
                debit: debit,
                credit: credit,
                plan_analytique: cells[10].textContent.trim() === 'Oui' ? 1 : 0,
                exercices_comptables_id: formData.get('id_exercice'),
                code_journal_id: formData.get('code_journal_id'),
                journaux_saisis_id: formData.get('journaux_saisis_id'),
                compte_tresorerie_id: compteTresorerieId || null,
                source: 'manuel'
            });
        });

        const formDataToSend = new FormData();
        formDataToSend.append('ecritures', JSON.stringify(ecritures));
        formDataToSend.append('source', 'manuel');
        if (pieceFile) {
            formDataToSend.append('piece_justificatif', pieceFile);
        }

        try {
            const res = await fetch("{{ route('api.brouillons.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formDataToSend
            });
            
            const json = await res.json();
            if (json.success) {
                showAlert('success', 'Brouillon sauvegardé avec succès !');
                window.location.href = "{{ route('brouillons.index') }}";
            } else {
                throw new Error(json.error || json.message || 'Erreur lors de la sauvegarde');
            }
        } catch (e) {
            showAlert('danger', 'Erreur: ' + e.message);
        } finally {
            btnBrouillon.disabled = false;
            btnBrouillon.innerHTML = originalContent;
        }
    }
    
    // Fonction pour effacer le brouillon
    function effacerBrouillon() {
        if (confirm('Voulez-vous vraiment effacer le brouillon en cours ?')) {
            localStorage.removeItem('brouillon_ecritures');
            updateBrouillonIndicator(false);
            showAlert('info', 'Brouillon effacé avec succès');
        }
    }
    
    // Fonction pour mettre à jour les totaux
    function updateTotals() {
        const tbody = document.querySelector('#tableEcritures tbody');
        if (!tbody) return;

        let totalDebit = 0;
        let totalCredit = 0;

        const rows = tbody.getElementsByTagName('tr');
        for (let row of rows) {
            const debitCell = row.cells[7]; // Colonne Débit
            const creditCell = row.cells[8]; // Colonne Crédit

            if (debitCell && debitCell.textContent) {
                totalDebit += parseFloat(debitCell.textContent.replace(/\s/g, '').replace(',', '.') || 0);
            }
            if (creditCell && creditCell.textContent) {
                totalCredit += parseFloat(creditCell.textContent.replace(/\s/g, '').replace(',', '.') || 0);
            }
        }

        const totalDebitElement = document.getElementById('totalDebit');
        const totalCreditElement = document.getElementById('totalCredit');
        const saveButton = document.getElementById('btnEnregistrer');
        const totalRow = document.querySelector('tfoot tr');
        
        // Formater et afficher les totaux
        const formattedDebit = totalDebit.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        const formattedCredit = totalCredit.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        
        if (totalDebitElement) totalDebitElement.textContent = formattedDebit;
        if (totalCreditElement) totalCreditElement.textContent = formattedCredit;
        
        // Vérifier l'équilibre avec une tolérance pour les arrondis
        const isBalanced = Math.abs(totalDebit - totalCredit) < 0.01;
        
        // Mettre à jour l'état du bouton d'enregistrement
        if (saveButton) {
            saveButton.disabled = !isBalanced || rows.length === 0;
            saveButton.title = isBalanced 
                ? 'Enregistrer les écritures' 
                : 'Les totaux débit et crédit doivent être égaux';
        }
        
        // Mettre à jour l'indicateur de balance
        const balanceIndicator = document.getElementById('balanceIndicator');
        if (balanceIndicator) {
            if (isBalanced && rows.length > 0) {
                balanceIndicator.innerHTML = '<i class="bx bx-check-circle text-success fs-3"></i>';
            } else if (rows.length === 0) {
                balanceIndicator.innerHTML = '<i class="bx bx-minus-circle text-muted fs-3"></i>';
            } else {
                balanceIndicator.innerHTML = '<i class="bx bx-error-circle text-danger fs-3"></i>';
            }
        }
        
        // Mettre en évidence la ligne des totaux si non équilibrée
        if (totalRow) {
            if (!isBalanced) {
                totalRow.classList.add('table-warning');
            } else {
                totalRow.classList.remove('table-warning');
            }
        }
        
        return { totalDebit, totalCredit, isBalanced };
    }

    // Fonction pour afficher des alertes stylisées
    function showAlert(type, message) {
        // Supprimer les alertes existantes
        const existingAlerts = document.querySelectorAll('.custom-alert');
        existingAlerts.forEach(alert => alert.remove());

        // Créer l'élément d'alerte
        const alertDiv = document.createElement('div');
        alertDiv.className = `custom-alert alert alert-${type} alert-dismissible fade show`;
        alertDiv.role = 'alert';
        
        // Ajouter le contenu de l'alerte
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        // Positionner l'alerte en haut à droite
        alertDiv.style.position = 'fixed';
        alertDiv.style.top = '20px';
        alertDiv.style.right = '20px';
        alertDiv.style.zIndex = '9999';
        alertDiv.style.minWidth = '300px';

        // Ajouter l'alerte au body
        document.body.appendChild(alertDiv);

        // Supprimer automatiquement après 5 secondes
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }


    // Fonction pour supprimer une ligne du tableau
    function supprimerLigne(button) {
        if (confirm('Voulez-vous vraiment supprimer cette ligne ?')) {
            const row = button.closest('tr');
            if (row) {
                row.remove();
                updateTotals();
                showAlert('success', 'Ligne supprimée avec succès');
            }
        }
    }
    
    // Fonction pour modifier une écriture
    // Fonction pour charger le compte de trésorerie en fonction du journal sélectionné
    function chargerCompteTresorerie(journalId) {
        if (!journalId || journalId === 'N/A' || journalId === 'null') return;
        
        fetch(`/api/journal/compte-treso/${journalId}`)
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    const selectTreso = document.getElementById('compte_tresorerie');
                    if (selectTreso) {
                        selectTreso.value = data.compte.id;
                        if (typeof $(selectTreso).select2 === 'function') {
                            $(selectTreso).trigger('change'); // Si Select2 est utilisé
                        }
                    }
                }
            })
            .catch(error => console.error('Erreur lors du chargement du compte de trésorerie:', error));
    }

    function modifierEcriture(row) {
        // Récupérer les données de la ligne
        const cells = row.cells;
        
        // Extraire les données de chaque cellule
        const date = cells[0].textContent.trim();
        const nSaisie = cells[1].textContent.trim();
        const journal = cells[2].textContent.trim();
        const libelle = cells[3].textContent.trim();
        const referencePiece = cells[4].textContent.trim();
        
        // Pour le compte général, lire depuis l'attribut data-plan-comptable-id et le texte
        const compteGeneralCell = cells[5];
        const compteGeneral = compteGeneralCell.textContent.trim();
        const compteGeneralId = compteGeneralCell.getAttribute('data-plan-comptable-id');
        
        // Pour le compte tiers, lire depuis l'attribut data-tiers-id et le texte
        const compteTiersCell = cells[6];
        const compteTiers = compteTiersCell.textContent.trim();
        const compteTiersId = compteTiersCell.getAttribute('data-tiers-id');
        
        const debit = cells[7].textContent.trim();
        const credit = cells[8].textContent.trim();
        
        // Pour la case analytique, elle est dans la cellule 9 (index 9)
        let analytique = false;
        if (cells[9]) {
            const checkbox = cells[9].querySelector('input[type="checkbox"]');
            if (checkbox) {
                analytique = checkbox.checked;
            } else {
                // Si pas de checkbox, vérifier si le texte contient "Oui"
                const text = cells[9].textContent.trim();
                analytique = text === 'Oui' || text === 'true' || text === '1';
            }
        }
        
        // Mettre à jour le formulaire principal avec les valeurs de la ligne
        document.getElementById('date').value = date;
        document.getElementById('n_saisie').value = nSaisie;
        document.getElementById('description_operation').value = libelle;
        document.getElementById('reference_piece').value = referencePiece;
        
        // Pour le journal, chercher dans le selecteur
        const journalSelect = document.getElementById('code_journal_affiche');
        if (journalSelect && journalSelect.options) {
            // Chercher l'option qui correspond au texte du journal
            const options = journalSelect.options;
            for (let i = 0; i < options.length; i++) {
                if (options[i].text.includes(journal)) {
                    journalSelect.value = options[i].value;
                    // Déclencher le changement pour mettre à jour le champ caché
                    journalSelect.dispatchEvent(new Event('change'));
                    break;
                }
            }
        }
        
        // Pour le compte général, utiliser l'ID si disponible, sinon le texte
        if (compteGeneralId) {
            const compteGeneralSelect = document.getElementById('compte_general');
            if (compteGeneralSelect) {
                compteGeneralSelect.value = compteGeneralId;
                // Déclencher le changement pour mettre à jour les champs
                compteGeneralSelect.dispatchEvent(new Event('change'));
            }
        } else {
            // Fallback avec le champ de recherche
            const compteGeneralInput = document.getElementById('compteGeneralSearch');
            if (compteGeneralInput && compteGeneral !== '-') {
                compteGeneralInput.value = compteGeneral;
                setTimeout(() => {
                    compteGeneralInput.dispatchEvent(new Event('input'));
                }, 100);
            }
        }
        
        // Pour le compte tiers, utiliser l'ID si disponible
        if (compteTiersId) {
            const compteTiersSelect = document.getElementById('compte_tiers');
            if (compteTiersSelect) {
                compteTiersSelect.value = compteTiersId;
                // Déclencher le changement
                compteTiersSelect.dispatchEvent(new Event('change'));
            }
        } else if (compteTiers !== '-') {
            // Fallback avec le texte
            const compteTiersSelect = document.getElementById('compte_tiers');
            if (compteTiersSelect && compteTiersSelect.options) {
                const options = compteTiersSelect.options;
                for (let i = 0; i < options.length; i++) {
                    if (options[i].text.includes(compteTiers)) {
                        compteTiersSelect.value = options[i].value;
                        break;
                    }
                }
            }
        }
        
        // Mettre à jour les montants dans les champs temporaires
        const debitInput = document.getElementById('debit');
        const creditInput = document.getElementById('credit');
        if (debitInput) debitInput.value = debit.replace(/\s/g, '').replace(',', '.');
        if (creditInput) creditInput.value = credit.replace(/\s/g, '').replace(',', '.');
        
        // Cocher la case analytique si nécessaire
        const analytiqueCheckbox = document.getElementById('plan_analytique');
        if (analytiqueCheckbox) analytiqueCheckbox.checked = analytique;
        
        // Supprimer la ligne modifiée du tableau
        row.remove();
        updateTotals();
        
        // Mettre le focus sur le premier champ
        document.getElementById('description_operation').focus();
        
        // Afficher un message d'aide
        showAlert('info', 'Ligne chargée dans le formulaire. Modifiez si nécessaire et cliquez sur "Ajouter une ligne".');
    }

    // Fonction pour supprimer une écriture
    function supprimerEcriture(row) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette écriture ?')) {
            row.remove();
            updateTotals();
            showAlert('success', 'Écriture supprimée avec succès !');
        }
    }

    // Gestionnaire d'événements pour le changement de type de tiers
</script>
