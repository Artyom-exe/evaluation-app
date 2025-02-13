<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Résultats du formulaire</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            line-height: 1.6;
            color: #2d3748;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }

        .header h1 {
            color: #1a365d;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .header-info {
            display: block;
            color: #4a5568;
            margin: 5px 0;
        }

        .question {
            background: #f7fafc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .question h3 {
            color: #2c5282;
            font-size: 18px;
            margin: 0 0 15px 0;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }

        .response {
            background: white;
            margin: 10px 0;
            padding: 12px 15px;
            border-radius: 6px;
            border-left: 4px solid #4299e1;
        }

        .stats {
            color: #718096;
            font-size: 0.9em;
        }

        .progress-bar {
            background: #edf2f7;
            height: 20px;
            border-radius: 10px;
            margin: 5px 0;
            overflow: hidden;
        }

        .progress-value {
            background: #4299e1;
            height: 100%;
        }

        .percentage {
            font-weight: bold;
            color: #2b6cb0;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #718096;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }

        .student-name {
            color: #4a5568;
            font-style: italic;
        }

        @page {
            margin: 40px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>{{ $form->title }}</h1>
        <span class="header-info">Module : {{ $form->module->name }}</span>
        <span class="header-info">Professeur : {{ $form->module->professor->name }}</span>
        <span class="header-info">Date : {{ now()->format('d/m/Y') }}</span>
    </div>

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
                        <div class="stats">
                            {{ $answer }}
                            <span class="percentage">
                                ({{ $count }} / {{ $total }} - {{ round(($count / $total) * 100) }}%)
                            </span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-value" style="width: {{ round(($count / $total) * 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            @else
                @foreach ($response['responses'] as $answer)
                    <div class="response">
                        <div class="answer-text">"{{ $answer['value'] }}"</div>
                        @if (isset($answer['student']))
                            <div class="student-name">- {{ $answer['student']['name'] }}</div>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>
    @endforeach

    <div class="footer">
        Document généré le {{ now()->format('d/m/Y à H:i') }} - {{ config('app.name') }}
    </div>
</body>

</html>
