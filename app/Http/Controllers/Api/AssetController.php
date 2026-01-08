<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('activos_fijos')
            ->leftJoin('areas', 'activos_fijos.area_id', '=', 'areas.id')
            ->leftJoin('personal', 'activos_fijos.personal_id', '=', 'personal.id')
            ->select(
                'activos_fijos.*',
                'areas.nombre as area_nombre',
                'personal.nombre as personal_nombre',
                'personal.apellido as personal_apellido'
            );

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('activos_fijos.nombre_activo', 'like', "%{$search}%")
                    ->orWhere('activos_fijos.codigo_inventario', 'like', "%{$search}%")
                    ->orWhere('activos_fijos.marca', 'like', "%{$search}%")
                    ->orWhere('activos_fijos.serie', 'like', "%{$search}%");
            });
        }

        // Filtering by state
        if ($request->filled('estado')) {
            $query->where('activos_fijos.estado', $request->estado);
        }

        $assets = $query->orderBy('activos_fijos.created_at', 'desc')
            ->paginate($request->get('per_page', 10));

        // Format for JSON Response to match TypeScript interface
        $assets->getCollection()->transform(function ($asset) {
            return [
                'id' => $asset->id,
                'codigo_inventario' => $asset->codigo_inventario,
                'nombre_activo' => $asset->nombre_activo,
                'marca' => $asset->marca,
                'modelo' => $asset->modelo,
                'serie' => $asset->serie,
                'estado' => $asset->estado,
                'precio_adquisicion' => (float) $asset->precio_adquisicion,
                'fecha_adquisicion' => $asset->fecha_adquisicion,
                'area' => $asset->area_nombre ? ['nombre' => $asset->area_nombre] : null,
                'personal' => $asset->personal_nombre ? [
                    'nombre' => $asset->personal_nombre,
                    'apellido' => $asset->personal_apellido
                ] : null,
            ];
        });

        return response()->json($assets);
    }

    public function show($id)
    {
        $asset = DB::table('activos_fijos')
            ->leftJoin('areas', 'activos_fijos.area_id', '=', 'areas.id')
            ->leftJoin('personal', 'activos_fijos.personal_id', '=', 'personal.id')
            ->select(
                'activos_fijos.*',
                'areas.nombre as area_nombre',
                'personal.nombre as personal_nombre',
                'personal.apellido as personal_apellido'
            )
            ->where('activos_fijos.id', $id)
            ->first();

        if (!$asset) {
            return response()->json(['message' => 'Activo no encontrado'], 404);
        }

        return response()->json($asset);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo_inventario' => 'required|unique:activos_fijos,codigo_inventario',
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
        ]);

        $id = DB::table('activos_fijos')->insertGetId(array_merge($validated, [
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        return response()->json(['id' => $id, 'message' => 'Activo creado correctamente'], 201);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
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
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'personal_id' => 'nullable|exists:personal,id',
            'cheque_id' => 'nullable|exists:cheques,id',
            'monto_cheque_utilizado' => 'nullable|numeric',
        ]);

        DB::table('activos_fijos')->where('id', $id)->update(array_merge($validated, [
            'updated_at' => now(),
        ]));

        return response()->json(['message' => 'Activo actualizado correctamente']);
    }

    public function destroy($id)
    {
        DB::table('activos_fijos')->where('id', $id)->delete();
        return response()->json(['message' => 'Activo eliminado correctamente']);
    }

    public function clasificaciones()
    {
        return response()->json(DB::table('clasificaciones')->get());
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
}
