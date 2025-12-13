<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserQuickCreateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $full = User::firstOrCreate(
            ['email' => 'full@example.com'],
            [
                'nombre' => 'Full',
                'apellido' => 'Access',
                'password' => Hash::make('secret123'),
                'estado' => 'ACTIVO',
            ],
        );

        $full->syncRoles(Role::pluck('name'));

        $editor = User::firstOrCreate(
            ['email' => 'activos@example.com'],
            [
                'nombre' => 'Activos',
                'apellido' => 'Editor',
                'password' => Hash::make('secret123'),
                'estado' => 'ACTIVO',
            ],
        );

        $editor->syncRoles(['editor']);
    }
}
