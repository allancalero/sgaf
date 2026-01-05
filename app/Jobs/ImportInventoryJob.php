<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportInventoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 1;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $filePath,
        protected int $userId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $spreadsheet = IOFactory::load($this->filePath);
        $sheet = $spreadsheet->getSheetByName('INVENTARIO GENERAL') ?? $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestRow();

        // Collect unique values for catalogs
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
            $nombre = trim($sheet->getCell('I' . $row)->getValue() ?? '');
            $marca = trim($sheet->getCell('J' . $row)->getValue() ?? '');
            $precioUnit = floatval($sheet->getCell('K' . $row)->getCalculatedValue() ?? 0);
            $precioAdq = floatval($sheet->getCell('L' . $row)->getCalculatedValue() ?? 0);
            $color = trim($sheet->getCell('N' . $row)->getValue() ?? '');
            $serie = trim($sheet->getCell('O' . $row)->getValue() ?? '');
            $modelo = trim($sheet->getCell('P' . $row)->getValue() ?? '');
            $responsable = trim($sheet->getCell('Q' . $row)->getValue() ?? '');
            $estado = strtoupper(trim($sheet->getCell('R' . $row)->getValue() ?? 'BUENO'));

            if (!in_array($estado, ['BUENO', 'REGULAR', 'MALO'])) {
                $estado = 'BUENO';
            }

            // Collect unique entries
            if (!empty($clasificacion) && $clasificacion !== '****') $clasificaciones[$clasificacion] = true;
            if (!empty($area) && $area !== '****') $areas[$area] = true;
            if (!empty($fuente) && $fuente !== '****') $fuentes[$fuente] = true;
            if (!empty($proveedor) && $proveedor !== '****') $proveedores[$proveedor] = true;
            if (!empty($cheque) && $cheque !== '****') $cheques[$cheque] = true;
            if (!empty($responsable) && $responsable !== '****') $responsables[$responsable] = true;

            // Convert date
            $fecha = null;
            if ($fechaRaw && $fechaRaw !== '****') {
                if (is_numeric($fechaRaw)) {
                    $fecha = ExcelDate::excelToDateTimeObject($fechaRaw)->format('Y-m-d');
                } else {
                    $fecha = date('Y-m-d', strtotime($fechaRaw));
                }
            }
            if (!$fecha || $fecha === '1970-01-01') {
                $fecha = now()->format('Y-m-d');
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

        DB::transaction(function () use ($clasificaciones, $areas, $fuentes, $proveedores, $cheques, $responsables, $activos) {
            // Insert clasificaciones
            $clasifIds = [];
            foreach (array_keys($clasificaciones) as $nombre) {
                $existing = DB::table('clasificaciones')->where('nombre', $nombre)->first();
                if ($existing) {
                    $clasifIds[$nombre] = $existing->id;
                } else {
                    $id = DB::table('clasificaciones')->insertGetId([
                        'nombre' => $nombre,
                        'descripcion' => 'Importado desde Excel',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $clasifIds[$nombre] = $id;
                }
            }

            // Insert areas
            $areaIds = [];
            foreach (array_keys($areas) as $nombre) {
                $existing = DB::table('areas')->where('nombre', $nombre)->first();
                if ($existing) {
                    $areaIds[$nombre] = $existing->id;
                } else {
                    $id = DB::table('areas')->insertGetId([
                        'nombre' => $nombre,
                        'descripcion' => 'Importado desde Excel',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $areaIds[$nombre] = $id;
                }
            }

            // Insert fuentes
            $fuenteIds = [];
            foreach (array_keys($fuentes) as $nombre) {
                $existing = DB::table('fuentes_financiamiento')->where('nombre', $nombre)->first();
                if ($existing) {
                    $fuenteIds[$nombre] = $existing->id;
                } else {
                    $id = DB::table('fuentes_financiamiento')->insertGetId([
                        'nombre' => $nombre,
                        'descripcion' => 'Importado desde Excel',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $fuenteIds[$nombre] = $id;
                }
            }

            // Insert proveedores
            $provIds = [];
            foreach (array_keys($proveedores) as $nombre) {
                $existing = DB::table('proveedores')->where('nombre', $nombre)->first();
                if ($existing) {
                    $provIds[$nombre] = $existing->id;
                } else {
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
            }

            // Insert cheques
            $chequeIds = [];
            foreach (array_keys($cheques) as $numero) {
                $existing = DB::table('cheques')->where('numero_cheque', $numero)->first();
                if ($existing) {
                    $chequeIds[$numero] = $existing->id;
                } else {
                    $id = DB::table('cheques')->insertGetId([
                        'numero_cheque' => $numero,
                        'banco' => 'BANCO NACIONAL',
                        'fecha_emision' => now(),
                        'monto' => 0,
                        'beneficiario' => 'ALCALDÍA DE TIPITAPA',
                        'concepto' => 'Adquisición de activos',
                        'estado' => 'pagado',
                        'usuario_emisor_id' => $this->userId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $chequeIds[$numero] = $id;
                }
            }

            // Insert personal/responsables
            $respIds = [];
            $defaultAreaId = !empty($areaIds) ? reset($areaIds) : null;
            foreach (array_keys($responsables) as $nombreCompleto) {
                $existing = DB::table('personal')
                    ->whereRaw("CONCAT(nombre, ' ', apellido) = ?", [$nombreCompleto])
                    ->first();
                if ($existing) {
                    $respIds[$nombreCompleto] = $existing->id;
                } else {
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
                        'area_id' => $defaultAreaId,
                        'telefono' => '',
                        'email' => '',
                        'estado' => 'activo',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $respIds[$nombreCompleto] = $id;
                }
            }

            // Insert activos (skip duplicates by codigo_inventario)
            foreach ($activos as $activo) {
                $exists = DB::table('activos_fijos')
                    ->where('codigo_inventario', $activo['codigo'])
                    ->exists();

                if ($exists) {
                    continue; // Skip duplicates
                }

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
            }
        });

        // Clean up the temp file
        if (file_exists($this->filePath)) {
            unlink($this->filePath);
        }
    }
}
