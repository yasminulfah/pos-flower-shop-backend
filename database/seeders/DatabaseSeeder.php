<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Shipping;
use App\Models\Packaging;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Owner
        User::create([
            'name' => 'Owner Uma Bloemist',
            'email' => 'owner@umabloemist.com',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'is_active' => true,
        ]);

        // 2. Admin
        User::create([
            'name' => 'Admin Uma Bloemist',
            'email' => 'admin@umabloemist.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // 3. Kasir
        User::create([
            'name' => 'Yasmin Cashier',
            'email' => 'yasmin@umabloemist.com',
            'password' => Hash::make('password123'),
            'role' => 'cashier',
            'is_active' => true,
        ]);

        // 4. Customer Contoh
        User::create([
            'name' => 'Ulfah Customer',
            'email' => 'budi@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'is_active' => true,
        ]);

        // 5. Seed Data Shippings
        $shippings = [
            ['shipping_method' => 'Ambil di Toko', 'base_shipping_cost' => 0, 'estimated_time' => 'Depends on you', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['shipping_method' => 'Kurir Internal (Flat)', 'base_shipping_cost' => 15000, 'estimated_time' => 'Everyday, 1-5 pm', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['shipping_method' => 'GrabExpress / GoSend', 'base_shipping_cost' => 25000, 'estimated_time' => 'Everyday, 10 am - 5 pm', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['shipping_method' => 'JNE Express', 'base_shipping_cost' => 30000, 'estimated_time' => '3-4 days', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($shippings as $s) {
            Shipping::create($s);
        }

        // 6. Seed Data Packaging
        $packagings = [
            [
                'packaging_name' => 'Paper Wrap Standard', 
                'base_packaging_cost' => 0, 
                'packaging_description' => 'Basic paper wrap for single flower', 
                'packaging_image' => '/images/paper-wrap.jpg'
            ],
            [
                'packaging_name' => 'Paper Bag', 
                'base_packaging_cost' => 5000, 
                'packaging_description' => 'Paper bag for small bouquets', 
                'packaging_image' => '/images/paper-bag.jpg'
            ],
            [
                'packaging_name' => 'Box', 
                'base_packaging_cost' => 15000, 
                'packaging_description' => 'Shipping box', 
                'packaging_image' => '/images/gift-box.jpg'
            ],
        ];

        foreach ($packagings as $p) {
            Packaging::create([
                'packaging_name' => $p['packaging_name'],
                'base_packaging_cost' => $p['base_packaging_cost'],
                'packaging_image' => $p['packaging_image'], 
                'packaging_description' => $p['packaging_description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 7. Seed Categories
        $categories = [
            ['category_name' => 'Single Flower', 'slug' => 'single-flower'],
            ['category_name' => 'Bouquet', 'slug' => 'bouquet'],
            ['category_name' => 'Flower Box', 'slug' => 'flower-box'],
            ['category_name' => 'Vase Flower', 'slug' => 'vase-flower'],
        ];

        foreach ($categories as $cat) {
            \App\Models\Category::create($cat);
        }

        // 8. Seed Products & Variants
        $rose = \App\Models\Product::create([
            'category_id' => 1,
            'product_name' => 'Holland Rose',
            'slug' => 'holland-rose-flower',
            'description' => 'Premium rose with big petals',
            'main_image' => '/images/rose-main.jpg'
        ]);

        $rose->variants()->createMany([
            [
                'product_id' => $rose->id,
                'variant_name' => 'Red',
                'price' => 15000,
                'stock' => 50,
                'sku' => 'ROSE-RED-' . strval(rand(100, 999)),
                'detail_image' => '/images/rose-red.jpg' 
            ],
            [
                'product_id' => $rose->id,
                'variant_name' => 'White',
                'price' => 15000,
                'stock' => 30,
                'sku' => 'ROSE-WHITE-' . strval(rand(100, 999)),
                'detail_image' => '/images/rose-white.jpg'
            ],
            [
                'product_id' => $rose->id,
                'variant_name' => 'Pink',
                'price' => 17000,
                'stock' => 4,
                'sku' => 'ROSE-PINK-' . strval(rand(100, 999)),
                'detail_image' => '/images/rose-pink.jpg'
            ],
            [
                'product_id' => $rose->id,
                'variant_name' => 'Yellow',
                'price' => 17000,
                'stock' => 50,
                'sku' => 'ROSE-YELLOW-' . strval(rand(100, 999)),
                'detail_image' => '/images/rose-yellow.jpg'
            ],
            [
                'product_id' => $rose->id,
                'variant_name' => 'Orange',
                'price' => 17000,
                'stock' => 20,
                'sku' => 'ROSE-ORANGE-' . strval(rand(100, 999)),
                'detail_image' => '/images/rose-orange.jpg'
            ],
            [
                'product_id' => $rose->id,
                'variant_name' => 'Peach',
                'price' => 17000,
                'stock' => 20,
                'sku' => 'ROSE-PEACH-' . strval(rand(100, 999)),
                'detail_image' => '/images/rose-peach.jpg'
            ],
        ]);

        $gerbera = \App\Models\Product::create([
            'category_id' => 1,
            'product_name' => 'gerbera',
            'slug' => 'gerbera-flower',
            'description' => 'Premium gerbera flower',
            'main_image' => '/images/gerbera-main.jpg'
        ]);

        $rose->variants()->createMany([
            [
                'product_id' => $gerbera->id,
                'variant_name' => 'Red',
                'price' => 10000,
                'stock' => 25,
                'sku' => 'GERBERA-RED-' . strval(rand(100, 999)),
                'detail_image' => '/images/gerbera-red.jpg' 
            ],
            [
                'product_id' => $gerbera->id,
                'variant_name' => 'White',
                'price' => 10000,
                'stock' => 25,
                'sku' => 'GERBERA-WHITE-' . strval(rand(100, 999)),
                'detail_image' => '/images/gerbera-white.jpg' 
            ],
            [
                'product_id' => $gerbera->id,
                'variant_name' => 'Pink',
                'price' => 10000,
                'stock' => 25,
                'sku' => 'GERBERA-PINK-' . strval(rand(100, 999)),
                'detail_image' => '/images/gerbera-pink.jpg' 
            ],
            [
                'product_id' => $gerbera->id,
                'variant_name' => 'Fuschia',
                'price' => 10000,
                'stock' => 25,
                'sku' => 'GERBERA-FUSCHIA-' . strval(rand(100, 999)),
                'detail_image' => '/images/gerbera-fuschia.jpg' 
            ],
            [
                'product_id' => $gerbera->id,
                'variant_name' => 'Yellow',
                'price' => 10000,
                'stock' => 25,
                'sku' => 'GERBERA-YELLOW-' . strval(rand(100, 999)),
                'detail_image' => '/images/gerbera-yellow.jpg' 
            ],
            [
                'product_id' => $gerbera->id,
                'variant_name' => 'Peach',
                'price' => 10000,
                'stock' => 25,
                'sku' => 'GERBERA-PEACH-' . strval(rand(100, 999)),
                'detail_image' => '/images/gerbera-peach.jpg' 
            ],
        ]);
    }
}