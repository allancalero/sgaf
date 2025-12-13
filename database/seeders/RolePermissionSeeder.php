<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'activos.view',
            'activos.manage',
            'catalogos.manage',
            'sistema.manage',
            'seguridad.manage',
            'respaldos.download',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $consulta = Role::firstOrCreate(['name' => 'consulta', 'guard_name' => 'web']);
        $fullAccess = Role::firstOrCreate(['name' => 'fullaccess', 'guard_name' => 'web']);

        $admin->syncPermissions($permissions);
        $editor->syncPermissions([
            'activos.view',
            'activos.manage',
        ]);
        $consulta->syncPermissions([
            'activos.view',
        ]);

        $fullAccess->syncPermissions($permissions);

        if ($user = User::first()) {
            $user->assignRole($admin);
        }
    }
}
