<div class="modal fade" id="modalImportInstructions" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden">
            <div class="modal-header bg-slate-900 p-6">
                <h5 class="modal-title text-white font-black"><i class="fa-solid fa-circle-info me-2"></i> Guide d'Importation : Plan Comptable</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-8">
                <div class="bg-blue-50 p-6 rounded-2xl mb-6">
                    <h6 class="font-black text-blue-900 mb-2">Structure attendue (Colonnes)</h6>
                    <p class="text-sm text-blue-700 mb-4">Les colonnes peuvent être dans n'importe quel ordre. Le système identifiera automatiquement les intitulés.</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered bg-white">
                            <thead>
                                <tr>
                                    <th class="bg-blue-100">N° comptes</th>
                                    <th class="bg-blue-100">Intitulé</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>601000</td><td>ACHATS DE MARCHANDISES</td></tr>
                                <tr><td>701000</td><td>VENTES DE MARCHANDISES</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="alert alert-info border-0 text-sm">
                    <i class="fa-solid fa-lightbulb me-2"></i>
                    <strong>Intelligence :</strong> Vous pouvez importer des fichiers sans titres de colonnes. Le système analysera le contenu pour faire correspondre les données. Retirez les lignes de totaux ou informations inutiles du fichier.
                </div>
            </div>
            <div class="modal-footer bg-slate-50 p-6 border-0">
                <button type="button" class="btn btn-primary font-black px-8 py-3 rounded-xl" data-bs-dismiss="modal">J'ai compris</button>
            </div>
        </div>
    </div>
</div>
