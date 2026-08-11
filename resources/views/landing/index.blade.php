<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Flow Compta - L'élégance de la comptabilité moderne</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; background-color: #fafbfc; }
    .reveal { opacity: 0; transform: translateY(30px); transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
    .reveal.active { opacity: 1; transform: translateY(0); }
    .hero-gradient { background: radial-gradient(circle at 50% -20%, rgba(99,102,241,0.15), rgba(255,255,255,0) 70%); }
    .glass-card { background: rgba(255,255,255,0.8); backdrop-filter: blur(12px); border: 1px solid rgba(226,232,240,0.8); }
    @keyframes marquee { 0% { transform: translateX(0%); } 100% { transform: translateX(-50%); } }
    .animate-marquee { display: flex; width: 200%; animation: marquee 25s linear infinite; }
    .animate-marquee:hover { animation-play-state: paused; }
    @keyframes floatUp { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
    .float-anim { animation: floatUp 6s ease-in-out infinite; }
  </style>
</head>
<body class="text-slate-800 antialiased overflow-x-hidden">

  <!-- ===== NAVBAR ===== -->
  <header class="fixed top-0 left-0 right-0 z-50 glass-card transition-all duration-300">
    <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
      <a href="#" class="flex items-center gap-3 group">
        <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-500/30 group-hover:scale-105 transition-transform">
          <i class="fa-solid fa-bolt text-lg"></i>
        </div>
        <span class="text-xl font-bold text-slate-900 tracking-tight">Flow Compta</span>
      </a>
      <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-600">
        <a href="#features" class="hover:text-indigo-600 transition-colors">Fonctionnalités</a>
        <a href="#demo" class="hover:text-indigo-600 transition-colors">Démo &amp; Liasses</a>
        <a href="#syscohada" class="hover:text-indigo-600 transition-colors">Conformité</a>
        <a href="#about" class="hover:text-indigo-600 transition-colors">À propos</a>
      </nav>
      <div class="flex items-center gap-4">
        <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-700 hover:text-indigo-600 px-4 py-2 rounded-lg transition-colors">Connexion</a>
        <a href="{{ route('landing.pricing') }}" class="text-sm font-semibold text-white bg-slate-900 hover:bg-indigo-600 px-5 py-2.5 rounded-xl shadow-md transition-all duration-300 transform hover:-translate-y-0.5">S'inscrire</a>
      </div>
    </div>
  </header>

  <main class="pt-20">

    <!-- ===== HERO ===== -->
    <section class="relative hero-gradient pt-20 pb-16 md:pt-32 md:pb-24 overflow-hidden">
      <div class="max-w-7xl mx-auto px-6 text-center">

        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-indigo-50 border border-indigo-100 text-indigo-700 text-xs font-semibold uppercase tracking-wider mb-8 reveal">
          <span class="w-2 h-2 rounded-full bg-indigo-600 animate-pulse"></span>
          La nouvelle norme comptable en Côte d'Ivoire &amp; SYSCOHADA
        </div>

        <h1 class="text-4xl md:text-6xl lg:text-7xl font-extrabold text-slate-900 tracking-tight leading-tight max-w-4xl mx-auto mb-6 reveal">
          Pilotez votre finance avec
          <span class="bg-gradient-to-r from-indigo-600 to-violet-600 bg-clip-text text-transparent"> intelligence.</span>
        </h1>

        <p class="text-lg md:text-xl text-slate-600 max-w-2xl mx-auto mb-10 leading-relaxed reveal">
          Flow Compta réinvente la gestion comptable pour les cabinets et les entreprises. Centralisez, automatisez et analysez vos données en un clic avec l'IA.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-16 reveal">
          <a href="{{ route('landing.pricing') }}" class="w-full sm:w-auto px-8 py-4 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-2xl shadow-xl shadow-indigo-500/25 transition-all transform hover:-translate-y-1 flex items-center justify-center gap-3 group">
            <span>Démarrer gratuitement</span>
            <i class="fa-solid fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
          </a>
          <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-4 bg-white hover:bg-slate-50 text-slate-700 font-semibold rounded-2xl border border-slate-200 shadow-sm transition-all flex items-center justify-center gap-3">
            <i class="fa-solid fa-lock text-slate-400"></i>
            <span>Mon espace</span>
          </a>
        </div>

        <!-- Aperçu Dashboard -->
        <div class="relative mx-auto max-w-5xl rounded-3xl p-3 bg-slate-900/5 ring-1 ring-slate-900/10 backdrop-blur-xl shadow-2xl reveal float-anim">
          <div class="rounded-2xl overflow-hidden bg-white border border-slate-200">
            <!-- Barre navigateur simulée -->
            <div class="h-10 bg-slate-100 border-b border-slate-200 px-4 flex items-center gap-2">
              <div class="w-3 h-3 rounded-full bg-red-400"></div>
              <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
              <div class="w-3 h-3 rounded-full bg-green-400"></div>
              <span class="ml-4 text-xs font-mono text-slate-400">comptaflow.dc-knowing.com/dashboard</span>
            </div>
            <!-- Mockup 4 cartes -->
            <div class="p-6 bg-slate-50 grid grid-cols-2 md:grid-cols-4 gap-4 text-left">
              <div class="p-4 bg-white rounded-xl border border-slate-200/80 shadow-sm">
                <p class="text-xs text-slate-500 uppercase font-bold tracking-wide">Activité Mensuelle</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-2">12 450 000 <span class="text-xs text-slate-400 font-normal">FCFA</span></p>
                <span class="text-xs text-emerald-600 font-semibold flex items-center gap-1 mt-2">
                  <i class="fa-solid fa-arrow-up"></i> +14.2% vs mois dernier
                </span>
              </div>
              <div class="p-4 bg-white rounded-xl border border-slate-200/80 shadow-sm">
                <p class="text-xs text-slate-500 uppercase font-bold tracking-wide">Trésorerie Globale</p>
                <p class="text-2xl font-extrabold text-indigo-600 mt-2">5 000 000 <span class="text-xs text-slate-400 font-normal">FCFA</span></p>
                <span class="text-xs text-indigo-500 font-semibold flex items-center gap-1 mt-2">
                  <i class="fa-solid fa-wallet"></i> Compte Actif
                </span>
              </div>
              <div class="p-4 bg-white rounded-xl border border-slate-200/80 shadow-sm">
                <p class="text-xs text-slate-500 uppercase font-bold tracking-wide">Revenus / Charges</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-2">85% / 15%</p>
                <span class="text-xs text-slate-500 font-medium flex items-center gap-1 mt-2">
                  Ratio optimal
                </span>
              </div>
              <div class="p-4 bg-white rounded-xl border border-slate-200/80 shadow-sm">
                <p class="text-xs text-slate-500 uppercase font-bold tracking-wide">Exercice Actif</p>
                <p class="text-2xl font-extrabold text-slate-900 mt-2">2026</p>
                <span class="text-xs text-emerald-600 font-semibold flex items-center gap-1 mt-2">
                  <i class="fa-solid fa-check"></i> Conforme SYSCOHADA
                </span>
              </div>
            </div>
          </div>
        </div>


      </div>
    </section>

    <!-- ===== DÉMO MARQUEE ===== -->
    <section id="demo" class="py-20 bg-slate-900 text-white overflow-hidden">
      <div class="max-w-7xl mx-auto px-6 mb-12 text-center reveal">
        <h2 class="text-3xl md:text-4xl font-bold mb-4">Saisie Rapide &amp; Automatisation IA</h2>
        <p class="text-slate-400 max-w-2xl mx-auto">Visionnez le défilement ultrarapide de la génération d'écritures comptables et du traitement des pièces justificatives.</p>
      </div>
      <div class="relative w-full overflow-hidden">
        <div class="animate-marquee gap-6">

          <div class="w-80 flex-shrink-0 bg-slate-800 p-5 rounded-2xl border border-slate-700/60 shadow-lg">
            <div class="flex items-center justify-between text-xs text-slate-400 mb-3">
              <span>Journal: Ventes (VT)</span>
              <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-mono">Auto-Généré</span>
            </div>
            <p class="font-mono text-sm text-indigo-300 mb-1">D: 411100 - Client Abidjan</p>
            <p class="font-mono text-sm text-emerald-400 mb-1">C: 701100 - Vente Marchandises</p>
            <p class="font-mono text-sm text-amber-400 mb-3">C: 443100 - TVA Facturée</p>
            <div class="border-t border-slate-700 pt-2 flex justify-between text-xs text-slate-400">
              <span>Montant HT: 1 000 000 F</span><span>TVA: 18%</span>
            </div>
          </div>

          <div class="w-80 flex-shrink-0 bg-indigo-950/60 p-5 rounded-2xl border border-indigo-500/30 shadow-lg">
            <div class="flex items-center justify-between text-xs text-indigo-300 mb-3">
              <span>Liasse Fiscale SYSCOHADA</span>
              <span class="px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300 font-mono">Bilan Actif</span>
            </div>
            <p class="text-sm font-semibold text-white mb-2">Calcul automatique du Tableau FLUX (TFT)</p>
            <div class="h-2 bg-slate-700 rounded-full overflow-hidden mb-2">
              <div class="w-3/4 h-full bg-indigo-500 animate-pulse"></div>
            </div>
            <p class="text-xs text-slate-400">Génération des états 100% conforme DGI</p>
          </div>

          <div class="w-80 flex-shrink-0 bg-slate-800 p-5 rounded-2xl border border-slate-700/60 shadow-lg">
            <div class="flex items-center justify-between text-xs text-slate-400 mb-3">
              <span>Journal: Achats (AC)</span>
              <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-mono">OCR IA</span>
            </div>
            <p class="font-mono text-sm text-indigo-300 mb-1">D: 601100 - Achat de fournitures</p>
            <p class="font-mono text-sm text-amber-400 mb-1">D: 445100 - TVA Déductible</p>
            <p class="font-mono text-sm text-emerald-400 mb-3">C: 401100 - Fournisseur CI</p>
            <div class="border-t border-slate-700 pt-2 flex justify-between text-xs text-slate-400">
              <span>Scan Facture.pdf</span>
              <span class="text-emerald-400"><i class="fa-solid fa-check-double"></i> Validé</span>
            </div>
          </div>

          <div class="w-80 flex-shrink-0 bg-slate-800 p-5 rounded-2xl border border-slate-700/60 shadow-lg">
            <div class="flex items-center justify-between text-xs text-slate-400 mb-3">
              <span>Tableau de Flux de Trésorerie</span>
              <span class="px-2 py-0.5 rounded bg-amber-500/20 text-amber-300 font-mono">TFT</span>
            </div>
            <p class="font-mono text-sm text-white mb-1">Flux de Trésorerie d'Exploitation</p>
            <p class="font-mono text-sm text-emerald-400 mb-3">+ 15 800 000 FCFA</p>
            <div class="border-t border-slate-700 pt-2 text-xs text-slate-400">Rapprochement bancaire synchronisé</div>
          </div>

          <!-- Duplicate for seamless loop -->
          <div class="w-80 flex-shrink-0 bg-slate-800 p-5 rounded-2xl border border-slate-700/60 shadow-lg">
            <div class="flex items-center justify-between text-xs text-slate-400 mb-3">
              <span>Journal: Ventes (VT)</span>
              <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-mono">Auto-Généré</span>
            </div>
            <p class="font-mono text-sm text-indigo-300 mb-1">D: 411100 - Client Abidjan</p>
            <p class="font-mono text-sm text-emerald-400 mb-1">C: 701100 - Vente Marchandises</p>
            <p class="font-mono text-sm text-amber-400 mb-3">C: 443100 - TVA Facturée</p>
            <div class="border-t border-slate-700 pt-2 flex justify-between text-xs text-slate-400">
              <span>Montant HT: 1 000 000 F</span><span>TVA: 18%</span>
            </div>
          </div>

          <div class="w-80 flex-shrink-0 bg-indigo-950/60 p-5 rounded-2xl border border-indigo-500/30 shadow-lg">
            <div class="flex items-center justify-between text-xs text-indigo-300 mb-3">
              <span>Liasse Fiscale SYSCOHADA</span>
              <span class="px-2 py-0.5 rounded bg-indigo-500/20 text-indigo-300 font-mono">Bilan Actif</span>
            </div>
            <p class="text-sm font-semibold text-white mb-2">Calcul automatique du Tableau FLUX (TFT)</p>
            <div class="h-2 bg-slate-700 rounded-full overflow-hidden mb-2">
              <div class="w-3/4 h-full bg-indigo-500 animate-pulse"></div>
            </div>
            <p class="text-xs text-slate-400">Génération des états 100% conforme DGI</p>
          </div>

          <div class="w-80 flex-shrink-0 bg-slate-800 p-5 rounded-2xl border border-slate-700/60 shadow-lg">
            <div class="flex items-center justify-between text-xs text-slate-400 mb-3">
              <span>Journal: Achats (AC)</span>
              <span class="px-2 py-0.5 rounded bg-emerald-500/20 text-emerald-400 font-mono">OCR IA</span>
            </div>
            <p class="font-mono text-sm text-indigo-300 mb-1">D: 601100 - Achat de fournitures</p>
            <p class="font-mono text-sm text-amber-400 mb-1">D: 445100 - TVA Déductible</p>
            <p class="font-mono text-sm text-emerald-400 mb-3">C: 401100 - Fournisseur CI</p>
            <div class="border-t border-slate-700 pt-2 flex justify-between text-xs text-slate-400">
              <span>Scan Facture.pdf</span>
              <span class="text-emerald-400"><i class="fa-solid fa-check-double"></i> Validé</span>
            </div>
          </div>

          <div class="w-80 flex-shrink-0 bg-slate-800 p-5 rounded-2xl border border-slate-700/60 shadow-lg">
            <div class="flex items-center justify-between text-xs text-slate-400 mb-3">
              <span>Tableau de Flux de Trésorerie</span>
              <span class="px-2 py-0.5 rounded bg-amber-500/20 text-amber-300 font-mono">TFT</span>
            </div>
            <p class="font-mono text-sm text-white mb-1">Flux de Trésorerie d'Exploitation</p>
            <p class="font-mono text-sm text-emerald-400 mb-3">+ 15 800 000 FCFA</p>
            <div class="border-t border-slate-700 pt-2 text-xs text-slate-400">Rapprochement bancaire synchronisé</div>
          </div>

        </div>
      </div>
    </section>

    <!-- ===== SYSCOHADA ===== -->
    <section id="syscohada" class="py-24 bg-white">
      <div class="max-w-7xl mx-auto px-6">
        <div class="text-center max-w-3xl mx-auto mb-16 reveal">
          <span class="text-indigo-600 font-semibold text-sm uppercase tracking-wider">Conformité Totale</span>
          <h2 class="text-3xl md:text-5xl font-extrabold text-slate-900 mt-2 mb-4">Générez vos Liasses Fiscales SYSCOHADA en un clic</h2>
          <p class="text-slate-600">Plus de stress lors des clôtures annuelles. Flow Compta prépare automatiquement tous les états réglementaires exigés par la Direction Générale des Impôts.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

          <div class="glass-card p-8 rounded-3xl border border-slate-200 hover:border-indigo-500/50 transition-all duration-300 hover:shadow-xl reveal">
            <div class="w-12 h-12 bg-indigo-100 rounded-2xl flex items-center justify-center text-indigo-600 mb-6 text-xl">
              <i class="fa-solid fa-file-invoice-dollar"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-3">Bilan Actif &amp; Passif</h3>
            <p class="text-slate-600 text-sm mb-6 leading-relaxed">Présentation détaillée des immobilisations, stocks, créances, capitaux propres et dettes selon le système normal SYSCOHADA révisé.</p>
            <ul class="text-xs text-slate-500 space-y-2 font-medium">
              <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-500"></i> Équilibre automatique Actif = Passif</li>
              <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-500"></i> Contrôle de cohérence en temps réel</li>
            </ul>
          </div>

          <div class="glass-card p-8 rounded-3xl border border-slate-200 hover:border-indigo-500/50 transition-all duration-300 hover:shadow-xl reveal">
            <div class="w-12 h-12 bg-violet-100 rounded-2xl flex items-center justify-center text-violet-600 mb-6 text-xl">
              <i class="fa-solid fa-chart-line"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-3">Compte de Résultat</h3>
            <p class="text-slate-600 text-sm mb-6 leading-relaxed">Calcul précis du Marge Brute, de la Valeur Ajoutée, de l'EBE et du Résultat Net d'exploitation.</p>
            <ul class="text-xs text-slate-500 space-y-2 font-medium">
              <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-500"></i> Intégration des SIG (Soldes Intermédiaires)</li>
              <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-500"></i> Export direct pour déclaration fiscale</li>
            </ul>
          </div>

          <div class="glass-card p-8 rounded-3xl border border-slate-200 hover:border-indigo-500/50 transition-all duration-300 hover:shadow-xl reveal">
            <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center text-emerald-600 mb-6 text-xl">
              <i class="fa-solid fa-arrow-right-arrow-left"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-3">Tableau des Flux (TFT)</h3>
            <p class="text-slate-600 text-sm mb-6 leading-relaxed">Analyse complète de la variation de trésorerie liée aux activités d'exploitation, d'investissement et de financement.</p>
            <ul class="text-xs text-slate-500 space-y-2 font-medium">
              <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-500"></i> Flux d'Exploitation &amp; d'Investissement</li>
              <li class="flex items-center gap-2"><i class="fa-solid fa-circle-check text-emerald-500"></i> Rapprochement des disponibilités</li>
            </ul>
          </div>

        </div>
      </div>
    </section>

    <!-- ===== FONCTIONNALITÉS ===== -->
    <section id="features" class="py-24 bg-slate-50">
      <div class="max-w-7xl mx-auto px-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

          <div class="reveal">
            <span class="text-indigo-600 font-semibold text-sm uppercase tracking-wider">Pourquoi Flow Compta ?</span>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mt-2 mb-6">Conçu sur mesure pour les cabinets et PME en Côte d'Ivoire.</h2>
            <div class="space-y-6">
              <div class="flex gap-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center flex-shrink-0 mt-1"><i class="fa-solid fa-brain"></i></div>
                <div>
                  <h4 class="text-lg font-bold text-slate-900">Intelligence Artificielle Intégrée</h4>
                  <p class="text-slate-600 text-sm mt-1">Saisie automatisée à partir de vos factures scannées, prédictions financières et détection des anomalies.</p>
                </div>
              </div>
              <div class="flex gap-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center flex-shrink-0 mt-1"><i class="fa-solid fa-users"></i></div>
                <div>
                  <h4 class="text-lg font-bold text-slate-900">Collaboration Multi-utilisateurs</h4>
                  <p class="text-slate-600 text-sm mt-1">Donnez des accès personnalisés à vos collaborateurs, comptables et administrateurs en toute sécurité.</p>
                </div>
              </div>
              <div class="flex gap-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center flex-shrink-0 mt-1"><i class="fa-solid fa-shield-halved"></i></div>
                <div>
                  <h4 class="text-lg font-bold text-slate-900">Sécurité &amp; Sauvegardes Cloud</h4>
                  <p class="text-slate-600 text-sm mt-1">Vos données comptables sont cryptées et sauvegardées en continu sur des serveurs hautement sécurisés.</p>
                </div>
              </div>
            </div>
          </div>

          <div id="about" class="relative reveal">
            <div class="bg-gradient-to-tr from-indigo-500 to-violet-500 rounded-3xl p-8 text-white shadow-2xl">
              <h3 class="text-2xl font-bold mb-4">Support de proximité</h3>
              <p class="text-indigo-100 text-sm mb-6">Une équipe d'experts à Abidjan disponible pour vous accompagner dans la prise en main du logiciel.</p>
              <div class="p-4 bg-white/10 rounded-2xl backdrop-blur-md border border-white/20">
                <p class="text-xs uppercase tracking-wider text-indigo-200">Besoin d'aide ?</p>
                <p class="text-lg font-bold mt-1">07 67 13 19 93</p>
                <p class="text-xs text-indigo-200 mt-1">it.dcknowing@gmail.com</p>
              </div>
            </div>
          </div>

        </div>
      </div>
    </section>

    <!-- ===== CTA ===== -->
    <section class="py-20 bg-indigo-600 text-white relative overflow-hidden">
      <div class="max-w-5xl mx-auto px-6 text-center relative z-10 reveal">
        <h2 class="text-3xl md:text-5xl font-extrabold mb-6">Prêt à moderniser votre gestion comptable ?</h2>
        <p class="text-indigo-100 text-lg max-w-2xl mx-auto mb-8">Rejoignez la nouvelle référence en Côte d'Ivoire. Créez votre compte en moins de 2 minutes.</p>
        <a href="{{ route('landing.pricing') }}" class="inline-flex items-center gap-3 px-8 py-4 bg-white text-indigo-600 font-bold rounded-2xl shadow-2xl hover:bg-slate-100 transition-all transform hover:-translate-y-1">
          <span>Démarrer gratuitement</span>
          <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>
    </section>

  </main>

  <!-- ===== FOOTER ===== -->
  <footer class="bg-slate-950 text-slate-400 py-12 border-t border-slate-800">
    <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-6 text-sm">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center text-white">
          <i class="fa-solid fa-bolt text-sm"></i>
        </div>
        <span class="text-white font-bold text-base">Flow Compta</span>
      </div>
      <p>© 2026 Flow Compta by <strong>DCKnowing</strong>. Tous droits réservés.</p>
      <div class="flex items-center gap-6">
        <span>Support: <strong>07 67 13 19 93</strong></span>
        <a href="mailto:it.dcknowing@gmail.com" class="hover:text-white transition-colors">it.dcknowing@gmail.com</a>
      </div>
    </div>
  </footer>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const reveals = document.querySelectorAll('.reveal');
      const revealOnScroll = () => {
        const wh = window.innerHeight;
        reveals.forEach(el => {
          if (el.getBoundingClientRect().top < wh - 100) el.classList.add('active');
        });
      };
      window.addEventListener('scroll', revealOnScroll);
      revealOnScroll();
    });
  </script>

</body>
</html>