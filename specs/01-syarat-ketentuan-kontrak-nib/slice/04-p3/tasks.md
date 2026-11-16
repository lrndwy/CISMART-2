# Slice 04-p3 — NIB terlihat oleh admin

## Objective

Admin yang meninjau permohonan bisa membuka NIB setiap pendaftar langsung dari panel, sehingga keputusan setuju/tolak tidak lagi diambil tanpa melihat izin usaha.

Open question yang diwariskan (dari spec §17): Q-005 "Apakah admin perlu melihat riwayat persetujuan per versi di panel?" — default: tidak di plan ini; stage ini hanya menampilkan NIB.

## Vertical Slice

Filament resource → disk `public` → browser admin. Satisfies §8 FR-018; §7 US-005; §10 flow C.

## Tracer Bullet

Bukan tracer bullet: jalur admin hanya membaca data yang sudah ditulis `p2`, jadi menaruhnya lebih awal akan menampilkan field kosong.

## Dependencies

### BLOCKED BY

- p2

### BLOCKS

- None

## Tasks

### TASK-010 — Field NIB di panel admin

- [x] Implemented
- [ ] Reviewed
- [ ] Tested

#### Description

Di `app/Filament/Resources/SellerApplicationResource.php`, tambahkan tampilan NIB memakai `FileUpload::make('nib_document_path')->disk('public')->disabled()->dehydrated(false)` (file tunggal, bukan `multiple()`), berlabel "Izin Usaha (NIB)", mengikuti pola field "Dokumen Pendukung" yang sudah ada dan membantu admin membuka file di tab baru.

#### Acceptance Criteria

- Halaman view permohonan merender NIB yang tersimpan sehingga admin bisa membukanya.
- Permohonan tanpa NIB menampilkan keadaan kosong tanpa galat.
- Field NIB tidak ikut tersimpan saat form disubmit (`dehydrated(false)`), sehingga status tidak berubah karena field ini.

#### Dependencies

- TASK-007