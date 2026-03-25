<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bienvenue sur Flow Compta</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f8fafc;
            color: #334155;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            padding: 30px;
            text-align: center;
            color: white;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .content {
            padding: 30px;
        }
        .content h2 {
            color: #0f172a;
            font-size: 20px;
            margin-top: 0;
        }
        .highlight {
            color: #2563eb;
            font-weight: bold;
        }
        .box {
            background-color: #f1f5f9;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #3b82f6;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px;
            text-align: center;
            font-size: 13px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
        .btn {
            display: inline-block;
            background-color: #1e40af;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: bold;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Flow Compta</h1>
        </div>
        <div class="content">
            <h2>Bonjour {{ $user->name }} {{ $user->last_name }},</h2>
            
            <p>Félicitations et bienvenue sur <strong>Flow Compta</strong> ! Votre inscription a été validée avec succès.</p>
            
            <div class="box">
                <p style="margin-top: 0;"><strong>Récapitulatif de votre compte :</strong></p>
                <ul style="margin-bottom: 0;">
                    <li><strong>Identifiant de connexion :</strong> <span class="highlight">{{ $user->email_adresse }}</span></li>
                    <li><strong>Entreprise / Entité :</strong> {{ $company->company_name }}</li>
                    <li><strong>Pack sélectionné :</strong> {{ ucfirst($type) }}</li>
                </ul>
            </div>

            <p>Vous pouvez dès à présent vous connecter à votre espace d'administration et commencer à configurer votre comptabilité via le bouton ci-dessous :</p>
            
            <div style="text-align: center;">
                <a href="{{ route('login') }}" class="btn">Accéder à mon espace</a>
            </div>

            <p style="margin-top: 30px;">
                <strong>Besoin d'aide ou d'informations complémentaires ?</strong><br>
                Notre équipe support est à votre entière disposition.<br>
                📧 E-mail : <a href="mailto:it.dcknowing@gmail.com" class="highlight">it.dcknowing@gmail.com</a><br>
                📞 Téléphone du cabinet : <strong>07 67 13 19 93</strong>
            </p>
            
            <p>Très cordialement,<br><strong>L'équipe Flow Compta</strong></p>
        </div>
        <div class="footer">
            Cet e-mail a été envoyé automatiquement suite à votre inscription sur Flow Compta. Veuillez ne pas y répondre directement.
        </div>
    </div>
</body>
</html>
