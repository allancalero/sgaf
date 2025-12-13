<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Catálogos base
        $this->call([
            AreasSeeder::class,
            CargosSeeder::class,
            UbicacionesSeeder::class,
            ClasificacionesSeeder::class,
            FuentesFinanciamientoSeeder::class,
            TiposActivosSeeder::class,
            ProveedoresSeeder::class,
            PersonalSeeder::class,
            ResponsablesSeeder::class,
            ActivosFijosSeeder::class,
        ]);

        // Usuario demo
        $user = User::factory()->create([
            'nombre' => 'Admin',
            'apellido' => 'Demo',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'rol' => 'ADMIN',
            'estado' => 'ACTIVO',
        ]);

        $this->call(RolePermissionSeeder::class);
    }
}
