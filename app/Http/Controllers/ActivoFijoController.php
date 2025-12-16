<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreActivoFijoRequest;
use App\Http\Requests\UpdateActivoFijoRequest;
use App\Models\ActivoFijo;
use App\Models\Area;
use App\Models\Ubicacion;
use App\Models\Clasificacion;
use App\Models\TipoActivo;
use App\Models\FuenteFinanciamiento;
use App\Models\Proveedor;
use App\Models\Personal;
use App\Models\SystemSetting;
use App\Models\Cheque;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\Image\Svg;
use Barryvdh\DomPDF\Facade\Pdf;

class ActivoFijoController extends Controller
{
    public function index(): Response
    {
        $activos = ActivoFijo::query()
            ->leftJoin('areas', 'activos_fijos.area_id', '=', 'areas.id')
            ->leftJoin('ubicaciones', 'activos_fijos.ubicacion_id', '=', 'ubicaciones.id')
            ->leftJoin('clasificaciones', 'activos_fijos.clasificacion_id', '=', 'clasificaciones.id')
            ->leftJoin('tipos_activos', 'activos_fijos.tipo_activo_id', '=', 'tipos_activos.id')
            ->leftJoin('fuentes_financiamiento', 'activos_fijos.fuente_financiamiento_id', '=', 'fuentes_financiamiento.id')
            ->leftJoin('proveedores', 'activos_fijos.proveedor_id', '=', 'proveedores.id')
            ->leftJoin('personal', 'activos_fijos.personal_id', '=', 'personal.id')
            ->leftJoin('cheques', 'activos_fijos.cheque_id', '=', 'cheques.id')
            ->select(
                'activos_fijos.id',
                'activos_fijos.codigo_inventario',
                'activos_fijos.nombre_activo',
                'activos_fijos.estado',
                'activos_fijos.cantidad',
                'activos_fijos.precio_adquisicion',
                'activos_fijos.fecha_adquisicion',
                'activos_fijos.area_id',
                'activos_fijos.ubicacion_id',
                'activos_fijos.clasificacion_id',
                'activos_fijos.tipo_activo_id',
                'activos_fijos.fuente_financiamiento_id',
                'activos_fijos.proveedor_id',
                'activos_fijos.personal_id',
                'activos_fijos.cheque_id',
                'activos_fijos.updated_at',
                'areas.nombre as area',
                'ubicaciones.nombre as ubicacion',
                'clasificaciones.nombre as clasificacion',
                'tipos_activos.nombre as tipo',
                'fuentes_financiamiento.nombre as fuente',
                'proveedores.nombre as proveedor',
                'cheques.numero_cheque',
                DB::raw("CONCAT(personal.nombre, ' ', personal.apellido) as responsable")
            )
            ->orderByDesc('activos_fijos.id')
            ->get();

        $areas = Area::orderBy('nombre')->get(['id', 'nombre']);
        $ubicaciones = Ubicacion::orderBy('nombre')->get(['id', 'nombre']);
        $clasificaciones = Clasificacion::orderBy('codigo')->get(['id', 'codigo', 'nombre']);
        $tipos = TipoActivo::orderBy('nombre')->get(['id', 'nombre', 'clasificacion_id']);
        $fuentes = FuenteFinanciamiento::orderBy('nombre')->get(['id', 'nombre']);
        $proveedores = Proveedor::orderBy('nombre')->get(['id', 'nombre']);
        $personal = Personal::orderBy('nombre')->get(['id', 'nombre', 'apellido', 'area_id']);
        $cheques = \App\Models\Cheque::orderBy('numero_cheque')->get(['id', 'numero_cheque', 'banco', 'saldo_disponible']);

        return Inertia::render('Activos/Index', [
            'activos' => $activos,
            'areas' => $areas,
            'ubicaciones' => $ubicaciones,
            'clasificaciones' => $clasificaciones,
            'tipos' => $tipos,
            'fuentes' => $fuentes,
            'proveedores' => $proveedores,
            'personal' => $personal,
            'cheques' => $cheques,
        ]);
    }

    public function trazabilidad(): Response
    {
        $activos = ActivoFijo::query()
            ->leftJoin('areas', 'activos_fijos.area_id', '=', 'areas.id')
            ->leftJoin('ubicaciones', 'activos_fijos.ubicacion_id', '=', 'ubicaciones.id')
            ->leftJoin('personal', 'activos_fijos.personal_id', '=', 'personal.id')
            ->select(
                'activos_fijos.id',
                'activos_fijos.codigo_inventario',
                'activos_fijos.nombre_activo',
                'activos_fijos.estado',
                'activos_fijos.personal_id',
                'areas.nombre as area',
                'ubicaciones.nombre as ubicacion',
                DB::raw("CONCAT_WS(' ', personal.nombre, personal.apellido) as responsable"),
            )
            ->orderBy('activos_fijos.codigo_inventario')
            ->get();

        $personal = Personal::orderBy('nombre')->get(['id', 'nombre', 'apellido']);

        $historial = DB::table('historial_asignaciones as h')
            ->join('activos_fijos as a', 'h.activo_id', '=', 'a.id')
            ->leftJoin('personal as anterior', 'h.asignacion_anterior_id', '=', 'anterior.id')
            ->join('personal as nuevo', 'h.asignacion_nuevo_id', '=', 'nuevo.id')
            ->leftJoin('areas as area_anterior', 'anterior.area_id', '=', 'area_anterior.id')
            ->leftJoin('areas as area_nuevo', 'nuevo.area_id', '=', 'area_nuevo.id')
            ->select(
                'h.id',
                'h.activo_id',
                'h.fecha_asignacion',
                'h.motivo',
                DB::raw("CONCAT_WS(' ', anterior.nombre, anterior.apellido) as anterior_nombre"),
                DB::raw("CONCAT_WS(' ', nuevo.nombre, nuevo.apellido) as nuevo_nombre"),
                'area_anterior.nombre as area_anterior',
                'area_nuevo.nombre as area_nuevo',
            )
            ->orderByDesc('h.fecha_asignacion')
            ->orderByDesc('h.id')
            ->get()
            ->groupBy('activo_id');

        $activosConHistorial = $activos->map(function ($activo) use ($historial) {
            $movimientos = $historial->get($activo->id, collect());

            return [
                'id' => $activo->id,
                'codigo_inventario' => $activo->codigo_inventario,
                'nombre_activo' => $activo->nombre_activo,
                'estado' => $activo->estado,
                'personal_id' => $activo->personal_id,
                'area' => $activo->area,
                'ubicacion' => $activo->ubicacion,
                'responsable' => $activo->responsable,
                'historial' => $movimientos->map(fn ($row) => [
                    'id' => $row->id,
                    'fecha_asignacion' => $row->fecha_asignacion,
                    'motivo' => $row->motivo,
                    'desde' => $row->anterior_nombre,
                    'hacia' => $row->nuevo_nombre,
                    'area_desde' => $row->area_anterior,
                    'area_hacia' => $row->area_nuevo,
                ])->values()->all(),
            ];
        });

        return Inertia::render('Activos/Trazabilidad', [
            'activos' => $activosConHistorial,
            'personal' => $personal,
        ]);
    }

    public function actualizarTrazabilidad(Request $request, ActivoFijo $activo): RedirectResponse
    {
        $data = $request->validate([
            'personal_id' => ['required', 'exists:personal,id'],
            'fecha_asignacion' => ['required', 'date'],
            'motivo' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($activo, $data) {
            DB::table('historial_asignaciones')->insert([
                'activo_id' => $activo->id,
                'asignacion_anterior_id' => $activo->personal_id,
                'asignacion_nuevo_id' => $data['personal_id'],
                'fecha_asignacion' => $data['fecha_asignacion'],
                'motivo' => $data['motivo'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $activo->update([
                'personal_id' => $data['personal_id'],
            ]);
        });

        return redirect()
            ->route('activos.trazabilidad')
            ->with('success', 'Trazabilidad actualizada');
    }

    public function reportes(Request $request): Response
    {
        $filters = $request->validate([
            'area_id' => ['nullable', 'integer'],
            'ubicacion_id' => ['nullable', 'integer'],
            'personal_id' => ['nullable', 'integer'],
            'clasificacion_id' => ['nullable', 'integer'],
            'estado' => ['nullable', 'string'],
        ]);

        $query = ActivoFijo::query()
            ->leftJoin('areas', 'activos_fijos.area_id', '=', 'areas.id')
            ->leftJoin('ubicaciones', 'activos_fijos.ubicacion_id', '=', 'ubicaciones.id')
            ->leftJoin('clasificaciones', 'activos_fijos.clasificacion_id', '=', 'clasificaciones.id')
            ->leftJoin('personal', 'activos_fijos.personal_id', '=', 'personal.id')
            ->select(
                'activos_fijos.id',
                'activos_fijos.codigo_inventario',
                'activos_fijos.nombre_activo',
                'activos_fijos.estado',
                'activos_fijos.precio_adquisicion',
                'areas.nombre as area',
                'ubicaciones.nombre as ubicacion',
                'clasificaciones.nombre as clasificacion',
                DB::raw("CONCAT_WS(' ', personal.nombre, personal.apellido) as responsable"),
            );

        if ($filters['area_id'] ?? false) {
            $query->where('activos_fijos.area_id', $filters['area_id']);
        }
        if ($filters['ubicacion_id'] ?? false) {
            $query->where('activos_fijos.ubicacion_id', $filters['ubicacion_id']);
        }
        if ($filters['personal_id'] ?? false) {
            $query->where('activos_fijos.personal_id', $filters['personal_id']);
        }
        if ($filters['clasificacion_id'] ?? false) {
            $query->where('activos_fijos.clasificacion_id', $filters['clasificacion_id']);
        }
        if ($filters['estado'] ?? false) {
            $query->where('activos_fijos.estado', $filters['estado']);
        }

        $activos = $query->orderBy('activos_fijos.codigo_inventario')->get();

        $totales = [
            'cantidad' => $activos->count(),
            'valor' => $activos->sum(function ($a) {
                return (float) $a->precio_adquisicion;
            }),
        ];

        $areas = Area::orderBy('nombre')->get(['id', 'nombre']);
        $ubicaciones = Ubicacion::where('estado', 'activo')->orderBy('nombre')->get(['id', 'nombre']);
        $clasificaciones = Clasificacion::orderBy('codigo')->get(['id', 'codigo', 'nombre']);
        $activosList = ActivoFijo::orderBy('codigo_inventario')->get(['id', 'codigo_inventario', 'nombre_activo']);
        $personal = Personal::orderBy('nombre')->get(['id', 'nombre', 'apellido', 'area_id']);

        // Mapa de ubicaciones usadas por area
        $areaLocationMap = ActivoFijo::select('area_id', 'ubicacion_id')
            ->whereNotNull('area_id')
            ->whereNotNull('ubicacion_id')
            ->distinct()
            ->get()
            ->groupBy('area_id')
            ->map(fn($items) => $items->pluck('ubicacion_id')->unique()->values()->all())
            ->all();

        return Inertia::render('Activos/Reportes', [
            'activos' => $activos,
            'totales' => $totales,
            'areas' => $areas,
            'ubicaciones' => $ubicaciones,
            'clasificaciones' => $clasificaciones,
            'filters' => $filters,
            'activosList' => $activosList,
            'personal' => $personal,
            'areaLocationMap' => $areaLocationMap,
        ]);
    }

    public function exportInventarioCsv(Request $request)
    {
        $filters = $request->validate([
            'area_id' => ['nullable', 'integer'],
            'ubicacion_id' => ['nullable', 'integer'],
            'personal_id' => ['nullable', 'integer'],
            'clasificacion_id' => ['nullable', 'integer'],
            'estado' => ['nullable', 'string'],
        ]);

        $query = ActivoFijo::query()
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

        if ($filters['area_id'] ?? false) {
            $query->where('activos_fijos.area_id', $filters['area_id']);
        }
        if ($filters['ubicacion_id'] ?? false) {
            $query->where('activos_fijos.ubicacion_id', $filters['ubicacion_id']);
        }
        if ($filters['personal_id'] ?? false) {
            $query->where('activos_fijos.personal_id', $filters['personal_id']);
        }
        if ($filters['clasificacion_id'] ?? false) {
            $query->where('activos_fijos.clasificacion_id', $filters['clasificacion_id']);
        }
        if ($filters['estado'] ?? false) {
            $query->where('activos_fijos.estado', $filters['estado']);
        }

        $rows = $query->orderBy('activos_fijos.codigo_inventario')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="inventario.csv"',
        ];

        $callback = function () use ($rows) {
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
        };

        return response()->streamDownload($callback, 'inventario.csv', $headers);
    }

    public function exportTrazabilidadCsv(Request $request)
    {
        $filters = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date'],
            'activo_id' => ['nullable', 'integer'],
            'area_id' => ['nullable', 'integer'],
            'personal_id' => ['nullable', 'integer'],
        ]);

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
                'h.motivo',
            );

        if ($filters['desde'] ?? false) {
            $query->whereDate('h.fecha_asignacion', '>=', $filters['desde']);
        }
        if ($filters['hasta'] ?? false) {
            $query->whereDate('h.fecha_asignacion', '<=', $filters['hasta']);
        }
        if ($filters['activo_id'] ?? false) {
            $query->where('h.activo_id', $filters['activo_id']);
        }
        if ($filters['area_id'] ?? false) {
            $query->where('area_nuevo.id', $filters['area_id']);
        }
        if ($filters['personal_id'] ?? false) {
            $query->where('h.asignacion_nuevo_id', $filters['personal_id']);
        }

        $rows = $query->orderByDesc('h.fecha_asignacion')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="trazabilidad.csv"',
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Fecha', 'Codigo', 'Activo', 'Desde', 'Hacia', 'Area desde', 'Area hacia', 'Motivo']);
            foreach ($rows as $row) {
                fputcsv($out, [
                    $row->fecha_asignacion,
                    $row->codigo_inventario,
                    $row->nombre_activo,
                    $row->desde,
                    $row->hacia,
                    $row->area_desde,
                    $row->area_hacia,
                    $row->motivo,
                ]);
            }
            fclose($out);
        };

        return response()->streamDownload($callback, 'trazabilidad.csv', $headers);
    }

    public function store(StoreActivoFijoRequest $request): RedirectResponse
    {
        $data = $request->validated();
        
        // Handle photo upload
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $filename = time() . '_' . $foto->getClientOriginalName();
            $path = $foto->storeAs('activos', $filename, 'public');
            $data['foto'] = $path;
        }
        
        ActivoFijo::create($data);

        return redirect()->route('activos.index')->with('success', 'Activo creado');
    }

    public function update(UpdateActivoFijoRequest $request, ActivoFijo $activo): RedirectResponse
    {
        $data = $request->validated();
        
        // Handle photo upload
        if ($request->hasFile('foto')) {
            // Delete old photo if exists
            if ($activo->foto && \Storage::disk('public')->exists($activo->foto)) {
                \Storage::disk('public')->delete($activo->foto);
            }
            
            $foto = $request->file('foto');
            $filename = time() . '_' . $foto->getClientOriginalName();
            $path = $foto->storeAs('activos', $filename, 'public');
            $data['foto'] = $path;
        }
        
        $activo->update($data);

        return redirect()->route('activos.index')->with('success', 'Activo actualizado');
    }

    public function destroy(ActivoFijo $activo): RedirectResponse
    {
        // Delete photo if exists
        if ($activo->foto && \Storage::disk('public')->exists($activo->foto)) {
            \Storage::disk('public')->delete($activo->foto);
        }
        
        $activo->delete();

        return redirect()->route('activos.index')->with('success', 'Activo eliminado');
    }

    public function qr(ActivoFijo $activo)
    {
        $detalle = ActivoFijo::query()
            ->leftJoin('areas', 'activos_fijos.area_id', '=', 'areas.id')
            ->leftJoin('ubicaciones', 'activos_fijos.ubicacion_id', '=', 'ubicaciones.id')
            ->leftJoin('personal', 'activos_fijos.personal_id', '=', 'personal.id')
            ->select(
                'activos_fijos.id',
                'activos_fijos.codigo_inventario',
                'activos_fijos.nombre_activo',
                'activos_fijos.estado',
                'activos_fijos.area_id',
                'activos_fijos.ubicacion_id',
                'activos_fijos.personal_id',
                'activos_fijos.fecha_adquisicion',
                'areas.nombre as area_nombre',
                'ubicaciones.nombre as ubicacion_nombre',
                DB::raw("CONCAT(personal.nombre, ' ', personal.apellido) as responsable_nombre")
            )
            ->where('activos_fijos.id', $activo->id)
            ->firstOrFail();

        $payload = json_encode([
            'codigo' => $detalle->codigo_inventario,
            'nombre' => $detalle->nombre_activo,
            'estado' => $detalle->estado,
            'area' => $detalle->area_nombre,
            'ubicacion' => $detalle->ubicacion_nombre,
            'responsable' => $detalle->responsable_nombre,
            'fecha_adquisicion' => $detalle->fecha_adquisicion,
            'url' => route('activos.index'),
        ]);

        $renderer = new Svg();
        $renderer->setWidth(320);
        $renderer->setHeight(320);
        $renderer->setMargin(1);

        $writer = new Writer($renderer);
        $svg = $writer->writeString($payload);

        if (request()->boolean('raw')) {
            return response($svg)->header('Content-Type', 'image/svg+xml');
        }

        return response()->view('qr-activo', [
            'svg' => $svg,
            'detalle' => $detalle,
        ]);
    }

    public function exportInventarioPdf(Request $request)
    {
        $filters = $request->validate([
            'area_id' => ['nullable', 'integer'],
            'ubicacion_id' => ['nullable', 'integer'],
            'personal_id' => ['nullable', 'integer'],
            'clasificacion_id' => ['nullable', 'integer'],
            'estado' => ['nullable', 'string'],
        ]);

        $query = ActivoFijo::query()
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

        if ($filters['area_id'] ?? false) {
            $query->where('activos_fijos.area_id', $filters['area_id']);
        }
        if ($filters['ubicacion_id'] ?? false) {
            $query->where('activos_fijos.ubicacion_id', $filters['ubicacion_id']);
        }
        if ($filters['personal_id'] ?? false) {
            $query->where('activos_fijos.personal_id', $filters['personal_id']);
        }
        if ($filters['clasificacion_id'] ?? false) {
            $query->where('activos_fijos.clasificacion_id', $filters['clasificacion_id']);
        }
        if ($filters['estado'] ?? false) {
            $query->where('activos_fijos.estado', $filters['estado']);
        }

        $activos = $query->orderBy('activos_fijos.codigo_inventario')->get();
        
        $totales = [
            'cantidad' => $activos->count(),
            'valor' => $activos->sum(fn($a) => (float) $a->precio_adquisicion),
        ];

        $system = \App\Models\SystemSetting::first();

        $pdf = Pdf::loadView('reportes.inventario-pdf', [
            'activos' => $activos,
            'totales' => $totales,
            'filters' => $filters,
            'system' => $system,
        ]);
        
        $pdf->setPaper('letter', 'landscape');

        return $pdf->stream('inventario.pdf', [
            'Attachment' => true
        ]);
    }

    public function exportTrazabilidadPdf(Request $request)
    {
        $filters = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date'],
            'activo_id' => ['nullable', 'integer'],
            'area_id' => ['nullable', 'integer'],
            'personal_id' => ['nullable', 'integer'],
        ]);

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
                'h.motivo',
            );

        if ($filters['desde'] ?? false) {
            $query->whereDate('h.fecha_asignacion', '>=', $filters['desde']);
        }
        if ($filters['hasta'] ?? false) {
            $query->whereDate('h.fecha_asignacion', '<=', $filters['hasta']);
        }
        if ($filters['activo_id'] ?? false) {
            $query->where('h.activo_id', $filters['activo_id']);
        }
        if ($filters['area_id'] ?? false) {
            $query->where('area_nuevo.id', $filters['area_id']);
        }
        if ($filters['personal_id'] ?? false) {
            $query->where('h.asignacion_nuevo_id', $filters['personal_id']);
        }

        $movimientos = $query->orderByDesc('h.fecha_asignacion')->get();

        $system = \App\Models\SystemSetting::first();

        $pdf = Pdf::loadView('reportes.trazabilidad-pdf', [
            'movimientos' => $movimientos,
            'filters' => $filters,
            'system' => $system,
        ]);
        
        $pdf->setPaper('letter', 'landscape');

        return $pdf->stream('trazabilidad.pdf', [
            'Attachment' => true
        ]);
    }

    /**
     * Generar Acta de Asignación en PDF
     */
    public function generarActaAsignacion($id)
    {
        $activo = ActivoFijo::with(['area', 'ubicacion', 'clasificacion', 'personal'])
            ->findOrFail($id);

        // Verificar que tenga responsable asignado
        if (!$activo->personal_id) {
            return redirect()->back()->with('error', 'El activo no tiene un responsable asignado.');
        }

        $system = SystemSetting::first();

        // Generate QR Code
        $renderer = new Svg();
        $renderer->setHeight(120);
        $renderer->setWidth(120);
        $writer = new Writer($renderer);
        $qrSvg = $writer->writeString($activo->codigo_inventario);
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
        
        // Convertir logo a base64 para DomPDF
        $logoPath = public_path('logo-alcaldia.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
        }
        
        $pdf = Pdf::loadView('reportes.acta-asignacion-pdf', [
            'activo' => $activo,
            'system' => $system,
            'fecha_emision' => now()->format('d/m/Y'),
            'qrCode' => $qrBase64,
            'logoBase64' => $logoBase64,
        ]);

        $pdf->setPaper('letter', 'portrait');

        // Sanitize filename to avoid issues with special characters
    $safeCode = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', $activo->codigo_inventario);
    $filename = "acta-asignacion-{$safeCode}.pdf";
    
    return $pdf->stream($filename, [
        'Attachment' => true  // Force download instead of inline display
    ]);
    }

    /**
     * Vista para seleccionar activos y generar etiquetas QR
     */
    public function etiquetasQr(Request $request): Response
    {
        $filters = $request->validate([
            'area_id' => ['nullable', 'integer'],
            'ubicacion_id' => ['nullable', 'integer'],
            'personal_id' => ['nullable', 'integer'],
            'clasificacion_id' => ['nullable', 'integer'],
            'estado' => ['nullable', 'string'],
        ]);

        $query = ActivoFijo::query()
            ->leftJoin('areas', 'activos_fijos.area_id', '=', 'areas.id')
            ->leftJoin('ubicaciones', 'activos_fijos.ubicacion_id', '=', 'ubicaciones.id')
            ->leftJoin('clasificaciones', 'activos_fijos.clasificacion_id', '=', 'clasificaciones.id')
            ->leftJoin('personal', 'activos_fijos.personal_id', '=', 'personal.id')
            ->select(
                'activos_fijos.id',
                'activos_fijos.codigo_inventario',
                'activos_fijos.nombre_activo',
                'activos_fijos.estado',
                'areas.nombre as area',
                'ubicaciones.nombre as ubicacion',
                'clasificaciones.nombre as clasificacion',
                DB::raw("CONCAT_WS(' ', personal.nombre, personal.apellido) as responsable"),
            );

        if ($filters['area_id'] ?? false) {
            $query->where('activos_fijos.area_id', $filters['area_id']);
        }
        if ($filters['ubicacion_id'] ?? false) {
            $query->where('activos_fijos.ubicacion_id', $filters['ubicacion_id']);
        }
        if ($filters['personal_id'] ?? false) {
            $query->where('activos_fijos.personal_id', $filters['personal_id']);
        }
        if ($filters['clasificacion_id'] ?? false) {
            $query->where('activos_fijos.clasificacion_id', $filters['clasificacion_id']);
        }
        if ($filters['estado'] ?? false) {
            $query->where('activos_fijos.estado', $filters['estado']);
        }

        $activos = $query->orderBy('activos_fijos.codigo_inventario')->get();

        $areas = Area::orderBy('nombre')->get(['id', 'nombre']);
        $ubicaciones = Ubicacion::where('estado', 'activo')->orderBy('nombre')->get(['id', 'nombre']);
        $clasificaciones = Clasificacion::orderBy('codigo')->get(['id', 'codigo', 'nombre']);
        $personal = Personal::orderBy('nombre')->get(['id', 'nombre', 'apellido', 'area_id']);

        // Mapa de ubicaciones usadas por area
        $areaLocationMap = ActivoFijo::select('area_id', 'ubicacion_id')
            ->whereNotNull('area_id')
            ->whereNotNull('ubicacion_id')
            ->distinct()
            ->get()
            ->groupBy('area_id')
            ->map(fn($items) => $items->pluck('ubicacion_id')->unique()->values()->all())
            ->all();

        return Inertia::render('Activos/EtiquetasQr', [
            'activos' => $activos,
            'areas' => $areas,
            'ubicaciones' => $ubicaciones,
            'clasificaciones' => $clasificaciones,
            'personal' => $personal,
            'filters' => $filters,
            'areaLocationMap' => $areaLocationMap,
        ]);
    }

    /**
     * Generar PDF con etiquetas QR para impresión
     */
    public function generarEtiquetasQrPdf(Request $request)
    {
        $filters = $request->validate([
            'area_id' => ['nullable', 'integer'],
            'ubicacion_id' => ['nullable', 'integer'],
            'personal_id' => ['nullable', 'integer'],
            'clasificacion_id' => ['nullable', 'integer'],
            'estado' => ['nullable', 'string'],
        ]);

        $query = ActivoFijo::query()
            ->leftJoin('areas', 'activos_fijos.area_id', '=', 'areas.id')
            ->leftJoin('ubicaciones', 'activos_fijos.ubicacion_id', '=', 'ubicaciones.id')
            ->leftJoin('clasificaciones', 'activos_fijos.clasificacion_id', '=', 'clasificaciones.id')
            ->select(
                'activos_fijos.id',
                'activos_fijos.codigo_inventario',
                'activos_fijos.nombre_activo',
                'areas.nombre as area',
                'ubicaciones.nombre as ubicacion',
                'clasificaciones.nombre as clasificacion'
            );

        if ($filters['area_id'] ?? false) {
            $query->where('activos_fijos.area_id', $filters['area_id']);
        }
        if ($filters['ubicacion_id'] ?? false) {
            $query->where('activos_fijos.ubicacion_id', $filters['ubicacion_id']);
        }
        if ($filters['personal_id'] ?? false) {
            $query->where('activos_fijos.personal_id', $filters['personal_id']);
        }
        if ($filters['clasificacion_id'] ?? false) {
            $query->where('activos_fijos.clasificacion_id', $filters['clasificacion_id']);
        }
        if ($filters['estado'] ?? false) {
            $query->where('activos_fijos.estado', $filters['estado']);
        }

        $activos = $query->orderBy('activos_fijos.codigo_inventario')->get();

        // Generar QR codes para cada activo
        $activosConQr = $activos->map(function($activo) {
            // Crear nuevo renderer y writer para cada QR
            $renderer = new Svg();
            $renderer->setHeight(100);
            $renderer->setWidth(100);
            $writer = new Writer($renderer);
            
            $qrSvg = $writer->writeString($activo->codigo_inventario);
            $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
            
            return [
                'codigo' => $activo->codigo_inventario,
                'nombre' => $activo->nombre_activo,
                'area' => $activo->area,
                'qr' => $qrBase64,
            ];
        });

        $system = SystemSetting::first();

        // Convertir logo a base64 para DomPDF
        $logoPath = public_path('logo-alcaldia.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
        }

        $pdf = Pdf::loadView('reportes.etiquetas-qr-pdf', [
            'activos' => $activosConQr,
            'system' => $system,
            'logoBase64' => $logoBase64,
        ]);

        $pdf->setPaper('letter', 'portrait');

        return $pdf->stream('etiquetas-qr.pdf', [
            'Attachment' => true
        ]);
    }
}
