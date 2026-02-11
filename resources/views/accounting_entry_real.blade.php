<!doctype html>

<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/"
  data-template="vertical-menu-template-free" data-bs-theme="light">
  <head>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
      /* --- PLAN TIERS PREMIUM MODAL STYLES --- */
      .premium-modal-content-tiers {
          background: rgba(255, 255, 255, 0.98) !important;
          backdrop-filter: blur(15px) !important;
          border: 1px solid rgba(255, 255, 255, 1) !important;
          border-radius: 20px !important;
          box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.1) !important;
          font-family: 'Plus Jakarta Sans', sans-serif !important;
          padding: 1.5rem !important;
      }
      .text-blue-gradient-premium {
          background: linear-gradient(to right, #1e40af, #3b82f6);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
          font-weight: 800;
      }
      .input-field-premium {
          transition: all 0.2s ease;
          border: 2px solid #f1f5f9 !important;
          background-color: #f8fafc !important;
          border-radius: 16px !important;
          padding: 0.75rem 1rem !important;
          font-size: 0.8rem !important;
          font-weight: 600 !important;
          color: #0f172a !important;
          width: 100%;
      }
      .input-field-premium:focus {
          border-color: #1e40af !important;
          background-color: #ffffff !important;
          box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.05) !important;
          outline: none !important;
      }
      .input-label-premium {
          font-size: 0.7rem !important;
          font-weight: 800 !important;
          color: #64748b !important;
          text-transform: uppercase !important;
          letter-spacing: 0.05em !important;
          margin-bottom: 0.5rem !important;
          display: block;
      }
      .btn-save-premium {
          background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
          color: white;
          border: none;
          border-radius: 16px;
          padding: 0.75rem 1.5rem;
          font-weight: 700;
          transition: all 0.3s ease;
          box-shadow: 0 10px 15px -3px rgba(30, 64, 175, 0.3);
      }
      .btn-save-premium:hover {
          transform: translateY(-2px);
          box-shadow: 0 15px 20px -3px rgba(30, 64, 175, 0.4);
      }
      .btn-cancel-premium {
          background: #f1f5f9;
          color: #64748b;
          border: none;
          border-radius: 16px;
          padding: 0.75rem 1.5rem;
          font-weight: 700;
          transition: all 0.3s ease;
      }
      .btn-cancel-premium:hover {
          background: #e2e8f0;
          color: #475569;
      }

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

    /* Fix Select2 Premium Styling for Treasury */
    .select2-container--default .select2-selection--single {
        border: 2px solid #e2e8f0 !important;
        border-radius: 15px !important;
        min-height: 50px !important;
        height: auto !important;
        padding: 0.5rem 0.5rem !important;
        background-color: #ffffff !important;
        transition: all 0.3s ease !important;
        display: flex !important;
        align-items: center !important;
        flex-wrap: wrap !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #475569 !important;
        font-weight: 500 !important;
        font-size: 1rem !important;
        padding-left: 0.7rem !important;
        display: flex !important;
        align-items: center !important;
        flex-wrap: wrap !important;
        gap: 5px;
        text-overflow: unset !important;
        white-space: normal !important;
        overflow: visible !important;
        line-height: 1.4 !important;
        padding-top: 5px !important;
        padding-bottom: 5px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 100% !important;
        right: 10px !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default.select2-container--disabled .select2-selection--single {
        background-color: #f8f9fa !important;
        border-color: #e2e8f0 !important;
        cursor: not-allowed !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #94a3b8 !important;
        box-shadow: 0 0 0 0.25rem rgba(148, 163, 184, 0.1) !important;
    }
    /* Results highlight - No Blue */
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #f1f5f9 !important;
        color: #1e293b !important;
    }
    /* Selection background - No Blue */
    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: #e2e8f0 !important;
    }
    /* Neutral Dropdown category backround (No Blue) */
    .treasury-category-badge {
        background-color: #f1f5f9 !important; 
        color: #475569 !important; 
        border: 1px solid #e2e8f0 !important;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.85em;
        font-weight: 600;
        display: inline-block;
        white-space: nowrap;
    }

    /* Table Premium - Refonte Conteneur */
    .table-container-premium {
        background: #ffffff !important;
        border-radius: 24px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05) !important;
        overflow: hidden !important;
        margin-bottom: 2rem !important;
    }

    .table-responsive-premium {
        overflow-x: auto !important;
    }

    #tableEcritures {
        margin: 0 !important;
        width: 100% !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    #tableEcritures thead {
        background-color: #f8fafc !important;
        border-bottom: 2px solid #e2e8f0 !important;
    }

    #tableEcritures th {
        color: #475569 !important;
        text-transform: uppercase !important;
        font-size: 0.7rem !important;
        font-weight: 800 !important;
        letter-spacing: 0.05em !important;
        padding: 1.25rem 1rem !important;
        border: none !important;
        background-color: #f8fafc !important;
        white-space: nowrap !important;
    }

    #tableEcritures td {
        padding: 1rem !important;
        font-size: 0.85rem !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f5f9 !important;
        color: #1e293b !important;
        font-weight: 500 !important;
    }

    #tableEcritures tbody tr:hover {
        background-color: #fcfdfe !important;
    }

    .table-badge {
        padding: 0.35rem 0.6rem !important;
        border-radius: 8px !important;
        font-weight: 700 !important;
        font-size: 0.7rem !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.3rem !important;
        white-space: nowrap !important;
    }

    .badge-journal { background-color: #eff6ff !important; color: #1e40af !important; border: 1px solid #dbeafe !important; }
    .badge-compte { background-color: #f8fafc !important; color: #475569 !important; border: 1px solid #e2e8f0 !important; }
    .amount-debit { color: #dc2626 !important; font-weight: 700 !important; text-align: right !important; }
    .amount-credit { color: #059669 !important; font-weight: 700 !important; text-align: right !important; }
    
    .poste-badge-text {
        font-size: 0.65rem !important;
        padding: 0.3rem 0.5rem !important;
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
        border-radius: 16px !important;
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
    
    /* Labels et ContrÃ´les Premium */
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

    /* Table Premium - Refonte Conteneur */
    .table-container-premium {
        background: #ffffff !important;
        border-radius: 24px !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05) !important;
        overflow: hidden !important;
        margin-bottom: 2rem !important;
    }

    .table-responsive-premium {
        overflow-x: auto !important;
    }

    #tableEcritures {
        margin: 0 !important;
        width: 100% !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    #tableEcritures thead {
        background-color: #f8fafc !important;
        border-bottom: 2px solid #e2e8f0 !important;
    }

    #tableEcritures th {
        color: #475569 !important;
        text-transform: uppercase !important;
        font-size: 0.7rem !important;
        font-weight: 800 !important;
        letter-spacing: 0.05em !important;
        padding: 1.25rem 1rem !important;
        border: none !important;
        background-color: #f8fafc !important;
        white-space: nowrap !important;
    }

    #tableEcritures td {
        padding: 1rem !important;
        font-size: 0.85rem !important;
        vertical-align: middle !important;
        border-bottom: 1px solid #f1f5f9 !important;
        color: #1e293b !important;
        font-weight: 500 !important;
    }

    #tableEcritures tbody tr:hover {
        background-color: #fcfdfe !important;
    }

    .table-badge {
        padding: 0.35rem 0.6rem !important;
        border-radius: 8px !important;
        font-weight: 700 !important;
        font-size: 0.7rem !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 0.3rem !important;
        white-space: nowrap !important;
    }

    .badge-journal { background-color: #eff6ff !important; color: #1e40af !important; border: 1px solid #dbeafe !important; }
    .badge-compte { background-color: #f8fafc !important; color: #475569 !important; border: 1px solid #e2e8f0 !important; }
    .amount-debit { color: #dc2626 !important; font-weight: 700 !important; text-align: right !important; }
    .amount-credit { color: #059669 !important; font-weight: 700 !important; text-align: right !important; }
    
    .poste-badge-text {
        font-size: 0.65rem !important;
        padding: 0.3rem 0.5rem !important;
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
                                        @php
                                            // Déterminer la date par défaut et les limites
                                            $exoStart = $exerciceActif->date_debut ?? date('Y-01-01');
                                            $exoEnd = $exerciceActif->date_fin ?? date('Y-12-31');
                                            
                                            // Date par défaut : 
                                            // 1. Si mois/année donnés via le modal -> 1er du mois
                                            // 2. Sinon Aujourd'hui si dans l'intervalle
                                            // 3. Sinon Date de début de l'exercice
                                            
                                            $defaultDate = date('Y-m-d');
                                            
                                            if (isset($data['annee']) && isset($data['mois'])) {
                                                try {
                                                    $defaultDate = \Carbon\Carbon::createFromDate($data['annee'], $data['mois'], 1)->format('Y-m-d');
                                                } catch (\Exception $e) {}
                                            } elseif ($defaultDate < $exoStart || $defaultDate > $exoEnd) {
                                                $defaultDate = $exoStart;
                                            }
                                        @endphp
                                        <div class="form-control d-flex align-items-center" style="border-radius: 12px; border: 2px solid #e2e8f0; background-color: #ffffff; padding: 0.25rem 0.75rem;">
                                            @php
                                                $currentDay = date('d', strtotime($defaultDate));
                                                $monthYear = date('/m/Y', strtotime($defaultDate));
                                            @endphp
                                            <select id="day_select" class="border-0 bg-transparent p-0" style="width: 25px; font-weight: bold; color: #1e293b; outline: none; appearance: none; -moz-appearance: none; -webkit-appearance: none; cursor: pointer;">
                                                @for ($i = 1; $i <= 31; $i++)
                                                    @php $d = str_pad($i, 2, '0', STR_PAD_LEFT); @endphp
                                                    <option value="{{ $d }}" {{ $currentDay == $d ? 'selected' : '' }}>{{ $d }}</option>
                                                @endfor
                                            </select>
                                            <span id="month_year_display" style="color: #94a3b8; font-weight: 600; cursor: default; user-select: none;">{{ $monthYear }}</span>
                                            <input type="hidden" id="date" name="date" value="{{ $defaultDate }}" />
                                        </div>
                                        <div class="invalid-feedback">Veuillez renseigner une date valide dans l'exercice.</div>
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
                                    <div class="col-md-3">
                                        <label for="n_saisie_user" class="form-label">
                                            <i class="bx bx-hash"></i>N° de Saisie (original)
                                        </label>
                                        <input type="text" id="n_saisie_user" name="n_saisie_user" class="form-control" readonly value="" style="font-weight: bold; color: #64748b;" />
                                        <small class="form-text text-muted">Numéro d'origine importé (si disponible)</small>
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
                                                                    <div class="col-md-5" id="div_compte_tresorerie" style="display: none;">
                                        <label for="compte_tresorerie" class="form-label">
                                            <i class="bx bx-receipt"></i>Compte Trésorerie
                                        </label>
                                        <div class="d-flex gap-2 align-items-center">
                                            <select id="compte_tresorerie" name="compte_tresorerie" class="form-select select2" style="flex: 1;">
                                                <option value="" selected disabled>Sélectionner un compte...</option>
                                                @foreach($comptesTresorerie as $treso)
                                                    <option value="{{ $treso->id }}" data-category="{{ $treso->category->name ?? '' }}">{{ $treso->name }} - {{ $treso->category->name ?? 'Sans catégorie' }}</option>
                                                @endforeach
                                            </select>
                                        </div>
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
                                    <div class="col-md-4">
                                        <label for="compte_general" class="form-label">
                                            <i class="bx bx-folder-open"></i>Compte Général <span class="text-danger">*</span>
                                        </label>
                                        <div class="d-flex gap-2 align-items-center">
                                            <select id="compte_general" name="compte_general"
                                                class="form-select select2" style="flex: 1;" data-live-search="true"
                                                title="Sélectionner un compte général" required>
                                                <option value="" selected disabled>Sélectionner un compte</option>
                                                @if(isset($plansComptables))
                                                    @php
                                                        $classLabels = [
                                                            '1' => 'Classe 1 - Comptes de ressources durables',
                                                            '2' => 'Classe 2 - Comptes d\'actif immobilisé',
                                                            '3' => 'Classe 3 - Comptes de stocks',
                                                            '4' => 'Classe 4 - Comptes de tiers',
                                                            '5' => 'Classe 5 - Comptes de trésorerie',
                                                            '6' => 'Classe 6 - Comptes de charges',
                                                            '7' => 'Classe 7 - Comptes de produits',
                                                            '8' => 'Classe 8 - Comptes spéciaux',
                                                            '9' => 'Classe 9 - Comptes de coût'
                                                        ];
                                                        $groupedPlans = $plansComptables->groupBy(function($item) {
                                                            return substr($item->numero_de_compte, 0, 1);
                                                        });
                                                    @endphp
                                                    @foreach ($groupedPlans as $class => $accounts)
                                                        <optgroup label="{{ $classLabels[$class] ?? 'Classe ' . $class }}">
                                                            @foreach ($accounts as $plan)
                                                                <option value="{{ $plan->id }}"
                                                                    data-numero="{{ $plan->numero_de_compte }}"
                                                                    data-intitule_compte_general="{{ $plan->numero_de_compte }}">
                                                                    {{ $plan->numero_de_compte }} -
                                                                    {{ $plan->intitule }}
                                                                </option>
                                                            @endforeach
                                                        </optgroup>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <button type="button" class="btn btn-outline-secondary btn-premium" data-bs-toggle="modal" data-bs-target="#modalCenterCreate" title="Créer un nouveau compte" style="
                                                background: #ffffff;
                                                border: 2px solid #e2e8f0;
                                                color: #64748b;
                                                padding: 0.7rem 1rem;
                                                font-weight: 600;
                                                transition: all 0.3s ease;
                                                white-space: nowrap;
                                            " onmouseover="this.style.borderColor='#cbd5e1'; this.style.color='#1a202c';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#64748b';">
                                                <i class="bx bx-plus"></i> Créer
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="compte_tiers" class="form-label">
                                            <i class="bx bx-user"></i>Compte Tiers (Le cas échéant)
                                        </label>
                                        <div class="d-flex gap-2 align-items-center">
                                            <select id="compte_tiers" name="compte_tiers"
                                                class="form-select select2" style="flex: 1;" data-live-search="true"
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
                                            <button type="button" class="btn btn-outline-secondary btn-premium" data-bs-toggle="modal" data-bs-target="#createTiersModal" title="Créer un nouveau compte tiers" style="
                                                background: #ffffff;
                                                border: 2px solid #e2e8f0;
                                                color: #64748b;
                                                padding: 0.7rem 1rem;
                                                font-weight: 600;
                                                transition: all 0.3s ease;
                                                white-space: nowrap;
                                            " onmouseover="this.style.borderColor='#cbd5e1'; this.style.color='#1a202c';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#64748b';">
                                                <i class="bx bx-plus"></i> Créer
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-4" id="div_poste_tresorerie">
                                        <label for="poste_tresorerie" class="form-label">
                                            <i class="bx bx-receipt"></i>Poste Trésorerie
                                        </label>
                                        <div class="d-flex gap-2 align-items-center">
                                            <select id="poste_tresorerie" name="poste_tresorerie" class="form-select select2" style="flex: 1;" disabled>
                                                <option value="" selected disabled>Sélectionner un poste...</option>
                                                @foreach($comptesTresorerie as $treso)
                                                    <option value="{{ $treso->id }}" 
                                                        data-category="{{ $treso->category->name ?? '' }}"
                                                        data-syscohada-line-id="{{ $treso->syscohada_line_id ?? '' }}">
                                                        {{ $treso->name }} - {{ $treso->category->name ?? 'Sans catégorie' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <button type="button" id="btn_create_poste_entry" class="btn btn-outline-secondary btn-premium d-none" data-bs-toggle="modal" data-bs-target="#modalCreatePoste" title="Créer un nouveau poste de trésorerie" style="
                                                background: #ffffff;
                                                border: 2px solid #e2e8f0;
                                                color: #64748b;
                                                padding: 0.7rem 1rem;
                                                font-weight: 600;
                                                transition: all 0.3s ease;
                                                white-space: nowrap;
                                            " onmouseover="this.style.borderColor='#cbd5e1'; this.style.color='#1a202c';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.color='#64748b';">
                                                <i class="bx bx-plus"></i> Créer
                                            </button>
                                        </div>
                                        <div id="poste_mapping_indicator" class="mt-1" style="display: none;">
                                            <small class="text-blue-600 fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">
                                                <i class="bx bx-bolt-circle"></i> <span id="poste_mapping_text">Classification Automatique</span>
                                            </small>
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
                                            <option value="" disabled selected>Sélectionner...</option>
                                            <option value="1">Oui</option>
                                            <option value="0">Non</option>
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
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-primary-premium btn-premium w-100" onclick="ajouterEcriture()">
                                        <i class="bx bx-plus-circle me-2"></i>Ajouter à la ligne
                                    </button>
                                </div>
                            </div>

                            <div class="table-container-premium">
                                <div class="table-responsive-premium">
                                    <table class="table table-hover" id="tableEcritures">
                                        <thead>
                                            <tr>
                                                <th style="width: 100px;">DATE</th>
                                                <th style="width: 120px;">N° SAISIE</th>
                                                <th style="width: 100px;">JOURNAL</th>
                                                <th style="min-width: 250px;">LIBELLÉ OPÉRATION</th>
                                                <th style="width: 120px;">RÉF. PIÈCE</th>
                                                <th style="width: 150px;">CPTE GÉNÉRAL</th>
                                                <th style="width: 150px;">CPTE TIERS</th>
                                                <th style="width: 130px;" class="text-end">DÉBIT</th>
                                                <th style="width: 130px;" class="text-end">CRÉDIT</th>
                                                <th style="width: 180px;">POSTE TRÉSORERIE</th>
                                                <th style="width: 80px;" class="text-center">PIÈCE</th>
                                                <th style="width: 100px;" class="text-center">ANALYTIQUE</th>
                                                <th style="width: 120px;" class="text-center">ACTIONS</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tableEcrituresBody">
                                            <!-- Les lignes seront insérées ici dynamiquement -->
                                            <tr id="emptyStateRow">
                                                <td colspan="13" class="text-center py-5">
                                                    <div class="d-flex flex-column align-items-center opacity-50">
                                                        <i class="bx bx-receipt fs-1 mb-2"></i>
                                                        <p class="mb-0 fw-semibold">Aucune ligne d'écriture pour le moment</p>
                                                        <small>Commencez par ajouter une ligne ci-dessus</small>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
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

      <!-- Modal Creation Poste Trésorerie -->
      <div class="modal fade" id="modalCreatePoste" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
              <form id="createPosteForm" class="w-full">
                  <div class="modal-content premium-modal-content">
                      <div class="text-center mb-6 position-relative">
                          <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal" aria-label="Fermer"></button>
                          <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                              Nouveau <span class="text-blue-gradient-premium">Poste</span>
                          </h1>
                          <div class="h-1 w-8 bg-blue-700 mx-auto mt-2 rounded-full"></div>
                      </div>

                      <div class="space-y-4 px-4 pb-4">
                          <div>
                              <label class="input-label-premium">Nom du Poste *</label>
                              <input type="text" id="poste_name" name="name" class="input-field-premium" required placeholder="Ex: Ventes de marchandises">
                          </div>
                          <div>
                              <label class="input-label-premium">Catégorie *</label>
                              <select id="poste_category_id" name="category_id" class="input-field-premium" required>
                                  <option value="" disabled selected>-- Sélectionner --</option>
                                  @foreach($categories as $category)
                                      <option value="{{ $category->id }}">{{ $category->name }}</option>
                                  @endforeach
                              </select>
                          </div>
                      </div>

                      <div class="grid grid-cols-2 gap-4 pt-4 px-4 pb-4 row">
                          <div class="col-6">
                              <button type="button" class="btn-cancel-premium w-100" data-bs-dismiss="modal">Annuler</button>
                          </div>
                          <div class="col-6">
                              <button type="button" id="btnSavePoste" onclick="createPosteSimple(event)" class="btn-save-premium w-100">Enregistrer</button>
                          </div>
                      </div>
                  </div>
              </form>
          </div>
      </div>

      <!-- Modal Nouveau Tiers (Plan Tiers Style) -->
      <div class="modal fade" id="createTiersModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
              <form id="createTiersForm" class="w-full">
                  <div class="modal-content premium-modal-content-tiers">
                      
                      <!-- En-tête -->
                      <div class="text-center mb-6 position-relative">
                          <button type="button" class="btn-close position-absolute end-0 top-0" data-bs-dismiss="modal" aria-label="Fermer"></button>
                          <h1 class="text-xl font-extrabold tracking-tight text-slate-900" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                              Nouveau <span class="text-blue-gradient-premium">Tiers</span>
                          </h1>
                          <div class="h-1 w-8 bg-blue-700 mx-auto mt-2 rounded-full"></div>
                      </div>

                      <div class="space-y-4">
                          
                          <!-- Catégorie (Type de tiers) -->
                          <div class="mb-3">
                              <label class="input-label-premium">Catégorie</label>
                              <select id="type_tiers" name="type_de_tiers" class="input-field-premium" required>
                                  <option value="" disabled selected>Sélectionner une catégorie</option>
                                  <option value="Fournisseur" data-prefix="40">Fournisseur</option>
                                  <option value="Client" data-prefix="41">Client</option>
                                  <option value="Personnel" data-prefix="42">Personnel</option>
                                  <option value="CNPS" data-prefix="43">Organisme sociaux / CNPS</option>
                                  <option value="Impots" data-prefix="44">Impôt</option>
                                  <option value="Organisme international" data-prefix="45">Organisme international</option>
                                  <option value="Associé" data-prefix="46">Associé / Actionnaire</option>
                                  <option value="Divers Tiers" data-prefix="47">Divers Tiers</option>
                              </select>
                          </div>

                          <!-- Numéro de tiers -->
                          <div class="mb-3">
                              <label class="input-label-premium">Numéro de tiers</label>
                              <input type="text" id="numero_tiers" name="numero_de_tiers" 
                                  class="input-field-premium opacity-75" placeholder="Généré automatiquement" required readonly>
                          </div>

                          <!-- Compte de Rattachement (Compte général associé) -->
                          <div class="mb-3">
                              <label class="input-label-premium">Compte de Rattachement</label>
                              <div class="d-flex gap-2">
                                  <select id="compte_general_tiers" name="compte_general" class="input-field-premium form-select" style="flex: 1;">
                                      <option value="" disabled selected>-- Sélectionnez un compte --</option>
                                  </select>
                                  <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center" type="button" onclick="window.showAllAccountsTiers()" title="Afficher tous les comptes de classe 4" style="border-radius: 12px; border: 2px solid #e2e8f0; background-color: #fff; width: 50px; flex-shrink: 0;">
                                      <i class="bx bx-show fs-4"></i>
                                  </button>
                              </div>
                          </div>

                          <!-- Nom / Raison Sociale (Intitulé) -->
                          <div class="mb-3">
                              <label class="input-label-premium">Nom / Raison Sociale</label>
                              <input type="text" id="intitule_tiers" name="intitule" 
                                  class="input-field-premium" placeholder="Entrez le nom de l'entité" required>
                          </div>

                      </div>

                      <!-- Actions -->
                      <div class="row g-3 mt-4">
                          <div class="col-6">
                              <button type="button" class="btn-cancel-premium w-100" data-bs-dismiss="modal">
                                  Annuler
                              </button>
                          </div>
                          <div class="col-6">
                              <button type="button" id="btnCreateTiers" onclick="window.createTiersSimple(event)" class="btn-save-premium w-100">
                                  Enregistrer
                              </button>
                          </div>
                      </div>

                  </div>
              </form>
          </div>
      </div>

      <!-- Modal Creation Compte Général -->
      <div class="modal fade" id="modalCenterCreate" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" style="max-width: 450px;">
              <form action="{{ route('plan_comptable.store') }}" method="POST" id="planComptableForm" class="w-full">
                  @csrf
                  <div class="modal-content premium-modal-content" style="border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                      <div class="text-center mb-4 position-relative" style="padding: 1.5rem 1.5rem 0;">
                          <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal" aria-label="Fermer"></button>
                          <h1 class="text-xl font-extrabold tracking-tight text-slate-900" style="font-size: 1.5rem; font-weight: 800; margin-bottom: 0.5rem;">
                              Nouveau <span class="text-blue-gradient-premium">Compte</span>
                          </h1>
                          <div class="h-1 w-8 bg-blue-700 mx-auto rounded-full" style="height: 4px; width: 32px;"></div>
                      </div>
                      <div class="modal-body" style="padding: 0 2rem 2rem;">
                          <div class="space-y-4">
                              <div class="mb-3">
                                  <label for="numero_de_compte" class="input-label-premium" style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 0.5rem; display: block;">Numéro de compte</label>
                                  <input type="text" class="input-field-premium" id="numero_de_compte" name="numero_de_compte" 
                                      maxlength="8" placeholder="Ex: 41110000" required style="width: 100%; padding: 0.75rem 1rem; border: 2px solid #e2e8f0; border-radius: 12px;">
                              </div>
                              <div class="mb-3">
                                  <label for="intitule" class="input-label-premium" style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 0.5rem; display: block;">Intitulé du compte</label>
                                  <input type="text" class="input-field-premium" id="intitule" name="intitule" 
                                      placeholder="Entrez l'intitulé du compte" required style="width: 100%; padding: 0.75rem 1rem; border: 2px solid #e2e8f0; border-radius: 12px;">
                              </div>
                          </div>
                          <div class="row g-3 mt-2">
                              <div class="col-6">
                                  <button type="button" class="btn btn-light w-100" data-bs-dismiss="modal" style="padding: 0.75rem; border-radius: 12px; font-weight: 700; color: #64748b;">Annuler</button>
                              </div>
                              <div class="col-6">
                                  <button type="submit" class="btn btn-primary-premium w-100" style="padding: 0.75rem; border-radius: 12px; font-weight: 700; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); border: none; color: white;">Enregistrer</button>
                              </div>
                          </div>
                      </div>
                  </div>
              </form>
          </div>
      </div>


      @include('components.footer')


    </body>

    </html>

<script>
// Helper function for text truncation
const compasTextShortcut = (txt) => (txt && txt.length > 30) ? txt.substring(0, 27) + '...' : (txt || '');

// Logic for Creating Tiers and Modal Management
document.addEventListener('DOMContentLoaded', function() {
    // --- DATE HYBRIDE SYNCHRONIZATION ---
    const daySelect = document.getElementById('day_select');
    const dateHidden = document.getElementById('date');
    const monthYearDisplay = document.getElementById('month_year_display');

    if (daySelect && dateHidden) {
        daySelect.addEventListener('change', function() {
            const day = this.value;
            const currentDate = dateHidden.value; // YYYY-MM-DD
            const parts = currentDate.split('-');
            if (parts.length === 3) {
                const newDate = `${parts[0]}-${parts[1]}-${day}`;
                dateHidden.value = newDate;
                console.log('Date updated to:', newDate);
            }
        });
    }

    // Fonction globale pour mettre Ã  jour la date (utilisée lors des chargements de brouillons)
    window.updateHybridDate = function(fullDate) {
        if (!fullDate) return;
        const parts = fullDate.split('-');
        if (parts.length === 3) {
            const day = parts[2];
            const month = parts[1];
            const year = parts[0];
            
            if (daySelect) daySelect.value = day;
            if (monthYearDisplay) {
                if (monthYearDisplay.tagName === 'SPAN') {
                    monthYearDisplay.textContent = `/${month}/${year}`;
                } else {
                    monthYearDisplay.value = `/${month}/${year}`;
                }
            }
            if (dateHidden) dateHidden.value = fullDate;
        }
    };

    // --- GESTION DES TIERS (REFONTE ROBUSTE) ---
    const posteTresorSelect = document.getElementById('poste_tresorerie');
    const mappingIndicator = document.getElementById('poste_mapping_indicator');
    const mappingText = document.getElementById('poste_mapping_text');

    if (posteTresorSelect) {
        $(posteTresorSelect).on('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (!selectedOption || !this.value) {
                if (mappingIndicator) mappingIndicator.style.display = 'none';
                return;
            }

            const syscId = selectedOption.getAttribute('data-syscohada-line-id');
            if (mappingIndicator && mappingText) {
                mappingIndicator.style.display = 'block';
                if (syscId) {
                    const label = syscId.startsWith('INV') ? 'Investissement' : 'Financement';
                    mappingText.innerHTML = `Classification configurée (${label})`;
                    mappingText.parentElement.classList.replace('text-blue-600', 'text-success');
                } else {
                    mappingText.innerHTML = `Classification Automatique`;
                    mappingText.parentElement.classList.replace('text-success', 'text-blue-600');
                }
            }
        });
    }

    const createTiersModalEl = document.getElementById('createTiersModal');
    if (createTiersModalEl) {
        const tiersModal = new bootstrap.Modal(createTiersModalEl);
        const typeTiersSelect = document.getElementById('type_tiers');
        const compteGeneralTiers = document.getElementById('compte_general_tiers');
        const numeroTiersInput = document.getElementById('numero_tiers');
        const intituleTiersInput = document.getElementById('intitule_tiers');
        const btnCreateTiers = document.getElementById('btnCreateTiers');

        // Reset au chargement
        createTiersModalEl.addEventListener('show.bs.modal', function () {
            document.getElementById('createTiersForm').reset();
            compteGeneralTiers.innerHTML = '<option value="" selected disabled>Choisir le type...</option>';
            numeroTiersInput.value = '';
        });

        // Changement de type -> Filtre comptes & Génération numéro
        if (typeTiersSelect) {
            typeTiersSelect.addEventListener('change', function() {
                const type = this.value;
                numeroTiersInput.value = '';
                numeroTiersInput.placeholder = 'Calcul...';
                
                // Prefixes standard SYSCOHADA
                const prefixes = {
                    'Fournisseur': '40',
                    'Client': '41',
                    'Personnel': '42',
                    'CNPS': '43',
                    'Impots': '44',
                    'Associé': '45',
                    'Organisme international': '45', // Often same as Associate in some plans or 47
                    'Divers Tiers': '47'
                };

                const prefix = prefixes[type];

                // 1. Filtrer les comptes généraux
                const mainSelect = document.getElementById('compte_general');
                if (mainSelect) {
                    const options = Array.from(mainSelect.options).filter(opt => opt.value);
                    let filtered = [];
                    
                    if (type === 'Divers Tiers') {
                        const allPrefixes = ['40', '41', '42', '43', '44', '45', '46'];
                        filtered = options.filter(opt => {
                            const numero = opt.getAttribute('data-numero') || opt.textContent.trim();
                            return !allPrefixes.some(p => numero.startsWith(p));
                        });
                    } else if (prefix) {
                        filtered = options.filter(opt => {
                            const numero = opt.getAttribute('data-numero') || opt.textContent.trim();
                            return numero.startsWith(prefix);
                        });
                    }

                    compteGeneralTiers.innerHTML = '<option value="" selected disabled>Sélectionner un compte rattaché</option>';
                    filtered.forEach(opt => {
                        const newOpt = document.createElement('option');
                        newOpt.value = opt.value;
                        newOpt.setAttribute('data-numero', opt.getAttribute('data-numero') || opt.textContent.split(' - ')[0].trim());
                        newOpt.textContent = opt.textContent;
                        compteGeneralTiers.appendChild(newOpt);
                    });
                }

                // 2. Générer le numéro via API
                if (type !== 'Divers Tiers' && prefix) {
                    fetch(`/plan_tiers/${prefix}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data.numero) {
                                numeroTiersInput.value = data.numero;
                            } else {
                                numeroTiersInput.placeholder = 'Erreur';
                            }
                        })
                        .catch(err => {
                            console.error('Erreur génération numéro:', err);
                            const fallback = prefix + Math.floor(Math.random() * 89999 + 10000);
                            numeroTiersInput.value = fallback;
                        });
                } else {
                    numeroTiersInput.placeholder = 'Saisir manuellement';
                    numeroTiersInput.readOnly = false;
                    numeroTiersInput.style.backgroundColor = '#ffffff';
                }
            });
        }

        // Afficher tous les comptes (Voir)
        window.showAllAccountsTiers = function() {
            const mainSelect = document.getElementById('compte_general');
            const targetSelect = document.getElementById('compte_general_tiers');
            
            if (mainSelect && targetSelect) {
                const options = Array.from(mainSelect.options).filter(opt => opt.value);
                const filtered = options.filter(opt => {
                    const numero = opt.getAttribute('data-numero') || opt.textContent.trim();
                    return numero.startsWith('4');
                });

                targetSelect.innerHTML = '<option value="" selected disabled>-- Tous les comptes de classe 4 --</option>';
                filtered.forEach(opt => {
                    const newOpt = document.createElement('option');
                    newOpt.value = opt.value;
                    newOpt.setAttribute('data-numero', opt.getAttribute('data-numero') || opt.textContent.split(' - ')[0].trim());
                    newOpt.textContent = opt.textContent;
                    targetSelect.appendChild(newOpt);
                });
            }
        };

        // Création Effective
        window.createTiersSimple = function(event) {
            if (event) event.preventDefault();
            
            const data = {
                type_de_tiers: typeTiersSelect.value,
                compte_general: compteGeneralTiers.value,
                numero_de_tiers: numeroTiersInput.value,
                intitule: intituleTiersInput.value.trim()
            };

            if (!data.type_de_tiers || !data.compte_general || !data.numero_de_tiers || !data.intitule) {
                Swal.fire({ icon: 'warning', title: 'Champs manquants', text: 'Veuillez remplir toutes les informations obligatoires.' });
                return;
            }

            const originalBtnHtml = btnCreateTiers.innerHTML;
            btnCreateTiers.disabled = true;
            btnCreateTiers.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Création...';

            fetch('{{ route("plan_tiers.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    tiersModal.hide();
                    
                    // Mise à jour du dropdown principal
                    const mainTiersSelect = document.getElementById('compte_tiers');
                    if (mainTiersSelect) {
                        const newOption = new Option(`${result.numero_de_tiers} - ${result.intitule}`, result.id, true, true);
                        mainTiersSelect.add(newOption);
                        if (typeof $ !== 'undefined' && $(mainTiersSelect).data('select2')) {
                            $(mainTiersSelect).trigger('change');
                        } else {
                            mainTiersSelect.dispatchEvent(new Event('change'));
                        }
                    }

                    Swal.fire({ icon: 'success', title: 'Succès !', text: 'Le compte tiers a été créé et sélectionné.', timer: 2000, showConfirmButton: false });
                } else {
                    throw new Error(result.error || 'Erreur lors de la création');
                }
            })
            .catch(err => {
                console.error('Erreur:', err);
                Swal.fire({ icon: 'error', title: 'Oups...', text: 'Une erreur est survenue : ' + err.message });
            })
            .finally(() => {
                btnCreateTiers.disabled = false;
                btnCreateTiers.innerHTML = originalBtnHtml;
            });
        };
    }

    // --- GESTION DES POSTES TRÉSORERIE ---
    const modalCreatePosteEl = document.getElementById('modalCreatePoste');
    if (modalCreatePosteEl) {
        const posteModal = new bootstrap.Modal(modalCreatePosteEl);
        
        window.createPosteSimple = function(event) {
            if (event) event.preventDefault();
            
            const btn = document.getElementById('btnSavePoste');
            const name = document.getElementById('poste_name').value.trim();
            const category_id = document.getElementById('poste_category_id').value;

            if (!name || !category_id) {
                Swal.fire({ icon: 'warning', title: 'Champs manquants', text: 'Veuillez remplir toutes les informations obligatoires.' });
                return;
            }

            const originalBtnHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Création...';

            fetch('{{ route("postetresorerie.store_poste") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name, category_id })
            })
            .then(res => res.json())
            .then(result => {
                if (result.success) {
                    posteModal.hide();
                    document.getElementById('createPosteForm').reset();
                    
                    // Mise à jour du dropdown
                    const posteSelect = document.getElementById('poste_tresorerie');
                    if (posteSelect) {
                        const newOption = new Option(result.name, result.id, true, true);
                        posteSelect.add(newOption);
                        if (typeof $ !== 'undefined' && $(posteSelect).data('select2')) {
                            $(posteSelect).trigger('change');
                        } else {
                            posteSelect.dispatchEvent(new Event('change'));
                        }
                    }
                    
                    Swal.fire({ icon: 'success', title: 'Succès !', text: 'Le poste de trésorerie a été créé et sélectionné.', timer: 2000, showConfirmButton: false });
                } else {
                    throw new Error(result.error || 'Erreur lors de la création');
                }
            })
            .catch(err => {
                console.error('Erreur:', err);
                Swal.fire({ icon: 'error', title: 'Oups...', text: 'Une erreur est survenue : ' + err.message });
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;
            });
        };
    }

    // --- QUICK EDIT/CREATE POSTE TRÉSORERIE IN ROWS ---
    const treasuryCategories = @json($categories ?? []);
    
    // Define SYSCOHADA options (same as in list view)
    const syscohadaOptions = {
        '': 'Aucun (Non spécifié)',
        'INV_ACQ': 'INV - Acquisition d\'immobilisations',
        'INV_CES': 'INV - Cession d\'immobilisations',
        'FIN_EMP': 'FIN - Emprunt (Encaissement)',
        'FIN_RMB': 'FIN - Remboursement d\'emprunt',
        'FIN_DIV': 'FIN - Dividendes versés',
        'FIN_CAP': 'FIN - Augmentation de capital',
        'FIN_SUB': 'FIN - Subvention d\'investissement'
    };

    function getSyscohadaOptionsHtml(selected = '') {
        return Object.entries(syscohadaOptions).map(([key, label]) => 
            `<option value="${key}" ${key === selected ? 'selected' : ''}>${label}</option>`
        ).join('');
    }

    window.quickEditPosteRow = function(btn, posteId) {
        const row = btn.closest('tr');
        const currentText = row.querySelector('.poste-badge-text').innerText.trim();
        const [currentName, currentCategory] = currentText.split(' - ');
        
        // We might want to pass current syscohada code here, but it's not stored in the row data currently.
        // We could fetch it, or just let the user set it. 
        // For simplicity in this context (creating on the fly), defaults to empty is acceptable 
        // OR we could try to look it up if we had the full poste object.
        // Since storeQuickAJAX returns it, we can store it in a data attribute.
        const currentSyscohadaId = row.getAttribute('data-syscohada-id') || '';

        const categoryOptions = treasuryCategories.map(c => 
            `<option value="${c.id}" ${c.name === currentCategory ? 'selected' : ''}>${c.name}</option>`
        ).join('');
        const syscohadaOptionsHtml = getSyscohadaOptionsHtml(currentSyscohadaId);

        Swal.fire({
            title: 'Modifier le poste de trésorerie',
            html: `
                <div class="mb-3 text-start">
                    <label class="form-label">Nom du poste</label>
                    <input type="text" id="swal_row_poste_name" class="form-control" value="${currentName || ''}">
                </div>
                <div class="mb-3 text-start">
                    <label class="form-label">Catégorie</label>
                    <select id="swal_row_poste_category" class="form-select">
                        <option value="">Sélectionner une catégorie...</option>
                        ${categoryOptions}
                    </select>
                </div>
                <div class="mb-3 text-start">
                    <label class="form-label">Flux SYSCOHADA (TFT)</label>
                    <div class="form-text text-muted mb-1 text-xs">Obligatoire pour Inv/Fin</div>
                    <select id="swal_row_poste_syscohada" class="form-select">
                        ${syscohadaOptionsHtml}
                    </select>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Enregistrer',
            cancelButtonText: 'Annuler',
            preConfirm: () => {
                const name = document.getElementById('swal_row_poste_name').value;
                const category_id = document.getElementById('swal_row_poste_category').value;
                const syscohada_line_id = document.getElementById('swal_row_poste_syscohada').value;
                if (!name || !category_id) {
                    Swal.showValidationMessage('Veuillez remplir tous les champs');
                    return false;
                }
                return { name, category_id, syscohada_line_id };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                saveQuickPosteRow(row, result.value.name, result.value.category_id, result.value.syscohada_line_id);
            }
        });
    };

    window.quickCreatePosteRow = function(btn) {
        const row = btn.closest('tr');
        const categoryOptions = treasuryCategories.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
        const syscohadaOptionsHtml = getSyscohadaOptionsHtml();

        Swal.fire({
            title: 'Nouveau poste de trésorerie',
            html: `
                <div class="mb-3 text-start">
                    <label class="form-label">Nom du poste</label>
                    <input type="text" id="swal_row_poste_name" class="form-control" placeholder="Ex: Caisse Menue Dépense">
                </div>
                <div class="mb-3 text-start">
                    <label class="form-label">Catégorie</label>
                    <select id="swal_row_poste_category" class="form-select">
                        <option value="">Sélectionner une catégorie...</option>
                        ${categoryOptions}
                    </select>
                </div>
                <div class="mb-3 text-start">
                    <label class="form-label">Flux SYSCOHADA (TFT)</label>
                    <div class="form-text text-muted mb-1 text-xs">Obligatoire pour Inv/Fin</div>
                    <select id="swal_row_poste_syscohada" class="form-select">
                        ${syscohadaOptionsHtml}
                    </select>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Créer et Assigner',
            cancelButtonText: 'Annuler',
            preConfirm: () => {
                const name = document.getElementById('swal_row_poste_name').value;
                const category_id = document.getElementById('swal_row_poste_category').value;
                const syscohada_line_id = document.getElementById('swal_row_poste_syscohada').value;
                if (!name || !category_id) {
                    Swal.showValidationMessage('Veuillez remplir tous les champs');
                    return false;
                }
                return { name, category_id, syscohada_line_id };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                saveQuickPosteRow(row, result.value.name, result.value.category_id, result.value.syscohada_line_id);
            }
        });
    };

    function saveQuickPosteRow(row, name, categoryId, syscohadaLineId) {
        // Here we just update the row attributes, as those rows are not yet saved in DB (unless editing)
        // If it's a REAL entry being edited (e.g. from approval editing), we might want to call the API.
        // But the primary flow here is constructing the rows for a NEW validation.
        // For simplicity and to match the 'storeQuickAJAX' logic, let's call the API to ensure the Poste exists.
        
        fetch('{{ route("postetresorerie.store_quick") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                name: name,
                category_id: categoryId,
                syscohada_line_id: syscohadaLineId
                // We don't necessarily have an ecriture_id if this is a NEW row being added
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Update row's data attribute and UI
                row.setAttribute('data-poste-tresorerie-id', data.id);
                row.setAttribute('data-syscohada-id', data.syscohada_line_id || ''); // Store for future edits
                const badge = row.querySelector('.poste-badge-text');
                if (badge) badge.innerText = `${data.name} - ${data.category_name}`;
                
                // Update the button icon/onclick if it was a "Plus" button
                const btnContainer = row.querySelector('.td-poste-treso-row .group');
                if (btnContainer && btnContainer.querySelector('.bx-plus')) {
                    btnContainer.innerHTML = `
                        <span class="badge bg-label-info poste-badge-text">${data.name} - ${data.category_name}</span>
                        <button type="button" class="btn btn-xs btn-icon btn-label-secondary opacity-0 group-hover:opacity-100 transition-opacity" 
                            onclick="window.quickEditPosteRow(this, ${data.id})" title="Modifier le poste">
                            <i class="bx bx-edit-alt text-xs"></i>
                        </button>
                    `;
                }

                Swal.fire({ icon: 'success', title: 'Succès !', text: 'Poste mis à jour.', timer: 1500, showConfirmButton: false });
            } else {
                throw new Error(data.error || 'Erreur lors de la sauvegarde');
            }
        })
        .catch(err => {
            console.error('Erreur:', err);
            Swal.fire({ icon: 'error', title: 'Oups...', text: err.message });
        });
    }

    // --- AUTRES FONCTIONS ---
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

        const tbody = document.getElementById('tableEcrituresBody');
        if (!tbody) {
            alert('Tableau des écritures introuvable.');
            return;
        }
        
        // Supprimer la ligne "Aucune écriture" si elle existe
        const emptyRow = document.getElementById('emptyStateRow');
        if (emptyRow) {
            emptyRow.remove();
        }
        
        const compteTresorerieSelect = document.getElementById('compte_tresorerie');
        const compteTresorerieId = compteTresorerieSelect && 
                                 window.getComputedStyle(compteTresorerieSelect.parentElement).display !== 'none' ? 
                                 compteTresorerieSelect.value : '';
        
        // Récupérer l'ID du poste de trésorerie
        const posteTresorerieSelect = document.getElementById('poste_tresorerie');
        const posteTresorerieId = posteTresorerieSelect && !posteTresorerieSelect.disabled ? 
                                 posteTresorerieSelect.value : '';
        const posteTresorerieText = posteTresorerieId ? posteTresorerieSelect.options[posteTresorerieSelect.selectedIndex].text : '';

        const newRow = tbody.insertRow();

        const imputationValue = imputationInput ? imputationInput.value : '';
        const analytiqueValue = planAnalytique ? (planAnalytique.value === '1' ? '<span class="badge bg-label-success">Oui</span>' : '<span class="badge bg-label-secondary">Non</span>') : '';
        const compteText = compteGeneral.options[compteGeneral.selectedIndex].text;
        const compteTiersValue = compteTiers && compteTiers.value ? compteTiers.options[compteTiers.selectedIndex].text : '-';
        const pieceFileName = pieceFile && pieceFile.files[0] ? pieceFile.files[0].name : '';

        // Stocker l'ID du compte de trésorerie et du poste dans la ligne
        if (compteTresorerieId) {
            newRow.setAttribute('data-compte-tresorerie-id', compteTresorerieId);
        }
        if (posteTresorerieId) {
            newRow.setAttribute('data-poste-tresorerie-id', posteTresorerieId);
        }
        
        // Formattage des montants
        const formatNumber = (val) => {
            if (!val) return '-';
            // Remplacer la virgule par un point pour le calcul
            const numericVal = typeof val === 'string' ? parseFloat(val.replace(',', '.')) : val;
            return isNaN(numericVal) ? '-' : numericVal.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };

        // Ajouter chaque cellule avec son contenu
        newRow.innerHTML = `
            <td>${date.value}</td>
            <td class="fw-bold text-slate-700">${nSaisie ? nSaisie.value : ''}</td>
            <td><span class="table-badge badge-journal">${imputationValue}</span></td>
            <td><div class="text-truncate" style="max-width: 250px;" title="${libelle.value}">${libelle.value}</div></td>
            <td><span class="text-muted small fw-bold">${referencePiece ? referencePiece.value || '-' : '-'}</span></td>
            <td data-plan-comptable-id="${compteGeneral.value}"><span class="table-badge badge-compte">${compasTextShortcut(compteText)}</span></td>
            <td data-tiers-id="${compteTiers ? compteTiers.value : ''}">${compteTiers && compteTiers.value ? `<span class="table-badge badge-compte">${compteTiersValue}</span>` : '-'}</td>
            <td class="text-end amount-debit">${formatNumber(debit.value)}</td>
            <td class="text-end amount-credit">${formatNumber(credit.value)}</td>
            <td class="td-poste-treso-row">
                <div class="d-flex align-items-center gap-2 group">
                    <span class="badge bg-label-info poste-badge-text" style="font-size: 0.65rem;">
                        ${(() => {
                            if (!posteTresorerieText) return '-';
                            const parts = posteTresorerieText.split(' - ');
                            if (parts.length > 1) {
                                return `${parts[0]} <span class="badge bg-white text-info shadow-sm ms-1">${parts.slice(1).join(' - ')}</span>`;
                            }
                            return posteTresorerieText;
                        })()}
                    </span>
                    ${posteTresorerieId ? `
                        <button type="button" class="btn btn-xs btn-icon btn-label-secondary opacity-0 group-hover:opacity-100 transition-opacity" 
                            onclick="window.quickEditPosteRow(this, ${posteTresorerieId})">
                            <i class="bx bx-edit-alt text-xs"></i>
                        </button>
                    ` : (compteText.startsWith('5') ? `
                        <button type="button" class="btn btn-xs btn-icon btn-label-warning opacity-0 group-hover:opacity-100 transition-opacity" 
                            onclick="window.quickCreatePosteRow(this)">
                            <i class="bx bx-plus text-xs"></i>
                        </button>
                    ` : '')}
                </div>
            </td>
            <td class="text-center">
                ${pieceFileName ? '<button type="button" class="btn btn-xs btn-icon btn-label-primary" onclick="voirPieceJustificativeLocale()"><i class="bx bx-show"></i></button>' : '-'}
            </td>
            <td class="text-center">${analytiqueValue}</td>
            <td class="text-center">
                <div class="d-flex gap-1 justify-content-center">
                    <button type="button" class="btn btn-icon btn-sm btn-label-warning" onclick="modifierEcriture(this.closest('tr'));" title="Modifier">
                        <i class="bx bx-edit"></i>
                    </button>
                    <button type="button" class="btn btn-icon btn-sm btn-label-danger" onclick="supprimerEcriture(this.closest('tr'));" title="Supprimer">
                        <i class="bx bx-trash"></i>
                    </button>
                </div>
            </td>
        `;

        // Helper function (now defined globally at script start)

        // Réinitialisation SEULEMENT des champs spécifiques à chaque ligne
        compteGeneral.value = '';
        if (compteTiers) compteTiers.value = '';
        const posteTresoSelect = document.getElementById('poste_tresorerie');
        if (posteTresoSelect) {
            posteTresoSelect.value = '';
            if (typeof $ !== 'undefined' && $(posteTresoSelect).data('select2')) {
                $(posteTresoSelect).val('').trigger('change');
            }
        }
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
            alert('Aucun fichier Ã  visualiser');
            return;
        }
        
        // Ouvrir le fichier dans une nouvelle fenÃªtre
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
                        champSaisie.value = data.numero;
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
    const approvalEditingData = @json($approvalEditingData ?? null);

    document.addEventListener('DOMContentLoaded', function() {
        if (approvalEditingData) {
            chargerDonneesApprobation(approvalEditingData);
        }
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
                const val = source.value.trim();
                const numericVal = parseFloat(val) || 0;
                
                if (val !== '' && numericVal >= 0) {
                    target.value = '';
                    target.readOnly = true;
                    target.style.backgroundColor = '#f1f5f9';
                    target.style.cursor = 'not-allowed';
                    if (target.classList.contains('select2-hidden-accessible')) {
                        $(target).prop('disabled', true).trigger('change');
                    }
                } else {
                    target.readOnly = false;
                    target.style.backgroundColor = '';
                    target.style.cursor = '';
                    if (target.classList.contains('select2-hidden-accessible')) {
                        $(target).prop('disabled', false).trigger('change');
                    }
                }
            };

            debitInput.addEventListener('input', () => toggleExclusivity(debitInput, creditInput));
            creditInput.addEventListener('input', () => toggleExclusivity(creditInput, debitInput));
            
            // Exécuter une fois pour initialiser (utile lors de l'ajout d'une ligne ou chargement)
            window.resetExclusivity = () => {
                debitInput.value = '';
                creditInput.value = '';
                debitInput.readOnly = false;
                debitInput.style.backgroundColor = '';
                debitInput.style.cursor = '';
                creditInput.readOnly = false;
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
                if (json.summary.date) window.updateHybridDate(json.summary.date);
                if (json.summary.description) document.getElementById('description_operation').value = json.summary.description;
                if (json.summary.reference) document.getElementById('reference_piece').value = json.summary.reference;
                
                // Journal
                if (json.summary.code_journal_id) {
                    const imputationHidden = document.getElementById('imputation');
                    const codeJournalAffiche = document.getElementById('code_journal_affiche');
                    if (imputationHidden) imputationHidden.value = json.summary.code_journal_id;
                    if (codeJournalAffiche) codeJournalAffiche.value = json.summary.journal_code;
                    
                    // Déclencher la vérification du type de journal (pour afficher le compte de trÃ©sorerie si nécessaire)
                    if (typeof verifierTypeJournal === 'function') {
                        verifierTypeJournal(json.summary.journal_code);
                    }
                }

                // N° de Saisie
                if (json.summary.n_saisie) {
                    const nSaisieInput = document.getElementById('n_saisie');
                    if (nSaisieInput) nSaisieInput.value = json.summary.n_saisie;
                }

                // N° de Saisie (original)
                if (json.summary.n_saisie_user !== undefined) {
                    const nSaisieUserInput = document.getElementById('n_saisie_user');
                    if (nSaisieUserInput) nSaisieUserInput.value = json.summary.n_saisie_user || '';
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
                if (json.summary.date) window.updateHybridDate(json.summary.date);
                if (json.summary.description) document.getElementById('description_operation').value = json.summary.description;
                if (json.summary.reference) document.getElementById('reference_piece').value = json.summary.reference;
                
                // Journal
                if (json.summary.code_journal_id) {
                    const imputationHidden = document.getElementById('imputation');
                    const codeJournalAffiche = document.getElementById('code_journal_affiche');
                    if (imputationHidden) imputationHidden.value = json.summary.code_journal_id;
                    if (codeJournalAffiche) codeJournalAffiche.value = json.summary.journal_code;
                    
                    // Déclencher la vérification du type de journal (pour afficher le compte de trÃ©sorerie si nécessaire)
                    if (typeof verifierTypeJournal === 'function') {
                        verifierTypeJournal(json.summary.journal_code);
                    }
                }

                // N° de Saisie
                if (json.summary.n_saisie) {
                    const nSaisieInput = document.getElementById('n_saisie');
                    if (nSaisieInput) nSaisieInput.value = json.summary.n_saisie;
                }

                // N° de Saisie (original)
                if (json.summary.n_saisie_user !== undefined) {
                    const nSaisieUserInput = document.getElementById('n_saisie_user');
                    if (nSaisieUserInput) nSaisieUserInput.value = json.summary.n_saisie_user || '';
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
        const tbody = document.getElementById('tableEcrituresBody');
        if (!tbody) return;

        // Supprimer la ligne "Aucune écriture"
        const emptyRow = document.getElementById('emptyStateRow');
        if (emptyRow) emptyRow.remove();

        const tr = tbody.insertRow();
        
        const date = b.date || document.getElementById('date').value;
        const nSaisie = b.n_saisie || document.getElementById('n_saisie').value;
        const imputation = document.getElementById('code_journal_affiche').value;
        const description = b.description_operation || '';
        const reference = b.reference_piece || '';
        const compteText = b.plan_comptable ? `${b.plan_comptable.numero_de_compte} - ${b.plan_comptable.intitule}` : '-';
        const tiersText = b.plan_tiers ? `${b.plan_tiers.numero_de_tiers} - ${b.plan_tiers.intitule}` : '-';
        const debit = b.debit || 0;
        const credit = b.credit || 0;
        const analytique = b.plan_analytique || b.analytique === 'Oui';
        const pieceFileName = b.piece_justificatif || '';
        const posteTresorerieName = b.poste_tresorerie ? b.poste_tresorerie.name : '';
        const posteTresorerieId = b.poste_tresorerie_id || '';

        if (b.compte_tresorerie_id) {
            tr.setAttribute('data-compte-tresorerie-id', b.compte_tresorerie_id);
        }
        if (posteTresorerieId) {
            tr.setAttribute('data-poste-tresorerie-id', posteTresorerieId);
        }

        const formatNumber = (val) => {
            if (!val && val !== 0) return '-';
            const numericVal = typeof val === 'string' ? parseFloat(val.replace(',', '.')) : val;
            return isNaN(numericVal) ? '-' : numericVal.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };
        const analytiqueBadge = analytique ? '<span class="badge bg-label-success">Oui</span>' : '<span class="badge bg-label-secondary">Non</span>';

        tr.innerHTML = `
            <td>${date}</td>
            <td class="fw-bold text-slate-700">${nSaisie}</td>
            <td><span class="table-badge badge-journal">${imputation}</span></td>
            <td><div class="text-truncate" style="max-width: 250px;" title="${description}">${description}</div></td>
            <td><span class="text-muted small fw-bold">${reference || '-'}</span></td>
            <td data-plan-comptable-id="${b.plan_comptable_id}"><span class="table-badge badge-compte">${compasTextShortcut(compteText)}</span></td>
            <td data-tiers-id="${b.plan_tiers_id || ''}"><span class="table-badge badge-compte">${tiersText}</span></td>
            <td class="text-end amount-debit">${formatNumber(debit)}</td>
            <td class="text-end amount-credit">${formatNumber(credit)}</td>
            <td class="td-poste-treso-row">
                <div class="d-flex align-items-center gap-2 group">
                    <span class="badge bg-label-info poste-badge-text">
                        ${posteTresorerieName || '-'}
                    </span>
                    ${posteTresorerieId ? `
                        <button type="button" class="btn btn-xs btn-icon btn-label-secondary opacity-0 group-hover:opacity-100 transition-opacity" 
                            onclick="window.quickEditPosteRow(this, ${posteTresorerieId})">
                            <i class="bx bx-edit-alt text-xs"></i>
                        </button>
                    ` : (compteText.startsWith('5') ? `
                        <button type="button" class="btn btn-xs btn-icon btn-label-warning opacity-0 group-hover:opacity-100 transition-opacity" 
                            onclick="window.quickCreatePosteRow(this)">
                            <i class="bx bx-plus text-xs"></i>
                        </button>
                    ` : '')}
                </div>
            </td>
            <td class="text-center">
                ${pieceFileName ? `<button type="button" class="btn btn-xs btn-icon btn-label-primary" onclick="voirPieceJustificative('${pieceFileName}')"><i class="bx bx-show"></i></button>` : '-'}
            </td>
            <td class="text-center">${analytiqueBadge}</td>
            <td class="text-center">
                <div class="d-flex gap-1 justify-content-center">
                    <button type="button" class="btn btn-icon btn-sm btn-label-warning" onclick="modifierEcriture(this.closest('tr'));" title="Modifier">
                        <i class="bx bx-edit"></i>
                    </button>
                    <button type="button" class="btn btn-icon btn-sm btn-label-danger" onclick="supprimerEcriture(this.closest('tr'));" title="Supprimer">
                        <i class="bx bx-trash"></i>
                    </button>
                </div>
            </td>
        `;
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
                compte_tresorerie_id: compteTresorerieId || null,
                poste_tresorerie_id: row.getAttribute('data-poste-tresorerie-id') || null
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
                    // Ensuite, crÃ©er les nouvelles Ã©critures
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
        } else if (approvalEditingData) {
            // Mode modification d'approbation (Admin)
            updateApprovalEntries(ecritures, pieceFile, approvalEditingData.approval_id);
        } else {
            // Mode création : créer directement les écritures
            creerNouvellesEcritures(ecritures, pieceFile, batchId);
        }
    }

    function chargerDonneesApprobation(data) {
        console.log("Loading Approval Data", data);
        const btnEnregistrer = document.getElementById('btnEnregistrer');
        const btnText = document.getElementById('btnText');
        
        if (btnText) btnText.textContent = 'VALIDER LA MODIFICATION';
        
        // Remplir les champs communs
        if (data.date) window.updateHybridDate(data.date);
        if (data.description) document.getElementById('description_operation').value = data.description;
        if (data.reference) document.getElementById('reference_piece').value = data.reference;
        if (data.n_saisie) document.getElementById('n_saisie').value = data.n_saisie;

        // Journal
        if (data.code_journal_id) {
            document.getElementById('imputation').value = data.code_journal_id;
            // Trigger change if needed or manually update display
             const select = document.getElementById('imputation');
             const option = select.querySelector(`option[value="${data.code_journal_id}"]`);
             if(option) {
                 document.getElementById('code_journal_affiche').value = option.getAttribute('data-code');
                 verifierTypeJournal(option.getAttribute('data-code'));
             }
        }
        
        // Compte Trésorerie
        if (data.compte_tresorerie_id) {
            const selectTreso = document.getElementById('compte_tresorerie');
            if (selectTreso) {
                selectTreso.value = data.compte_tresorerie_id;
                if (typeof $(selectTreso).select2 === 'function') $(selectTreso).trigger('change');
            }
        }

        // Lignes
        const tbody = document.querySelector('#tableEcritures tbody');
        if (tbody) tbody.innerHTML = '';
        
        data.lines.forEach(line => {
             ajouterLigneEcriture({
                date: line.date,
                piece: line.reference_piece,
                journal: line.code_journal ? line.code_journal.code_journal : '',
                compte: line.plan_comptable ? `${line.plan_comptable.numero_de_compte} - ${line.plan_comptable.intitule}` : '',
                libelle: line.description_operation,
                poste: line.poste_tresorerie ? `${line.poste_tresorerie.name} - ${line.poste_tresorerie.category ? line.poste_tresorerie.category.name : ''}` : '',
                tiers: line.plan_tiers ? `${line.plan_tiers.numero_de_tiers} - ${line.plan_tiers.intitule}` : '',
                debit: line.debit ? line.debit.toString().replace('.', ',') : '0,00',
                credit: line.credit ? line.credit.toString().replace('.', ',') : '0,00',
                analytique: line.plan_analytique ? 'Oui' : 'Non'
            });
            // Update attributes manually for IDs
            const lastRow = tbody.lastElementChild;
            if(lastRow) {
                lastRow.cells[5].setAttribute('data-plan-comptable-id', line.plan_comptable_id);
                if(line.plan_tiers_id) lastRow.cells[6].setAttribute('data-tiers-id', line.plan_tiers_id);
                if(line.compte_tresorerie_id) lastRow.setAttribute('data-compte-tresorerie-id', line.compte_tresorerie_id);
                if(line.poste_tresorerie_id) lastRow.setAttribute('data-poste-tresorerie-id', line.poste_tresorerie_id);
                
                // Handle attachment button if present
                if (line.piece_justificatif) {
                     lastRow.cells[9].innerHTML = `<button class="btn btn-sm btn-primary-premium btn-premium" onclick="voirPieceJustificative('${line.piece_justificatif}')" style="border-radius: 10px;"><i class="bx bx-eye me-1"></i>Voir</button>`;
                     lastRow.cells[9].setAttribute('data-piece-filename', line.piece_justificatif);
                }
            }
        });
        
        updateTotals();
        showAlert('info', 'Données d\'approbation chargées pour modification.');
    }

    function updateApprovalEntries(ecritures, pieceFile, approvalId) {
        const formDataToSend = new FormData();
        formDataToSend.append('ecritures', JSON.stringify(ecritures));
        formDataToSend.append('approval_id', approvalId);
        if (pieceFile) {
            formDataToSend.append('piece_justificatif', pieceFile);
        }

        const btnEnregistrer = document.getElementById('btnEnregistrer');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');
        
        btnText.textContent = 'VALIDATION EN COURS...';
        btnEnregistrer.disabled = true;
        btnSpinner.classList.remove('d-none');

        fetch("{{ route('ecriture.update_approval') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formDataToSend
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                if (data.redirect) {
                    setTimeout(() => window.location.href = data.redirect, 1500);
                } else {
                     window.location.reload();
                }
            } else {
                throw new Error(data.message || 'Erreur inconnue');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showAlert('danger', 'Erreur lors de la mise à jour: ' + error.message);
            btnText.textContent = 'VALIDER LA MODIFICATION';
            btnEnregistrer.disabled = false;
            btnSpinner.classList.add('d-none');
        });
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
                
                const tbody = document.querySelector('#tableEcritures tbody');
                
                // Mettre à jour les boutons "Voir" avec le nom de fichier sauvegardé
                if (body.piece_filename && tbody) {
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
                
                if (tbody) tbody.innerHTML = '';
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

    function ajouterLigneEcriture(ligne = {}) {
        const tbody = document.getElementById('tableEcrituresBody');
        if (!tbody) return;

        // Supprimer la ligne "Aucune écriture"
        const emptyRow = document.getElementById('emptyStateRow');
        if (emptyRow) emptyRow.remove();

        const tr = document.createElement('tr');
        
        const date = ligne.date || document.getElementById('date').value;
        const nSaisie = ligne.piece || ligne.nSaisie || document.getElementById('n_saisie').value || '';
        const journal = ligne.journal || document.getElementById('code_journal_affiche').value || '';
        const libelle = ligne.libelle || '';
        const reference = ligne.reference || ligne.piece_justificatif || '';
        const compte = ligne.compte || '';
        const tiers = ligne.tiers || '-';
        const debit = parseFloat((ligne.debit || '0').replace(/\s/g, '').replace(',', '.')) || 0;
        const credit = parseFloat((ligne.credit || '0').replace(/\s/g, '').replace(',', '.')) || 0;
        const analytique = ligne.analytique === 'Oui' || ligne.analytique === true;
        
        const formatNumber = (val) => {
            if (!val && val !== 0) return '-';
            const numericVal = typeof val === 'string' ? parseFloat(val.replace(',', '.')) : val;
            return isNaN(numericVal) ? '-' : numericVal.toLocaleString('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        };
        const analytiqueBadge = analytique ? '<span class="badge bg-label-success">Oui</span>' : '<span class="badge bg-label-secondary">Non</span>';

        const poste = ligne.poste || '-';
        
        tr.innerHTML = `
            <td>${date}</td>
            <td class="fw-bold text-slate-700">${nSaisie}</td>
            <td><span class="table-badge badge-journal">${journal}</span></td>
            <td><div class="text-truncate" style="max-width: 250px;" title="${libelle}">${libelle}</div></td>
            <td><span class="text-muted small fw-bold">${reference || '-'}</span></td>
            <td><span class="table-badge badge-compte">${compasTextShortcut(compte)}</span></td>
            <td><span class="table-badge badge-compte">${tiers}</span></td>
            <td class="text-end amount-debit">${formatNumber(debit)}</td>
            <td class="text-end amount-credit">${formatNumber(credit)}</td>
            <td class="td-poste-treso-row">
                <div class="d-flex align-items-center gap-2 group">
                    <span class="badge bg-label-info poste-badge-text" style="font-size: 0.65rem;">
                         ${(() => {
                            if (!poste || poste === '-') return '-';
                            const parts = poste.split(' - ');
                            if (parts.length > 1) {
                                return `${parts[0]} <span class="badge bg-white text-info shadow-sm ms-1">${parts.slice(1).join(' - ')}</span>`;
                            }
                            return poste;
                        })()}
                    </span>
                    <button type="button" class="btn btn-xs btn-icon btn-label-secondary opacity-0 group-hover:opacity-100 transition-opacity" 
                        onclick="const row = this.closest('tr'); window.quickEditPosteRow(this, row.getAttribute('data-poste-tresorerie-id'))">
                        <i class="bx bx-edit-alt text-xs"></i>
                    </button>
                </div>
            </td>
            <td class="text-center">-</td>
            <td class="text-center">${analytiqueBadge}</td>
            <td class="text-center">
                <div class="d-flex gap-1 justify-content-center">
                    <button type="button" class="btn btn-icon btn-sm btn-label-warning" onclick="modifierEcriture(this.closest('tr'));" title="Modifier">
                        <i class="bx bx-edit"></i>
                    </button>
                    <button type="button" class="btn btn-icon btn-sm btn-label-danger" onclick="supprimerEcriture(this.closest('tr'));" title="Supprimer">
                        <i class="bx bx-trash"></i>
                    </button>
                </div>
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
                plan_analytique: cells[11].textContent.trim() === 'Oui' ? 1 : 0,
                exercices_comptables_id: formData.get('id_exercice'),
                code_journal_id: formData.get('code_journal_id'),
                journaux_saisis_id: formData.get('journaux_saisis_id'),
                compte_tresorerie_id: compteTresorerieId || null,
                poste_tresorerie_id: row.getAttribute('data-poste-tresorerie-id') || null,
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
                const val = parseFloat(debitCell.textContent.replace(/\s/g, '').replace(',', '.'));
                totalDebit += isNaN(val) ? 0 : val;
            }
            if (creditCell && creditCell.textContent) {
                const val = parseFloat(creditCell.textContent.replace(/\s/g, '').replace(',', '.'));
                totalCredit += isNaN(val) ? 0 : val;
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
        
        // Pour le compte général
        const compteGeneralCell = cells[5];
        const compteGeneralId = compteGeneralCell.getAttribute('data-plan-comptable-id');
        
        // Pour le compte tiers
        const compteTiersCell = cells[6];
        const compteTiersId = compteTiersCell.getAttribute('data-tiers-id');
        
        const debit = cells[7].textContent.trim().replace(/\s/g, '').replace(',', '.');
        const credit = cells[8].textContent.trim().replace(/\s/g, '').replace(',', '.');
        
        const posteTresorerieId = row.getAttribute('data-poste-tresorerie-id');
        const compteTresorerieId = row.getAttribute('data-compte-tresorerie-id');
        
        // Analytique (index 11)
        let analytique = false;
        if (cells[11]) {
            const text = cells[11].textContent.trim();
            analytique = text === 'Oui';
        }
        
        // --- MISE À JOUR DU FORMULAIRE ---
        if (document.getElementById('date')) document.getElementById('date').value = date;
        if (document.getElementById('n_saisie')) document.getElementById('n_saisie').value = nSaisie;
        if (document.getElementById('description_operation')) document.getElementById('description_operation').value = libelle;
        if (document.getElementById('reference_piece')) document.getElementById('reference_piece').value = referencePiece === '-' ? '' : referencePiece;
        if (document.getElementById('debit')) document.getElementById('debit').value = debit === '-' ? '' : debit;
        if (document.getElementById('credit')) document.getElementById('credit').value = credit === '-' ? '' : credit;
        
        if (document.getElementById('plan_analytique')) {
            document.getElementById('plan_analytique').value = analytique ? '1' : '0';
        }

        // Comptes (Select2)
        if (compteGeneralId) {
            $('#compte_general').val(compteGeneralId).trigger('change');
        }
        if (compteTiersId) {
            $('#compte_tiers').val(compteTiersId).trigger('change');
        } else {
            $('#compte_tiers').val('').trigger('change');
        }
        
        // Journal (Select2 si applicable, sinon text)
        const journalField = document.getElementById('code_journal_affiche');
        if (journalField) journalField.value = journal;

        // Trésorerie
        if (compteTresorerieId) {
            $('#compte_tresorerie').val(compteTresorerieId).trigger('change');
        }
        if (posteTresorerieId) {
            $('#poste_tresorerie').val(posteTresorerieId).trigger('change');
        } else {
            $('#poste_tresorerie').val('').trigger('change');
        }
        
        // Supprimer la ligne et recalculer
        row.remove();
        
        // Gérer l'état "vide" du tableau
        const tbody = document.getElementById('tableEcrituresBody');
        if (tbody && tbody.rows.length === 0) {
            const emptyTr = document.createElement('tr');
            emptyTr.id = 'emptyStateRow';
            emptyTr.innerHTML = '<td colspan="13" class="text-center py-5 text-muted"><i class="bx bx-info-circle fs-4 mb-2 d-block"></i>Aucune écriture ajoutée pour le moment.</td>';
            tbody.appendChild(emptyTr);
        }
        
        updateTotals();
        
        // Focus
        document.getElementById('description_operation').focus();
        showAlert('info', 'Écriture chargée pour modification.');
    }

    // Fonction pour supprimer une écriture
    function supprimerEcriture(row) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette écriture ?')) {
            row.remove();
            
            // Gérer l'état "vide" du tableau
            const tbody = document.getElementById('tableEcrituresBody');
            if (tbody && tbody.rows.length === 0) {
                const emptyTr = document.createElement('tr');
                emptyTr.id = 'emptyStateRow';
                emptyTr.innerHTML = '<td colspan="13" class="text-center py-5 text-muted"><i class="bx bx-info-circle fs-4 mb-2 d-block"></i>Aucune écriture ajoutée pour le moment.</td>';
                tbody.appendChild(emptyTr);
            }
            
            updateTotals();
            showAlert('success', 'Écriture supprimée avec succès !');
        }
    }

    // Gestionnaire d'Ã©vÃ©nements pour le changement de type de tiers

    // --- LOGIQUE ADDITIONNELLE (Date & Tiers) ---
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Masquage de la date (modification du jour uniquement)
        const dateInput = document.getElementById('date');
        if (dateInput) {
            const originalDate = dateInput.value;
            if (originalDate) {
                const dateParts = originalDate.split('-');
                const year = dateParts[0];
                const month = dateParts[1];
                
                dateInput.addEventListener('change', function() {
                    const newDate = this.value;
                    const newParts = newDate.split('-');
                    
                    if (newParts[0] !== year || newParts[1] !== month) {
                        this.value = `${year}-${month}-${newParts[2]}`;
                        showAlert('warning', 'Vous ne pouvez modifier que le jour dans cet exercice.');
                    }
                });
            }
        }

        // 2. Désactivation du compte tiers pour la classe 6
        const compteGeneralSelect = document.getElementById('compte_general');
        const compteTiersSelect = document.getElementById('compte_tiers');

        if (compteGeneralSelect && compteTiersSelect) {
            $(compteGeneralSelect).on('select2:select change', function(e) {
                const option = this.options[this.selectedIndex];
                const numero = option ? (option.getAttribute('data-numero') || option.text.split(' - ')[0] || '') : '';
                
                // --- Logique Poste Trésorerie (Saisi) ---
                const posteTresoSelect = document.getElementById('poste_tresorerie');
                const btnCreatePoste = document.getElementById('btn_create_poste_entry');
                if (posteTresoSelect) {
                    if (numero.startsWith('5')) {
                        $(posteTresoSelect).prop('disabled', false);
                        posteTresoSelect.setAttribute('required', 'required');
                        if (btnCreatePoste) btnCreatePoste.classList.remove('d-none');
                    } else {
                        $(posteTresoSelect).prop('disabled', true);
                        posteTresoSelect.removeAttribute('required');
                        // Reset value if not class 5
                        $(posteTresoSelect).val(null);
                        if (btnCreatePoste) btnCreatePoste.classList.add('d-none');
                    }
                    $(posteTresoSelect).trigger('change');
                }

                if (numero.startsWith('6') || numero.startsWith('7')) {
                    if (numero.startsWith('6')) {
                        $(compteTiersSelect).val(null).trigger('change');
                        $(compteTiersSelect).prop('disabled', true).trigger('change');
                        const parent = compteTiersSelect.closest('.d-flex');
                        if (parent) {
                            parent.style.opacity = '0.5';
                            parent.style.pointerEvents = 'none';
                        }
                    }
                } else {
                    $(compteTiersSelect).prop('disabled', false).trigger('change');
                    const parent = compteTiersSelect.closest('.d-flex');
                    if (parent) {
                        parent.style.opacity = '1';
                        parent.style.pointerEvents = 'auto';
                    }
                }
            });
        }

        // 3. Custom Matcher for Prefix-based filtering (especially for account numbers)
        function prefixMatcher(params, data) {
            if ($.trim(params.term) === '') {
                return data;
            }
            if (typeof data.text === 'undefined') {
                return null;
            }

            const term = params.term.toLowerCase();
            const text = data.text.toLowerCase();

            // If it's a digit, we prioritize "starts with"
            if (/^\d/.test(term)) {
                if (text.startsWith(term)) {
                    return data;
                }
                // Also check if any part of the string (like after a hyphen) starts with it
                const parts = text.split(/[\s-]+/);
                for (let part of parts) {
                    if (part.startsWith(term)) {
                        return data;
                    }
                }
                return null;
            }

            // For non-digits, keep standard "contains" behavior
            if (text.indexOf(term) > -1) {
                return data;
            }
            return null;
        }

        // 4. Custom Template for Treasury Posts (Select2)
        function formatTreasuryResult(state) {
            if (!state.id) return state.text;
            const parts = state.text.split(' - ');
            if (parts.length < 2) return state.text;
            
            const name = parts[0];
            const type = parts.slice(1).join(' - ');
            
            // Format "nom - type" avec le type en fond gris clair/blanc via CSS class
            return $(
                '<span>' + name + ' - </span>' +
                '<span class="treasury-category-badge">' + type + '</span>'
            );
        }

        function formatTreasurySelection(state) {
            if (!state.id) return state.text;
            const parts = state.text.split(' - ');
            if (parts.length < 2) return state.text.split(' - ')[0]; // Fallback to name if hyphen missing
            
            const name = parts[0];
            const type = parts.slice(1).join(' - ');
            
            return $(
                '<span>' + name + ' - </span>' +
                '<span class="treasury-category-badge">' + type + '</span>'
            );
        }

        if (typeof $ !== 'undefined' && typeof $.fn.select2 !== 'undefined') {
            $('#compte_general, #compte_tiers, #compte_tresorerie, #poste_tresorerie').select2({
                matcher: prefixMatcher,
                templateResult: (state) => {
                    // Use custom template for treasury fields, default for others
                    const id = state.element ? state.element.parentElement.id : '';
                    if (id === 'compte_tresorerie' || id === 'poste_tresorerie') {
                        return formatTreasuryResult(state);
                    }
                    return state.text;
                },
                templateSelection: (state) => {
                    const id = state.element ? state.element.parentElement.id : '';
                    if (id === 'compte_tresorerie' || id === 'poste_tresorerie') {
                        return formatTreasurySelection(state);
                    }
                    return state.text;
                },
                escapeMarkup: function(m) { return m; },
                width: '100%'
            });
        }
    });
</script>
