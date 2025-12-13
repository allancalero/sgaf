<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

// Crear usuario
$user = User::create([
    'nombre' => 'SGAF',
    'apellido' => 'Admin',
    'email' => 'sgaf@example.com',
    'password' => Hash::make('1111'),
    'rol' => 'ADMIN',
    'estado' => 'ACTIVO',
]);

// Asignar rol admin
$admin = Role::where('name', 'admin')->first();
if ($admin) {
    $user->assignRole($admin);
}

echo "Usuario creado exitosamente:\n";
echo "Email: " . $user->email . "\n";
echo "Rol: " . $user->rol . "\n";
