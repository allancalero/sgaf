<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Actualizando prefijos con numeración secuencial...\n\n";

// Get all classifications ordered by codigo (to maintain logical order)
$clasificaciones = DB::table('clasificaciones')
    ->orderBy('codigo')
    ->get();

$numero = 1;
foreach ($clasificaciones as $c) {
    $prefijo = str_pad($numero, 2, '0', STR_PAD_LEFT); // 01, 02, 03...
    
    DB::table('clasificaciones')
        ->where('id', $c->id)
        ->update(['prefijo' => $prefijo]);
    
    echo "  {$prefijo} - {$c->nombre}\n";
    $numero++;
}

echo "\n¡Prefijos actualizados con numeración secuencial!\n";
echo "Total: " . ($numero - 1) . " clasificaciones\n";
