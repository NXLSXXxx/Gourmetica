<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Headquarter;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        $roles = [
            ['name' => 'Admin General', 'slug' => 'admin_general'],
            ['name' => 'Admin Sede', 'slug' => 'admin_sede'],
            ['name' => 'Cajero', 'slug' => 'cajero'],
            ['name' => 'Ingeniero', 'slug' => 'ingeniero'],
            ['name' => 'Cliente', 'slug' => 'cliente'],
        ];
        foreach ($roles as $r) {
            Role::create($r);
        }

        // Headquarters
        $hq = Headquarter::create([
            'name' => 'Sede Principal - Miraflores',
            'address' => 'Av. Larco 1234',
            'city' => 'Miraflores',
            'phone' => '(01) 234-5678',
            'email' => 'miraflores@gourmetica.pe',
            'is_active' => true,
        ]);
        Headquarter::create([
            'name' => 'Sede San Isidro',
            'address' => 'Av. Conquistadores 567',
            'city' => 'San Isidro',
            'phone' => '(01) 876-5432',
            'email' => 'sanisidro@gourmetica.pe',
            'is_active' => true,
        ]);

        // Categories
        $tortas = Category::create(['name' => 'Tortas', 'slug' => 'tortas']);
        $postres = Category::create(['name' => 'Postres', 'slug' => 'postres']);
        $bebidas = Category::create(['name' => 'Bebidas', 'slug' => 'bebidas']);
        $alfajores = Category::create(['name' => 'Alfajores', 'slug' => 'alfajores']);
        Category::create(['name' => 'Brownies', 'slug' => 'brownies']);
        Category::create(['name' => 'Cupcakes', 'slug' => 'cupcakes']);

        // Products
        $products = [
            // Tortas
            ['category_id' => $tortas->id, 'name' => 'Torta Clásica de Vainilla', 'description' => 'Esponjoso bizcocho de vainilla relleno de crema pastelera y cubierto con buttercream.', 'base_price' => 89.00, 'is_active' => true],
            ['category_id' => $tortas->id, 'name' => 'Torta de Chocolate', 'description' => 'Intenso bizcocho de chocolate relleno de ganache y cubierto con frosting de chocolate.', 'base_price' => 95.00, 'is_active' => true],
            // Postres
            ['category_id' => $postres->id, 'name' => 'Cheesecake de Maracuyá', 'description' => 'Suave cheesecake con base de galleta y cobertura de maracuyá natural.', 'base_price' => 22.00, 'is_active' => true],
            ['category_id' => $postres->id, 'name' => 'Tiramisú', 'description' => 'Clásico tiramisú italiano con mascarpone, café y cacao.', 'base_price' => 25.00, 'is_active' => true],
            ['category_id' => $postres->id, 'name' => 'Crème Brûlée', 'description' => 'Clásica crema de vainilla con capa de caramelo crujiente.', 'base_price' => 18.00, 'is_active' => true],
            // Alfajores
            ['category_id' => $alfajores->id, 'name' => 'Alfajor de Maicena', 'description' => 'Dos tapas de maicena rellenas de manjar blanco y bañadas en coco rallado.', 'base_price' => 8.00, 'is_active' => true],
            ['category_id' => $alfajores->id, 'name' => 'Alfajor de Chocolate', 'description' => 'Tapas de cacao rellenas de manjar blanco bañado en chocolate bitter.', 'base_price' => 10.00, 'is_active' => true],
            // Bebidas
            ['category_id' => $bebidas->id, 'name' => 'Café Latte', 'description' => 'Espresso con leche vaporizada.', 'base_price' => 12.00, 'is_active' => true],
            ['category_id' => $bebidas->id, 'name' => 'Chocolate Caliente', 'description' => 'Chocolate belga con leche y marshmallows.', 'base_price' => 14.00, 'is_active' => true],
            ['category_id' => $bebidas->id, 'name' => 'Limonada Frozen', 'description' => 'Limonada natural con hielo picado y hierbabuena.', 'base_price' => 10.00, 'is_active' => true],
            ['category_id' => $bebidas->id, 'name' => 'Té de la Casa', 'description' => 'Selección especial de té negro con frutos rojos.', 'base_price' => 9.00, 'is_active' => true],
        ];

        foreach ($products as $p) {
            $product = Product::create($p);
            $product->headquarters()->attach($hq->id, ['stock' => 50, 'price' => $p['base_price']]);
        }

        // Admin user
        $adminRole = Role::where('slug', 'admin_general')->first();
        User::create([
            'name' => 'Admin Gourmetica',
            'email' => 'admin@gourmetica.pe',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'headquarter_id' => $hq->id,
        ]);
    }
}
