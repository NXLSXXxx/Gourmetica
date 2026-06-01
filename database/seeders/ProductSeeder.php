<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\Headquarter;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Existing sample products to keep compatibility
        $catPostres = Category::firstOrCreate(
            ['slug' => 'postres'],
            ['name' => 'Postres']
        );

        $torta = Product::updateOrCreate(
            ['slug' => 'torta-de-chocolate'],
            [
                'category_id' => $catPostres->id,
                'name' => 'Torta de Chocolate',
                'description' => 'Nuestra clásica torta de chocolate con fudge artesanal.',
                'base_price' => 45.00,
                'is_active' => true
            ]
        );

        $optTamano = ProductOption::firstOrCreate(
            ['product_id' => $torta->id, 'name' => 'Tamaño']
        );
        ProductOptionValue::updateOrCreate(
            ['product_option_id' => $optTamano->id, 'value' => 'Porción'],
            ['price_modifier' => -35.00]
        );
        ProductOptionValue::updateOrCreate(
            ['product_option_id' => $optTamano->id, 'value' => 'Entera (12 porciones)'],
            ['price_modifier' => 0.00]
        );

        $cupcake = Product::updateOrCreate(
            ['slug' => 'cupcake-de-vainilla'],
            [
                'category_id' => $catPostres->id,
                'name' => 'Cupcake de Vainilla',
                'description' => 'Suave cupcake de vainilla con frosting de crema.',
                'base_price' => 8.50,
                'is_active' => true
            ]
        );

        $optTopping = ProductOption::firstOrCreate(
            ['product_id' => $cupcake->id, 'name' => 'Toppings']
        );
        ProductOptionValue::updateOrCreate(
            ['product_option_id' => $optTopping->id, 'value' => 'Chispas de Chocolate'],
            ['price_modifier' => 1.50]
        );
        ProductOptionValue::updateOrCreate(
            ['product_option_id' => $optTopping->id, 'value' => 'Fresas'],
            ['price_modifier' => 2.50]
        );
        ProductOptionValue::updateOrCreate(
            ['product_option_id' => $optTopping->id, 'value' => 'Sin Toppings'],
            ['price_modifier' => 0.00]
        );

        // 2. New products and categories requested by user
        $categoriesData = [
            'combos-y-promociones' => [
                'name' => 'Combos y Promociones',
                'products' => [
                    [
                        'name' => 'Promo Jars',
                        'description' => "3 Cheesecake's jar a elección con un precio especial. Sabores disponibles: Fresa, Arándanos, Maracuyá, Chocomaní y Chocoavellanas",
                        'base_price' => 39.00,
                        'options' => [
                            'Sabor de Jar 1' => ['Fresa', 'Arándanos', 'Maracuyá', 'Chocomaní', 'Chocoavellanas'],
                            'Sabor de Jar 2' => ['Fresa', 'Arándanos', 'Maracuyá', 'Chocomaní', 'Chocoavellanas'],
                            'Sabor de Jar 3' => ['Fresa', 'Arándanos', 'Maracuyá', 'Chocomaní', 'Chocoavellanas']
                        ]
                    ],
                    [
                        'name' => 'PROMO Porciones Saludables',
                        'description' => '3 porciones saludables, sabores a elección según stock disponible',
                        'base_price' => 46.00,
                    ],
                    [
                        'name' => 'Trío Perfecto',
                        'description' => "1 Cheesecake's Jar sabor a elección + 1 FIT Snickers + 1 Súper alfajor",
                        'base_price' => 35.00,
                    ],
                    [
                        'name' => 'DeliPack (Chocotorta)',
                        'description' => '1 Chocotorta de quinoa + 6 cookies cacaochips',
                        'base_price' => 23.00,
                    ],
                    [
                        'name' => 'DeliDuo (Carrot Cake)',
                        'description' => '1 porción Fit Carrot Cake + 4 trufas de frutos secos',
                        'base_price' => 19.90,
                    ],
                    [
                        'name' => 'DeliDuo (Chocotorta)',
                        'description' => '1 Chocotorta de quinoa + 4 trufas de frutos secos',
                        'base_price' => 19.90,
                    ],
                    [
                        'name' => 'DeliPack (Carrot Cake)',
                        'description' => '1 Fit Carrot Cake + 6 cookies cacaochips',
                        'base_price' => 23.00,
                    ]
                ]
            ],
            'boxes-y-regalos' => [
                'name' => 'Boxes y Regalos',
                'products' => [
                    [
                        'name' => 'Box Bocaditos Saludables 25un. 🎀',
                        'description' => "25un bocaditos decorados según imagen:\n\n5 mini blondies de almendras\n5 trufas de frutos secos\n5 alfajorcitos de almendras\n5 mini brownies de cacao\n5 cookies cacaochips\n\nIncluye: Box, Lazo, Tarjeta y Dedicatoria 🎁",
                        'base_price' => 60.00,
                    ],
                    [
                        'name' => 'BOX Trufas de frutos secos 16un',
                        'description' => "16un. de nuestras clásicas trufas de frutos secos\n\nIncluye: Box, Lazo, Tarjeta y Dedicatoria 🎁",
                        'base_price' => 40.00,
                    ],
                    [
                        'name' => 'BOX Alfajorcitos corazón de almendras 16un.',
                        'description' => "16un de nuestros clásicos alfajorcitos de almendras, relleno a elección: Manjar de panela o Chocomaní o mixto\n\nIncluye: Box, Lazo, Tarjeta y Dedicatoria 🎁",
                        'base_price' => 38.00,
                        'options' => [
                            'Relleno a elección' => ['Manjar de panela', 'Chocomaní', 'Mixto']
                        ]
                    ],
                    [
                        'name' => 'BOX Bocaditos Saludables clásicos 16un.',
                        'description' => "4 mini blondies de almendras\n4 trufas de frutos secos\n4 alfajorcitos de almendras\n4 mini brownies de cacao\n\nIncluye: Box, Lazo, Tarjeta y Dedicatoria 🎁",
                        'base_price' => 38.00,
                    ],
                    [
                        'name' => 'Box Bocaditos Saludables 16un. 🌀',
                        'description' => "16un bocaditos decorados según imagen:\n\n4 mini blondies de almendras\n4 trufas de frutos secos\n4 alfajorcitos de almendras\n4 mini brownies de cacao\n\nIncluye: Box, Lazo, Tarjeta y Dedicatoria 🎁",
                        'base_price' => 38.00,
                    ],
                    [
                        'name' => 'Box Bocaditos saludable 🌼',
                        'description' => "16un bocaditos decorados según imagen:\n\n4 mini blondies de almendras\n4 trufas de frutos secos\n4 alfajorcitos de almendras\n4 mini brownies de cacao\n\nIncluye: Box, Lazo, Tarjeta y Dedicatoria 🎁",
                        'base_price' => 38.00,
                    ],
                    [
                        'name' => 'Box Bocaditos Saludables 16un 🌸',
                        'description' => "16un bocaditos decorados según imagen:\n\n4 mini blondies de almendras\n4 trufas de frutos secos\n4 alfajorcitos de almendras\n4 mini brownies de cacao\n\nIncluye: Box, Lazo, Tarjeta y Dedicatoria 🎁",
                        'base_price' => 38.00,
                    ],
                    [
                        'name' => 'GIFT BOX I love You',
                        'description' => "1 fit snickers\n1 súper alfajor\n1 cheesecake's jar sabor a elección\n1 brownie o blondie a elección\n3 cookies cacaochips\n\nIncluye: Box, Lazo, Tarjeta y Dedicatoria 🎁.",
                        'base_price' => 58.00,
                        'options' => [
                            'Sabor de Cheesecake Jar' => ['Fresa', 'Arándanos', 'Maracuyá', 'Chocomaní', 'Chocoavellanas'],
                            'Bizcocho a elección' => ['Brownie de cacao', 'Blondie de almendras']
                        ]
                    ],
                    [
                        'name' => 'GIFT BOX My Love',
                        'description' => "1 fit snickers\n1 brownie o blondie a elección\n1 cheesecake's jar sabor a elección\n3 cookies cacaochips\n\nIncluye: Box, Lazo, Tarjeta y Dedicatoria 🎁.",
                        'base_price' => 48.00,
                        'options' => [
                            'Sabor de Cheesecake Jar' => ['Fresa', 'Arándanos', 'Maracuyá', 'Chocomaní', 'Chocoavellanas'],
                            'Bizcocho a elección' => ['Brownie de cacao', 'Blondie de almendras']
                        ]
                    ],
                    [
                        'name' => 'GIFT BOX 3',
                        'description' => "1 mantequilla de maní 350gr.\n1 parfait brownie o blondie a elección\n1 brownie o blondie a elección\n3 cookies cacaochips\n\nIncluye: Box, Lazo, Tarjeta y Dedicatoria 🎁.",
                        'base_price' => 63.00,
                        'options' => [
                            'Parfait a elección' => ['Parfait Brownie', 'Parfait Blondie'],
                            'Bizcocho a elección' => ['Brownie de cacao', 'Blondie de almendras']
                        ]
                    ],
                    [
                        'name' => 'GIFT BOX 1',
                        'description' => "1 cheesecake's jar sabor a elección\n1 brownie o blondie a elección\n1 súper alfajor bañado en chocolate sin azúcar\n4 cookies cacaochips\n\nIncluye: Box, Lazo, Tarjeta y Dedicatoria 🎁",
                        'base_price' => 45.00,
                        'options' => [
                            'Sabor de Cheesecake Jar' => ['Fresa', 'Arándanos', 'Maracuyá', 'Chocomaní', 'Chocoavellanas'],
                            'Bizcocho a elección' => ['Brownie de cacao', 'Blondie de almendras']
                        ]
                    ],
                    [
                        'name' => 'GIFT BOX 2',
                        'description' => "1 parfait brownie o blondie a elección\n1 cheesecake jar sabor a elección\n1 brownie o blondie a elección\n3 cookies cacaochips\n\nIncluye: Box, Lazo, Tarjeta y Dedicatoria 🎁",
                        'base_price' => 51.00,
                        'options' => [
                            'Parfait a elección' => ['Parfait Brownie', 'Parfait Blondie'],
                            'Sabor de Cheesecake Jar' => ['Fresa', 'Arándanos', 'Maracuyá', 'Chocomaní', 'Chocoavellanas'],
                            'Bizcocho a elección' => ['Brownie de cacao', 'Blondie de almendras']
                        ]
                    ]
                ]
            ],
            'postres-en-frasco-y-parfaits' => [
                'name' => 'Postres en Frasco y Parfaits',
                'products' => [
                    [
                        'name' => "Cheesecake's Jar (sabor a elegir)",
                        'description' => "Avena, aceite de coco, queso descremado, endulzados con panela. Sabores disponibles: Fresa, Arándanos, Maracuyá, Chocomani y Chocoavellanas.",
                        'base_price' => 14.00,
                        'options' => [
                            'Sabor de Cheesecake' => ['Fresa', 'Arándanos', 'Maracuyá', 'Chocomaní', 'Chocoavellanas']
                        ]
                    ],
                    [
                        'name' => 'Parfait Blondie o Brownie',
                        'description' => "Yogurt griego, granola artesanal de frutos secos, 2 frutas a elección, brownies o blondies en trozos, 1 topping de miel o mantequilla de maní.\n\n1 Frasco 400gr.",
                        'base_price' => 22.00,
                        'options' => [
                            'Tipo de Parfait' => ['Parfait Brownie', 'Parfait Blondie'],
                            'Toppings / Adicional' => ['Miel', 'Mantequilla de maní']
                        ]
                    ]
                ]
            ],
            'cheesecakes-especiales' => [
                'name' => 'Cheesecakes Especiales',
                'products' => [
                    [
                        'name' => 'Cheesecake Oreo',
                        'description' => 'Avena, cacao, aceite de coco, yogurt griego, queso descremado, endulzo con panela, decorado con fudge de cacao y trozos de nuestra galleta artesanal de cacao y avena.',
                        'base_price' => 17.00,
                    ],
                    [
                        'name' => 'Cheesecake de mango',
                        'description' => 'Avena, almendras, canela, aceite de coco, yogurt griego, queso descremado, endulzado con panela, decorado con salsa y trozos de mango.',
                        'base_price' => 17.00,
                    ],
                    [
                        'name' => 'Cheesecake Alfajor',
                        'description' => 'Avena, almendras, canela, aceite de coco, yogurt griego, queso descremado, endulzo con panela, bañado en manjar de panela y decorado con un alfajorcito de almendras.',
                        'base_price' => 17.00,
                    ],
                    [
                        'name' => 'Cheesecake de Pecanas',
                        'description' => 'Hojuela avena, aceite de coco, queso descremado, caramelo de pecanas, endulzado con panela.',
                        'base_price' => 17.00,
                    ],
                    [
                        'name' => 'Cheesecake Blondie',
                        'description' => 'Harina de almendras, aceite de coco, queso descremado, caramelo de nueces, endulzado con panela.',
                        'base_price' => 17.00,
                    ]
                ]
            ],
            'tortas-y-postres' => [
                'name' => 'Tortas y Postres',
                'products' => [
                    [
                        'name' => 'Húracan de lúcuma',
                        'description' => 'Trozos de nuestros clásicos brownies de cacao, con crema de lúcuma (pulpa de lúcuma, manjar de panela) libre de glúten, contiene lácteos.',
                        'base_price' => 16.00,
                    ],
                    [
                        'name' => 'Tres leches de almendras',
                        'description' => 'Harina pura de almendras, leche vegetal, endulzada con panela, decorada con frosting descremado y canela. (Libre de glúten/Keto)',
                        'base_price' => 14.00,
                    ],
                    [
                        'name' => 'Volcán de lúcuma',
                        'description' => 'Nuestra clásica Chocotorta de quinoa, con manjar de lucúma (pulpa de lúcuma, leche descremada, panela) libre de glúten, contiene lácteos.',
                        'base_price' => 17.00,
                    ],
                    [
                        'name' => 'Crema Volteada',
                        'description' => 'Leche vegetal de coco, huevo, panela. (Sin lácteos, Sin glúten)',
                        'base_price' => 18.00,
                    ],
                    [
                        'name' => 'Red Velvet',
                        'description' => 'Harina de almendras, quinoa, tinte vegetal, aceite de oliva extra virgen, leche vegetal, endulzada con panela, rellena de manjar de panela y bañada en frosting descremado. Decorada con arándanos/fresa o almendras.',
                        'base_price' => 17.00,
                        'options' => [
                            'Decoración' => ['Arándanos y Fresa', 'Almendras']
                        ]
                    ],
                    [
                        'name' => 'FIT Carrot Cake',
                        'description' => 'Harina integral, zanahoria, pecanas, aceite oliva extra virgen, endulzada con panela, rellena y bañada en frosting descremado.',
                        'base_price' => 16.00,
                    ],
                    [
                        'name' => 'Chocotorta de Quinoa',
                        'description' => 'Harina de quinoa, cacao, leche vegetal, chocolate 55% cacao, endulzada con panela. Libre de glúten y lácteos.',
                        'base_price' => 16.00,
                    ]
                ]
            ],
            'bocaditos-y-galletas' => [
                'name' => 'Bocaditos y Galletas',
                'products' => [
                    [
                        'name' => 'Blondie de almendras',
                        'description' => 'Harina de almendras, integral, aceite de coco, trozos de nueces, endulzado con panela.',
                        'base_price' => 8.00,
                    ],
                    [
                        'name' => 'Brownie de cacao',
                        'description' => 'Cacao, aceite de coco, harina de avena, endulzados con panela, decorado con almendras. Libre de gluten y lácteos.',
                        'base_price' => 8.00,
                        'options' => [
                            'Presentación' => [
                                'Unidad' => 0.00,
                                'Pack de 4 unidades' => 22.00,
                                'Pack de 6 unidades' => 37.00
                            ]
                        ]
                    ],
                    [
                        'name' => 'Súper Alfajor',
                        'description' => 'Almendras, avena, aceite de coco, relleno de nuestro manjar de panela, bañado en chocolate sin azúcar 55% cacao',
                        'base_price' => 12.00,
                    ],
                    [
                        'name' => 'FIT Snickers',
                        'description' => 'Avena, caramel vegano, maní, bañado en chocolate sin azúcar 55% cacao. (Libre de glúten, lácteos y vegano)',
                        'base_price' => 13.00,
                    ],
                    [
                        'name' => 'Súper Cookie rellena doble chocolate',
                        'description' => 'Avena, cacao, aceite de coco, endulzadas con panela, rellenas de fudge vegetal. (Sin glúten. Sin lácteos, vegana)',
                        'base_price' => 12.00,
                    ],
                    [
                        'name' => 'Cookies cacaochips 6un.',
                        'description' => 'Harina integral, aceite de coco, chispas de cacao, endulzadas con panela.',
                        'base_price' => 12.00,
                        'options' => [
                            'Presentación / Cantidad' => [
                                '6 unidades' => 0.00,
                                '12 unidades' => 11.00
                            ]
                        ]
                    ],
                    [
                        'name' => 'Trufas de frutos secos 6un.',
                        'description' => 'Mantequilla de maní pura, hojuelas de avena, pecanas, semillas de girasol, miel, bañadas en chocolate sin azúcar 55% cacao.',
                        'base_price' => 13.00,
                        'options' => [
                            'Presentación / Cantidad' => [
                                '6 unidades' => 0.00,
                                '12 unidades' => 12.00
                            ]
                        ]
                    ],
                    [
                        'name' => 'Alfajorcitos de almendras 6un.',
                        'description' => 'Almendras, aceite de coco, endulzados con panela, relleno: Manjar de panela o Chocomaní',
                        'base_price' => 12.00,
                        'options' => [
                            'Relleno' => ['Manjar de panela', 'Chocomaní']
                        ]
                    ]
                ]
            ]
        ];

        $headquarters = Headquarter::all();
        $hqSyncData = [];
        foreach ($headquarters as $hq) {
            $hqSyncData[$hq->id] = ['stock' => 50];
        }

        foreach ($categoriesData as $catSlug => $catInfo) {
            // Create Category
            $category = Category::firstOrCreate(
                ['slug' => $catSlug],
                ['name' => $catInfo['name']]
            );

            foreach ($catInfo['products'] as $prodData) {
                // Generate Slug
                $slug = Str::slug($prodData['name']);

                // Ensure unique slug for duplicate product names (e.g. DeliDuo, DeliPack)
                $count = Product::where('slug', 'like', $slug . '%')->count();
                if ($count > 0 && !Product::where('slug', $slug)->exists()) {
                    // It doesn't exist exactly, but has similar ones. Wait, if it exists exactly, we can update or create a new unique one.
                }

                // Let's perform a unique check based on name and category_id to prevent duplicates of DeliDuo or DeliPack
                // Check if product with this exact name and category already exists.
                $product = Product::where('name', $prodData['name'])
                    ->where('category_id', $category->id)
                    ->where('base_price', $prodData['base_price'])
                    ->first();

                if ($product) {
                    $product->update([
                        'description' => $prodData['description'],
                        'base_price' => $prodData['base_price'],
                        'is_active' => true
                    ]);
                } else {
                    // Create with unique slug
                    $uniqueSlug = $slug;
                    $suffix = 2;
                    while (Product::where('slug', $uniqueSlug)->exists()) {
                        $uniqueSlug = $slug . '-' . $suffix;
                        $suffix++;
                    }

                    $product = Product::create([
                        'category_id' => $category->id,
                        'name' => $prodData['name'],
                        'slug' => $uniqueSlug,
                        'description' => $prodData['description'],
                        'base_price' => $prodData['base_price'],
                        'is_active' => true
                    ]);
                }

                // Seed Headquarters relations
                if (!empty($hqSyncData)) {
                    $product->headquarters()->syncWithoutDetaching($hqSyncData);
                }

                // Seed Options and Option Values
                if (isset($prodData['options'])) {
                    foreach ($prodData['options'] as $optionName => $optionValues) {
                        $option = ProductOption::firstOrCreate([
                            'product_id' => $product->id,
                            'name' => $optionName
                        ]);

                        foreach ($optionValues as $key => $val) {
                            if (is_numeric($key)) {
                                // Array is ['value1', 'value2'] -> no price modifier
                                ProductOptionValue::updateOrCreate(
                                    ['product_option_id' => $option->id, 'value' => $val],
                                    ['price_modifier' => 0.00]
                                );
                            } else {
                                // Associative array is ['value1' => price_modifier]
                                ProductOptionValue::updateOrCreate(
                                    ['product_option_id' => $option->id, 'value' => $key],
                                    ['price_modifier' => $val]
                                );
                            }
                        }
                    }
                }
            }
        }
    }
}
