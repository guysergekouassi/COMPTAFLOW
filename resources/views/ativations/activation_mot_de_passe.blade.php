<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Activation du compte — ComptaFlow</title>
    <style>
        body { margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center;
               background:#f4f6f8; font-family:Arial,Helvetica,sans-serif; color:#1f2933; }
        .carte { background:#fff; padding:32px; border-radius:8px; width:100%; max-width:420px;
                 box-shadow:0 1px 3px rgba(0,0,0,.12); }
        h1 { font-size:20px; margin:0 0 8px; }
        p.sous { color:#616e7c; font-size:14px; margin:0 0 24px; line-height:1.5; }
        label { display:block; font-size:13px; font-weight:bold; margin-bottom:6px; }
        input { width:100%; box-sizing:border-box; padding:11px 12px; border:1px solid #cbd2d9;
                border-radius:6px; margin-bottom:16px; font-size:14px; }
        button { width:100%; padding:13px; background:#2563eb; color:#fff; border:0; border-radius:6px;
                 font-size:15px; font-weight:bold; cursor:pointer; }
        .erreurs { background:#fdecea; border:1px solid #f5c2c0; color:#8a1c14; padding:12px;
                   border-radius:6px; font-size:13px; margin-bottom:16px; }
        .erreurs ul { margin:0; padding-left:18px; }
    </style>
</head>
<body>
    <div class="carte">
        <h1>Choisissez votre mot de passe</h1>
        <p class="sous">
            Aucun mot de passe ne vous a été attribué : vous êtes seul à connaître celui que vous
            posez ici.
        </p>

        @if ($errors->any())
            <div class="erreurs">
                <ul>
                    @foreach ($errors->all() as $erreur)
                        <li>{{ $erreur }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('activation.enregistrer', ['jeton' => $jeton]) }}">
            @csrf

            <label for="password">Mot de passe (8 caractères minimum)</label>
            <input type="password" id="password" name="password" required autofocus autocomplete="new-password">

            <label for="password_confirmation">Confirmation</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">

            <button type="submit">Activer mon compte</button>
        </form>
    </div>
</body>
</html>
