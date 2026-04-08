<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue sur Flow Compta</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f4f7fa;
            color: #334155;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f4f7fa;
            padding-bottom: 40px;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            padding: 40px 20px;
            text-align: center;
            color: white;
        }
        .header .logo-icon {
            font-size: 32px;
            margin-bottom: 10px;
            display: block;
        }
        .header h1 {
            margin: 0;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.025em;
        }
        .content {
            padding: 40px;
        }
        .welcome-title {
            color: #0f172a;
            font-size: 22px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 16px;
            text-align: center;
        }
        .welcome-text {
            font-size: 16px;
            color: #475569;
            text-align: center;
            line-height: 1.6;
            margin-bottom: 32px;
        }
        .recap-box {
            background-color: #f8fafc;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
            border: 1px solid #e2e8f0;
        }
        .recap-title {
            font-size: 14px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 16px;
            display: block;
            text-align: center;
        }
        .info-row {
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #64748b;
            font-size: 14px;
        }
        .info-value {
            font-weight: 700;
            color: #1e40af;
            font-size: 14px;
        }
        .password-value {
            background: #dbeafe;
            padding: 4px 10px;
            border-radius: 6px;
            color: #1e40af;
            font-family: monospace;
            font-size: 15px;
        }
        .cta-container {
            text-align: center;
            margin-top: 40px;
        }
        .btn {
            display: inline-block;
            background-color: #1e40af;
            color: #ffffff !important;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 16px;
            transition: background-color 0.2s;
            box-shadow: 0 4px 6px -1px rgba(30, 64, 175, 0.4);
        }
        .footer {
            background-color: #f8fafc;
            padding: 30px;
            text-align: center;
            font-size: 13px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
        .support-info {
            margin-bottom: 20px;
            font-style: normal;
        }
        .support-link {
            color: #1e40af;
            text-decoration: none;
            font-weight: 600;
        }
        .social-notice {
            margin-top: 20px;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <span class="logo-icon">⚡</span>
                <h1>Flow Compta</h1>
            </div>
            
            <div class="content">
                <h2 class="welcome-title">Félicitations, {{ $user->name }} !</h2>
                <p class="welcome-text">
                    Votre espace de gestion comptable est désormais actif. Nous sommes ravis de vous accompagner dans la digitalisation de votre comptabilité.
                </p>
                
                <div class="recap-box">
                    <span class="recap-title">Vos identifiants de connexion</span>
                    
                    <div style="display: table; width: 100%;">
                        <div style="display: table-row;">
                            <div style="display: table-cell; padding: 12px 0; border-bottom: 1px solid #e2e8f0; color: #64748b; font-weight: 600; font-size: 14px;">Email Log :</div>
                            <div style="display: table-cell; padding: 12px 0; border-bottom: 1px solid #e2e8f0; color: #1e40af; font-weight: 700; font-size: 14px; text-align: right;">{{ $user->email_adresse }}</div>
                        </div>
                        <div style="display: table-row;">
                            <div style="display: table-cell; padding: 12px 0; border-bottom: 1px solid #e2e8f0; color: #64748b; font-weight: 600; font-size: 14px;">Mot de passe :</div>
                            <div style="display: table-cell; padding: 12px 0; border-bottom: 1px solid #e2e8f0; color: #1e40af; font-weight: 700; font-size: 14px; text-align: right;">
                                <span style="background: #dbeafe; padding: 4px 10px; border-radius: 6px; font-family: monospace;">{{ $password }}</span>
                            </div>
                        </div>
                        <div style="display: table-row;">
                            <div style="display: table-cell; padding: 12px 0; border-bottom: 1px solid #e2e8f0; color: #64748b; font-weight: 600; font-size: 14px;">Entreprise :</div>
                            <div style="display: table-cell; padding: 12px 0; border-bottom: 1px solid #e2e8f0; color: #1e40af; font-weight: 700; font-size: 14px; text-align: right;">{{ $company->company_name }}</div>
                        </div>
                        <div style="display: table-row;">
                            <div style="display: table-cell; padding: 12px 0; color: #64748b; font-weight: 600; font-size: 14px;">Pack choisi :</div>
                            <div style="display: table-cell; padding: 12px 0; color: #1e40af; font-weight: 700; font-size: 14px; text-align: right;">{{ ucfirst($type) }}</div>
                        </div>
                    </div>
                </div>

                <div class="cta-container">
                    <a href="{{ config('app.url') }}/login" class="btn">Accéder à mon espace</a>
                </div>

                <p style="text-align: center; color: #94a3b8; font-size: 14px; margin-top: 32px;">
                    Pour votre sécurité, nous vous recommandons de modifier votre mot de passe dès votre première connexion.
                </p>
            </div>

            <div class="footer">
                <div class="support-info">
                    <strong>Une question ? Notre équipe est là pour vous :</strong><br>
                    📧 E-mail : <a href="mailto:it.dcknowing@gmail.com" class="support-link">it.dcknowing@gmail.com</a><br>
                    📞 Support Client : <span class="support-link">07 67 13 19 93</span>
                </div>
                
                <p><strong>L'équipe Flow Compta</strong></p>
                
                <div class="social-notice">
                    Cet e-mail est généré automatiquement, merci de ne pas y répondre directement.<br>
                    © {{ date('Y') }} Flow Compta. Tous droits réservés.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
