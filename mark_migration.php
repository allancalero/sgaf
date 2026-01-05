<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Marcar la migración de foto como ejecutada
$batch = DB::table('migrations')->max('batch') + 1;
DB::table('migrations')->insert([
    'migration' => '2025_12_14_120116_add_foto_to_activos_fijos_table',
    'batch' => $batch
]);

echo "✓ Migración de foto marcada como ejecutada" . PHP_EOL;
echo "Ahora puedes ejecutar: php artisan migrate" . PHP_EOL;
