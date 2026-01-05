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
                    'fecha' => $reasignacion->fecha_reasignacion->format('Y-m-d H:i'),
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
        $activos = ActivoFijo::with(['ubicacion', 'responsable', 'area'])
            ->orderBy('codigo_inventario')
            ->get()
            ->map(function ($activo) {
                return [
                    'id' => $activo->id,
                    'codigo' => $activo->codigo_inventario,
                    'descripcion' => $activo->nombre_activo,
                    'area_id' => $activo->area_id,
                    'ubicacion_actual_id' => $activo->ubicacion_id,
                    'ubicacion_actual' => $activo->ubicacion?->nombre,
                    'responsable_actual_id' => $activo->personal_id,
                    'responsable_actual' => $activo->responsable ? $activo->responsable->nombre . ' ' . $activo->responsable->apellido : null,
                    'foto' => $activo->foto,
                ];
            });

        $ubicaciones = Ubicacion::orderBy('nombre')->get(['id', 'nombre']);
        $personal = Personal::orderBy('nombre')->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'nombre_completo' => $p->nombre . ' ' . $p->apellido,
                'area_id' => $p->area_id,
            ];
        });

        $areas = \App\Models\Area::orderBy('nombre')->get(['id', 'nombre']);

        return Inertia::render('Reasignaciones/Create', [
            'activos' => $activos,
            'ubicaciones' => $ubicaciones,
            'personal' => $personal,
            'areas' => $areas,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'activo_id' => 'required|exists:activos_fijos,id',
            'area_nueva_id' => 'nullable|exists:areas,id',
            'ubicacion_nueva_id' => 'nullable|exists:ubicaciones,id',
            'responsable_nuevo_id' => 'nullable|exists:personal,id',
            'motivo' => 'required|string',
            'observaciones' => 'nullable|string',
            'fecha_reasignacion' => 'required|date',
            'foto_reasignacion' => 'nullable|image|max:2048',
        ]);

        // Get current asset values
        $activo = ActivoFijo::findOrFail($validated['activo_id']);
        
        // Handle photo upload
        $fotoPath = null;
        if ($request->hasFile('foto_reasignacion')) {
            $fotoPath = $request->file('foto_reasignacion')->store('reasignaciones', 'public');
        }
        
        // Create reassignment record
        $reasignacion = Reasignacion::create([
            'activo_id' => $validated['activo_id'],
            'ubicacion_anterior_id' => $activo->ubicacion_id,
            'responsable_anterior_id' => $activo->personal_id,
            'ubicacion_nueva_id' => $validated['ubicacion_nueva_id'] ?? $activo->ubicacion_id,
            'responsable_nuevo_id' => $validated['responsable_nuevo_id'] ?? $activo->personal_id,
            'motivo' => $validated['motivo'],
            'observaciones' => $validated['observaciones'],
            'foto_reasignacion' => $fotoPath ? '/storage/' . $fotoPath : null,
            'fecha_reasignacion' => now(),
            'estado' => 'completada',
            'usuario_id' => auth()->id(),
        ]);

        // Update asset
        $activo->update([
            'area_id' => $validated['area_nueva_id'] ?? $activo->area_id,
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
            'reasignacion' => [
                'id' => $reasignacion->id,
                'activo' => $reasignacion->activo,
                'ubicacion_anterior' => $reasignacion->ubicacionAnterior,
                'ubicacion_nueva' => $reasignacion->ubicacionNueva,
                'responsable_anterior' => $reasignacion->responsableAnterior,
                'responsable_nuevo' => $reasignacion->responsableNuevo,
                'motivo' => $reasignacion->motivo,
                'observaciones' => $reasignacion->observaciones,
                'fecha_reasignacion' => $reasignacion->fecha_reasignacion?->format('Y-m-d'),
                'usuario' => $reasignacion->usuario,
                'created_at' => $reasignacion->created_at?->format('Y-m-d H:i:s'),
                'foto_reasignacion' => $reasignacion->foto_reasignacion,
            ],
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

        // Generate QR Code with unique data
        $renderer = new Svg();
        $renderer->setHeight(120);
        $renderer->setWidth(120);
        $writer = new Writer($renderer);
        
        // Generar código único: ID reasignación + código activo + fecha reasignación + timestamp generación
        $qrData = sprintf(
            "REASIG|ID:%d|COD:%s|FECHA:%s|GEN:%s",
            $reasignacion->id,
            $reasignacion->activo->codigo_inventario ?? 'N-A',
            $reasignacion->fecha_reasignacion->format('Y-m-d'),
            now()->format('YmdHis')
        );
        $qrSvg = $writer->writeString($qrData);
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrSvg);
        
        // Convertir logo a base64 para DomPDF
        $logoBase64 = '';
        if ($system && $system->logo_url) {
            // Extraer el path del storage desde la URL
            $logoPath = str_replace('/storage', 'storage/app/public', parse_url($system->logo_url, PHP_URL_PATH));
            $fullPath = base_path($logoPath);
            
            if (file_exists($fullPath)) {
                $logoData = file_get_contents($fullPath);
                $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
            }
        }
        
        $pdf = Pdf::loadView('reportes.acta-reasignacion-pdf', [
            'reasignacion' => $reasignacion,
            'system' => $system,
            'fecha_emision' => now()->format('d/m/Y'),
            'qrCode' => $qrBase64,
            'logoBase64' => $logoBase64,
            'usuario' => auth()->user() ? (auth()->user()->full_name ?: auth()->user()->email) : 'Sistema',
        ]);

        $pdf->setPaper('letter', 'portrait');

        $codigo = $reasignacion->activo->codigo_inventario ?? 'N-A';
        $filename = "acta-reasignacion-{$codigo}-{$reasignacion->id}.pdf";
        
        return response($pdf->output(), 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }
}
