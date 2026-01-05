<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class AreasSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $data = [
            ['nombre' => 'Alcaldía Central', 'estado' => 'ACTIVO'],
            ['nombre' => 'Finanzas', 'estado' => 'ACTIVO'],
            ['nombre' => 'Recursos Humanos', 'estado' => 'ACTIVO'],
            ['nombre' => 'Obras Públicas', 'estado' => 'ACTIVO'],
            ['nombre' => 'Tecnología', 'estado' => 'ACTIVO'],
        ];

        DB::table('areas')->insert(array_map(fn ($row) => [
            ...$row,
            'created_at' => $now,
            'updated_at' => $now,
        ], $data));
    }
}
