<!doctype html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">

@include('components.head')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

<style>
    :root {
        --liasse-primary: #1e40af;
        --liasse-bg: #f8fafc;
        --liasse-card-bg: rgba(255, 255, 255, 0.95);
    }

    body {
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        background-color: var(--liasse-bg);
    }

    .liasse-container {
        display: flex;
        flex-direction: column;
        height: calc(100vh - 120px);
        gap: 1rem;
    }

    .toolbar-premium {
        background: var(--liasse-card-bg);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 16px;
        padding: 0.85rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
    }

    .excel-canvas {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
    }

    .canvas-header {
        background: #f1f5f9;
        padding: 0.65rem 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-shrink: 0;
    }

    .canvas-body {
        flex: 1;
        overflow: auto;
        padding: 1.5rem;
        position: relative;
    }

    .pagination-excel {
        display: flex;
        gap: 0.4rem;
        padding: 0.75rem 1rem;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        overflow-x: auto;
        white-space: nowrap;
        flex-shrink: 0;
    }

    .page-btn {
        padding: 0.4rem 1rem;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        white-space: nowrap;
    }

    .page-btn:hover {
        background: #f1f5f9;
        border-color: var(--liasse-primary);
        color: var(--liasse-primary);
    }

    .page-btn.active {
        background: var(--liasse-primary);
        color: white;
        border-color: var(--liasse-primary);
        box-shadow: 0 4px 12px rgba(30, 64, 175, 0.2);
    }

    .page-indicator {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #cbd5e1;
        flex-shrink: 0;
    }

    .page-btn.active .page-indicator { background: rgba(255,255,255,0.6); }

    .btn-export {
        padding: 0.4rem 0.85rem;
        border-radius: 10px;
        font-size: 0.78rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        transition: all 0.2s;
        cursor: pointer;
        border: none;
    }

    .btn-pdf { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    .btn-excel { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .btn-xml { background: #fef9c3; color: #854d0e; border: 1px solid #fef08a; }

    .btn-export:hover { transform: translateY(-1px); box-shadow: 0 4px 10px rgba(0,0,0,0.08); }

    #loadingOverlay {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(255,255,255,0.8);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 100;
        backdrop-filter: blur(2px);
    }

    /* Liasse table styles */
    .liasse-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
    .liasse-table th { background: #1e293b; color: white; border: 1px solid #334155; padding: 8px 10px; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.04em; }
    .liasse-table td { border: 1px solid #e2e8f0; padding: 6px 10px; }
    .row-section { background: #eff6ff; font-weight: 800; color: #1e40af; }
    .row-total { background: #f8fafc; font-weight: 700; border-top: 2px solid #1e40af !important; }
    .col-code { width: 50px; text-align: center; background: #f8fafc; font-weight: 700; color: #64748b; font-size: 0.75rem; }
    .col-val { width: 140px; text-align: right; font-weight: 600; }
    .col-val-net { background: #eff6ff; }
    .liasse-input { width: 100%; border: 1px solid #e2e8f0; padding: 3px 6px; text-align: right; font-family: inherit; border-radius: 4px; transition: all 0.2s; font-size: 0.85rem; }
    .liasse-input:focus { border-color: #1e40af; outline: none; box-shadow: 0 0 0 2px rgba(30,64,175,0.1); }
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
                            <div>
                                <h5 class="fw-800 mb-0 text-dark">Liasse Fiscale <span class="text-primary">DGI</span></h5>
                                <p class="text-muted small mb-0">Exercice : <strong>{{ $exercice->intitule }}</strong> &nbsp;|&nbsp; SYSCOHADA Révisé</p>
                            </div>

                            <div class="d-flex gap-2">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-primary dropdown-toggle rounded-pill px-3" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-export me-1"></i> Toutes les pages
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="{{ route('reporting.liasse.export', 'pdf') }}"><i class="bx bxs-file-pdf text-danger me-2"></i>Exporter Tout (PDF)</a></li>
                                        <li><a class="dropdown-item" href="{{ route('reporting.liasse.export', 'excel') }}"><i class="bx bxs-file-json text-success me-2"></i>Exporter Tout (Excel)</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="{{ route('reporting.liasse.export', 'xml') }}"><i class="bx bxs-file-xml text-warning me-2"></i>Générer XML e-SINTAX</a></li>
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
            },
            error: function(xhr) {
                $('#loadingOverlay').hide();
                $('#pageContent').html('<div class="alert alert-warning m-3">Cette page est en cours de développement.<br><small>' + (xhr.responseJSON ? xhr.responseJSON.error : '') + '</small></div>');
                $('#currentPageTitle').text('Page ' + pageNumber + ' (en développement)');
            }
        });
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
        var page = $('#currentPageNumber').val();
        window.location.href = liasseExportRoute + '/' + format + '?page=' + page;
    }

    function showToast(message, type) {
        var toast = $('<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100"><div class="toast show align-items-center text-white bg-' + type + ' border-0" role="alert"><div class="d-flex"><div class="toast-body">' + message + '</div></div></div></div>');
        $('body').append(toast);
        setTimeout(function() { toast.remove(); }, 3000);
    }

    // Load first page on start
    $(document).ready(function() {
        loadPage(1, $('.page-btn[data-page="1"]')[0]);
    });
</script>
</body>
</html>
