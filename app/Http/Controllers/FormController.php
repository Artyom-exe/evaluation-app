<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\FormSubmissionMail;
use App\Models\Form;
use App\Models\Module;
use App\Models\Year;
use App\Models\QuestionType;
use App\Models\FormQuestion;
use App\Models\Choice;
use App\Models\Student;
use App\Models\Response; // Ajout de l'import manquant
use Inertia\Inertia;

class FormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Forms/Index', [
            'forms' => Form::with(['module.professor', 'module.year'])->get(),
            'modules' => Module::with(['professor', 'year'])->get(),
            'years' => Year::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Forms/Create', [
            'modules' => Module::with('professor')->get(),
            'questionTypes' => QuestionType::all() // Utilisation du modèle QuestionType
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            \Log::info('Données reçues:', $request->all());

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'module_id' => 'required|exists:modules,id',
                'questions' => 'required|array|min:1',
                'questions.*.type' => 'required|string',
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

            \Log::info('Formulaire créé:', ['form' => $form->toArray()]);

            // Ajouter les questions
            foreach ($validated['questions'] as $questionData) {
                \Log::info('Traitement question:', $questionData);

                $questionType = QuestionType::where('type', $questionData['type'])->first();

                if (!$questionType) {
                    throw new \Exception("Type de question non trouvé: " . $questionData['type']);
                }

                $question = new FormQuestion([
                    'label' => $questionData['label'],
                    'questions_types_id' => $questionType->id
                ]);

                $form->questions()->save($question);

                if (!empty($questionData['options'])) {
                    foreach ($questionData['options'] as $optionText) {
                        if (!empty($optionText)) {
                            $question->choices()->create(['text' => $optionText]);
                        }
                    }
                }
            }

            \DB::commit();

            // Envoyer le formulaire aux étudiants inscrits au module
            $this->sendFormToStudents($form);

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


    public function sendFormToStudents(Form $form)
    {
        $students = Student::whereHas('modules', function ($query) use ($form) {
            $query->where('module_id', $form->module_id);
        })->get();

        foreach ($students as $student) {
            Mail::to($student->email)->send(new FormSubmissionMail($form));
        }
    }

    public function showSubmissionForm(Form $form)
    {
        return view('forms.submit', [
            'form' => $form
        ]);
    }

    public function storeSubmission(Request $request, Form $form)
    {
        $validated = $request->validate([
            'responses.*' => 'required'
        ]);

        // ID test pour simuler un étudiant
        $testStudentId = 1;

        foreach ($request->responses as $questionId => $answer) {
            Response::create([
                'form_question_id' => $questionId,
                'student_id' => $testStudentId, // ID fixe pour le test
                'answers' => $answer // pas besoin de json_encode ici
            ]);
        }

        return redirect()->back()->with('success', 'Merci pour votre réponse !');
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
        return Inertia::render('Forms/Edit', [
            'form' => Form::with(['questions.choices', 'questions.questionType'])->find($form->id),
            'modules' => Module::with('professor')->get(),
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

    public function showResults(Form $form)
    {
        $responses = Response::with('form_question')
            ->whereIn('form_question_id', $form->questions->pluck('id'))
            ->get()
            ->groupBy('student_id');

        return Inertia::render('Forms/Results', [
            'form' => $form->load('questions'),
            'responses' => $responses
        ]);
    }
}
