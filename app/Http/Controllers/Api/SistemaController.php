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

    public function respaldo()
    {
        // Return database backup info
        $backups = [];
        $backupPath = storage_path('app/backups');

        if (is_dir($backupPath)) {
            $files = scandir($backupPath);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..') {
                    $backups[] = [
                        'nombre' => $file,
                        'size' => filesize($backupPath . '/' . $file),
                        'fecha' => date('Y-m-d H:i:s', filemtime($backupPath . '/' . $file)),
                    ];
                }
            }
        }

        return response()->json([
            'backups' => $backups,
            'database' => config('database.default'),
            'storage_path' => $backupPath,
        ]);
    }

    public function seguridad()
    {
        $roles = DB::table('roles')->get();
        $permissions = DB::table('permissions')->get();

        return response()->json([
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }
}
