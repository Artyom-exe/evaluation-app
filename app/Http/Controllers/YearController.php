<?php

namespace App\Http\Controllers;

use App\Models\Year;
use Illuminate\Http\Request;

class YearController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:years,name'
            ]);

            Year::create($validated);
            return redirect()->back()->with('success', 'Année ajoutée avec succès');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Year $year)
    {
        try {
            if ($year->modules()->exists()) {
                return back()->with('error', "Cette année ne peut pas être supprimée car elle est associée à des modules");
            }

            $year->delete();
            return back()->with('success', 'Année supprimée avec succès');
        } catch (\Exception $e) {
            return back()->with('error', 'Une erreur est survenue lors de la suppression');
        }
    }
}
