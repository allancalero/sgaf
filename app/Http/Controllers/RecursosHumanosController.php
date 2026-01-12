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
        $personalQuery = Personal::query()
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
            $personalQuery->where('personal.area_id', $request->area_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $personalQuery->where(function ($q) use ($search) {
                $q->where('personal.nombre', 'like', "%{$search}%")
                  ->orWhere('personal.apellido', 'like', "%{$search}%")
                  ->orWhere('personal.email', 'like', "%{$search}%");
            });
        }

        $personal = $personalQuery->orderBy('personal.id')->paginate(10, ['*'], 'page')->withQueryString();

        // Obtener datos de Cargos paginados
        $cargosPaginados = Cargo::orderBy('id')->paginate(10, ['*'], 'p_cargos')->withQueryString();
        
        // Obtener datos de Áreas paginados (con su ubicación)
        $areasPaginadas = Area::with('ubicacion')->orderBy('id')->paginate(10, ['*'], 'p_areas')->withQueryString();
        
        // Obtener datos de Ubicaciones paginados
        $ubicacionesPaginadas = Ubicacion::orderBy('id')->paginate(10, ['*'], 'p_ubicaciones')->withQueryString();

        // Obtener TODOS los catálogos para dropdowns en formularios
        $todosLosCargos = Cargo::where('estado', 'ACTIVO')->orderBy('nombre')->get(['id', 'nombre']);
        $todasLasAreas = Area::where('estado', 'ACTIVO')->orderBy('nombre')->get(['id', 'nombre']);
        $todasLasUbicaciones = Ubicacion::where('estado', 'ACTIVO')->orderBy('nombre')->get(['id', 'nombre']);

        return Inertia::render('RecursosHumanos/Index', [
            'personal' => $personal,
            'cargos' => $cargosPaginados,
            'areasPaginadas' => $areasPaginadas,
            'ubicacionesPaginadas' => $ubicacionesPaginadas,
            'todosLosCargos' => $todosLosCargos,
            'areas' => $todasLasAreas, // Para compatibilidad con el dropdown de Personal existente
            'ubicaciones' => $todasLasUbicaciones, // Para compatibilidad con el dropdown de Personal existente
            'filters' => $request->only(['area_id', 'search']),
        ]);
    }
}
