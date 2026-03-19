<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">

@include('components.head')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --liasse-primary: #2563eb;
        --liasse-secondary: #64748b;
        --liasse-accent: #f59e0b;
        --liasse-bg: #f1f5f9;
        --liasse-card-bg: rgba(255, 255, 255, 0.85);
        --excel-green: #166534;
    }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        background-image: 
            radial-gradient(at 0% 0%, rgba(37, 99, 235, 0.05) 0px, transparent 50%),
            radial-gradient(at 50% 0%, rgba(245, 158, 11, 0.05) 0px, transparent 50%);
        background-attachment: fixed;
    }

    .liasse-container {
        display: flex;
        flex-direction: column;
        height: calc(100vh - 100px);
        gap: 1.25rem;
        overflow: visible !important;
        padding-bottom: 1rem;
    }

    .toolbar-premium {
        background: var(--liasse-card-bg);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 20px;
        padding: 1rem 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.07);
        position: relative;
        z-index: 1000;
    }

    .summary-pills {
        display: flex;
        gap: 1rem;
        margin-left: 2rem;
    }

    .summary-pill {
        background: white;
        border-radius: 12px;
        padding: 0.5rem 1rem;
        border: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        min-width: 120px;
    }

    .summary-pill .label { font-size: 0.65rem; color: #64748b; text-transform: uppercase; font-weight: 700; }
    .summary-pill .value { font-size: 0.9rem; font-weight: 800; color: #1e293b; }

    .excel-canvas {
        background: white;
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 24px;
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.05);
    }

    .canvas-header {
        background: #ffffff;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
    }

    .canvas-body {
        flex: 1;
        overflow: auto;
        padding: 2rem;
        position: relative;
        background: #fff;
    }

    /* Modern Tabs (Excel Style) */
    .pagination-excel {
        display: flex;
        gap: 0;
        padding: 0;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        overflow-x: auto;
        white-space: nowrap;
        flex-shrink: 0;
        scrollbar-width: thin;
    }

    .page-btn {
        padding: 0.75rem 1.5rem;
        background: #f8fafc;
        border: none;
        border-right: 1px solid #e2e8f0;
        font-size: 0.8rem;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        position: relative;
    }

    .page-btn:hover {
        background: #f1f5f9;
        color: var(--liasse-primary);
    }

    .page-btn.active {
        background: white;
        color: var(--liasse-primary);
        font-weight: 800;
        box-shadow: inset 0 3px 0 var(--liasse-primary);
    }

    .page-btn.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        right: 0;
        height: 2px;
        background: white;
        z-index: 10;
    }

    .page-indicator {
        width: 8px;
        height: 8px;
        border-radius: 2px;
        background: #cbd5e1;
        transform: rotate(45deg);
    }

    .page-btn.active .page-indicator { background: var(--liasse-primary); }

    .btn-export {
        padding: 0.5rem 1rem;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        border: 1px solid transparent;
    }

    .btn-pdf { background: #fef2f2; color: #991b1b; border-color: #fee2e2; }
    .btn-excel { background: #f0fdf4; color: #166534; border-color: #dcfce7; }
    .btn-xml { background: #fffbeb; color: #92400e; border-color: #fef3c7; }

    .btn-export:hover { 
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }

    #loadingOverlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(255,255,255,0.7);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 100;
        backdrop-filter: blur(4px);
    }

    /* Premium Table */
    .liasse-table { 
        width: 100%; 
        border-collapse: separate; 
        border-spacing: 0;
        font-size: 0.9rem; 
    }
    
    .liasse-table th { 
        background: #f8fafc; 
        color: #475569; 
        border-bottom: 2px solid #e2e8f0;
        padding: 12px 15px; 
        font-size: 0.75rem; 
        font-weight: 800;
        text-transform: uppercase; 
        letter-spacing: 0.05em; 
        text-align: left;
    }

    .liasse-table td { 
        border-bottom: 1px solid #f1f5f9; 
        padding: 10px 15px; 
        vertical-align: middle;
    }

    .row-section { background: #f8fafc; font-weight: 800; color: #1e40af; }
    .row-total { background: #f1f5f9; font-weight: 800; }
    
    .col-code { width: 60px; text-align: center; color: #94a3b8; font-weight: 700; font-size: 0.7rem; }
    .col-val { width: 160px; text-align: right; font-weight: 700; font-variant-numeric: tabular-nums; }
    
    .liasse-input { 
        width: 100%; 
        border: 1.5px solid #e2e8f0; 
        padding: 6px 10px; 
        text-align: right; 
        border-radius: 8px; 
        transition: all 0.2s; 
        font-weight: 700;
        background: #fafafa;
    }
    
    .liasse-input:focus { 
        background: white;
        border-color: var(--liasse-primary); 
        outline: none; 
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1); 
    }

    .text-gradient {
        background: linear-gradient(135deg, #2563eb, #7c3aed);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
</style>

<body>
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('components.sidebar')

        <div class="layout-page">
            @include('components.header', ['page_title' => 'Liasse Fiscale <span class="text-gradient">e-SINTAX</span>'])

            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">

                    <div class="liasse-container">

                        {{-- Toolbar --}}
                        <div class="toolbar-premium">
                            <div class="d-flex align-items-center">
                                @if(file_exists(public_path('logo_armoiries.png')))
                                    <div class="me-3 p-2 bg-white rounded-3 shadow-sm" style="border: 1px solid #e2e8f0;">
                                        <img src="{{ asset('logo_armoiries.png') }}" alt="Logo DGI" style="height: 45px;">
                                    </div>
                                @endif
                                <div>
                                    <h4 class="fw-900 mb-0 text-dark">Liasse Fiscale <span class="text-gradient">e-SINTAX</span></h4>
                                    <p class="text-muted small mb-0 font-weight-600">Exercice : <span class="badge bg-label-primary">{{ $exercice->intitule }}</span> &nbsp;•&nbsp; <strong>SYSCOHADA Révisé</strong></p>
                                </div>
                                <div class="summary-pills d-none d-lg-flex">
                                    <div class="summary-pill">
                                        <span class="label">Total Actif</span>
                                        <span class="value text-primary" id="sumActif">--</span>
                                    </div>
                                    <div class="summary-pill">
                                        <span class="label">Total Passif</span>
                                        <span class="value text-info" id="sumPassif">--</span>
                                    </div>
                                    <div class="summary-pill">
                                        <span class="label">Résultat Net</span>
                                        <span class="value text-success" id="sumResultat">--</span>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-3">
                                <div class="dropdown">
                                    <button class="btn btn-primary dropdown-toggle rounded-pill px-4 shadow-sm fw-700" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bx bx-cloud-download me-2 fs-5"></i> Export Complet
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-2xl border-0 p-2" style="min-width: 260px; border-radius: 16px;">
                                        <li><a class="dropdown-item py-3 rounded-3" href="{{ route('reporting.liasse.export', 'pdf') }}"><div class="d-flex align-items-center"><i class="bx bxs-file-pdf text-danger me-3 fs-3"></i><div><div class="fw-700">Document PDF</div><small class="text-muted">Prêt pour impression</small></div></div></a></li>
                                        <li><a class="dropdown-item py-3 rounded-3" href="{{ route('reporting.liasse.export', 'excel') }}"><div class="d-flex align-items-center"><i class="bx bxs-spreadsheet text-success me-3 fs-3"></i><div><div class="fw-700">Fichier Excel</div><small class="text-muted">Analyse et retraitement</small></div></div></a></li>
                                        <li><hr class="dropdown-divider opacity-50"></li>
                                        <li><a class="dropdown-item py-3 rounded-3" style="background: rgba(255, 171, 0, 0.1);" href="{{ route('reporting.liasse.export', 'xml') }}"><div class="d-flex align-items-center"><i class="bx bx-code-alt text-warning me-3 fs-3"></i><div><div class="fw-700 text-warning">Flux XML EDI</div><small class="text-muted">Télétransmission DGI</small></div></div></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>


                        {{-- Excel Surface --}}
                        <div class="excel-canvas">
                            {{-- Canvas Header --}}
                            <div class="canvas-header">
                                <span id="currentPageTitle" class="fw-700 text-uppercase text-dark" style="letter-spacing: 0.05em; font-size: 0.85rem;">CHARGEMENT...</span>
                                <div class="d-flex gap-2">
                                    <button class="btn-export btn-pdf" onclick="exportPage('pdf')"><i class="bx bxs-file-pdf"></i> PDF</button>
                                    <button class="btn-export btn-excel" onclick="exportPage('excel')"><i class="bx bxs-spreadsheet"></i> Excel</button>
                                    <button class="btn-export btn-xml" onclick="exportPage('xml')"><i class="bx bx-code-alt"></i> XML</button>
                                </div>
                            </div>

                            {{-- Page Content Area --}}
                            <div class="canvas-body">
                                <div id="loadingOverlay">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Chargement...</span>
                                    </div>
                                </div>
                                <input type="hidden" id="currentPageCode" value="">
                                <input type="hidden" id="currentPageNumber" value="1">
                                <div id="pageContent">
                                    <!-- AJAX Content loaded here -->
                                </div>
                            </div>

                            {{-- Footer Pagination Tabs --}}
                            <div class="pagination-excel" id="pageTabs">
                                @foreach($pages as $index => $page)
                                    <button class="page-btn {{ $index === 1 ? 'active' : '' }}"
                                            onclick="loadPage({{ $index }}, this)"
                                            data-page="{{ $index }}"
                                            title="{{ $page['title'] }}">
                                        <div class="page-indicator"></div>
                                        <span>{{ $index }}</span>
                                        <span class="d-none d-xl-inline">{{ $page['title'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                    </div>{{-- /.liasse-container --}}

                </div>
                @include('components.footer')
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    var liassePageRoute = "{{ route('reporting.liasse.page', ':page') }}";
    var liasseSaveRoute = "{{ route('reporting.liasse.save') }}";
    var liasseExportRoute = "{{ url('reporting/liasse/export') }}";
    var csrfToken = "{{ csrf_token() }}";

    function loadPage(pageNumber, element) {
        $('.page-btn').removeClass('active');
        $(element).addClass('active');
        $('#currentPageNumber').val(pageNumber);
        $('#loadingOverlay').css('display', 'flex');

        $.ajax({
            url: liassePageRoute.replace(':page', pageNumber),
            method: 'GET',
            success: function(response) {
                $('#pageContent').html(response.html);
                $('#currentPageTitle').text(response.title);
                $('#currentPageCode').val(response.code);
                $('#loadingOverlay').hide();
                $('.canvas-body').scrollTop(0);

                // Update summary pills if available
                if (response.summary) {
                    if (response.summary.total_actif !== undefined) $('#sumActif').text(formatNumber(response.summary.total_actif));
                    if (response.summary.total_passif !== undefined) $('#sumPassif').text(formatNumber(response.summary.total_passif));
                    if (response.summary.resultat_net !== undefined) $('#sumResultat').text(formatNumber(response.summary.resultat_net));
                }
            },
            error: function(xhr) {
                $('#loadingOverlay').hide();
                $('#pageContent').html('<div class="alert alert-warning m-5 text-center p-5 rounded-4 shadow-sm border-0"><i class="bx bx-error-circle fs-1 text-warning mb-3"></i><br>Cette page est en cours de développement.<br><small class="text-muted">' + (xhr.responseJSON ? xhr.responseJSON.error : '') + '</small></div>');
                $('#currentPageTitle').text('Page ' + pageNumber + ' (en développement)');
            }
        });
    }

    function formatNumber(num) {
        return new Intl.NumberFormat('fr-FR').format(num);
    }

    function savePageData() {
        var pageCode = $('#currentPageCode').val();
        var formData = {};

        $('.liasse-input').each(function() {
            formData[$(this).attr('name')] = $(this).val();
        });

        if (Object.keys(formData).length === 0) {
            showToast('Aucune donnée saisissable sur cette page.', 'warning');
            return;
        }

        $('#loadingOverlay').css('display', 'flex');

        $.ajax({
            url: liasseSaveRoute,
            method: 'POST',
            data: { _token: csrfToken, page_code: pageCode, data: formData },
            success: function() {
                $('#loadingOverlay').hide();
                showToast('Données enregistrées avec succès.', 'success');
            },
            error: function() {
                $('#loadingOverlay').hide();
                showToast("Erreur lors de l'enregistrement.", 'danger');
            }
        });
    }

    function exportPage(format) {
        var page = $('#currentPageCode').val();
        window.location.href = liasseExportRoute + '/' + format + '?page=' + page;
    }

    function showToast(message, type) {
        var toast = $('<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100"><div class="toast show align-items-center text-white bg-' + type + ' border-0" role="alert"><div class="d-flex"><div class="toast-body">' + message + '</div></div></div></div>');
        $('body').append(toast);
        setTimeout(function() { toast.remove(); }, 3000);
    }

    // Load first page on start
    $(document).ready(function() {
        var firstBtn = $('.page-btn[data-page="1"]');
        if (firstBtn.length) {
            loadPage(1, firstBtn[0]);
        }
    });
</script>
</body>
</html>
