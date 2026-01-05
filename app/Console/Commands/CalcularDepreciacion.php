<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DepreciacionService;

class CalcularDepreciacion extends Command
{
    protected $signature = 'activos:calcular-depreciacion';
    protected $description = 'Calcula la depreciación acumulada de todos los activos fijos';

    public function handle(DepreciacionService $service)
    {
        $this->info('Iniciando cálculo de depreciación...');
        
        $resultado = $service->calcularDepreciacionTodos();
        
        $this->info("Procesados: {$resultado['procesados']} activos");
        
        if ($resultado['errores'] > 0) {
            $this->warn("Errores: {$resultado['errores']}");
        }
        
        $this->info('¡Depreciación calculada exitosamente!');
        
        return 0;
    }
}
