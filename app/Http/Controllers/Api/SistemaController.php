<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class SistemaController extends Controller
{
    public function auditoria(Request $request)
    {
        // Check if activity_log table exists
        if (!DB::getSchemaBuilder()->hasTable('activity_log')) {
            return response()->json(['data' => [], 'message' => 'Tabla de auditoría no encontrada']);
        }

        $query = DB::table('activity_log')
            ->leftJoin('users', 'activity_log.causer_id', '=', 'users.id')
            ->select(
                'activity_log.*',
                DB::raw("CONCAT(users.nombre, ' ', users.apellido) as usuario")
            );

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('activity_log.description', 'like', "%{$search}%")
                    ->orWhere('activity_log.subject_type', 'like', "%{$search}%");
            });
        }

        $logs = $query->orderBy('activity_log.created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json($logs);
    }

    public function generarRespaldo()
    {
        try {
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');
            $dbHost = config('database.connections.mysql.host');
            
            // Get dump binary path from config or default
            $dumpPath = config('database.connections.mysql.dump.dump_binary_path', 'C:\xampp\mysql\bin');
            $mysqldump = rtrim($dumpPath, '\\/') . DIRECTORY_SEPARATOR . 'mysqldump.exe';

            if (!file_exists($mysqldump)) {
                // Fallback attempt to find it if config is wrong but standard path exists
                if (file_exists('C:\xampp\mysql\bin\mysqldump.exe')) {
                    $mysqldump = 'C:\xampp\mysql\bin\mysqldump.exe';
                } else {
                    throw new \Exception("mysqldump.exe no encontrado en: $mysqldump");
                }
            }

            // Ensure backups directory exists
            $backupPath = storage_path('app/backups');
            if (!file_exists($backupPath)) {
                mkdir($backupPath, 0755, true);
            }

            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filePath = $backupPath . DIRECTORY_SEPARATOR . $filename;

            // Construct command
            $command = [
                $mysqldump,
                "--user={$dbUser}",
                "--host={$dbHost}",
                "--result-file={$filePath}",
                "--single-transaction",
                "--routines",
                "--triggers"
            ];

            if ($dbPass) {
                $command[] = "--password={$dbPass}";
            }

            $command[] = $dbName;

            $env = [
                'SystemRoot' => env('SystemRoot', 'C:\Windows'),
                'Path' => getenv('Path'),
                'TEMP' => storage_path('app/backup-temp'),
                'TMP' => storage_path('app/backup-temp'),
            ];

            $process = new \Symfony\Component\Process\Process($command, base_path(), $env);
            $process->setTimeout(300);
            $process->run();

            if (!$process->isSuccessful()) {
                throw new \Exception("Error exit code: " . $process->getExitCode() . " Output: " . $process->getErrorOutput());
            }

            return response()->json([
                'message' => 'Respaldo generado correctamente',
                'file' => $filename,
                'path' => $filePath
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Manual Backup Error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al generar el respaldo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function respaldo()
    {
        // Ensure the default backup directory exists so users don't see "No encontrado"
        $defaultBackupPath = storage_path('app/backups');
        if (!file_exists($defaultBackupPath)) {
            mkdir($defaultBackupPath, 0755, true);
        }

        $backups = [];
        
        // Check different possible paths
        $possiblePaths = [
            $defaultBackupPath,
            storage_path('app/Laravel'),
            storage_path('app/' . config('app.name')),
        ];

        $foundPath = null;
        foreach ($possiblePaths as $path) {
            if (is_dir($path)) {
                $files = scandir($path);
                $hasFiles = false;
                foreach ($files as $file) {
                    if ($file !== '.' && $file !== '..') {
                        $backups[] = [
                            'nombre' => $file,
                            'size' => filesize($path . '/' . $file),
                            'fecha' => date('Y-m-d H:i:s', filemtime($path . '/' . $file)),
                        ];
                        $hasFiles = true;
                    }
                }
                if ($hasFiles) {
                    $foundPath = $path;
                    break;
                }
            }
        }

        return response()->json([
            'backups' => array_reverse(collect($backups)->sortBy('fecha')->values()->all()),
            'database' => config('database.connections.mysql.database') ?: config('database.default'),
            'storage_path' => $foundPath ?? $defaultBackupPath,
        ]);
    }

    public function descargarRespaldo($filename)
    {
        // Validate filename
        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
            return response()->json(['message' => 'Nombre de archivo no válido'], 400);
        }

        $possiblePaths = [
            storage_path('app/backups'),
            storage_path('app/Laravel'),
            storage_path('app/' . config('app.name')),
        ];

        foreach ($possiblePaths as $path) {
            $filePath = $path . DIRECTORY_SEPARATOR . $filename;
            if (file_exists($filePath)) {
                return response()->download($filePath);
            }
        }

        return response()->json(['message' => 'Archivo no encontrado'], 404);
    }

    public function eliminarRespaldo($filename)
    {
        try {
            // Validate filename to prevent path traversal
            if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
                return response()->json(['message' => 'Nombre de archivo no válido'], 400);
            }

            $possiblePaths = [
                storage_path('app/backups'),
                storage_path('app/Laravel'),
                storage_path('app/' . config('app.name')),
            ];

            foreach ($possiblePaths as $path) {
                $filePath = $path . DIRECTORY_SEPARATOR . $filename;
                if (file_exists($filePath)) {
                    unlink($filePath);
                    return response()->json(['message' => 'Respaldo eliminado correctamente']);
                }
            }

            return response()->json(['message' => 'Archivo no encontrado'], 404);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al eliminar respaldo', 'error' => $e->getMessage()], 500);
        }
    }

    public function seguridad()
    {
        \Log::info('Seguridad endpoint hit, user: ' . auth()->id());
        \Log::info('Connected to DB: ' . DB::connection()->getDatabaseName());
        
        // Clear cache programmatically just in case
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        $roles = \Spatie\Permission\Models\Role::all();
        $permissions = \Spatie\Permission\Models\Permission::all();

        \Log::info('Roles count: ' . $roles->count());

        return response()->json([
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }
}
