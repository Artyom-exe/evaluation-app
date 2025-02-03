<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfessorController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:professors,email'
            ]);

            Professor::create($validated);

            return redirect()->back()->with('success', 'Professeur ajouté avec succès');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function destroy(Professor $professor)
    {
        try {
            // Vérifier si d'autres professeurs ont le même nom
            $homonyms = Professor::where('name', $professor->name)
                ->where('id', '!=', $professor->id)
                ->exists();

            // Vérifier les modules associés
            $hasModules = $professor->modules()->exists();

            if ($hasModules) {
                return back()->withErrors([
                    'error' => "Le professeur {$professor->name} ({$professor->email}) ne peut pas être supprimé car il est associé à des modules."
                ]);
            }

            // Si c'est un homonyme, inclure l'email dans le message de succès
            $successMessage = $homonyms
                ? "Le professeur {$professor->name} ({$professor->email}) a été supprimé avec succès"
                : "Le professeur {$professor->name} a été supprimé avec succès";

            $professor->delete();
            return back()->with('success', $successMessage);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Une erreur est survenue lors de la suppression']);
        }
    }

    public function update(Request $request, Professor $professor)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required', 'email', Rule::unique('professors')->ignore($professor->id)]
            ]);

            $professor->update($validated);
            return redirect()->back()->with('success', 'Professeur modifié avec succès');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
