<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Form;
use App\Models\Module;
use App\Models\Year;
use Inertia\Inertia;

class FormController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Forms/Index', [
            'forms' => Form::with(['module.professor', 'module.year'])->get(),
            'modules' => Module::with(['professor', 'year'])->get(),
            'years' => Year::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Forms/Create', [
            'modules' => Module::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'module_id' => 'required|exists:modules,id',
            'statut' => 'required|in:open,closed',
        ]);

        Form::create($request->only(['title', 'module_id', 'statut']));

        return redirect()->route('forms.index');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Form $form)
    {
        return Inertia::render('Forms/Edit', [
            'form' => $form,
            'modules' => Module::all()
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Form $form)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'module_id' => 'required|exists:modules,id',
            'statut' => 'required|in:open,closed',
        ]);

        $form->update($request->only(['title', 'module_id', 'statut']));

        return redirect()->route('forms.index');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Form $form)
    {
        $form->delete();
        return redirect()->route('forms.index');
    }
}
