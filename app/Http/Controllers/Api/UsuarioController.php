<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\User::query()
            ->select('id', 'nombre', 'apellido', 'email', 'rol', 'estado', 'created_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('apellido', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $usuarios = $query->orderBy('nombre')->paginate($request->get('per_page', 15));

        return response()->json($usuarios);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'apellido' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required',
            'rol' => 'required'
        ]);

        $user = \App\Models\User::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => $request->rol,
            'estado' => $request->estado ?? 'ACTIVO'
        ]);

        $this->assignSpatieRole($user, $request->rol);

        return response()->json(['id' => $user->id, 'message' => 'Usuario creado exitosamente con permisos']);
    }

    public function update(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);

        $data = [
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'email' => $request->email,
            'rol' => $request->rol,
            'estado' => $request->estado,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Update Spatie Role
        if ($request->filled('rol')) {
            $this->assignSpatieRole($user, $request->rol);
        }

        return response()->json(['message' => 'Usuario actualizado exitosamente']);
    }

    public function destroy($id)
    {
        \App\Models\User::findOrFail($id)->delete();
        return response()->json(['message' => 'Usuario eliminado exitosamente']);
    }

    public function resetPassword($id)
    {
        $user = \App\Models\User::findOrFail($id);
        $newPassword = Str::random(8);
        
        $user->update([
            'password' => Hash::make($newPassword),
            'must_change_password' => true
        ]);

        return response()->json([
            'message' => 'Contraseña restablecida exitosamente',
            'new_password' => $newPassword
        ]);
    }

    /**
     * Maps the legacy string role to the correct Spatie Role
     */
    private function assignSpatieRole($user, $roleString)
    {
        $map = [
            'ADMIN' => 'admin',
            'OPERADOR' => 'editor',
            'CONSULTA' => 'consulta',
            'FULLACCESS' => 'fullaccess'
        ];

        $spatieRole = $map[$roleString] ?? 'consulta';

        // Sync for both guards to ensure consistent permissions
        $user->syncRoles([]); // Clear first? No, syncRoles is guard-specific.
        
        foreach (['web', 'api_jwt'] as $guard) {
            $role = \Spatie\Permission\Models\Role::where('name', $spatieRole)
                ->where('guard_name', $guard)
                ->first();

            if ($role) {
                // Detach existing roles for this guard to avoid duplicates/conflicts
                $user->roles()->where('guard_name', $guard)->detach();
                // Assign the specific role model
                $user->assignRole($role);
            }
        }
    }
}
