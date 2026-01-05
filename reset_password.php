<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Buscar el usuario admin@example.com
$user = DB::table('users')->where('email', 'admin@example.com')->first();

if (!$user) {
    echo "❌ Usuario admin@example.com NO encontrado.\n";
    echo "Los usuarios disponibles son:\n";
    $users = DB::table('users')->select('email')->get();
    foreach ($users as $u) {
        echo "  - {$u->email}\n";
    }
    exit(1);
}

// Resetear contraseña a "admin"
$newPassword = Hash::make('admin');
DB::table('users')->where('email', 'admin@example.com')->update([
    'password' => $newPassword,
    'updated_at' => now()
]);

echo "✅ Contraseña actualizada exitosamente!\n";
echo "\nCredenciales:\n";
echo "Email: admin@example.com\n";
echo "Password: admin\n";
