@component('mail::message')
    # Résultats du formulaire

    Bonjour,

    Vous trouverez ci-joint les résultats du formulaire "{{ $form->title }}" pour le module {{ $form->module->name }}.

    Cordialement,
    {{ config('app.name') }}
@endcomponent
