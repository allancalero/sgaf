<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$reasignacion = App\Models\Reasignacion::with(['activo', 'ubicacionAnterior', 'ubicacionNueva', 'responsableAnterior', 'responsableNuevo', 'usuario'])->find(1);

if (!$reasignacion) {
    echo "No se encontró la reasignación ID 1\n";
    exit;
}

echo "=== REASIGNACIÓN ID 1 ===\n\n";
echo "ID: " . $reasignacion->id . "\n";
echo "Activo ID: " . $reasignacion->activo_id . "\n";
echo "Activo: " . ($reasignacion->activo ? $reasignacion->activo->codigo_inventario . " - " . $reasignacion->activo->nombre_activo : "NULL") . "\n\n";

echo "Ubicación Anterior ID: " . $reasignacion->ubicacion_anterior_id . "\n";
echo "Ubicación Anterior: " . ($reasignacion->ubicacionAnterior ? $reasignacion->ubicacionAnterior->nombre : "NULL") . "\n\n";

echo "Ubicación Nueva ID: " . $reasignacion->ubicacion_nueva_id . "\n";
echo "Ubicación Nueva: " . ($reasignacion->ubicacionNueva ? $reasignacion->ubicacionNueva->nombre : "NULL") . "\n\n";

echo "Responsable Anterior ID: " . $reasignacion->responsable_anterior_id . "\n";
echo "Responsable Anterior: " . ($reasignacion->responsableAnterior ? $reasignacion->responsableAnterior->nombre . " " . $reasignacion->responsableAnterior->apellido : "NULL") . "\n\n";

echo "Responsable Nuevo ID: " . $reasignacion->responsable_nuevo_id . "\n";
echo "Responsable Nuevo: " . ($reasignacion->responsableNuevo ? $reasignacion->responsableNuevo->nombre . " " . $reasignacion->responsableNuevo->apellido : "NULL") . "\n\n";

echo "Motivo: " . $reasignacion->motivo . "\n";
echo "Observaciones: " . $reasignacion->observaciones . "\n";
echo "Fecha Reasignación: " . $reasignacion->fecha_reasignacion . "\n";
echo "Usuario ID: " . $reasignacion->usuario_id . "\n";
echo "Usuario: " . ($reasignacion->usuario ? $reasignacion->usuario->nombre . " " . $reasignacion->usuario->apellido : "NULL") . "\n";
echo "Created At: " . $reasignacion->created_at . "\n";
