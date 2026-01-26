<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Análisis de clasificaciones en activos_fijos ===\n\n";

// Get classification IDs used in activos_fijos
$usedIds = DB::table('activos_fijos')
    ->selectRaw('clasificacion_id, COUNT(*) as total')
    ->groupBy('clasificacion_id')
    ->orderBy('clasificacion_id')
    ->get();

echo "IDs de clasificación en uso en activos:\n";
foreach ($usedIds as $row) {
    $id = $row->clasificacion_id ?? 'NULL';
    echo "  ID {$id}: {$row->total} activos\n";
}

echo "\n=== Clasificaciones válidas actuales ===\n";
$validClasificaciones = DB::table('clasificaciones')->get();
foreach ($validClasificaciones as $c) {
    echo "  ID {$c->id}: {$c->codigo} - {$c->nombre}\n";
}
$validIds = $validClasificaciones->pluck('id')->toArray();

echo "\n=== Activos con IDs inválidos ===\n";
$invalidCount = DB::table('activos_fijos')
    ->whereNotIn('clasificacion_id', $validIds)
    ->whereNotNull('clasificacion_id')
    ->count();
echo "Total activos con clasificacion_id inválido: {$invalidCount}\n";

// Show breakdown by invalid ID
if ($invalidCount > 0) {
    echo "\nDesglose por ID inválido:\n";
    $invalidBreakdown = DB::table('activos_fijos')
        ->selectRaw('clasificacion_id, COUNT(*) as total')
        ->whereNotIn('clasificacion_id', $validIds)
        ->whereNotNull('clasificacion_id')
        ->groupBy('clasificacion_id')
        ->orderBy('total', 'desc')
        ->get();
    foreach ($invalidBreakdown as $row) {
        echo "  ID {$row->clasificacion_id}: {$row->total} activos\n";
    }
}

// Total assets with NULL clasificacion_id
$nullCount = DB::table('activos_fijos')->whereNull('clasificacion_id')->count();
echo "\nTotal activos sin clasificación (NULL): {$nullCount}\n";
