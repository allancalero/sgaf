<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Models\Cheque;
use App\Models\FuenteFinanciamiento;
use App\Models\Clasificacion;
use App\Models\TipoActivo;
use App\Models\Responsable;
use App\Models\Area;
use Inertia\Inertia;
use Inertia\Response;

class ActivosFijoController extends Controller
{
    public function index(): Response
    {
        // Proveedores
        $proveedores = Proveedor::orderBy('id')->get(['id', 'nombre', 'ruc', 'direccion', 'telefono', 'email', 'created_at']);

        // Cheques (sin relación de área ya que la columna no existe en la BD)
        $cheques = Cheque::orderBy('id')->get([
            'id',
            'numero_cheque',
            'banco',
            'monto_total',
            'estado',
            'created_at'
        ]);

        // Fuentes de Financiamiento
        $fuentes = FuenteFinanciamiento::orderBy('id')->get(['id', 'nombre', 'estado', 'created_at']);

        // Clasificaciones (sin columna estado)
        $clasificaciones = Clasificacion::orderBy('id')->get(['id', 'nombre', 'created_at']);

        // Tipos de Activos (sin columna estado)
        $tipos = TipoActivo::orderBy('id')->get(['id', 'nombre', 'created_at']);

        // Responsables (Asignación) - solo columnas que existen
        $responsables = Responsable::orderBy('id')->get(['id', 'nombre', 'created_at']);

        // Catálogos necesarios
        $areas = Area::orderBy('nombre')->get(['id', 'nombre']);

        return Inertia::render('ActivosFijo/Index', [
            'proveedores' => $proveedores,
            'cheques' => $cheques,
            'fuentes' => $fuentes,
            'clasificaciones' => $clasificaciones,
            'tipos' => $tipos,
            'responsables' => $responsables,
            'areas' => $areas,
        ]);
    }
}
