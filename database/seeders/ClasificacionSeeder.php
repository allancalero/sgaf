<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Clasificacion;

class ClasificacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clasificaciones = [
            ['prefijo' => '01', 'nombre' => 'Activos Biológicos', 'codigo' => '123 005 000 000 000 000'],
            ['prefijo' => '12', 'nombre' => 'Bienes de Defensa', 'codigo' => '123 002 008 000 000 000'],
            ['prefijo' => '13', 'nombre' => 'Bienes de Seguridad', 'codigo' => '123 002 009 000 000 000'],
            ['prefijo' => '06', 'nombre' => 'Carreteras, Calles, Puentes y Aeropuertos', 'codigo' => '123 002 004 000 000 000'],
            ['prefijo' => '09', 'nombre' => 'Construcción y Edificaciones de Otras Infraestructuras', 'codigo' => '123 002 007 000 000 000'],
            ['prefijo' => '99', 'nombre' => 'Depreciación de Maquinaria y Equipo', 'codigo' => '123 004 099 000 000 000'],
            ['prefijo' => '11', 'nombre' => 'Edificios e Instalaciones', 'codigo' => '123 002 000 000 000 000'],
            ['prefijo' => '04', 'nombre' => 'Edificios no Residenciales e Instalaciones', 'codigo' => '123 002 002 000 000 000'],
            ['prefijo' => '17', 'nombre' => 'Equipo de transporte, tracción y elevación', 'codigo' => '123 004 004 000 000 000'],
            ['prefijo' => '16', 'nombre' => 'Equipo educacional y recreativo', 'codigo' => '123 004 003 000 000 000'],
            ['prefijo' => '15', 'nombre' => 'Equipo médicos, Sanitarios y de laboratorio', 'codigo' => '123 004 002 000 000 000'],
            ['prefijo' => '02', 'nombre' => 'Equipos de computación', 'codigo' => '123 004 007 000 000 000'],
            ['prefijo' => '19', 'nombre' => 'Equipos de comunicaciones y señalización', 'codigo' => '123 004 006 000 000 000'],
            ['prefijo' => '03', 'nombre' => 'Equipos de Oficina', 'codigo' => '123 004 001 000 000 000'],
            ['prefijo' => '18', 'nombre' => 'Equipos de producción', 'codigo' => '123 004 005 000 000 000'],
            ['prefijo' => '21', 'nombre' => 'Equipos de Seguridad y militar', 'codigo' => '123 004 009 000 000 000'],
            ['prefijo' => '20', 'nombre' => 'Herramientas mayores', 'codigo' => '123 004 008 000 000 000'],
            ['prefijo' => '14', 'nombre' => 'Maquinaria y Equipo', 'codigo' => '123 004 000 000 000 000'],
            ['prefijo' => '22', 'nombre' => 'Maquinaria y Equipo Institucionales en Tránsito', 'codigo' => '123 004 010 000 000 000'],
            ['prefijo' => '07', 'nombre' => 'Obra e Infraestructura Hidráulica', 'codigo' => '123 002 005 000 000 000'],
            ['prefijo' => '08', 'nombre' => 'Obra e Infraestructura Urbanísticas', 'codigo' => '123 002 006 000 000 000'],
            ['prefijo' => '05', 'nombre' => 'Obras e Infraestructura Agropecuarias', 'codigo' => '123 002 003 000 000 000'],
            ['prefijo' => '10', 'nombre' => 'Tierras y Terrenos', 'codigo' => '123 001 001 000 000 000'],
        ];

        foreach ($clasificaciones as $clasificacion) {
            DB::table('clasificaciones')->updateOrInsert(
                ['prefijo' => $clasificacion['prefijo']],
                ['nombre' => $clasificacion['nombre'], 'codigo' => $clasificacion['codigo']]
            );
        }
    }
}
