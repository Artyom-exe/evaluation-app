<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Professor;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ModuleController extends Controller
{
    public function updateStudents(Request $request, Module $module)
    {
        try {
            $validated = $request->validate([
                'emails' => 'required|string'
            ]);

            \DB::beginTransaction();

            $emails = array_map('trim', explode(',', $validated['emails']));
            $emails = array_filter($emails, 'filter_var', FILTER_VALIDATE_EMAIL);

            $module->students()->detach();

            foreach ($emails as $email) {
                $student = Student::firstOrCreate(['email' => $email]);
                $module->students()->attach($student->id);
            }

            \DB::commit();
            return redirect()->back()->with('success', 'Étudiants mis à jour avec succès');
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function updateProfessor(Request $request, Module $module)
    {
        try {
            $validated = $request->validate([
                'professor_id' => 'required|exists:professors,id'
            ]);

            $module->update(['professor_id' => $validated['professor_id']]);
            return response()->json(['message' => 'Professeur mis à jour avec succès']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateProfessorAndYear(Request $request, Module $module)
    {
        try {
            $validated = $request->validate([
                'professor_id' => 'required|exists:professors,id',
                'year_id' => 'required|exists:years,id'
            ]);

            $module->update($validated);

            return redirect()->back()->with('success', 'Module mis à jour avec succès');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'year_id' => 'required|exists:years,id',
            'professor_id' => 'required|exists:professors,id',
            'image' => 'nullable|image|max:2048', // Max 2MB
            'students' => 'required|json'
        ]);

        try {
            \DB::beginTransaction();

            if ($request->hasFile('image')) {
                // S'assurer que le répertoire existe
                Storage::disk('public')->makeDirectory('modules');

                $path = $request->file('image')->store('modules', 'public');
                $validated['image_path'] = Storage::url($path);
            } else {
                // Utiliser l'image par défaut
                $validated['image_path'] = '/storage/modules/default/default-module.jpg';
            }

            $module = Module::create($validated);

            // Traitement des étudiants
            $students = json_decode($request->students, true);
            foreach ($students as $studentData) {
                if (isset($studentData['email']) && filter_var($studentData['email'], FILTER_VALIDATE_EMAIL)) {
                    $student = Student::firstOrCreate(
                        ['email' => $studentData['email']],
                        ['name' => $studentData['name'] ?? null]
                    );
                    $module->students()->attach($student->id);
                }
            }

            \DB::commit();
            return redirect()->back()->with('success', 'Module créé avec succès');
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->withErrors(['error' => "Une erreur est survenue lors de la création du module: " . $e->getMessage()]);
        }
    }

    public function destroy(Module $module)
    {
        try {
            if ($module->forms()->exists()) {
                return back()->withErrors([
                    'error' => "Impossible de supprimer le module \"{$module->name}\" car il est déjà associé à un ou plusieurs formulaires"
                ]);
            }

            $module->students()->detach();
            $module->delete();

            return redirect()->back()->with('success', 'Module supprimé avec succès');
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => "Une erreur est survenue lors de la suppression du module"
            ]);
        }
    }
}
