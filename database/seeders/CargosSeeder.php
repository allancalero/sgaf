<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CargosSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $data = [
            ['nombre' => 'Director', 'estado' => 'ACTIVO'],
            ['nombre' => 'Jefe de Área', 'estado' => 'ACTIVO'],
            ['nombre' => 'Analista', 'estado' => 'ACTIVO'],
            ['nombre' => 'Técnico', 'estado' => 'ACTIVO'],
            ['nombre' => 'Auxiliar', 'estado' => 'ACTIVO'],
        ];

        DB::table('cargos')->insert(array_map(fn ($row) => [
            ...$row,
            'created_at' => $now,
            'updated_at' => $now,
        ], $data));
    }
}
