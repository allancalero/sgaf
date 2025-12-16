<?php

namespace App\Http\Controllers;

use App\Models\Reasignacion;
use App\Models\ActivoFijo;
use App\Models\Ubicacion;
use App\Models\Personal;
use App\Models\Trazabilidad;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\Image\Svg;

class ReasignacionController extends Controller
{
    public function index()
    {
        $reasignaciones = Reasignacion::with(['activo', 'ubicacionAnterior', 'ubicacionNueva', 'responsableAnterior', 'responsableNuevo', 'usuario'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($reasignacion) {
                return [
                    'id' => $reasignacion->id,
                    'activo' => $reasignacion->activo ? [
                        'id' => $reasignacion->activo->id,
                        'codigo' => $reasignacion->activo->codigo_inventario,
                        'descripcion' => $reasignacion->activo->nombre_activo,
                    ] : null,
                    'ubicacion_anterior' => $reasignacion->ubicacionAnterior?->nombre,
                    'ubicacion_nueva' => $reasignacion->ubicacionNueva?->nombre,
                    'responsable_anterior' => $reasignacion->responsableAnterior ? $reasignacion->responsableAnterior->nombre . ' ' . $reasignacion->responsableAnterior->apellido : null,
                    'responsable_nuevo' => $reasignacion->responsableNuevo ? $reasignacion->responsableNuevo->nombre . ' ' . $reasignacion->responsableNuevo->apellido : null,
                    'motivo' => $reasignacion->motivo,
                    'fecha' => $reasignacion->fecha_reasignacion->format('Y-m-d'),
                    'usuario' => $reasignacion->usuario->nombre . ' ' . $reasignacion->usuario->apellido,
                    'created_at' => $reasignacion->created_at->format('Y-m-d H:i'),
                ];
            });

        return Inertia::render('Reasignaciones/Index', [
            'reasignaciones' => $reasignaciones,
        ]);
    }

    public function create()
    {
        $activos = ActivoFijo::with(['ubicacion', 'responsable'])
            ->orderBy('codigo_inventario')
            ->get()
            ->map(function ($activo) {
                return [
                    'id' => $activo->id,
                    'codigo' => $activo->codigo_inventario,
                    'descripcion' => $activo->nombre_activo,
                    'ubicacion_actual_id' => $activo->ubicacion_id,
                    'ubicacion_actual' => $activo->ubicacion?->nombre,
                    'responsable_actual_id' => $activo->personal_id,
                    'responsable_actual' => $activo->responsable ? $activo->responsable->nombre . ' ' . $activo->responsable->apellido : null,
                ];
            });

        $ubicaciones = Ubicacion::orderBy('nombre')->get(['id', 'nombre']);
        $personal = Personal::orderBy('nombre')->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'nombre_completo' => $p->nombre . ' ' . $p->apellido,
            ];
        });

        return Inertia::render('Reasignaciones/Create', [
            'activos' => $activos,
            'ubicaciones' => $ubicaciones,
            'personal' => $personal,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'activo_id' => 'required|exists:activos_fijos,id',
            'ubicacion_nueva_id' => 'nullable|exists:ubicaciones,id',
            'responsable_nuevo_id' => 'nullable|exists:personal,id',
            'motivo' => 'required|string',
            'observaciones' => 'nullable|string',
            'fecha_reasignacion' => 'required|date',
        ]);

        // Get current asset values
        $activo = ActivoFijo::findOrFail($validated['activo_id']);
        
        // Create reassignment record
        $reasignacion = Reasignacion::create([
            'activo_id' => $validated['activo_id'],
            'ubicacion_anterior_id' => $activo->ubicacion_id,
            'responsable_anterior_id' => $activo->personal_id,
            'ubicacion_nueva_id' => $validated['ubicacion_nueva_id'] ?? $activo->ubicacion_id,
            'responsable_nuevo_id' => $validated['responsable_nuevo_id'] ?? $activo->personal_id,
            'motivo' => $validated['motivo'],
            'observaciones' => $validated['observaciones'],
            'fecha_reasignacion' => $validated['fecha_reasignacion'],
            'estado' => 'completada',
            'usuario_id' => auth()->id(),
        ]);

        // Update asset
        $activo->update([
            'ubicacion_id' => $validated['ubicacion_nueva_id'] ?? $activo->ubicacion_id,
            'personal_id' => $validated['responsable_nuevo_id'] ?? $activo->personal_id,
        ]);

        // Create traceability record
        Trazabilidad::create([
            'activo_id' => $activo->id,
            'tipo_movimiento' => 'reasignacion',
            'ubicacion_id' => $activo->ubicacion_id,
            'responsable_id' => $activo->personal_id,
            'area_id' => $activo->area_id,
            'observaciones' => "Reasignación: {$validated['motivo']}",
            'usuario_id' => auth()->id(),
        ]);

        return redirect()->route('reasignaciones.index')->with('success', 'Reasignación creada exitosamente');
    }

    public function show(Reasignacion $reasignacion)
    {
        $reasignacion->load(['activo', 'ubicacionAnterior', 'ubicacionNueva', 'responsableAnterior', 'responsableNuevo', 'usuario']);
        
        return Inertia::render('Reasignaciones/Show', [
            'reasignacion' => $reasignacion,
        ]);
    }

    public function destroy(Reasignacion $reasignacion)
    {
        $reasignacion->delete();
        return redirect()->route('reasignaciones.index')->with('success', 'Reasignación eliminada');
    }

    /**
     * Generar Acta de Reasignación en PDF
     */
    public function generarActaPdf(Reasignacion $reasignacion)
    {
        $reasignacion->load([
            'activo.clasificacion', 
            'ubicacionAnterior', 
            'ubicacionNueva', 
            'responsableAnterior', 
            'responsableNuevo', 
            'usuario'
        ]);

        $system = SystemSetting::first();

        // Generate QR Code
        $renderer = new Svg();
        $renderer->setHeight(120);
        $renderer->setWidth(120);
        $writer = new Writer($renderer);
        $qrData = "REASIG-{$reasignacion->id}-{$reasignacion->activo->codigo_inventario}";
        $qrSvg = $writer->writeString($qrData);
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
        
        // Convertir logo a base64 para DomPDF
        $logoPath = public_path('logo-alcaldia.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
        }
        
        $pdf = Pdf::loadView('reportes.acta-reasignacion-pdf', [
            'reasignacion' => $reasignacion,
            'system' => $system,
            'fecha_emision' => now()->format('d/m/Y'),
            'qrCode' => $qrBase64,
            'logoBase64' => $logoBase64,
        ]);

        $pdf->setPaper('letter', 'portrait');

        $codigo = $reasignacion->activo->codigo_inventario ?? 'N-A';
        $filename = "acta-reasignacion-{$codigo}-{$reasignacion->id}.pdf";
        
        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
