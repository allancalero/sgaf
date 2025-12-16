<?php
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
require __DIR__.'/bootstrap/app.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$filePath = 'C:\Users\IT\Desktop\INVENTARIO ALCALDIA DE TIPITAPA AL 23 -04-25.xlsx';

echo "=== IMPORTACIÓN DE INVENTARIO ===\n\n";

try {
    $spreadsheet = IOFactory::load($filePath);
    $sheet = $spreadsheet->getSheetByName('INVENTARIO GENERAL');
    $highestRow = $sheet->getHighestRow();
    
    echo "Total filas a procesar: " . ($highestRow - 2) . "\n\n";
    
    // 1. Limpiar datos existentes
    echo "1. Limpiando datos existentes...\n";
    DB::table('trazabilidad')->delete();
    DB::table('reasignaciones')->delete();
    DB::table('activos_fijos')->delete();
    DB::table('personal')->delete();
    DB::table('clasificaciones')->delete();
    DB::table('areas')->delete();
    DB::table('fuentes_financiamiento')->delete();
    DB::table('proveedores')->delete();
    DB::table('cheques')->delete();
    echo "   ✓ Datos limpiados\n\n";
    
    // 2. Recolectar datos únicos
    echo "2. Recolectando datos únicos del Excel...\n";
    
    $clasificaciones = [];
    $areas = [];
    $fuentes = [];
    $proveedores = [];
    $cheques = [];
    $responsables = [];
    $activos = [];
    
    for ($row = 3; $row <= $highestRow; $row++) {
        $codigo = trim($sheet->getCell('A' . $row)->getValue() ?? '');
        if (empty($codigo)) continue;
        
        $clasificacion = trim($sheet->getCell('B' . $row)->getValue() ?? '');
        $area = trim($sheet->getCell('C' . $row)->getValue() ?? '');
        $fuente = trim($sheet->getCell('D' . $row)->getValue() ?? '');
        $proveedor = trim($sheet->getCell('E' . $row)->getValue() ?? '');
        $fechaRaw = $sheet->getCell('F' . $row)->getValue();
        $cheque = trim($sheet->getCell('G' . $row)->getValue() ?? '');
        $cantidad = intval($sheet->getCell('H' . $row)->getValue() ?? 1);
        $nombre = trim($sheet->getCell('I' . $row)->getValue() ?? '');
        $marca = trim($sheet->getCell('J' . $row)->getValue() ?? '');
        $precioUnit = floatval($sheet->getCell('K' . $row)->getCalculatedValue() ?? 0);
        $precioAdq = floatval($sheet->getCell('L' . $row)->getCalculatedValue() ?? 0);
        $factura = trim($sheet->getCell('M' . $row)->getValue() ?? '');
        $color = trim($sheet->getCell('N' . $row)->getValue() ?? '');
        $serie = trim($sheet->getCell('O' . $row)->getValue() ?? '');
        $modelo = trim($sheet->getCell('P' . $row)->getValue() ?? '');
        $responsable = trim($sheet->getCell('Q' . $row)->getValue() ?? '');
        $estado = strtoupper(trim($sheet->getCell('R' . $row)->getValue() ?? 'BUENO'));
        
        // Normalizar estado
        if (!in_array($estado, ['BUENO', 'REGULAR', 'MALO'])) {
            $estado = 'BUENO';
        }
        
        // Recolectar únicos
        if (!empty($clasificacion) && $clasificacion !== '****') $clasificaciones[$clasificacion] = true;
        if (!empty($area) && $area !== '****') $areas[$area] = true;
        if (!empty($fuente) && $fuente !== '****') $fuentes[$fuente] = true;
        if (!empty($proveedor) && $proveedor !== '****') $proveedores[$proveedor] = true;
        if (!empty($cheque) && $cheque !== '****') $cheques[$cheque] = true;
        if (!empty($responsable) && $responsable !== '****') $responsables[$responsable] = true;
        
        // Convertir fecha
        $fecha = null;
        if ($fechaRaw && $fechaRaw !== '****') {
            if (is_numeric($fechaRaw)) {
                $fecha = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($fechaRaw)->format('Y-m-d');
            } else {
                $fecha = date('Y-m-d', strtotime($fechaRaw));
            }
        }
        if (!$fecha || $fecha === '1970-01-01') {
            $fecha = '2024-01-01';
        }
        
        $activos[] = [
            'codigo' => $codigo,
            'nombre' => $nombre ?: 'SIN NOMBRE',
            'clasificacion' => $clasificacion,
            'area' => $area,
            'fuente' => $fuente,
            'proveedor' => $proveedor,
            'cheque' => $cheque,
            'fecha' => $fecha,
            'marca' => $marca,
            'modelo' => $modelo,
            'serie' => $serie,
            'color' => $color,
            'precio' => $precioAdq > 0 ? $precioAdq : $precioUnit,
            'responsable' => $responsable,
            'estado' => $estado,
        ];
    }
    
    echo "   Clasificaciones: " . count($clasificaciones) . "\n";
    echo "   Áreas: " . count($areas) . "\n";
    echo "   Fuentes: " . count($fuentes) . "\n";
    echo "   Proveedores: " . count($proveedores) . "\n";
    echo "   Cheques: " . count($cheques) . "\n";
    echo "   Responsables: " . count($responsables) . "\n";
    echo "   Activos: " . count($activos) . "\n\n";
    
    // 3. Insertar catálogos
    echo "3. Insertando catálogos...\n";
    
    $clasifIds = [];
    foreach (array_keys($clasificaciones) as $nombre) {
        $id = DB::table('clasificaciones')->insertGetId([
            'nombre' => $nombre,
            'descripcion' => 'Importado desde Excel',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $clasifIds[$nombre] = $id;
    }
    echo "   ✓ Clasificaciones insertadas\n";
    
    $areaIds = [];
    foreach (array_keys($areas) as $nombre) {
        $id = DB::table('areas')->insertGetId([
            'nombre' => $nombre,
            'descripcion' => 'Importado desde Excel',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $areaIds[$nombre] = $id;
    }
    echo "   ✓ Áreas insertadas\n";
    
    $fuenteIds = [];
    foreach (array_keys($fuentes) as $nombre) {
        $id = DB::table('fuentes_financiamiento')->insertGetId([
            'nombre' => $nombre,
            'descripcion' => 'Importado desde Excel',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $fuenteIds[$nombre] = $id;
    }
    echo "   ✓ Fuentes insertadas\n";
    
    $provIds = [];
    foreach (array_keys($proveedores) as $nombre) {
        $id = DB::table('proveedores')->insertGetId([
            'nombre' => $nombre,
            'contacto' => '',
            'telefono' => '',
            'email' => '',
            'direccion' => '',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $provIds[$nombre] = $id;
    }
    echo "   ✓ Proveedores insertados\n";
    
    $chequeIds = [];
    foreach (array_keys($cheques) as $numero) {
        $id = DB::table('cheques')->insertGetId([
            'numero' => $numero,
            'banco' => 'BANCO NACIONAL',
            'fecha_emision' => now(),
            'monto' => 0,
            'beneficiario' => 'ALCALDÍA DE TIPITAPA',
            'concepto' => 'Adquisición de activos',
            'estado' => 'pagado',
            'usuario_emisor_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $chequeIds[$numero] = $id;
    }
    echo "   ✓ Cheques insertados\n";
    
    $respIds = [];
    foreach (array_keys($responsables) as $nombreCompleto) {
        $partes = explode(' ', $nombreCompleto);
        $apellido = array_pop($partes);
        if (count($partes) > 1) {
            $apellido = array_pop($partes) . ' ' . $apellido;
        }
        $nombre = implode(' ', $partes) ?: $apellido;
        
        $id = DB::table('personal')->insertGetId([
            'nombre' => $nombre,
            'apellido' => $apellido,
            'cedula' => '',
            'cargo' => 'EMPLEADO',
            'area_id' => $areaIds[array_key_first($areas)] ?? null,
            'telefono' => '',
            'email' => '',
            'estado' => 'activo',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $respIds[$nombreCompleto] = $id;
    }
    echo "   ✓ Personal insertado\n\n";
    
    // 4. Insertar activos
    echo "4. Insertando activos fijos...\n";
    $contador = 0;
    $errores = 0;
    
    foreach ($activos as $activo) {
        try {
            DB::table('activos_fijos')->insert([
                'codigo_inventario' => $activo['codigo'],
                'nombre_activo' => $activo['nombre'],
                'descripcion' => "Marca: {$activo['marca']}, Modelo: {$activo['modelo']}, Color: {$activo['color']}",
                'clasificacion_id' => $clasifIds[$activo['clasificacion']] ?? null,
                'area_id' => $areaIds[$activo['area']] ?? null,
                'fuente_financiamiento_id' => $fuenteIds[$activo['fuente']] ?? null,
                'proveedor_id' => $provIds[$activo['proveedor']] ?? null,
                'cheque_id' => $chequeIds[$activo['cheque']] ?? null,
                'personal_id' => $respIds[$activo['responsable']] ?? null,
                'fecha_adquisicion' => $activo['fecha'],
                'precio_adquisicion' => $activo['precio'],
                'marca' => $activo['marca'] !== 'S/M' ? $activo['marca'] : null,
                'modelo' => $activo['modelo'] !== 'S/M' ? $activo['modelo'] : null,
                'serie' => $activo['serie'] !== 'S/S' ? $activo['serie'] : null,
                'estado' => $activo['estado'],
                'vida_util_anos' => 5,
                'valor_residual' => 0,
                'depreciacion_acumulada' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $contador++;
            
            if ($contador % 500 === 0) {
                echo "   Procesados: {$contador} activos...\n";
            }
        } catch (\Exception $e) {
            $errores++;
        }
    }
    
    echo "\n=== IMPORTACIÓN COMPLETADA ===\n";
    echo "✓ Activos importados: {$contador}\n";
    echo "✗ Errores: {$errores}\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
