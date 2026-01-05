<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClasificacionesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $data = [
            ['codigo' => '123 002 002 000 000 000', 'nombre' => 'Edificios no Residenciales e Instalaciones'],
            ['codigo' => '123 002 003 000 000 000', 'nombre' => 'Obras e Infraestructura Agropecuarias'],
            ['codigo' => '123 002 004 000 000 000', 'nombre' => 'Carreteras, Calles, Puentes y Aeropuertos'],
            ['codigo' => '123 002 005 000 000 000', 'nombre' => 'Obra e Infraestructura Hidráulica'],
            ['codigo' => '123 002 006 000 000 000', 'nombre' => 'Obra e Infraestructura Urbanística'],
            ['codigo' => '123 002 007 000 000 000', 'nombre' => 'Construcción y Edificaciones de Otras Infraestructuras'],
            ['codigo' => '123 002 008 000 000 000', 'nombre' => 'Bienes de Defensa'],
            ['codigo' => '123 002 009 000 000 000', 'nombre' => 'Bienes de Seguridad'],
            ['codigo' => '123 004 000 000 000 000', 'nombre' => 'Maquinaria y Equipo'],
            ['codigo' => '123 004 001 000 000 000', 'nombre' => 'Equipos de Oficina'],
            ['codigo' => '123 004 002 000 000 000', 'nombre' => 'Equipos médicos, sanitarios y de laboratorio'],
            ['codigo' => '123 004 003 000 000 000', 'nombre' => 'Equipo educacional y recreativo'],
            ['codigo' => '123 004 004 000 000 000', 'nombre' => 'Equipo de transporte, tracción y elevación'],
            ['codigo' => '123 004 005 000 000 000', 'nombre' => 'Equipos de producción'],
            ['codigo' => '123 004 006 000 000 000', 'nombre' => 'Equipos de comunicaciones y señalización'],
            ['codigo' => '123 004 007 000 000 000', 'nombre' => 'Equipo de computación'],
            ['codigo' => '123 004 008 000 000 000', 'nombre' => 'Herramientas mayores'],
            ['codigo' => '123 004 009 000 000 000', 'nombre' => 'Equipo de seguridad'],
            ['codigo' => '123 004 010 000 000 000', 'nombre' => 'Maquinaria y Equipo Institucionales en Tránsito'],
            ['codigo' => '123 004 099 000 000 000', 'nombre' => 'Depreciación de Maquinaria y Equipo'],
            ['codigo' => '123 005 000 000 000 000', 'nombre' => 'Activos Biológicos'],
            ['codigo' => '123 005 001 000 000 000', 'nombre' => 'Semovientes'],
            ['codigo' => '123 005 002 000 000 000', 'nombre' => 'Otros activos biológicos'],
            ['codigo' => '123 005 003 000 000 000', 'nombre' => 'Activos Biológicos Institucionales en Tránsito'],
            ['codigo' => '123 005 099 000 000 000', 'nombre' => 'Agotamiento de activos biológicos'],
            ['codigo' => '123 006 000 000 000 000', 'nombre' => 'Otros Activos Fijos Tangibles'],
            ['codigo' => '123 006 001 000 000 000', 'nombre' => 'Libros, Revistas y Otros Coleccionables'],
            ['codigo' => '123 006 002 000 000 000', 'nombre' => 'Obras de Arte'],
            ['codigo' => '123 006 090 000 000 000', 'nombre' => 'Otros Activos Fijos Tangibles'],
            ['codigo' => '123 006 091 000 000 000', 'nombre' => 'Otros Activos Tangibles Institucionales en Tránsito'],
            ['codigo' => '123 006 099 000 000 000', 'nombre' => 'Depreciación de Otros Activos Fijos Tangibles'],
        ];

        DB::table('clasificaciones')->insert(array_map(fn ($row) => [
            ...$row,
            'created_at' => $now,
            'updated_at' => $now,
        ], $data));
    }
}
