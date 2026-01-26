<div class="modal fade" id="modalImportInstructions" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-2xl rounded-3xl overflow-hidden">
            <div class="modal-header bg-slate-900 p-6">
                <h5 class="modal-title text-white font-black"><i class="fa-solid fa-circle-info me-2"></i> Guide d'Importation : Plan Tiers</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-8">
                <div class="bg-indigo-50 p-6 rounded-2xl mb-6">
                    <h6 class="font-black text-indigo-900 mb-2">Structure attendue (Colonnes)</h6>
                    <p class="text-sm text-indigo-700 mb-4">Le <strong>Numéro de Tiers</strong> est généré automatiquement par le système. Vous n'avez pas besoin de le fournir.</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered bg-white">
                            <thead>
                                <tr>
                                    <th class="bg-indigo-100 text-muted">Numéro Tiers (Auto)</th>
                                    <th class="bg-indigo-100">Nom / Intitulé</th>
                                    <th class="bg-indigo-100">Compte général</th>
                                    <th class="bg-indigo-100">Catégorie</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td class="text-muted italic">Généré par Flow</td><td>ETS KOUASSI & FILS</td><td>401100</td><td>Fournisseur</td></tr>
                                <tr><td class="text-muted italic">Généré par Flow</td><td>BOUTIQUE DU PLATEAU</td><td>411100</td><td>Client</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="alert alert-warning border-0 text-sm font-bold">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>
                    Le compte général doit exister dans votre plan comptable avant l'importation des tiers.
                </div>
            </div>
            <div class="modal-footer bg-slate-50 p-6 border-0">
                <button type="button" class="btn btn-primary font-black px-8 py-3 rounded-xl" data-bs-dismiss="modal">J'ai compris</button>
            </div>
        </div>
    </div>
</div>
