<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Résultats du formulaire</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
        }

        .question {
            margin-bottom: 20px;
        }

        .response {
            margin-left: 20px;
        }

        .stats {
            color: #666;
            font-size: 0.9em;
        }
    </style>
</head>

<body>
    <h1>{{ $form->title }}</h1>
    <p>Module : {{ $form->module->name }}</p>
    <p>Professeur : {{ $form->module->professor->name }}</p>
    <hr>

    @foreach ($responses as $response)
        <div class="question">
            <h3>{{ $response['question'] }}</h3>

            @if (in_array($response['type'], ['checkbox', 'radio']))
                @php
                    $responsesCollection = collect($response['responses']);
                    $values = $responsesCollection->pluck('value')->flatten();
                    $counts = $values->countBy();
                    $total = $responsesCollection->count();
                @endphp

                @foreach ($counts as $answer => $count)
                    <div class="response">
                        {{ $answer }}: {{ $count }} réponses ({{ round(($count / $total) * 100) }}%)
                    </div>
                @endforeach
            @else
                @foreach ($response['responses'] as $answer)
                    <div class="response">
                        "{{ $answer['value'] }}"
                        @if (isset($answer['student']))
                            - {{ $answer['student']['name'] }}
                        @endif
                    </div>
                @endforeach
            @endif
        </div>
    @endforeach
</body>

</html>
