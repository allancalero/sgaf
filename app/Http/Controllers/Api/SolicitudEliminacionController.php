<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SolicitudEliminacion;
use App\Models\ActivoFijo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SolicitudEliminacionController extends Controller
{
    /**
     * List requests (For Admin)
     */
    public function index(Request $request)
    {
        // Only admin sees all, or filter by pending
        $query = SolicitudEliminacion::with(['activo', 'solicitante', 'procesador'])
            ->orderBy('created_at', 'desc');

        if ($request->has('estado')) {
            $query->where('estado', $request->estado);
        }

        return response()->json($query->paginate(20));
    }

    /**
     * Create a new request (For Editor)
     */
    public function store(Request $request)
    {
        $request->validate([
            'activo_id' => 'required|exists:activos_fijos,id',
            'motivo' => 'required|string|min:10'
        ]);

        // Check if already pending
        $exists = SolicitudEliminacion::where('activo_id', $request->activo_id)
            ->where('estado', 'PENDIENTE')
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Ya existe una solicitud pendiente para este activo.'], 409);
        }

        $solicitud = SolicitudEliminacion::create([
            'activo_id' => $request->activo_id,
            'solicitante_id' => auth()->id(),
            'motivo' => $request->motivo,
            'estado' => 'PENDIENTE'
        ]);

        return response()->json(['message' => 'Solicitud enviada correctamente', 'data' => $solicitud], 201);
    }

    /**
     * Approve Request (Executes Deletion)
     */
    public function aprobar(Request $request, $id)
    {
        $solicitud = SolicitudEliminacion::findOrFail($id);
        
        if ($solicitud->estado !== 'PENDIENTE') {
            return response()->json(['message' => 'Esta solicitud ya fue procesada.'], 400);
        }

        $activo = ActivoFijo::find($solicitud->activo_id);
        if (!$activo) {
            return response()->json(['message' => 'El activo ya no existe.'], 404);
        }

        // 1. Snapshot / Update Request Log
        // We store the asset code in the admin note automatically to preserve context
        $autoNote = "Activo Eliminado: " . $activo->codigo_inventario . " - " . $activo->nombre_activo . ". " . ($request->nota_admin ?? '');
        
        $solicitud->update([
            'estado' => 'APROBADO',
            'procesado_por' => auth()->id(),
            'nota_admin' => $autoNote
        ]);

        // 2. Delete the asset
        // This will trigger Set Null on the solicitud's activo_id column due to our migration fix
        $activo->delete();

        return response()->json(['message' => 'Activo eliminado permanentemente y solicitud registrada.']);
    }

    /**
     * Reject Request
     */
    public function rechazar(Request $request, $id)
    {
        $solicitud = SolicitudEliminacion::findOrFail($id);
        
        if ($solicitud->estado !== 'PENDIENTE') {
            return response()->json(['message' => 'Esta solicitud ya fue procesada.'], 400);
        }

        $solicitud->update([
            'estado' => 'RECHAZADO',
            'procesado_por' => auth()->id(),
            'nota_admin' => $request->nota_admin
        ]);

        return response()->json(['message' => 'Solicitud rechazada.']);
    }
}
