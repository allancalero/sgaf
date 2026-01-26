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
        $fechaCorte = $request->filled('fecha_corte') ? new \DateTime($request->fecha_corte) : new \DateTime();
        
        $diff = $fechaAdquisicion->diff($fechaCorte);
        $anosTranscurridos = $diff->y + ($diff->m / 12) + ($diff->d / 365.25);
        $anosTranscurridos = max(0, $anosTranscurridos);

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
    public function getCatalogo()
    {
        return response()->json(DB::table('catalogo_depreciacion')->get());
    }

    public function getSinConfigurar(Request $request)
    {
        $query = DB::table('activos_fijos')
            ->leftJoin('areas', 'activos_fijos.area_id', '=', 'areas.id')
            ->select(
                'activos_fijos.id',
                'activos_fijos.codigo_inventario',
                'activos_fijos.nombre_activo',
                'activos_fijos.precio_adquisicion',
                'activos_fijos.fecha_adquisicion',
                'areas.nombre as area'
            )
            ->where(function ($q) {
                $q->whereNull('activos_fijos.vida_util_anos')
                  ->orWhere('activos_fijos.vida_util_anos', '<=', 0);
            });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('activos_fijos.nombre_activo', 'like', "%{$search}%")
                  ->orWhere('activos_fijos.codigo_inventario', 'like', "%{$search}%");
            });
        }

        return response()->json($query->orderBy('activos_fijos.nombre_activo')->paginate(15));
    }

    public function configurar(Request $request, $id)
    {
        $request->validate([
            'vida_util_anos' => 'required|integer|min:1',
            'valor_residual' => 'nullable|numeric',
            'metodo_depreciacion' => 'nullable|string',
            'fecha_adquisicion' => 'nullable|date'
        ]);

        $updateData = [
            'vida_util_anos' => $request->vida_util_anos,
            'valor_residual' => $request->valor_residual ?? 0,
            'metodo_depreciacion' => $request->metodo_depreciacion ?? 'linea_recta',
            'updated_at' => now(),
        ];

        if ($request->filled('fecha_adquisicion')) {
            $updateData['fecha_adquisicion'] = $request->fecha_adquisicion;
        }

        DB::table('activos_fijos')->where('id', $id)->update($updateData);

        // Disparar cálculo inmediato con la fecha de corte provista o la actual
        $this->calcular($request, $id);

        return response()->json(['message' => 'Configuración guardada y depreciación calculada exitosamente.']);
    }

    public function resetear()
    {
        DB::table('activos_fijos')
            ->whereNotNull('vida_util_anos')
            ->where('vida_util_anos', '>', 0)
            ->update([
                'depreciacion_anual' => 0,
                'depreciacion_acumulada' => 0,
                'valor_libros' => DB::raw('precio_adquisicion'),
                'fecha_ultima_depreciacion' => null,
                'updated_at' => now(),
            ]);

        return response()->json(['message' => 'Valores de depreciación reiniciados. Los activos siguen configurados.']);
    }

    public function limpiarTodo()
    {
        DB::table('activos_fijos')->update([
            'vida_util_anos' => null,
            'valor_residual' => 0,
            'metodo_depreciacion' => 'linea_recta',
            'depreciacion_anual' => 0,
            'depreciacion_acumulada' => 0,
            'valor_libros' => DB::raw('precio_adquisicion'),
            'fecha_ultima_depreciacion' => null,
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Todos los activos han sido desconfigurados y movidos a la pestaña "Sin Configurar".']);
    }

    public function procesarMasivo(Request $request)
    {
        $activos = DB::table('activos_fijos')
            ->whereNotNull('vida_util_anos')
            ->where('vida_util_anos', '>', 0)
            ->get();

        $fechaCorte = $request->filled('fecha_corte') ? new \DateTime($request->fecha_corte) : new \DateTime();

        foreach ($activos as $activo) {
            $valorDepreciable = $activo->precio_adquisicion - ($activo->valor_residual ?? 0);
            $depreciacionAnual = $valorDepreciable / $activo->vida_util_anos;

            $fechaAdquisicion = new \DateTime($activo->fecha_adquisicion);
            
            $diff = $fechaAdquisicion->diff($fechaCorte);
            $anosTranscurridos = $diff->y + ($diff->m / 12) + ($diff->d / 365.25);
            $anosTranscurridos = max(0, $anosTranscurridos);

            $depreciacionAcumulada = min($depreciacionAnual * $anosTranscurridos, $valorDepreciable);
            $valorLibros = $activo->precio_adquisicion - $depreciacionAcumulada;

            DB::table('activos_fijos')->where('id', $activo->id)->update([
                'depreciacion_anual' => $depreciacionAnual,
                'depreciacion_acumulada' => $depreciacionAcumulada,
                'valor_libros' => $valorLibros,
                'fecha_ultima_depreciacion' => now(),
                'updated_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Proceso de depreciación masiva completado con éxito hasta ' . $fechaCorte->format('d/m/Y') . '.']);
    }
}
