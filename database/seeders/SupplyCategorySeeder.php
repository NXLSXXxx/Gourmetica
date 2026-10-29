<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SupplyCategory;
use App\Models\Supply;

class SupplyCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Lácteos y Huevos',
                'description' => 'Leche, crema de leche, mantequilla, queso crema, huevos',
                'keywords' => ['leche', 'crema', 'mantequilla', 'queso', 'huevo', 'mascarpone', 'yogurt']
            ],
            [
                'name' => 'Harinas, Azúcares y Secos',
                'description' => 'Harinas, féculas, azúcares, polvo de hornear, bicarbonato, sal',
                'keywords' => ['harina', 'azucar', 'azúcar', 'maicena', 'polvo', 'bicarbonato', 'sal', 'levadura', 'fecula', 'fécula', 'avena']
            ],
            [
                'name' => 'Chocolates y Coberturas',
                'description' => 'Cacao en polvo, chocolate bitter, chocolate de leche, chispas',
                'keywords' => ['chocolate', 'cacao', 'cobertura', 'ganache', 'fudge', 'chispas']
            ],
            [
                'name' => 'Frutas, Pulpa y Rellenos',
                'description' => 'Frutas frescas, pulpas, mermeladas, manjar blanco, jaleas',
                'keywords' => ['manjar', 'maracuya', 'maracuyá', 'fresa', 'limon', 'limón', 'pulpa', 'fruta', 'mermelada', 'frambuesa', 'arándano', 'arandano', 'durazno', 'manzana']
            ],
            [
                'name' => 'Esencias, Frutos Secos y Café',
                'description' => 'Vainilla, canela, café, almendras, nueces, licores',
                'keywords' => ['vainilla', 'cafe', 'café', 'canela', 'almendra', 'nuez', 'pecana', 'licor', 'pisco', 'ron', 'esencia', 'colorante']
            ],
            [
                'name' => 'Envases y Empaques',
                'description' => 'Cajas de torta, envases triangulares, envases kraft, bolsas',
                'keywords' => ['envase', 'caja', 'bolsa', 'taper', 'táper', 'blonda', 'base', 'kraft', 'ct4', 'cupcake 7oz', 'triangular']
            ],
            [
                'name' => 'Descartables y Limpieza',
                'description' => 'Papel film, papel manteca, tocas, guantes, servilletas, cucharitas',
                'keywords' => ['film', 'aluminio', 'servilleta', 'cucharita', 'cuchara', 'toca', 'guante', 'manteca', 'papel']
            ],
        ];

        foreach ($categories as $catData) {
            $cat = SupplyCategory::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($catData['name'])],
                [
                    'name' => $catData['name'],
                    'description' => $catData['description']
                ]
            );

            // Auto-categorize existing supplies
            foreach ($catData['keywords'] as $kw) {
                Supply::whereNull('supply_category_id')
                    ->where('name', 'like', "%{$kw}%")
                    ->update(['supply_category_id' => $cat->id]);
            }
        }

        // Put any remaining supplies in Harinas/Secos or Envases as fallback
        $defaultCat = SupplyCategory::where('slug', 'harinas-azucares-y-secos')->first();
        if ($defaultCat) {
            Supply::whereNull('supply_category_id')->update(['supply_category_id' => $defaultCat->id]);
        }
    }
}
