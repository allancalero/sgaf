<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Ubicacion;
use Inertia\Inertia;
use Inertia\Response;

class UbicacionesController extends Controller
{
    public function index(): Response
    {
        $areas = Area::orderBy('id')->get(['id', 'nombre', 'estado', 'created_at']);
        $ubicaciones = Ubicacion::orderBy('id')->get(['id', 'nombre', 'direccion', 'estado', 'created_at']);

        return Inertia::render('UbicacionesVista/Index', [
            'areas' => $areas,
            'ubicaciones' => $ubicaciones,
        ]);
    }
}
