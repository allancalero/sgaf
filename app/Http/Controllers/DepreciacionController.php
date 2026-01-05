<?php

namespace App\Http\Controllers;

use App\Models\ActivoFijo;
use App\Services\DepreciacionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class DepreciacionController extends Controller
{
    protected $depreciacionService;

    public function __construct(DepreciacionService $depreciacionService)
    {
        $this->depreciacionService = $depreciacionService;
    }

    /**
     * Mostrar módulo principal de depreciación
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['area_id', 'clasificacion_id', 'search', 'sin_configurar']);

        // Activos CON configuración de depreciación
        $queryConfigurados = ActivoFijo::query()
            ->leftJoin('areas', 'activos_fijos.area_id', '=', 'areas.id')
            ->leftJoin('clasificaciones', 'activos_fijos.clasificacion_id', '=', 'clasificaciones.id')
            ->select(
                'activos_fijos.id',
                'activos_fijos.codigo_inventario',
                'activos_fijos.nombre_activo',
                'activos_fijos.fecha_adquisicion',
                'activos_fijos.precio_adquisicion',
                'activos_fijos.vida_util_anos',
                'activos_fijos.valor_residual',
                'activos_fijos.depreciacion_anual',
                'activos_fijos.depreciacion_acumulada',
                'activos_fijos.valor_libros',
                'activos_fijos.metodo_depreciacion',
                'activos_fijos.fecha_ultima_depreciacion',
                'areas.nombre as area',
                'clasificaciones.nombre as clasificacion'
            )
            ->whereNotNull('activos_fijos.vida_util_anos');

        // Activos SIN configuración de depreciación
        $querySinConfigurar = ActivoFijo::query()
            ->leftJoin('areas', 'activos_fijos.area_id', '=', 'areas.id')
            ->leftJoin('clasificaciones', 'activos_fijos.clasificacion_id', '=', 'clasificaciones.id')
            ->select(
                'activos_fijos.id',
                'activos_fijos.codigo_inventario',
                'activos_fijos.nombre_activo',
                'activos_fijos.fecha_adquisicion',
                'activos_fijos.precio_adquisicion',
                'areas.nombre as area',
                'clasificaciones.nombre as clasificacion'
            )
            ->whereNull('activos_fijos.vida_util_anos');

        // Aplicar filtros a ambas queries
        foreach ([$queryConfigurados, $querySinConfigurar] as $query) {
            if ($filters['area_id'] ?? false) {
                $query->where('activos_fijos.area_id', $filters['area_id']);
            }
            if ($filters['clasificacion_id'] ?? false) {
                $query->where('activos_fijos.clasificacion_id', $filters['clasificacion_id']);
            }
            if ($filters['search'] ?? false) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('activos_fijos.codigo_inventario', 'like', "%{$search}%")
                      ->orWhere('activos_fijos.nombre_activo', 'like', "%{$search}%");
                });
            }
        }

        $activos = $queryConfigurados->orderBy('activos_fijos.codigo_inventario')->get();
        $activosSinConfigurar = $querySinConfigurar->orderBy('activos_fijos.codigo_inventario')->get();

        // Calcular totales
        $totales = [
            'total_activos' => $activos->count(),
            'sin_configurar' => $activosSinConfigurar->count(),
            'valor_original' => $activos->sum('precio_adquisicion'),
            'depreciacion_acumulada' => $activos->sum('depreciacion_acumulada'),
            'valor_libros' => $activos->sum('valor_libros'),
        ];

        // Datos para gráficas
        $porClasificacion = DB::table('activos_fijos')
            ->leftJoin('clasificaciones', 'activos_fijos.clasificacion_id', '=', 'clasificaciones.id')
            ->select('clasificaciones.nombre', DB::raw('SUM(activos_fijos.depreciacion_acumulada) as total'))
            ->whereNotNull('activos_fijos.vida_util_anos')
            ->groupBy('clasificaciones.id', 'clasificaciones.nombre')
            ->get();

        // Cargar datos para filtros
        $areas = \App\Models\Area::orderBy('nombre')->get(['id', 'nombre']);
        $clasificaciones = \App\Models\Clasificacion::orderBy('nombre')->get(['id', 'nombre']);

        return Inertia::render('Activos/Depreciacion', [
            'activos' => $activos,
            'activosSinConfigurar' => $activosSinConfigurar,
            'totales' => $totales,
            'porClasificacion' => $porClasificacion,
            'areas' => $areas,
            'clasificaciones' => $clasificaciones,
            'filters' => $filters,
        ]);
    }

    /**
     * Calcular depreciación manualmente
     */
    public function calcular()
    {
        $resultado = $this->depreciacionService->calcularDepreciacionTodos();
        
        return redirect()->route('activos.depreciacion')
            ->with('success', "Depreciación calculada: {$resultado['procesados']} activos procesados");
    }

    /**
     * Exportar reporte a PDF
     */
    public function exportPdf(Request $request)
    {
        $filters = $request->only(['area_id', 'clasificacion_id']);

        $query = ActivoFijo::query()
            ->leftJoin('areas', 'activos_fijos.area_id', '=', 'areas.id')
            ->leftJoin('clasificaciones', 'activos_fijos.clasificacion_id', '=', 'clasificaciones.id')
            ->select(
                'activos_fijos.*',
                'areas.nombre as area',
                'clasificaciones.nombre as clasificacion'
            )
            ->whereNotNull('activos_fijos.vida_util_anos');

        if ($filters['area_id'] ?? false) {
            $query->where('activos_fijos.area_id', $filters['area_id']);
        }
        if ($filters['clasificacion_id'] ?? false) {
            $query->where('activos_fijos.clasificacion_id', $filters['clasificacion_id']);
        }

        $activos = $query->orderBy('activos_fijos.codigo_inventario')->get();

        $totales = [
            'valor_original' => $activos->sum('precio_adquisicion'),
            'depreciacion_acumulada' => $activos->sum('depreciacion_acumulada'),
            'valor_libros' => $activos->sum('valor_libros'),
        ];

        $system = \App\Models\SystemSetting::first();

        $pdf = Pdf::loadView('reportes.depreciacion-pdf', [
            'activos' => $activos,
            'totales' => $totales,
            'system' => $system,
        ]);

        $pdf->setPaper('letter', 'landscape');

        return $pdf->download('depreciacion.pdf');
    }

    /**
     * Configurar depreciación de un activo individual
     */
    public function configurar(Request $request, ActivoFijo $activo)
    {
        $validated = $request->validate([
            'vida_util_anos' => 'required|integer|min:1|max:100',
            'valor_residual' => 'nullable|numeric|min:0',
            'metodo_depreciacion' => 'required|in:LINEAL,SALDO_DECRECIENTE,UNIDADES_PRODUCIDAS',
        ]);

        $activo->update([
            'vida_util_anos' => $validated['vida_util_anos'],
            'valor_residual' => $validated['valor_residual'] ?? 0,
            'metodo_depreciacion' => $validated['metodo_depreciacion'],
        ]);

        // Calcular depreciación inicial
        $this->depreciacionService->calcularDepreciacionActivo($activo);

        return redirect()->route('activos.depreciacion')
            ->with('success', "Depreciación configurada para: {$activo->nombre_activo}");
    }

    /**
     * Configurar depreciación masiva de múltiples activos
     */
    public function configurarMasivo(Request $request)
    {
        $validated = $request->validate([
            'activo_ids' => 'required|array|min:1',
            'activo_ids.*' => 'exists:activos_fijos,id',
            'vida_util_anos' => 'required|integer|min:1|max:100',
            'valor_residual' => 'nullable|numeric|min:0',
            'metodo_depreciacion' => 'required|in:LINEAL,SALDO_DECRECIENTE,UNIDADES_PRODUCIDAS',
        ]);

        $count = 0;
        foreach ($validated['activo_ids'] as $id) {
            $activo = ActivoFijo::find($id);
            if ($activo) {
                $activo->update([
                    'vida_util_anos' => $validated['vida_util_anos'],
                    'valor_residual' => $validated['valor_residual'] ?? 0,
                    'metodo_depreciacion' => $validated['metodo_depreciacion'],
                ]);
                $this->depreciacionService->calcularDepreciacionActivo($activo);
                $count++;
            }
        }

        return redirect()->route('activos.depreciacion')
            ->with('success', "Depreciación configurada para {$count} activos");
    }
}
