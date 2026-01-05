<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCargoRequest;
use App\Http\Requests\UpdateCargoRequest;
use App\Models\Cargo;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CargoController extends Controller
{
    public function index(): Response
    {
        $cargos = Cargo::orderBy('id')->paginate(10, ['id', 'nombre', 'estado', 'created_at']);

        return Inertia::render('Cargos/Index', [
            'cargos' => $cargos,
        ]);
    }

    public function store(StoreCargoRequest $request): RedirectResponse
    {
        Cargo::create($request->validated());

        return redirect()->route('cargos.index')->with('success', 'Cargo creado');
    }

    public function update(UpdateCargoRequest $request, Cargo $cargo): RedirectResponse
    {
        $cargo->update($request->validated());

        return redirect()->route('cargos.index')->with('success', 'Cargo actualizado');
    }

    public function destroy(Cargo $cargo): RedirectResponse
    {
        $cargo->delete();

        return redirect()->route('cargos.index')->with('success', 'Cargo eliminado');
    }
}
