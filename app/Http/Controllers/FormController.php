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
use App\Services\PdfService;
use App\Mail\FormResultsPdfMail;
use Illuminate\Support\Facades\Storage;

class FormController extends Controller
{
    protected $pdfService;

    public function __construct(PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Forms/Index', [
            'forms' => Form::with(['module.professor', 'module.year'])
                ->orderBy('created_at', 'desc')
                ->get(),
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

            // Créer le formulaire avec statut 'draft'
            $form = Form::create([
                'title' => $validated['title'],
                'module_id' => $validated['module_id'],
                'statut' => 'draft'  // Changé de 'open' à 'draft'
            ]);

            // Attacher le module après que le formulaire soit créé et ait un ID
            \DB::table('modules_forms')->insert([
                'module_id' => $validated['module_id'],
                'form_id' => $form->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Optionnel : ajouter le module principal aussi dans la table pivot
            // pour maintenir la cohérence des données
            $form->additionalModules()->attach($validated['module_id']);

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
        if ($form->statut !== 'draft') {
            return back()->withErrors(['error' => 'Ce formulaire ne peut plus être modifié']);
        }
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
        if ($form->statut === 'pending') {
            return back()->withErrors(['error' => 'Ce formulaire ne peut pas être supprimé car il est en cours']);
        }
        $form->delete();
        return redirect()->route('forms.index')->with('success', 'Formulaire supprimé avec succès');
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
                'statut' => 'draft'  // Changé de 'open' à 'draft'
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
            if ($form->statut !== 'draft') {
                throw new \Exception('Ce formulaire ne peut plus être modifié');
            }

            $form->load('module');

            if (!$form->module) {
                throw new \Exception('Module non trouvé pour ce formulaire');
            }

            $students = $form->module->students;

            if ($students->isEmpty()) {
                throw new \Exception('Aucun étudiant trouvé dans ce module');
            }

            \DB::beginTransaction();

            // Créer les tokens et envoyer les emails
            foreach ($students as $student) {
                $token = FormAccessToken::create([
                    'student_id' => $student->id,
                    'form_id' => $form->id,
                    'token' => Str::uuid(),
                    'expires_at' => now()->addDays(7),
                ]);

                Mail::to($student->email)->send(new FormAccessEmail($token));
            }

            // Mettre à jour le statut du formulaire
            $form->update(['statut' => 'pending']);

            \DB::commit();

            // Redirection simple vers l'index
            return redirect()->route('forms.index')
                ->with('success', 'Les emails ont été envoyés avec succès');
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function checkFormStatus(Form $form)
    {
        try {
            $totalStudents = $form->module->students->count();
            $respondedStudents = Response::where('form_question_id', $form->questions->first()->id)
                ->where('is_temporary', false)
                ->distinct('student_id')
                ->count();

            $hasValidTokens = FormAccessToken::where('form_id', $form->id)
                ->where('expires_at', '>', now())
                ->exists();

            if (($totalStudents === $respondedStudents || !$hasValidTokens) && $form->statut === 'pending') {
                $form->update(['statut' => 'completed']);
            }

            return response()->json([
                'status' => $form->statut,
                'message' => 'Statut vérifié avec succès'
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la vérification du statut:', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Erreur lors de la vérification du statut'
            ], 500);
        }
    }

    private function getFormattedResponses(Form $form)
    {
        $responses = Response::with(['student'])
            ->where('is_temporary', false)
            ->whereIn('form_question_id', $form->questions->pluck('id'))
            ->get();

        return $form->questions->map(function ($question) use ($responses) {
            $questionResponses = $responses->where('form_question_id', $question->id);

            return [
                'question' => $question->label,
                'type' => $question->questionType->type,
                'responses' => $questionResponses->map(function ($response) {
                    return [
                        'value' => $response->answers['value'],
                        'student' => $response->student ? [
                            'name' => $response->student->name,
                            'email' => $response->student->email
                        ] : null
                    ];
                })->toArray()
            ];
        })->toArray();
    }

    public function answer(string $token)
    {
        try {
            $accessToken = FormAccessToken::where('token', $token)
                ->with([
                    'form.questions.choices',
                    'form.questions.questionType'
                ])
                ->where('expires_at', '>', now())
                ->where('used', false) // On garde cette vérification ici
                ->firstOrFail();

            // Récupérer toutes les réponses existantes (temporaires ou non)
            $existingResponses = Response::where('student_id', $accessToken->student_id)
                ->whereIn('form_question_id', $accessToken->form->questions->pluck('id'))
                ->where('is_temporary', true) // On ne récupère que les réponses temporaires
                ->get();

            // Récupérer les réponses temporaires existantes
            $savedAnswers = [];
            foreach ($existingResponses as $response) {
                $savedAnswers[$response->form_question_id] = $response->answers['value'];
            }

            return Inertia::render('Forms/Answer', [
                'form' => $accessToken->form,
                'token' => $token,
                'savedAnswers' => $savedAnswers
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur:', ['message' => $e->getMessage()]);
            return redirect()->route('forms.error');
        }
    }

    public function saveProgress(Request $request, string $token)
    {
        try {
            $accessToken = FormAccessToken::where('token', $token)
                ->where('expires_at', '>', now())
                ->firstOrFail(); // On retire la vérification 'used' ici

            // Vérifier si le token n'a pas déjà été utilisé pour une soumission finale
            if ($accessToken->used) {
                return back()->withErrors(['error' => 'Ce formulaire a déjà été soumis']);
            }

            $answers = $request->input('answers');

            foreach ($answers as $questionId => $answer) {
                Response::updateOrCreate(
                    [
                        'form_question_id' => $questionId,
                        'student_id' => $accessToken->student_id,
                        'is_temporary' => true
                    ],
                    [
                        'answers' => ['value' => $answer]
                    ]
                );
            }

            return back();
        } catch (\Exception $e) {
            \Log::error('Erreur sauvegarde temporaire', ['error' => $e->getMessage()]); // Correction ici
            return back()->withErrors(['error' => 'Erreur lors de la sauvegarde temporaire']);
        }
    }

    public function submitAnswer(Request $request, string $token)
    {
        try {
            \DB::beginTransaction();

            $accessToken = FormAccessToken::lockForUpdate() // Verrouiller le token pour éviter les soumissions simultanées
                ->where('token', $token)
                ->where('expires_at', '>', now())
                ->first();

            if (!$accessToken || $accessToken->used) {
                return redirect()->route('forms.error')
                    ->with('error', 'Ce formulaire a déjà été soumis ou est invalide');
            }

            $validated = $request->validate([
                'answers' => 'required|array'
            ]);

            // Supprimer les réponses temporaires
            Response::where('student_id', $accessToken->student_id)
                ->where('is_temporary', true)
                ->whereIn('form_question_id', array_keys($validated['answers']))
                ->delete();

            // Créer les réponses finales
            foreach ($validated['answers'] as $questionId => $answer) {
                Response::create([
                    'form_question_id' => $questionId,
                    'student_id' => $accessToken->student_id,
                    'answers' => ['value' => $answer],
                    'is_temporary' => false
                ]);
            }

            // Marquer le token comme utilisé
            $accessToken->used = true;
            $accessToken->save();

            // Vérifier si tous les étudiants ont répondu
            $form = $accessToken->form;
            $totalStudents = $form->module->students->count();
            $respondedStudents = Response::where('form_question_id', $form->questions->first()->id)
                ->where('is_temporary', false)
                ->distinct('student_id')
                ->count();

            // Si tous les étudiants ont répondu, marquer comme terminé
            if ($totalStudents === $respondedStudents) {
                $form->update(['statut' => 'completed']);
            }

            \DB::commit();

            // Retourner une réponse Inertia plutôt qu'une redirection standard
            return Inertia::location(route('forms.thankyou'));
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Erreur soumission', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Erreur lors de la soumission']);
        }
    }

    public function results(Form $form)
    {
        // Vérifier si le formulaire est accessible
        if ($form->statut !== 'pending' && $form->statut !== 'completed') {
            return redirect()->route('forms.index')
                ->with('error', 'Ce formulaire n\'est pas accessible');
        }

        $form->load([
            'module' => function ($query) {
                $query->with(['professor', 'year', 'students']);
            },
            'questions.questionType',
            'questions.choices'
        ]);

        $modules = Module::with(['professor', 'year', 'students'])->get();
        $responses = Response::with(['student'])
            ->where('is_temporary', false)
            ->whereIn('form_question_id', $form->questions->pluck('id'))
            ->get();

        $uniqueStudentsCount = $responses->pluck('student_id')->unique()->count();
        $totalStudents = $form->module->students->count();
        $responsesByQuestion = $responses->groupBy('form_question_id');

        // Simplification du formatage des réponses
        $formattedResponses = $form->questions->map(function ($question) use ($responsesByQuestion) {
            $questionResponses = $responsesByQuestion->get($question->id, collect());
            $type = $question->questionType->type;

            return [
                'question_id' => $question->id,
                'question' => $question->label,
                'type' => $type,
                'responses' => $questionResponses->map(function ($response) {
                    // Extraction directe de la valeur sans structure JSON
                    $value = is_array($response->answers) ?
                        ($response->answers['value'] ?? '') : (is_string($response->answers) ? json_decode($response->answers, true)['value'] ?? '' : '');

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
            'totalStudents' => $totalStudents,
            'modules' => $modules
        ]);
    }

    public function sendPdf(Form $form)
    {
        try {
            if ($form->statut !== 'completed') {
                return back()->withErrors(['error' => 'Le formulaire doit être terminé pour envoyer le PDF']);
            }

            // Charger les relations nécessaires
            $form->load(['module.professor', 'questions.questionType']);

            if (!$form->module || !$form->module->professor) {
                return back()->withErrors(['error' => 'Impossible de trouver le professeur associé au module']);
            }

            try {
                $responses = $this->getFormattedResponses($form);
                $pdfPath = $this->pdfService->generateFormResultsPdf($form, $responses);

                Mail::to($form->module->professor->email)
                    ->send(new FormResultsPdfMail($form, $pdfPath));

                if (file_exists($pdfPath)) {
                    unlink($pdfPath);
                }

                return back()->with('success', 'PDF envoyé avec succès');
            } catch (\Exception $e) {
                if (isset($pdfPath) && file_exists($pdfPath)) {
                    unlink($pdfPath);
                }
                throw $e;
            }
        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'envoi du PDF:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Erreur lors de l\'envoi du PDF: ' . $e->getMessage()]);
        }
    }
}
