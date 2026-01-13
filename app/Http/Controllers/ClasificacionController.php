<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClasificacionRequest;
use App\Http\Requests\UpdateClasificacionRequest;
use App\Models\Clasificacion;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ClasificacionController extends Controller
{
    public function index(): Response
    {
        $clasificaciones = Clasificacion::orderBy('prefijo')->paginate(10, ['id', 'prefijo', 'codigo', 'nombre', 'created_at']);

        return Inertia::render('Clasificaciones/Index', [
            'clasificaciones' => $clasificaciones,
        ]);
    }

    public function store(StoreClasificacionRequest $request): RedirectResponse
    {
        Clasificacion::create($request->validated());

        return redirect()->route('clasificaciones.index')->with('success', 'Clasificación creada');
    }

    public function update(UpdateClasificacionRequest $request, Clasificacion $clasificacion): RedirectResponse
    {
        $clasificacion->update($request->validated());

        return redirect()->route('clasificaciones.index')->with('success', 'Clasificación actualizada');
    }

    public function destroy(Clasificacion $clasificacion): RedirectResponse
    {
        $clasificacion->delete();

        return redirect()->route('clasificaciones.index')->with('success', 'Clasificación eliminada');
    }
}
