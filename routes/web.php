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

    // Module routes
    Route::prefix('modules')->name('modules.')->group(function () {
        Route::post('/', [ModuleController::class, 'store'])->name('store');
        Route::put('{module}/update', [ModuleController::class, 'update'])->name('update');
        Route::put('{module}/students', [ModuleController::class, 'updateStudents'])->name('updateStudents');
        Route::delete('{module}', [ModuleController::class, 'destroy'])->name('destroy');
        Route::delete('{module}/students', [ModuleController::class, 'removeStudent'])->name('removeStudent');
    });

    // Professor routes
    Route::resource('professors', ProfessorController::class)->only(['store', 'update', 'destroy']);
});
