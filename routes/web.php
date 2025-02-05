<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\FormController;
use App\Http\Controllers\QuestionTypeController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TestMailController;



Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Routes publiques pour le formulaire (hors middleware)
Route::get('/forms/{form}/submit', [FormController::class, 'showSubmissionForm'])->name('forms.submit');
Route::post('/forms/{form}/submit', [FormController::class, 'storeSubmission'])->name('forms.process');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::resource('forms', FormController::class);
    Route::post('/forms/{form}/duplicate', [FormController::class, 'duplicate'])->name('forms.duplicate');

    // Route pour afficher la vue d'assignation d'étudiant
    Route::get('/assign-student', [StudentController::class, 'showAssignStudentPage'])->name('students.assign');

    // Route pour effectuer l'assignation de l'étudiant au module
    Route::post('/assign-student', [StudentController::class, 'assignStudentToModule']);

    // Route pour afficher les résultats du formulaire
    Route::get('/forms/{form}/results', [FormController::class, 'showResults'])->name('forms.results');
});
