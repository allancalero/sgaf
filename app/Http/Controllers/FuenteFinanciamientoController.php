<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFuenteFinanciamientoRequest;
use App\Http\Requests\UpdateFuenteFinanciamientoRequest;
use App\Models\FuenteFinanciamiento;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class FuenteFinanciamientoController extends Controller
{
    public function index(): Response
    {
        $fuentes = FuenteFinanciamiento::orderBy('id')->get(['id', 'nombre', 'estado', 'created_at']);

        return Inertia::render('FuentesFinanciamiento/Index', [
            'fuentes' => $fuentes,
        ]);
    }

    public function store(StoreFuenteFinanciamientoRequest $request): RedirectResponse
    {
        FuenteFinanciamiento::create($request->validated());

        return redirect()->route('fuentes.index')->with('success', 'Fuente creada');
    }

    public function update(UpdateFuenteFinanciamientoRequest $request, FuenteFinanciamiento $fuente): RedirectResponse
    {
        $fuente->update($request->validated());

        return redirect()->route('fuentes.index')->with('success', 'Fuente actualizada');
    }

    public function destroy(FuenteFinanciamiento $fuente): RedirectResponse
    {
        $fuente->delete();

        return redirect()->route('fuentes.index')->with('success', 'Fuente eliminada');
    }
}
