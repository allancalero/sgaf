<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = new App\Models\User();
$user->nombre = 'SGAF';
$user->apellido = 'Admin';
$user->email = 'sgaf@example.com';
$user->password = Hash::make('7777');
$user->created_at = now();
$user->updated_at = now();
$user->save();

echo "✅ Usuario creado exitosamente!\n";
echo "Nombre: SGAF Admin\n";
echo "Email: sgaf@example.com\n";
echo "Password: 7777\n";
