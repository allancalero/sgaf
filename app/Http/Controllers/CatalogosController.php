<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CatalogosController extends Controller
{
    public function index(): Response
    {
        $areas = DB::table('areas')->select('id', 'nombre', 'estado')->orderBy('id')->get();
        $cargos = DB::table('cargos')->select('id', 'nombre', 'estado')->orderBy('id')->get();
        $ubicaciones = DB::table('ubicaciones')->select('id', 'nombre', 'estado')->orderBy('id')->get();
        $clasificaciones = DB::table('clasificaciones')->select('id', 'nombre')->orderBy('id')->get();
        $fuentes = DB::table('fuentes_financiamiento')->select('id', 'nombre', 'estado')->orderBy('id')->get();
        $tipos = DB::table('tipos_activos')
            ->join('clasificaciones', 'tipos_activos.clasificacion_id', '=', 'clasificaciones.id')
            ->select('tipos_activos.id', 'tipos_activos.nombre', 'clasificaciones.nombre as clasificacion')
            ->orderBy('tipos_activos.id')
            ->get();
        $proveedores = DB::table('proveedores')->select('id', 'nombre', 'ruc', 'telefono', 'email')->orderBy('id')->get();
        $personal = DB::table('personal')
            ->leftJoin('cargos', 'personal.cargo_id', '=', 'cargos.id')
            ->leftJoin('areas', 'personal.area_id', '=', 'areas.id')
            ->leftJoin('ubicaciones', 'personal.ubicacion_id', '=', 'ubicaciones.id')
            ->select(
                'personal.id',
                'personal.nombre',
                'personal.apellido',
                'cargos.nombre as cargo',
                'areas.nombre as area',
                'ubicaciones.nombre as ubicacion',
                'personal.email'
            )
            ->orderBy('personal.id')
            ->get();
        $responsables = DB::table('responsables')
            ->leftJoin('cargos', 'responsables.id_cargo', '=', 'cargos.id')
            ->leftJoin('areas', 'responsables.area_id', '=', 'areas.id')
            ->select(
                'responsables.id',
                'responsables.nombre',
                'cargos.nombre as cargo',
                'areas.nombre as area',
                'responsables.estado'
            )
            ->orderBy('responsables.id')
            ->get();

        return Inertia::render('Catalogos/Index', [
            'areas' => $areas,
            'cargos' => $cargos,
            'ubicaciones' => $ubicaciones,
            'clasificaciones' => $clasificaciones,
            'fuentes' => $fuentes,
            'tipos' => $tipos,
            'proveedores' => $proveedores,
            'personal' => $personal,
            'responsables' => $responsables,
        ]);
    }
}
