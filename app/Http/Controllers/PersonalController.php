<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonalRequest;
use App\Http\Requests\UpdatePersonalRequest;
use App\Models\Personal;
use App\Models\Cargo;
use App\Models\Area;
use App\Models\Ubicacion;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PersonalController extends Controller
{
    public function index(): Response
    {
        $personal = Personal::query()
            ->leftJoin('cargos', 'personal.cargo_id', '=', 'cargos.id')
            ->leftJoin('areas', 'personal.area_id', '=', 'areas.id')
            ->leftJoin('ubicaciones', 'personal.ubicacion_id', '=', 'ubicaciones.id')
            ->select(
                'personal.id',
                'personal.nombre',
                'personal.apellido',
                'personal.telefono',
                'personal.email',
                'personal.estado',
                'personal.cargo_id',
                'personal.area_id',
                'personal.ubicacion_id',
                'cargos.nombre as cargo',
                'areas.nombre as area',
                'ubicaciones.nombre as ubicacion'
            )
            ->orderBy('personal.id')
            ->get();

        $cargos = Cargo::orderBy('nombre')->get(['id', 'nombre']);
        $areas = Area::orderBy('nombre')->get(['id', 'nombre']);
        $ubicaciones = Ubicacion::orderBy('nombre')->get(['id', 'nombre']);

        return Inertia::render('Personal/Index', [
            'personal' => $personal,
            'cargos' => $cargos,
            'areas' => $areas,
            'ubicaciones' => $ubicaciones,
        ]);
    }

    public function store(StorePersonalRequest $request): RedirectResponse
    {
        Personal::create($request->validated());

        return redirect()->route('personal.index')->with('success', 'Personal creado');
    }

    public function update(UpdatePersonalRequest $request, Personal $personal): RedirectResponse
    {
        $personal->update($request->validated());

        return redirect()->route('personal.index')->with('success', 'Personal actualizado');
    }

    public function destroy(Personal $personal): RedirectResponse
    {
        $personal->delete();

        return redirect()->route('personal.index')->with('success', 'Personal eliminado');
    }
}
