<?php

namespace App\Http\Controllers;

use App\Models\ClassificationField;
use App\Models\Clasificacion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClassificationFieldController extends Controller
{
    /**
     * Get fields for a specific classification (API endpoint)
     */
    public function getFields($clasificacionId)
    {
        $fields = ClassificationField::where('clasificacion_id', $clasificacionId)
            ->orderBy('order')
            ->get();

        return response()->json($fields);
    }

    /**
     * Show configuration page for classification fields
     */
    public function index(Clasificacion $clasificacion)
    {
        $fields = $clasificacion->customFields;

        return Inertia::render('Clasificaciones/ConfigFields', [
            'clasificacion' => $clasificacion,
            'fields' => $fields,
        ]);
    }

    /**
     * Store a new classification field
     */
    public function store(Request $request, Clasificacion $clasificacion)
    {
        $validated = $request->validate([
            'field_name' => ['required', 'string', 'max:255'],
            'field_label' => ['required', 'string', 'max:255'],
            'field_type' => ['required', 'in:text,number,select,date'],
            'field_options' => ['nullable', 'array'],
            'required' => ['boolean'],
            'order' => ['integer'],
        ]);

        $validated['clasificacion_id'] = $clasificacion->id;

        ClassificationField::create($validated);

        return redirect()->back()->with('success', 'Campo agregado correctamente');
    }

    /**
     * Update a classification field
     */
    public function update(Request $request, ClassificationField $field)
    {
        $validated = $request->validate([
            'field_name' => ['required', 'string', 'max:255'],
            'field_label' => ['required', 'string', 'max:255'],
            'field_type' => ['required', 'in:text,number,select,date'],
            'field_options' => ['nullable', 'array'],
            'required' => ['boolean'],
            'order' => ['integer'],
        ]);

        $field->update($validated);

        return redirect()->back()->with('success', 'Campo actualizado correctamente');
    }

    /**
     * Delete a classification field
     */
    public function destroy(ClassificationField $field)
    {
        $field->delete();

        return redirect()->back()->with('success', 'Campo eliminado correctamente');
    }
}
