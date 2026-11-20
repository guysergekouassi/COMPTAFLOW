<!doctype html>
<html lang="fr" class="layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">
@include('components.head')
<style>
.apk-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;margin-bottom:18px}
.apk-muted{color:#64748b}.apk-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px}
.apk-actions{display:flex;gap:8px;flex-wrap:wrap}.apk-code{font-family:monospace;overflow-wrap:anywhere}
.apk-checks{display:flex;gap:18px;flex-wrap:wrap}.apk-checks label{font-size:13px}.apk-pair-code{font:600 14px ui-monospace,monospace;overflow-wrap:anywhere;background:#f1f5f9;border-radius:8px;padding:12px}
</style>
<body>
<div class="layout-wrapper layout-content-navbar"><div class="layout-container">
@include('components.sidebar')
<div class="layout-page">
@include('components.header',['page_title'=>'Liaison COMPTA-APK'])
<div class="content-wrapper"><div class="container-xxl flex-grow-1 container-p-y">
<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
 <div><h3 class="fw-bold mb-1">Liaison COMPTA-APK</h3><p class="text-muted mb-0">Registre centralisé des applications, des dossiers autorisés et des accès API.</p></div>
 <div class="btn-group"><a class="btn {{ $type==='maison'?'btn-primary':'btn-outline-primary' }}" href="{{ route('superadmin.liaison_compta.maison') }}">APPLICATION MAISON — LA SUITE FLOW</a><a class="btn {{ $type==='externe'?'btn-primary':'btn-outline-primary' }}" href="{{ route('superadmin.liaison_compta.externe') }}">APPLICATION EXTERNE</a></div>
</div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

@if($type==='maison')
<div class="apk-card">
 <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <div><h5 class="fw-bold mb-1">Applications de la Suite FLOW</h5><p class="apk-muted mb-0">Chaque application possède son propre espace et ses propres dossiers liés.</p></div>
  @if($selectedApplicationId)
   <a class="btn btn-outline-secondary btn-sm" href="{{ route('superadmin.liaison_compta.maison') }}"><i class="fa-solid fa-arrow-left me-1"></i> Toutes les applications</a>
  @endif
 </div>
 <div class="apk-app-grid">
 @forelse($allApps as $houseApp)
  <a class="apk-app-button {{ $selectedApplicationId===$houseApp->id?'active':'' }}" href="{{ route('superadmin.liaison_compta.maison',['application'=>$houseApp->id]) }}">
   <span class="apk-app-symbol"><i class="fa-solid fa-cubes"></i></span>
   <span class="apk-app-name">{{ $houseApp->name }}</span>
   <span class="badge {{ $houseApp->status==='active'?'bg-success':($houseApp->status==='pending'?'bg-warning text-dark':'bg-secondary') }}">{{ strtoupper($houseApp->status) }}</span>
   <span class="apk-app-arrow"><i class="fa-solid fa-chevron-right"></i></span>
  </a>
 @empty
  <p class="text-muted mb-0">Aucune application maison enregistrée.</p>
 @endforelse
 </div>
</div>
@if(!$selectedApplicationId)
<div class="apk-card">
 <h5 class="fw-bold mb-1">Connecter une nouvelle application</h5>
 <p class="apk-muted">Enregistrez l'application une seule fois. Elle apparaîtra ensuite comme un bouton indépendant ci-dessus.</p>
 <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#apkNewAppForm"><i class="fa-solid fa-plus me-1"></i> Ajouter une application</button>
 <div class="collapse mt-3" id="apkNewAppForm">
@endif
@if(session('integration_pairing_code'))
<div class="alert alert-success border-success">
 <h5 class="fw-bold mb-1">Code de liaison à copier dans {{ session("integration_pairing_code.application") }}</h5>
 <p class="mb-2">Ce code est affiché une seule fois. Validité choisie : {{ session("integration_pairing_code.validity_days") }} jours (expiration : {{ session("integration_pairing_code.expires_at") }}).</p>
 <div class="apk-pair-code mb-2" id="newPairingCode">{{ session("integration_pairing_code.code") }}</div>
 <button class="btn btn-sm btn-success" type="button" onclick="navigator.clipboard.writeText(document.getElementById('newPairingCode').textContent.trim())">Copier le code de liaison</button>
</div>
@endif
@if(session('integration_credentials'))
<div class="alert alert-warning border-warning">
 <h5 class="fw-bold">Identifiants affichés une seule fois</h5>
 <p class="mb-1">Enregistrez-les immédiatement dans la configuration sécurisée de l'application cliente. Le secret ne sera plus consultable.</p>
 <div class="mb-2"><strong>Client ID</strong><div class="apk-code">{{ session('integration_credentials.client_id') }}</div></div>
 <div class="mb-2"><strong>Client Secret</strong><div class="apk-code">{{ session('integration_credentials.client_secret') }}</div></div>
 <button class="btn btn-sm btn-dark" type="button" onclick="navigator.clipboard.writeText(@js(session('integration_credentials.client_id')."\n".session('integration_credentials.client_secret')))">Copier les identifiants</button>
</div>
@endif

<div class="apk-card">
 <h5 class="fw-bold mb-2">{{ $type==='maison'?'Connecter une application de la Suite FLOW':'Enregistrer une application externe' }}</h5>
 <p class="apk-muted mb-3">{{ $type==='maison'?'Indiquez son URL. COMPTAFLOW génère ensuite un code à copier dans l’application partenaire.':'Renseignez l’application partenaire ; COMPTAFLOW lui attribuera ses identifiants et son dossier dédié.' }}</p>
 <form method="POST" action="{{ route('superadmin.liaison_compta.applications.store') }}">@csrf
 <input type="hidden" name="type" value="{{ $type }}">
 <div class="apk-grid">
  <div><label class="form-label">Nom de l’application *</label><input class="form-control" name="name" required maxlength="160" placeholder="{{ $type==='maison'?'Ex. RH-FLOW':'Ex. Partenaire API' }}"></div>
  <div><label class="form-label">URL de l’application *</label><input class="form-control" type="url" name="app_url" required maxlength="500" placeholder="https://rhflow.dc-knowing.com"></div>
  <div><label class="form-label">Propriétaire / éditeur</label><input class="form-control" name="owner_name" {{ $type==='externe'?'required':'' }} placeholder="Organisation / éditeur"></div>
  <div><label class="form-label">E-mail propriétaire</label><input class="form-control" type="email" name="owner_email"></div>
  <div><label class="form-label">Description</label><input class="form-control" name="description" maxlength="2000"></div>
 </div><button class="btn btn-primary mt-3">{{ $type==='maison'?'Enregistrer et générer le code de liaison':'Enregistrer et générer les identifiants API' }}</button>
 </form>
</div>
@if($type==='maison')
 </div>
@endif

@if($type==='externe')
<div class="alert alert-info">Une application externe ne peut être liée qu’à un seul dossier COMPTAFLOW dédié. Elle ne reçoit jamais la liste globale des entreprises et ne peut pas changer de dossier dans ses payloads.</div>
@else
<div class="alert alert-info">Les applications maison peuvent être liées à plusieurs dossiers, mais chaque jeton Bearer est limité à un seul dossier autorisé. SELFLOW reste affiché en mode historique pendant sa migration ; RH-FLOW est référencé comme prévu, sans connecteur déclaré opérationnel.</div>
@endif

@if($type==='externe')
<div class="apk-card">
 <h5 class="fw-bold mb-2">Demandes de création reçues</h5>
 <p class="apk-muted small">Les applications externes demandent un nouveau dossier. COMPTAFLOW ne leur permet jamais de choisir une entreprise existante. Après vérification, renseignez l’exercice comptable puis acceptez la demande.</p>
 @forelse($companyRequests as $companyRequest)
 <div class="border rounded p-3 mb-3">
  <div class="d-flex justify-content-between flex-wrap gap-2">
   <div><strong>{{ $companyRequest->company_name }}</strong><div class="small text-muted">Application : {{ $companyRequest->application_name }} · Référence : {{ $companyRequest->external_request_id }}</div><div class="small">Gérant : {{ $companyRequest->manager_name }} · {{ $companyRequest->manager_email }}</div><div class="small">Activité : {{ $companyRequest->activity ?: 'À préciser' }} · Forme : {{ $companyRequest->legal_form ?: 'À préciser' }}</div></div>
   <span class="badge bg-warning text-dark">EN ATTENTE</span>
  </div>
  <form method="POST" action="{{ route('superadmin.liaison_compta.requests.approve',$companyRequest->id) }}" class="apk-grid mt-3">@csrf
   <div><label class="form-label">Début de l’exercice *</label><input class="form-control" type="date" name="exercice_debut" required></div>
   <div><label class="form-label">Fin de l’exercice *</label><input class="form-control" type="date" name="exercice_fin" required></div>
   <div><label class="form-label">Intitulé de l’exercice</label><input class="form-control" name="exercice_intitule" placeholder="Exercice 2026"></div>
   <div class="d-flex align-items-end"><button class="btn btn-success w-100" onclick="return confirm('Créer le dossier COMPTAFLOW dédié et envoyer le lien d’activation au gérant ?')">Accepter et créer le dossier</button></div>
  </form>
  <form method="POST" action="{{ route('superadmin.liaison_compta.requests.reject',$companyRequest->id) }}" class="d-flex gap-2 mt-2">@csrf<input class="form-control" name="rejection_reason" required maxlength="1000" placeholder="Motif du rejet"><button class="btn btn-outline-danger">Rejeter</button></form>
 </div>
 @empty
 <p class="text-muted mb-0">Aucune demande en attente.</p>
 @endforelse
</div>
@endif

@if($type==='externe')
<h5 class="fw-bold mb-3">Applications enregistrées</h5>
@endif
@if($type==='maison' && $selectedApplicationId)
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3"><div><h4 class="fw-bold mb-1">{{ $apps->first()->name }}</h4><p class="text-muted mb-0">Paramètres et dossiers associés à cette application uniquement.</p></div></div>
@endif
@forelse($apps as $app)
<div class="apk-card">
 <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
  <div><h5 class="mb-1">{{ $app->name }}</h5><div class="apk-muted small">Code : {{ $app->code }} · Propriétaire : {{ $app->owner_name ?: 'Suite Flow' }}</div></div>
  <div class="apk-actions">
   <span class="badge {{ $app->status==='active'?'bg-success':($app->status==='pending'?'bg-warning text-dark':'bg-secondary') }}">{{ strtoupper($app->status) }}</span>
   <span class="badge bg-label-info">{{ strtoupper($app->auth_mode) }}</span>
  </div>
 </div>
 @if($app->description)<p class="apk-muted">{{ $app->description }}</p>@endif
 @if($app->client_id)<div class="small mb-2"><strong>Identifiant application :</strong> #{{ $app->id }} · <strong>Client ID :</strong> <span class="apk-code">{{ $app->client_id }}</span> · <strong>Expiration secret :</strong> {{ $app->client_secret_expires_at ?: '—' }}</div>@endif
 @if($app->app_url)<div class="small apk-muted mb-2"><strong>URL partenaire :</strong> {{ $app->app_url }}</div>@endif
 <div class="mb-3"><strong>Liaisons actives</strong>
 @forelse($app->links->where('status','active') as $link)
  @php($perms=json_decode($link->permissions,true) ?: [])
  <div class="d-flex justify-content-between align-items-center flex-wrap border rounded p-2 mt-2 gap-2">
   <div><strong>{{ $link->company_name }}</strong> <span class="text-muted small">(#{{ $link->company_id }})</span><div class="small text-muted">Partenaire : {{ $link->partner_company_name ?: 'Non associé' }} @if($link->partner_company_id) (#{{ $link->partner_company_id }}) @endif</div><div class="small text-muted">Droits : {{ implode(', ', $perms) ?: 'aucun' }}</div></div>
   <form method="POST" action="{{ route('superadmin.liaison_compta.unlink',[$app->id,$link->company_id]) }}" onsubmit="return confirm('Révoquer cette liaison et ses jetons ?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Révoquer la liaison</button></form>
  </div>
 @empty <p class="text-muted small">Aucun dossier actif lié.</p> @endforelse
 </div>

 @if($type==='maison' && $app->status!=='revoked')
 <div class="border-top pt-3">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
   <strong>Étape 2 — Associer les dossiers</strong>
   <form method="POST" action="{{ route('superadmin.liaison_compta.pairing.generate',$app->id) }}" class="d-flex flex-wrap gap-2 align-items-end" onsubmit="return confirm('Générer un nouveau code ? Le code précédent sera invalidé.')">@csrf
    <div><label class="form-label small mb-1">Validité du code</label><select class="form-select form-select-sm" name="validity_days" required><option value="90">3 mois</option><option value="180">6 mois</option><option value="365" selected>1 an</option><option value="730">2 ans</option></select></div>
    <button class="btn btn-sm btn-outline-warning">Générer / renouveler le code</button>
   </form>
  </div>
  <div class="row g-2 align-items-end">
   <div class="col-md-7"><label class="form-label">1. Choisir le dossier COMPTAFLOW</label><select class="form-select apk-local-company" data-app="{{ $app->id }}"><option value="">Choisir un dossier COMPTAFLOW</option>@foreach($companies as $company)<option value="{{ $company->id }}">{{ $company->company_name }} (#{{ $company->id }})</option>@endforeach</select></div>
   <div class="col-md-5"><button type="button" class="btn btn-primary w-100 apk-open-partner" data-app="{{ $app->id }}" data-url="{{ route('superadmin.liaison_compta.partner.companies',$app->id) }}">2. Choisir l’entreprise partenaire</button></div>
  </div>
  <small class="apk-muted d-block mt-2">La liste partenaire est chargée depuis son API après sélection du dossier COMPTAFLOW.</small>
 </div>
 @endif

 @if($app->status!=='revoked')
 <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 border-top mt-3 pt-3">
  <form method="POST" action="{{ route('superadmin.liaison_compta.applications.update',$app->id) }}" class="d-flex flex-wrap gap-2 align-items-end">@csrf @method('PUT')
   <div><label class="form-label small">Nom</label><input class="form-control form-control-sm" name="name" value="{{ $app->name }}" required></div>
   <div><label class="form-label small">URL application</label><input class="form-control form-control-sm" type="url" name="app_url" value="{{ $app->app_url }}" placeholder="https://..."></div>
   <div><label class="form-label small">Propriétaire</label><input class="form-control form-control-sm" name="owner_name" value="{{ $app->owner_name }}"></div>
   <div><label class="form-label small">E-mail</label><input class="form-control form-control-sm" name="owner_email" value="{{ $app->owner_email }}"></div>
   <div><label class="form-label small">Statut</label><select class="form-select form-select-sm" name="status"><option value="active" {{ $app->status==='active'?'selected':'' }}>Actif</option><option value="pending" {{ $app->status==='pending'?'selected':'' }}>En attente</option><option value="suspended" {{ $app->status==='suspended'?'selected':'' }}>Suspendu</option></select></div>
   <input type="hidden" name="description" value="{{ $app->description }}"><button class="btn btn-sm btn-outline-secondary">Mettre à jour</button>
  </form>
  <div class="apk-actions">
   @if($app->status==='active')
   <form method="POST" action="{{ route('superadmin.liaison_compta.secret.rotate',$app->id) }}" onsubmit="return confirm('Renouveler le secret et révoquer les jetons actuels ?')">@csrf<button class="btn btn-sm btn-outline-warning">{{ $app->auth_mode==='legacy'?'Activer Bearer / générer les identifiants':'Renouveler les identifiants' }}</button></form>
   @endif
   @if($app->status!=='revoked')
   <form method="POST" action="{{ route('superadmin.liaison_compta.application.revoke',$app->id) }}" onsubmit="return confirm('Révoquer définitivement cette application et toutes ses liaisons ?')">@csrf<button class="btn btn-sm btn-outline-danger">Révoquer l’application</button></form>
   @endif
  </div>
 </div>
 @endif
</div>
@empty
@if($type==='maison' && $selectedApplicationId)
<div class="apk-card text-muted">Aucune information pour cette application.</div>
@elseif($type==='externe')
<div class="apk-card text-muted">Aucune application enregistrée dans cette catégorie.</div>
@endif
@endforelse

<div class="apk-card mt-4">
 <h5 class="fw-bold">Historique détaillé des connexions et dépôts</h5><p class="apk-muted small">Date, application, dossier, origine, action, méthode HTTP, endpoint, réponse et adresse IP. Ouvrez « Voir » pour consulter le payload et la réponse expurgés.</p>
 <div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Date / heure</th><th>Application</th><th>Dossier</th><th>Qui</th><th>Quoi</th><th>Méthode</th><th>Route</th><th>HTTP</th><th>IP</th><th>Détails</th></tr></thead><tbody>
 @forelse($events as $event)<tr>
 <td>{{ $event->created_at }}</td><td>{{ $event->application_name ?: '—' }}</td><td>{{ $event->company_name ?: '—' }}</td>
 <td>{{ data_get(json_decode($event->details ?? '{}',true),'actor_user_id','Application') }}</td>
 <td>{{ $event->event }}</td><td>{{ $event->http_method ?: '—' }}</td><td class="small">{{ $event->route }}</td><td>{{ $event->http_status }}</td><td>{{ $event->ip_address }}</td>
 <td><details><summary>Voir</summary><pre class="small text-wrap mt-2" style="max-width:420px;white-space:pre-wrap">{{ json_encode(json_decode($event->details ?? '{}',true), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre></details></td>
</tr>
 @empty<tr><td colspan="10" class="text-muted text-center">Aucun événement enregistré.</td></tr>@endforelse
 </tbody></table></div>
</div>
</div></div>
</div></div></div>

<div class="modal fade" id="apkPartnerModal" tabindex="-1" aria-hidden="true">
 <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title">Choisir l’entreprise de l’application partenaire</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <form method="POST" id="apkPartnerForm">@csrf
   <div class="modal-body">
    <input type="hidden" name="company_id" id="apkLocalCompanyId">
    <label class="form-label">Entreprise partenaire</label>
    <select class="form-select" name="partner_company_id" id="apkRemoteCompany" required><option value="">Chargement...</option></select>
    <input type="hidden" name="partner_company_name" id="apkRemoteCompanyName">
    <div class="apk-checks mt-3">
     <label><input type="checkbox" name="permissions[]" value="referentiel" checked> Référentiel</label>
     <label><input type="checkbox" name="permissions[]" value="ecritures" checked> Écritures</label>
     <label><input type="checkbox" name="permissions[]" value="pieces_justificatives" checked> Pièces</label>
    </div>
    <div class="small text-danger mt-2" id="apkPartnerError"></div>
   </div>
   <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button><button class="btn btn-primary" id="apkConfirmLink" disabled>Confirmer la liaison</button></div>
  </form>
 </div></div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
 const element=document.getElementById('apkPartnerModal');
 if(!element || !window.bootstrap) return;
 const modal=new bootstrap.Modal(element);
 document.querySelectorAll('.apk-open-partner').forEach(button=>{
  button.addEventListener('click', async ()=>{
   const appId=button.dataset.app;
   const local=document.querySelector('.apk-local-company[data-app="'+appId+'"]');
   const error=document.getElementById('apkPartnerError');
   const select=document.getElementById('apkRemoteCompany');
   error.textContent='';
   if(!local || !local.value){alert('Choisissez d’abord un dossier COMPTAFLOW.');return;}
   document.getElementById('apkLocalCompanyId').value=local.value;
   document.getElementById('apkPartnerForm').action='{{ url('/superadmin/liaison-compta-apk/applications') }}/'+appId+'/liaisons';
   select.innerHTML='<option value="">Chargement des entreprises...</option>';
   document.getElementById('apkConfirmLink').disabled=true;
   modal.show();
   try {
    const response=await fetch(button.dataset.url,{headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
    const payload=await response.json();
    if(!response.ok) throw new Error(payload.message || 'Le catalogue partenaire est indisponible.');
    select.innerHTML='<option value="">Sélectionner une entreprise</option>';
    (payload.data || []).forEach(company=>{
     const option=document.createElement('option');
     option.value=company.id; option.textContent=company.name; option.dataset.name=company.name;
     select.appendChild(option);
    });
    if(!payload.data || !payload.data.length) throw new Error('Aucune entreprise n’a été retournée par l’application partenaire.');
    document.getElementById('apkConfirmLink').disabled=false;
   } catch(e) {
    select.innerHTML='<option value="">Liste indisponible</option>';
    error.textContent=e.message;
   }
  });
 });
 document.getElementById('apkRemoteCompany')?.addEventListener('change',event=>{
  document.getElementById('apkRemoteCompanyName').value=event.target.selectedOptions[0]?.dataset.name || '';
 });
});
</script>

</body></html>
