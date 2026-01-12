<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UbicacionController extends Controller
{
    public function areas(Request $request)
    {
        $areas = DB::table('areas')
            ->leftJoin('ubicaciones', 'areas.ubicacion_id', '=', 'ubicaciones.id')
            ->select('areas.*', 'ubicaciones.nombre as ubicacion_nombre')
            ->orderBy('areas.nombre')
            ->paginate($request->get('per_page', 10));

        return response()->json($areas);
    }

    public function ubicaciones(Request $request)
    {
        $ubicaciones = DB::table('ubicaciones')
            ->orderBy('nombre')
            ->paginate($request->get('per_page', 10));

        return response()->json($ubicaciones);
    }
    public function allAreas()
    {
        $areas = DB::table('areas')
            ->leftJoin('ubicaciones', 'areas.ubicacion_id', '=', 'ubicaciones.id')
            ->select('areas.*', 'ubicaciones.nombre as ubicacion_nombre')
            ->orderBy('areas.nombre')
            ->get();
        return response()->json($areas);
    }

    public function allUbicaciones()
    {
        $ubicaciones = DB::table('ubicaciones')
            ->orderBy('nombre')
            ->get();
        return response()->json($ubicaciones);
    }

    public function storeArea(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255|unique:areas,nombre',
                'ubicacion_id' => 'nullable|exists:ubicaciones,id',
                'estado' => 'required|in:ACTIVO,INACTIVO'
            ]);

            $id = DB::table('areas')->insertGetId([
                'nombre' => $validated['nombre'],
                'ubicacion_id' => $validated['ubicacion_id'],
                'estado' => $validated['estado'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['id' => $id, 'message' => 'Área creada exitosamente'], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear el área: ' . $e->getMessage()], 500);
        }
    }

    public function updateArea(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255|unique:areas,nombre,' . $id,
                'ubicacion_id' => 'nullable|exists:ubicaciones,id',
                'estado' => 'required|in:ACTIVO,INACTIVO'
            ]);

            DB::table('areas')->where('id', $id)->update([
                'nombre' => $validated['nombre'],
                'ubicacion_id' => $validated['ubicacion_id'],
                'estado' => $validated['estado'],
                'updated_at' => now(),
            ]);

            return response()->json(['message' => 'Área actualizada exitosamente']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar el área: ' . $e->getMessage()], 500);
        }
    }

    public function deleteArea($id)
    {
        try {
            // Optional: Check if used in other tables (e.g. personal, activos) before deleting?
            // For now, standard delete.
            DB::table('areas')->where('id', $id)->delete();
            return response()->json(['message' => 'Área eliminada exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al eliminar el área: ' . $e->getMessage()], 500);
        }
    }

    public function storeUbicacion(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255|unique:ubicaciones,nombre',
                'direccion' => 'nullable|string|max:255',
                'estado' => 'required|in:ACTIVO,INACTIVO'
            ]);

            $id = DB::table('ubicaciones')->insertGetId([
                'nombre' => $validated['nombre'],
                'direccion' => $validated['direccion'],
                'estado' => $validated['estado'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['id' => $id, 'message' => 'Ubicación creada exitosamente'], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear la ubicación: ' . $e->getMessage()], 500);
        }
    }

    public function updateUbicacion(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255|unique:ubicaciones,nombre,' . $id,
                'direccion' => 'nullable|string|max:255',
                'estado' => 'required|in:ACTIVO,INACTIVO'
            ]);

            DB::table('ubicaciones')->where('id', $id)->update([
                'nombre' => $validated['nombre'],
                'direccion' => $validated['direccion'],
                'estado' => $validated['estado'],
                'updated_at' => now(),
            ]);

            return response()->json(['message' => 'Ubicación actualizada exitosamente']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Error de validación', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al actualizar la ubicación: ' . $e->getMessage()], 500);
        }
    }

    public function deleteUbicacion($id)
    {
        try {
            DB::table('ubicaciones')->where('id', $id)->delete();
            return response()->json(['message' => 'Ubicación eliminada exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al eliminar la ubicación: ' . $e->getMessage()], 500);
        }
    }
}
