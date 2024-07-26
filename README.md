# Olshop API

Ini merupakan RESTful API untuk pembuatan toko online sederhana, API ini dapat diakses disini `https://api.baktiweb.my.id/`

## Spesifikasi

-   Language: PHP
-   Framework: Laravel
-   Web Server: Nginx
-   Kontainerisasi: Docker
-   Library Utama: MongoDB, Passport, OAuth 2.0, Redis, Cloudinary, Midtrans, RajaOngkir, Websockets, Scout, Meilisearch
-   Pola Arsitektur: Repository Pattern

## Fitur

-   Payment Gateway
-   Sistem Cek Ongkir
-   Realtime Notifikasi
-   Pencarian Pintar
-   Multi Bahasa
-   Multi Hak Akses
-   Multi Database (SQL & NoSQL)

## Arsitektur

Aplikasi ini mengadopsi pola arsitektur Repository Pattern untuk memisahkan logika bisnis dari lapisan penyimpanan data. Struktur aplikasi ini memiliki komponen-komponen berikut:

-   app: Direktori ini berisi implementasi logika bisnis, termasuk model, controller, dan service dan repository
-   config: Direktori ini berisi file konfigurasi Laravel, seperti konfigurasi database, file sistem, dan lainnya
-   database: Direktori ini berisi migrasi dan pengaturan pengisian awal (seeder) database
-   routes: Direktori ini berisi definisi routing HTTP untuk aplikasi
-   tests: Direktori ini berisi unit tes dan tes fitur untuk memastikan kualitas kode
