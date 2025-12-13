<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActivosFijosSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $data = [
            [
                'codigo_inventario' => 'AF-001',
                'nombre_activo' => 'Laptop Dell Latitude',
                'marca' => 'Dell',
                'modelo' => 'Latitude 5440',
                'color' => 'Negro',
                'serie' => 'DL-5440-001',
                'descripcion' => 'Equipo asignado a analista de TI',
                'cantidad' => 1,
                'precio_adquisicion' => 1250.00,
                'fecha_adquisicion' => now()->subMonths(6),
                'numero_factura' => 'FAC-1001',
                'estado' => 'BUENO',
                'area_id' => 5,
                'ubicacion_id' => 1,
                'clasificacion_id' => 1,
                'tipo_activo_id' => 1,
                'fuente_financiamiento_id' => 1,
                'proveedor_id' => 1,
                'personal_id' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo_inventario' => 'AF-002',
                'nombre_activo' => 'Proyector Epson',
                'marca' => 'Epson',
                'modelo' => 'X39',
                'color' => 'Blanco',
                'serie' => 'EP-X39-009',
                'descripcion' => 'Proyector sala de reuniones',
                'cantidad' => 1,
                'precio_adquisicion' => 680.00,
                'fecha_adquisicion' => now()->subMonths(10),
                'numero_factura' => 'FAC-1002',
                'estado' => 'BUENO',
                'area_id' => 2,
                'ubicacion_id' => 2,
                'clasificacion_id' => 2,
                'tipo_activo_id' => null,
                'fuente_financiamiento_id' => 2,
                'proveedor_id' => 2,
                'personal_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'codigo_inventario' => 'AF-003',
                'nombre_activo' => 'Vehículo Pickup',
                'marca' => 'Toyota',
                'modelo' => 'Hilux',
                'color' => 'Blanco',
                'serie' => 'TY-HX-2023-15',
                'descripcion' => 'Vehículo asignado a obras públicas',
                'cantidad' => 1,
                'precio_adquisicion' => 28000.00,
                'fecha_adquisicion' => now()->subYear(),
                'numero_factura' => 'FAC-1003',
                'estado' => 'REGULAR',
                'area_id' => 4,
                'ubicacion_id' => 3,
                'clasificacion_id' => 3,
                'tipo_activo_id' => null,
                'fuente_financiamiento_id' => 1,
                'proveedor_id' => 3,
                'personal_id' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('activos_fijos')->insert($data);
    }
}
