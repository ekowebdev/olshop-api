<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Faker\Factory as Faker;
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
            $faker = Faker::create();

            $productNames = [
                'Samsung Galaxy S9',
                'Samsung Galaxy S10',
                'Samsung Galaxy S20',
                'Samsung Galaxy Note 10',
                'Samsung Galaxy Note 20',
                'iPhone 12 Pro',
                'Xiaomi Redmi Note 10 Pro',
                'Google Pixel 5',
                'OnePlus 9 Pro',
            ];

            $productDescriptions = [
                'Ukuran layar: 6.2 inci, Dual Edge Super AMOLED 2960 x 1440 (Quad HD+) 529 ppi, 18.5:9 Memori: RAM 6 GB (LPDDR4), ROM 64 GB, MicroSD up to 400GB Sistem operasi: Android 8.0 (Oreo) CPU: Exynos 9810 Octa-core (2.7GHz Quad + 1.7GHz Quad), 64 bit, 10nm processor Kamera: Super Speed Dual Pixel, 12 MP OIS (F1.5/F2.4 Dual Aperture) + 12MP OIS (F2.4) with LED flash, depan 8 MP, f/1.7, autofocus, 1440p@30fps, dual video call, Auto HDR SIM: Dual SIM (Nano-SIM) Baterai: Non-removable Li-Ion 3500 mAh , Fast Charging on wired and wireless',
                'Ukuran layar: 6.1 inci, Dynamic AMOLED 3040 x 1440 (Quad HD+) 550 ppi, 19:9 Memori: RAM 8 GB (LPDDR5), ROM 128 GB, MicroSD up to 512GB Sistem operasi: Android 9.0 (Pie) CPU: Exynos 9820 Octa-core (2.9GHz Quad + 1.8GHz Quad), 64 bit, 8nm processor Kamera: Triple Camera: 12 MP OIS (F1.5/F2.4 Dual Aperture) + 12MP OIS (F2.4) + 16MP Ultra Wide (F2.2) with LED flash, depan 10 MP, f/1.9, autofocus, 1440p@30fps, dual video call, Auto HDR SIM: Dual SIM (Nano-SIM) Baterai: Non-removable Li-Ion 3400 mAh , Fast Charging on wired and wireless',
                'Ukuran layar: 6.2 inci, Dynamic AMOLED 3200 x 1440 (Quad HD+) 563 ppi, 20:9 Memori: RAM 12 GB (LPDDR5), ROM 256 GB, MicroSD up to 1TB Sistem operasi: Android 10 (Q) CPU: Exynos 990 Octa-core (2.7GHz Quad + 2.0GHz Quad), 64 bit, 7nm processor Kamera: Quad Camera: 12 MP OIS (F1.8) + 64MP Telephoto (F2.0) + 12MP Ultra Wide (F2.2) + ToF Sensor with LED flash, depan 10 MP, f/2.2, autofocus, 1440p@30fps, dual video call, Auto HDR SIM: Dual SIM (Nano-SIM) Baterai: Non-removable Li-Ion 4000 mAh , Fast Charging on wired and wireless',
                'Ukuran layar: 6.3 inci, Super AMOLED 2280 x 1080 (Full HD+) 401 ppi, 19:9 Memori: RAM 8 GB (LPDDR4), ROM 256 GB, MicroSD up to 1TB Sistem operasi: Android 9.0 (Pie) CPU: Exynos 9825 Octa-core (2.8GHz Quad + 1.7GHz Quad), 64 bit, 7nm processor Kamera: Triple Camera: 12 MP OIS (F1.5/F2.4 Dual Aperture) + 12MP Telephoto (F2.1) + 16MP Ultra Wide (F2.2) with LED flash, depan 10 MP, f/2.2, autofocus, 1440p@30fps, dual video call, Auto HDR SIM: Dual SIM (Nano-SIM) Baterai: Non-removable Li-Ion 3500 mAh , Fast Charging on wired and wireless',
                'Ukuran layar: 6.7 inci, Dynamic AMOLED 3200 x 1440 (Quad HD+) 525 ppi, 20:9 Memori: RAM 12 GB (LPDDR5), ROM 512 GB, MicroSD up to 1TB Sistem operasi: Android 10 (Q) CPU: Exynos 990 Octa-core (2.7GHz Quad + 2.0GHz Quad), 64 bit, 7nm processor Kamera: Quad Camera: 108 MP OIS (F1.8) + 48MP Telephoto (F3.5) + 12MP Ultra Wide (F2.2) + ToF Sensor with LED flash, depan 40 MP, f/2.2, autofocus, 1440p@30fps, dual video call, Auto HDR SIM: Dual SIM (Nano-SIM) Baterai: Non-removable Li-Ion 4500 mAh , Fast Charging on wired and wireless',
                'Ukuran layar: 6.1 inci, Super Retina XDR OLED, 2532 x 1170 piksel, 460 ppi, Memori: RAM 6 GB, ROM 128 GB, Sistem operasi: iOS 14, CPU: Apple A14 Bionic Hexa-core (2x3.1 GHz Firestorm + 4x1.8 GHz Icestorm), Kamera: Triple kamera 12 MP (Wide, Ultra Wide, Telefoto) dengan fitur Night mode, Deep Fusion, dan Dolby Vision HDR recording, kamera depan 12 MP dengan fitur Night mode dan Deep Fusion, SIM: Nano-SIM dan eSIM, Baterai: Non-removable Li-Ion 2815 mAh, Fast Charging, Wireless Charging',
                'Ukuran layar: 6.67 inci, Super AMOLED, 1080 x 2400 piksel, 395 ppi, Memori: RAM 8 GB, ROM 128 GB, MicroSD up to 512GB (shared SIM slot), Sistem operasi: Android 11, MIUI 12, CPU: Qualcomm Snapdragon 732G Octa-core (2x2.3 GHz Kryo 470 Gold + 6x1.8 GHz Kryo 470 Silver), Kamera: Quad kamera 64 MP (Wide), 8 MP (Ultra Wide), 5 MP (Macro), 2 MP (Depth) dengan fitur Night mode, HDR, dan Panorama, kamera depan 16 MP dengan fitur HDR, SIM: Hybrid Dual SIM (Nano-SIM, dual stand-by), Baterai: Non-removable Li-Po 5020 mAh, Fast Charging 33W',
                'Ukuran layar: 6 inci, OLED, 1080 x 2340 piksel, 432 ppi, Memori: RAM 8 GB, ROM 128 GB, Sistem operasi: Android 11, CPU: Qualcomm Snapdragon 765G Octa-core (1x2.4 GHz Kryo 475 Prime + 1x2.2 GHz Kryo 475 Gold + 6x1.8 GHz Kryo 475 Silver), Kamera: Dual kamera 12.2 MP (Wide), 16 MP (Ultra Wide) dengan fitur Night Sight, Portrait Light, dan HDR+, kamera depan 8 MP dengan fitur Night Sight dan HDR+, SIM: Nano-SIM dan eSIM, Baterai: Non-removable Li-Po 4080 mAh, Fast Charging 18W, Wireless Charging',
                'Ukuran layar: 6.7 inci, Fluid AMOLED, 1440 x 3216 piksel, 525 ppi, 120Hz, Memori: RAM 12 GB (LPDDR5), ROM 256 GB (UFS 3.1), Sistem operasi: OxygenOS based on Android 11, CPU: Qualcomm Snapdragon 888 Octa-core (1x2.84 GHz Kryo 680 + 3x2.42 GHz Kryo 680 + 4x1.80 GHz Kryo 680), Kamera: Quad kamera 48 MP (Wide), 50 MP (Ultra Wide), 8 MP (Telefoto), 2 MP (Monochrome) dengan fitur Hasselblad Camera for Mobile, Nightscape, Super Macro, dan banyak lagi, kamera depan 16 MP dengan fitur Nightscape, SIM: Dual SIM (Nano-SIM, dual stand-by), Baterai: Non-removable Li-Po 4500 mAh, Warp Charge 65T, 50W Wireless Charging',
            ];

            $chunkSize = 1000;
            $products = [];

            for ($i = 0; $i < 10000; $i++) {
                $randomIndex = array_rand($productNames);

                $products[] = [
                    'code' => Str::random(15),
                    'name' => $productNames[$randomIndex] . $i,
                    'slug' => Str::slug($productNames[$randomIndex] . $i),
                    'description' => $productDescriptions[$randomIndex],
                    'point' => rand(1000, 10000) * 1000, // Generate a random price with at least three zeros
                    'weight' => $faker->numberBetween(100, 200), // Generate a random weight
                    'quantity' => $faker->numberBetween(1, 100), // Generate a random quantity
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                // Insert in chunks
                if (count($products) % $chunkSize == 0) {
                    Product::insert($products);
                    $products = [];
                }
            }

            // Insert any remaining products
            if (!empty($products)) {
                Product::insert($products);
            }

            // for ($i = 0; $i < 5; $i++) {
            //     $randomIndex = array_rand($productNames);
            //     Product::create([
            //         'code' => Str::random(15),
            //         'name' => $productNames[$randomIndex] . $i,
            //         'slug' => Str::slug($productNames[$randomIndex] . $i),
            //         'description' => $productDescriptions[$randomIndex],
            //         'point' => rand(1000, 10000) * 1000,
            //         'weight' => $faker->numberBetween(100, 200),
            //         'quantity' => $faker->numberBetween(1, 100)
            //     ]);
            // }
        }
    }
}
