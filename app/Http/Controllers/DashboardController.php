<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $activosPorEstado = DB::table('activos_fijos')
            ->select('estado as label', DB::raw('COUNT(*) as value'))
            ->groupBy('estado')
            ->orderBy('estado')
            ->get();

        $activosPorArea = DB::table('activos_fijos')
            ->join('areas', 'activos_fijos.area_id', '=', 'areas.id')
            ->select('areas.nombre as label', DB::raw('COUNT(*) as value'))
            ->groupBy('areas.nombre')
            ->orderBy('areas.nombre')
            ->get();

        $responsablesPorArea = DB::table('responsables')
            ->join('areas', 'responsables.area_id', '=', 'areas.id')
            ->select('areas.nombre as label', DB::raw('COUNT(*) as value'))
            ->groupBy('areas.nombre')
            ->orderBy('areas.nombre')
            ->get();

        $activosPorClasificacion = DB::table('activos_fijos')
            ->join('clasificaciones', 'activos_fijos.clasificacion_id', '=', 'clasificaciones.id')
            ->select('clasificaciones.nombre as label', DB::raw('COUNT(*) as value'))
            ->groupBy('clasificaciones.nombre')
            ->orderBy('clasificaciones.nombre')
            ->get();

        $valorPorArea = DB::table('activos_fijos')
            ->join('areas', 'activos_fijos.area_id', '=', 'areas.id')
            ->select('areas.nombre as label', DB::raw('SUM(COALESCE(precio_adquisicion, 0)) as value'))
            ->groupBy('areas.nombre')
            ->orderBy('areas.nombre')
            ->get();

        $asignacionesPorMes = DB::table('historial_asignaciones')
            ->select(
                DB::raw("CONCAT(YEAR(fecha_asignacion), '-', LPAD(MONTH(fecha_asignacion), 2, '0')) as label"),
                DB::raw('COUNT(*) as value'),
                DB::raw('YEAR(fecha_asignacion) as year'),
                DB::raw('MONTH(fecha_asignacion) as month')
            )
            ->groupBy(
                DB::raw("CONCAT(YEAR(fecha_asignacion), '-', LPAD(MONTH(fecha_asignacion), 2, '0'))"),
                DB::raw('YEAR(fecha_asignacion)'),
                DB::raw('MONTH(fecha_asignacion)')
            )
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return Inertia::render('Dashboard', [
            'totals' => [
                'activos' => $activosPorEstado->sum('value'),
                'responsables' => DB::table('responsables')->count(),
                'asignaciones' => DB::table('historial_asignaciones')->count(),
                'sin_responsable' => DB::table('activos_fijos')->whereNull('personal_id')->count(),
                'costo_total' => DB::table('activos_fijos')->sum(DB::raw('COALESCE(precio_adquisicion, 0)')),
            ],
            'charts' => [
                'activosPorEstado' => $activosPorEstado,
                'activosPorArea' => $activosPorArea,
                'responsablesPorArea' => $responsablesPorArea,
                'asignacionesPorMes' => $asignacionesPorMes,
                'activosPorClasificacion' => $activosPorClasificacion,
                'valorPorArea' => $valorPorArea,
            ],
        ]);
    }
}
