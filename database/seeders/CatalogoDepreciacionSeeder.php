<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CatalogoDepreciacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \Illuminate\Support\Facades\DB::table('catalogo_depreciacion')->insert([
            // 1. De edificios
            ['categoria_general' => 'De edificios', 'especifica' => 'Industriales', 'mas_especifica' => null, 'vida_util_anos' => 10, 'tasa_anual' => 10.00, 'tasa_mensual' => 0.83],
            ['categoria_general' => 'De edificios', 'especifica' => 'Comerciales', 'mas_especifica' => null, 'vida_util_anos' => 20, 'tasa_anual' => 5.00, 'tasa_mensual' => 0.42],
            ['categoria_general' => 'De edificios', 'especifica' => 'Residencia del propietario (explotación agropecuaria)', 'mas_especifica' => null, 'vida_util_anos' => 10, 'tasa_anual' => 10.00, 'tasa_mensual' => 0.83],
            ['categoria_general' => 'De edificios', 'especifica' => 'Instalaciones fijas en explotaciones agropecuarias', 'mas_especifica' => null, 'vida_util_anos' => 10, 'tasa_anual' => 10.00, 'tasa_mensual' => 0.83],
            ['categoria_general' => 'De edificios', 'especifica' => 'Para edificios de alquiler', 'mas_especifica' => null, 'vida_util_anos' => 30, 'tasa_anual' => 3.00, 'tasa_mensual' => 0.28],

            // 2. De equipo de transporte
            ['categoria_general' => 'De equipo de transporte', 'especifica' => 'Colectivo o de carga', 'mas_especifica' => null, 'vida_util_anos' => 5, 'tasa_anual' => 20.00, 'tasa_mensual' => 1.67],
            ['categoria_general' => 'De equipo de transporte', 'especifica' => 'Vehículos de empresas de alquiler', 'mas_especifica' => null, 'vida_util_anos' => 3, 'tasa_anual' => 33.33, 'tasa_mensual' => 2.78],
            ['categoria_general' => 'De equipo de transporte', 'especifica' => 'Uso particular usados en actividades económicas', 'mas_especifica' => null, 'vida_util_anos' => 5, 'tasa_anual' => 20.00, 'tasa_mensual' => 1.67],
            ['categoria_general' => 'De equipo de transporte', 'especifica' => 'Otros equipos de transporte', 'mas_especifica' => null, 'vida_util_anos' => 8, 'tasa_anual' => 12.50, 'tasa_mensual' => 1.04],

            // 3. De maquinaria y equipos
            ['categoria_general' => 'De maquinaria y equipos', 'especifica' => 'Industriales en general', 'mas_especifica' => 'Fija en un bien inmóvil', 'vida_util_anos' => 10, 'tasa_anual' => 10.00, 'tasa_mensual' => 0.83],
            ['categoria_general' => 'De maquinaria y equipos', 'especifica' => 'Industriales en general', 'mas_especifica' => 'No adherido a la planta', 'vida_util_anos' => 7, 'tasa_anual' => 14.28, 'tasa_mensual' => 1.19],
            ['categoria_general' => 'De maquinaria y equipos', 'especifica' => 'Industriales en general', 'mas_especifica' => 'Otras maquinarias y equipos', 'vida_util_anos' => 5, 'tasa_anual' => 20.00, 'tasa_mensual' => 1.67],
            ['categoria_general' => 'De maquinaria y equipos', 'especifica' => 'Equipo empresas agroindustriales', 'mas_especifica' => null, 'vida_util_anos' => 5, 'tasa_anual' => 20.00, 'tasa_mensual' => 1.67],
            ['categoria_general' => 'De maquinaria y equipos', 'especifica' => 'Agrícolas', 'mas_especifica' => null, 'vida_util_anos' => 5, 'tasa_anual' => 20.00, 'tasa_mensual' => 1.67],
            ['categoria_general' => 'De maquinaria y equipos', 'especifica' => 'Otros, bienes muebles', 'mas_especifica' => 'Mobiliario y equipo de oficina', 'vida_util_anos' => 5, 'tasa_anual' => 20.00, 'tasa_mensual' => 1.67],
            ['categoria_general' => 'De maquinaria y equipos', 'especifica' => 'Otros, bienes muebles', 'mas_especifica' => 'Equipos de comunicación', 'vida_util_anos' => 5, 'tasa_anual' => 20.00, 'tasa_mensual' => 1.67],
            ['categoria_general' => 'De maquinaria y equipos', 'especifica' => 'Otros, bienes muebles', 'mas_especifica' => 'Ascensores y AC', 'vida_util_anos' => 10, 'tasa_anual' => 10.00, 'tasa_mensual' => 0.83],
            ['categoria_general' => 'De maquinaria y equipos', 'especifica' => 'Otros, bienes muebles', 'mas_especifica' => 'Equipos de computación', 'vida_util_anos' => 2, 'tasa_anual' => 50.00, 'tasa_mensual' => 4.17],
            ['categoria_general' => 'De maquinaria y equipos', 'especifica' => 'Otros, bienes muebles', 'mas_especifica' => 'Equipos para medios de comunicación', 'vida_util_anos' => 2, 'tasa_anual' => 50.00, 'tasa_mensual' => 4.17],
            ['categoria_general' => 'De maquinaria y equipos', 'especifica' => 'Otros, bienes muebles', 'mas_especifica' => 'Los demás no comprendidos', 'vida_util_anos' => 5, 'tasa_anual' => 20.00, 'tasa_mensual' => 1.67],
        ]);
    }
}
