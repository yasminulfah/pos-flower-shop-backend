<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Shipping;
use App\Models\Packaging;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Owner (Akses penuh, lihat laporan)
        User::create([
            'name' => 'Owner Uma Bloemist',
            'email' => 'owner@umabloemist.com',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'is_active' => true,
        ]);

        // 2. Admin (Kelola Produk)
        User::create([
            'name' => 'Admin Uma Bloemist',
            'email' => 'admin@umabloemist.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // 3. Kasir (Operasional Toko & Konfirmasi Pre-Order)
        User::create([
            'name' => 'Yasmin Cashier',
            'email' => 'yasmin@umabloemist.com',
            'password' => Hash::make('password123'),
            'role' => 'cashier', // Pastikan di Migration enum-nya 'cashier' atau 'kasir' ya!
            'is_active' => true,
        ]);

        // 4. Customer Contoh (Untuk testing login di React)
        User::create([
            'name' => 'Ulfah Customer',
            'email' => 'budi@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'customer',
            'is_active' => true,
        ]);

        // 5. Seed Data Shippings (Kurir)
        $shippings = [
            ['shipping_method' => 'Ambil di Toko', 'base_shipping_cost' => 0, 'estimated_time' => 'Depends on you', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['shipping_method' => 'Kurir Internal (Flat)', 'base_shipping_cost' => 15000, 'estimated_time' => 'Everyday, 1-5 pm', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['shipping_method' => 'GrabExpress / GoSend', 'base_shipping_cost' => 25000, 'estimated_time' => 'Everyday, 10 am - 5 pm', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['shipping_method' => 'JNE Express', 'base_shipping_cost' => 30000, 'estimated_time' => '3-4 days', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($shippings as $s) {
            Shipping::create($s);
        }

        // 6. Seed Data Packaging (Kemasan)
        $packagings = [
            ['packaging_name' => 'Paper Bag', 'base_packaging_cost' => 5000, 'packaging_image' => 'https://www.freepik.com/free-psd/isolated-brown-paper-bag-background_49178037.htm#fromView=keyword&page=1&position=1&uuid=b0c604ad-5c61-48f8-916a-903c53f245d5&query=Paper+bag+mockup', 'packaging_description' => 'Paper bag for small bouquets', 'created_at' => now(), 'updated_at' => now()],
            ['packaging_name' => 'Paper Wrap Standard', 'base_packaging_cost' => 3000, 'packaging_image' => 'https://www.freepik.com/free-photo/top-view-bouquet-colorful-tulip-flowers-craft-paper-white-background-with-copy-space_8898090.htm#fromView=search&page=1&position=14&uuid=aa11a96b-9426-45ae-a298-17ed8c769cd0&query=Plastic+bouquet+mockup', 'packaging_description' => 'Basic paper wrap for single flower', 'created_at' => now(), 'updated_at' => now()],
            ['packaging_name' => 'Box', 'base_packaging_cost' => 15000, 'packaging_image' => 'https://www.freepik.com/free-photo/brown-paper-box_3988596.htm#fromView=search&page=2&position=28&uuid=3944638e-069a-49a8-8d70-4488b1967543&query=box+large+mockup', 'packaging_description' => 'Shipping box', 'created_at' => now(), 'updated_at' => now()],
        ];

        foreach ($packagings as $p) {
            Packaging::create($p);
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
            'category_id' => 1, // Single Flower
            'product_name' => 'Holland Rose',
            'slug' => 'holland-rose-flower',
            'description' => 'Premium rose with big petals',
            'main_image' => 'https://www.freepik.com/free-photo/red-rose-background_1182785.htm#fromView=search&page=1&position=41&uuid=04cbd56c-ad66-46e9-8539-2338905a90e1&query=holland+rose'
        ]);

        $rose->variants()->createMany([
            ['product_id' => 1, 'variant_name' => 'Merah', 'price' => 15000, 'stock' => 50, 'sku' => 'ROSE-RED-' . strval(rand(100, 999)), 'detail_image' => 'https://pixabay.com/images/download/x-8940207_1920.jpg'],
            ['product_id' => 1, 'variant_name' => 'Putih', 'price' => 15000, 'stock' => 30, 'sku' => 'ROSE-WHITE-' . strval(rand(100, 999)), 'detail_image' => 'https://pixabay.com/images/download/x-2390503_1920.jpg'],
            ['product_id' => 1, 'variant_name' => 'Pink', 'price' => 17000, 'stock' => 4, 'sku' => 'ROSE-PINK-' . strval(rand(100, 999)), 'detail_image' => 'https://pixabay.com/images/download/x-1610932_1920.jpg'],
        ]);
    }
}