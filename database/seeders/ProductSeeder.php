<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use App\Http\Models\Product;
use Illuminate\Database\Seeder;
use App\Http\Models\ProductImage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(count(Product::all()) == 0) {
            $product1 = Product::create([
                'code' => Str::random(15),
                'name' => 'Samsung Galaxy S9',
                'slug' => Str::slug('Samsung Galaxy S9'),
                'description' => 'Ukuran layar: 6.2 inci, Dual Edge Super AMOLED 2960 x 1440 (Quad HD+) 529 ppi, 18.5:9 Memori: RAM 6 GB (LPDDR4), ROM 64 GB, MicroSD up to 400GB Sistem operasi: Android 8.0 (Oreo) CPU: Exynos 9810 Octa-core (2.7GHz Quad + 1.7GHz Quad), 64 bit, 10nm processor Kamera: Super Speed Dual Pixel, 12 MP OIS (F1.5/F2.4 Dual Aperture) + 12MP OIS (F2.4) with LED flash, depan 8 MP, f/1.7, autofocus, 1440p@30fps, dual video call, Auto HDR SIM: Dual SIM (Nano-SIM) Baterai: Non-removable Li-Ion 3500 mAh , Fast Charging on wired and wireless',
                'point' => 2000000,
                'weight' => 150,
                'quantity' => 30
            ]);

            $product2 = Product::create([
                'code' => Str::random(15),
                'name' => 'iPhone 12 Pro',
                'slug' => Str::slug('iPhone 12 Pro'),
                'description' => 'Ukuran layar: 6.1 inci, Super Retina XDR OLED, 2532 x 1170 piksel, 460 ppi, Memori: RAM 6 GB, ROM 128 GB, Sistem operasi: iOS 14, CPU: Apple A14 Bionic Hexa-core (2x3.1 GHz Firestorm + 4x1.8 GHz Icestorm), Kamera: Triple kamera 12 MP (Wide, Ultra Wide, Telefoto) dengan fitur Night mode, Deep Fusion, dan Dolby Vision HDR recording, kamera depan 12 MP dengan fitur Night mode dan Deep Fusion, SIM: Nano-SIM dan eSIM, Baterai: Non-removable Li-Ion 2815 mAh, Fast Charging, Wireless Charging',
                'point' => 5000000,
                'weight' => 120,
                'quantity' => 15
            ]);

            $product3 = Product::create([
                'code' => Str::random(15),
                'name' => 'Xiaomi Redmi Note 10 Pro',
                'slug' => Str::slug('Xiaomi Redmi Note 10 Pro'),
                'description' => 'Ukuran layar: 6.67 inci, Super AMOLED, 1080 x 2400 piksel, 395 ppi, Memori: RAM 8 GB, ROM 128 GB, MicroSD up to 512GB (shared SIM slot), Sistem operasi: Android 11, MIUI 12, CPU: Qualcomm Snapdragon 732G Octa-core (2x2.3 GHz Kryo 470 Gold + 6x1.8 GHz Kryo 470 Silver), Kamera: Quad kamera 64 MP (Wide), 8 MP (Ultra Wide), 5 MP (Macro), 2 MP (Depth) dengan fitur Night mode, HDR, dan Panorama, kamera depan 16 MP dengan fitur HDR, SIM: Hybrid Dual SIM (Nano-SIM, dual stand-by), Baterai: Non-removable Li-Po 5020 mAh, Fast Charging 33W',
                'point' => 1500000,
                'weight' => 200,
                'quantity' => 25
            ]);

            $product4 = Product::create([
                'code' => Str::random(15),
                'name' => 'Google Pixel 5',
                'slug' => Str::slug('Google Pixel 5'),
                'description' => 'Ukuran layar: 6 inci, OLED, 1080 x 2340 piksel, 432 ppi, Memori: RAM 8 GB, ROM 128 GB, Sistem operasi: Android 11, CPU: Qualcomm Snapdragon 765G Octa-core (1x2.4 GHz Kryo 475 Prime + 1x2.2 GHz Kryo 475 Gold + 6x1.8 GHz Kryo 475 Silver), Kamera: Dual kamera 12.2 MP (Wide), 16 MP (Ultra Wide) dengan fitur Night Sight, Portrait Light, dan HDR+, kamera depan 8 MP dengan fitur Night Sight dan HDR+, SIM: Nano-SIM dan eSIM, Baterai: Non-removable Li-Po 4080 mAh, Fast Charging 18W, Wireless Charging',
                'point' => 1800000,
                'weight' => 200,
                'quantity' => 10
            ]);

            $product5 = Product::create([
                'code' => Str::random(15),
                'name' => 'OnePlus 9 Pro',
                'slug' => Str::slug('OnePlus 9 Pro'),
                'description' => 'Ukuran layar: 6.7 inci, Fluid AMOLED, 1440 x 3216 piksel, 525 ppi, 120Hz, Memori: RAM 12 GB (LPDDR5), ROM 256 GB (UFS 3.1), Sistem operasi: OxygenOS based on Android 11, CPU: Qualcomm Snapdragon 888 Octa-core (1x2.84 GHz Kryo 680 + 3x2.42 GHz Kryo 680 + 4x1.80 GHz Kryo 680), Kamera: Quad kamera 48 MP (Wide), 50 MP (Ultra Wide), 8 MP (Telefoto), 2 MP (Monochrome) dengan fitur Hasselblad Camera for Mobile, Nightscape, Super Macro, dan banyak lagi, kamera depan 16 MP dengan fitur Nightscape, SIM: Dual SIM (Nano-SIM, dual stand-by), Baterai: Non-removable Li-Po 4500 mAh, Warp Charge 65T, 50W Wireless Charging',
                'point' => 1700000,
                'weight' => 250,
                'quantity' => 15
            ]);
        }
    }
}
