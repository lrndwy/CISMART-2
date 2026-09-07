# Baca Saya 
## Detail Skema 
Ini adalah skema docker compose yang bisa digunakan untuk menjalankan laravel. 
Beberapa program yang digunakan adalah:
- Apache
- PHP 8.2
- Laravel Versi 12
- Postgres

## Cara Menggunakan
1. Pastikan anda sudah menginstall docker dan docker-compose di komputer anda.
2. Download atau clone repository ini.
3. Buka terminal dan masuk ke direktori tempat anda menyimpan file ini.
4. Jalankan **build cepat** (app + postgres saja; ML & pgAdmin opsional):
   ```bash
   DOCKER_BUILDKIT=1 docker compose up -d --build
   ```
5. Setelah container berjalan, buka browser anda dan akses `http://localhost:8000` untuk melihat aplikasi Laravel yang sedang berjalan.
6. Jangan lupa untuk mengatur file `.env` sesuai dengan kebutuhan anda, terutama bagian database.

### Build lebih cepat
- ML service tidak ikut di-build by default (berat karena `pip install`). Aktifkan bila perlu:
  ```bash
  DOCKER_BUILDKIT=1 docker compose --profile ml up -d --build
  ```
- pgAdmin (opsional):
  ```bash
  docker compose --profile tools up -d
  ```
- Setelah image pernah dibuat, rebuild hanya service yang berubah:
  ```bash
  DOCKER_BUILDKIT=1 docker compose build app
  docker compose up -d app
  ```
- Jangan pakai `--no-cache` kecuali benar-benar perlu; itu memaksa unduh & compile ulang semuanya.
