<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Form;
use App\Models\Module;
use App\Models\Year;
use App\Models\QuestionType;
use App\Models\FormQuestion;
use App\Models\Choice;
use Inertia\Inertia;
use App\Models\Professor;
use App\Mail\FormAccessEmail;
use App\Models\FormAccessToken;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Models\Response;


class FormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Forms/Index', [
            'forms' => Form::with(['module.professor', 'module.year'])->get(),
            'modules' => Module::with(['professor', 'year', 'students'])->get(), // Ajout de 'year' dans le with
            'years' => Year::all(),
            'professors' => Professor::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $modules = Module::with(['students', 'professor', 'year'])->get();
        return Inertia::render('Forms/Create', [
            'modules' => $modules,
            'questionTypes' => QuestionType::all() // Utilisation du modèle QuestionType
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Log complet des données reçues
            \Log::info('Données reçues:', $request->all());

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'module_id' => 'required|exists:modules,id',
                'questions' => 'required|array|min:1',
                'questions.*.type' => 'required|string',  // Suppression de la validation exists
                'questions.*.label' => 'required|string',
                'questions.*.options' => 'nullable|array'
            ]);

            \DB::beginTransaction();

            // Créer le formulaire
            $form = Form::create([
                'title' => $validated['title'],
                'module_id' => $validated['module_id'],
                'statut' => 'open'
            ]);

            // Log après création du formulaire
            \Log::info('Formulaire créé:', ['form' => $form->toArray()]);

            // Pour chaque question
            foreach ($validated['questions'] as $questionData) {
                // Log de chaque question
                \Log::info('Traitement question:', $questionData);

                // Recherche du type de question
                $questionType = QuestionType::where('type', $questionData['type'])->first();

                if (!$questionType) {
                    throw new \Exception("Type de question non trouvé: " . $questionData['type']);
                }

                // Créer la question
                $question = new FormQuestion([
                    'label' => $questionData['label'],
                    'questions_types_id' => $questionType->id
                ]);

                $form->questions()->save($question);

                // Pour les questions avec options
                if (!empty($questionData['options'])) {
                    foreach ($questionData['options'] as $optionText) {
                        if (!empty($optionText)) {
                            $question->choices()->create(['text' => $optionText]);
                        }
                    }
                }
            }

            \DB::commit();
            return redirect()->route('forms.index');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Erreur détaillée:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'error' => 'Erreur lors de la création du formulaire: ' . $e->getMessage()
            ]);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Form $form)
    {
        $modules = Module::with(['students', 'professor', 'year'])->get();
        return Inertia::render('Forms/Edit', [
            'form' => Form::with(['questions.choices', 'questions.questionType'])->find($form->id),
            'modules' => $modules,
            'questionTypes' => QuestionType::all()
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Form $form)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'module_id' => 'required|exists:modules,id',
                'questions' => 'required|array|min:1',
                'questions.*.type' => 'required|string',
                'questions.*.label' => 'required|string',
                'questions.*.options' => 'nullable|array'
            ]);

            \DB::beginTransaction();

            // Mise à jour des informations de base du formulaire
            $form->update([
                'title' => $validated['title'],
                'module_id' => $validated['module_id']
            ]);

            // Suppression des anciennes questions et leurs choix
            $form->questions()->delete();

            // Création des nouvelles questions
            foreach ($validated['questions'] as $questionData) {
                $questionType = QuestionType::where('type', $questionData['type'])->first();

                $question = new FormQuestion([
                    'label' => $questionData['label'],
                    'questions_types_id' => $questionType->id
                ]);

                $form->questions()->save($question);

                // Ajout des options si présentes
                if (!empty($questionData['options'])) {
                    foreach ($questionData['options'] as $optionText) {
                        if (!empty($optionText)) {
                            $question->choices()->create(['text' => $optionText]);
                        }
                    }
                }
            }

            \DB::commit();
            return redirect()->route('forms.index')->with('success', 'Formulaire mis à jour avec succès');
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->withErrors([
                'error' => 'Erreur lors de la mise à jour du formulaire: ' . $e->getMessage()
            ]);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Form $form)
    {
        $form->delete();
        return redirect()->route('forms.index');
    }

    public function duplicate(Form $form)
    {
        try {
            \DB::beginTransaction();

            // Chargement explicite des relations nécessaires
            $form->load(['questions.choices', 'questions.questionType']);

            // Créer une copie du formulaire
            $newForm = Form::create([
                'title' => $form->title . ' (copie)',
                'module_id' => $form->module_id,
                'statut' => 'open'
            ]);

            // Dupliquer les questions avec leurs options
            foreach ($form->questions as $question) {
                // Créer la nouvelle question
                $newQuestion = new FormQuestion();
                $newQuestion->form_id = $newForm->id;
                $newQuestion->label = $question->label;
                $newQuestion->questions_types_id = $question->questions_types_id;
                $newQuestion->save();

                // Dupliquer les choix de la question
                if ($question->choices && $question->choices->count() > 0) {
                    foreach ($question->choices as $choice) {
                        $newChoice = new Choice();
                        $newChoice->form_question_id = $newQuestion->id;
                        $newChoice->text = $choice->text;
                        $newChoice->save();
                    }
                }
            }

            \DB::commit();

            // Log de débogage
            \Log::info('Formulaire dupliqué avec succès', [
                'original_id' => $form->id,
                'new_id' => $newForm->id,
                'questions_count' => $newForm->questions()->count()
            ]);

            return redirect()->route('forms.index')
                ->with('success', 'Formulaire dupliqué avec succès');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Erreur de duplication:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Erreur lors de la duplication: ' . $e->getMessage());
        }
    }

    public function sendAccess(Form $form)
    {
        try {
            if (!$form->module) {
                throw new \Exception('Module non trouvé pour ce formulaire');
            }

            $students = $form->module->students;

            if ($students->isEmpty()) {
                throw new \Exception('Aucun étudiant trouvé dans ce module');
            }

            foreach ($students as $student) {
                \Log::info('Envoi email à:', ['email' => $student->email]);

                $token = FormAccessToken::create([
                    'student_id' => $student->id,
                    'form_id' => $form->id,
                    'token' => Str::uuid(),
                    'expires_at' => now()->addDays(7),
                ]);

                try {
                    Mail::to($student->email)->send(new FormAccessEmail($token));
                } catch (\Exception $mailError) {
                    \Log::error('Erreur d\'envoi email:', ['error' => $mailError->getMessage()]);
                }
            }

            return back()->with('success', 'Les emails ont été envoyés avec succès');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'envoi des emails:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Erreur lors de l\'envoi des emails: ' . $e->getMessage()
            ], 500);
        }
    }

    public function answer(string $token)
    {
        try {
            $accessToken = FormAccessToken::where('token', $token)
                ->with([
                    'form.questions.choices',
                    'form.questions.questionType' // Assurez-vous que cette relation est chargée
                ])
                ->where('used', false)
                ->where('expires_at', '>', now())
                ->firstOrFail();

            // Debug pour vérifier les données
            \Log::info('Question Types:', [
                'questions' => $accessToken->form->questions->map(function ($q) {
                    return [
                        'id' => $q->id,
                        'type' => $q->questionType?->type
                    ];
                })
            ]);

            return Inertia::render('Forms/Answer', [
                'form' => $accessToken->form,
                'token' => $token
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur:', ['message' => $e->getMessage()]);
            return redirect()->route('forms.error');
        }
    }

    public function submitAnswer(Request $request, string $token)
    {
        try {
            $accessToken = FormAccessToken::where('token', $token)
                ->where('used', false)
                ->where('expires_at', '>', now())
                ->firstOrFail();

            $validated = $request->validate([
                'answers' => 'required|array'
            ]);

            \Log::info('Réponses reçues:', $validated['answers']);

            foreach ($validated['answers'] as $questionId => $answer) {
                $formQuestion = FormQuestion::with('questionType')->find($questionId);

                // Pour les questions de type checkbox
                if ($formQuestion->questionType->type === 'checkbox') {
                    // S'assurer que nous avons un tableau non vide
                    if (!is_array($answer)) {
                        $answer = [$answer];
                    }

                    // Filtrer les valeurs vides
                    $answer = array_filter($answer);

                    // Ne pas enregistrer si le tableau est vide
                    if (empty($answer)) {
                        continue;
                    }
                }

                Response::create([
                    'form_question_id' => $questionId,
                    'student_id' => $accessToken->student_id,
                    'answers' => ['value' => $answer]
                ]);

                \Log::info('Réponse sauvegardée:', [
                    'question_id' => $questionId,
                    'type' => $formQuestion->questionType->type,
                    'answer' => $answer
                ]);
            }

            $accessToken->update(['used' => true]);
            return redirect()->route('forms.thankyou');
        } catch (\Exception $e) {
            \Log::error('Erreur soumission:', $e->getMessage());
            return back()->withErrors(['error' => 'Erreur lors de la soumission']);
        }
    }

    public function results(Form $form)
    {
        \Log::info('Début de la méthode results', ['form_id' => $form->id]);

        // Charger le module avec ses étudiants directement
        $form->load([
            'module.professor',
            'module.year',
            'module.students', // Chargement explicite des étudiants
            'questions.questionType',
            'questions.choices' // Ajout des choix pour les questions à choix multiples
        ]);

        \Log::info('Module chargé', [
            'module_id' => $form->module->id,
            'students_count' => $form->module->students->count()
        ]);

        // Récupérer toutes les réponses avec les étudiants
        $responses = Response::with(['student'])
            ->whereIn('form_question_id', $form->questions->pluck('id'))
            ->get();

        // Compter le nombre d'étudiants uniques ayant répondu
        $uniqueStudentsCount = $responses->pluck('student_id')->unique()->count();

        // Nombre total d'étudiants dans le module
        $totalStudents = $form->module->students->count();

        \Log::info('Statistiques', [
            'total_students' => $totalStudents,
            'unique_respondents' => $uniqueStudentsCount
        ]);

        // Grouper les réponses par question
        $responsesByQuestion = $responses->groupBy('form_question_id');

        // Formater les données pour la vue
        $formattedResponses = $form->questions->map(function ($question) use ($responsesByQuestion) {
            $questionResponses = $responsesByQuestion->get($question->id, collect());
            $type = $question->questionType->type;

            return [
                'question_id' => $question->id,
                'question' => $question->label,
                'type' => $type,
                'responses' => $questionResponses->map(function ($response) {
                    // Récupérer la valeur brute de la réponse
                    $value = $response->answers['value'];

                    // Si la valeur est une chaîne JSON, la décoder
                    if (is_string($value) && json_validate($value)) {
                        $value = json_decode($value, true);
                    }

                    return [
                        'value' => $value,
                        'student' => $response->student ? [
                            'name' => $response->student->name,
                            'email' => $response->student->email
                        ] : null
                    ];
                })
            ];
        });

        return Inertia::render('Forms/Results', [
            'form' => $form,
            'responses' => $formattedResponses,
            'studentsCount' => $uniqueStudentsCount,
            'totalStudents' => $totalStudents
        ]);
    }
}
