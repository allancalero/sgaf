<?php

namespace App\Http\Controllers;

use App\Models\ActivoFijo;
use App\Models\Area;
use App\Models\Ubicacion;
use App\Models\Clasificacion;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\Image\Svg;

class ExportController extends Controller
{
    public function exportInventarioCsv(Request $request)
    {
        $filters = $request->all();

        $query = DB::table('activos_fijos')
            ->leftJoin('areas', 'activos_fijos.area_id', '=', 'areas.id')
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
                'activos_fijos.precio_adquisicion',
            );

        if (!empty($filters['area_id'])) $query->where('activos_fijos.area_id', $filters['area_id']);
        if (!empty($filters['ubicacion_id'])) $query->where('activos_fijos.ubicacion_id', $filters['ubicacion_id']);
        if (!empty($filters['personal_id'])) $query->where('activos_fijos.personal_id', $filters['personal_id']);
        if (!empty($filters['clasificacion_id'])) $query->where('activos_fijos.clasificacion_id', $filters['clasificacion_id']);
        if (!empty($filters['estado'])) $query->where('activos_fijos.estado', $filters['estado']);

        $rows = $query->orderBy('activos_fijos.codigo_inventario')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="inventario.csv"',
        ];

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Codigo', 'Nombre', 'Area', 'Clasificacion', 'Ubicacion', 'Responsable', 'Estado', 'Precio']);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->codigo_inventario,
                    $row->nombre_activo,
                    $row->area,
                    $row->clasificacion,
                    $row->ubicacion,
                    $row->responsable,
                    $row->estado,
                    $row->precio_adquisicion,
                ]);
            }
            fclose($out);
        }, 'inventario.csv', $headers);
    }

    public function exportInventarioPdf(Request $request)
    {
        $filters = $request->all();

        $query = ActivoFijo::query()
            ->with(['area', 'ubicacion', 'clasificacion', 'personal'])
            ->orderBy('codigo_inventario');

        if (!empty($filters['area_id'])) $query->where('area_id', $filters['area_id']);
        if (!empty($filters['ubicacion_id'])) $query->where('ubicacion_id', $filters['ubicacion_id']);
        if (!empty($filters['personal_id'])) $query->where('personal_id', $filters['personal_id']);
        if (!empty($filters['clasificacion_id'])) $query->where('clasificacion_id', $filters['clasificacion_id']);
        if (!empty($filters['estado'])) $query->where('estado', $filters['estado']);

        $activos = $query->get();
        $totales = [
            'cantidad' => $activos->count(),
            'valor' => $activos->sum(fn($a) => (float) $a->precio_adquisicion),
        ];

        $system = SystemSetting::first();
        
        $pdf = Pdf::loadView('reportes.inventario-pdf', [
            'activos' => $activos,
            'totales' => $totales,
            'system' => $system,
            'usuario' => auth()->user() ? auth()->user()->email : 'Sistema',
        ]);
        
        $pdf->setPaper('letter', 'landscape');
        return $pdf->stream('inventario.pdf');
    }

    public function qr(ActivoFijo $activo)
    {
        $activo->load(['area', 'ubicacion', 'personal']);

        // ONLY codigo_inventario for scanner compatibility
        $payload = $activo->codigo_inventario;

        $renderer = new Svg();
        $renderer->setWidth(320); $renderer->setHeight(320);

        $writer = new Writer($renderer);
        $svg = $writer->writeString($payload);

        return response()->view('qr-activo', [
            'svg' => $svg,
            'detalle' => $activo,
        ]);
    }

    public function descargarRespaldo(Request $request)
    {
        $filename = $request->query('file');
        if (!$filename) return abort(404);

        $path = storage_path('app/SGAF2/' . $filename);
        if (!file_exists($path)) return abort(404);

        return response()->download($path);
    }
}
