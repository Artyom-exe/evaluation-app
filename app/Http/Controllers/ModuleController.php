<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Professor;
use App\Models\Student;
use Illuminate\Http\Request;

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
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'professor_id' => 'required|exists:professors,id',
                'year_id' => 'required|exists:years,id'
            ]);

            $module = Module::create($validated);

            return redirect()->back()->with('success', 'Module créé avec succès');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
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
