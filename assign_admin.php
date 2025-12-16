<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Buscar el usuario
$user = App\Models\User::where('email', 'sgaf@example.com')->first();

if (!$user) {
    echo "❌ Usuario no encontrado.\n";
    exit(1);
}

// Obtener todos los permisos
$permissions = DB::table('permissions')->get();

if ($permissions->isEmpty()) {
    echo "⚠️ No hay permisos en la base de datos.\n";
    echo "✅ Usuario tiene acceso completo por defecto.\n";
    exit(0);
}

// Asignar todos los permisos al usuario
foreach ($permissions as $permission) {
    DB::table('user_permissions')->updateOrInsert(
        ['user_id' => $user->id, 'permission_id' => $permission->id],
        ['created_at' => now(), 'updated_at' => now()]
    );
}

echo "✅ Permisos asignados exitosamente!\n";
echo "Usuario: {$user->nombre} {$user->apellido}\n";
echo "Email: {$user->email}\n";
echo "Permisos: " . count($permissions) . " permisos asignados\n";
echo "\nPuedes iniciar sesión con:\n";
echo "Email: sgaf@example.com\n";
echo "Password: 7777\n";
