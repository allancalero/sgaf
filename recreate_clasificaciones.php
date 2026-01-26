<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Disable foreign key checks temporarily
DB::statement('SET FOREIGN_KEY_CHECKS=0');

// Truncate the table
DB::table('clasificaciones')->truncate();

echo "Tabla clasificaciones truncada.\n";

// Insert the official classifications from the accounting document
$clasificaciones = [
    // 123-001: TIERRAS Y TERRENOS
    ['codigo' => '123-001-001-000-000-000', 'nombre' => 'Tierras y Terrenos', 'prefijo' => '01'],
    
    // 123-002: EDIFICACIONES E INSTALACIONES
    ['codigo' => '123-002-000-000-000-000', 'nombre' => 'Edificaciones e Instalaciones', 'prefijo' => '02'],
    ['codigo' => '123-002-002-000-000-000', 'nombre' => 'Edificios no Residenciales e Instalaciones', 'prefijo' => '02-A'],
    ['codigo' => '123-002-003-000-000-000', 'nombre' => 'Obras e Infraestructura Agropecuarias', 'prefijo' => '02-B'],
    ['codigo' => '123-002-004-000-000-000', 'nombre' => 'Carreteras, Calles, Puentes y Aeropuertos', 'prefijo' => '02-C'],
    ['codigo' => '123-002-005-000-000-000', 'nombre' => 'Obra e Infraestructura Hidráulica', 'prefijo' => '02-D'],
    ['codigo' => '123-002-006-000-000-000', 'nombre' => 'Obra e Infraestructura Urbanísticas', 'prefijo' => '02-E'],
    ['codigo' => '123-002-007-000-000-000', 'nombre' => 'Construcción y Edificaciones de Otras Infraestructuras', 'prefijo' => '02-F'],
    ['codigo' => '123-002-008-000-000-000', 'nombre' => 'Bienes de Defensa', 'prefijo' => '02-G'],
    ['codigo' => '123-002-009-000-000-000', 'nombre' => 'Bienes de Seguridad', 'prefijo' => '02-H'],
    
    // 123-004: MAQUINARIA Y EQUIPO
    ['codigo' => '123-004-000-000-000-000', 'nombre' => 'Maquinaria y Equipo', 'prefijo' => '04'],
    ['codigo' => '123-004-001-000-000-000', 'nombre' => 'Equipos de Oficina', 'prefijo' => '04-A'],
    ['codigo' => '123-004-002-000-000-000', 'nombre' => 'Equipo médicos, Sanitarios y de laboratorio', 'prefijo' => '04-B'],
    ['codigo' => '123-004-003-000-000-000', 'nombre' => 'Equipo educacional y recreativo', 'prefijo' => '04-C'],
    ['codigo' => '123-004-004-000-000-000', 'nombre' => 'Equipo de transporte, tracción y elevación', 'prefijo' => '04-D'],
    ['codigo' => '123-004-005-000-000-000', 'nombre' => 'Equipos de producción', 'prefijo' => '04-E'],
    ['codigo' => '123-004-006-000-000-000', 'nombre' => 'Equipos de comunicaciones y señalización', 'prefijo' => '04-F'],
    ['codigo' => '123-004-007-000-000-000', 'nombre' => 'Equipos de computación', 'prefijo' => '04-G'],
    ['codigo' => '123-004-008-000-000-000', 'nombre' => 'Herramientas mayores', 'prefijo' => '04-H'],
    ['codigo' => '123-004-009-000-000-000', 'nombre' => 'Equipos de Seguridad y militar', 'prefijo' => '04-I'],
    ['codigo' => '123-004-009-001-000-000', 'nombre' => 'Equipo de Seguridad', 'prefijo' => '04-I-1'],
    ['codigo' => '123-004-010-000-000-000', 'nombre' => 'Maquinaria y Equipo Institucionales en Tránsito', 'prefijo' => '04-J'],
    
    // 123-005: ACTIVOS BIOLÓGICOS
    ['codigo' => '123-005-000-000-000-000', 'nombre' => 'Activos Biológicos', 'prefijo' => '05'],
    ['codigo' => '123-005-003-000-000-000', 'nombre' => 'Activos Biológicos Institucionales en Tránsito', 'prefijo' => '05-A'],
    
    // 123-006: OTROS ACTIVOS FIJOS TANGIBLES
    ['codigo' => '123-006-000-000-000-000', 'nombre' => 'Otros Activos Fijos Tangibles', 'prefijo' => '06'],
    ['codigo' => '123-006-001-000-000-000', 'nombre' => 'Libros, Revistas y Otros Coleccionable', 'prefijo' => '06-A'],
    ['codigo' => '123-006-002-000-000-000', 'nombre' => 'Obras de Arte', 'prefijo' => '06-B'],
    ['codigo' => '123-006-090-000-000-000', 'nombre' => 'Otros Activos Fijos Tangibles', 'prefijo' => '06-C'],
    ['codigo' => '123-006-091-000-000-000', 'nombre' => 'Otros Activos Tangibles Institucionales en Tránsito', 'prefijo' => '06-D'],
];

foreach ($clasificaciones as $c) {
    DB::table('clasificaciones')->insert([
        'codigo' => $c['codigo'],
        'nombre' => $c['nombre'],
        'prefijo' => $c['prefijo'],
        'created_at' => now(),
        'updated_at' => now()
    ]);
    echo "Insertado: {$c['prefijo']} - {$c['nombre']}\n";
}

// Re-enable foreign key checks
DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo "\n¡Clasificaciones insertadas correctamente!\n";
echo "Total: " . count($clasificaciones) . " registros\n";
