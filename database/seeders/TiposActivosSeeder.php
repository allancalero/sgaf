<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TiposActivosSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $clasificaciones = DB::table('clasificaciones')
            ->pluck('id', 'codigo');

        $data = [
            ['nombre' => 'Laptop', 'codigo' => '123 004 007 000 000 000'], // Equipo de computación
            ['nombre' => 'Desktop', 'codigo' => '123 004 007 000 000 000'],
            ['nombre' => 'Impresora', 'codigo' => '123 004 007 000 000 000'],
            ['nombre' => 'Escritorio', 'codigo' => '123 004 001 000 000 000'], // Equipos de Oficina
            ['nombre' => 'Silla ergonómica', 'codigo' => '123 004 001 000 000 000'],
            ['nombre' => 'Vehículo liviano', 'codigo' => '123 004 004 000 000 000'], // Transporte
            ['nombre' => 'Camión', 'codigo' => '123 004 004 000 000 000'],
            ['nombre' => 'Retroexcavadora', 'codigo' => '123 004 005 000 000 000'], // Producción
        ];

        $rows = [];
        foreach ($data as $row) {
            $clasificacionId = $clasificaciones[$row['codigo']] ?? null;
            if (!$clasificacionId) {
                continue;
            }
            $rows[] = [
                'nombre' => $row['nombre'],
                'clasificacion_id' => $clasificacionId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows) {
            DB::table('tipos_activos')->insert($rows);
        }
    }
}
