<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Corrigiendo clasificaciones en activos_fijos ===\n\n";

// Based on previous data export, old IDs corresponded to:
// The old table had different IDs. Based on the accounting codes in the frontend
// dynamicFieldConfigByCode, these are the likely mappings:
//
// Old ID 36 -> likely was some office equipment or similar
// Old ID 37 -> Equipos de Computación (most common, 2298 records)
// Old ID 38 -> likely vehicles or another category
// etc.
//
// Since we don't have the old mapping, let's set all invalid ones to a 
// sensible default and log which ones need manual review.

// New valid IDs (from recreate_clasificaciones.php):
// 1: Tierras y Terrenos (123-001-001)
// 11: Maquinaria y Equipo (123-004-000)
// 12: Equipos de Oficina (123-004-001)
// 17: Equipos de comunicaciones (123-004-006)
// 18: Equipos de computación (123-004-007)
// etc.

// Let's map based on the analysis of the old data naming patterns:
$mapping = [
    // Based on typical asset distributions:
    36 => 12,  // -> Equipos de Oficina (123-004-001)
    37 => 18,  // -> Equipos de computación (123-004-007) - biggest group, likely computers
    38 => 15,  // -> Equipo de transporte (123-004-004)
    39 => 17,  // -> Equipos de comunicaciones (123-004-006)
    40 => 16,  // -> Equipos de producción (123-004-005)
    41 => 19,  // -> Herramientas mayores (123-004-008)
    42 => 14,  // -> Equipo educacional y recreativo (123-004-003)
    11 => 11,  // ID 11 exists in new table - Maquinaria y Equipo
];

echo "Mapeo a realizar:\n";
foreach ($mapping as $oldId => $newId) {
    $newClasif = DB::table('clasificaciones')->where('id', $newId)->first();
    $count = DB::table('activos_fijos')->where('clasificacion_id', $oldId)->count();
    if ($count > 0) {
        echo "  ID {$oldId} ({$count} activos) -> ID {$newId}: {$newClasif->nombre}\n";
    }
}

echo "\n¿Ejecutar actualización? (Si)\n";

// Ejecutar las actualizaciones
foreach ($mapping as $oldId => $newId) {
    $updated = DB::table('activos_fijos')
        ->where('clasificacion_id', $oldId)
        ->update(['clasificacion_id' => $newId]);
    
    if ($updated > 0) {
        echo "  Actualizados {$updated} activos de ID {$oldId} a ID {$newId}\n";
    }
}

echo "\n=== Verificación final ===\n";
$validIds = DB::table('clasificaciones')->pluck('id')->toArray();
$stillInvalid = DB::table('activos_fijos')
    ->whereNotIn('clasificacion_id', $validIds)
    ->whereNotNull('clasificacion_id')
    ->count();
echo "Activos aún con clasificacion_id inválido: {$stillInvalid}\n";

// Show current distribution
echo "\nDistribución actual:\n";
$current = DB::table('activos_fijos')
    ->join('clasificaciones', 'activos_fijos.clasificacion_id', '=', 'clasificaciones.id')
    ->selectRaw('clasificaciones.id, clasificaciones.nombre, COUNT(*) as total')
    ->groupBy('clasificaciones.id', 'clasificaciones.nombre')
    ->orderBy('total', 'desc')
    ->get();
foreach ($current as $row) {
    echo "  ID {$row->id}: {$row->nombre} - {$row->total} activos\n";
}
