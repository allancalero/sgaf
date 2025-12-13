<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChequeRequest;
use App\Http\Requests\UpdateChequeRequest;
use App\Models\Area;
use App\Models\Cheque;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ChequeController extends Controller
{
    public function index(): Response
    {
        $cheques = Cheque::with(['areaSolicitante:id,nombre', 'usuarioEmisor:id,nombre,apellido'])
            ->orderBy('id', 'desc')
            ->get();

        $areas = Area::where('estado', 'ACTIVO')
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return Inertia::render('Cheques/Index', [
            'cheques' => $cheques,
            'areas' => $areas,
        ]);
    }

    public function store(StoreChequeRequest $request): RedirectResponse
    {
        Cheque::create($request->validated());

        return redirect()->route('cheques.index')->with('success', 'Cheque creado exitosamente');
    }

    public function update(UpdateChequeRequest $request, Cheque $cheque): RedirectResponse
    {
        $cheque->update($request->validated());

        return redirect()->route('cheques.index')->with('success', 'Cheque actualizado exitosamente');
    }

    public function destroy(Cheque $cheque): RedirectResponse
    {
        // Verificar si el cheque tiene activos asociados
        if ($cheque->activosFijos()->count() > 0) {
            return redirect()->route('cheques.index')
                ->with('error', 'No se puede eliminar el cheque porque tiene activos asociados');
        }

        $cheque->delete();

        return redirect()->route('cheques.index')->with('success', 'Cheque eliminado exitosamente');
    }
}
