<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class TrazabilidadController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('historial_asignaciones')
            ->leftJoin('activos_fijos', 'historial_asignaciones.activo_id', '=', 'activos_fijos.id')
            ->leftJoin('personal as anterior', 'historial_asignaciones.asignacion_anterior_id', '=', 'anterior.id')
            ->leftJoin('personal as nuevo', 'historial_asignaciones.asignacion_nuevo_id', '=', 'nuevo.id')
            ->leftJoin('areas as area_ant', 'anterior.area_id', '=', 'area_ant.id')
            ->leftJoin('areas as area_new', 'nuevo.area_id', '=', 'area_new.id')
            ->select(
                'historial_asignaciones.*',
                'activos_fijos.nombre_activo',
                'activos_fijos.codigo_inventario',
                DB::raw("CONCAT(anterior.nombre, ' ', anterior.apellido) as personal_anterior"),
                DB::raw("CONCAT(nuevo.nombre, ' ', nuevo.apellido) as personal_nuevo"),
                'area_ant.nombre as area_anterior',
                'area_new.nombre as area_nueva'
            );

        // Filter by asset
        if ($request->filled('activo_id')) {
            $query->where('historial_asignaciones.activo_id', $request->activo_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('activos_fijos.nombre_activo', 'like', "%{$search}%")
                    ->orWhere('activos_fijos.codigo_inventario', 'like', "%{$search}%");
            });
        }

        $historial = $query->orderBy('historial_asignaciones.fecha_asignacion', 'desc')
            ->paginate($request->get('per_page', 20));

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
