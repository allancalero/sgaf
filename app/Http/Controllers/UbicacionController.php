<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUbicacionRequest;
use App\Http\Requests\UpdateUbicacionRequest;
use App\Models\Ubicacion;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class UbicacionController extends Controller
{
    public function index(): Response
    {
        $ubicaciones = Ubicacion::orderBy('id')->get(['id', 'nombre', 'estado', 'created_at']);

        return Inertia::render('Ubicaciones/Index', [
            'ubicaciones' => $ubicaciones,
        ]);
    }

    public function store(StoreUbicacionRequest $request): RedirectResponse
    {
        Ubicacion::create($request->validated());

        return redirect()->route('ubicaciones.index')->with('success', 'Ubicación creada');
    }

    public function update(UpdateUbicacionRequest $request, Ubicacion $ubicacion): RedirectResponse
    {
        $ubicacion->update($request->validated());

        return redirect()->route('ubicaciones.index')->with('success', 'Ubicación actualizada');
    }

    public function destroy(Ubicacion $ubicacion): RedirectResponse
    {
        $ubicacion->delete();

        return redirect()->route('ubicaciones.index')->with('success', 'Ubicación eliminada');
    }
}
