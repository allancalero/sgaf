<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class BuildFrontend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'frontend:build';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Construye el frontend de Angular y despliega los archivos en la carpeta pública de Laravel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando construcción del frontend Angular...');

        $os = strtoupper(substr(PHP_OS, 0, 3));
        
        if ($os === 'WIN') {
            $this->comment('Detectado sistema operativo Windows.');
            $command = ['powershell', '-ExecutionPolicy', 'Bypass', '-File', './build-angular.ps1'];
        } else {
            $this->comment('Detectado sistema operativo Unix/Linux.');
            $command = ['/bin/bash', './build-angular.sh'];
            
            if (!file_exists('./build-angular.sh')) {
                $this->error('No se encontró el script de construcción para Linux (build-angular.sh).');
                return 1;
            }
        }

        $process = new Process($command);
        $process->setTimeout(600); // 10 minutos de timeout
        $process->setIdleTimeout(60);

        try {
            $process->run(function ($type, $buffer) {
                if (Process::ERR === $type) {
                    $this->output->write('<fg=red>' . $buffer . '</>');
                } else {
                    $this->output->write($buffer);
                }
            });

            if ($process->isSuccessful()) {
                $this->info('¡Frontend construido y desplegado exitosamente!');
                return 0;
            } else {
                $this->error('Hubo un error durante la construcción del frontend.');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('Error al ejecutar el proceso: ' . $e->getMessage());
            return 1;
        }
    }
}
