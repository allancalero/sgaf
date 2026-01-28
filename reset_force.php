<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('email', 'acalero@tipitapa.gob.ni')->first();
if ($user) {
    $user->password = \Illuminate\Support\Facades\Hash::make('Tipitapa2026*');
    $user->must_change_password = true;
    $user->save();
    echo "RESET_SUCCESS";
} else {
    echo "USER_NOT_FOUND";
}
