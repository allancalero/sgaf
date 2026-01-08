<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ReasignacionController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('historial_asignaciones')
            ->leftJoin('activos_fijos', 'historial_asignaciones.activo_id', '=', 'activos_fijos.id')
            ->leftJoin('personal as anterior', 'historial_asignaciones.asignacion_anterior_id', '=', 'anterior.id')
            ->leftJoin('personal as nuevo', 'historial_asignaciones.asignacion_nuevo_id', '=', 'nuevo.id')
            ->select(
                'historial_asignaciones.*',
                'activos_fijos.nombre_activo',
                'activos_fijos.codigo_inventario',
                DB::raw("CONCAT(anterior.nombre, ' ', anterior.apellido) as personal_anterior"),
                DB::raw("CONCAT(nuevo.nombre, ' ', nuevo.apellido) as personal_nuevo")
            );

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('activos_fijos.nombre_activo', 'like', "%{$search}%")
                    ->orWhere('activos_fijos.codigo_inventario', 'like', "%{$search}%");
            });
        }

        $reasignaciones = $query->orderBy('historial_asignaciones.fecha_asignacion', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json($reasignaciones);
    }

    public function store(Request $request)
    {
        $activo = DB::table('activos_fijos')->where('id', $request->activo_id)->first();

        $id = DB::table('historial_asignaciones')->insertGetId([
            'activo_id' => $request->activo_id,
            'asignacion_anterior_id' => $activo->personal_id,
            'asignacion_nuevo_id' => $request->personal_nuevo_id,
            'fecha_asignacion' => now(),
            'motivo' => $request->motivo,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update the asset with new owner
        DB::table('activos_fijos')->where('id', $request->activo_id)->update([
            'personal_id' => $request->personal_nuevo_id,
            'updated_at' => now(),
        ]);

        return response()->json(['id' => $id, 'message' => 'Reasignación realizada exitosamente']);
    }
}
