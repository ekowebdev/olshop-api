# Olshop API

Ini merupakan RESTful API yang dibuat untuk keperluan pembuatan toko online. Aplikasi ini di deploy menggunakan VPS dan dapat diakses dengan link ini `https://api.baktiweb.my.id/`

## Spesifikasi Teknis

-   Framework: Laravel
-   Server Web: Nginx
-   Kontainerisasi: Docker
-   Library Utama: Passport, Cloudinary, Midtrans, RajaOngkir, Websockets, Meilisearch
-   Pola Arsitektur: Repository Pattern

## Fitur

-   Payment Gateway
-   Sistem Cek Ongkir
-   Realtime Notifikasi
-   Pencarian Pintar
-   Multi Bahasa
-   Multi Hak Akses
-   dll

## Arsitektur/Modularitas

Aplikasi ini mengadopsi pola arsitektur Repository Pattern untuk memisahkan logika bisnis dari lapisan penyimpanan data. Struktur aplikasi ini memiliki komponen-komponen berikut:

-   app: Direktori ini berisi implementasi logika bisnis, termasuk model, controller, dan service dan repository.
-   config: Direktori ini berisi file konfigurasi Laravel, seperti konfigurasi database, file sistem, dan lainnya.
-   database: Direktori ini berisi migrasi dan pengaturan pengisian awal (seeder) database.
-   routes: Direktori ini berisi definisi routing HTTP untuk aplikasi.
-   tests: Direktori ini berisi unit tes dan tes fitur untuk memastikan kualitas dan keandalan kode.
