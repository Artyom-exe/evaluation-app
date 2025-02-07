<!DOCTYPE html>
<html>
<head>
    <title>Accès au formulaire d'évaluation</title>
</head>
<body>
    <h1>Bonjour {{ $studentName }},</h1>

    <p>Vous avez un nouveau formulaire d'évaluation à compléter : {{ $formTitle }}</p>

    <p>Pour accéder à votre formulaire, veuillez cliquer sur le lien ci-dessous :</p>

    <a href="{{ route('forms.answer', $token) }}">Accéder au formulaire</a>

    <p>Ce lien expire le {{ $expiresAt }}.</p>

    <p>Cordialement,<br>
    L'équipe pédagogique</p>
</body>
</html>
