<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReasignacionController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('reasignaciones')
            ->leftJoin('activos_fijos', 'reasignaciones.activo_id', '=', 'activos_fijos.id')
            ->leftJoin('personal as anterior', 'reasignaciones.responsable_anterior_id', '=', 'anterior.id')
            ->leftJoin('personal as nuevo', 'reasignaciones.responsable_nuevo_id', '=', 'nuevo.id')
            ->select(
                'reasignaciones.*',
                'activos_fijos.nombre_activo',
                'activos_fijos.codigo_inventario',
                DB::raw("CONCAT(anterior.nombre, ' ', anterior.apellido) as personal_anterior"),
                DB::raw("CONCAT(nuevo.nombre, ' ', nuevo.apellido) as personal_nuevo"),
                'reasignaciones.fecha_reasignacion as fecha_asignacion' // Alias for compatibility with frontend
            );

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('activos_fijos.nombre_activo', 'like', "%{$search}%")
                    ->orWhere('activos_fijos.codigo_inventario', 'like', "%{$search}%");
            });
        }

        $reasignaciones = $query->orderBy('reasignaciones.fecha_reasignacion', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json($reasignaciones);
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'activo_id' => 'required|exists:activos_fijos,id',
                'personal_nuevo_id' => 'required|exists:personal,id',
                'area_nueva_id' => 'required|exists:areas,id',
                'motivo' => 'required|string',
                'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        }

        $asset = DB::table('activos_fijos')->where('id', $request->activo_id)->first();
        if (!$asset) {
            return response()->json(['message' => 'Activo no encontrado'], 404);
        }

        $foto_path = null;
        if ($request->hasFile('foto')) {
            $foto_path = $request->file('foto')->store('reasignaciones', 'public');
            // Ensure we don't store the /storage/ prefix, just the relative path
            $foto_path = str_replace('public/', '', $foto_path);
        }

        // Create the reassignment record
        $reasignacionId = DB::table('reasignaciones')->insertGetId([
            'activo_id' => $request->activo_id,
            'responsable_anterior_id' => $asset->personal_id,
            'responsable_nuevo_id' => $request->personal_nuevo_id,
            'area_anterior_id' => $asset->area_id, // Match new column name
            'area_nueva_id' => $request->area_nueva_id, // Match new column name
            'motivo' => $request->motivo,
            'foto_reasignacion' => $foto_path,
            'fecha_reasignacion' => now(),
            'estado' => 'PROCESADO',
            'usuario_id' => auth()->id() ?? 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update the asset with the new custodian AND new area
        DB::table('activos_fijos')->where('id', $request->activo_id)->update([
            'personal_id' => $request->personal_nuevo_id,
            'area_id' => $request->area_nueva_id,
            'updated_at' => now(),
        ]);

        return response()->json(['id' => $reasignacionId, 'message' => 'Reasignación realizada exitosamente']);
    }

    public function downloadActa($id)
    {
        $reasignacion = \App\Models\Reasignacion::with([
            'activo.clasificacion',
            'responsableAnterior',
            'responsableNuevo',
            'areaAnterior',
            'areaNueva',
            'usuario'
        ])->findOrFail($id);

        $system = \App\Models\SystemSetting::first();
        // Generate QR Code content - ONLY codigo_inventario for scanner compatibility
        $qrData = $reasignacion->activo->codigo_inventario;
        
        $qrCode = null;
        try {
            // First try modern Generator class (v4+)
            if (class_exists('SimpleSoftwareIO\QrCode\Generator')) {
                $qrGenerator = new \SimpleSoftwareIO\QrCode\Generator();
                $qrCode = 'data:image/svg+xml;base64,' . base64_encode($qrGenerator->size(100)->generate($qrData));
            } 
            // Fallback for older versions if they somehow still exist
            elseif (class_exists('SimpleSoftwareIO\QrCode\BaconQrCodeGenerator')) {
                $qrGenerator = new \SimpleSoftwareIO\QrCode\BaconQrCodeGenerator();
                $qrCode = 'data:image/svg+xml;base64,' . base64_encode($qrGenerator->size(100)->generate($qrData));
            }
        } catch (\Exception $e) {
            \Log::error('QR Generation failed: ' . $e->getMessage());
        }

        $logoBase64 = null;
        $logoPath = public_path('logo-alcaldia.png');
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reportes.acta-reasignacion-pdf', [
            'reasignacion' => $reasignacion,
            'system' => $system,
            'qrCode' => $qrCode,
            'logoBase64' => $logoBase64,
            'fecha_emision' => now()->format('d/m/Y H:i A'),
            'usuario' => auth()->user() ? (auth()->user()->full_name ?: auth()->user()->email) : 'Sistema'
        ]);

        return $pdf->stream("Acta_Reasignacion_{$reasignacion->id}.pdf");
    }
}
