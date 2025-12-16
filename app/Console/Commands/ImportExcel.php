<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportExcel extends Command
{
    protected $signature = 'import:excel {file}';
    protected $description = 'Importar inventario desde archivo Excel';

    public function handle()
    {
        $filePath = $this->argument('file');
        
        if (!file_exists($filePath)) {
            $this->error("El archivo no existe: {$filePath}");
            return 1;
        }

        $this->info("=== IMPORTACION DE INVENTARIO ===\n");

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getSheetByName('INVENTARIO GENERAL');
            
            if (!$sheet) {
                $this->error("No se encontro la hoja 'INVENTARIO GENERAL'");
                return 1;
            }
            
            $highestRow = $sheet->getHighestRow();
            $this->info("Total filas a procesar: " . ($highestRow - 2));

            // 1. Limpiar datos existentes
            $this->info("\n1. Limpiando datos existentes...");
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('trazabilidad')->delete();
            DB::table('reasignaciones')->delete();
            DB::table('activos_fijos')->delete();
            DB::table('personal')->delete();
            DB::table('cargos')->delete();
            DB::table('ubicaciones')->delete();
            DB::table('cheques')->delete();
            DB::table('clasificaciones')->delete();
            DB::table('areas')->delete();
            DB::table('fuentes_financiamiento')->delete();
            DB::table('proveedores')->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->info("   OK");

            // 2. Leer Excel
            $this->info("\n2. Leyendo datos del Excel...");
            
            $clasificaciones = [];
            $areas = [];
            $fuentes = [];
            $proveedores = [];
            $responsables = [];
            $activos = [];
            
            for ($row = 3; $row <= $highestRow; $row++) {
                $codigo = trim($sheet->getCell('A' . $row)->getValue() ?? '');
                if (empty($codigo)) continue;
                
                $clasificacion = trim($sheet->getCell('B' . $row)->getValue() ?? '');
                $area = trim($sheet->getCell('C' . $row)->getValue() ?? '');
                $fuente = trim($sheet->getCell('D' . $row)->getValue() ?? '');
                $proveedor = trim($sheet->getCell('E' . $row)->getValue() ?? '');
                $nombre = trim($sheet->getCell('I' . $row)->getValue() ?? '');
                $marca = trim($sheet->getCell('J' . $row)->getValue() ?? '');
                $precioUnit = floatval($sheet->getCell('K' . $row)->getCalculatedValue() ?? 0);
                $color = trim($sheet->getCell('N' . $row)->getValue() ?? '');
                $serie = trim($sheet->getCell('O' . $row)->getValue() ?? '');
                $modelo = trim($sheet->getCell('P' . $row)->getValue() ?? '');
                $responsable = trim($sheet->getCell('Q' . $row)->getValue() ?? '');
                $estado = strtoupper(trim($sheet->getCell('R' . $row)->getValue() ?? 'BUENO'));
                
                if (!in_array($estado, ['BUENO', 'REGULAR', 'MALO'])) {
                    $estado = 'BUENO';
                }
                
                if (!empty($clasificacion) && $clasificacion !== '****') $clasificaciones[$clasificacion] = true;
                if (!empty($area) && $area !== '****') $areas[$area] = true;
                if (!empty($fuente) && $fuente !== '****') $fuentes[$fuente] = true;
                if (!empty($proveedor) && $proveedor !== '****') $proveedores[$proveedor] = true;
                if (!empty($responsable) && $responsable !== '****') $responsables[$responsable] = true;
                
                $activos[] = [
                    'codigo' => $codigo,
                    'nombre' => $nombre ?: 'SIN NOMBRE',
                    'clasificacion' => $clasificacion,
                    'area' => $area,
                    'fuente' => $fuente,
                    'proveedor' => $proveedor,
                    'marca' => $marca,
                    'modelo' => $modelo,
                    'serie' => $serie,
                    'color' => $color,
                    'precio' => $precioUnit > 0 ? $precioUnit : 0,
                    'responsable' => $responsable,
                    'estado' => $estado,
                ];
            }
            
            $this->info("   Clasificaciones: " . count($clasificaciones));
            $this->info("   Areas: " . count($areas));
            $this->info("   Responsables: " . count($responsables));
            $this->info("   Activos: " . count($activos));

            // 3. Insertar catalogos
            $this->info("\n3. Insertando catalogos...");
            
            $codClasif = 1;
            $clasifIds = [];
            foreach (array_keys($clasificaciones) as $nom) {
                $id = DB::table('clasificaciones')->insertGetId([
                    'codigo' => 'CLAS-' . str_pad($codClasif++, 3, '0', STR_PAD_LEFT),
                    'nombre' => $nom,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $clasifIds[$nom] = $id;
            }
            // ID por defecto si falla mapeo
            $defaultClasifId = reset($clasifIds);
            
            $areaIds = [];
            foreach (array_keys($areas) as $nom) {
                $id = DB::table('areas')->insertGetId([
                    'nombre' => $nom,
                    'estado' => 'activo',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $areaIds[$nom] = $id;
            }
            $defaultAreaId = reset($areaIds);
            
            $fuenteIds = [];
            foreach (array_keys($fuentes) as $nom) {
                $id = DB::table('fuentes_financiamiento')->insertGetId([
                    'nombre' => $nom,
                    'estado' => 'activo',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $fuenteIds[$nom] = $id;
            }
            $defaultFuenteId = reset($fuenteIds);
            
            $provIds = [];
            foreach (array_keys($proveedores) as $nom) {
                $id = DB::table('proveedores')->insertGetId([
                    'nombre' => $nom,
                    'ruc' => '',
                    'direccion' => '',
                    'telefono' => '',
                    'email' => '',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $provIds[$nom] = $id;
            }
            
            // Crear Cargo y Ubicacion por defecto
            $cargoId = DB::table('cargos')->insertGetId([
                'nombre' => 'EMPLEADO GENERAL',
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $ubicacionId = DB::table('ubicaciones')->insertGetId([
                'nombre' => 'OFICINA PRINCIPAL',
                'estado' => 'activo',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            $this->info("   Catalogos OK");
            
            // 4. Insertar personal
            $this->info("\n4. Insertando personal...");
            $respIds = [];
            $contPersonal = 0;
            
            foreach (array_keys($responsables) as $nombreCompleto) {
                $partes = explode(' ', $nombreCompleto);
                $apellido = count($partes) > 1 ? array_pop($partes) : $partes[0];
                $nombre = implode(' ', $partes) ?: $apellido;
                
                $id = DB::table('personal')->insertGetId([
                    'nombre' => substr($nombre, 0, 100),
                    'apellido' => substr($apellido, 0, 100),
                    'cargo_id' => $cargoId,
                    'area_id' => $defaultAreaId,
                    'ubicacion_id' => $ubicacionId,
                    'telefono' => '',
                    'email' => '',
                    'numero_empleado' => '',
                    'numero_cedula' => '',
                    'fecha_nac' => null,
                    'edad' => 0,
                    'direccion' => '',
                    'profesion' => '',
                    'estado' => 'activo',
                    'foto' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $respIds[$nombreCompleto] = $id;
                $contPersonal++;
            }
            $this->info("   Personal insertado: {$contPersonal}");

            // 5. Insertar activos
            $this->info("\n5. Insertando activos...");
            $contador = 0;
            $errores = 0;
            
            foreach ($activos as $activo) {
                try {
                    DB::table('activos_fijos')->insert([
                        'codigo_inventario' => $activo['codigo'],
                        'nombre_activo' => substr($activo['nombre'], 0, 255),
                        'marca' => ($activo['marca'] !== 'S/M' && $activo['marca'] !== '') ? substr($activo['marca'], 0, 100) : null,
                        'modelo' => ($activo['modelo'] !== 'S/M' && $activo['modelo'] !== '') ? substr($activo['modelo'], 0, 100) : null,
                        'color' => $activo['color'] ? substr($activo['color'], 0, 50) : null,
                        'serie' => ($activo['serie'] !== 'S/S' && $activo['serie'] !== '') ? substr($activo['serie'], 0, 100) : null,
                        'cantidad' => 1,
                        'precio_unitario' => $activo['precio'],
                        'precio_adquisicion' => $activo['precio'],
                        'fecha_adquisicion' => '2024-01-01',
                        'estado' => $activo['estado'],
                        'vida_util_anos' => 5,
                        'valor_residual' => 0,
                        'metodo_depreciacion' => 'linea_recta',
                        'depreciacion_anual' => 0,
                        'depreciacion_acumulada' => 0,
                        'valor_libros' => $activo['precio'],
                        'area_id' => $areaIds[$activo['area']] ?? $defaultAreaId, // Fallback a ID por defecto
                        'ubicacion_id' => $ubicacionId, // Usar ubicacion por defecto
                        'clasificacion_id' => $clasifIds[$activo['clasificacion']] ?? $defaultClasifId, // Fallback
                        'fuente_financiamiento_id' => $fuenteIds[$activo['fuente']] ?? $defaultFuenteId, // Fallback
                        'proveedor_id' => $provIds[$activo['proveedor']] ?? null,
                        'personal_id' => $respIds[$activo['responsable']] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $contador++;
                    
                    if ($contador % 500 === 0) {
                        $this->info("   Procesados: {$contador}");
                    }
                } catch (\Exception $e) {
                    $errores++;
                    if ($errores <= 3) {
                        $this->error("   Error: " . substr($e->getMessage(), 0, 150));
                    }
                }
            }

            $this->info("\n=== COMPLETADO ===");
            $this->info("Activos importados: {$contador}");
            if ($errores > 0) {
                $this->warn("Errores: {$errores}");
            }
            
            return 0;

        } catch (\Exception $e) {
            $this->error("Error general: " . $e->getMessage());
            return 1;
        }
    }
}
