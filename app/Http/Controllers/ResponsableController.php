<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreResponsableRequest;
use App\Http\Requests\UpdateResponsableRequest;
use App\Models\Responsable;
use App\Models\Cargo;
use App\Models\Area;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ResponsableController extends Controller
{
    public function index(): Response
    {
        $responsables = Responsable::query()
            ->leftJoin('cargos', 'responsables.id_cargo', '=', 'cargos.id')
            ->leftJoin('areas', 'responsables.area_id', '=', 'areas.id')
            ->select(
                'responsables.id',
                'responsables.nombre',
                'responsables.estado',
                'responsables.id_cargo',
                'responsables.area_id',
                'cargos.nombre as cargo',
                'areas.nombre as area'
            )
            ->orderBy('responsables.id')
            ->get();

        $cargos = Cargo::orderBy('nombre')->get(['id', 'nombre']);
        $areas = Area::orderBy('nombre')->get(['id', 'nombre']);

        return Inertia::render('Responsables/Index', [
            'responsables' => $responsables,
            'cargos' => $cargos,
            'areas' => $areas,
        ]);
    }

    public function store(StoreResponsableRequest $request): RedirectResponse
    {
        Responsable::create($request->validated());

        return redirect()->route('responsables.index')->with('success', 'Responsable creado');
    }

    public function update(UpdateResponsableRequest $request, Responsable $responsable): RedirectResponse
    {
        $responsable->update($request->validated());

        return redirect()->route('responsables.index')->with('success', 'Responsable actualizado');
    }

    public function destroy(Responsable $responsable): RedirectResponse
    {
        $responsable->delete();

        return redirect()->route('responsables.index')->with('success', 'Responsable eliminado');
    }
}
