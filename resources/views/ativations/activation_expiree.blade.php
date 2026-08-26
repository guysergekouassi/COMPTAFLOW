<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lien d'activation expiré — ComptaFlow</title>
    <style>
        body { margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center;
               background:#f4f6f8; font-family:Arial,Helvetica,sans-serif; color:#1f2933; }
        .carte { background:#fff; padding:32px; border-radius:8px; width:100%; max-width:420px;
                 box-shadow:0 1px 3px rgba(0,0,0,.12); }
        h1 { font-size:20px; margin:0 0 12px; }
        p { color:#616e7c; font-size:14px; line-height:1.6; }
        a { color:#2563eb; }
    </style>
</head>
<body>
    <div class="carte">
        <h1>Ce lien n'est plus valable</h1>
        <p>
            Un lien d'activation ne fonctionne qu'une fois, et il expire au bout de sept jours.
            Celui-ci a déjà servi, ou son délai est passé.
        </p>
        <p>
            Demandez-en un nouveau à l'administrateur de votre dossier, puis revenez à la
            <a href="{{ route('login') }}">page de connexion</a>.
        </p>
    </div>
</body>
</html>
