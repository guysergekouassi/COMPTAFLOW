<!DOCTYPE html>
<html lang="fr" class="layout-compact" data-assets-path="../assets/" data-template="vertical-menu-template-free" data-bs-theme="light">
@include('components.head')
<style>
.page-control-hero{background:linear-gradient(110deg,#edf4ff,#fff);border:1px solid #dce8fb;border-radius:18px;padding:24px}
.page-section{border:1px solid #e5ebf4;border-radius:14px;overflow:hidden;background:#fff}
.page-section summary{cursor:pointer;list-style:none;padding:16px 18px;font-weight:800;color:#193b75;display:flex;justify-content:space-between;align-items:center}
.page-section summary::-webkit-details-marker{display:none}
.page-rule-row{padding:14px 18px;border-top:1px solid #eef2f7;display:grid;grid-template-columns:minmax(160px,1fr) minmax(260px,1.5fr);gap:18px;align-items:center}
.page-rule-row:nth-child(even){background:#fbfdff}
.page-route{font-size:.78rem;color:#718096}
.page-actions{display:flex;flex-wrap:wrap;gap:7px;align-items:center}
.page-actions input,.page-actions select{min-width:120px}
.key-card{border:1px solid #e2eaf5;border-radius:14px;padding:16px;background:#fff}
.superadmin-workspace{background:#f6f8fc;border:1px solid #e7edf6;border-radius:18px;padding:18px}
.page-control-hero{box-shadow:0 8px 24px rgba(28,67,130,.06)}
.control-stat{background:#fff;border:1px solid #e5ebf4;border-radius:14px;padding:14px 16px;min-width:150px}
.control-stat strong{display:block;font-size:1.35rem;color:#173f86}
.page-filter{max-width:420px;border-radius:12px}
.nav-pills .nav-link{border:1px solid #e4eaf3;background:#fff;color:#52627a;margin-right:7px;font-weight:700}
.nav-pills .nav-link.active{background:#2456bd;color:#fff;border-color:#2456bd;box-shadow:0 5px 14px rgba(36,86,189,.16)}
.page-section{box-shadow:0 4px 14px rgba(28,67,130,.035)}
.page-section summary:hover{background:#f7faff}
.page-rule-row{transition:background .15s}
.page-rule-row:hover{background:#f3f7ff}
.key-card.is-active{border-color:#b8d4ff;background:#f8fbff}
.superadmin-section-title{font-weight:800;color:#172b4d}
@media(max-width:800px){.superadmin-workspace{padding:10px}.control-stat{flex:1 1 130px}}
@media(max-width:800px){.page-rule-row{grid-template-columns:1fr}.page-actions{align-items:stretch}.page-actions>*{flex:1 1 130px}}

/* La page reprend le layout Super Admin commun ; seuls les composants propres
   au catalogue de pages sont stylés ici. */
.page-control-account{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;background:#fff;border:1px solid #e2eaf5;border-radius:14px;padding:14px 18px;margin-bottom:18px}
.page-control-account .account-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#718096;font-weight:800}
.page-control-account .account-value{font-weight:700;color:#1c3156;overflow-wrap:anywhere}
.page-control-shortcut{display:inline-flex;align-items:center;gap:8px;text-decoration:none}
.page-control-content{min-width:0;width:100%}
@media(max-width:800px){.page-control-account{align-items:flex-start}.page-control-shortcut{width:100%;justify-content:center}}
</style>
<body>
<div class="layout-wrapper layout-content-navbar"><div class="layout-container">
@include('components.sidebar')
<div class="layout-page">
@include('components.header',['page_title'=>'Pilotage des fonctionnalités'])
<div class="content-wrapper"><div class="container-xxl flex-grow-1 container-p-y page-control-content">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

<div class="page-control-account">
 <div><div class="account-label">Compte Super Admin connecté</div><div class="account-value">{{ auth()->user()->name }} {{ auth()->user()->last_name }}</div></div>
 <div><div class="account-label">Adresse de connexion</div><div class="account-value">{{ auth()->user()->email_adresse ?? 'Non renseignée' }}</div></div>
 <div><div class="account-label">Identifiant utilisateur</div><div class="account-value">#{{ auth()->user()->id }}</div></div>
 <div><div class="account-label">Statut du compte</div><div class="account-value"><span class="badge bg-{{ auth()->user()->is_active ? 'success' : 'secondary' }}">{{ auth()->user()->is_active ? 'Actif' : 'Statut non confirmé' }}</span></div></div>
 <a href="{{ route('superadmin.liaisons.index') }}" class="btn btn-outline-primary btn-sm page-control-shortcut"><i class="fa-solid fa-link"></i> Gérer les liaisons d'applications</a>
</div>
<div class="page-control-hero mb-4">
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
<div><span class="badge bg-primary-subtle text-primary mb-2">SUPER ADMIN · CONFIGURATION</span><h3 class="mb-1 fw-bold">Visibilité des pages & clés IA</h3><p class="text-muted mb-0">Contrôlez les fonctionnalités disponibles, globalement ou entreprise par entreprise.</p></div>
<div class="d-flex flex-wrap gap-2">
<div class="control-stat"><span class="small text-muted">Pages répertoriées</span><strong>{{ collect($sections)->sum(fn($items)=>count($items)) }}</strong></div>
<div class="control-stat"><span class="small text-muted">Configurations IA</span><strong>{{ $credentials->count() }}</strong></div>
<div class="control-stat"><span class="small text-muted">Entreprises</span><strong>{{ $companies->count() }}</strong></div>
</div>
</div></div>
<ul class="nav nav-pills mb-3" role="tablist">
<li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#pages-tab" type="button">Pages de l'application</button></li>
<li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#ia-tab" type="button">Fournisseurs & clés API IA</button></li>
<li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#quotas-tab" type="button">Quotas SCAN</button></li>
</ul>
<div class="tab-content">
<div class="tab-pane fade show active" id="pages-tab">
<div class="superadmin-workspace mb-3"><div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
<div><div class="superadmin-section-title">Catalogue des fonctionnalités</div><div class="small text-muted">Recherchez une page, choisissez la portée de la règle et précisez le motif de masquage.</div></div>
<input type="search" id="pageFilter" class="form-control page-filter" placeholder="Rechercher une page, une section ou une route…">
</div></div>
<div class="alert alert-info border-0"><strong>Règle de priorité :</strong> une règle définie pour une entreprise remplace la règle générale. Sans règle spécifique, la règle générale s'applique. Une page masquée est aussi bloquée côté serveur.</div>
@foreach($sections as $section=>$pages)
@if(count($pages))
<details class="page-section mb-3" open data-page-section><summary><span>{{ $section }} <small class="text-muted fw-normal">({{ count($pages) }} pages)</small></span><i class="fa-solid fa-chevron-down"></i></summary>
@foreach($pages as $routeName=>$page)
@php($globalRule=$rules->get('global',collect())->firstWhere('route_name',$routeName))
<div class="page-rule-row" data-page-item data-search="{{ strtolower($section.' '.$page['label'].' '.$page['uri'].' '.$routeName) }}">
<div><div class="fw-semibold">{{ $page['label'] }}</div><div class="page-route">{{ $page['uri'] }} · <code>{{ $routeName }}</code></div>
@if($globalRule)<small class="d-block text-{{ $globalRule->is_visible?'success':'danger' }}">Règle générale : {{ $globalRule->is_visible?'Visible':'Masquée' }} @if($globalRule->reason) · {{ $globalRule->reason }} @endif</small>@else<small class="d-block text-muted">Règle générale : visibilité par défaut</small>@endif
@foreach($rules as $scopeId=>$scopeRules)
@if($scopeId !== 'global' && ($companyRule=$scopeRules->firstWhere('route_name',$routeName)))
@php($ruleCompany=$companies->firstWhere('id',(int)$scopeId))
@if($ruleCompany)<small class="d-block {{ $companyRule->is_visible?'text-success':'text-danger' }}">{{ $ruleCompany->company_name }} : {{ $companyRule->is_visible?'Visible':'Masquée' }} <form class="d-inline" method="POST" action="{{ route('superadmin.pages.rules.delete',$companyRule) }}">@csrf @method('DELETE')<button class="btn btn-link btn-sm p-0 text-muted" title="Supprimer cette règle">×</button></form></small>@endif
@endif
@endforeach
</div>
<form class="page-actions" method="POST" action="{{ route('superadmin.pages.rules.store') }}">@csrf
<input type="hidden" name="route_name" value="{{ $routeName }}">
<select name="scope" class="form-select form-select-sm">
<option value="global">Toute l'application</option><option value="company">Une ou plusieurs entreprises</option></select>
<select name="company_ids[]" class="form-select form-select-sm" multiple size="1" disabled title="Maintenez Ctrl pour sélectionner plusieurs entreprises">
@foreach($companies as $company)<option value="{{ $company->id }}">{{ $company->company_name }}</option>@endforeach</select>
<select name="is_visible" class="form-select form-select-sm"><option value="0">Masquer</option><option value="1">Afficher</option></select>
<input name="reason" class="form-control form-control-sm" placeholder="Motif / précision (facultatif)">
<button class="btn btn-sm btn-primary" type="submit"><i class="fa-solid fa-floppy-disk me-1"></i>Appliquer</button>
</form>
</div>
@endforeach</details>
@endif
@endforeach
</div>
<div class="tab-pane fade" id="ia-tab">
<div class="alert alert-info border-0 mb-3"><strong>API gratuite conseillée pour les essais :</strong> Google AI Studio · Gemini 2.5 Flash-Lite. Créez une clé sur <a href="https://aistudio.google.com/app/apikey" target="_blank" rel="noopener noreferrer">Google AI Studio</a>, puis enregistrez-la ici. Modèle : <code>gemini-2.5-flash-lite</code>. N’utilisez que des factures fictives ou anonymisées sur le niveau gratuit : Google indique que les contenus soumis au service gratuit peuvent être utilisés pour améliorer ses produits et examinés par des personnes.</div>
<div class="row g-4">
<div class="col-xl-5"><div class="card border-0 shadow-sm"><div class="card-header bg-white"><h5 class="mb-0">Ajouter un fournisseur / modèle</h5></div><div class="card-body">
<form method="POST" action="{{ route('superadmin.ai-credentials.store') }}">@csrf
<div class="mb-3"><label class="form-label">Maison / fournisseur IA</label><input name="provider" class="form-control" list="ai-providers" value="Google Gemini" placeholder="Ex. Google Gemini, OpenAI, Anthropic, Mistral" required><datalist id="ai-providers">@foreach($credentials->pluck('provider')->unique() as $provider)<option value="{{ $provider }}">@endforeach</datalist></div>
<div class="mb-3"><label class="form-label">Nom du modèle</label><input name="model" class="form-control" value="gemini-2.5-flash-lite" placeholder="Ex. gemini-2.5-flash-lite" required></div>
<div class="mb-3"><label class="form-label">Clé API</label><input name="api_key" type="password" class="form-control" autocomplete="new-password" required></div>
<div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2" placeholder="Usage, environnement, remarques"></textarea></div>
<button class="btn btn-primary w-100"><i class="fa-solid fa-shield-halved me-1"></i>Enregistrer la configuration</button>
</form><small class="text-muted d-block mt-3">Les clés sont chiffrées en base et ne sont jamais réaffichées en clair. Le fournisseur actif est utilisé par le service Python de scan via une connexion HTTPS sécurisée.</small>
</div></div></div>
<div class="col-xl-7"><h5 class="fw-bold mb-3">Modèles configurés</h5>
@if($credentials->isEmpty())<div class="card border-0 bg-light p-4 text-center text-muted">Aucun fournisseur IA configuré pour le moment.</div>@endif
@foreach($credentials as $credential)<div class="key-card mb-3 {{ $credential->is_active ? 'is-active' : '' }}"><div class="d-flex justify-content-between gap-3 align-items-start"><div><div class="fw-bold">{{ $credential->provider }} <span class="text-primary">/</span> {{ $credential->model }}</div><div class="small text-muted">Clé enregistrée · {{ $credential->updated_at?->format('d/m/Y H:i') }}</div>@if($credential->notes)<p class="small mb-0 mt-2">{{ $credential->notes }}</p>@endif</div><span class="badge bg-{{ $credential->is_active?'success':'secondary' }}">{{ $credential->is_active?'Active':'Inactive' }}</span></div>
<div class="d-flex gap-2 mt-3"><button type="button" class="btn btn-sm btn-outline-info" onclick="testAiCredential(this,'{{ route('superadmin.ai-credentials.test',$credential) }}')"><i class="fa-solid fa-plug me-1"></i>Tester la connexion</button><form method="POST" action="{{ route('superadmin.ai-credentials.toggle',$credential) }}">@csrf<button class="btn btn-sm btn-outline-primary">{{ $credential->is_active?'Désactiver':'Activer' }}</button></form><form method="POST" action="{{ route('superadmin.ai-credentials.delete',$credential) }}" onsubmit="return confirm('Supprimer cette configuration IA ?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Supprimer</button></form></div></div>@endforeach
</div></div>
<div class="tab-pane fade" id="quotas-tab">
<div class="alert alert-info border-0"><strong>Contrôle désactivé par défaut.</strong> Aucun plafond n'est appliqué tant que cette option reste désactivée. Les valeurs ci-dessous sont préparées pour une configuration ultérieure.</div>
<div class="card border-0 shadow-sm"><div class="card-header bg-white"><h5 class="mb-0">Paramétrage des quotas de factures</h5></div><div class="card-body">
<form method="POST" action="{{ route('superadmin.scan-quotas.store') }}">@csrf
<div class="form-check form-switch mb-4">
<input class="form-check-input" type="checkbox" role="switch" id="quotas_enabled" name="quotas_enabled" value="1" {{ $scanSettings->quotas_enabled ? 'checked' : '' }}>
<label class="form-check-label fw-bold" for="quotas_enabled">Activer le contrôle des quotas de scan</label>
</div>
<div class="table-responsive"><table class="table table-bordered align-middle">
<thead class="table-light"><tr><th>Forfait</th><th>Factures / mois</th><th>Factures / traitement groupé</th></tr></thead><tbody>
@foreach(['Starter','Pro','Enterprise'] as $pack)
<tr><th>{{ $pack }}</th>
<td><input type="number" min="1" class="form-control" name="limits[{{ $pack }}][monthly]" value="{{ data_get($scanSettings->limits, $pack.'.monthly') }}" placeholder="Sans limite"></td>
<td><input type="number" min="1" class="form-control" name="limits[{{ $pack }}][batch]" value="{{ data_get($scanSettings->limits, $pack.'.batch') }}" placeholder="Sans limite"></td>
</tr>
@endforeach
</tbody></table></div>
<p class="small text-muted">Laissez une valeur vide pour ne pas définir de plafond sur cette dimension. L'application des limites et du compteur de consommation sera activée dans une étape ultérieure.</p>
<button class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Enregistrer les paramètres</button>
</form></div></div></div>
</div>
</div>
</div></div></div></div>
<script>
document.addEventListener('DOMContentLoaded', function () {
 const filter=document.getElementById('pageFilter');
 if(filter) filter.addEventListener('input',function(){
  const term=this.value.trim().toLocaleLowerCase('fr');
  document.querySelectorAll('[data-page-section]').forEach(section=>{
   let visible=0;
   section.querySelectorAll('[data-page-item]').forEach(item=>{
    const match=!term||(item.dataset.search||'').includes(term);
    item.classList.toggle('d-none',!match); if(match) visible++;
   });
   section.classList.toggle('d-none',visible===0); if(term&&visible)section.open=true;
  });
 });
 document.querySelectorAll('select[name="scope"]').forEach(select=>{
  const company=select.form.querySelector('[name="company_ids[]"]');
  const sync=()=>{company.disabled=select.value!=='company';company.required=select.value==='company';};
  select.addEventListener('change',sync);sync();
 });
});
</script>
<script>
async function testAiCredential(button, url) {
 const old=button.innerHTML; button.disabled=true; button.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span>Test…';
 try {
  const response=await fetch(url,{method:'POST',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content||'','Accept':'application/json'}});
  const data=await response.json();
  if(window.Swal) await Swal.fire({icon:data.success?'success':'warning',title:data.success?'Connexion réussie':'Test non concluant',text:data.message||'Le test n’a pas abouti.',confirmButtonColor:'#2456bd'});
  else alert(data.message||'Test terminé');
 } catch(e) {
  if(window.Swal) await Swal.fire({icon:'warning',title:'Test indisponible',text:'Le service de test est momentanément indisponible. Contactez le support technique DC-KNOWING.'});
 } finally {button.disabled=false;button.innerHTML=old;}
}
</script>
</body></html>