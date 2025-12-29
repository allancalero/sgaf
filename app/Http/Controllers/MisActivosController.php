<?php

namespace App\Http\Controllers;

use App\Models\ActivoFijo;
use App\Models\Asignacion;
use App\Models\Personal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MisActivosController extends Controller
{
    /**
     * Mostrar los activos asignados al usuario actual
     */
    public function index()
    {
        $user = Auth::user();
        
        // Obtener el personal asociado al usuario (buscar por email)
        $personal = Personal::where('email', $user->email)->first();
        
        if (!$personal) {
            return Inertia::render('MisActivos/Index', [
                'activos' => [],
                'personalArea' => [],
                'miArea' => 'Sin área asignada',
            ]);
        }
        
        // Obtener activos asignados al usuario actual
        $activos = ActivoFijo::with(['clasificacion', 'area', 'ubicacionRelacion'])
            ->where('responsable_actual_id', $personal->id)
            ->orderBy('codigo')
            ->get();
        
        // Obtener personal de la misma área para delegar
        $personalMismaArea = Personal::where('area_id', $personal->area_id)
            ->where('id', '!=', $personal->id) // Excluir al usuario actual
            ->where('estado', 'ACTIVO')
            ->orderBy('nombre_completo')
            ->get(['id', 'nombre_completo', 'cargo']);
        
        return Inertia::render('MisActivos/Index', [
            'activos' => $activos,
            'personalArea' => $personalMismaArea,
            'miArea' => $personal->area->nombre ?? 'Sin área',
        ]);
    }
    
    /**
     * Delegar un activo a otro personal del área
     */
    public function delegar(Request $request)
    {
        $request->validate([
            'activo_id' => 'required|exists:activos_fijos,id',
            'nuevo_responsable_id' => 'required|exists:personal,id',
            'observaciones' => 'nullable|string|max:500',
        ]);
        
        $user = Auth::user();
        $personal = Personal::where('email', $user->email)->first();
        
        if (!$personal) {
            return back()->withErrors(['error' => 'No tienes un perfil de personal asociado.']);
        }
        
        $activo = ActivoFijo::findOrFail($request->activo_id);
        
        // Verificar que el activo esté asignado al usuario actual
        if ($activo->responsable_actual_id !== $personal->id) {
            return back()->withErrors(['error' => 'Este activo no está asignado a ti.']);
        }
        
        $nuevoResponsable = Personal::findOrFail($request->nuevo_responsable_id);
        
        // Verificar que el nuevo responsable sea de la misma área
        if ($nuevoResponsable->area_id !== $personal->area_id) {
            return back()->withErrors(['error' => 'Solo puedes delegar a personas de tu misma área.']);
        }
        
        // Crear registro de asignación
        Asignacion::create([
            'activo_fijo_id' => $activo->id,
            'area_id' => $nuevoResponsable->area_id,
            'ubicacion_id' => $activo->ubicacion_actual_id,
            'responsable_id' => $nuevoResponsable->id,
            'tipo_movimiento' => 'DELEGACIÓN',
            'fecha_asignacion' => now(),
            'observaciones' => $request->observaciones ?? "Delegado por {$personal->nombre_completo}",
            'estado' => 'ACTIVO',
        ]);
        
        // Actualizar el responsable del activo
        $activo->update([
            'responsable_actual_id' => $nuevoResponsable->id,
        ]);
        
        return back()->with('success', 'Activo delegado exitosamente.');
    }
}
