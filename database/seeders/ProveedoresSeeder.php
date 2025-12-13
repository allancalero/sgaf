<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProveedoresSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $data = [
            ['nombre' => 'Tech Solutions S.A.', 'ruc' => '20123456789', 'direccion' => 'Av. Central 123', 'telefono' => '2222-1000', 'email' => 'ventas@techsolutions.com'],
            ['nombre' => 'Muebles & Diseño', 'ruc' => '20987654321', 'direccion' => 'Zona Industrial 45', 'telefono' => '2222-2000', 'email' => 'contacto@mueblesdiseno.com'],
            ['nombre' => 'Motores del Norte', 'ruc' => '20765432109', 'direccion' => 'Km 15 Carretera Norte', 'telefono' => '2222-3000', 'email' => 'ventas@motoresnorte.com'],
        ];

        DB::table('proveedores')->insert(array_map(fn ($row) => [
            ...$row,
            'created_at' => $now,
            'updated_at' => $now,
        ], $data));
    }
}
