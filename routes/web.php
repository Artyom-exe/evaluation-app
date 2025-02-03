<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\FormController;
use App\Http\Controllers\QuestionTypeController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\ProfessorController;


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

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

    // Mise à jour des routes pour les modules
    Route::prefix('modules')->name('modules.')->group(function () {
        Route::post('/', [ModuleController::class, 'store'])->name('store');
        Route::put('{module}/students', [ModuleController::class, 'updateStudents'])->name('updateStudents');
        Route::put('{module}/update', [ModuleController::class, 'updateProfessorAndYear'])->name('updateProfessorAndYear');
        Route::delete('{module}', [ModuleController::class, 'destroy'])->name('destroy');
        // Corriger la route de suppression d'étudiant (enlever 'modules' en double)
        Route::delete('{module}/students', [ModuleController::class, 'removeStudent'])->name('removeStudent');
        Route::put('{module}/update', [ModuleController::class, 'update'])->name('update'); // Changer post en put
    });

    // Ajouter cette nouvelle route
    Route::post('/professors', [ProfessorController::class, 'store'])->name('professors.store');
    Route::put('/professors/{professor}', [ProfessorController::class, 'update'])->name('professors.update');
    Route::delete('/professors/{professor}', [ProfessorController::class, 'destroy'])->name('professors.destroy');
});
