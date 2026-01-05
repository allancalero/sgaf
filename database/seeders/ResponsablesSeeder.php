<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ResponsablesSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $areas = DB::table('areas')->pluck('id', 'nombre');
        $cargos = DB::table('cargos')->pluck('id', 'nombre');

        $data = [
            ['nombre' => 'Responsable Finanzas', 'cargo' => 'Jefe de Área', 'area' => 'Finanzas'],
            ['nombre' => 'Responsable Tecnología', 'cargo' => 'Jefe de Área', 'area' => 'Tecnología'],
            ['nombre' => 'Responsable Obras Públicas', 'cargo' => 'Jefe de Área', 'area' => 'Obras Públicas'],
        ];

        $rows = [];
        foreach ($data as $row) {
            $cargoId = $cargos[$row['cargo']] ?? null;
            $areaId = $areas[$row['area']] ?? null;
            if (!$cargoId || !$areaId) {
                continue;
            }

            $rows[] = [
                'nombre' => $row['nombre'],
                'id_cargo' => $cargoId,
                'area_id' => $areaId,
                'estado' => 'ACTIVO',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows) {
            DB::table('responsables')->insert($rows);
        }
    }
}
