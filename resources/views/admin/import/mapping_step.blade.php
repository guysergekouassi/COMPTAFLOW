@include('components.head')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@200;300;400;500;600;700;800&display=swap');
    body { font-family: 'Outfit', sans-serif; background-color: #f8fafc; }
    
    .cursor-pointer { cursor: pointer; }
    .card-filter:hover { transform: translateY(-2px); transition: all 0.2s; }
    .card-filter.active { ring: 2px; ring-color: #000; }
    
    .table-container { max-height: 600px; overflow-y: auto; }
    thead th { position: sticky; top: 0; z-index: 10; background-color: #f9fafb; }
</style>
<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Validation <span class="text-blue-600">Import</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Import /</span> Rapport de Validation</h4>
                        </div>

                        <!-- STATS CARDS (Clickable Filters) -->
                        <div class="row g-4 mb-4">
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm rounded-xl card-filter cursor-pointer bg-green-50 border-start border-4 border-green-500" onclick="filterTable('valid')">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="text-xs font-bold text-green-600 uppercase tracking-wider mb-1">Lignes Valides</div>
                                                <h2 class="mb-0 font-black text-green-700 display-6">{{ $valid_count }}</h2>
                                            </div>
                                            <div class="bg-green-100 p-3 rounded-full text-green-600">
                                                <i class="fa-solid fa-check-circle fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-0 shadow-sm rounded-xl card-filter cursor-pointer bg-red-50 border-start border-4 border-red-500" onclick="filterTable('error')">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="text-xs font-bold text-red-600 uppercase tracking-wider mb-1">Erreurs Détectées</div>
                                                <h2 class="mb-0 font-black text-red-700 display-6">{{ $error_count }}</h2>
                                            </div>
                                            <div class="bg-red-100 p-3 rounded-full text-red-600">
                                                <i class="fa-solid fa-triangle-exclamation fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('admin.import.process') }}" method="POST">
                            @csrf
                            <input type="hidden" name="batch_id" value="{{ $batch_id }}">
                            <input type="hidden" name="type" value="{{ $type }}">

                             <!-- MAPPING CONFIG (Collapsed by default if valid?) No, kept visible for changes -->
                             <div class="card rounded-xl border-0 shadow-sm mb-4">
                                <div class="card-header bg-white border-bottom py-3">
                                    <h5 class="mb-0 font-bold text-slate-700"><i class="fa-solid fa-sliders me-2 text-blue-600"></i>Configuration du Mapping</h5>
                                </div>
                                <div class="card-body p-4 bg-slate-50">
                                    <div class="row g-3">
                                        @php
                                            $targets = [];
                                            if($type == 'accounts') $targets = ['numero_de_compte' => 'Numéro *', 'intitule' => 'Intitulé *', 'classe' => 'Classe'];
                                            if($type == 'journals') $targets = ['code_journal' => 'Code *', 'intitule' => 'Intitulé *'];
                                            if($type == 'tiers') $targets = ['numero_de_tiers' => 'Numéro *', 'intitule' => 'Nom *', 'compte_general' => 'Compte Coll.'];
                                            if($type == 'entries') $targets = ['date_ecriture' => 'Date *', 'code_journal' => 'Jnl *', 'numero_compte' => 'Compte *', 'libelle' => 'Libellé *', 'debit' => 'Débit', 'credit' => 'Crédit', 'piece_ref' => 'Ref', 'type_ecriture' => 'Type (G/A)'];
                                        @endphp
                        
                                        @foreach($targets as $field => $label)
                                            <div class="col-md-3"> 
                                                <label class="form-label text-xs font-bold text-slate-500 uppercase">{{ $label }}</label>
                                                <select name="mapping[{{ $field }}]" class="form-select form-select-sm rounded-lg border-slate-200">
                                                    <option value="">(Ignorer)</option>
                                                    @foreach($headers as $header)
                                                        @php $isSelected = (isset($proposal[$field]) && $proposal[$field] === $header); @endphp
                                                        <option value="{{ $header }}" {{ $isSelected ? 'selected' : '' }}>{{ $header }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="mt-3 text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.location.reload()">
                                            <i class="fa-solid fa-rotate me-1"></i> Recalculer la validation (Si changement)
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- DATA TABLE -->
                            <div class="card rounded-xl border-0 shadow-sm">
                                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-3">
                                        <h5 class="mb-0 font-bold text-slate-700">Données Importées</h5>
                                        <input type="text" id="tableSearch" class="form-control form-control-sm" placeholder="Rechercher..." style="width: 200px;" onkeyup="searchTable()">
                                    </div>
                                    <div>
                                        <button type="button" id="toggleDatesBtn" class="btn btn-sm btn-outline-primary me-2" onclick="toggleDateDisplay()">
                                            <i class="fa-solid fa-calendar me-1"></i> Afficher les dates
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light border text-slate-500" onclick="filterTable('all')">Tout voir</button>
                                    </div>
                                </div>
                                <div class="table-container text-nowrap">
                                    <table class="table table-hover mb-0" id="validationTable">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="ps-4" width="50">Status</th>
                                                <th width="200">Rapport</th>
                                                @foreach($headers as $h)
                                                    <th class="text-xs uppercase font-bold text-slate-500">{{ $h }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($staging_rows as $row)
                                                @php 
                                                    $data = is_string($row->raw_data) ? json_decode($row->raw_data, true) : $row->raw_data;
                                                    $statusClass = $row->status == 'valid' ? 'row-valid' : 'row-error bg-red-50';
                                                    $icon = $row->status == 'valid' ? '<i class="fa-solid fa-check text-green-500"></i>' : '<i class="fa-solid fa-times text-red-500"></i>';
                                                @endphp
                                                <tr class="align-middle {{ $statusClass }}">
                                                    <td class="ps-4 text-center">{!! $icon !!}</td>
                                                    <td>
                                                        @if($row->status == 'error')
                                                            <span class="badge bg-red-100 text-red-600 rounded-pill text-wrap text-start" style="font-size: 0.75rem; white-space: normal;">
                                                                {{ $row->error_log ?? 'Erreur Inconnue' }}
                                                            </span>
                                                        @else
                                                            <span class="badge bg-green-100 text-green-600 rounded-pill">OK</span>
                                                        @endif
                                                    </td>
                                                    @foreach($data as $cell)
                                                        <td class="text-sm text-slate-600">{{ \Illuminate\Support\Str::limit($cell, 40) }}</td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="card-footer bg-white border-top p-4 text-end">
                                    <a href="{{ route('admin.config.external_import') }}" class="btn btn-outline-secondary me-2 rounded-lg font-bold px-4">
                                        <i class="fa-solid fa-times me-2"></i>Annuler
                                    </a>
                                    @if($error_count == 0)
                                        <button type="submit" class="btn btn-success btn-lg rounded-lg font-bold px-6 shadow-lg shadow-green-500/30 text-white">
                                            <i class="fa-solid fa-file-import me-2"></i> IMPORTER DONNÉES
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-danger btn-lg rounded-lg font-bold px-6 opacity-50 cursor-not-allowed" disabled>
                                            <i class="fa-solid fa-ban me-2"></i> CORRIGER LES ERREURS
                                        </button>
                                        <div class="mt-2 text-xs text-red-500 font-bold">L'import est bloqué tant qu'il y a des erreurs.</div>
                                    @endif
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let datesConverted = false;

        function toggleDateDisplay() {
            const btn = document.getElementById('toggleDatesBtn');
            
            if (!datesConverted) {
                convertAllDates();
                btn.innerHTML = '<i class="fa-solid fa-hashtag me-1"></i> Afficher les codes';
                btn.classList.remove('btn-outline-primary');
                btn.classList.add('btn-primary');
                datesConverted = true;
            } else {
                restoreAllDates();
                btn.innerHTML = '<i class="fa-solid fa-calendar me-1"></i> Afficher les dates';
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-outline-primary');
                datesConverted = false;
            }
        }

        function convertAllDates() {
            const rows = document.querySelectorAll('#validationTable tbody tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                cells.forEach(cell => {
                    const originalValue = cell.innerText.trim();
                    
                    // Save original if not already saved
                    if (!cell.hasAttribute('data-original')) {
                        cell.setAttribute('data-original', originalValue);
                    }

                    // Check if it looks like an Excel Serial (Number > 30000 and < 60000)
                    if (isNumeric(originalValue)) {
                        const num = parseFloat(originalValue);
                        if (num >= 30000 && num <= 60000) {
                            const dateStr = excelDateToJSDate(num);
                            cell.innerHTML = `<span class="fw-bold text-primary">${dateStr}</span>`;
                        }
                    }
                });
            });
        }

        function restoreAllDates() {
            const cells = document.querySelectorAll('#validationTable td[data-original]');
            cells.forEach(cell => {
                cell.innerHTML = cell.getAttribute('data-original');
            });
        }

        function isNumeric(n) {
            return !isNaN(parseFloat(n)) && isFinite(n);
        }

        function excelDateToJSDate(serial) {
            // Excel serial to JavaScript Date
            // Excel base: 1900-01-01 (with leap year bug adjustment)
            const totalSeconds = (serial - 25569) * 86400;
            const date = new Date(totalSeconds * 1000);
            
            // Fix timezone offset
            const offset = date.getTimezoneOffset() * 60 * 1000;
            const finalDate = new Date(date.getTime() + offset); 

            const day = String(finalDate.getDate()).padStart(2, '0');
            const month = String(finalDate.getMonth() + 1).padStart(2, '0');
            const year = finalDate.getFullYear();

            return `${day}/${month}/${year}`;
        }

        function filterTable(type) {
            const rows = document.querySelectorAll('#validationTable tbody tr');
            rows.forEach(row => {
                if (type === 'all') {
                    row.style.display = '';
                } else if (type === 'valid') {
                    row.style.display = row.classList.contains('row-valid') ? '' : 'none';
                } else if (type === 'error') {
                    row.style.display = row.classList.contains('row-error') ? '' : 'none';
                }
            });
        }

        function searchTable() {
            const input = document.getElementById('tableSearch');
            const filter = input.value.toLowerCase();
            const rows = document.querySelectorAll('#validationTable tbody tr');

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        }
    </script>
</body>
