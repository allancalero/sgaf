<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Verificar columnas en la tabla settings
$columns = DB::select("SHOW COLUMNS FROM settings");

echo "=== COLUMNAS EN LA TABLA SETTINGS ===" . PHP_EOL . PHP_EOL;
foreach ($columns as $col) {
    echo "- {$col->Field} ({$col->Type})" . PHP_EOL;
}
