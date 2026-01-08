<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DepreciacionController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('activos_fijos')
            ->leftJoin('areas', 'activos_fijos.area_id', '=', 'areas.id')
            ->select(
                'activos_fijos.id',
                'activos_fijos.codigo_inventario',
                'activos_fijos.nombre_activo',
                'activos_fijos.precio_adquisicion',
                'activos_fijos.fecha_adquisicion',
                'activos_fijos.vida_util_anos',
                'activos_fijos.valor_residual',
                'activos_fijos.metodo_depreciacion',
                'activos_fijos.depreciacion_anual',
                'activos_fijos.depreciacion_acumulada',
                'activos_fijos.valor_libros',
                'areas.nombre as area'
            )
            ->whereNotNull('activos_fijos.vida_util_anos')
            ->where('activos_fijos.vida_util_anos', '>', 0);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('activos_fijos.nombre_activo', 'like', "%{$search}%")
                    ->orWhere('activos_fijos.codigo_inventario', 'like', "%{$search}%");
            });
        }

        $activos = $query->orderBy('activos_fijos.nombre_activo')->paginate($request->get('per_page', 15));

        return response()->json($activos);
    }

    public function calcular(Request $request, $id)
    {
        $activo = DB::table('activos_fijos')->where('id', $id)->first();

        if (!$activo || !$activo->vida_util_anos) {
            return response()->json(['error' => 'Activo no válido para depreciación'], 400);
        }

        $valorDepreciable = $activo->precio_adquisicion - ($activo->valor_residual ?? 0);
        $depreciacionAnual = $valorDepreciable / $activo->vida_util_anos;

        $fechaAdquisicion = new \DateTime($activo->fecha_adquisicion);
        $hoy = new \DateTime();
        $anosTranscurridos = $fechaAdquisicion->diff($hoy)->y;

        $depreciacionAcumulada = min($depreciacionAnual * $anosTranscurridos, $valorDepreciable);
        $valorLibros = $activo->precio_adquisicion - $depreciacionAcumulada;

        DB::table('activos_fijos')->where('id', $id)->update([
            'depreciacion_anual' => $depreciacionAnual,
            'depreciacion_acumulada' => $depreciacionAcumulada,
            'valor_libros' => $valorLibros,
            'fecha_ultima_depreciacion' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'depreciacion_anual' => $depreciacionAnual,
            'depreciacion_acumulada' => $depreciacionAcumulada,
            'valor_libros' => $valorLibros,
            'message' => 'Depreciación calculada exitosamente'
        ]);
    }
}
