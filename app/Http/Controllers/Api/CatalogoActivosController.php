<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogoActivosController extends Controller
{
    // ==================== PROVEEDORES ====================
    public function proveedores()
    {
        $proveedores = DB::table('proveedores')
            ->orderBy('nombre')
            ->get();

        return response()->json($proveedores);
    }

    public function storeProveedor(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'ruc' => 'nullable|string|max:100',
                'direccion' => 'nullable|string|max:255',
                'telefono' => 'nullable|string|max:100',
                'email' => 'nullable|email|max:255'
            ]);

            $id = DB::table('proveedores')->insertGetId([
                'nombre' => $validated['nombre'],
                'ruc' => $validated['ruc'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'email' => $validated['email'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['id' => $id, 'message' => 'Proveedor creado exitosamente'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear el proveedor: ' . $e->getMessage()], 500);
        }
    }

    public function updateProveedor(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'ruc' => 'nullable|string|max:100',
                'direccion' => 'nullable|string|max:255',
                'telefono' => 'nullable|string|max:100',
                'email' => 'nullable|email|max:255'
            ]);

            DB::table('proveedores')->where('id', $id)->update([
                'nombre' => $validated['nombre'],
                'ruc' => $validated['ruc'] ?? null,
                'direccion' => $validated['direccion'] ?? null,
                'telefono' => $validated['telefono'] ?? null,
                'email' => $validated['email'] ?? null,
                'updated_at' => now(),
            ]);

            return response()->json(['message' => 'Proveedor actualizado exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar el proveedor: ' . $e->getMessage()], 500);
        }
    }

    public function deleteProveedor($id)
    {
        try {
            DB::table('proveedores')->where('id', $id)->delete();
            return response()->json(['message' => 'Proveedor eliminado exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al eliminar el proveedor: ' . $e->getMessage()], 500);
        }
    }

    // ==================== CLASIFICACIONES ====================
    public function clasificaciones()
    {
        $clasificaciones = DB::table('clasificaciones')
            ->orderBy('nombre')
            ->get();

        return response()->json($clasificaciones);
    }

    public function storeClasificacion(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255|unique:clasificaciones,nombre'
            ]);

            $id = DB::table('clasificaciones')->insertGetId([
                'nombre' => $validated['nombre'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['id' => $id, 'message' => 'Clasificación creada exitosamente'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear la clasificación: ' . $e->getMessage()], 500);
        }
    }

    public function updateClasificacion(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255|unique:clasificaciones,nombre,' . $id
            ]);

            DB::table('clasificaciones')->where('id', $id)->update([
                'nombre' => $validated['nombre'],
                'updated_at' => now(),
            ]);

            return response()->json(['message' => 'Clasificación actualizada exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar la clasificación: ' . $e->getMessage()], 500);
        }
    }

    public function deleteClasificacion($id)
    {
        try {
            DB::table('clasificaciones')->where('id', $id)->delete();
            return response()->json(['message' => 'Clasificación eliminada exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al eliminar la clasificación: ' . $e->getMessage()], 500);
        }
    }

    // ==================== FUENTES FINANCIAMIENTO ====================
    public function fuentes()
    {
        $fuentes = DB::table('fuentes_financiamiento')
            ->orderBy('nombre')
            ->get();

        return response()->json($fuentes);
    }

    public function storeFuente(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'estado' => 'required|in:ACTIVO,INACTIVO'
            ]);

            $id = DB::table('fuentes_financiamiento')->insertGetId([
                'nombre' => $validated['nombre'],
                'estado' => $validated['estado'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['id' => $id, 'message' => 'Fuente de financiamiento creada exitosamente'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear la fuente: ' . $e->getMessage()], 500);
        }
    }

    public function updateFuente(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'estado' => 'required|in:ACTIVO,INACTIVO'
            ]);

            DB::table('fuentes_financiamiento')->where('id', $id)->update([
                'nombre' => $validated['nombre'],
                'estado' => $validated['estado'],
                'updated_at' => now(),
            ]);

            return response()->json(['message' => 'Fuente de financiamiento actualizada exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar la fuente: ' . $e->getMessage()], 500);
        }
    }

    public function deleteFuente($id)
    {
        try {
            DB::table('fuentes_financiamiento')->where('id', $id)->delete();
            return response()->json(['message' => 'Fuente de financiamiento eliminada exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al eliminar la fuente: ' . $e->getMessage()], 500);
        }
    }

    // ==================== TIPOS DE ACTIVOS ====================
    public function tipos()
    {
        $tipos = DB::table('tipos_activos')
            ->leftJoin('clasificaciones', 'tipos_activos.clasificacion_id', '=', 'clasificaciones.id')
            ->select('tipos_activos.*', 'clasificaciones.nombre as clasificacion_nombre')
            ->orderBy('tipos_activos.nombre')
            ->get();

        return response()->json($tipos);
    }

    public function storeTipo(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255|unique:tipos_activos,nombre',
                'clasificacion_id' => 'required|exists:clasificaciones,id'
            ]);

            $id = DB::table('tipos_activos')->insertGetId([
                'nombre' => $validated['nombre'],
                'clasificacion_id' => $validated['clasificacion_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['id' => $id, 'message' => 'Tipo de activo creado exitosamente'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear el tipo: ' . $e->getMessage()], 500);
        }
    }

    public function updateTipo(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255|unique:tipos_activos,nombre,' . $id,
                'clasificacion_id' => 'required|exists:clasificaciones,id'
            ]);

            DB::table('tipos_activos')->where('id', $id)->update([
                'nombre' => $validated['nombre'],
                'clasificacion_id' => $validated['clasificacion_id'],
                'updated_at' => now(),
            ]);

            return response()->json(['message' => 'Tipo de activo actualizado exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar el tipo: ' . $e->getMessage()], 500);
        }
    }

    public function deleteTipo($id)
    {
        try {
            DB::table('tipos_activos')->where('id', $id)->delete();
            return response()->json(['message' => 'Tipo de activo eliminado exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al eliminar el tipo: ' . $e->getMessage()], 500);
        }
    }

    // ==================== CHEQUES ====================
    public function cheques()
    {
        $cheques = DB::table('cheques')
            ->leftJoin('areas', 'cheques.area_solicitante_id', '=', 'areas.id')
            ->leftJoin('users', 'cheques.usuario_emisor_id', '=', 'users.id')
            ->select(
                'cheques.*',
                'areas.nombre as area_nombre',
                DB::raw("CONCAT(users.nombre, ' ', users.apellido) as usuario_nombre")
            )
            ->orderBy('cheques.fecha_emision', 'desc')
            ->get();

        return response()->json($cheques);
    }

    public function storeCheque(Request $request)
    {
        try {
            $validated = $request->validate([
                'numero_cheque' => 'required|string|max:50|unique:cheques,numero_cheque',
                'banco' => 'required|string|max:100',
                'cuenta_bancaria' => 'required|string|max:50',
                'monto_total' => 'required|numeric|min:0',
                'fecha_emision' => 'required|date',
                'fecha_vencimiento' => 'nullable|date',
                'beneficiario' => 'required|string|max:255',
                'beneficiario_ruc' => 'nullable|string|max:100',
                'descripcion' => 'nullable|string',
                'estado' => 'required|in:EMITIDO,COBRADO,ANULADO',
                'area_solicitante_id' => 'required|exists:areas,id',
                'usuario_emisor_id' => 'required|exists:users,id'
            ]);

            $validated['saldo_disponible'] = $validated['monto_total'];

            $id = DB::table('cheques')->insertGetId(array_merge($validated, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            return response()->json(['id' => $id, 'message' => 'Cheque creado exitosamente'], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear el cheque: ' . $e->getMessage()], 500);
        }
    }

    public function updateCheque(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'numero_cheque' => 'required|string|max:50|unique:cheques,numero_cheque,' . $id,
                'banco' => 'required|string|max:100',
                'cuenta_bancaria' => 'required|string|max:50',
                'monto_total' => 'required|numeric|min:0',
                'fecha_emision' => 'required|date',
                'fecha_vencimiento' => 'nullable|date',
                'beneficiario' => 'required|string|max:255',
                'beneficiario_ruc' => 'nullable|string|max:100',
                'descripcion' => 'nullable|string',
                'estado' => 'required|in:EMITIDO,COBRADO,ANULADO',
                'area_solicitante_id' => 'required|exists:areas,id',
                'usuario_emisor_id' => 'required|exists:users,id'
            ]);

            DB::table('cheques')->where('id', $id)->update(array_merge($validated, [
                'updated_at' => now(),
            ]));

            return response()->json(['message' => 'Cheque actualizado exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar el cheque: ' . $e->getMessage()], 500);
        }
    }

    public function deleteCheque($id)
    {
        try {
            DB::table('cheques')->where('id', $id)->delete();
            return response()->json(['message' => 'Cheque eliminado exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al eliminar el cheque: ' . $e->getMessage()], 500);
        }
    }
}
