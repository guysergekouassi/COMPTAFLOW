<!doctype html>
<html lang="fr" class="layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">
@include('components.head')
<style>
.apk-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:20px;margin-bottom:18px}
.apk-muted{color:#64748b}.apk-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px}
.apk-actions{display:flex;gap:8px;flex-wrap:wrap}.apk-code{font-family:monospace;overflow-wrap:anywhere}
.apk-checks{display:flex;gap:18px;flex-wrap:wrap}.apk-checks label{font-size:13px}
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
 <h5 class="fw-bold mb-3">Enregistrer une application {{ $type==='maison'?'maison':'externe' }}</h5>
 <form method="POST" action="{{ route('superadmin.liaison_compta.applications.store') }}">@csrf
 <input type="hidden" name="type" value="{{ $type }}">
 <div class="apk-grid">
  <div><label class="form-label">Nom de l’application *</label><input class="form-control" name="name" required maxlength="160" placeholder="{{ $type==='maison'?'Ex. STOCK-FLOW':'Ex. Partenaire API' }}"></div>
  <div><label class="form-label">Propriétaire</label><input class="form-control" name="owner_name" {{ $type==='externe'?'required':'' }} placeholder="Organisation / éditeur"></div>
  <div><label class="form-label">E-mail propriétaire</label><input class="form-control" type="email" name="owner_email"></div>
  <div><label class="form-label">Description</label><input class="form-control" name="description" maxlength="2000"></div>
 </div><button class="btn btn-primary mt-3">Enregistrer et générer les identifiants</button>
 </form>
</div>

@if($type==='externe')
<div class="alert alert-info">Une application externe ne peut être liée qu’à un seul dossier COMPTAFLOW dédié. Elle ne reçoit jamais la liste globale des entreprises et ne peut pas changer de dossier dans ses payloads.</div>
@else
<div class="alert alert-info">Les applications maison peuvent être liées à plusieurs dossiers, mais chaque jeton Bearer est limité à un seul dossier autorisé. SELFLOW reste affiché en mode historique pendant sa migration ; RH-FLOW est référencé comme prévu, sans connecteur déclaré opérationnel.</div>
@endif

<h5 class="fw-bold mb-3">Applications enregistrées</h5>
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
 @if($app->client_id)<div class="small mb-2"><strong>Client ID :</strong> <span class="apk-code">{{ $app->client_id }}</span> · <strong>Expiration secret :</strong> {{ $app->client_secret_expires_at ?: '—' }}</div>@endif
 <div class="mb-3"><strong>Liaisons actives</strong>
 @forelse($app->links->where('status','active') as $link)
  @php($perms=json_decode($link->permissions,true) ?: [])
  <div class="d-flex justify-content-between align-items-center flex-wrap border rounded p-2 mt-2 gap-2">
   <div><strong>{{ $link->company_name }}</strong> <span class="text-muted small">(#{{ $link->company_id }})</span><div class="small text-muted">Droits : {{ implode(', ', $perms) ?: 'aucun' }}</div></div>
   <form method="POST" action="{{ route('superadmin.liaison_compta.unlink',[$app->id,$link->company_id]) }}" onsubmit="return confirm('Révoquer cette liaison et ses jetons ?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Révoquer la liaison</button></form>
  </div>
 @empty <p class="text-muted small">Aucun dossier actif lié.</p> @endforelse
 </div>

 @if($app->status==='active' && $app->status!=='pending')
 <form method="POST" action="{{ route('superadmin.liaison_compta.link', $app->id) }}" class="border-top pt-3">@csrf
  <div class="row g-2 align-items-end">
   <div class="col-md-5"><label class="form-label">Lier à une entreprise existante</label><select name="company_id" class="form-select" required><option value="">Choisir un dossier COMPTAFLOW</option>@foreach($companies as $company)<option value="{{ $company->id }}">{{ $company->company_name }} (#{{ $company->id }})</option>@endforeach</select></div>
   <div class="col-md-5"><label class="form-label">Permissions</label><div class="apk-checks">
    <label><input type="checkbox" name="permissions[]" value="referentiel" checked> Référentiel</label>
    <label><input type="checkbox" name="permissions[]" value="ecritures" checked> Écritures</label>
    <label><input type="checkbox" name="permissions[]" value="pieces_justificatives" checked> Pièces</label>
   </div></div><div class="col-md-2"><button class="btn btn-outline-primary w-100">Lier</button></div>
  </div>
 </form>
 @endif

 @if($type==='externe' && $app->status==='active' && !$app->links->where('status','active')->count())
 <details class="border-top pt-3 mt-3"><summary class="fw-semibold text-primary">Créer une entreprise COMPTAFLOW dédiée et la lier</summary>
 <form method="POST" action="{{ route('superadmin.liaison_compta.company.create', $app->id) }}" class="mt-3">@csrf
  <div class="apk-grid">
   <div><label class="form-label">Nom de l’entreprise *</label><input class="form-control" name="company_name" required></div>
   <div><label class="form-label">Activité *</label><input class="form-control" name="activity" required></div>
   <div><label class="form-label">Forme juridique *</label><input class="form-control" name="juridique_form" required placeholder="SARL, SA..."></div>
   <div><label class="form-label">E-mail de contact *</label><input class="form-control" type="email" name="email_adresse" required></div>
   <div><label class="form-label">Contact technique</label><input class="form-control" name="owner_contact"></div>
   <div><label class="form-label">Téléphone</label><input class="form-control" name="phone_number"></div>
   <div><label class="form-label">Adresse</label><input class="form-control" name="adresse"></div>
   <div><label class="form-label">Ville</label><input class="form-control" name="city" value="Abidjan"></div>
   <div><label class="form-label">Pays</label><input class="form-control" name="country" value="Côte d'Ivoire"></div>
  </div>
  <div class="apk-checks my-3"><label><input type="checkbox" name="permissions[]" value="referentiel" checked> Référentiel</label><label><input type="checkbox" name="permissions[]" value="ecritures" checked> Écritures</label><label><input type="checkbox" name="permissions[]" value="pieces_justificatives" checked> Pièces justificatives</label></div>
  <button class="btn btn-success">Créer le dossier et établir la liaison</button>
 </form></details>
 @endif

 @if($app->auth_mode!=='planned')
 <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 border-top mt-3 pt-3">
  <form method="POST" action="{{ route('superadmin.liaison_compta.applications.update',$app->id) }}" class="d-flex flex-wrap gap-2 align-items-end">@csrf @method('PUT')
   <div><label class="form-label small">Nom</label><input class="form-control form-control-sm" name="name" value="{{ $app->name }}" required></div>
   <div><label class="form-label small">Propriétaire</label><input class="form-control form-control-sm" name="owner_name" value="{{ $app->owner_name }}"></div>
   <div><label class="form-label small">E-mail</label><input class="form-control form-control-sm" name="owner_email" value="{{ $app->owner_email }}"></div>
   <div><label class="form-label small">Statut</label><select class="form-select form-select-sm" name="status"><option value="active" {{ $app->status==='active'?'selected':'' }}>Actif</option><option value="suspended" {{ $app->status==='suspended'?'selected':'' }}>Suspendu</option></select></div>
   <input type="hidden" name="description" value="{{ $app->description }}"><button class="btn btn-sm btn-outline-secondary">Mettre à jour</button>
  </form>
  <div class="apk-actions">
   @if($app->auth_mode!=='planned' && $app->status==='active')
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
<div class="apk-card text-muted">Aucune application enregistrée dans cette catégorie.</div>
@endforelse

<div class="apk-card mt-4">
 <h5 class="fw-bold">Historique des connexions et dépôts</h5>
 <div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Date</th><th>Application</th><th>Dossier</th><th>Événement</th><th>Route</th><th>HTTP</th><th>IP</th></tr></thead><tbody>
 @forelse($events as $event)<tr><td>{{ $event->created_at }}</td><td>{{ $event->application_name ?: '—' }}</td><td>{{ $event->company_name ?: '—' }}</td><td>{{ $event->event }}</td><td class="small">{{ $event->route }}</td><td>{{ $event->http_status }}</td><td>{{ $event->ip_address }}</td></tr>
 @empty<tr><td colspan="7" class="text-muted text-center">Aucun événement enregistré.</td></tr>@endforelse
 </tbody></table></div>
</div>
</div></div>
</div></div></div>
</body></html>
