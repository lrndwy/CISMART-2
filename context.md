# Project Context

## Project

CISMART (repo `CISMART-2`) — marketplace UMKM Cilacap. Kode aplikasi ada di `laravel_app/`, service ML di `ml_service/`.

## Purpose

Mempertemukan UMKM Cilacap dengan pembeli lokal, dan memberi admin gambaran stratifikasi usaha lewat service ML.

## Target Users

Pembeli di Cilacap; pemilik UMKM yang mendaftar sebagai penjual; admin/operator CISMART.

## Core Features

Katalog produk & toko, keranjang, checkout dengan ongkir, pembayaran QRIS, pesanan, ulasan, favorit; permohonan penjual (`SellerApplication`) dengan persetujuan admin yang otomatis membuat toko; panel admin Filament di `/admin`; clustering UMKM via Flask.

## User Flow

Pembeli: jelajah → keranjang → checkout → bayar QRIS → pesanan → ulasan. Penjual: `/register/umkm` → permohonan `pending` → admin setujui → role `seller` + toko aktif.

## Important Business Rules

- Role `seller` diberikan saat admin menyetujui permohonan (`SellerApplication::provisionShop()`), bukan saat submit.
- Toko dibuat/diaktifkan otomatis dari data permohonan ketika status menjadi `approved`; idempoten, tidak menimpa toko yang sudah diisi penjual.
- Satu user punya satu permohonan penjual (`hasOne`).
- Area akun butuh email terverifikasi bila `REQUIRE_EMAIL_VERIFICATION=true`.
- Dokumen pendukung permohonan: 1–3 file JPG/PNG/PDF, maks 2 MB per file.

## Architecture

Monolith Laravel: Blade + halaman Livewire untuk sisi pembeli, Filament 4 di `/admin`; service Flask terpisah dihubungi lewat `App\Services\MLService` (`ML_SERVICE_URL`). PostgreSQL. Docker Compose: app `:8000`, ml_service `:5000`, postgres `:5433`.

## Technology

PHP 8.4 + Apache di container, Laravel 12, Livewire 3 + Volt, Filament 4, Tailwind v4, Pest/PHPUnit, PostgreSQL 15, Flask + scikit-learn, Docker Compose; CI di `.github/workflows/ci.yml`.

## Project Rules

- Semua teks UI berbahasa Indonesia.
- Test di `laravel_app/tests` memakai Pest; test `Feature` memakai `RefreshDatabase`.
- Perubahan skema lewat migration baru; migration yang sudah dijalankan tidak diedit.
- Unggahan dokumen disimpan di disk `public`, folder `seller-documents` (butuh `php artisan storage:link`).
- Fitur admin dikerjakan sebagai Filament Resource di `app/Filament/Resources`.

## Current Development Status

- `plan-01-syarat-ketentuan-kontrak-nib` — halaman Syarat & Ketentuan, halaman Perjanjian Penjual, checkbox persetujuan yang tercatat, dan unggah NIB wajib: p0–p3 ✓ implemented | review & check pending — 10 tasks.
- Baseline tanpa spec: seluruh fitur sebelum plan-01 (katalog, checkout, QRIS, pesanan, ulasan, permohonan penjual, ML) — kode adalah sumber kebenaran.

## Important Decisions

- 2026-11-16: teks legal ditulis di Blade statis dengan versi/tanggal di `config/legal.php`, bukan tabel DB + Filament; admin tidak bisa mengubah teks tanpa deploy (dikorbankan, lihat `plan-01` §18).
- 2026-11-16: persetujuan kontrak dicatat di tabel `agreement_acceptances` per versi, bukan kolom di `seller_applications`, agar riwayat tahan pengajuan ulang.
- 2026-11-16: test lama `RegistrationTest` boleh disesuaikan untuk mengisi field wajib baru (NIB + persetujuan); tidak ada test yang dihapus. Constraint plan-01 §16 ("tanpa perubahan pada test lama") terlalu ketat.

## Known Constraints

- PHP di container masih memakai batas unggah bawaan (`upload_max_filesize=2M`), harus dinaikkan untuk file 5 MB.
- Dokumen penjual tersimpan di disk `public`: siapa pun yang tahu URL-nya bisa membuka file.

## Current Phase

EXEC