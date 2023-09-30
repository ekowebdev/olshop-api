# Olshop API

Ini merupakan RESTful API yang dibuat untuk keperluan pembuatan toko online. Aplikasi ini di deploy menggunakan AWS EC2 instance dan aplikasi ini dapat diakses dengan link `http://ec2-18-143-187-165.ap-southeast-1.compute.amazonaws.com/`. Aplikasi ini juga telah menerapkan fitur multi bahasa dan juga RBAC dimana hak akses dalam aplikasi ini terbagi menjadi 2 yaitu `admin` & `customer`.

## Spesifikasi Teknis

-   Framework: Laravel
-   Server Web: Nginx
-   Kontainerisasi: Docker
-   Library Utama: Passport, Spatie Permission, AWS S3
-   Pola Arsitektur: Repository Pattern

## Arsitektur/Modularitas

Aplikasi ini mengadopsi pola arsitektur Repository Pattern untuk memisahkan logika bisnis dari lapisan penyimpanan data. Struktur aplikasi ini memiliki komponen-komponen berikut:

-   app: Direktori ini berisi implementasi logika bisnis, termasuk model, controller, dan service dan repository.
-   config: Direktori ini berisi file konfigurasi Laravel, seperti konfigurasi database, file sistem, dan lainnya.
-   database: Direktori ini berisi migrasi dan pengaturan pengisian awal (seeder) database.
-   routes: Direktori ini berisi definisi routing HTTP untuk aplikasi.
-   tests: Direktori ini berisi unit tes dan tes fitur untuk memastikan kualitas dan keandalan kode.

## Dokumentasi Setup

### Setup di Lokal

Berikut adalah langkah-langkah untuk melakukan setup aplikasi di lokal server Anda:

-   Pastikan Anda memiliki Docker dan Docker Compose terinstal di komputer Anda.
-   Clone repositori ini ke direktori lokal Anda.
-   Buka terminal dan arahkan ke direktori aplikasi.
-   Duplikat file .env.example dan ubah namanya menjadi .env. Sesuaikan konfigurasi yang dibutuhkan, seperti pengaturan database dan AWS S3.
-   Jalankan perintah `docker-compose up -d` untuk memulai kontainer Docker.
-   Setelah kontainer selesai dijalankan, jalankan perintah `docker-compose exec app php artisan key:generate` untuk menghasilkan kunci aplikasi yang baru.
-   Jalankan perintah `docker-compose exec app php artisan migrate --seed` untuk menjalankan migrasi database dan pengisian awal.
-   Jalankan perintah `docker-compose exec app php artisan passport:install`.
-   Aplikasi sekarang siap untuk digunakan di lokal komputer Anda. Buka browser dan akses URL `http://localhost` untuk melihat aplikasi.

### Automate Testing

Jika Anda ingin menjalankan automation test, berikut adalah langkah-langkahnya:

-   Pastikan lokal server sudah disiapkan seperti yang dijelaskan sebelumnya.
-   Buka terminal dan arahkan ke direktori aplikasi.
-   Jalankan perintah `docker-compose exec app php artisan test` untuk menjalankan semua tes yang ada.

### Setup Deployment

Jika ingin melakukan deploy aplikasi ini ke AWS EC2 dengan menggunakan Git Workflow, berikut adalah langkah-langkahnya:

-   Pastikan Anda memiliki akun AWS yang valid dan telah membuat EC2 instance.
-   Buka terminal atau command prompt lokal Anda.
-   Clone repository ini ke komputer lokal Anda dengan menggunakan perintah `git clone https://github.com/ekowebdev/olshop-api.git`.
-   Pindah ke direktori aplikasi dengan perintah `cd olshop-api`.
-   Buat file konfigurasi .env dengan perintah `cp .env.example .env`.
-   Sesuaikan konfigurasi pada file .env dengan pengaturan AWS S3 dan informasi database yang sesuai.
-   Buatlah workflows file pada direktori aplikasi.
-   Lakukan commit dan push perubahan tersebut ke repositori git Anda dengan perintah `git add .`, `git commit -m "Menambahkan konfigurasi .env"`, dan `git push origin master`.

#### Konfigurasi di AWS EC2 instance,

-   Buka AWS Management Console dan akses halaman EC2.
-   Pilih EC2 instance yang telah Anda buat untuk deployment aplikasi ini.
-   Hubungkan ke EC2 instance melalui SSH menggunakan private key Anda. Contoh perintah: `ssh -i <path to private key> <username>@<EC2 instance IP>`.
-   Di dalam EC2 instance, pastikan Docker dan Docker Compose terinstal. Jika belum, Anda dapat mengikuti panduan instalasi resmi Docker untuk sistem operasi yang digunakan.
-   Di dalam direktori server EC2 instance, clone repositori git dengan perintah `git clone https://github.com/ekowebdev/olshop-api.git`.
-   Pindah ke direktori aplikasi dengan perintah `cd olshop-api`.
-   Buat file konfigurasi .env dengan perintah `cp .env.example .env`.
-   Sesuaikan konfigurasi pada file .env dengan pengaturan informasi database yang sesuai dan konfigurasi AWS S3.
-   Jalankan perintah `docker-compose up -d` untuk memulai kontainer Docker.
-   Aplikasi sekarang akan dijalankan di instance EC2.

#### Git Workflow

Untuk melakukan update aplikasi setelah melakukan perubahan dan mengikuti Git Workflow, berikut adalah langkah-langkahnya:

-   Lakukan perubahan pada kode aplikasi di komputer lokal Anda.
-   Jalankan perintah `git add .` untuk menambahkan perubahan.
-   Jalankan perintah `git commit -m "Pesan commit"` untuk melakukan commit perubahan.
-   Jalankan perintah `git push origin master` untuk mengirim perubahan ke repository git Anda.
-   Kembali ke EC2 instance melalui SSH.
-   Di dalam direktori aplikasi, jalankan perintah `git pull origin master` untuk mengambil perubahan terbaru dari repository git.
-   Jalankan perintah `docker-compose up -d` untuk memperbarui kontainer Docker dengan versi terbaru dari aplikasi.
