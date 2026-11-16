# Baca Saya

Marketplace UMKM Cilacap (CISMART) — Laravel 12 + Livewire/Volt + Filament 4, dengan
service ML (Flask) untuk stratifikasi/clustering UMKM.

## Detail Skema

Skema docker compose untuk menjalankan aplikasi:

- Apache + **PHP 8.4**
- Laravel 12 (Livewire 3, Filament 4)
- PostgreSQL 15
- Flask ML service (profil `ml`, opsional)

## Cara Menggunakan

1. Pastikan Docker & Docker Compose terpasang.
2. Siapkan file `.env` di root (berisi `APP_KEY`, kredensial DB, dan opsi baru —
   lihat tabel di bawah). `APP_ENV=local` penting untuk pengembangan lokal.
3. Jalankan **build cepat** (app + postgres saja; ML & pgAdmin opsional):
   ```bash
   DOCKER_BUILDKIT=1 docker compose up -d --build
   ```
4. Buka `http://localhost:8000` · admin `http://localhost:8000/admin/login`
   (`admin@cismart.test` / `password`).
5. Jalankan migrasi & seeder bila perlu:
   ```bash
   docker compose exec app php artisan migrate --force
   docker compose exec app php artisan db:seed --force
   # data demo (kategori/produk/order): php artisan db:seed --class=DemoSeeder
   ```

### Build lebih cepat

- ML service berat (pip + compiler). Aktifkan bila perlu:
  ```bash
  DOCKER_BUILDKIT=1 docker compose --profile ml up -d --build
  ```
- pgAdmin (opsional): `docker compose --profile tools up -d` (http://localhost:8001)
- Setelah image pernah dibuat, rebuild hanya service yang berubah:
  ```bash
  DOCKER_BUILDKIT=1 docker compose build app && docker compose up -d app
  ```

### Menjalankan tanpa Docker

```bash
bash scripts/start-local.sh
```

## Variabel environment penting

| Variabel | Default | Fungsi |
|---|---|---|
| `APP_KEY` | — | wajib (di-inject ke container) |
| `APP_ENV` | `production` | `local` untuk pengembangan (aset HTTP) |
| `REQUIRE_EMAIL_VERIFICATION` | `true` | area akun hanya untuk email terverifikasi |
| `ORDER_PAYMENT_WINDOW_HOURS` | `24` | batas bayar QRIS sebelum auto-batal |
| `ORDER_CONFIRMATION_WINDOW_HOURS` | `72` | batas konfirmasi COD sebelum auto-batal |
| `ML_SERVICE_URL` | `http://ml_service:5000` | alamat service ML |
| `ML_SERVICE_TIMEOUT` | `60` | timeout request ML |
| `ML_SERVICE_TOKEN` | — | token `X-Internal-Token` Laravel ↔ ML |
| `ML_CORS_ORIGINS` | `http://localhost:8000` | origin yang diizinkan ML service |
| `ML_MAX_TRAINING_FILES` | `10` | retensi file training di volume ML |

> Verifikasi email memakai `MAIL_MAILER=log` secara default, jadi tautan verifikasi
> muncul di `storage/logs/laravel.log`. Untuk demo tanpa mail catcher, set
> `REQUIRE_EMAIL_VERIFICATION=false`.

## Test

```bash
docker compose exec app composer install        # image produksi tanpa dev-deps
docker compose exec app ./vendor/bin/pest
```

Test selalu berjalan di **sqlite in-memory** — `tests/TestCase.php` memaksa
environment dan menolak jalan di database nyata, supaya `RefreshDatabase` tidak
menghapus data dev (docker compose menyuntik `DB_CONNECTION=pgsql`).

## Struktur Direktori

```
laravel_app/        Aplikasi Laravel (app/, tests/, database/)
ml_service/         ML service (Flask) + data training
machine_learning/   Dataset & notebook eksperimen
docker/             Konfigurasi image (php, apache)
docs/               Semua panduan
scripts/            Skrip operasional (start, deploy, train, debug)
docker-compose.yml  Orkestrasi container
```

## Dokumentasi

- `docs/ALUR-SISTEM.md` — alur & aturan bisnis (order, stok, pembayaran, ulasan).
- `docs/LOGIC-AUDIT-2026-11-10.md` — audit logika menyeluruh + status perbaikan.
- `docs/DEPLOYMENT_GUIDE.md`, `docs/ML_INTEGRATION_GUIDE.md`,
  `docs/ML_TRAINING_PRODUCTION_GUIDE.md`, `docs/ML_TESTING_GUIDE.md`,
  `docs/DOCKER_HEALTHCHECK_FIX.md`.
