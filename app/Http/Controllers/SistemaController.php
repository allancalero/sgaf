<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Backup;
use App\Services\BackupService;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SistemaController extends Controller
{
    public function index()
    {
        return Inertia::render('Sistema/Index');
    }

    public function respaldo(BackupService $backupService)
    {
        try {
            $backups = $backupService->listBackups();
        } catch (\Exception $e) {
            // If service fails, return empty array
            $backups = [];
            \Log::error('Backup service error: ' . $e->getMessage());
        }
        
        return Inertia::render('Sistema/Respaldo', [
            'backups' => $backups,
        ]);
    }

    public function seguridad()
    {
        $roles = Role::pluck('name');
        $permissions = Permission::pluck('name')->groupBy(fn ($name) => explode('.', $name)[0]);

        $users = User::select('id', 'nombre', 'apellido', 'email')
            ->get()
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'nombre' => $user->nombre,
                    'apellido' => $user->apellido,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames()->values(),
                    'permissions' => $user->getAllPermissions()->pluck('name')->values(),
                ];
            });

        return Inertia::render('Sistema/Seguridad', [
            'roles' => $roles,
            'permissions' => $permissions,
            'users' => $users,
        ]);
    }

    public function actualizarAccesos(Request $request, User $user)
    {
        $allRoles = Role::pluck('name')->all();
        $allPermissions = Permission::pluck('name')->all();

        $data = $request->validate([
            'roles' => ['array'],
            'roles.*' => [Rule::in($allRoles)],
            'permissions' => ['array'],
            'permissions.*' => [Rule::in($allPermissions)],
        ]);

        $user->syncRoles($data['roles'] ?? []);
        $user->syncPermissions($data['permissions'] ?? []);

        return back()->with('success', 'Accesos actualizados.');
    }

    public function asignarTodosLosRoles(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            return back()->with('error', 'Usuario no encontrado.');
        }

        $roles = Role::pluck('name')->all();

        if (empty($roles)) {
            return back()->with('error', 'No hay roles configurados.');
        }

        $user->syncRoles($roles);

        return back()->with('success', 'Se asignaron todos los roles al usuario.');
    }

    public function descargarRespaldo(): StreamedResponse
    {
        $timestamp = now()->format('Ymd_His');

        $data = [
            'meta' => [
                'generated_at' => now()->toIso8601String(),
                'app' => config('app.name'),
                'driver' => config('database.default'),
            ],
            'areas' => DB::table('areas')->get(),
            'ubicaciones' => DB::table('ubicaciones')->get(),
            'clasificaciones' => DB::table('clasificaciones')->get(),
            'tipos_activos' => DB::table('tipos_activos')->get(),
            'fuentes_financiamiento' => DB::table('fuentes_financiamiento')->get(),
            'proveedores' => DB::table('proveedores')->get(),
            'personal' => DB::table('personal')->get(),
            'responsables' => DB::table('responsables')->get(),
            'activos_fijos' => DB::table('activos_fijos')->get(),
        ];

        $payload = json_encode($data, JSON_PRETTY_PRINT);

        return response()->streamDownload(function () use ($payload) {
            echo $payload;
        }, 'respaldo_sgaf_'.$timestamp.'.json', [
            'Content-Type' => 'application/json',
        ]);
    }

    public function crearRespaldo(BackupService $backupService)
    {
        try {
            $backup = $backupService->createBackup('manual', auth()->id());
            
            return back()->with('success', 'Respaldo creado exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al crear respaldo: ' . $e->getMessage());
        }
    }

    public function descargarRespaldoSQL(BackupService $backupService, Backup $backup)
    {
        try {
            return $backupService->downloadBackup($backup->id);
        } catch (\Exception $e) {
            return back()->with('error', 'Error al descargar respaldo: ' . $e->getMessage());
        }
    }

    public function restaurarRespaldo(Request $request, BackupService $backupService, Backup $backup)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        // Verify user password
        if (!Hash::check($request->password, auth()->user()->password)) {
            throw ValidationException::withMessages([
                'password' => ['La contraseña es incorrecta.'],
            ]);
        }

        try {
            $backupService->restoreBackup($backup->id);
            
            return back()->with('success', 'Base de datos restaurada exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al restaurar: ' . $e->getMessage());
        }
    }

    public function eliminarRespaldo(BackupService $backupService, Backup $backup)
    {
        try {
            $backupService->deleteBackup($backup->id);
            
            return back()->with('success', 'Respaldo eliminado.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar respaldo: ' . $e->getMessage());
        }
    }
}
