<!DOCTYPE html>
<html lang="fr" class="layout-menu-fixed layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free">
@include('components.head')

<body>
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            @include('components.sidebar')
            <div class="layout-page">
                @include('components.header', ['page_title' => 'Détails <span class="text-gradient">Analyse IA</span>'])
                <div class="content-wrapper">
<div class="px-4 py-4" style="max-width: 1400px; margin: 0 auto; padding-top: 40px !important;">
    
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('excel_ia.historique') }}" class="text-decoration-none text-muted mb-2 d-inline-block">
                <i class="fas fa-arrow-left"></i> Retour à l'historique
            </a>
            <h2 class="fw-bold fs-3 text-dark mb-0">Détails de l'analyse</h2>
        </div>
        <div class="d-flex gap-2">
            @if(!$analyse->injecte_bdd)
            <form id="injectionForm" action="{{ route('excel_ia.injecter_bdd') }}" method="POST" onsubmit="return preparerInjection(this)">
                @csrf
                <input type="hidden" name="analyse_id" value="{{ $analyse->id }}">
                <input type="hidden" name="ecritures_json" id="ecritures_json_input">
                <button type="submit" class="btn btn-success fw-bold shadow-sm rounded-3 px-4">
                    <i class="fas fa-database me-2"></i> Injecter BDD
                </button>
            </form>
            @endif
            <form action="{{ route('excel_ia.export_txt') }}" method="POST">
                @csrf
                <input type="hidden" name="analyse_id" value="{{ $analyse->id }}">
                <button type="submit" class="btn btn-primary fw-bold shadow-sm rounded-3 px-4">
                    <i class="fas fa-download me-2"></i> Télécharger TXT
                </button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        {{-- PANNEAU INFO --}}
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold text-dark"><i class="fas fa-info-circle text-primary me-2"></i> Informations</h5>
                </div>
                <div class="card-body p-4">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3 d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted">Mois cible</span>
                            <span class="fw-bold">{{ $analyse->mois_cible }}</span>
                        </li>
                        <li class="mb-3 d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted">Généré le</span>
                            <span class="fw-bold">{{ $analyse->created_at->format('d/m/Y H:i') }}</span>
                        </li>
                        <li class="mb-3 d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted">Par</span>
                            <span class="fw-bold">{{ $analyse->user->name ?? 'N/A' }}</span>
                        </li>
                        <li class="mb-3 d-flex justify-content-between border-bottom pb-2">
                            <span class="text-muted">Statut DB</span>
                            @if($analyse->injecte_bdd)
                                <span class="badge bg-success">Injecté le {{ $analyse->injecte_le->format('d/m/y H:i') }}</span>
                            @else
                                <span class="badge bg-warning text-dark">Non injecté</span>
                            @endif
                        </li>
                    </ul>

                    <h6 class="fw-bold mt-4 mb-3">Fichiers analysés</h6>
                    <ul class="list-group list-group-flush rounded-3 border">
                        @foreach($analyse->fichiers_noms_array as $fc)
                            <li class="list-group-item bg-light border-bottom px-3 py-2 text-truncate" style="font-size: 0.85rem;">
                                <i class="fas fa-file text-secondary me-2"></i> {{ $fc }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{-- RAPPORT --}}
            @if($analyse->rapport_transparence)
            <div class="card border-0 shadow-sm rounded-4 border-start border-primary border-4">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold text-dark"><i class="fas fa-clipboard-list text-primary me-2"></i> Transparence IA</h5>
                </div>
                <div class="card-body p-4">
                    <pre class="bg-light p-3 rounded-3 text-muted mb-0" style="font-size: 0.8rem; max-height: 300px; overflow-y: auto; white-space: pre-wrap;">{{ $analyse->rapport_transparence }}</pre>
                </div>
            </div>
            @endif
        </div>

        {{-- TABLEAU ECRITURES --}}
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0"><i class="fas fa-list-alt text-primary me-2"></i> Lignes d'écritures ({{ $analyse->nb_ecritures }})</h5>
                    
                    <div class="d-flex gap-3 align-items-center bg-light px-3 py-2 rounded-pill">
                        <div class="text-end">
                            <small class="text-muted d-block lh-1">Total Débit</small>
                            <strong class="text-success" id="total-debit-val">{{ number_format($analyse->total_debit, 0, ',', ' ') }}</strong>
                        </div>
                        <div class="vr"></div>
                        <div class="text-end">
                            <small class="text-muted d-block lh-1">Total Crédit</small>
                            <strong class="text-warning" id="total-credit-val">{{ number_format($analyse->total_credit, 0, ',', ' ') }}</strong>
                        </div>
                        <div class="vr"></div>
                        <div id="status-icon">
                            @if($analyse->equilibre)
                                <i class="fas fa-check-circle text-success fs-4"></i>
                            @else
                                <i class="fas fa-times-circle text-danger fs-4" title="Déséquilibré"></i>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0 text-nowrap" style="font-size: 0.85rem;">
                            <thead class="bg-light" style="position: sticky; top: 0; z-index: 10;">
                                <tr>
                                    <th class="ps-4">Date (JJMMAA)</th>
                                    <th>Facture</th>
                                    <th>JNL</th>
                                    <th>Compte</th>
                                    <th>Libellé</th>
                                    <th class="text-end text-success">Débit</th>
                                    <th class="text-end text-warning">Crédit</th>
                                    <th>Tiers</th>
                                    <th class="pe-4 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($ecritures as $e)
                                    @php
                                        // Formater la date JJMMAA -> format lisible (visuel seulement)
                                        $d = $e['date'] ?? '';
                                        if (strlen($d) === 6) {
                                            $dMsg = substr($d, 0, 2).'/'.substr($d, 2, 2).'/20'.substr($d, 4, 2);
                                        } else {
                                            $dMsg = $d;
                                        }
                                        $deb = (float)($e['debit'] ?? 0);
                                        $cre = (float)($e['credit'] ?? 0);
                                    @endphp
                                <tr class="ecriture-row">
                                    <td class="ps-4" contenteditable="true" data-key="date" oninput="calculateTotals()">{{ $e['date'] ?? '' }}</td>
                                    <td contenteditable="true" data-key="num_facture" oninput="calculateTotals()">{{ $e['num_facture'] ?? '' }}</td>
                                    <td contenteditable="true" data-key="journal" oninput="calculateTotals()"><span class="badge bg-primary bg-opacity-10 text-primary">{{ $e['journal'] ?? '' }}</span></td>
                                    <td contenteditable="true" data-key="compte" oninput="calculateTotals()"><strong class="text-dark">{{ $e['compte'] ?? '' }}</strong></td>
                                    <td contenteditable="true" data-key="libelle" title="{{ $e['libelle'] ?? '' }}" oninput="calculateTotals()">
                                        {{ $e['libelle'] ?? '' }}
                                    </td>
                                    <td contenteditable="true" data-key="debit" class="text-end fw-bold {{ $deb > 0 ? 'text-success' : 'text-muted' }}" oninput="calculateTotals()">{{ $deb > 0 ? number_format($deb, 0, '.', '') : '0' }}</td>
                                    <td contenteditable="true" data-key="credit" class="text-end fw-bold {{ $cre > 0 ? 'text-warning' : 'text-muted' }}" oninput="calculateTotals()">{{ $cre > 0 ? number_format($cre, 0, '.', '') : '0' }}</td>
                                    <td contenteditable="true" data-key="tiers" oninput="calculateTotals()"><small class="text-secondary">{{ $e['tiers'] ?? '' }}</small></td>
                                    <td class="pe-4 text-center">
                                        <button type="button" class="btn btn-link btn-sm text-danger p-0" onclick="this.closest('tr').remove(); calculateTotals();">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
                    <!-- / Content wrapper -->
                </div>
                <!-- / Layout page -->
            </div>

            <div class="layout-overlay layout-menu-toggle"></div>
        </div>
    </div>

    @include('components.footer')

    <script>
    function calculateTotals() {
        let totalDebit = 0;
        let totalCredit = 0;
        
        document.querySelectorAll('.ecriture-row').forEach(row => {
            const debText = row.querySelector('[data-key="debit"]').innerText.replace(/\s+/g, '').replace(',', '.') || '0';
            const creText = row.querySelector('[data-key="credit"]').innerText.replace(/\s+/g, '').replace(',', '.') || '0';
            
            const deb = parseFloat(debText) || 0;
            const cre = parseFloat(creText) || 0;
            
            totalDebit += deb;
            totalCredit += cre;

            // Mettre à jour les couleurs en temps réel
            row.querySelector('[data-key="debit"]').className = `text-end fw-bold ${deb > 0 ? 'text-success' : 'text-muted'}`;
            row.querySelector('[data-key="credit"]').className = `text-end fw-bold ${cre > 0 ? 'text-warning' : 'text-muted'}`;
        });

        // Mise à jour de l'affichage
        document.getElementById('total-debit-val').innerText = new Intl.NumberFormat('fr-FR').format(totalDebit);
        document.getElementById('total-credit-val').innerText = new Intl.NumberFormat('fr-FR').format(totalCredit);

        // Icône d'équilibre
        const statusDiv = document.getElementById('status-icon');
        if (Math.abs(totalDebit - totalCredit) < 0.01) {
            statusDiv.innerHTML = '<i class="fas fa-check-circle text-success fs-4"></i>';
        } else {
            statusDiv.innerHTML = '<i class="fas fa-times-circle text-danger fs-4" title="Déséquilibré"></i>';
        }
    }

    function preparerInjection(form) {
        const ecritures = [];
        document.querySelectorAll('.ecriture-row').forEach(row => {
            ecritures.push({
                date: row.querySelector('[data-key="date"]').innerText.trim(),
                num_facture: row.querySelector('[data-key="num_facture"]').innerText.trim(),
                journal: row.querySelector('[data-key="journal"]').innerText.trim(),
                compte: row.querySelector('[data-key="compte"]').innerText.trim(),
                libelle: row.querySelector('[data-key="libelle"]').innerText.trim(),
                debit: row.querySelector('[data-key="debit"]').innerText.trim().replace(/\s/g, '').replace(',', '.'),
                credit: row.querySelector('[data-key="credit"]').innerText.trim().replace(/\s/g, '').replace(',', '.'),
                tiers: row.querySelector('[data-key="tiers"]').innerText.trim()
            });
        });

        if (ecritures.length === 0) {
            alert("Aucune écriture à injecter.");
            return false;
        }

        document.getElementById('ecritures_json_input').value = JSON.stringify(ecritures);
        return true;
    }
    </script>

</body>
</html>
