<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class TrazabilidadController extends Controller
{
    public function index(Request $request)
    {
        // 1. Query for legacy table (historial_asignaciones)
        $legacyQuery = DB::table('historial_asignaciones')
            ->leftJoin('activos_fijos', 'historial_asignaciones.activo_id', '=', 'activos_fijos.id')
            ->leftJoin('personal as anterior', 'historial_asignaciones.asignacion_anterior_id', '=', 'anterior.id')
            ->leftJoin('personal as nuevo', 'historial_asignaciones.asignacion_nuevo_id', '=', 'nuevo.id')
            ->leftJoin('areas as area_ant', 'anterior.area_id', '=', 'area_ant.id')
            ->leftJoin('areas as area_new', 'nuevo.area_id', '=', 'area_new.id')
            ->select(
                'historial_asignaciones.id',
                'historial_asignaciones.activo_id',
                'historial_asignaciones.fecha_asignacion',
                'historial_asignaciones.motivo',
                DB::raw("NULL as foto_reasignacion"),
                DB::raw("CONCAT(anterior.nombre, ' ', anterior.apellido) as personal_anterior"),
                DB::raw("CONCAT(nuevo.nombre, ' ', nuevo.apellido) as personal_nuevo"),
                'area_ant.nombre as area_anterior',
                'area_new.nombre as area_nueva',
                'activos_fijos.nombre_activo',
                'activos_fijos.codigo_inventario'
            );

        // 2. Query for new table (reasignaciones)
        $newQuery = DB::table('reasignaciones')
            ->leftJoin('activos_fijos', 'reasignaciones.activo_id', '=', 'activos_fijos.id')
            ->leftJoin('personal as anterior', 'reasignaciones.responsable_anterior_id', '=', 'anterior.id')
            ->leftJoin('personal as nuevo', 'reasignaciones.responsable_nuevo_id', '=', 'nuevo.id')
            ->leftJoin('areas as area_ant', 'reasignaciones.area_anterior_id', '=', 'area_ant.id')
            ->leftJoin('areas as area_new', 'reasignaciones.area_nueva_id', '=', 'area_new.id')
            ->select(
                'reasignaciones.id',
                'reasignaciones.activo_id',
                'reasignaciones.fecha_reasignacion as fecha_asignacion',
                'reasignaciones.motivo',
                'reasignaciones.foto_reasignacion',
                DB::raw("CONCAT(anterior.nombre, ' ', anterior.apellido) as personal_anterior"),
                DB::raw("CONCAT(nuevo.nombre, ' ', nuevo.apellido) as personal_nuevo"),
                'area_ant.nombre as area_anterior',
                'area_new.nombre as area_nueva',
                'activos_fijos.nombre_activo',
                'activos_fijos.codigo_inventario'
            );

        // Apply filters to both parts of the union if necessary
        if ($request->filled('activo_id')) {
            $activoId = $request->activo_id;
            $legacyQuery->where('historial_asignaciones.activo_id', $activoId);
            $newQuery->where('reasignaciones.activo_id', $activoId);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $legacyQuery->where(function ($q) use ($search) {
                $q->where('activos_fijos.nombre_activo', 'like', "%{$search}%")
                  ->orWhere('activos_fijos.codigo_inventario', 'like', "%{$search}%");
            });
            $newQuery->where(function ($q) use ($search) {
                $q->where('activos_fijos.nombre_activo', 'like', "%{$search}%")
                  ->orWhere('activos_fijos.codigo_inventario', 'like', "%{$search}%");
            });
        }

        // Combine using UNION and sort
        $combined = $legacyQuery->union($newQuery);
        
        // Wrap in a subquery to allow overall ordering and pagination
        $finalQuery = DB::table(DB::raw("({$combined->toSql()}) as combined_history"))
            ->mergeBindings($combined)
            ->orderBy('fecha_asignacion', 'desc');

        $historial = $finalQuery->paginate($request->get('per_page', 20));

        return response()->json($historial);
    }

    public function buscarActivo(Request $request)
    {
        $search = $request->get('search', '');

        $activos = DB::table('activos_fijos')
            ->where('nombre_activo', 'like', "%{$search}%")
            ->orWhere('codigo_inventario', 'like', "%{$search}%")
            ->select('id', 'nombre_activo', 'codigo_inventario')
            ->limit(10)
            ->get();

        return response()->json($activos);
    }
}
