@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <h1>Contrats d'intégration COMPTAFLOW</h1>
        <p class="text-muted">Payloads officiels à respecter par SELFLOW, RH-FLOW et les autres applications connectées.</p>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <h5>1. Référentiel</h5>
            <p>Plan comptable, codes journaux et tiers : structure reçue par l'endpoint de référentiel.</p>
            <a class="btn btn-primary" href="{{ route('superadmin.payloads.download', 'referentiel') }}">Télécharger le TXT</a>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5>2. Écritures comptables</h5>
            <p>Format JSON de l'API et rappel du fichier historique à neuf colonnes.</p>
            <a class="btn btn-primary" href="{{ route('superadmin.payloads.download', 'ecritures') }}">Télécharger le TXT</a>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h5>3. Pièces justificatives</h5>
            <p>Contrat cible de rattachement exact des PDF par clé d'opération. Endpoint à implémenter.</p>
            <a class="btn btn-primary" href="{{ route('superadmin.payloads.download', 'pieces') }}">Télécharger le TXT</a>
        </div>
    </div>
</div>
@endsection
