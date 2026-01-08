<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function resumen()
    {
        $resumen = [
            'total_activos' => DB::table('activos_fijos')->count(),
            'activos_buenos' => DB::table('activos_fijos')->where('estado', 'BUENO')->count(),
            'activos_regulares' => DB::table('activos_fijos')->where('estado', 'REGULAR')->count(),
            'activos_malos' => DB::table('activos_fijos')->where('estado', 'MALO')->count(),
            'sin_asignar' => DB::table('activos_fijos')->whereNull('personal_id')->count(),
            'valor_total' => DB::table('activos_fijos')->sum('precio_adquisicion'),
            'total_personal' => DB::table('personal')->count(),
            'total_areas' => DB::table('areas')->count(),
        ];

        return response()->json($resumen);
    }

    public function activosPorArea()
    {
        $data = DB::table('activos_fijos')
            ->leftJoin('areas', 'activos_fijos.area_id', '=', 'areas.id')
            ->select('areas.nombre as area', DB::raw('COUNT(*) as cantidad'), DB::raw('SUM(precio_adquisicion) as valor'))
            ->groupBy('areas.nombre')
            ->orderBy('cantidad', 'desc')
            ->get();

        return response()->json($data);
    }

    public function activosPorEstado()
    {
        $data = DB::table('activos_fijos')
            ->select('estado', DB::raw('COUNT(*) as cantidad'))
            ->groupBy('estado')
            ->get();

        return response()->json($data);
    }

    public function activosPorClasificacion()
    {
        $data = DB::table('activos_fijos')
            ->leftJoin('clasificaciones', 'activos_fijos.clasificacion_id', '=', 'clasificaciones.id')
            ->select('clasificaciones.nombre as clasificacion', DB::raw('COUNT(*) as cantidad'))
            ->groupBy('clasificaciones.nombre')
            ->orderBy('cantidad', 'desc')
            ->get();

        return response()->json($data);
    }
}
