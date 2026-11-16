# Slice 03-p2 — Izin Usaha (NIB) wajib dan bisa dilihat

## Objective

Setiap permohonan penjual yang masuk membawa file NIB yang benar-benar bisa dibuka, pemohon melihat kembali file yang ia unggah di halaman status, dan pengajuan ulang setelah ditolak tidak memaksa mengunggah ulang.

Open question yang diwariskan (dari spec §17): Q-002 "Perlukah dokumen penjual pindah dari disk `public` ke disk privat + signed route?" — default: tidak untuk plan ini; file memakai nama acak hasil `store()` dan hanya ditautkan ke pemilik serta admin.

## Vertical Slice

Input file Livewire → validasi tipe/ukuran → disk `public` → kolom `nib_document_path` → halaman status. Satisfies §8 FR-014, FR-015, FR-016, FR-017, FR-019; §7 US-003, US-004; §10 flow B (cabang NIB) dan C langkah pertama.

## Tracer Bullet

Bukan tracer bullet. Risiko integrasinya (batas unggah PHP di image Docker) sudah diketahui dari spec §11/D-007, jadi ditangani di stage ini sebagai task tersendiri, bukan dengan memindahkan urutan.

## Dependencies

### BLOCKED BY

- p1

### BLOCKS

- p3

## Tasks

### TASK-007 — Unggah NIB di form permohonan

- [x] Implemented
- [ ] Reviewed
- [ ] Tested

#### Description

Tambahkan properti `nib_document` (satu file), aturan `required` bila `application?->nib_document_path` kosong, `file`, `mimes:pdf,jpg,jpeg,png`, `max:5120`, plus pesan galat Indonesia. Simpan ke `seller-documents/nib` pada disk `public` lewat `store()`, tulis path ke `nib_document_path` di transaksi yang sama dengan permohonan, pertahankan file lama saat submit ulang tanpa file baru, dan hapus file lama setelah transaksi sukses bila diganti. Tambahkan `nib_document_path` ke `$fillable` dan helper `SellerApplication::hasNib()`.

#### Acceptance Criteria

- Submit tanpa file pada permohonan baru gagal validasi pada `nib_document` dan tidak membuat permohonan.
- File `.exe` atau 6 MB gagal validasi tanpa menyimpan file apa pun.
- PDF/JPG 1 MB tersimpan di disk `public` folder `seller-documents/nib`, dan `nib_document_path` terisi.
- Submit ulang tanpa file baru berhasil dan `nib_document_path` tidak berubah.

#### Dependencies

- TASK-004

### TASK-008 — NIB tampil di halaman status permohonan

- [x] Implemented
- [ ] Reviewed
- [ ] Tested

#### Description

Di `livewire/pages/register-umkm-page.blade.php` bagian status (`pending`/`approved`), tampilkan baris "Izin Usaha (NIB)" dengan tautan ke file, memakai pola `Storage::disk('public')->url()` yang sudah dipakai daftar dokumen pendukung; tampilkan "Belum diunggah" bila path kosong. Bagian ini hanya dirender untuk pemilik permohonan (perilaku yang sudah ada).

#### Acceptance Criteria

- Halaman status pemilik menampilkan tautan yang menunjuk ke file NIB yang tersimpan.
- Permohonan tanpa `nib_document_path` menampilkan "Belum diunggah" dan tidak melempar galat.

#### Dependencies

- TASK-007

### TASK-009 — Batas unggah PHP dinaikkan

- [x] Implemented
- [ ] Reviewed
- [ ] Tested

#### Description

`docker/php/Dockerfile` belum mengatur `upload_max_filesize`/`post_max_size` (bawaan 2 MB / 8 MB), sehingga file 5 MB yang sah akan ditolak PHP sebelum validasi Laravel. Tambahkan konfigurasi `upload_max_filesize=10M` dan `post_max_size=12M` ke image, lalu catat di `docs/DEPLOYMENT_GUIDE.md` bahwa nilai itu bisa ditimpa di server.

#### Acceptance Criteria

- Container hasil build melaporkan `upload_max_filesize=10M` dan `post_max_size=12M` dari `php -i`.
- `docs/DEPLOYMENT_GUIDE.md` menyebut kedua nilai dan cara menimpanya.

#### Dependencies

- None