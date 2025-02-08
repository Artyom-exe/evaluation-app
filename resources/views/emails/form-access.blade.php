<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: system-ui, -apple-system, sans-serif;
            line-height: 1.5;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 2rem auto;
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .title {
            color: #111827;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .description {
            color: #6b7280;
            margin-bottom: 2rem;
        }

        .button {
            display: inline-block;
            background-color: #2563eb;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            text-decoration: none;
            font-weight: 500;
            margin: 1rem 0;
        }

        .button:hover {
            background-color: #1d4ed8;
        }

        .footer {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 0.875rem;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1 class="title">Évaluation de Module</h1>
        </div>
        <div class="description">
            <p>Bonjour,</p>
            <p>Vous avez été invité(e) à participer à l'évaluation d'un de vos modules. Votre retour est important pour
                nous aider à améliorer la qualité de l'enseignement.</p>
            <p>Pour accéder au formulaire d'évaluation, cliquez sur le bouton ci-dessous :</p>
        </div>
        <div style="text-align: center;">
            <a href="{{ route('forms.answer', $token->token) }}" class="button">
                Répondre au questionnaire
            </a>
        </div>
        <div class="description">
            <p>Ce lien est personnel et ne peut être utilisé qu'une seule fois. Il expirera dans 7 jours.</p>
        </div>
        <div class="footer">
            <p>Si vous rencontrez des difficultés pour accéder au formulaire, vous pouvez copier-coller ce lien dans
                votre navigateur :</p>
            <p style="word-break: break-all;">{{ route('forms.answer', $token->token) }}</p>
        </div>
    </div>
</body>

</html>
