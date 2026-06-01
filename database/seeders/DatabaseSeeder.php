<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Roles
        $adminRole = \App\Models\Role::create(['name' => 'Admin General', 'slug' => 'admin_general']);
        $sedeAdminRole = \App\Models\Role::create(['name' => 'Admin de Sede', 'slug' => 'admin_sede']);
        $ingenieroRole = \App\Models\Role::create(['name' => 'Ingeniero', 'slug' => 'ingeniero']);
        $clienteRole = \App\Models\Role::create(['name' => 'Cliente', 'slug' => 'cliente']);

        // 2. Create a default Headquarter
        $hqLima = \App\Models\Headquarter::create([
            'name' => 'Sede Central Lima',
            'address' => 'Av. Arequipa 1234',
            'city' => 'Lima',
            'phone' => '01 4445556',
            'email' => 'lima@gourmetica.com',
            'is_active' => true
        ]);

        // 3. Create Admin User
        User::create([
            'name' => 'Administrador General',
            'email' => 'admin@gourmetica.com',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'headquarter_id' => null, // Global access
        ]);

        // 4. Create an Ingeniero User
        User::create([
            'name' => 'Ingeniero de Sistemas',
            'email' => 'ingeniero@gourmetica.com',
            'password' => bcrypt('password'),
            'role_id' => $ingenieroRole->id,
            'headquarter_id' => $hqLima->id,
        ]);

        User::create([
            'name' => 'Cliente de Prueba',
            'email' => 'cliente@gourmetica.com',
            'password' => bcrypt('password'),
            'role_id' => $clienteRole->id,
            'headquarter_id' => null,
        ]);

        $this->call([
            ProductSeeder::class,
            BannerSeeder::class,
        ]);
    }
}
