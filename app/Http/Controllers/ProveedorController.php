<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProveedorRequest;
use App\Http\Requests\UpdateProveedorRequest;
use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProveedorController extends Controller
{
    public function index(): Response
    {
        $proveedores = Proveedor::orderBy('id')->get(['id', 'nombre', 'ruc', 'direccion', 'telefono', 'email', 'created_at']);

        return Inertia::render('Proveedores/Index', [
            'proveedores' => $proveedores,
        ]);
    }

    public function store(StoreProveedorRequest $request): RedirectResponse
    {
        Proveedor::create($request->validated());

        return redirect()->route('proveedores.index')->with('success', 'Proveedor creado');
    }

    public function update(UpdateProveedorRequest $request, Proveedor $proveedore): RedirectResponse
    {
        $proveedore->update($request->validated());

        return redirect()->route('proveedores.index')->with('success', 'Proveedor actualizado');
    }

    public function destroy(Proveedor $proveedore): RedirectResponse
    {
        $proveedore->delete();

        return redirect()->route('proveedores.index')->with('success', 'Proveedor eliminado');
    }
}
