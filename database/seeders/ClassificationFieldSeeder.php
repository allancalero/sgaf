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
        // Leer archivo JSON con la configuración de campos
        $jsonPath = database_path('seeders/classifications_fields.json');
        
        if (!file_exists($jsonPath)) {
            echo "❌ Archivo JSON no encontrado: {$jsonPath}\n";
            return;
        }

        $jsonContent = file_get_contents($jsonPath);
        $configuracion = json_decode($jsonContent, true);

        if (!$configuracion) {
            echo "❌ Error al decodificar JSON\n";
            return;
        }

        $totalCampos = 0;
        $totalClasificaciones = 0;

        // Procesar cada categoría
        foreach ($configuracion as $categoria => $clasificaciones) {
            foreach ($clasificaciones as $config) {
                $clasificacionId = $config['id'];
                $nombreEsperado = $config['nombre'];
                $campos = $config['campos'];

                // Buscar la clasificación en la base de datos
                $clasificacion = Clasificacion::find($clasificacionId);

                if (!$clasificacion) {
                    echo "⚠️  Clasificación ID {$clasificacionId} ({$nombreEsperado}) no encontrada\n";
                    continue;
                }

                echo "✓ Procesando: {$clasificacion->nombre} (ID: {$clasificacionId})\n";
                $totalClasificaciones++;

                // Crear cada campo dinámico
                foreach ($campos as $campo) {
                    $fieldData = [
                        'clasificacion_id' => $clasificacionId,
                        'field_name' => $campo['nombre'],
                        'field_label' => $campo['etiqueta'],
                        'field_type' => $campo['tipo'],
                        'required' => $campo['requerido'],
                        'order' => $campo['orden'],
                    ];

                    // Agregar opciones si es un campo select
                    if ($campo['tipo'] === 'select' && isset($campo['opciones'])) {
                        $fieldData['field_options'] = $campo['opciones'];
                    }

                    ClassificationField::create($fieldData);
                    $totalCampos++;
                }
            }
        }

        echo "\n";
        echo "✅ Campos de clasificación creados exitosamente\n";
        echo "   📊 Total clasificaciones procesadas: {$totalClasificaciones}\n";
        echo "   📋 Total campos creados: {$totalCampos}\n";
    }
}
