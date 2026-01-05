<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExportBackupCommand extends Command
{
    protected $signature = 'backup:export {--path= : Ruta personalizada para guardar el JSON}';

    protected $description = 'Genera un respaldo lógico en JSON de tablas clave (catálogos y activos)';

    public function handle(): int
    {
        $timestamp = now()->format('Ymd_His');
        $defaultPath = 'backups/respaldo_sgaf_'.$timestamp.'.json';
        $path = $this->option('path') ?: $defaultPath;

        $tables = [
            'areas',
            'ubicaciones',
            'clasificaciones',
            'tipos_activos',
            'fuentes_financiamiento',
            'proveedores',
            'personal',
            'responsables',
            'activos_fijos',
        ];

        $this->info('Generando respaldo...');

        $data = [
            'meta' => [
                'generated_at' => now()->toIso8601String(),
                'app' => config('app.name'),
                'database' => config('database.default'),
                'tables' => $tables,
            ],
        ];

        foreach ($tables as $table) {
            $this->line(" - Exportando {$table}...");
            $data[$table] = DB::table($table)->get();
        }

        $json = json_encode($data, JSON_PRETTY_PRINT);
        Storage::put($path, $json);

        $this->info('Respaldo guardado en: '.storage_path('app/'.$path));

        return self::SUCCESS;
    }
}
