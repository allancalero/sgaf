<?php

namespace Database\Seeders;

use App\Models\ActivoFijo;
use App\Models\Area;
use App\Models\Cargo;
use App\Models\Clasificacion;
use App\Models\FuenteFinanciamiento;
use App\Models\Personal;
use App\Models\Proveedor;
use App\Models\TipoActivo;
use App\Models\Ubicacion;
use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoActivosConMovimientosSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('es_ES');

        // Garantiza catálogos mínimos solo si están vacíos (evita duplicados si ya hay data).
        $this->seedCatalogos();

        // Asegura personal suficiente para asignaciones.
        $this->seedPersonalSiHaceFalta($faker);

        $areas = Area::pluck('id');
        $ubicaciones = Ubicacion::pluck('id');
        $clasificaciones = Clasificacion::pluck('id');
        $tipos = TipoActivo::pluck('id');
        $fuentes = FuenteFinanciamiento::pluck('id');
        $proveedores = Proveedor::pluck('id');
        $personal = Personal::pluck('id');

        $estados = ['BUENO', 'REGULAR', 'MALO'];

        for ($i = 1; $i <= 100; $i++) {
            $codigo = $this->codigoUnico($i);
            $areaId = $areas->random();
            $ubicacionId = $ubicaciones->random();
            $clasificacionId = $clasificaciones->random();
            $tipoId = $tipos->random();
            $fuenteId = $fuentes->random();
            $proveedorId = $proveedores->random();
            $responsableId = $personal->random();

            $precio = $faker->randomFloat(2, 100, 5000);
            $fechaAdq = Carbon::now()->subDays(random_int(30, 400));

            $activo = ActivoFijo::create([
                'codigo_inventario' => $codigo,
                'nombre_activo' => $faker->words(3, true),
                'marca' => $faker->randomElement(['Dell', 'HP', 'Lenovo', 'LG', 'Epson', 'Canon', 'Xiaomi']),
                'modelo' => strtoupper(Str::random(4)).'-'.$faker->numberBetween(10, 999),
                'color' => $faker->safeColorName(),
                'serie' => strtoupper(Str::random(10)),
                'foto' => null,
                'descripcion' => $faker->sentence(8),
                'cantidad' => 1,
                'precio_unitario' => $precio,
                'precio_adquisicion' => $precio,
                'fecha_adquisicion' => $fechaAdq->format('Y-m-d'),
                'numero_factura' => 'FAC-'.$faker->numberBetween(10000, 99999),
                'cheque_id' => null,
                'monto_cheque_utilizado' => null,
                'estado' => $faker->randomElement($estados),
                'area_id' => $areaId,
                'ubicacion_id' => $ubicacionId,
                'clasificacion_id' => $clasificacionId,
                'tipo_activo_id' => $tipoId,
                'fuente_financiamiento_id' => $fuenteId,
                'proveedor_id' => $proveedorId,
                'personal_id' => $responsableId,
            ]);

            // Genera historial de asignaciones (1 a 3 movimientos) y deja el último como asignación vigente.
            $movimientos = random_int(1, 3);
            $fechaBase = $fechaAdq->copy();
            $anterior = null;

            for ($m = 0; $m < $movimientos; $m++) {
                $nuevo = $personal->random();
                $fecha = $fechaBase->copy()->addDays($m * 15 + $faker->numberBetween(1, 10));

                DB::table('historial_asignaciones')->insert([
                    'activo_id' => $activo->id,
                    'asignacion_anterior_id' => $anterior,
                    'asignacion_nuevo_id' => $nuevo,
                    'fecha_asignacion' => $fecha->format('Y-m-d'),
                    'motivo' => $faker->sentence(6),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $anterior = $nuevo;
            }

            // Deja como responsable final al último movimiento.
            $activo->update(['personal_id' => $anterior]);
        }
    }

    private function seedCatalogos(): void
    {
        $now = now();

        if (Area::count() === 0) {
            Area::insert([
                ['nombre' => 'Alcaldía Central', 'estado' => 'ACTIVO', 'created_at' => $now, 'updated_at' => $now],
                ['nombre' => 'Finanzas', 'estado' => 'ACTIVO', 'created_at' => $now, 'updated_at' => $now],
                ['nombre' => 'Recursos Humanos', 'estado' => 'ACTIVO', 'created_at' => $now, 'updated_at' => $now],
                ['nombre' => 'Obras Públicas', 'estado' => 'ACTIVO', 'created_at' => $now, 'updated_at' => $now],
                ['nombre' => 'Tecnología', 'estado' => 'ACTIVO', 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        if (Ubicacion::count() === 0) {
            Ubicacion::insert([
                ['nombre' => 'Planta Baja', 'estado' => 'ACTIVO', 'created_at' => $now, 'updated_at' => $now],
                ['nombre' => 'Primer Piso', 'estado' => 'ACTIVO', 'created_at' => $now, 'updated_at' => $now],
                ['nombre' => 'Segundo Piso', 'estado' => 'ACTIVO', 'created_at' => $now, 'updated_at' => $now],
                ['nombre' => 'Bodega Central', 'estado' => 'ACTIVO', 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        if (Cargo::count() === 0) {
            Cargo::insert([
                ['nombre' => 'Analista', 'estado' => 'ACTIVO', 'created_at' => $now, 'updated_at' => $now],
                ['nombre' => 'Coordinador', 'estado' => 'ACTIVO', 'created_at' => $now, 'updated_at' => $now],
                ['nombre' => 'Director', 'estado' => 'ACTIVO', 'created_at' => $now, 'updated_at' => $now],
                ['nombre' => 'Técnico', 'estado' => 'ACTIVO', 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        if (Clasificacion::count() === 0) {
            Clasificacion::insert([
                ['nombre' => 'Tecnología', 'created_at' => $now, 'updated_at' => $now],
                ['nombre' => 'Mobiliario', 'created_at' => $now, 'updated_at' => $now],
                ['nombre' => 'Vehículos', 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        if (TipoActivo::count() === 0) {
            $clasif = Clasificacion::pluck('id');
            if ($clasif->isEmpty()) {
                $clasifId = Clasificacion::insertGetId(['nombre' => 'Tecnología', 'created_at' => $now, 'updated_at' => $now]);
                $clasif = collect([$clasifId]);
            }
            TipoActivo::insert([
                ['nombre' => 'Laptop', 'clasificacion_id' => $clasif->random(), 'created_at' => $now, 'updated_at' => $now],
                ['nombre' => 'Desktop', 'clasificacion_id' => $clasif->random(), 'created_at' => $now, 'updated_at' => $now],
                ['nombre' => 'Impresora', 'clasificacion_id' => $clasif->random(), 'created_at' => $now, 'updated_at' => $now],
                ['nombre' => 'Mueble', 'clasificacion_id' => $clasif->random(), 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        if (FuenteFinanciamiento::count() === 0) {
            FuenteFinanciamiento::insert([
                ['nombre' => 'Presupuesto Municipal', 'estado' => 'ACTIVO', 'created_at' => $now, 'updated_at' => $now],
                ['nombre' => 'Donación', 'estado' => 'ACTIVO', 'created_at' => $now, 'updated_at' => $now],
                ['nombre' => 'Transferencia', 'estado' => 'ACTIVO', 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        if (Proveedor::count() === 0) {
            Proveedor::insert([
                ['nombre' => 'Proveedor Alfa', 'ruc' => 'RUC-001', 'created_at' => $now, 'updated_at' => $now],
                ['nombre' => 'Proveedor Beta', 'ruc' => 'RUC-002', 'created_at' => $now, 'updated_at' => $now],
                ['nombre' => 'Proveedor Gamma', 'ruc' => 'RUC-003', 'created_at' => $now, 'updated_at' => $now],
            ]);
        }
    }

    private function seedPersonalSiHaceFalta($faker): void
    {
        $areas = Area::pluck('id');
        $cargos = Cargo::pluck('id');
        $ubicaciones = Ubicacion::pluck('id');

        $faltan = max(0, 20 - Personal::count());

        for ($i = 0; $i < $faltan; $i++) {
            Personal::create([
                'nombre' => $faker->firstName(),
                'apellido' => $faker->lastName(),
                'cargo_id' => $cargos->random(),
                'area_id' => $areas->random(),
                'ubicacion_id' => $ubicaciones->random(),
                'telefono' => $faker->e164PhoneNumber(),
                'email' => $faker->unique()->safeEmail(),
                'numero_empleado' => 'EMP-'.$faker->numberBetween(1000, 9999),
                'numero_cedula' => 'CID-'.$faker->numberBetween(100000, 999999),
                'fecha_nac' => Carbon::now()->subYears(random_int(22, 55))->format('Y-m-d'),
                'edad' => random_int(22, 55),
                'direccion' => $faker->streetAddress(),
                'profesion' => $faker->jobTitle(),
                'estado' => 'ACTIVO',
                'foto' => null,
            ]);
        }
    }

    private function codigoUnico(int $i): string
    {
        $base = 'AC-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT);
        $code = $base;
        $suffix = 1;
        while (ActivoFijo::where('codigo_inventario', $code)->exists()) {
            $code = $base.'-'.$suffix;
            $suffix++;
        }
        return $code;
    }
}
