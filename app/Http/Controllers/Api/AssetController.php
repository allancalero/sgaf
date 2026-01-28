<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivoFijo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AssetController extends Controller
{
    public function quickSearch(Request $request)
    {
        $search = $request->query('search', '');
        
        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $assets = DB::table('activos_fijos')
            ->leftJoin('areas', 'activos_fijos.area_id', '=', 'areas.id')
            ->leftJoin('personal', 'activos_fijos.personal_id', '=', 'personal.id')
            ->leftJoin('fuentes_financiamiento', 'activos_fijos.fuente_financiamiento_id', '=', 'fuentes_financiamiento.id')
            ->select(
                'activos_fijos.id', 
                'activos_fijos.nombre_activo', 
                'activos_fijos.codigo_inventario', 
                'activos_fijos.marca', 
                'activos_fijos.modelo',
                'activos_fijos.serie',
                'activos_fijos.estado',
                'activos_fijos.fecha_adquisicion',
                'activos_fijos.precio_adquisicion as valor_adquisicion',
                'areas.nombre as area_nombre',
                'personal.nombre as personal_nombre',
                'personal.apellido as personal_apellido',
                'fuentes_financiamiento.nombre as fuente_nombre'
            )
            ->where(function($query) use ($search) {
                $query->where('activos_fijos.nombre_activo', 'like', "%{$search}%")
                      ->orWhere('activos_fijos.codigo_inventario', 'like', "%{$search}%")
                      ->orWhere('activos_fijos.serie', 'like', "%{$search}%")
                      ->orWhere('areas.nombre', 'like', "%{$search}%")
                      ->orWhere('personal.nombre', 'like', "%{$search}%")
                      ->orWhere('personal.apellido', 'like', "%{$search}%");
            })
            ->limit(10)
            ->get();

        return response()->json($assets);
    }

    /**
     * Verify asset by codigo_inventario (for QR scanning)
     */
    public function verifyByCodigo($codigo)
    {
        $asset = ActivoFijo::with([
            'area',
            'ubicacion',
            'clasificacion',
            'personal',
            'fuenteFinanciamiento'
        ])->where('codigo_inventario', $codigo)->first();

        if (!$asset) {
            return response()->json(['message' => 'Activo no encontrado'], 404);
        }

        return response()->json($asset);
    }

    public function getNextCode($clasificacionId)
    {
        $clasificacion = DB::table('clasificaciones')->where('id', $clasificacionId)->first();
        
        if (!$clasificacion) {
            return response()->json(['code' => '']);
        }

        // Extract accounting segments (e.g., "123 004 007" -> "123-004-007")
        $codigo = $clasificacion->codigo ?? '';
        $parts = explode(' ', $codigo);
        $segments = array_slice($parts, 0, 3);
        $basePrefix = implode('-', $segments);

        // Generate sequential number based on existing assets in this classification
        $count = DB::table('activos_fijos')->where('clasificacion_id', $clasificacionId)->count();
        $sequence = str_pad($count + 1, 6, '0', STR_PAD_LEFT);

        // Format: [Accounting-Segments]-[Sequence]
        // Example: 123-004-007-000001
        $code = $basePrefix . '-' . $sequence;
        
        return response()->json(['code' => $code]);
    }

    public function index(Request $request)
    {
        $query = ActivoFijo::with([
            'area', 
            'personal', 
            'clasificacion', 
            'ubicacion', 
            'fuenteFinanciamiento'
        ]);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre_activo', 'like', "%{$search}%")
                    ->orWhere('codigo_inventario', 'like', "%{$search}%")
                    ->orWhere('marca', 'like', "%{$search}%")
                    ->orWhere('serie', 'like', "%{$search}%")
                    ->orWhereHas('clasificacion', function($q2) use ($search) {
                        $q2->where('nombre', 'like', "%{$search}%");
                    });
            });
        }

        // Filtering
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->get('area_id')) {
            $query->where('area_id', $request->area_id);
        }
        if ($request->get('personal_id')) {
            $query->where('personal_id', $request->personal_id);
        }
        if ($request->get('clasificacion_id')) {
            $query->where('clasificacion_id', $request->clasificacion_id);
        }

        $assets = $query->orderBy('created_at', 'desc')
            ->paginate((int) $request->get('per_page', 15));

        return \App\Http\Resources\AssetResource::collection($assets);
    }

    public function show($id)
    {
        $asset = ActivoFijo::conRelaciones()
            ->where('id', $id)
            ->first();

        if (!$asset) {
            return response()->json(['message' => 'Activo no encontrado'], 404);
        }

        return response()->json($asset);
    }

    public function store(Request $request)
    {
        // Require create permission
        if (!auth()->user()->can('activos.create')) {
             return response()->json(['message' => 'No autorizado para crear activos'], 403);
        }

        $validated = $request->validate([
            'codigo_inventario' => [
                'required',
                'unique:activos_fijos,codigo_inventario',
                'regex:/^[\d]+(-[\d]+)*$/'
            ],
            'nombre_activo' => 'required|string|max:255',
            'marca' => 'nullable|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:50',
            'serie' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'nullable|numeric',
            'precio_adquisicion' => 'nullable|numeric',
            'fecha_adquisicion' => 'nullable|date',
            'numero_factura' => 'nullable|string|max:100',
            'estado' => 'required|in:BUENO,REGULAR,MALO',
            'area_id' => 'required|exists:areas,id',
            'ubicacion_id' => 'required|exists:ubicaciones,id',
            'clasificacion_id' => 'required|exists:clasificaciones,id',
            'tipo_activo_id' => 'nullable|exists:tipos_activos,id',
            'fuente_financiamiento_id' => 'required|exists:fuentes_financiamiento,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'personal_id' => 'nullable|exists:personal,id',
            'cheque_id' => 'nullable|exists:cheques,id',
            'monto_cheque_utilizado' => 'nullable|numeric',
            'custom_fields' => 'nullable',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            // Depreciation fields
            'vida_util_anos' => 'nullable|integer|min:0',
            'valor_residual' => 'nullable|numeric',
            'metodo_depreciacion' => 'nullable|string',
        ]);

        // Handle custom_fields if sent as JSON string
        if (isset($validated['custom_fields']) && is_string($validated['custom_fields'])) {
            $validated['custom_fields'] = json_decode($validated['custom_fields'], true);
        }

        // Handle photo upload
        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('activos', 'public');
            $validated['foto'] = $path;
        }

        $asset = ActivoFijo::create($validated);

        return response()->json(['id' => $asset->id, 'message' => 'Activo creado correctamente'], 201);
    }

    public function update(Request $request, $id)
    {
        // Strict Edit Permission Check
        if (!auth()->user()->can('activos.edit')) {
             return response()->json(['message' => 'No autorizado para editar activos. Contacte al administrador.'], 403);
        }

        $asset = ActivoFijo::findOrFail($id);

        $validated = $request->validate([
            'nombre_activo' => 'sometimes|required|string|max:255',
            'marca' => 'sometimes|nullable|string|max:255',
            'modelo' => 'sometimes|nullable|string|max:255',
            'color' => 'sometimes|nullable|string|max:50',
            'serie' => 'sometimes|nullable|string|max:255',
            'descripcion' => 'sometimes|nullable|string',
            'cantidad' => 'sometimes|required|integer|min:1',
            'precio_unitario' => 'sometimes|nullable|numeric',
            'precio_adquisicion' => 'sometimes|nullable|numeric',
            'fecha_adquisicion' => 'sometimes|nullable|date',
            'numero_factura' => 'sometimes|nullable|string|max:100',
            'estado' => 'sometimes|required|in:BUENO,REGULAR,MALO,DESUSO,BAJA',
            'area_id' => 'sometimes|required|exists:areas,id',
            'ubicacion_id' => 'sometimes|required|exists:ubicaciones,id',
            'clasificacion_id' => 'sometimes|required|exists:clasificaciones,id',
            'tipo_activo_id' => 'sometimes|nullable|exists:tipos_activos,id',
            'proveedor_id' => 'sometimes|nullable|exists:proveedores,id',
            'personal_id' => 'sometimes|nullable|exists:personal,id',
            'cheque_id' => 'sometimes|nullable|exists:cheques,id',
            'monto_cheque_utilizado' => 'sometimes|nullable|numeric',
            'custom_fields' => 'sometimes|nullable',
            'foto' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            // Depreciation fields
            'vida_util_anos' => 'sometimes|nullable|integer|min:0',
            'valor_residual' => 'sometimes|nullable|numeric',
            'metodo_depreciacion' => 'sometimes|nullable|string',
        ]);

        // Handle custom_fields if sent as JSON string
        if (isset($validated['custom_fields']) && is_string($validated['custom_fields'])) {
            $validated['custom_fields'] = json_decode($validated['custom_fields'], true);
        }

        // Handle photo upload
        if ($request->hasFile('foto')) {
            // Delete old photo if exists
            if ($asset->foto && Storage::disk('public')->exists($asset->foto)) {
                Storage::disk('public')->delete($asset->foto);
            }
            $path = $request->file('foto')->store('activos', 'public');
            $validated['foto'] = $path;
        }

        $asset->update($validated);

        return response()->json(['message' => 'Activo actualizado correctamente']);
    }

    public function destroy($id)
    {
        // Strict Delete Permission Check
        if (!auth()->user()->can('activos.delete')) {
             return response()->json(['message' => 'No autorizado para eliminar activos.'], 403);
        }

        $asset = ActivoFijo::findOrFail($id);
        $asset->delete();
        // DB::table('activos_fijos')->where('id', $id)->delete();
        return response()->json(['message' => 'Activo eliminado correctamente']);
    }

    public function clasificaciones()
    {
        return response()->json(DB::table('clasificaciones')->orderBy('prefijo')->get());
    }

    public function fuentes()
    {
        return response()->json(DB::table('fuentes_financiamiento')->get());
    }

    public function tipos()
    {
        return response()->json(DB::table('tipos_activos')->get());
    }

    public function proveedores()
    {
        return response()->json(DB::table('proveedores')->get());
    }

    public function cheques()
    {
        return response()->json(DB::table('cheques')->get());
    }

    public function downloadActa($id)
    {
        $asset = ActivoFijo::with([
            'clasificacion',
            'personal',
            'area',
            'ubicacion',
            'tipoActivo',
            'fuenteFinanciamiento',
            'proveedor'
        ])->findOrFail($id);
        
        $system = \App\Models\SystemSetting::first();
        
        // Generate QR Code content - ONLY codigo_inventario for scanner compatibility
        $qrData = $asset->codigo_inventario;
        
        $qrCode = null;
        try {
            if (class_exists('SimpleSoftwareIO\QrCode\Generator')) {
                $qrGenerator = new \SimpleSoftwareIO\QrCode\Generator();
                $qrCode = 'data:image/svg+xml;base64,' . base64_encode($qrGenerator->size(100)->generate($qrData));
            } elseif (class_exists('SimpleSoftwareIO\QrCode\BaconQrCodeGenerator')) {
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

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reportes.acta-asignacion-pdf', [
            'activo' => $asset,
            'system' => $system,
            'qrCode' => $qrCode,
            'logoBase64' => $logoBase64,
            'fecha_emision' => now()->format('d/m/Y H:i A'),
            'usuario' => auth()->user() ? (auth()->user()->full_name ?: auth()->user()->email) : 'Sistema'
        ]);

        return $pdf->stream("Acta_Asignacion_{$asset->codigo_inventario}.pdf");
    }

    public function generateQr($id)
    {
        $asset = DB::table('activos_fijos')->where('id', $id)->first();

        if (!$asset) {
            return response()->json(['message' => 'Activo no encontrado'], 404);
        }

        try {
            // Check for modern Generator class
            if (class_exists('SimpleSoftwareIO\QrCode\Generator')) {
                $qrGenerator = new \SimpleSoftwareIO\QrCode\Generator();
            } else {
                // Fallback
                $qrGenerator = new \SimpleSoftwareIO\QrCode\BaconQrCodeGenerator();
            }

            // Generate SVG content directly
            $qrContent = $qrGenerator->size(200)->format('svg')->generate($asset->codigo_inventario);

            return response($qrContent)->header('Content-Type', 'image/svg+xml');

        } catch (\Exception $e) {
            \Log::error('QR Generation failed: ' . $e->getMessage());
            return response()->json(['message' => 'Error generando QR'], 500);
        }
    }
}
