<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonalController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('personal')
            ->leftJoin('cargos', 'personal.cargo_id', '=', 'cargos.id')
            ->leftJoin('areas', 'personal.area_id', '=', 'areas.id')
            ->select('personal.*', 'cargos.nombre as cargo', 'areas.nombre as area');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('personal.nombre', 'like', "%{$search}%")
                    ->orWhere('personal.apellido', 'like', "%{$search}%")
                    ->orWhere('personal.email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('area_id')) {
            $query->where('personal.area_id', $request->area_id);
        }

        $personal = $query->orderBy('personal.nombre')->paginate($request->get('per_page', 10));

        return response()->json($personal);
    }

    public function all(Request $request)
    {
        $query = DB::table('personal')
            ->where('estado', 'activo');

        if ($request->has('area_id') && $request->area_id != '') {
            $query->where('area_id', $request->area_id);
        }

        $personal = $query->orderBy('nombre')->get();
        return response()->json($personal);
    }

    public function cargos(Request $request)
    {
        $cargos = DB::table('cargos')->orderBy('nombre')->paginate($request->get('per_page', 10));
        return response()->json($cargos);
    }

    public function allCargos()
    {
        $cargos = DB::table('cargos')->where('estado', 'ACTIVO')->orderBy('nombre')->get();
        return response()->json($cargos);
    }

    public function store(Request $request)
    {
        $data = [
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'cargo_id' => $request->cargo_id && $request->cargo_id !== 'undefined' ? $request->cargo_id : null,
            'area_id' => $request->area_id && $request->area_id !== 'undefined' ? $request->area_id : null,
            'ubicacion_id' => $request->ubicacion_id,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'numero_empleado' => $request->numero_empleado,
            'numero_cedula' => $request->numero_cedula,
            'sexo' => $request->sexo,
            'direccion' => $request->direccion,
            'estado' => $request->estado ?? 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('personal_photos', 'public');
            $data['foto'] = '/storage/' . $path;
        }

        $id = DB::table('personal')->insertGetId($data);

        return response()->json(['id' => $id, 'message' => 'Personal creado exitosamente']);
    }

    public function update(Request $request, $id)
    {
        $data = [
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'cargo_id' => $request->cargo_id && $request->cargo_id !== 'undefined' ? $request->cargo_id : null,
            'area_id' => $request->area_id && $request->area_id !== 'undefined' ? $request->area_id : null,
            'ubicacion_id' => $request->ubicacion_id,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'numero_empleado' => $request->numero_empleado,
            'numero_cedula' => $request->numero_cedula,
            'sexo' => $request->sexo,
            'direccion' => $request->direccion,
            'estado' => $request->estado,
            'updated_at' => now(),
        ];

        if ($request->hasFile('foto')) {
            // Delete old photo if exists
            $old = DB::table('personal')->where('id', $id)->value('foto');
            if ($old) {
                $oldPath = str_replace('/storage/', '', $old);
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('foto')->store('personal_photos', 'public');
            $data['foto'] = '/storage/' . $path;
        }

        DB::table('personal')->where('id', $id)->update($data);

        return response()->json(['message' => 'Personal actualizado exitosamente']);
    }

    public function destroy($id)
    {
        DB::table('personal')->where('id', $id)->delete();
        return response()->json(['message' => 'Personal eliminado exitosamente']);
    }

    public function storeCargo(Request $request)
    {
        $id = DB::table('cargos')->insertGetId([
            'nombre' => $request->nombre,
            'estado' => $request->estado ?? 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['id' => $id, 'message' => 'Cargo creado exitosamente']);
    }

    public function updateCargo(Request $request, $id)
    {
        DB::table('cargos')->where('id', $id)->update([
            'nombre' => $request->nombre,
            'estado' => $request->estado,
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Cargo actualizado exitosamente']);
    }

    public function destroyCargo($id)
    {
        DB::table('cargos')->where('id', $id)->delete();
        return response()->json(['message' => 'Cargo eliminado exitosamente']);
    }
}
