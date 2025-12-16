<?php

namespace App\Services;

use App\Models\ActivoFijo;
use Carbon\Carbon;

class DepreciacionService
{
    /**
     * Calcula la depreciación de un activo usando el método lineal
     */
    public function calcularDepreciacionLineal(ActivoFijo $activo): array
    {
        // Si no tiene vida útil, no se puede depreciar
        if (!$activo->vida_util_anos || $activo->vida_util_anos <= 0) {
            return [
                'depreciacion_anual' => 0,
                'depreciacion_mensual' => 0,
                'depreciacion_acumulada' => 0,
                'valor_libros' => $activo->precio_adquisicion,
                'porcentaje_depreciado' => 0,
                'anos_transcurridos' => 0,
                'totalmente_depreciado' => false,
            ];
        }

        $precioAdquisicion = (float) $activo->precio_adquisicion;
        $valorResidual = (float) ($activo->valor_residual ?? 0);
        $vidaUtil = (int) $activo->vida_util_anos;
        
        // Calcular depreciación anual
        $depreciacionAnual = ($precioAdquisicion - $valorResidual) / $vidaUtil;
        $depreciacionMensual = $depreciacionAnual / 12;

        // Calcular años transcurridos desde la adquisición
        if (!$activo->fecha_adquisicion) {
            return [
                'depreciacion_anual' => $depreciacionAnual,
                'depreciacion_mensual' => $depreciacionMensual,
                'depreciacion_acumulada' => 0,
                'valor_libros' => $precioAdquisicion,
                'porcentaje_depreciado' => 0,
                'anos_transcurridos' => 0,
                'totalmente_depreciado' => false,
            ];
        }

        $fechaAdquisicion = Carbon::parse($activo->fecha_adquisicion);
        $fechaActual = Carbon::now();
        
        // Calcular meses transcurridos para mayor precisión
        $mesesTranscurridos = $fechaAdquisicion->diffInMonths($fechaActual);
        $anosTranscurridos = $mesesTranscurridos / 12;

        // Calcular depreciación acumulada (no puede exceder el valor depreciable)
        $valorDepreciable = $precioAdquisicion - $valorResidual;
        $depreciacionAcumulada = min(
            $depreciacionMensual * $mesesTranscurridos,
            $valorDepreciable
        );

        // Calcular valor en libros (no puede ser menor que valor residual)
        $valorLibros = max(
            $precioAdquisicion - $depreciacionAcumulada,
            $valorResidual
        );

        // Calcular porcentaje depreciado
        $porcentajeDepreciado = $valorDepreciable > 0 
            ? ($depreciacionAcumulada / $valorDepreciable) * 100 
            : 0;

        // Verificar si está totalmente depreciado
        $totalmenteDepreciado = $valorLibros <= $valorResidual || $anosTranscurridos >= $vidaUtil;

        return [
            'depreciacion_anual' => round($depreciacionAnual, 2),
            'depreciacion_mensual' => round($depreciacionMensual, 2),
            'depreciacion_acumulada' => round($depreciacionAcumulada, 2),
            'valor_libros' => round($valorLibros, 2),
            'porcentaje_depreciado' => round($porcentajeDepreciado, 2),
            'anos_transcurridos' => round($anosTranscurridos, 2),
            'meses_transcurridos' => $mesesTranscurridos,
            'totalmente_depreciado' => $totalmenteDepreciado,
        ];
    }

    /**
     * Actualiza la depreciación de un activo en la base de datos
     */
    public function actualizarDepreciacion(ActivoFijo $activo): void
    {
        $calculo = $this->calcularDepreciacionLineal($activo);

        $activo->update([
            'depreciacion_anual' => $calculo['depreciacion_anual'],
            'depreciacion_acumulada' => $calculo['depreciacion_acumulada'],
            'valor_libros' => $calculo['valor_libros'],
            'fecha_ultima_depreciacion' => now(),
        ]);
    }

    /**
     * Calcula y actualiza la depreciación de todos los activos
     */
    public function calcularDepreciacionTodos(): array
    {
        $activos = ActivoFijo::whereNotNull('vida_util_anos')
            ->where('vida_util_anos', '>', 0)
            ->get();

        $procesados = 0;
        $errores = 0;

        foreach ($activos as $activo) {
            try {
                $this->actualizarDepreciacion($activo);
                $procesados++;
            } catch (\Exception $e) {
                $errores++;
                \Log::error("Error calculando depreciación para activo {$activo->id}: {$e->getMessage()}");
            }
        }

        return [
            'total' => $activos->count(),
            'procesados' => $procesados,
            'errores' => $errores,
        ];
    }

    /**
     * Obtiene proyección de depreciación para los próximos años
     */
    public function obtenerProyeccion(ActivoFijo $activo, int $anos = 5): array
    {
        if (!$activo->vida_util_anos || $activo->vida_util_anos <= 0) {
            return [];
        }

        $proyeccion = [];
        $precioAdquisicion = (float) $activo->precio_adquisicion;
        $valorResidual = (float) ($activo->valor_residual ?? 0);
        $depreciacionAnual = ($precioAdquisicion - $valorResidual) / (int) $activo->vida_util_anos;
        $fechaInicio = Carbon::parse($activo->fecha_adquisicion ?? now());

        for ($i = 1; $i <= $anos; $i++) {
            $depreciacionAcumulada = min($depreciacionAnual * $i, $precioAdquisicion - $valorResidual);
            $valorLibros = max($precioAdquisicion - $depreciacionAcumulada, $valorResidual);

            $proyeccion[] = [
                'ano' => $i,
                'fecha' => $fechaInicio->copy()->addYears($i)->format('Y-m-d'),
                'depreciacion_periodo' =>round($depreciacionAnual, 2),
                'depreciacion_acumulada' => round($depreciacionAcumulada, 2),
                'valor_libros' => round($valorLibros, 2),
            ];

            // Si ya está totalmente depreciado, no seguir
            if ($valorLibros <= $valorResidual) {
                break;
            }
        }

        return $proyeccion;
    }
}
