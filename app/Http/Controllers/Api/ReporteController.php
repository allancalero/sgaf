<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivoFijo;
use App\Models\SystemSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function download($reportId)
    {
        $system = SystemSetting::first();
        $logoBase64 = '';
        if ($system && $system->logo_url) {
            $logoPath = str_replace('/storage', 'storage/app/public', parse_url($system->logo_url, PHP_URL_PATH));
            $fullPath = base_path($logoPath);
            if (file_exists($fullPath)) {
                $logoData = file_get_contents($fullPath);
                $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
            }
        }

        $usuario = auth()->user() ? (auth()->user()->full_name ?: auth()->user()->email) : 'Sistema';

        switch ($reportId) {
            case 'inventario-general':
                return $this->generarInventarioPdf('inventario_general.pdf', [], $system, $logoBase64, $usuario);

            case 'activos-por-area':
                return $this->generarInventarioPdf('activos_por_area.pdf', [], $system, $logoBase64, $usuario, 'area_id');

            case 'depreciacion-acumulada':
                return $this->generarDepreciacionPdf($system, $logoBase64);

            case 'historial-asignaciones':
                return $this->generarTrazabilidadPdf($system);

            case 'activos-en-mal-estado':
                return $this->generarInventarioPdf('activos_mal_estado.pdf', ['estado' => 'MALO'], $system, $logoBase64, $usuario);

            case 'personal-con-activos':
                $query = ActivoFijo::whereNotNull('personal_id');
                return $this->generarInventarioPdf('personal_con_activos.pdf', [], $system, $logoBase64, $usuario, 'personal_id', $query);

            default:
                return response()->json(['error' => 'Tipo de reporte no válido'], 400);
        }
    }

    private function generarInventarioPdf($filename, $filters, $system, $logoBase64, $usuario, $orderBy = 'codigo_inventario', $customQuery = null)
    {
        $query = $customQuery ?: ActivoFijo::query();
        $query->leftJoin('areas', 'activos_fijos.area_id', '=', 'areas.id')
            ->leftJoin('ubicaciones', 'activos_fijos.ubicacion_id', '=', 'ubicaciones.id')
            ->leftJoin('clasificaciones', 'activos_fijos.clasificacion_id', '=', 'clasificaciones.id')
            ->leftJoin('personal', 'activos_fijos.personal_id', '=', 'personal.id')
            ->select(
                'activos_fijos.codigo_inventario',
                'activos_fijos.nombre_activo',
                'areas.nombre as area',
                'clasificaciones.nombre as clasificacion',
                'ubicaciones.nombre as ubicacion',
                DB::raw("CONCAT_WS(' ', personal.nombre, personal.apellido) as responsable"),
                'activos_fijos.estado',
                'activos_fijos.precio_adquisicion'
            );

        if (isset($filters['estado'])) {
            $query->where('activos_fijos.estado', $filters['estado']);
        }

        $activos = $query->orderBy($orderBy === 'area_id' ? 'areas.nombre' : ($orderBy === 'personal_id' ? 'personal.nombre' : 'activos_fijos.codigo_inventario'))->get();

        $totales = [
            'cantidad' => $activos->count(),
            'valor' => $activos->sum(fn ($a) => (float) $a->precio_adquisicion),
        ];

        $pdf = Pdf::loadView('reportes.inventario-pdf', [
            'activos' => $activos,
            'totales' => $totales,
            'filters' => $filters,
            'system' => $system,
            'logoBase64' => $logoBase64,
            'usuario' => $usuario,
        ]);

        $pdf->setPaper('letter', 'landscape');
        return $pdf->stream($filename);
    }

    private function generarDepreciacionPdf($system, $logoBase64)
    {
        $activos = ActivoFijo::leftJoin('areas', 'activos_fijos.area_id', '=', 'areas.id')
            ->leftJoin('clasificaciones', 'activos_fijos.clasificacion_id', '=', 'clasificaciones.id')
            ->select('activos_fijos.*', 'areas.nombre as area', 'clasificaciones.nombre as clasificacion')
            ->whereNotNull('activos_fijos.vida_util_anos')
            ->orderBy('activos_fijos.codigo_inventario')
            ->get();

        $totales = [
            'valor_original' => $activos->sum('precio_adquisicion'),
            'depreciacion_acumulada' => $activos->sum('depreciacion_acumulada'),
            'valor_libros' => $activos->sum('valor_libros'),
        ];

        $pdf = Pdf::loadView('reportes.depreciacion-pdf', [
            'activos' => $activos,
            'totales' => $totales,
            'system' => $system,
            'logoBase64' => $logoBase64
        ]);

        $pdf->setPaper('letter', 'landscape');
        return $pdf->stream('depreciacion.pdf');
    }

    private function generarTrazabilidadPdf($system)
    {
        $movimientos = DB::table('historial_asignaciones as h')
            ->join('activos_fijos as a', 'h.activo_id', '=', 'a.id')
            ->leftJoin('personal as anterior', 'h.asignacion_anterior_id', '=', 'anterior.id')
            ->join('personal as nuevo', 'h.asignacion_nuevo_id', '=', 'nuevo.id')
            ->leftJoin('areas as area_anterior', 'anterior.area_id', '=', 'area_anterior.id')
            ->leftJoin('areas as area_nuevo', 'nuevo.area_id', '=', 'area_nuevo.id')
            ->select(
                'h.fecha_asignacion',
                'a.codigo_inventario',
                'a.nombre_activo',
                DB::raw("CONCAT_WS(' ', anterior.nombre, anterior.apellido) as desde"),
                DB::raw("CONCAT_WS(' ', nuevo.nombre, nuevo.apellido) as hacia"),
                'area_anterior.nombre as area_desde',
                'area_nuevo.nombre as area_hacia',
                'h.motivo'
            )
            ->orderByDesc('h.fecha_asignacion')
            ->get();

        $pdf = Pdf::loadView('reportes.trazabilidad-pdf', [
            'movimientos' => $movimientos,
            'filters' => [],
            'system' => $system,
        ]);

        $pdf->setPaper('letter', 'landscape');
        return $pdf->stream('trazabilidad.pdf');
    }

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
