# DPOKU - Sistem Data DPO

Aplikasi web PHP untuk mengelola Data Daftar Pencarian Orang (DPO), bounty, instansi, jenis kasus, jenis hukuman, dan user. Dilengkapi fitur export CSV, audit log, dan pencarian DPO.

## Teknologi

- PHP 8+
- PostgreSQL (via PDO pgsql)
- Tailwind CSS
- jQuery + DataTables + SweetAlert2

## Requirement

- PHP 8+ dengan ekstensi `pgsql` / `pdo_pgsql`
- PostgreSQL 14+
- Web server (Apache/nginx/Laragon/XAMPP)

## Instalasi

1. Clone repository:
   ```bash
   git clone https://github.com/AmirulJusticia0507/dpoku.git
   cd dpoku
   ```

2. Buat database dan import schema:
   ```bash
   psql -U postgres -c "CREATE DATABASE db_dpoku;"
   psql -U postgres -d db_dpoku -f schema_postgres.sql
   ```

   Atau buka `schema_postgres.sql` di Beekeeper Studio / pgAdmin dan jalankan.

3. Buat file `config.local.php` di root proyek (tidak ikut di-commit git):
   ```php
   <?php
   $db_pass = 'password_database_anda';
   ```

## Konfigurasi Database

Kredensial default aplikasi:

| Item       | Nilai              |
|------------|--------------------|
| Host       | `localhost`        |
| Port       | `5432`             |
| Database   | `db_dpoku`         |
| User       | `dpoku`            |
| Password   | `dpoku_4f2c9e7a1b30` |
| SSL        | disable (lokal)    |

Kredensial bisa di-override lewat environment variable:
`DPOKU_DB_HOST`, `DPOKU_DB_NAME`, `DPOKU_DB_USER`, `DPOKU_DB_PASS`, `DPOKU_DB_PORT`.

## User Default

| Username | Password  |
|----------|-----------|
| `admin`  | `admin123` |

> Ganti password setelah login pertama melalui menu User Management.

## Struktur Database

- `instansi` - daftar instansi penegak hukum
- `jenis_kasus` - master jenis kasus pidana (24 data)
- `jenis_hukuman` - master jenis hukuman (17 data)
- `"user"` - akun pengguna (reserved keyword, di-quote)
- `bounty` - jumlah bounty per kasus
- `dpo` - data daftar pencarian orang
- `audit_log` - catatan aktivitas pengguna

## Fitur

- Login dengan rate-limiting (anti brute-force)
- CRUD DPO, instansi, jenis kasus, jenis hukuman, user, bounty
- Pencarian DPO (NIK, nama, instansi)
- Upload foto DPO dengan frame "WANTED"
- Export CSV dengan filter
- Audit log semua aktivitas
- UI Tailwind CSS (mobile responsive)

## Lisensi

Proyek pribadi.
