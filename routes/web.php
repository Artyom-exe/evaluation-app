<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\FormController;
use App\Http\Controllers\QuestionTypeController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ProfessorController;
use App\Http\Controllers\YearController;

// Remplacer la route racine et la route dashboard
Route::get('/', function () {
    return redirect()->route('forms.index');
});

// Routes publiques pour les réponses aux formulaires
Route::get('/forms/answer/{token}', [FormController::class, 'answer'])
    ->name('forms.answer')
    ->middleware('guest'); // Seulement pour les invités

Route::post('/forms/submit-answer/{token}', [FormController::class, 'submitAnswer'])
    ->name('forms.submit-answer')
    ->middleware('guest');

// Ajouter cette nouvelle route pour la sauvegarde de la progression
Route::post('/forms/{token}/save-progress', [FormController::class, 'saveProgress'])
    ->name('forms.save-progress')
    ->middleware('guest');

Route::get('/forms/thankyou', function () {
    return Inertia::render('Forms/ThankYou');
})->name('forms.thankyou');

Route::get('/forms/error', function () {
    return Inertia::render('Forms/Error');
})->name('forms.error');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('forms.index');
    })->name('dashboard');

    Route::resource('forms', FormController::class);
    Route::post('/forms/{form}/duplicate', [FormController::class, 'duplicate'])->name('forms.duplicate');

    // Ajouter cette nouvelle route pour les résultats
    Route::get('/forms/{form}/results', [FormController::class, 'results'])->name('forms.results');

    // Routes pour l'envoi et la réponse aux formulaires
    Route::post('/forms/{form}/send-access', [FormController::class, 'sendAccess'])
        ->name('forms.send-access')
        ->middleware(['auth']);

    // Ajouter cette nouvelle route pour vérifier le statut du formulaire
    Route::post('/forms/{form}/check-status', [FormController::class, 'checkFormStatus'])
        ->name('forms.check-status');

    // Module routes
    Route::prefix('modules')->name('modules.')->group(function () {
        Route::post('/', [ModuleController::class, 'store'])->name('store');
        Route::put('{module}', [ModuleController::class, 'update'])->name('update');
        Route::put('{module}/update', [ModuleController::class, 'update'])->name('update');
        Route::post('{module}/update', [ModuleController::class, 'update'])->name('update');
        Route::put('{module}/students', [ModuleController::class, 'updateStudents'])->name('updateStudents');
        Route::delete('{module}', [ModuleController::class, 'destroy'])->name('destroy');
        Route::delete('{module}/students', [ModuleController::class, 'removeStudent'])->name('removeStudent');
        Route::get('/', [ModuleController::class, 'index'])->name('index');
    });

    // Professor routes
    Route::resource('professors', ProfessorController::class)->only(['store', 'update', 'destroy']);

    // Year routes
    Route::post('/years', [YearController::class, 'store'])->name('years.store');
    Route::delete('/years/{year}', [YearController::class, 'destroy'])->name('years.destroy');
});
