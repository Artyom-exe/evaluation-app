@extends('layouts.guest')

@section('content')
<div class="min-h-screen py-12 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <!-- En-tête du formulaire -->
        <div class="mb-8 text-center">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $form->title }}</h1>
            <p class="text-gray-600">Merci de prendre le temps de répondre à cette évaluation</p>
        </div>

        <!-- Card du formulaire -->
        <div class="bg-white shadow-xl rounded-lg overflow-hidden">
            <div class="p-6 sm:p-8">
                <form action="{{ route('forms.process', $form->id) }}" method="POST" class="space-y-6">
                    @csrf

                    @foreach ($form->questions as $question)
                        <div class="bg-gray-50 rounded-lg p-6 space-y-3">
                            <label class="block text-lg font-medium text-gray-900">
                                {{ $question->label }}
                            </label>

                            <input
                                type="text"
                                name="responses[{{ $question->id }}]"
                                class="w-full border-gray-300 rounded-md shadow-sm"
                                required
                            >
                        </div>
                    @endforeach

                    <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                        Soumettre
                    </button>
                </form>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-6 text-center text-sm text-gray-500">
            <p>Vos réponses resteront confidentielles</p>
        </div>
    </div>
</div>
@endsection


