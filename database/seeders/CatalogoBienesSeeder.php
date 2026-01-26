<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogoBienesSeeder extends Seeder
{
    /**
     * Catálogo Integral de Bienes Municipales
     * 16 Clasificaciones con más de 100 tipos de activos
     */
    public function run(): void
    {
        $catalogo = [
            [
                'id_clasificacion' => 'CLA-TEC-COMPUTO',
                'prefijo' => '01',
                'clasificacion' => 'Equipos de Computación y Procesamiento',
                'descripcion' => 'Hardware principal, periféricos y accesorios informáticos',
                'codigo' => '123-004-007-000-000-000',
                'tipos' => [
                    ['id' => 'TEC-001', 'nombre' => 'CPU / Torre (Unidad Central de Proceso)'],
                    ['id' => 'TEC-002', 'nombre' => 'Monitor LED/LCD (Suelto)'],
                    ['id' => 'TEC-003', 'nombre' => 'Computadora de Escritorio (Kit Completo: CPU+Monitor)'],
                    ['id' => 'TEC-004', 'nombre' => 'Servidor de Rack (Empresarial)'],
                    ['id' => 'TEC-005', 'nombre' => 'Servidor de Torre'],
                    ['id' => 'TEC-006', 'nombre' => 'Computadora All-in-One (Todo en uno)'],
                    ['id' => 'TEC-007', 'nombre' => 'Computadora Portátil (Laptop)'],
                    ['id' => 'TEC-008', 'nombre' => 'Tablet / Tableta Digital'],
                    ['id' => 'TEC-009', 'nombre' => 'Teclado (USB/Inalámbrico)'],
                    ['id' => 'TEC-010', 'nombre' => 'Mouse / Ratón'],
                    ['id' => 'TEC-011', 'nombre' => 'Disco Duro Externo'],
                    ['id' => 'TEC-012', 'nombre' => 'Memoria USB (Inventariable)'],
                    ['id' => 'TEC-013', 'nombre' => 'Cámara Web (Webcam)'],
                    ['id' => 'TEC-014', 'nombre' => 'Parlantes de Computadora'],
                    ['id' => 'TEC-015', 'nombre' => 'Docking Station (Base para Laptop)'],
                    ['id' => 'TEC-016', 'nombre' => 'Lector de Código de Barras'],
                ]
            ],
            [
                'id_clasificacion' => 'CLA-TEC-REDES',
                'prefijo' => '02',
                'clasificacion' => 'Infraestructura de Red y Energía',
                'descripcion' => 'Equipos de conectividad y respaldo eléctrico',
                'codigo' => '123-004-006-000-000-000',
                'tipos' => [
                    ['id' => 'RED-001', 'nombre' => 'UPS / No-Break (Respaldo Energía)'],
                    ['id' => 'RED-002', 'nombre' => 'Regulador de Voltaje'],
                    ['id' => 'RED-003', 'nombre' => 'Switch de Red (Administrable/No Administrable)'],
                    ['id' => 'RED-004', 'nombre' => 'Router Inalámbrico / Access Point'],
                    ['id' => 'RED-005', 'nombre' => 'Rack / Gabinete de Servidores'],
                    ['id' => 'RED-006', 'nombre' => 'Patch Panel'],
                    ['id' => 'RED-007', 'nombre' => 'Antena de Telecomunicaciones'],
                    ['id' => 'RED-008', 'nombre' => 'Modem de Internet'],
                ]
            ],
            [
                'id_clasificacion' => 'CLA-TEC-IMPRESION',
                'prefijo' => '03',
                'clasificacion' => 'Equipos de Impresión y Digitalización',
                'descripcion' => 'Dispositivos de salida y escaneo',
                'codigo' => '123-004-001-000-000-000',
                'tipos' => [
                    ['id' => 'IMP-001', 'nombre' => 'Impresora Multifuncional (Tinta/Láser)'],
                    ['id' => 'IMP-002', 'nombre' => 'Impresora Matricial (Para cheques/formularios)'],
                    ['id' => 'IMP-003', 'nombre' => 'Impresora de Carnets / Tarjetas PVC'],
                    ['id' => 'IMP-004', 'nombre' => 'Plotter (Impresora de Gran Formato)'],
                    ['id' => 'IMP-005', 'nombre' => 'Escáner de Documentos (Alta velocidad)'],
                    ['id' => 'IMP-006', 'nombre' => 'Escáner Plano (Cama plana)'],
                ]
            ],
            [
                'id_clasificacion' => 'CLA-MOB-ASIENTOS',
                'prefijo' => '04',
                'clasificacion' => 'Mobiliario - Sillas y Asientos',
                'descripcion' => 'Todo tipo de asientos para personal y público',
                'codigo' => '123-004-001-001-000-000',
                'tipos' => [
                    ['id' => 'SIL-001', 'nombre' => 'Silla Ejecutiva (Cuero/Ergonómica Alta)'],
                    ['id' => 'SIL-002', 'nombre' => 'Silla Secretarial (Operativa con rodos)'],
                    ['id' => 'SIL-003', 'nombre' => 'Silla de Visita (Fija/Trineo)'],
                    ['id' => 'SIL-004', 'nombre' => 'Silla de Espera (Tándem/Banca metálica)'],
                    ['id' => 'SIL-005', 'nombre' => 'Silla Plástica Apilable (Eventos)'],
                    ['id' => 'SIL-006', 'nombre' => 'Silla Cajero (Alta tipo banco)'],
                    ['id' => 'SIL-007', 'nombre' => 'Sofá (Para recepción/despacho)'],
                ]
            ],
            [
                'id_clasificacion' => 'CLA-MOB-SUPERFICIES',
                'prefijo' => '05',
                'clasificacion' => 'Mobiliario - Escritorios y Mesas',
                'descripcion' => 'Superficies de trabajo',
                'codigo' => '123-004-001-002-000-000',
                'tipos' => [
                    ['id' => 'ESC-001', 'nombre' => 'Escritorio Ejecutivo (Madera/Vidrio)'],
                    ['id' => 'ESC-002', 'nombre' => 'Escritorio en L (Modular)'],
                    ['id' => 'ESC-003', 'nombre' => 'Escritorio Operativo (Recto pequeño)'],
                    ['id' => 'ESC-004', 'nombre' => 'Mesa de Reuniones / Conferencias'],
                    ['id' => 'ESC-005', 'nombre' => 'Mesa de Computadora (Pequeña)'],
                    ['id' => 'ESC-006', 'nombre' => 'Mesa Plegable (Para eventos)'],
                    ['id' => 'ESC-007', 'nombre' => 'Mostrador de Atención al Cliente'],
                    ['id' => 'ESC-008', 'nombre' => 'Podium / Atril'],
                ]
            ],
            [
                'id_clasificacion' => 'CLA-MOB-ALMACEN',
                'prefijo' => '06',
                'clasificacion' => 'Mobiliario - Almacenamiento',
                'descripcion' => 'Muebles para guardar documentos y objetos',
                'codigo' => '123-004-001-003-000-000',
                'tipos' => [
                    ['id' => 'ALM-001', 'nombre' => 'Archivador Metálico (2, 3 o 4 Gavetas)'],
                    ['id' => 'ALM-002', 'nombre' => 'Estante / Librero de Madera'],
                    ['id' => 'ALM-003', 'nombre' => 'Estantería Metálica (Tipo Racks)'],
                    ['id' => 'ALM-004', 'nombre' => 'Credenza (Mueble bajo auxiliar)'],
                    ['id' => 'ALM-005', 'nombre' => 'Locker / Casillero Metálico'],
                    ['id' => 'ALM-006', 'nombre' => 'Armario de Suministros (Papelería)'],
                ]
            ],
            [
                'id_clasificacion' => 'CLA-OFICINA-EQUIPOS',
                'prefijo' => '07',
                'clasificacion' => 'Equipos de Oficina Diversos',
                'descripcion' => 'Maquinaria de apoyo administrativo',
                'codigo' => '123-004-001-004-000-000',
                'tipos' => [
                    ['id' => 'OFQ-001', 'nombre' => 'Fotocopiadora Industrial'],
                    ['id' => 'OFQ-002', 'nombre' => 'Trituradora de Papel'],
                    ['id' => 'OFQ-003', 'nombre' => 'Encuadernadora / Espiraladora'],
                    ['id' => 'OFQ-004', 'nombre' => 'Guillotina de Papel'],
                    ['id' => 'OFQ-005', 'nombre' => 'Reloj Biométrico (Asistencia)'],
                    ['id' => 'OFQ-006', 'nombre' => 'Contadora de Billetes'],
                    ['id' => 'OFQ-007', 'nombre' => 'Detector de Billetes Falsos'],
                    ['id' => 'OFQ-008', 'nombre' => 'Caja Fuerte'],
                    ['id' => 'OFQ-009', 'nombre' => 'Pizarra Acrílica / Corcho'],
                ]
            ],
            [
                'id_clasificacion' => 'CLA-CLIMATIZACION',
                'prefijo' => '08',
                'clasificacion' => 'Climatización y Electrodomésticos',
                'descripcion' => 'Confort y cocina',
                'codigo' => '123-004-001-005-000-000',
                'tipos' => [
                    ['id' => 'CLI-001', 'nombre' => 'Aire Acondicionado (Mini Split)'],
                    ['id' => 'CLI-002', 'nombre' => 'Aire Acondicionado (Ventana)'],
                    ['id' => 'CLI-003', 'nombre' => 'Ventilador (Pedestal/Pared/Techo)'],
                    ['id' => 'CLI-004', 'nombre' => 'Dispensador de Agua (Oasis)'],
                    ['id' => 'CLI-005', 'nombre' => 'Refrigeradora'],
                    ['id' => 'CLI-006', 'nombre' => 'Microondas'],
                    ['id' => 'CLI-007', 'nombre' => 'Cafetera Industrial'],
                ]
            ],
            [
                'id_clasificacion' => 'CLA-VEH-LIVIANO',
                'prefijo' => '09',
                'clasificacion' => 'Vehículos Livianos',
                'descripcion' => 'Transporte de personal y supervisión',
                'codigo' => '123-004-004-000-000-000',
                'tipos' => [
                    ['id' => 'VEH-001', 'nombre' => 'Camioneta Doble Cabina 4x4'],
                    ['id' => 'VEH-002', 'nombre' => 'Camioneta Sencilla'],
                    ['id' => 'VEH-003', 'nombre' => 'Automóvil Sedán'],
                    ['id' => 'VEH-004', 'nombre' => 'Motocicleta'],
                    ['id' => 'VEH-005', 'nombre' => 'Microbús / Minivan'],
                ]
            ],
            [
                'id_clasificacion' => 'CLA-VEH-PESADO',
                'prefijo' => '10',
                'clasificacion' => 'Maquinaria Pesada y Camiones',
                'descripcion' => 'Equipo caminero y de servicios públicos',
                'codigo' => '123-004-005-000-000-000',
                'tipos' => [
                    ['id' => 'MAQ-001', 'nombre' => 'Camión Compactador de Basura'],
                    ['id' => 'MAQ-002', 'nombre' => 'Camión Volquete'],
                    ['id' => 'MAQ-003', 'nombre' => 'Camión Cisterna (Pipa)'],
                    ['id' => 'MAQ-004', 'nombre' => 'Motoniveladora (Patrol)'],
                    ['id' => 'MAQ-005', 'nombre' => 'Retroexcavadora'],
                    ['id' => 'MAQ-006', 'nombre' => 'Rodillo Compactador'],
                    ['id' => 'MAQ-007', 'nombre' => 'Cargadora Frontal'],
                    ['id' => 'MAQ-008', 'nombre' => 'Tractor Agrícola'],
                ]
            ],
            [
                'id_clasificacion' => 'CLA-COM-SEG',
                'prefijo' => '11',
                'clasificacion' => 'Comunicación y Seguridad',
                'descripcion' => 'Radios, cámaras y vigilancia',
                'codigo' => '123-004-006-001-000-000',
                'tipos' => [
                    ['id' => 'SEG-001', 'nombre' => 'Radio Comunicador Portátil'],
                    ['id' => 'SEG-002', 'nombre' => 'Radio Base Vehicular'],
                    ['id' => 'SEG-003', 'nombre' => 'Cámara de Video Profesional'],
                    ['id' => 'SEG-004', 'nombre' => 'Cámara Fotográfica'],
                    ['id' => 'SEG-005', 'nombre' => 'Drone'],
                    ['id' => 'SEG-006', 'nombre' => 'DVR / NVR (Grabador de video)'],
                    ['id' => 'SEG-007', 'nombre' => 'Cámara de Seguridad CCTV'],
                    ['id' => 'SEG-008', 'nombre' => 'Arma de Fuego (Revólver/Escopeta)'],
                ]
            ],
            [
                'id_clasificacion' => 'CLA-INGENIERIA',
                'prefijo' => '12',
                'clasificacion' => 'Ingeniería y Topografía',
                'descripcion' => 'Equipos de medición técnica',
                'codigo' => '123-004-002-000-000-000',
                'tipos' => [
                    ['id' => 'ING-001', 'nombre' => 'Estación Total'],
                    ['id' => 'ING-002', 'nombre' => 'Teodolito'],
                    ['id' => 'ING-003', 'nombre' => 'Nivel de Ingeniero'],
                    ['id' => 'ING-004', 'nombre' => 'GPS Topográfico / Diferencial'],
                    ['id' => 'ING-005', 'nombre' => 'Distanciómetro Láser'],
                ]
            ],
            [
                'id_clasificacion' => 'CLA-HERRAMIENTAS',
                'prefijo' => '13',
                'clasificacion' => 'Herramientas y Mantenimiento',
                'descripcion' => 'Equipos para cuadrillas y taller',
                'codigo' => '123-004-008-000-000-000',
                'tipos' => [
                    ['id' => 'HER-001', 'nombre' => 'Desbrozadora (Guira)'],
                    ['id' => 'HER-002', 'nombre' => 'Motosierra'],
                    ['id' => 'HER-003', 'nombre' => 'Generador Eléctrico Portátil'],
                    ['id' => 'HER-004', 'nombre' => 'Bomba de Agua (Motobomba)'],
                    ['id' => 'HER-005', 'nombre' => 'Hidrolavadora'],
                    ['id' => 'HER-006', 'nombre' => 'Soldadora'],
                    ['id' => 'HER-007', 'nombre' => 'Compresor de Aire'],
                    ['id' => 'HER-008', 'nombre' => 'Escalera de Extensión'],
                ]
            ],
            [
                'id_clasificacion' => 'CLA-AUDIO',
                'prefijo' => '14',
                'clasificacion' => 'Audio y Eventos',
                'descripcion' => 'Equipos para actos públicos',
                'codigo' => '123-004-003-000-000-000',
                'tipos' => [
                    ['id' => 'AUD-001', 'nombre' => 'Parlante Amplificado'],
                    ['id' => 'AUD-002', 'nombre' => 'Consola de Sonido / Mixer'],
                    ['id' => 'AUD-003', 'nombre' => 'Micrófono (Alámbrico/Inalámbrico)'],
                    ['id' => 'AUD-004', 'nombre' => 'Proyector Multimedia (Data Show)'],
                    ['id' => 'AUD-005', 'nombre' => 'Pantalla para Proyección'],
                    ['id' => 'AUD-006', 'nombre' => 'Toldo Plegable'],
                ]
            ],
            [
                'id_clasificacion' => 'CLA-URBANO',
                'prefijo' => '15',
                'clasificacion' => 'Mobiliario Urbano',
                'descripcion' => 'Bienes instalados en espacios públicos',
                'codigo' => '123-002-000-000-000-000',
                'tipos' => [
                    ['id' => 'URB-001', 'nombre' => 'Banca de Parque'],
                    ['id' => 'URB-002', 'nombre' => 'Juegos Infantiles (Playground)'],
                    ['id' => 'URB-003', 'nombre' => 'Contenedor de Basura Público'],
                    ['id' => 'URB-004', 'nombre' => 'Parada de Autobús (Estructura)'],
                ]
            ],
            [
                'id_clasificacion' => 'CLA-SALUD',
                'prefijo' => '16',
                'clasificacion' => 'Equipos Médicos',
                'descripcion' => 'Para clínicas municipales y ambulancias',
                'codigo' => '123-004-002-001-000-000',
                'tipos' => [
                    ['id' => 'MED-001', 'nombre' => 'Camilla Médica'],
                    ['id' => 'MED-002', 'nombre' => 'Silla de Ruedas'],
                    ['id' => 'MED-003', 'nombre' => 'Tanque de Oxígeno'],
                    ['id' => 'MED-004', 'nombre' => 'Esterilizador'],
                ]
            ],
        ];

        $this->command->info('Iniciando carga del Catálogo Integral de Bienes Municipales...');

        foreach ($catalogo as $cat) {
            // Insert or update classification
            $clasificacionId = DB::table('clasificaciones')->updateOrInsert(
                ['prefijo' => $cat['prefijo']],
                [
                    'nombre' => $cat['clasificacion'],
                    'codigo' => $cat['codigo'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Get the classification ID
            $clasificacion = DB::table('clasificaciones')
                ->where('prefijo', $cat['prefijo'])
                ->first();

            if ($clasificacion) {
                $this->command->info("  ✓ Clasificación: {$cat['prefijo']} - {$cat['clasificacion']}");

                // Insert types for this classification
                foreach ($cat['tipos'] as $tipo) {
                    DB::table('tipos_activos')->updateOrInsert(
                        ['nombre' => $tipo['nombre']],
                        [
                            'clasificacion_id' => $clasificacion->id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
                
                $this->command->info("    → " . count($cat['tipos']) . " tipos agregados");
            }
        }

        $totalClasificaciones = DB::table('clasificaciones')->count();
        $totalTipos = DB::table('tipos_activos')->count();

        $this->command->info('');
        $this->command->info("✅ Catálogo cargado exitosamente:");
        $this->command->info("   - {$totalClasificaciones} clasificaciones");
        $this->command->info("   - {$totalTipos} tipos de activos");
    }
}
