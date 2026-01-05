<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== COLUMNAS DE ACTIVOS_FIJOS ===\n";
foreach(DB::select('SHOW COLUMNS FROM activos_fijos') as $col) { 
    echo str_pad($col->Field, 25) . " | Null: " . str_pad($col->Null, 4) . " | Default: " . ($col->Default ?? 'NULL') . "\n"; 
}
