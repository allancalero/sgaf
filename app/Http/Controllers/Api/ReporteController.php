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
    public function download(Request $request, $reportId)
    {
        $system = SystemSetting::first();
        $logoBase64 = '';
        
        // Try to get logo from DB
        if ($system && $system->logo_url) {
            $logoPath = str_replace('/storage', 'storage/app/public', parse_url($system->logo_url, PHP_URL_PATH));
            $fullPath = base_path($logoPath);
            if (file_exists($fullPath)) {
                $logoData = file_get_contents($fullPath);
                $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
            }
        }

        // Fallback to public/logo-alcaldia.png if still empty
        if (!$logoBase64) {
            $fallbackPath = public_path('logo-alcaldia.png');
            if (file_exists($fallbackPath)) {
                $logoData = file_get_contents($fallbackPath);
                $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
            }
        }

        $usuario = auth()->user() ? (auth()->user()->full_name ?: auth()->user()->email) : 'Sistema';

        // Extract filters from query parameters
        $filters = [];
        if ($request->filled('area_id')) {
            $filters['area_id'] = $request->area_id;
            $area = DB::table('areas')->where('id', $request->area_id)->first();
            $filters['area_nombre'] = $area ? $area->nombre : '';
        }
        if ($request->filled('clasificacion_id')) {
            $filters['clasificacion_id'] = $request->clasificacion_id;
            $clasificacion = DB::table('clasificaciones')->where('id', $request->clasificacion_id)->first();
            $filters['clasificacion_nombre'] = $clasificacion ? ($clasificacion->prefijo . ' ' . $clasificacion->nombre) : '';
        }
        if ($request->filled('ubicacion_id')) {
            $filters['ubicacion_id'] = $request->ubicacion_id;
            $ubicacion = DB::table('ubicaciones')->where('id', $request->ubicacion_id)->first();
            $filters['ubicacion_nombre'] = $ubicacion ? $ubicacion->nombre : '';
        }
        if ($request->filled('personal_id')) {
            $filters['personal_id'] = $request->personal_id;
            $personal = DB::table('personal')->where('id', $request->personal_id)->first();
            $filters['personal_nombre'] = $personal ? ($personal->nombre . ' ' . $personal->apellido) : '';
        }
        if ($request->filled('estado')) {
            $filters['estado'] = $request->estado;
        }

        switch ($reportId) {
            case 'inventario-general':
                return $this->generarInventarioPdf('inventario_general.pdf', $filters, $system, $logoBase64, $usuario);

            case 'activos-por-area':
                return $this->generarInventarioPdf('activos_por_area.pdf', $filters, $system, $logoBase64, $usuario, 'area_id');

            case 'depreciacion-acumulada':
                return $this->generarDepreciacionPdf($system, $logoBase64, $filters);

            case 'historial-asignaciones':
                return $this->generarTrazabilidadPdf($system, $filters);

            case 'activos-en-mal-estado':
                $filters['estado'] = 'MALO';
                return $this->generarInventarioPdf('activos_mal_estado.pdf', $filters, $system, $logoBase64, $usuario);

            case 'personal-con-activos':
                $query = ActivoFijo::whereNotNull('personal_id');
                return $this->generarInventarioPdf('personal_con_activos.pdf', $filters, $system, $logoBase64, $usuario, 'personal_id', $query);

            case 'activos-por-clasificacion':
                return $this->generarInventarioPdf('activos_por_clasificacion.pdf', $filters, $system, $logoBase64, $usuario, 'clasificacion_id');

            case 'activos-por-ubicacion':
                return $this->generarInventarioPdf('activos_por_ubicacion.pdf', $filters, $system, $logoBase64, $usuario, 'ubicacion_id');

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
                DB::raw("CONCAT_WS(' ', clasificaciones.prefijo, clasificaciones.nombre, clasificaciones.codigo) as clasificacion"),
                'ubicaciones.nombre as ubicacion',
                DB::raw("CONCAT_WS(' ', personal.nombre, personal.apellido) as responsable"),
                'activos_fijos.estado',
                'activos_fijos.precio_adquisicion'
            );

        if (isset($filters['estado'])) {
            $query->where('activos_fijos.estado', $filters['estado']);
        }
        if (isset($filters['area_id'])) {
            $query->where('activos_fijos.area_id', $filters['area_id']);
        }
        if (isset($filters['clasificacion_id'])) {
            $query->where('activos_fijos.clasificacion_id', $filters['clasificacion_id']);
        }
        if (isset($filters['ubicacion_id'])) {
            $query->where('activos_fijos.ubicacion_id', $filters['ubicacion_id']);
        }
        if (isset($filters['personal_id'])) {
            $query->where('activos_fijos.personal_id', $filters['personal_id']);
        }

        // Determine order column
        $orderColumn = 'activos_fijos.codigo_inventario';
        if ($orderBy === 'area_id') $orderColumn = 'areas.nombre';
        elseif ($orderBy === 'personal_id') $orderColumn = 'personal.nombre';
        elseif ($orderBy === 'clasificacion_id') $orderColumn = 'clasificaciones.prefijo';
        elseif ($orderBy === 'ubicacion_id') $orderColumn = 'ubicaciones.nombre';

        $activos = $query->orderBy($orderColumn)->get();

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

        $pdf->setOption('isPhpEnabled', true);
        $pdf->setPaper('letter', 'landscape');
        return $pdf->stream($filename);
    }

    private function generarDepreciacionPdf($system, $logoBase64, $filters = [])
    {
        $query = ActivoFijo::leftJoin('areas', 'activos_fijos.area_id', '=', 'areas.id')
            ->leftJoin('clasificaciones', 'activos_fijos.clasificacion_id', '=', 'clasificaciones.id')
            ->select(
                'activos_fijos.*', 
                'areas.nombre as area', 
                DB::raw("CONCAT_WS(' ', clasificaciones.prefijo, clasificaciones.nombre, clasificaciones.codigo) as clasificacion")
            )
            ->whereNotNull('activos_fijos.vida_util_anos');

        if (isset($filters['area_id'])) {
            $query->where('activos_fijos.area_id', $filters['area_id']);
        }
        if (isset($filters['clasificacion_id'])) {
            $query->where('activos_fijos.clasificacion_id', $filters['clasificacion_id']);
        }

        $activos = $query->orderBy('activos_fijos.codigo_inventario')->get();

        $totales = [
            'valor_original' => $activos->sum('precio_adquisicion'),
            'depreciacion_acumulada' => $activos->sum('depreciacion_acumulada'),
            'valor_libros' => $activos->sum('valor_libros'),
        ];

        $pdf = Pdf::loadView('reportes.depreciacion-pdf', [
            'activos' => $activos,
            'totales' => $totales,
            'system' => $system,
            'logoBase64' => $logoBase64,
            'filters' => $filters
        ]);

        $pdf->setOption('isPhpEnabled', true);
        $pdf->setPaper('letter', 'landscape');
        return $pdf->stream('depreciacion.pdf');
    }

    private function generarTrazabilidadPdf($system, $filters = [])
    {
        $query = DB::table('historial_asignaciones as h')
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
            );

        if (isset($filters['personal_id'])) {
            $query->where(function($q) use ($filters) {
                $q->where('h.asignacion_anterior_id', $filters['personal_id'])
                  ->orWhere('h.asignacion_nuevo_id', $filters['personal_id']);
            });
        }

        $movimientos = $query->orderByDesc('h.fecha_asignacion')->get();

        $pdf = Pdf::loadView('reportes.trazabilidad-pdf', [
            'movimientos' => $movimientos,
            'filters' => $filters,
            'system' => $system,
        ]);

        $pdf->setOption('isPhpEnabled', true);
        $pdf->setPaper('letter', 'landscape');
        return $pdf->stream('trazabilidad.pdf');
    }

    public function resumen(Request $request)
    {
        $query = DB::table('activos_fijos');
        
        // Apply filters
        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }
        if ($request->filled('ubicacion_id')) {
            $query->where('ubicacion_id', $request->ubicacion_id);
        }
        if ($request->filled('personal_id')) {
            $query->where('personal_id', $request->personal_id);
        }
        if ($request->filled('clasificacion_id')) {
            $query->where('clasificacion_id', $request->clasificacion_id);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $resumen = [
            'total_activos' => (clone $query)->count(),
            'activos_buenos' => (clone $query)->where('estado', 'BUENO')->count(),
            'activos_regulares' => (clone $query)->where('estado', 'REGULAR')->count(),
            'activos_malos' => (clone $query)->where('estado', 'MALO')->count(),
            'sin_asignar' => (clone $query)->whereNull('personal_id')->count(),
            'valor_total' => (clone $query)->sum('precio_adquisicion'),
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

    public function activosPorClasificacion(Request $request)
    {
        $query = DB::table('activos_fijos')
            ->leftJoin('clasificaciones', 'activos_fijos.clasificacion_id', '=', 'clasificaciones.id');
        
        // Apply filters
        if ($request->filled('area_id')) {
            $query->where('activos_fijos.area_id', $request->area_id);
        }
        if ($request->filled('ubicacion_id')) {
            $query->where('activos_fijos.ubicacion_id', $request->ubicacion_id);
        }
        if ($request->filled('personal_id')) {
            $query->where('activos_fijos.personal_id', $request->personal_id);
        }
        if ($request->filled('estado')) {
            $query->where('activos_fijos.estado', $request->estado);
        }

        $data = $query->select('clasificaciones.nombre as clasificacion', DB::raw('COUNT(*) as cantidad'))
            ->groupBy('clasificaciones.nombre')
            ->orderBy('cantidad', 'desc')
            ->get();

        return response()->json($data);
    }
}
