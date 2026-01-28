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
            'activos.create',
            'activos.edit',
            'activos.delete',
            'activos.manage', // Legacy/Super-admin catch-all
            'catalogos.manage',
            'sistema.manage',
            'seguridad.manage',
            'respaldos.download',
        ];

        $guards = ['web', 'api_jwt'];

        foreach ($guards as $guard) {
            foreach ($permissions as $perm) {
                Permission::firstOrCreate(['name' => $perm, 'guard_name' => $guard]);
            }

            $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
            $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => $guard]);
            $consulta = Role::firstOrCreate(['name' => 'consulta', 'guard_name' => $guard]);
            $fullAccess = Role::firstOrCreate(['name' => 'fullaccess', 'guard_name' => $guard]);

            $admin->syncPermissions($permissions);
            
            $editor->syncPermissions([
                'activos.view',
                'activos.create',
            ]);
            
            $consulta->syncPermissions([
                'activos.view',
            ]);

            $fullAccess->syncPermissions($permissions);

            if ($guard === 'api_jwt' && $user = User::first()) {
                $user->assignRole($admin);
            }
        }
    }
}
