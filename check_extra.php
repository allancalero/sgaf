<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== TABLAS CARGOS Y UBICACIONES ===\n";
try {
    echo "Cargos:\n";
    foreach(DB::select('SHOW COLUMNS FROM cargos') as $col) { 
        echo str_pad($col->Field, 20) . " | Null: " . str_pad($col->Null, 4) . "\n"; 
    }
} catch (\Exception $e) { echo "Tabla cargos error: " . $e->getMessage() . "\n"; }

try {
    echo "\nUbicaciones:\n";
    foreach(DB::select('SHOW COLUMNS FROM ubicaciones') as $col) { 
        echo str_pad($col->Field, 20) . " | Null: " . str_pad($col->Null, 4) . "\n"; 
    }
} catch (\Exception $e) { echo "Tabla ubicaciones error: " . $e->getMessage() . "\n"; }
