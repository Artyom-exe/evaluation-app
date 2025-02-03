<?php

namespace App\Http\Controllers;

use App\Models\Professor;
use Illuminate\Http\Request;

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
            // Vérifier si le professeur a des modules associés
            if ($professor->modules()->count() > 0) {
                return back()->withErrors([
                    'error' => 'Ce professeur ne peut pas être supprimé car il est associé à des modules.'
                ]);
            }

            $professor->delete();
            return back()->with('success', 'Professeur supprimé avec succès');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Une erreur est survenue lors de la suppression']);
        }
    }
}
