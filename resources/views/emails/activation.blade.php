<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Activez votre compte ComptaFlow</title>
</head>
<body style="margin:0;padding:24px;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#1f2933;">
    <div style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:8px;padding:32px;">

        <h1 style="margin:0 0 16px;font-size:20px;">Bonjour {{ $user->name }},</h1>

        <p style="line-height:1.6;">
            Le dossier comptable de <strong>{{ $company->company_name }}</strong> vient d'être ouvert
            dans ComptaFlow.
        </p>

        <p style="line-height:1.6;">
            Pour des raisons de sécurité, <strong>aucun mot de passe ne vous a été attribué</strong> :
            vous choisissez le vôtre, et vous êtes seul à le connaître.
        </p>

        <p style="margin:28px 0;text-align:center;">
            <a href="{{ $lien }}"
               style="display:inline-block;background:#2563eb;color:#ffffff;text-decoration:none;
                      padding:14px 28px;border-radius:6px;font-weight:bold;">
                Choisir mon mot de passe
            </a>
        </p>

        <p style="line-height:1.6;font-size:13px;color:#616e7c;">
            Ce lien est valable <strong>7 jours</strong> et ne fonctionne qu'une fois.
            S'il a expiré, demandez-en un nouveau depuis la page de connexion.
        </p>

        <p style="line-height:1.6;font-size:13px;color:#616e7c;">
            Si le bouton ne fonctionne pas, copiez cette adresse dans votre navigateur :<br>
            <span style="word-break:break-all;">{{ $lien }}</span>
        </p>

        <hr style="border:none;border-top:1px solid #e4e7eb;margin:24px 0;">

        <p style="line-height:1.6;font-size:12px;color:#9aa5b1;margin:0;">
            Vous ne vous attendiez pas à ce message ? Ignorez-le : sans ce lien, le compte reste
            inutilisable.
        </p>
    </div>
</body>
</html>
