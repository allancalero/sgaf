<?php

namespace Database\Seeders;

use App\Models\Clasificacion;
use App\Models\ClassificationField;
use Illuminate\Database\Seeder;

class ClassificationFieldSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar clasificación de Motocicletas (asumiendo ID 20)
        $motoClasificacion = Clasificacion::where('nombre', 'like', '%Motocicleta%')
            ->orWhere('nombre', 'like', '%MOTO%')
            ->orWhere('id', 20)
            ->first();

        if ($motoClasificacion) {
            ClassificationField::create([
                'clasificacion_id' => $motoClasificacion->id,
                'field_name' => 'motor',
                'field_label' => 'Motor',
                'field_type' => 'text',
                'required' => true,
                'order' => 1,
            ]);

            ClassificationField::create([
                'clasificacion_id' => $motoClasificacion->id,
                'field_name' => 'chasis',
                'field_label' => 'Chasis',
                'field_type' => 'text',
                'required' => true,
                'order' => 2,
            ]);

            ClassificationField::create([
                'clasificacion_id' => $motoClasificacion->id,
                'field_name' => 'placa',
                'field_label' => 'Placa',
                'field_type' => 'text',
                'required' => true,
                'order' => 3,
            ]);

            ClassificationField::create([
                'clasificacion_id' => $motoClasificacion->id,
                'field_name' => 'tipo_combustible',
                'field_label' => 'Tipo de Combustible',
                'field_type' => 'select',
                'field_options' => ['Gasolina', 'Diesel', 'Eléctrico', 'Híbrido'],
                'required' => true,
                'order' => 4,
            ]);
        }

        // Buscar clasificación de Vehículos
        $vehiculoClasificacion = Clasificacion::where('nombre', 'like', '%Vehículo%')
            ->orWhere('nombre', 'like', '%Vehiculo%')
            ->orWhere('nombre', 'like', '%VEHIC%')
            ->first();

        if ($vehiculoClasificacion) {
            ClassificationField::create([
                'clasificacion_id' => $vehiculoClasificacion->id,
                'field_name' => 'vin',
                'field_label' => 'VIN (Número de Identificación)',
                'field_type' => 'text',
                'required' => false,
                'order' => 1,
            ]);

            ClassificationField::create([
                'clasificacion_id' => $vehiculoClasificacion->id,
                'field_name' => 'placa',
                'field_label' => 'Placa',
                'field_type' => 'text',
                'required' => true,
                'order' => 2,
            ]);

            ClassificationField::create([
                'clasificacion_id' => $vehiculoClasificacion->id,
                'field_name' => 'kilometraje',
                'field_label' => 'Kilometraje',
                'field_type' => 'number',
                'required' => false,
                'order' => 3,
            ]);
        }

        echo "✅ Campos de clasificación creados exitosamente\n";
    }
}
