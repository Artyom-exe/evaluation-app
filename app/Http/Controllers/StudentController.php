<?php
namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Student;
use App\Models\Module;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Affiche la vue d'assignation des étudiants aux modules.
     */
    public function showAssignStudentPage()
    {
        // Récupère tous les étudiants et modules de la base de données
        $students = Student::all();
        $modules = Module::all();

        // Passe les étudiants et modules à la vue via Inertia
        return Inertia::render('AssignStudentToModule', [
            'students' => $students,
            'modules' => $modules,
        ]);
    }

    /**
     * Assigne un étudiant à un module.
     */
    public function assignStudentToModule(Request $request)
    {
        // Valide les données de la requête
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'module_id' => 'required|exists:modules,id',
        ]);

        // Récupère l'étudiant et le module
        $student = Student::find($request->student_id);
        $module = Module::find($request->module_id);

        // Associer l'étudiant au module via la table pivot `inscriptions`
        $student->modules()->attach($module);

        // Retourner une réponse à Inertia
        return redirect()->route('students.assign')->with('success', 'Étudiant assigné au module avec succès !');
    }
}

