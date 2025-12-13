<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UbicacionesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $data = [
            ['nombre' => 'Edificio Central - Piso 1', 'estado' => 'ACTIVO'],
            ['nombre' => 'Edificio Central - Piso 2', 'estado' => 'ACTIVO'],
            ['nombre' => 'Bodega Principal', 'estado' => 'ACTIVO'],
            ['nombre' => 'Taller de Mantenimiento', 'estado' => 'ACTIVO'],
            ['nombre' => 'Oficina de Campo', 'estado' => 'ACTIVO'],
        ];

        DB::table('ubicaciones')->insert(array_map(fn ($row) => [
            ...$row,
            'created_at' => $now,
            'updated_at' => $now,
        ], $data));
    }
}
