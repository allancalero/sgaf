<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTipoActivoRequest;
use App\Http\Requests\UpdateTipoActivoRequest;
use App\Models\Clasificacion;
use App\Models\TipoActivo;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TipoActivoController extends Controller
{
    public function index(): Response
    {
        $tipos = TipoActivo::query()
            ->select('tipos_activos.id', 'tipos_activos.nombre', 'clasificaciones.nombre as clasificacion', 'tipos_activos.clasificacion_id')
            ->leftJoin('clasificaciones', 'tipos_activos.clasificacion_id', '=', 'clasificaciones.id')
            ->orderBy('tipos_activos.id')
            ->get();

        $clasificaciones = Clasificacion::orderBy('nombre')->get(['id', 'nombre']);

        return Inertia::render('TiposActivos/Index', [
            'tipos' => $tipos,
            'clasificaciones' => $clasificaciones,
        ]);
    }

    public function store(StoreTipoActivoRequest $request): RedirectResponse
    {
        TipoActivo::create($request->validated());

        return redirect()->route('tipos.index')->with('success', 'Tipo de activo creado');
    }

    public function update(UpdateTipoActivoRequest $request, TipoActivo $tipo): RedirectResponse
    {
        $tipo->update($request->validated());

        return redirect()->route('tipos.index')->with('success', 'Tipo de activo actualizado');
    }

    public function destroy(TipoActivo $tipo): RedirectResponse
    {
        $tipo->delete();

        return redirect()->route('tipos.index')->with('success', 'Tipo de activo eliminado');
    }
}
