<?php

namespace App\Http\Controllers;

use App\Models\Personal;
use App\Models\Cargo;
use App\Models\Area;
use App\Models\Ubicacion;
use Inertia\Inertia;
use Inertia\Response;

class RecursosHumanosController extends Controller
{
    public function index(): Response
    {
        // Obtener datos de Personal
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

        // Obtener datos de Cargos
        $cargos = Cargo::orderBy('id')->get(['id', 'nombre', 'estado', 'created_at']);

        // Obtener catálogos necesarios
        $areas = Area::orderBy('nombre')->get(['id', 'nombre']);
        $ubicaciones = Ubicacion::orderBy('nombre')->get(['id', 'nombre']);

        return Inertia::render('RecursosHumanos/Index', [
            'personal' => $personal,
            'cargos' => $cargos,
            'areas' => $areas,
            'ubicaciones' => $ubicaciones,
        ]);
    }
}
