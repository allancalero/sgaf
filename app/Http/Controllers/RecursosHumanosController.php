<?php

namespace App\Http\Controllers;

use App\Models\Personal;
use App\Models\Cargo;
use App\Models\Area;
use App\Models\Ubicacion;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;

class RecursosHumanosController extends Controller
{
    public function index(Request $request): Response
    {
        // Obtener datos de Personal con filtros
        $query = Personal::query()
            ->leftJoin('cargos', 'personal.cargo_id', '=', 'cargos.id')
            ->leftJoin('areas', 'personal.area_id', '=', 'areas.id')
            ->leftJoin('ubicaciones', 'personal.ubicacion_id', '=', 'ubicaciones.id')
            ->select(
                'personal.id',
                'personal.nombre',
                'personal.apellido',
                'personal.telefono',
                'personal.email',
                'personal.numero_cedula',
                'personal.sexo',
                'personal.direccion',
                'personal.foto',
                'personal.estado',
                'personal.cargo_id',
                'personal.area_id',
                'personal.ubicacion_id',
                'cargos.nombre as cargo',
                'areas.nombre as area',
                'ubicaciones.nombre as ubicacion'
            );

        if ($request->filled('area_id')) {
            $query->where('personal.area_id', $request->area_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('personal.nombre', 'like', "%{$search}%")
                  ->orWhere('personal.apellido', 'like', "%{$search}%")
                  ->orWhere('personal.email', 'like', "%{$search}%");
            });
        }

        $personal = $query->orderBy('personal.id')->paginate(10)->withQueryString();

        // Obtener datos de Cargos paginados para la tabla
        $cargosPaginados = Cargo::orderBy('id')->paginate(10);
        // Obtener TODOS los cargos para dropdowns
        $todosLosCargos = Cargo::where('estado', 'ACTIVO')->orderBy('nombre')->get(['id', 'nombre']);

        // Obtener catálogos necesarios
        $areas = Area::orderBy('nombre')->get(['id', 'nombre']);
        $ubicaciones = Ubicacion::orderBy('nombre')->get(['id', 'nombre']);

        return Inertia::render('RecursosHumanos/Index', [
            'personal' => $personal,
            'cargos' => $cargosPaginados,
            'todosLosCargos' => $todosLosCargos,
            'areas' => $areas,
            'ubicaciones' => $ubicaciones,
            'filters' => $request->only(['area_id', 'search']),
        ]);
    }
}
