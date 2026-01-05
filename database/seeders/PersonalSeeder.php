<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonalSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $areas = DB::table('areas')->pluck('id', 'nombre');
        $cargos = DB::table('cargos')->pluck('id', 'nombre');
        $ubicaciones = DB::table('ubicaciones')->pluck('id', 'nombre');

        $data = [
            [
                'nombre' => 'Carlos',
                'apellido' => 'García',
                'cargo' => 'Director',
                'area' => 'Alcaldía Central',
                'ubicacion' => 'Edificio Central - Piso 1',
                'telefono' => '5555-1111',
                'email' => 'carlos.garcia@example.com',
                'numero_empleado' => 'EMP-001',
                'numero_cedula' => '001-010101-0001X',
                'fecha_nac' => '1980-01-01',
                'edad' => 45,
                'profesion' => 'Administrador',
            ],
            [
                'nombre' => 'María',
                'apellido' => 'Lopez',
                'cargo' => 'Jefe de Área',
                'area' => 'Finanzas',
                'ubicacion' => 'Edificio Central - Piso 2',
                'telefono' => '5555-2222',
                'email' => 'maria.lopez@example.com',
                'numero_empleado' => 'EMP-002',
                'numero_cedula' => '001-020202-0002X',
                'fecha_nac' => '1985-02-02',
                'edad' => 40,
                'profesion' => 'Contadora',
            ],
            [
                'nombre' => 'Luis',
                'apellido' => 'Martinez',
                'cargo' => 'Analista',
                'area' => 'Tecnología',
                'ubicacion' => 'Edificio Central - Piso 1',
                'telefono' => '5555-3333',
                'email' => 'luis.martinez@example.com',
                'numero_empleado' => 'EMP-003',
                'numero_cedula' => '001-030303-0003X',
                'fecha_nac' => '1990-03-03',
                'edad' => 35,
                'profesion' => 'Ingeniero en Sistemas',
            ],
        ];

        $rows = [];
        foreach ($data as $row) {
            $cargoId = $cargos[$row['cargo']] ?? null;
            $areaId = $areas[$row['area']] ?? null;
            $ubicacionId = $ubicaciones[$row['ubicacion']] ?? null;
            if (!$cargoId || !$areaId || !$ubicacionId) {
                continue;
            }

            $rows[] = [
                'nombre' => $row['nombre'],
                'apellido' => $row['apellido'],
                'cargo_id' => $cargoId,
                'area_id' => $areaId,
                'ubicacion_id' => $ubicacionId,
                'telefono' => $row['telefono'],
                'email' => $row['email'],
                'numero_empleado' => $row['numero_empleado'],
                'numero_cedula' => $row['numero_cedula'],
                'fecha_nac' => $row['fecha_nac'],
                'edad' => $row['edad'],
                'direccion' => null,
                'profesion' => $row['profesion'],
                'estado' => 'ACTIVO',
                'foto' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if ($rows) {
            DB::table('personal')->insert($rows);
        }
    }
}
