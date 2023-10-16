<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use App\Http\Models\ItemGift;
use Illuminate\Database\Seeder;
use App\Http\Models\ItemGiftImage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ItemGiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(count(ItemGift::all()) == 0) {
            $item_gift1 = ItemGift::create([
                'item_gift_code' => Str::random(15),
                'item_gift_name' => 'Samsung Galaxy S9',
                'item_gift_slug' => Str::slug('Samsung Galaxy S9'),
                'item_gift_description' => 'Ukuran layar: 6.2 inci, Dual Edge Super AMOLED 2960 x 1440 (Quad HD+) 529 ppi, 18.5:9 Memori: RAM 6 GB (LPDDR4), ROM 64 GB, MicroSD up to 400GB Sistem operasi: Android 8.0 (Oreo) CPU: Exynos 9810 Octa-core (2.7GHz Quad + 1.7GHz Quad), 64 bit, 10nm processor Kamera: Super Speed Dual Pixel, 12 MP OIS (F1.5/F2.4 Dual Aperture) + 12MP OIS (F2.4) with LED flash, depan 8 MP, f/1.7, autofocus, 1440p@30fps, dual video call, Auto HDR SIM: Dual SIM (Nano-SIM) Baterai: Non-removable Li-Ion 3500 mAh , Fast Charging on wired and wireless',
                'item_gift_point' => 200000,
                'item_gift_weight' => 1.5,
                'item_gift_quantity' => 30
            ]);
            $item_gift_image1 = new ItemGiftImage;
            $item_gift_image1->item_gift_id = $item_gift1->id;
            $item_gift_image1->item_gift_image = 'image.png';
            $item_gift1 = $item_gift1->item_gift_images()->saveMany([$item_gift_image1]);

            $item_gift2 = ItemGift::create([
                'item_gift_code' => Str::random(15),
                'item_gift_name' => 'iPhone 12 Pro',
                'item_gift_slug' => Str::slug('iPhone 12 Pro'),
                'item_gift_description' => 'Ukuran layar: 6.1 inci, Super Retina XDR OLED, 2532 x 1170 piksel, 460 ppi, Memori: RAM 6 GB, ROM 128 GB, Sistem operasi: iOS 14, CPU: Apple A14 Bionic Hexa-core (2x3.1 GHz Firestorm + 4x1.8 GHz Icestorm), Kamera: Triple kamera 12 MP (Wide, Ultra Wide, Telefoto) dengan fitur Night mode, Deep Fusion, dan Dolby Vision HDR recording, kamera depan 12 MP dengan fitur Night mode dan Deep Fusion, SIM: Nano-SIM dan eSIM, Baterai: Non-removable Li-Ion 2815 mAh, Fast Charging, Wireless Charging',
                'item_gift_point' => 500000,
                'item_gift_weight' => 1.5,
                'item_gift_quantity' => 15
            ]);
            $item_gift_image2 = new ItemGiftImage;
            $item_gift_image2->item_gift_id = $item_gift2->id;
            $item_gift_image2->item_gift_image = 'image.png';
            $item_gift2 = $item_gift2->item_gift_images()->saveMany([$item_gift_image2]);

            $item_gift3 = ItemGift::create([
                'item_gift_code' => Str::random(15),
                'item_gift_name' => 'Xiaomi Redmi Note 10 Pro',
                'item_gift_slug' => Str::slug('Xiaomi Redmi Note 10 Pro'),
                'item_gift_description' => 'Ukuran layar: 6.67 inci, Super AMOLED, 1080 x 2400 piksel, 395 ppi, Memori: RAM 8 GB, ROM 128 GB, MicroSD up to 512GB (shared SIM slot), Sistem operasi: Android 11, MIUI 12, CPU: Qualcomm Snapdragon 732G Octa-core (2x2.3 GHz Kryo 470 Gold + 6x1.8 GHz Kryo 470 Silver), Kamera: Quad kamera 64 MP (Wide), 8 MP (Ultra Wide), 5 MP (Macro), 2 MP (Depth) dengan fitur Night mode, HDR, dan Panorama, kamera depan 16 MP dengan fitur HDR, SIM: Hybrid Dual SIM (Nano-SIM, dual stand-by), Baterai: Non-removable Li-Po 5020 mAh, Fast Charging 33W',
                'item_gift_point' => 150000,
                'item_gift_weight' => 1.5,
                'item_gift_quantity' => 25
            ]);
            $item_gift_image3 = new ItemGiftImage;
            $item_gift_image3->item_gift_id = $item_gift3->id;
            $item_gift_image3->item_gift_image = 'image.png';
            $item_gift3 = $item_gift3->item_gift_images()->saveMany([$item_gift_image3]);

            $item_gift4 = ItemGift::create([
                'item_gift_code' => Str::random(15),
                'item_gift_name' => 'Google Pixel 5',
                'item_gift_slug' => Str::slug('Google Pixel 5'),
                'item_gift_description' => 'Ukuran layar: 6 inci, OLED, 1080 x 2340 piksel, 432 ppi, Memori: RAM 8 GB, ROM 128 GB, Sistem operasi: Android 11, CPU: Qualcomm Snapdragon 765G Octa-core (1x2.4 GHz Kryo 475 Prime + 1x2.2 GHz Kryo 475 Gold + 6x1.8 GHz Kryo 475 Silver), Kamera: Dual kamera 12.2 MP (Wide), 16 MP (Ultra Wide) dengan fitur Night Sight, Portrait Light, dan HDR+, kamera depan 8 MP dengan fitur Night Sight dan HDR+, SIM: Nano-SIM dan eSIM, Baterai: Non-removable Li-Po 4080 mAh, Fast Charging 18W, Wireless Charging',
                'item_gift_point' => 180000,
                'item_gift_weight' => 1.5,
                'item_gift_quantity' => 10
            ]);
            $item_gift_image4 = new ItemGiftImage;
            $item_gift_image4->item_gift_id = $item_gift4->id;
            $item_gift_image4->item_gift_image = 'image.png';
            $item_gift4 = $item_gift4->item_gift_images()->saveMany([$item_gift_image4]);

            $item_gift5 = ItemGift::create([
                'item_gift_code' => Str::random(15),
                'item_gift_name' => 'OnePlus 9 Pro',
                'item_gift_slug' => Str::slug('OnePlus 9 Pro'),
                'item_gift_description' => 'Ukuran layar: 6.7 inci, Fluid AMOLED, 1440 x 3216 piksel, 525 ppi, 120Hz, Memori: RAM 12 GB (LPDDR5), ROM 256 GB (UFS 3.1), Sistem operasi: OxygenOS based on Android 11, CPU: Qualcomm Snapdragon 888 Octa-core (1x2.84 GHz Kryo 680 + 3x2.42 GHz Kryo 680 + 4x1.80 GHz Kryo 680), Kamera: Quad kamera 48 MP (Wide), 50 MP (Ultra Wide), 8 MP (Telefoto), 2 MP (Monochrome) dengan fitur Hasselblad Camera for Mobile, Nightscape, Super Macro, dan banyak lagi, kamera depan 16 MP dengan fitur Nightscape, SIM: Dual SIM (Nano-SIM, dual stand-by), Baterai: Non-removable Li-Po 4500 mAh, Warp Charge 65T, 50W Wireless Charging',
                'item_gift_point' => 170000,
                'item_gift_weight' => 1.5,
                'item_gift_quantity' => 15
            ]);
            $item_gift_image5 = new ItemGiftImage;
            $item_gift_image5->item_gift_id = $item_gift5->id;
            $item_gift_image5->item_gift_image = 'image.png';
            $item_gift5 = $item_gift5->item_gift_images()->saveMany([$item_gift_image5]);
        }
    }
}
