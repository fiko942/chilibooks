# ChiliBooks

ChiliBooks adalah aplikasi pembukuan internal untuk Chili Oil Gen Z yang dibangun dengan CodeIgniter 4 dan MySQL. Aplikasi ini berfokus pada alur operasional owner: mencatat transaksi, mengelola produk dan stok, memantau pelanggan, serta membuat laporan penjualan dan profit.

## Fitur Utama

- Login owner/internal berbasis session
- Dashboard ringkasan omzet, profit, transaksi, dan pelanggan loyal
- CRUD produk dengan harga jual, harga modal, dan stok
- CRUD pelanggan dengan histori transaksi
- Pencatatan transaksi dengan snapshot harga
- Pembatalan transaksi dengan pengembalian stok
- Laporan dengan export PDF dan Excel
- UI mobile-first dengan lebar konten tetap

## Stack

- PHP 8.2+
- CodeIgniter 4
- MySQL / MariaDB
- CSS custom mobile-first

## Struktur Penting

- `app/Controllers` untuk auth, dashboard, produk, pelanggan, transaksi, dan laporan
- `app/Database/Migrations` untuk schema database
- `app/Database/Seeds` untuk data awal
- `app/Views` untuk tampilan mobile-first
- `public/` sebagai web root aplikasi

## Menjalankan Lokal

```bash
composer install
cp .env.example .env
php spark migrate
php spark db:seed InitialSeeder
php spark serve --host 127.0.0.1 --port 8080
```

Buka `http://localhost:8080/login`.

## Login Default

- Email: `owner@chilioilgenz.id`
- Password: `officer123`

Ubah akun ini setelah deploy production.

## Environment

File rahasia tidak disimpan di git. Gunakan `.env.example` sebagai template dan isi nilai production di server:

- `app.baseURL`
- `database.default.hostname`
- `database.default.database`
- `database.default.username`
- `database.default.password`
- `encryption.key`

## Deploy Notes

- Arahkan web server ke folder `public/`
- Jalankan migration dan seeder di environment tujuan
- Pastikan ekstensi PHP untuk `mysqli`, `intl`, `mbstring`, dan `json` aktif
- Untuk production, ubah `CI_ENVIRONMENT` menjadi `production`
