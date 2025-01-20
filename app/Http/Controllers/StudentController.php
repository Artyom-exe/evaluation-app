<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentController extends Controller
{
      /**
     * Affiche la vue d'assignation des étudiants aux modules.
     */
    public function showAssignStudentPage()
    {
        return Inertia::render('Admin/AssignStudentToModule');
    }
}
