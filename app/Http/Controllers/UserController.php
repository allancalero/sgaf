<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $users = User::query()
            ->when($search, fn($q) => $q->where('nombre', 'like', "%$search%")
                ->orWhere('apellido', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%"))
            ->with('roles')
            ->orderBy('id', 'desc')
            ->paginate(15);
        return Inertia::render('Usuarios/Index', [
            'users' => $users,
            'search' => $search,
        ]);
    }

    public function create()
    {
        $roles = Role::all();
        return Inertia::render('Usuarios/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'roles' => 'array',
        ]);
        $user = User::create([
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        if (!empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }
        return redirect()->route('usuarios.index')->with('success', 'Usuario creado');
    }

    public function edit(User $usuario)
    {
        $roles = Role::all();
        $usuario->load('roles');
        return Inertia::render('Usuarios/Edit', [
            'user' => $usuario,
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $usuario)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $usuario->id,
            'password' => 'nullable|string|min:6|confirmed',
            'roles' => 'array',
        ]);
        $usuario->nombre = $data['nombre'];
        $usuario->apellido = $data['apellido'];
        $usuario->email = $data['email'];
        if (!empty($data['password'])) {
            $usuario->password = Hash::make($data['password']);
        }
        $usuario->save();
        $usuario->syncRoles($data['roles'] ?? []);
        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado');
    }

    public function destroy(User $usuario)
    {
        $usuario->delete();
        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado');
    }
}
