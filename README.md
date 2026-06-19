# Cafe Management System (CafeWTA)

Sistem Informasi Manajemen Kafe berbasis **Laravel 12** dan **Filament V3** untuk pengelolaan data master, inventori, keuangan, dan pengaturan operasional kafe.

---

## Persyaratan Sistem

Pastikan sistem Anda telah memenuhi spesifikasi berikut:
- **PHP** >= 8.2
- **Composer** (untuk dependensi PHP)
- **Node.js & NPM** (untuk kompilasi aset front-end/Vite)
- **Database** (MySQL / PostgreSQL / SQLite)

---

## Langkah Instalasi

Ikuti langkah-langkah berikut untuk menjalankan project di perangkat lokal Anda:

### 1. Salin Environment File
Salin file konfigurasi `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```

### 2. Konfigurasi Database
Buka file `.env` yang baru dibuat dan sesuaikan konfigurasi koneksi database Anda (misalnya MySQL):
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cafewta
DB_USERNAME=root
DB_PASSWORD=
```
*(Pastikan Anda telah membuat database kosong dengan nama `cafewta` terlebih dahulu jika menggunakan MySQL).*

### 3. Install Dependensi PHP
Jalankan perintah berikut untuk mengunduh semua package PHP yang dibutuhkan:
```bash
composer install
```

### 4. Generate Application Key
Buat kunci enkripsi aplikasi Laravel baru:
```bash
php artisan key:generate
```

### 5. Jalankan Migrasi Database dan Seeder
Jalankan perintah migrasi untuk membuat tabel-tabel di database serta mengisi data awal (Master Data & Akun Admin):
```bash
php artisan migrate:fresh --seed
```

### 6. Install dan Kompilasi Dependensi Front-End
Unduh library Javascript/Vite dan jalankan proses build atau development server:
```bash
# Mengunduh dependensi frontend
npm install

# Kompilasi aset untuk produksi
npm run build
```

---

## Menjalankan Aplikasi

Untuk menjalankan server lokal aplikasi, Anda dapat menggunakan perintah bawaan Laravel Artisan:
```bash
php artisan serve
```
Aplikasi akan berjalan secara default di alamat: [http://127.0.0.1:8000](http://127.0.0.1:8000)

Untuk menjalankan server development terintegrasi (Artisan serve + Vite dev server + queue listener secara bersamaan), gunakan composer script:
```bash
composer run dev
```

---

## Mengakses Halaman Admin (Filament Panel)

Setelah server berjalan, Anda dapat mengakses dashboard admin Filament melalui tautan berikut:
- **URL Dashboard Admin**: [http://127.0.0.1:8000/admin](http://127.0.0.1:8000/admin)

### Akun Login Awal (Super Admin)
Gunakan kredensial berikut untuk masuk ke dashboard pertama kali:
- **Email**: `admin@cafewta.test`
- **Password**: `password`

Di panel admin ini, Anda dapat mengelola:
- **Data Master**: Kategori, Menu, Supplier, Meja Cafe, Metode Pembayaran, dan Akun Pengguna.
- **Inventori**: Bahan Baku dan Resep (Formula porsi bahan baku tiap menu).
- **Finance**: Kategori Pengeluaran.
