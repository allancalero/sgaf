<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Add prefijo column if it doesn't exist
if (!Schema::hasColumn('clasificaciones', 'prefijo')) {
    Schema::table('clasificaciones', function ($table) {
        $table->string('prefijo', 10)->nullable()->after('id');
    });
    echo "Columna 'prefijo' agregada.\n";
}

// Define prefijos based on the accounting codes
$prefijos = [
    '123-001-001' => '01',      // Tierras y Terrenos
    '123-002-000' => '02',      // Edificaciones e Instalaciones
    '123-002-002' => '02-A',    // Edificios no Residenciales e Instalaciones
    '123-002-003' => '02-B',    // Obras e Infraestructura Agropecuarias
    '123-002-004' => '02-C',    // Carreteras, Calles, Puentes y Aeropuertos
    '123-002-005' => '02-D',    // Obra e Infraestructura Hidráulica
    '123-002-006' => '02-E',    // Obra e Infraestructura Urbanísticas
    '123-002-007' => '02-F',    // Construcción y Edificaciones de Otras Infraestructuras
    '123-002-008' => '02-G',    // Bienes de Defensa
    '123-002-009' => '02-H',    // Bienes de Seguridad
    '123-004-000' => '04',      // Maquinaria y Equipo
    '123-004-001' => '04-A',    // Equipos de Oficina
    '123-004-002' => '04-B',    // Equipo médicos, Sanitarios y de laboratorio
    '123-004-003' => '04-C',    // Equipo educacional y recreativo
    '123-004-004' => '04-D',    // Equipo de transporte, tracción y elevación
    '123-004-005' => '04-E',    // Equipos de producción
    '123-004-006' => '04-F',    // Equipos de comunicaciones y señalización
    '123-004-007' => '04-G',    // Equipos de computación
    '123-004-008' => '04-H',    // Herramientas mayores
    '123-004-009' => '04-I',    // Equipos de Seguridad y militar
    '123-004-009-001' => '04-I-1', // Equipo de Seguridad
    '123-004-010' => '04-J',    // Maquinaria y Equipo Institucionales en Tránsito
    '123-005-000' => '05',      // Activos Biológicos
    '123-005-003' => '05-A',    // Activos Biológicos Institucionales en Tránsito
    '123-006-000' => '06',      // Otros Activos Fijos Tangibles
    '123-006-001' => '06-A',    // Libros, Revistas y Otros Coleccionable
    '123-006-002' => '06-B',    // Obras de Arte
    '123-006-090' => '06-C',    // Otros Activos Fijos Tangibles
    '123-006-091' => '06-D',    // Otros Activos Tangibles Institucionales en Tránsito
];

echo "\nActualizando prefijos...\n";

$clasificaciones = DB::table('clasificaciones')->get();

foreach ($clasificaciones as $c) {
    // Extract first 3 segments (or 4 for special cases like 123-004-009-001)
    $parts = explode('-', $c->codigo);
    $code3 = implode('-', array_slice($parts, 0, 3));
    $code4 = implode('-', array_slice($parts, 0, 4));
    
    $prefijo = $prefijos[$code4] ?? $prefijos[$code3] ?? null;
    
    if ($prefijo) {
        DB::table('clasificaciones')
            ->where('id', $c->id)
            ->update(['prefijo' => $prefijo]);
        echo "  ID {$c->id}: {$c->nombre} -> prefijo '{$prefijo}'\n";
    } else {
        echo "  ADVERTENCIA: No se encontró prefijo para código {$c->codigo}\n";
    }
}

echo "\n¡Prefijos actualizados!\n";

// Verify
echo "\nVerificación:\n";
$result = DB::table('clasificaciones')->orderBy('prefijo')->get();
foreach ($result as $r) {
    echo "  {$r->prefijo} - {$r->nombre}\n";
}
