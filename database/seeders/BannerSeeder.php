<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Banner;

class BannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar la tabla de banners antes de insertar para evitar duplicados
        Banner::truncate();

        $banners = [
            [
                'title' => "EL ARTE DE LA\nPASTELERÍA FINA",
                'subtitle' => "Sabores únicos creados con insumos de primera calidad y pasión artesanal",
                'button_text' => "Ver Carta",
                'button_url' => "/shop",
                'image_path' => "banners/hero_pastry.png",
                'type' => "hero",
                'is_active' => true,
                'order_index' => 0,
            ],
            [
                'title' => "DULCES MOMENTOS\nPARA COMPARTIR",
                'subtitle' => "Descubre nuestra selección especial de tortas de diseño y bocaditos gourmet",
                'button_text' => "Hacer Pedido",
                'button_url' => "/shop",
                'image_path' => "banners/hero_celebration.png",
                'type' => "hero",
                'is_active' => true,
                'order_index' => 1,
            ],
            [
                'title' => "¡20% de descuento en tu primer pedido con el cupón DULCEBIENVENIDA!",
                'subtitle' => null,
                'button_text' => "Canjear Cupón",
                'button_url' => "/shop",
                'image_path' => "banners/promo_discount.png",
                'type' => "promo",
                'is_active' => true,
                'order_index' => 0,
            ],
            [
                'title' => "Servicio Catering",
                'subtitle' => "Haz de tus celebraciones momentos inolvidables con nuestra selección exclusiva de bocaditos y tortas.",
                'button_text' => "Coordina tu evento",
                'button_url' => "/catering",
                'image_path' => "banners/service_catering.png",
                'type' => "service_catering",
                'is_active' => true,
                'order_index' => 0,
            ],
            [
                'title' => "Pedidos Corporativos",
                'subtitle' => "Sorprende a tu equipo y clientes con el detalle más dulce. Soluciones personalizadas para empresas.",
                'button_text' => "Coordina tu pedido",
                'button_url' => "/corporate",
                'image_path' => "banners/service_corporate.png",
                'type' => "service_corporate",
                'is_active' => true,
                'order_index' => 0,
            ],
            [
                'title' => "Fondo Nuestras Casas",
                'subtitle' => "Tenemos más de 10 casas listas para recibirte y endulzar tu día.",
                'button_text' => "VER NUESTRAS CASAS",
                'button_url' => "/locations",
                'image_path' => "banners/section_locations.png",
                'type' => "section_locations",
                'is_active' => true,
                'order_index' => 0,
            ],
        ];

        foreach ($banners as $bannerData) {
            Banner::create($bannerData);
        }
    }
}
