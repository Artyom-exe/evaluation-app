@component('mail::message')
# Bonjour,

Vous êtes invité(e) à remplir une évaluation pour le module **{{ $form->title }}**.

Cliquez sur le bouton ci-dessous pour accéder au formulaire :

@component('mail::button', ['url' => route('forms.submit', $form->id)])
Remplir l'évaluation
@endcomponent

Merci,
L'équipe pédagogique
@endcomponent

