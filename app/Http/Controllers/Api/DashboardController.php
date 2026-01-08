<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Totales
        $totalActivos = (int) DB::table('activos_fijos')->count();
        $totalPersonal = (int) DB::table('personal')->count();
        $totalAreas = (int) DB::table('areas')->count();
        $movimientosMes = (int) DB::table('historial_asignaciones')
            ->whereMonth('fecha_asignacion', date('m'))
            ->whereYear('fecha_asignacion', date('Y'))
            ->count();
        $sinAsignar = (int) DB::table('activos_fijos')->whereNull('personal_id')->count();
        $valorTotal = (float) DB::table('activos_fijos')->sum(DB::raw('COALESCE(precio_adquisicion, 0)'));

        // Activos por Estado
        $activosPorEstado = DB::table('activos_fijos')
            ->select('estado', DB::raw('COUNT(*) as cantidad'))
            ->groupBy('estado')
            ->orderBy('estado')
            ->get();

        // Activos por Área
        $activosPorArea = DB::table('activos_fijos')
            ->leftJoin('areas', 'activos_fijos.area_id', '=', 'areas.id')
            ->select('areas.nombre as area', DB::raw('COUNT(*) as cantidad'), DB::raw('SUM(COALESCE(precio_adquisicion, 0)) as valor'))
            ->groupBy('areas.nombre')
            ->orderByDesc('cantidad')
            ->get();

        // Responsables por Área
        $responsablesPorArea = DB::table('personal')
            ->leftJoin('areas', 'personal.area_id', '=', 'areas.id')
            ->select('areas.nombre as area', DB::raw('COUNT(*) as cantidad'))
            ->groupBy('areas.nombre')
            ->orderByDesc('cantidad')
            ->get();

        // Activos por Clasificación
        $activosPorClasificacion = DB::table('activos_fijos')
            ->leftJoin('clasificaciones', 'activos_fijos.clasificacion_id', '=', 'clasificaciones.id')
            ->select('clasificaciones.nombre as clasificacion', DB::raw('COUNT(*) as cantidad'))
            ->groupBy('clasificaciones.nombre')
            ->orderByDesc('cantidad')
            ->get();

        // Asignaciones por Mes (últimos 6 meses)
        $asignacionesPorMes = DB::table('historial_asignaciones')
            ->select(
                DB::raw("DATE_FORMAT(fecha_asignacion, '%Y-%m') as mes"),
                DB::raw('COUNT(*) as cantidad')
            )
            ->where('fecha_asignacion', '>=', now()->subMonths(6))
            ->groupBy(DB::raw("DATE_FORMAT(fecha_asignacion, '%Y-%m')"))
            ->orderBy('mes')
            ->get();

        // Valor por Área
        $valorPorArea = DB::table('activos_fijos')
            ->leftJoin('areas', 'activos_fijos.area_id', '=', 'areas.id')
            ->select('areas.nombre as area', DB::raw('SUM(COALESCE(precio_adquisicion, 0)) as valor'))
            ->groupBy('areas.nombre')
            ->orderByDesc('valor')
            ->get();

        return response()->json([
            'totalActivos' => $totalActivos,
            'totalPersonal' => $totalPersonal,
            'totalAreas' => $totalAreas,
            'movimientosMes' => $movimientosMes,
            'sinAsignar' => $sinAsignar,
            'valorTotal' => $valorTotal,
            'activosPorEstado' => $activosPorEstado,
            'activosPorArea' => $activosPorArea,
            'responsablesPorArea' => $responsablesPorArea,
            'asignacionesPorMes' => $asignacionesPorMes,
            'activosPorClasificacion' => $activosPorClasificacion,
            'valorPorArea' => $valorPorArea,
        ]);
    }
}
