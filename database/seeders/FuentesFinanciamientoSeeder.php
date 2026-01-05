<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FuentesFinanciamientoSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $data = [
            ['nombre' => 'Fondos Propios', 'estado' => 'ACTIVO'],
            ['nombre' => 'Transferencia Central', 'estado' => 'ACTIVO'],
            ['nombre' => 'Préstamo', 'estado' => 'ACTIVO'],
            ['nombre' => 'Donación', 'estado' => 'ACTIVO'],
        ];

        DB::table('fuentes_financiamiento')->insert(array_map(fn ($row) => [
            ...$row,
            'created_at' => $now,
            'updated_at' => $now,
        ], $data));
    }
}
