@include('components.head')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap');

    body {
        background-color: #f8fafc;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .mapper-card {
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.05);
    }

    .mapping-row {
        border-bottom: 1px solid #f1f5f9;
        padding: 1.25rem;
        transition: all 0.2s ease;
    }

    .mapping-row:hover {
        background: #f8fafc;
    }

    .field-label {
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .field-required::after {
        content: "*";
        color: #ef4444;
        margin-left: 4px;
    }

    .select-mapping {
        border-radius: 12px;
        border: 1px solid #cbd5e1;
        padding: 0.6rem 1rem;
        font-size: 0.875rem;
        width: 100%;
        transition: all 0.2s ease;
    }

    .select-mapping:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .step-indicator {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.875rem;
    }
</style>

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Importation / <span class="text-primary">' . $importTitle . '</span>'])

                <div class="content-wrapper">
                    <div class="container-xxl flex-grow-1 container-p-y">
                        
                        <div class="row mb-6">
                            <div class="col-12">
                                <div class="bg-white p-6 rounded-[24px] shadow-sm d-flex align-items-center justify-content-between border border-slate-100">
                                    <div class="d-flex align-items-center gap-4">
                                        <div class="step-indicator bg-primary text-white">2</div>
                                        <div>
                                            <h4 class="font-black mb-1 text-slate-900">{{ $importTitle }} : Mappage</h4>
                                            <p class="text-slate-500 mb-0">Associez les colonnes de votre fichier <strong>{{ $import->file_name }}</strong> aux champs de ComptaFlow.</p>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <div class="badge bg-label-info rounded-pill px-3">Source: {{ strtoupper($import->source) }}</div>
                                        <div class="badge bg-label-primary rounded-pill px-3">Type: {{ ucfirst($import->type) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-8">
                                <form action="{{ route('admin.import.process_mapping', $import->id) }}" method="POST">
                                    @csrf
                                    <div class="mapper-card overflow-hidden">
                                        <div class="bg-slate-50 p-4 border-bottom border-slate-200">
                                            <div class="row">
                                                <div class="col-md-5"><span class="text-xs font-bold text-slate-400 uppercase">Champ ComptaFlow</span></div>
                                                <div class="col-md-7"><span class="text-xs font-bold text-slate-400 uppercase">Colonne correspondante dans votre fichier</span></div>
                                            </div>
                                        </div>

                                        @foreach($fields as $key => $field)
                                            <div class="mapping-row">
                                                <div class="row align-items-center">
                                                    <div class="col-md-5">
                                                        <div class="field-label @if($field['required'] ?? false) field-required @endif">
                                                            <div class="w-8 h-8 rounded-lg bg-slate-100 d-flex align-items-center justify-content-center">
                                                                 <i class="fa-solid {{ $field['icon'] }} text-slate-500 text-xs"></i>
                                                             </div>
                                                             {{ $field['label'] }}
                                                             @if($field['auto_generate'] ?? false)
                                                                 <span class="badge bg-label-secondary ms-2 text-[10px]">AUTO-GÉNÉRÉ PAR DÉFAUT</span>
                                                             @endif
                                                         </div>
                                                     </div>
                                                     <div class="col-md-7">
                                                         <select name="mapping[{{ $key }}]" class="select-mapping" @if($field['required'] ?? false) required @endif>
                                                             <option value="">-- Ignorer cette colonne --</option>
                                                             @foreach($headers as $index => $header)
                                                                 @php
                                                                     $selected = false;
                                                                     $cleanHeader = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $header)));
                                                                     foreach(($field['match'] ?? []) as $m) {
                                                                         if(str_contains($cleanHeader, $m)) {
                                                                             $selected = true;
                                                                             break;
                                                                         }
                                                                     }
                                                                 @endphp
                                                                 <option value="{{ $index }}" {{ $selected ? 'selected' : '' }}>
                                                                     Colonne {{ $index + 1 }} : {{ $header }}
                                                                 </option>
                                                             @endforeach
                                                         </select>
                                                     </div>
                                                 </div>
                                             </div>
                                         @endforeach

                                        <div class="p-6 bg-slate-50 text-end">
                                            <a href="{{ route('admin.import.hub') }}" class="btn btn-label-secondary border-0 me-2 rounded-xl px-6">Annuler</a>
                                            <button type="submit" class="btn btn-primary rounded-xl px-10 py-3 font-bold shadow-lg shadow-primary/20">
                                                Valider le mappage <i class="fa-solid fa-arrow-right ms-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="col-lg-4">
                                <div class="bg-slate-900 text-white p-8 rounded-[32px] shadow-xl sticky-top" style="top: 100px;">
                                    <h5 class="font-black text-white mb-6">Aperçu du fichier (10 premières lignes)</h5>
                                    <div class="table-responsive" style="max-height: 500px;">
                                        <table class="table table-sm table-dark table-bordered border-slate-700 opacity-90" style="font-size: 0.65rem;">
                                            <thead>
                                                <tr class="bg-slate-800">
                                                    @foreach($headers as $idx => $header)
                                                        <th class="text-blue-300 px-2 py-2">
                                                            <div class="text-[9px] text-slate-500 uppercase">Col {{ $idx + 1 }}</div>
                                                            {{ \Illuminate\Support\Str::limit($header, 15) }}
                                                        </th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach(array_slice($import->raw_data, ($import->mapping['_header_index'] ?? 0) + 1, 10) as $row)
                                                    <tr>
                                                        @foreach($headers as $idx => $header)
                                                            <td class="px-2 py-1 @if(empty($row[$idx])) text-slate-600 @endif">
                                                                {{ \Illuminate\Support\Str::limit($row[$idx] ?? '-', 20) }}
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-4 text-center">
                                        <button type="button" id="toggleDatesBtn" class="btn btn-sm btn-outline-light" onclick="toggleDateDisplay()">
                                            <i class="fa-solid fa-calendar me-1"></i> Afficher les dates
                                        </button>
                                    </div>
                                    <p class="text-blue-200 text-xs mt-6 mb-0">
                                        <i class="fa-solid fa-circle-info me-1"></i> 
                                        Les 10 premières lignes sont affichées à titre d'exemple pour vous aider dans le mappage.
                                    </p>
                                </div>
                                <script>
                                    let datesConverted = false;

                                    function toggleDateDisplay() {
                                        const btn = document.getElementById('toggleDatesBtn');
                                        
                                        if (!datesConverted) {
                                            convertAllDates();
                                            btn.innerHTML = '<i class="fa-solid fa-hashtag me-1"></i> Afficher les codes';
                                            datesConverted = true;
                                        } else {
                                            restoreAllDates();
                                            btn.innerHTML = '<i class="fa-solid fa-calendar me-1"></i> Afficher les dates';
                                            datesConverted = false;
                                        }
                                    }

                                    function convertAllDates() {
                                        const cells = document.querySelectorAll('.table-dark tbody td');
                                        cells.forEach(cell => {
                                            const originalValue = cell.innerText.trim();
                                            
                                            if (!cell.hasAttribute('data-original')) {
                                                cell.setAttribute('data-original', originalValue);
                                            }

                                            if (isNumeric(originalValue)) {
                                                const num = parseFloat(originalValue);
                                                if (num >= 30000 && num <= 60000) {
                                                    const dateStr = excelDateToJSDate(num);
                                                    cell.innerHTML = `<span class="text-success fw-bold">${dateStr}</span>`;
                                                }
                                            }
                                        });
                                    }

                                    function restoreAllDates() {
                                        const cells = document.querySelectorAll('.table-dark tbody td[data-original]');
                                        cells.forEach(cell => {
                                            cell.innerHTML = cell.getAttribute('data-original');
                                        });
                                    }

                                    function isNumeric(n) {
                                        return !isNaN(parseFloat(n)) && isFinite(n);
                                    }

                                    function excelDateToJSDate(serial) {
                                        const totalSeconds = (serial - 25569) * 86400;
                                        const date = new Date(totalSeconds * 1000);
                                        const offset = date.getTimezoneOffset() * 60 * 1000;
                                        const finalDate = new Date(date.getTime() + offset); 

                                        const day = String(finalDate.getDate()).padStart(2, '0');
                                        const month = String(finalDate.getMonth() + 1).padStart(2, '0');
                                        const year = finalDate.getFullYear();

                                        return `${day}/${month}/${year}`;
                                    }
                                </script>
                            </div>
                        </div>

                    </div>
                    @include('components.footer')
                </div>
            </div>
        </div>
    </div>
</body>
</html>
